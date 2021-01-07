<?php
namespace Fluxx\Cards\Rules;

use Fluxx\Game\Utils;

class RuleRecycling extends RuleCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $canBeUsedByPlayer = true;

    $this->name = clienttranslate("Recycling");
    $this->subtitle = clienttranslate("Free Action");
    $this->description = clienttranslate(
      "Once during your turn, you may discard one of your Keepers from the table and draw 3 extra cards."
    );
  }

  public function immediateEffectOnPlay($player)
  {
    // @TODO
  }

  public function immediateEffectOnDiscard($player)
  {
    // @TODO
  }
}
