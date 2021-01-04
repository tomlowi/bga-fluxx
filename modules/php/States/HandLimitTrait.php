<?php
namespace Fluxx\States;

use Fluxx\Game\Utils;

trait HandLimitTrait
{
  public function st_enforceHandLimitForOthers()
  {
    $game = Utils::getGame();

    $handLimit = $game->getGameStateValue("handLimit");
    if ($handLimit < 0) {
      // no active Hand Limit, nothing to do
      $game->gamestate->nextstate("");
      return;
    }

    // The hand limit doesn't apply to the active player.
    $active_player_id = $game->getActivePlayerId();

    $players = $game->loadPlayersBasicInfos();
    // find all players with too much cards in hand
    $playersInInfraction = [];
    foreach ($players as $player_id => $player) {
      if ($player_id != $active_player_id) {
        $cardsInHand = $game->cards->countCardInLocation("hand", $player_id);
        if ($cardsInHand > $handLimit) {
          $playersInInfraction[] = $player_id;
        }
      }
    }

    // Activate all players that need to discard (if any)
    $game->gamestate->setPlayersMultiactive($playersInInfraction, "", true);
  }

  public function st_enforceHandLimitForSelf()
  {
    $game = Utils::getGame();

    $handLimit = $game->getGameStateValue("handLimit");
    if ($handLimit < 0) {
      // no active Hand Limit, nothing to do
      $game->gamestate->nextstate("");
      return;
    }

    $player_id = $game->getActivePlayerId();
    $cardsInHand = $game->cards->countCardInLocation("hand", $player_id);

    if ($cardsInHand <= $handLimit) {
      // Player is complying with the rule
      $game->gamestate->nextstate("");
      return;
    }
  }

  public function arg_enforceHandLimitForOthers()
  {
    $game = Utils::getGame();

    $handLimit = $game->getGameStateValue("handLimit");

    // multiple active state, can't use getCurrentPlayerId here!
    $players = $game->loadPlayersBasicInfos();
    $playersInfraction = [];

    foreach ($players as $player_id => $player) {
      $cardsInHand = $game->cards->countCardInLocation("hand", $player_id);
      $playersInfraction[$player_id] = ["count" => $cardsInHand - $handLimit];
    }

    return [
      "limit" => $handLimit,
      "_private" => $playersInfraction,
    ];
  }

  public function arg_enforceHandLimitForSelf()
  {
    $game = Utils::getGame();

    $handLimit = $game->getGameStateValue("handLimit");

    $player_id = $game->getActivePlayerId();
    $cardsInHand = $game->cards->countCardInLocation("hand", $player_id);

    $playersInfraction = [
      "active" => ["count" => $cardsInHand - $handLimit],
    ];

    return [
      "limit" => $handLimit,
      "_private" => $playersInfraction,
    ];
  }

  /*
   * Player discards a nr of cards for hand limit
   */
  function action_discardHandCards($cards_id)
  {
    $game = Utils::getGame();

    // possible multiple active state, so don't use checkAction or getActivePlayerId here!
    $game->gamestate->checkPossibleAction("discardHandCards");
    $player_id = $game->getCurrentPlayerId();

    $handLimit = $game->getGameStateValue("handLimit");
    $cardsInHand = $game->cards->countCardInLocation("hand", $player_id);
    $expectedCount = $cardsInHand - $handLimit;
    if (count($cards_id) != $expectedCount) {
      throw new BgaUserException(
        $game->_("Wrong number of cards. Expected: ") . $expectedCount
      );
    }

    $cards = [];
    $game->dump("discardingPlayer", $player_id);
    foreach ($cards_id as $card_id) {
      // Verify card was in player hand
      $card = $game->cards->getCard($card_id);
      if (
        $card == null ||
        $card["location"] != "hand" ||
        $card["location_arg"] != $player_id
      ) {
        throw new BgaUserException(
          $game->_("Impossible discard: invalid card ") . $card_id
        );
      }

      $cards[$card["id"]] = $card;

      // Discard card
      $game->cards->playCard($card["id"]);
    }

    $game->notifyAllPlayers("handDiscarded", "", [
      "player_id" => $player_id,
      "cards" => $cards,
      "discardCount" => $game->cards->countCardInLocation("discard"),
      "handCount" => $game->cards->countCardInLocation("hand", $player_id),
    ]);

    $state = $game->gamestate->state();

    if ($state["type"] == "multipleactiveplayer") {
      // Multiple active state: this player is done
      $game->gamestate->setPlayerNonMultiactive($player_id, "");
    } else {
      $game->gamestate->nextstate("");
    }
  }
}
