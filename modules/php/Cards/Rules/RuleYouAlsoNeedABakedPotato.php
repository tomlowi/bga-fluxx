<?php
namespace Fluxx\Cards\Rules;

use Fluxx\Game\Utils;

class RuleYouAlsoNeedABakedPotato extends RuleCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("You Also Need a Baked Potato");
    $this->subtitle = clienttranslate("Takes Instant Effect");
    $this->description = clienttranslate(
      "If the Radioactive Potato is in play, it does not prevent victory - instead, you must have the Potato in order to win, along with meeting the other conditions of the Goal."
    );
  }

  public function immediateEffectOnPlay($player)
  {
    // @TODO : set game state?
  }

  public function immediateEffectOnDiscard($player)
  {
    // @TODO : unset game state?
  }
}
