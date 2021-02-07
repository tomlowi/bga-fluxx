<?php
namespace Fluxx\Cards\Goals;

use Fluxx\Game\Utils;

class Goal10CardsInHand extends GoalCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("10 Cards in Hand");
    $this->description = clienttranslate(
      "If someone has 10 or more cards in his or her hand, then the player with the most cards in hand wins. In the event of a tie, continue playing until a clear winner emerges."
    );
  }

  public function goalReachedByPlayer()
  {
    // If someone has 10 or more cards in his or her hand,
    // then the player with the most cards in hand wins.
    // In the event of a tie, continue playing until a clear winner emerges.
    $players = Utils::getGame()->loadPlayersBasicInfos();
    $cards = Utils::getGame()->cards;

    $maxCards = 10;

    $addInflation = Utils::getActiveInflation() ? 1 : 0;
    $maxCards += $addInflation;

    $cardCounts = [];
    foreach ($players as $player_id => $player) {
      // Ruling from the designer: any player with creepers in play cannot win this goal,
      // and if several players are tied for max then only one without creepers can win
      // (and should win if all other have creepers in play)
      if ($this->isWinPreventedByCreepers($player_id, $this)) {
        continue;
      }

      // Count each player hand cards
      $nbCards = $cards->countCardInLocation("hand", $player_id);
      // this player has max (and more than 10)
      if ($nbCards > $maxCards) {
        // and they have more than the current highest
        $cardCounts = [$player_id]; // reset so only highest remains
        $maxCards = $nbCards;
      } elseif ($nbCards == $maxCards) {
        // there is a tie (for now)
        $cardCounts[] = $player_id;
      }
    }

    if (count($cardCounts) == 1) {
      // We have one winner, no tie
      $winner_id = $cardCounts[0];
      return $winner_id;
    }

    // no player has 10 or more hand cards, or it is a tie => no winner
    return null;
  }
}
