<?php
namespace Fluxx\States;

use Fluxx\Game\Utils;
use Fluxx\Cards\Rules\RuleCardFactory;
use Fluxx\Cards\Actions\ActionCardFactory;

trait ResolveActionTrait
{
  function st_resolveAction()
  {
    $player_id = self::getActivePlayerId();
    $players = self::loadPlayersBasicInfos();

    // @TODO: for now, just mark action as finished and continue play
    // this should actually be done as response to specific client actions
    // depending on the special action card that was played

    //self::action_resolveActionWithCards([]);
  }

  private function getCurrentResolveActionCard()
  {
    $game = Utils::getGame();
    $actionCardId = self::getGameStateValue("actionToResolve");
    $card = $game->cards->getCard($actionCardId);
    return ActionCardFactory::getCard($card["id"], $card["type_arg"]);
  }

  public function arg_resolveAction()
  {
    // $game = Utils::getGame();

    $actionCardId = self::getGameStateValue("actionToResolve");
    // $card = $game->cards->getCard($actionCardId);
    $actionCard = self::getCurrentResolveActionCard();

    return [
      "action_id" => $actionCard->getCardId(),
      "action_name" => $actionCard->getName(),
      "action_type" => $actionCard->interactionNeeded,
      "action_args" => $actionCard->resolveArgs(),
    ];
  }

  /*
   * Player resolves any action card, with the cards selected
   */
  public function action_resolveActionWithCards($option, $cards_id)
  {
    $game = Utils::getGame();

    self::checkAction("resolveAction");
    $player_id = self::getActivePlayerId();

    $args = self::arg_resolveAction();
    $actionCardId = $args["action_id"];
    $card = $game->cards->getCard($actionCardId);
    $actionCard = ActionCardFactory::getCard($card["id"], $card["type_arg"]);
    $actionName = $actionCard->getName();

    $stateTransition = $actionCard->resolvedBy($player_id, [
      "option" => $option,
      "cardIdsSelected" => $cards_id,
    ]);

    $players = self::loadPlayersBasicInfos();
    self::notifyAllPlayers(
      "actionDone",
      clienttranslate('${player_name} finished action ${action_name}'),
      [
        "player_id" => $player_id,
        "player_name" => $players[$player_id]["player_name"],
        "action_name" => $actionName,
      ]
    );
    self::setGameStateValue("actionToResolve", -1);

    if ($stateTransition != null) {
      $game->gamestate->nextstate($stateTransition);
    } else {
      $game->gamestate->nextstate("resolvedAction");
    }
  }

  private function _action_resolveAction($args)
  {
    $player_id = self::getActivePlayerId();

    $actionCard = self::getCurrentResolveActionCard();
    $actionName = $actionCard->getName();

    $stateTransition = $actionCard->resolvedBy($player_id, $args);

    self::setGameStateValue("actionToResolve", -1);

    $game = Utils::getGame();
    $game->checkWinConditions();

    if ($stateTransition != null) {
      $game->gamestate->nextstate($stateTransition);
    } else {
      $game->gamestate->nextstate("resolvedAction");
    }
  }

  public function action_resolveActionPlayerSelection($selected_player_id)
  {
    self::checkAction("resolveActionPlayerSelection");
    return self::_action_resolveAction([
      "selected_player_id" => $selected_player_id,
    ]);
  }

  public function action_resolveActionCardSelection(
    $card_id,
    $card_definition_id
  ) {
    self::checkAction("resolveActionCardSelection");

    $game = Utils::getGame();

    $card = $game->cards->getCard($card_id);

    return self::_action_resolveAction(["card" => $card]);
  }

  public function action_resolveActionCardsSelection($cards_id)
  {
    self::checkAction("resolveActionCardsSelection");

    $game = Utils::getGame();

    $cards = [];
    foreach ($cards_id as $card_id) {
      $cards[] = $game->cards->getCard($card_id);
    }
    return self::_action_resolveAction(["cards" => $cards]);
  }

  public function action_resolveActionKeepersExchange(
    $myKeeperId,
    $otherKeeperId
  ) {
    self::checkAction("resolveActionKeepersExchange");
    $game = Utils::getGame();

    $myKeeper = $game->cards->getCard($myKeeperId);
    $otherKeeper = $game->cards->getCard($otherKeeperId);

    return self::_action_resolveAction([
      "myKeeper" => $myKeeper,
      "otherKeeper" => $otherKeeper,
    ]);
  }

  public function action_resolveActionButtons($value)
  {
    self::checkAction("resolveActionButtons");
    return self::_action_resolveAction(["value" => $value]);
  }
}
