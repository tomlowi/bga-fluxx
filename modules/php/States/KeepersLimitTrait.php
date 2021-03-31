<?php
namespace Fluxx\States;

use Fluxx\Game\Utils;

trait KeepersLimitTrait
{
  private function getKeepersLimit()
  {
    return self::getGameStateValue("keepersLimit");
  }

  private function getKeepersInfractions($players_id = null)
  {
    $keepersLimit = $this->getKeepersLimit();

    // no active Keeper Limit, nothing to do
    if ($keepersLimit < 0) {
      return [];
    }

    $addInflation = Utils::getActiveInflation() ? 1 : 0;
    $keepersLimit += $addInflation;

    if ($players_id == null) {
      $players_id = array_keys(self::loadPlayersBasicInfos());
    }
    $playersInfraction = [];

    $cards = Utils::getGame()->cards;

    foreach ($players_id as $player_id) {
      $keepersInPlay = count(
        $cards->getCardsOfTypeInLocation("keeper", null, "keepers", $player_id)
      );
      if ($keepersInPlay > $keepersLimit) {
        $playersInfraction[$player_id] = [
          "discardCount" => $keepersInPlay - $keepersLimit,
        ];
      }
    }

    return $playersInfraction;
  }

  public function st_enforceKeepersLimitForOthers()
  {
    $playersInfraction = $this->getKeepersInfractions();

    // The keepers limit doesn't apply to the active player.
    $active_player_id = self::getActivePlayerId();

    if (array_key_exists($active_player_id, $playersInfraction)) {
      unset($playersInfraction[$active_player_id]);
    }

    $gamestate = Utils::getGame()->gamestate;

    // Activate all players that need to remove keepers (if any)
    $gamestate->setPlayersMultiactive(array_keys($playersInfraction), "", true);
  }

  public function st_enforceKeepersLimitForSelf()
  {
    $player_id = self::getActivePlayerId();
    $playersInfraction = $this->getKeepersInfractions([$player_id]);

    $gamestate = Utils::getGame()->gamestate;

    if (count($playersInfraction) == 0) {
      // Player is not in the infraction with the rule
      $gamestate->nextstate("");
      return;
    }
  }

  public function arg_enforceKeepersLimitForOthers()
  {
    $warnInflation = Utils::getActiveInflation() 
      ? clienttranslate('<span class="flx-warn-inflation">(+1 Inflation)</span>')
      : "";

    return [
      "i18n" => ["warnInflation"],
      "limit" => $this->getKeepersLimit(),
      "warnInflation" => $warnInflation,
      "_private" => $this->getKeepersInfractions(),
    ];
  }

  public function arg_enforceKeepersLimitForSelf()
  {
    $warnInflation = Utils::getActiveInflation() 
      ? clienttranslate('<span class="flx-warn-inflation">(+1 Inflation)</span>')
      : "";
    
    $player_id = self::getActivePlayerId();
    $playersInfraction = $this->getKeepersInfractions([$player_id]);

    return [
      "i18n" => ["warnInflation"],
      "limit" => $this->getKeepersLimit(),
      "warnInflation" => $warnInflation,
      "_private" => [
        "active" => $playersInfraction[$player_id] ?? ["discardCount" => 0],
      ],
    ];
  }

  /*
   * Player discards a nr of cards for keeper limit
   */
  function action_discardKeepers($cards_id)
  {
    $game = Utils::getGame();

    // possible multiple active state, so use currentPlayer rather than activePlayer
    $game->gamestate->checkPossibleAction("discardKeepers");
    $player_id = self::getCurrentPlayerId();

    $playersInfraction = $this->getKeepersInfractions([$player_id]);
    $expectedCount = $playersInfraction[$player_id]["discardCount"];
    if (count($cards_id) != $expectedCount) {
      Utils::throwInvalidUserAction(
        fluxx::totranslate("Wrong number of cards. Expected: ") . $expectedCount
      );
    }

    // verify these are all actually keeper cards in hand of player

    $cards = self::discardCardsFromLocation(
      $cards_id,
      "keepers",
      $player_id,
      "keeper"
    );

    self::notifyAllPlayers("keepersDiscarded", "", [
      "player_id" => $player_id,
      "cards" => $cards,
      "discardCount" => $game->cards->countCardInLocation("discard"),
      "creeperCount" => Utils::getPlayerCreeperCount($player_id),
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
