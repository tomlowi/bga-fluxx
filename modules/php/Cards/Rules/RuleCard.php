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
  public $canBeUsedByPlayer = false;
}
