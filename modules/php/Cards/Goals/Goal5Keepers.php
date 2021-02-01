<?php
namespace Fluxx\Cards\Goals;

use Fluxx\Game\Utils;

class Goal5Keepers extends GoalCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("5 Keepers");
    $this->description = clienttranslate(
      "If someone has 5 or more Keepers on the table, then the player with the most Keepers in play wins. In the event of a tie, continue playing until a clear winner emerges."
    );
  }

  public function goalReachedByPlayer()
  {
    // If someone has 5 or more Keepers on the table,
    // then the player with the most Keepers in play wins.
    // In the event of a tie, continue playing until a clear winner emerges.
    $players = Utils::getGame()->loadPlayersBasicInfos();
    $cards = Utils::getGame()->cards;

    $maxkeepers = 5;

    $addInflation = Utils::getActiveInflation() ? 1 : 0;
    $maxkeepers += $addInflation;

    $keeperCounts = [];
    foreach ($players as $player_id => $player) {
      // Ruling from the designer: any player with creepers in play cannot win this goal,
      // and if several players are tied for max then only one without creepers can win
      // (and should win if all other have creepers in play)
      if ($this->isWinPreventedByCreepers($player_id, $this))
        continue;
      
      // Count each player keepers
      $nbKeepers 
        = count($cards->getCardsOfTypeInLocation("keeper", null, "keepers", $player_id));
      if ($nbKeepers > $maxkeepers) {
        $keeperCounts = [$player_id];
        $maxkeepers = $nbKeepers;
      } elseif ($nbKeepers == $maxkeepers) {
        $keeperCounts[] = $player_id;
      }
    }

    if (count($keeperCounts) == 1) {
      // We have one winner, no tie
      $winner_id = $keeperCounts[0];
      return $winner_id;
    }

    // no player has 5 or more keepers, or it is a tie => no winner
    return null;
  }
}
