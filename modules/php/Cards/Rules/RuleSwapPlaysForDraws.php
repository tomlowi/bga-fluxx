<?php
namespace Fluxx\Cards\Rules;

use Fluxx\Game\Utils;

class RuleSwapPlaysForDraws extends RuleCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $canBeUsedByPlayer = true;

    $this->name = clienttranslate("Swap Plays for Draws");
    $this->subtitle = clienttranslate("Takes Instant Effect");
    $this->description = clienttranslate(
      "During your yurn, you may decide to play no more cards and instead draw as many cards as you have plays remaining. If Play All, draw as many cards as you hold."
    );
  }

  public function immediateEffectOnPlay($player)
  {
    // @TODO
  }

  public function immediateEffectOnDiscard($player)
  {
    // nothing
  }
}
