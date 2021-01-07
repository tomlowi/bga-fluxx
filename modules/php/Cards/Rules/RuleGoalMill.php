<?php
namespace Fluxx\Cards\Rules;

use Fluxx\Game\Utils;

class RuleGoalMill extends RuleCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $canBeUsedByPlayer = true;

    $this->name = clienttranslate("Goal Mill");
    $this->subtitle = clienttranslate("Free Action");
    $this->description = clienttranslate(
      "Once during your turn, discard as many of your Goal cards as you choose, then draw that many cards."
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
