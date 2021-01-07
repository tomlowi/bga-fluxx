<?php
namespace Fluxx\Cards\Rules;

use Fluxx\Game\Utils;

class RuleMysteryPlay extends RuleCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $canBeUsedByPlayer = true;

    $this->name = clienttranslate("Mystery Play");
    $this->subtitle = clienttranslate("Free Action");
    $this->description = clienttranslate(
      "Once during your turn, you may take the top card from the draw pile and play it immediately."
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
