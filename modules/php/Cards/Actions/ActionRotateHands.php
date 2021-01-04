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

  public function needsInteraction()
  {
    return true;
  }

  public function immediateEffectOnPlay($player)
  {
    // nothing now, needs to go to resolve action state
  }

  public function resolvedBy($player, $option, $cardIdsSelected)
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
}
