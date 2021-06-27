<?php
namespace Fluxx\Cards\Actions;

use Fluxx\Game\Utils;

class ActionJackpot extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Jackpot!");
    $this->description = clienttranslate("Draw 3 extra cards!");
  }

  public function immediateEffectOnPlay($player_id)
  {
    $game = Utils::getGame();
    
    $addInflation = Utils::getActiveInflation() ? 1 : 0;
    $extraCards = 3 + $addInflation;

    // make sure we can't draw back this card itself (after reshuffle if deck would be empty)
    $game->cards->moveCard($this->getCardId(), "side", $player_id);

    Utils::getGame()->performDrawCards($player_id, $extraCards);

    // move this card itself back to the discard pile
    $game->cards->playCard($this->getCardId());
  }
}
