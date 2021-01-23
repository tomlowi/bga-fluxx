<?php
namespace Fluxx\Cards\Rules;

use Fluxx\Cards\Card;
use Fluxx\Game\Utils;
/*
 * RuleCard: base class to handle new rule cards
 */
class RuleCard extends Card
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);
  }

  public function getRuleType()
  {
    return "others";
  }

  // Indicates this Rule effect can be used during client-side player turns
  public function canBeUsedInPlayerTurn($player_id)
  {
    return false;
  }

  // Indicates which interaction is expected by this Free Rule
  // null indicated that this can be handled without client-side interaction
  public $interactionNeeded = null;

  // Implements the immediate effect when this rule is played
  public function immediateEffectOnPlay($player_id)
  {
    if ($this->interactionNeeded != null) {
      Utils::getGame()->setGameStateValue(
        "freeRuleToResolve",
        $this->getCardId()
      );
      return "resolveFreeRule";
    }
    return null;
  }
}
