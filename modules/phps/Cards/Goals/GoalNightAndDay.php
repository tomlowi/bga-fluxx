<?php
namespace Fluxx\Cards\Goals;

use Fluxx\Game\Utils;

class GoalNightAndDay extends GoalTwoKeepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Night & Day");
    $this->subtitle = clienttranslate("The Sun + The Moon");

    $this->keeper1 = 17;
    $this->keeper2 = 9;
  }
}
