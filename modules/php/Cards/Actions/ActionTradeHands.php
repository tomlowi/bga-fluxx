<?php
namespace Fluxx\Cards\Actions;

use Fluxx\Game\Utils;
use fluxx;

class ActionTradeHands extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Trade Hands");
    $this->description = clienttranslate(
      "Trade your hand for the hand of one of your opponents. This is one of those times when you can get something for nothing!"
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
    // options: index or id of the player chosen ?
    // @TODO: TradeHands with selected player - for now simply 1/2/3
    $players_ordered = $game->getPlayersInOrder();
    $playerVictim = $players_ordered[max($option, count($players_ordered) - 1)];

    $game = Utils::getGame();
    // move current player cards to temporary hand
    $tempHand = -1;
    $game->cards->moveAllCardsInLocation("hand", "hand", $player, $tempHand);
    // now shift victim's hand to current player
    $game->cards->moveAllCardsInLocation(
      "hand",
      "hand",
      $playerVictim,
      $player
    );
    // finally move 1st player temp hand to victim player
    $game->cards->moveAllCardsInLocation(
      "hand",
      "hand",
      $tempHand,
      $playerVictim
    );
  }
}
