<?php
namespace Fluxx\States;

use Fluxx\Game\Utils;
use fluxx;
use Fluxx\Cards\Keepers\KeeperCardFactory;
use Fluxx\Cards\Goals\GoalCardFactory;
use Fluxx\Cards\Rules\RuleCardFactory;
use Fluxx\Cards\Actions\ActionCardFactory;
use Fluxx\Cards\Rules\RulePartyBonus;
use Fluxx\Cards\Rules\RuleRichBonus;

trait PlayCardTrait
{
  public function st_playCard()
  {
    $game = Utils::getGame();

    // If any card is a force move, play it
    $forcedCardId = $game->getGameStateValue("forcedCard");

    if ($forcedCardId != -1) {
      $game->setGameStateValue("forcedCard", -1);
      self::action_playCard($forcedCardId);
      return;
    }

    // If any "free action" rule can be played, we cannot move to the next state
    $rules = $game->cards->getCardsInLocation("rules", RULE_OTHERS);

    foreach ($rules as $rule_id => $rule) {
      $ruleCard = RuleCardFactory::getCard($rule["id"], $rule["type_arg"]);

      if ($ruleCard->canBeUsedByPlayer) {
        return;
      }
    }

    $player_id = $game->getActivePlayerId();

    // current rule and nb of cards already played
    $playRule = $game->getGameStateValue("playRule");
    $cardsPlayed = $game->getGameStateValue("playedCards");

    // check bonus rules
    $addInflation = Utils::getActiveInflation() ? 1 : 0;
    $partyBonus =
      Utils::getActivePartyBonus() && Utils::isPartyInPlay()
        ? 1 + $addInflation
        : 0;
    if ($partyBonus > 0) {
      RulePartyBonus::notifyActiveFor($player_id);
    }
    $richBonus =
      Utils::getActiveRichBonus() && Utils::hasMostKeepers($player_id)
        ? 1 + $addInflation
        : 0;
    if ($richBonus > 0) {
      RuleRichBonus::notifyActiveFor($player_id);
    }

    $playRule += $addInflation + $partyBonus + $richBonus;

    // still cards in hand?
    $cardsInHand = $game->cards->countCardInLocation("hand", $player_id);

    // @TODO: At the moment, we don't handle play all but 1 properly with Inflation
    // And we need to review how "rich bonus" is handled, because potentially we
    // would need a state just for this card

    // is Play All But 1 in play ?
    // If not, did the player play enough cards already (or hand empty) ?
    if (
      ($playRule == -1 && $cardsInHand == 1) ||
      ($playRule != -1 && $cardsPlayed >= $playRule) ||
      $cardsInHand == 0
    ) {
      $game->gamestate->nextstate("endOfTurn");
    }
  }

  public function arg_playCard()
  {
    $game = Utils::getGame();
    $player_id = $game->getActivePlayerId();

    $playRule = $game->getGameStateValue("playRule");
    $played = $game->getGameStateValue("playedCards");
    if ($playRule > 100) {
      return ["count" => "All"];
    }
    if ($playRule == -1) {
      return ["count" => "All but 1"];
    }

    $addInflation = Utils::getActiveInflation() ? 1 : 0;
    $partyBonus =
      Utils::getActivePartyBonus() && Utils::isPartyInPlay()
        ? 1 + $addInflation
        : 0;
    $richBonus =
      Utils::getActiveRichBonus() && Utils::hasMostKeepers($player_id)
        ? 1 + $addInflation
        : 0;

    $playRule += $addInflation + $partyBonus + $richBonus;

    return ["count" => $playRule - $played];
  }

  public function action_playCard($card_id)
  {
    $game = Utils::getGame();

    // Check that this is the player's turn and that it is a "possible action" at this game state (see states.inc.php)
    $game->checkAction("playCard");

    $player_id = $game->getActivePlayerId();
    $card = $game->cards->getCard($card_id);

    if ($card["location"] != "hand" or $card["location_arg"] != $player_id) {
      Utils::throwInvalidUserAction(
        fluxx::totranslate("You do not have this card in hand")
      );
    }

    $card_type = $card["type"];
    $stateTransition = null;
    switch ($card_type) {
      case "keeper":
        $this->playKeeperCard($player_id, $card);
        break;
      case "goal":
        $stateTransition = $this->playGoalCard($player_id, $card);
        break;
      case "rule":
        $stateTransition = $this->playRuleCard($player_id, $card);
        break;
      case "action":
        $stateTransition = $this->playActionCard($player_id, $card);
        break;
      default:
        die("Not implemented: Card type $card_type does not exist");
        break;
    }

    $game->incGameStateValue("playedCards", 1);

    // A card has been played: do we have a new winner?
    $game->checkWinConditions();

    if ($stateTransition != null) {
      // player must resolve something before continuing to play more cards
      $game->gamestate->nextstate($stateTransition);
    } else {
      // else: just let player continue playing cards
      // but explicitly set state again to force args refresh
      $game->gamestate->nextstate("continuePlay");
    }
  }

  public function playKeeperCard($player_id, $card)
  {
    $game = Utils::getGame();

    $game->cards->moveCard($card["id"], "keepers", $player_id);

    // Notify all players about the keeper played
    $keeperCard = KeeperCardFactory::getCard($card["id"], $card["type_arg"]);
    $game->notifyAllPlayers(
      "keeperPlayed",
      clienttranslate('${player_name} plays keeper <b>${card_name}</b>'),
      [
        "i18n" => ["card_name"],
        "player_name" => $game->getActivePlayerName(),
        "player_id" => $player_id,
        "card_name" => $keeperCard->getName(),
        "card" => $card,
        "handCount" => $game->cards->countCardInLocation("hand", $player_id),
      ]
    );
  }

  public function playGoalCard($player_id, $card)
  {
    $game = Utils::getGame();

    // Notify all players about the goal played
    $goalCard = GoalCardFactory::getCard($card["id"], $card["type_arg"]);
    $game->notifyAllPlayers(
      "goalPlayed",
      clienttranslate('${player_name} sets a new goal <b>${card_name}</b>'),
      [
        "i18n" => ["card_name"],
        "player_name" => $game->getActivePlayerName(),
        "player_id" => $player_id,
        "card_name" => $goalCard->getName(),
        "card" => $card,
        "handCount" => $game->cards->countCardInLocation("hand", $player_id),
      ]
    );

    $existingGoalCount = $game->cards->countCardInLocation("goals");
    $hasDoubleAgenda = Utils::getActiveDoubleAgenda();

    // No double agenda: we simply discard the oldest goal
    if (!$hasDoubleAgenda) {
      $goals = $game->cards->getCardsInLocation("goals");
      foreach ($goals as $goal_id => $goal) {
        $game->cards->playCard($goal_id);
      }

      if ($goals) {
        $game->notifyAllPlayers("goalsDiscarded", "", [
          "cards" => $goals,
          "discardCount" => $game->cards->countCardInLocation("discard"),
        ]);
      }
    }

    // We play the new goal
    $game->cards->moveCard($card["id"], "goals");

    if ($hasDoubleAgenda && $existingGoalCount > 1) {
      $game->setGameStateValue("lastGoalBeforeDoubleAgenda", $card["id"]);
      return "doubleAgendaRule";
    }
  }

  public function playRuleCard($player_id, $card)
  {
    $game = Utils::getGame();

    $ruleCard = RuleCardFactory::getCard($card["id"], $card["type_arg"]);
    $ruleType = $ruleCard->getRuleType();

    // Notify all players about the new rule
    // (this needs to be done before the effect, otherwise the history is confusing)
    $game->notifyAllPlayers(
      "rulePlayed",
      clienttranslate('${player_name} placed a new rule: <b>${card_name}</b>'),
      [
        "i18n" => ["card_name"],
        "player_name" => $game->getActivePlayerName(),
        "card_name" => $ruleCard->getName(),
        "player_id" => $player_id,
        "ruleType" => $ruleType,
        "card" => $card,
        "handCount" => $game->cards->countCardInLocation("hand", $player_id),
      ]
    );

    $location_arg = $game->getLocationArgForRuleType($ruleType);

    // Execute the immediate rule effect
    $stateTransition = $ruleCard->immediateEffectOnPlay($player_id);

    $game->cards->moveCard($card["id"], "rules", $location_arg);

    return $stateTransition;
  }

  public function playActionCard($player_id, $card)
  {
    $game = Utils::getGame();

    $game->setGameStateValue("actionToResolve", -1);
    $actionCard = ActionCardFactory::getCard($card["id"], $card["type_arg"]);

    // Notify all players about the action played
    // (this needs to be done before the effect, otherwise the history is confusing)
    $game->notifyAllPlayers(
      "actionPlayed",
      clienttranslate('${player_name} plays an action: <b>${card_name}</b>'),
      [
        "i18n" => ["card_name"],
        "player_name" => $game->getActivePlayerName(),
        "player_id" => $player_id,
        "card_name" => $actionCard->getName(),
        "card" => $card,
        "handCount" => $game->cards->countCardInLocation("hand", $player_id),
        "discardCount" => $game->cards->countCardInLocation("discard"),
      ]
    );

    // We play the new action card
    $game->cards->playCard($card["id"]);

    // execute the action immediate effect
    $stateTransition = $actionCard->immediateEffectOnPlay($player_id);

    return $stateTransition;
  }
}
