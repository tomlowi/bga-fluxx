<?php
namespace Fluxx\Cards\Actions;

use Fluxx\Game\Utils;

class ActionRandomTax extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Random Tax");
    $this->description = clienttranslate(
      "Take 1 card at random from the hand of each other player and add these cards to your own hand."
    );
  }

  public function immediateEffectOnPlay($player)
  {
    $game = Utils::getGame();
    $players_ordered = $game->getPlayersInOrder();
    $to_player = $player;
    for ($i = 1; $i <= count($players_ordered); $i++) {
      $from_player = $players_ordered[$i - 1];
      if ($from_player != $to_player) {
        $handCards = $game->cards->getCardsInLocation("hand", $from_player);
        if (count($handCards) > 0) {
          shuffle($handCards);
          $game->cards->moveCard($handCards[0]["id"], "hand", $to_player);
        }
      }
    }
  }
}
