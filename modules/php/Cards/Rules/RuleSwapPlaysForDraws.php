<?php
namespace Fluxx\Cards\Rules;

use Fluxx\Game\Utils;

class RuleSwapPlaysForDraws extends RuleCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Swap Plays for Draws");
    $this->subtitle = clienttranslate("Takes Instant Effect");
    $this->description = clienttranslate(
      "During your yurn, you may decide to play no more cards and instead draw as many cards as you have plays remaining. If Play All, draw as many cards as you hold."
    );
  }

  public function canBeUsedInPlayerTurn($player_id)
  {
    return true;
  }

  public function immediateEffectOnPlay($player)
  {
    // nothing
  }

  public function immediateEffectOnDiscard($player)
  {
    // nothing
  }

  public function freePlayInPlayerTurn($player_id)
  {
    // @TODO:
    // calculate how many cards player should still play
    // draw as many cards
    // force end of turn (set count cards played to 999)
    return null;
  }
}
