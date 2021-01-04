<?php
namespace Fluxx\States;

use Fluxx\Game\Utils;

trait KeepersLimitTrait
{
  public function st_enforceKeepersLimitForOthers()
  {
    $game = Utils::getGame();

    $keeperLimit = $game->getGameStateValue("keepersLimit");
    if ($keeperLimit < 0) {
      // no active Keeper Limit, nothing to do
      $game->gamestate->nextstate("");
      return;
    }

    // The keepers limit doesn't apply to the active player.
    $active_player_id = $game->getActivePlayerId();

    $players = $game->loadPlayersBasicInfos();
    // find all players with too much keepers in play
    $playersInInfraction = [];
    foreach ($players as $player_id => $player) {
      if ($player_id != $active_player_id) {
        $keepersInPlay = $game->cards->countCardInLocation(
          "keepers",
          $player_id
        );
        if ($keepersInPlay > $keeperLimit) {
          $playersInInfraction[] = $player_id;
        }
      }
    }

    // Activate all players that need to remove keepers (if any)
    $game->gamestate->setPlayersMultiactive($playersInInfraction, "", true);
  }

  public function st_enforceKeepersLimitForSelf()
  {
    $game = Utils::getGame();

    $keeperLimit = $game->getGameStateValue("keepersLimit");
    if ($keeperLimit < 0) {
      // no active Keepers Limit, nothing to do
      $game->gamestate->nextstate("");
      return;
    }

    $player_id = $game->getActivePlayerId();
    $keepersInPlay = $game->cards->countCardInLocation("keepers", $player_id);

    if ($keepersInPlay <= $keeperLimit) {
      // Player is complying with the rule
      $game->gamestate->nextstate("");
      return;
    }
  }

  public function arg_enforceKeepersLimitForOthers()
  {
    $game = Utils::getGame();

    $keepersLimit = $game->getGameStateValue("keepersLimit");

    // multiple active state, can't use getCurrentPlayerId here!
    $players = $game->loadPlayersBasicInfos();
    $playersInfraction = [];

    foreach ($players as $player_id => $player) {
      $keepersInPlay = $game->cards->countCardInLocation("keepers", $player_id);
      $playersInfraction[$player_id] = [
        "count" => $keepersInPlay - $keepersLimit,
      ];
    }

    return [
      "limit" => $keepersLimit,
      "_private" => $playersInfraction,
    ];
  }

  public function arg_enforceKeepersLimitForSelf()
  {
    $game = Utils::getGame();

    $keepersLimit = $game->getGameStateValue("keepersLimit");

    $player_id = $game->getActivePlayerId();
    $keepersInPlay = $game->cards->countCardInLocation("keepers", $player_id);

    $playersInfraction = [
      "active" => [
        "count" => $keepersInPlay - $keepersLimit,
      ],
    ];

    return [
      "limit" => $keepersLimit,
      "_private" => $playersInfraction,
    ];
  }

  /*
   * Player discards a nr of cards for keeper limit
   */
  function action_discardKeepers($cards_id)
  {
    $game = Utils::getGame();

    // multiple active state, so don't use checkAction or getActivePlayerId here!
    $game->gamestate->checkPossibleAction("discardKeepers");
    $player_id = $game->getCurrentPlayerId();

    $keepersLimit = $game->getGameStateValue("keepersLimit");
    $keepersInPlay = $game->cards->countCardInLocation("keepers", $player_id);
    $expectedCount = $keepersInPlay - $keepersLimit;
    if (count($cards_id) != $expectedCount) {
      throw new BgaUserException(
        $game->_("Wrong number of cards. Expected: ") . $expectedCount
      );
    }

    $cards = [];
    foreach ($cards_id as $card_id) {
      // Verify card was in player hand
      $card = $game->cards->getCard($card_id);
      if (
        $card == null ||
        $card["location"] != "keepers" ||
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

    $game->notifyAllPlayers("keepersDiscarded", "", [
      "player_id" => $player_id,
      "cards" => $cards,
      "discardCount" => $game->cards->countCardInLocation("discard"),
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
