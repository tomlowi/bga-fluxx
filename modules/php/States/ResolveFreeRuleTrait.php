<?php
namespace Fluxx\States;

use Fluxx\Game\Utils;
use Fluxx\Cards\Rules\RuleCardFactory;

trait ResolveFreeRuleTrait
{
  function st_resolveFreeRule()
  {
    $player_id = self::getActivePlayerId();
  }

  private function getCurrentResolveFreeRuleCard()
  {
    $game = Utils::getGame();
    $freeRuleCardId = self::getGameStateValue("freeRuleToResolve");
    $card = $game->cards->getCard($freeRuleCardId);
    return RuleCardFactory::getCard($card["id"], $card["type_arg"]);
  }

  public function arg_resolveFreeRule()
  {
    $freeRuleCard = self::getCurrentResolveFreeRuleCard();

    return [
      "action_id" => $freeRuleCard->getCardId(),
      "action_name" => $freeRuleCard->getName(),
      "action_type" => $freeRuleCard->interactionNeeded,
      "action_args" => $freeRuleCard->resolveArgs(),
    ];
  }

  private function _action_resolveFreeRule($args)
  {
    $player_id = self::getActivePlayerId();

    $card = self::getCurrentResolveFreeRuleCard();
    $cardName = $card->getName();

    $stateTransition = $card->resolvedBy($player_id, $args);

    self::setGameStateValue("freeRuleToResolve", -1);

    $game = Utils::getGame();

    // If we have a forced move, we cannot win yet
    if ($game->getGameStateValue("forcedCard") != -1) {
      // An action has been resolved: do we have a new winner?
      $game->checkWinConditions();
      // if not, maybe the card played had effect for any of the bonus conditions?
      $game->checkBonusConditions($player_id);
    }

    if ($stateTransition != null) {
      $game->gamestate->nextstate($stateTransition);
    } else {
      $game->gamestate->nextstate("resolvedFreeRule");
    }
  }

  public function action_resolveFreeRuleCardSelection($card_id)
  {
    self::checkAction("resolveFreeRuleCardSelection");

    $game = Utils::getGame();
    $card = $game->cards->getCard($card_id);

    return self::_action_resolveFreeRule(["card" => $card]);
  }

  public function action_resolveFreeRuleCardsSelection($cards_id)
  {
    self::checkAction("resolveFreeRuleCardsSelection");

    $game = Utils::getGame();

    $cards = [];
    foreach ($cards_id as $card_id) {
      $cards[] = $game->cards->getCard($card_id);
    }
    return self::_action_resolveFreeRule(["cards" => $cards]);
  }

}
