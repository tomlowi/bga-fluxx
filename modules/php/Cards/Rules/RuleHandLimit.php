<?php
namespace Fluxx\Cards\Rules;

use Fluxx\Game\Utils;
/*
 * RuleHandLimit: base class for all Rule cards that adapt the current Hand Limit
 */
class RuleHandLimit extends RuleCard
{
  public function getRuleType()
  {
    return "handLimit";
  }

  protected $handLimit;

  public function immediateEffectOnPlay($player)
  {
    // Discard old hand limit card in play
    Utils::getGame()->discardRuleCardsForType("handLimit");
    // set new hand limit rule
    Utils::getGame()->setGameStateValue("handLimit", $this->handLimit);
  }

  public function playFromHand($player)
  {
    // Execute the immediate effect
    $this->immediateEffectOnPlay($player);

    return "handLimitRulePlayed";
  }

  public function immediateEffectOnDiscard($player)
  {
    // reset to Basic Hand Limit = none
    Utils::getGame()->setGameStateValue("handLimit", -1);
  }
}
