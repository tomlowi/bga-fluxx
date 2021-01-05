<?php
namespace Fluxx\Cards\Goals;

use Fluxx\Game\Utils;

class GoalWorldPeace extends GoalTwoKeepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("World Peace");
    $this->subtitle = clienttranslate("Dreams + Peace");

    $this->keeper1 = 14;
    $this->keeper2 = 19;
  }
}
