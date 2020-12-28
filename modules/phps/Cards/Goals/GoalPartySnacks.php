<?php
namespace Fluxx\Cards\Goals;

use Fluxx\Game\Utils;

class GoalPartySnacks extends GoalTwoKeepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("The Brain (No TV)");
    $this->subtitle = clienttranslate("The Party + at least 1 food Keeper");

    $this->keeper1 = 16;
    $this->food_keepers = [3, 4, 5, 6];
  }

  public function goalReachedByPlayer()
  {
    $i = 0;
    $winner_id = null;
    while ($i < count($this->food_keepers) && $winner_id == null) {
      $winner_id = $this->checkTwoKeepersWin(
        $this->keeper1,
        $this->food_keepers[$i]
      );
      $i++;
    }

    return $winner_id;
  }
}
