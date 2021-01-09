<?php
namespace Fluxx\Cards\Rules;

use Fluxx\Game\Utils;

class RuleNoHandBonus extends RuleCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("No-Hand Bonus");
    $this->subtitle = clienttranslate("Start-of-Turn Event");
    $this->description = clienttranslate(
      "If empty handed, draw 3 cards before observing the current draw rule."
    );
  }

  public function immediateEffectOnPlay($player)
  {
    Utils::getGame()->setGameStateValue("activeNoHandBonus", 1);
  }

  public function immediateEffectOnDiscard($player)
  {
    Utils::getGame()->setGameStateValue("activeNoHandBonus", 0);
  }
}
