<?php
namespace Fluxx\Cards\ActionCards;

use Fluxx\Game\Utils;

class ActionShareTheWealth extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Share the Wealth");
    $this->description = clienttranslate(
      "Gather up all the Keepers on the table, shuffle them together, and deal them back out to all players, starting with yourself. These go immediately into play in front of their new owners. Everyone will probably end up with a different number of Keepers in play than they started with."
    );
  }

  public function needsInteraction()
  {
    return false;
  }

  public function immediateEffectOnPlay($player)
  {
    $game = Utils::getGame();

    $keepersInPlay = $game->cards->getCardsInLocation("keepers");
    $players_ordered = $game->getPlayersInOrder();
    $playerCount = count($players_ordered);

    // gather and shuffle all keepers in play
    shuffle($keepersInPlay);

    // deal them back out, starting with the current player
    $receivingPlayerIndex = 1;
    foreach ($keepersInPlay as $cardId => $card) {
      $game->cards->moveCard(
        $cardId,
        "keepers",
        $players_ordered[$receivingPlayer - 1]
      );

      $receivingPlayerIndex++;
      if ($receivingPlayerIndex > $playerCount) {
        $receivingPlayerIndex = 1;
      }
    }
  }
}
