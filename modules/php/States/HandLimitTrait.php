<?php
namespace Fluxx\States;

use Fluxx\Game\Utils;

trait HandLimitTrait
{
  private function getHandLimit()
  {
    return self::getGameStateValue("handLimit");
  }

  private function getHandInfractions($players_id = null)
  {
    $handLimit = $this->getHandLimit();

    // no active Hand Limit, nothing to do
    if ($handLimit < 0) {
      return [];
    }

    $addInflation = Utils::getActiveInflation() ? 1 : 0;
    $handLimit += $addInflation;

    if ($players_id == null) {
      $players_id = array_keys(self::loadPlayersBasicInfos());
    }
    $playersInfraction = [];

    $cards = Utils::getGame()->cards;

    foreach ($players_id as $player_id) {
      $handCount = $cards->countCardInLocation("hand", $player_id);
      if ($handCount > $handLimit) {
        $playersInfraction[$player_id] = [
          "count" => $handCount - $handLimit,
        ];
      }
    }

    return $playersInfraction;
  }

  public function st_enforceHandLimitForOthers()
  {
    $playersInfraction = $this->getHandInfractions();

    // The hand limit doesn't apply to the active player.
    $active_player_id = self::getActivePlayerId();

    if (array_key_exists($active_player_id, $playersInfraction)) {
      unset($playersInfraction[$active_player_id]);
    }

    $gamestate = Utils::getGame()->gamestate;

    // Activate all players that need to discard some cards (if any)
    $gamestate->setPlayersMultiactive(array_keys($playersInfraction), "", true);
  }

  public function st_enforceHandLimitForSelf()
  {
    $player_id = self::getActivePlayerId();
    $playersInfraction = $this->getHandInfractions([$player_id]);

    $gamestate = Utils::getGame()->gamestate;

    if (count($playersInfraction) == 0) {
      // Player is not in the infraction with the rule
      $gamestate->nextstate("");
      return;
    }
  }

  public function arg_enforceHandLimitForOthers()
  {
    return [
      "limit" => $this->getHandLimit(),
      "_private" => $this->getHandInfractions(),
    ];
  }

  public function arg_enforceHandLimitForSelf()
  {
    $player_id = self::getActivePlayerId();
    $playersInfraction = $this->getHandInfractions([$player_id]);

    $out = [
      "limit" => $this->getHandLimit(),
      "_private" => [
        "active" => $playersInfraction[$player_id] ?? ["count" => 0],
      ],
    ];

    return $out;
  }

  /*
   * Player discards a nr of cards for hand limit
   */
  function action_discardHandCards($cards_id)
  {
    $game = Utils::getGame();

    // possible multiple active state, so use currentPlayer rather than activePlayer
    $game->gamestate->checkPossibleAction("discardHandCards");
    $player_id = self::getCurrentPlayerId();

    $playersInfraction = $this->getHandInfractions([$player_id]);
    $expectedCount = $playersInfraction[$player_id]["count"];

    if (count($cards_id) != $expectedCount) {
      Utils::throwInvalidUserAction(
        fluxx::totranslate("Wrong number of cards. Expected: ") . $expectedCount
      );
    }

    $cards = self::discardCardsFromLocation(
      $cards_id,
      "hand",
      $player_id,
      null
    );

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
