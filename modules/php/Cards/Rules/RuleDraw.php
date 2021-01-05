<?php
namespace Fluxx\Cards\Rules;

use Fluxx\Game\Utils;
/*
 * RuleDraw: base class for all Rule cards that adapt the current Draw rule
 */
class RuleDraw extends RuleCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);
  }

  public function getRuleType()
  {
    return "drawRule";
  }

  protected $drawCount;

  public function immediateEffectOnPlay($player)
  {
    // current Draw Rule is changed immediately
    $oldValue = Utils::getGame()->getGameStateValue("drawRule");
    // discard any other draw rules
    Utils::getGame()->discardRuleCardsForType("drawRule");
    // set new draw rule
    Utils::getGame()->setGameStateValue("drawRule", $this->drawCount);
    // draw extra cards for the difference
    if ($this->drawCount - $oldValue > 0) {
      Utils::getGame()->performDrawCards($player, $this->drawCount - $oldValue);
      Utils::getGame()->setGameStateValue("drawnCards", $this->drawCount);
    }
  }

  public function immediateEffectOnDiscard($player)
  {
    // reset to Basic Draw Rule
    Utils::getGame()->setGameStateValue("drawRule", 1);
  }
}
