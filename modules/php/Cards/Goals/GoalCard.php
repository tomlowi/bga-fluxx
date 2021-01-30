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

  public function isWinPreventedByCreepers($player_id)
  {
    // @TODO: get creepers for player
    // @TODO: check Silver Lining active
    // @TODO: check Baked Potato active & Radioactive Potato in play somewhere
    // @TODO: override in special Creeper Goals for specific Creepers
    return false;
  }
}
