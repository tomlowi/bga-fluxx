<?php
namespace Fluxx\Cards\Rules;

use Fluxx\Game\Utils;

class RuleDoubleAgenda extends RuleCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Double Agenda");
    $this->subtitle = clienttranslate("Takes Instant Effect");
    $this->description = clienttranslate(
      "A second Goal can now be played. After this, whoever plays a new Goal must choose which of the current Goals to discard. You win if you satisfy either Goal."
    );
  }

  public function immediateEffectOnPlay($player)
  {
    Utils::getGame()->setGameStateValue("hasDoubleAgenda", 1);
  }

  public function immediateEffectOnDiscard($player)
  {
    Utils::getGame()->setGameStateValue("hasDoubleAgenda", 0);
  }
}
