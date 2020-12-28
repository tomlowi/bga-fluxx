<?php
namespace Fluxx\Cards\Goals;

use Fluxx\Game\Utils;

class GoalTheBrainNoTV extends GoalTwoKeepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("The Brain (No TV)");
    $this->description = clienttranslate(
      "If no one has Television on the table, the player with The Brain on the table wins."
    );

    $this->keeper1 = 2;
    $this->keeper2 = 12;
  }

  public function goalReachedByPlayer()
  {
    $winner_id = $this->checkTwoKeepersWin(
      $this->keeper1,
      $this->keeper2,
      true
    );

    return $winner_id;
  }
}
