<?php
namespace Fluxx\Cards\Goals;

use Fluxx\Game\Utils;

class GoalDayDreams extends GoalTwoKeepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Day Dreams");
    $this->subtitle = clienttranslate("The Sun + Dreams");

    $this->keeper1 = 17;
    $this->keeper2 = 14;
  }
}
