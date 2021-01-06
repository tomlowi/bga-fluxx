<?php
namespace Fluxx\Cards\Goals;

use Fluxx\Cards\Card;
use Fluxx\Game\Utils;
/*
 * GoalCard: base class to handle new goal cards
 */
class GoalCard extends Card
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);
  }

  /* check if this goal is reached by a single player, and return the winner player id */
  public function goalReachedByPlayer()
  {
    return null;
  }
}
