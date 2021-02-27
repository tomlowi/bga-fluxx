<?php
namespace Fluxx\States;

use Fluxx\Game\Utils;
use Fluxx\Cards\Rules\RuleCardFactory;
use Fluxx\Cards\Actions\ActionCardFactory;

trait ResolveActionTrait
{
  function st_resolveAction()
  {
    // nothing here, wait for clientside actions
    // depending on the special action card that was played
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

  private function _action_resolveAction($args)
  {
    $player_id = self::getActivePlayerId();

    $actionCard = self::getCurrentResolveActionCard();
    $actionName = $actionCard->getName();

    $stateTransition = $actionCard->resolvedBy($player_id, $args);

    self::setGameStateValue("actionToResolve", -1);

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

  public function action_resolveActionCardSelection($card_id)
  {
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
