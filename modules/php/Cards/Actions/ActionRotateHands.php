<?php
namespace Fluxx\Cards\Actions;

use Fluxx\Game\Utils;
use fluxx;

class ActionRotateHands extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Rotate Hands");
    $this->description = clienttranslate(
      "All players pass their hands to the player next to them. You decide which direction."
    );
  }

  public $interactionNeeded = "direction";

  public function resolvedByBak($player, $option, $cardIdsSelected)
  {
    // options: 1 = Left, 2 = Right
    $game = Utils::getGame();

    $players = $game->loadPlayersBasicInfos();
    $players_ordered = $game->getPlayersInOrder();
    if ($option != 1) {
      // Rotate Right instead of Left
      $players_ordered = array_reverse($players_ordered);
    }

    $count_players = count($players_ordered);
    // move 1st player cards to temporary hand
    $tempHand = -1;
    $game->cards->moveAllCardsInLocation(
      "hand",
      "hand",
      $players_ordered[0],
      $tempHand
    );
    // now shift all hands in between to previous player
    for ($i = 1; $i < $count_players; $i++) {
      $to_player_id = $players_ordered[$i - 1];
      $from_player_id = $players_ordered[$i];

      $game->cards->moveAllCardsInLocation(
        "hand",
        "hand",
        $from_player_id,
        $to_player_id
      );
    }
    // finally move 1st player temp hand to last player
    $game->cards->moveAllCardsInLocation(
      "hand",
      "hand",
      $tempHand,
      $players_ordered[$count_players - 1]
    );
  }

  public function resolvedBy($active_player_id, $args)
  {
    // 0 = Left, 1 = Right
    $direction = $args["direction"];

    $game = Utils::getGame();

    $players = $game->loadPlayersBasicInfos();
    $player_name = $players[$active_player_id]["player_name"];

    $startingHands = [];

    foreach ($players as $player_id => $player) {
      $startingHands[$player_id] = $game->cards->getCardsInLocation(
        "hand",
        $player_id
      );
    }

    if ($direction == 0) {
      $directionTable = $game->getNextPlayerTable();
      $msg = clienttranslate('${player_name} rotated hands to the left');
    } else {
      $directionTable = $game->getPrevPlayerTable();
      $msg = clienttranslate('${player_name} rotated hands to the right');
    }

    foreach ($players as $player_id => $player) {
      $selected_player_id = $directionTable[$player_id];

      $newHand = $startingHands[$selected_player_id];

      $game->notifyPlayer($selected_player_id, "cardsSentToPlayer", "", [
        "cards" => $newHand,
        "player_id" => $player_id,
      ]);
      $game->notifyPlayer($player_id, "cardsReceivedFromPlayer", "", [
        "cards" => $newHand,
        "player_id" => $selected_player_id,
      ]);
      $game->cards->moveCards(array_keys($newHand), "hand", $player_id);
    }

    $game->notifyAllPlayers("actionDone", $msg, [
      "player_name" => $player_name,
    ]);

    $game->sendHandCountNotifications();

    return "handsExchangeOccured";
  }
}
