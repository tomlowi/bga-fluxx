<?php
namespace Fluxx\Cards\Rules;

use Fluxx\Game\Utils;

class RuleSilverLining extends RuleCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->set = "creeperpack";
    $this->name = clienttranslate("Silver Lining");
    $this->subtitle = clienttranslate("Takes Instant Effect");
    $this->description = clienttranslate(
      "Creepers do not prevent you from winning, except when a Goal explicitly forbids a specific Creeper."
    );
  }

  public function immediateEffectOnPlay($player)
  {
    Utils::getGame()->setGameStateValue("activeSilverLining", 1);
  }

  public function immediateEffectOnDiscard($player)
  {
    Utils::getGame()->setGameStateValue("activeSilverLining", 0);
  }
}
