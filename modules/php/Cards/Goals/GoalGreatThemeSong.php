<?php
namespace Fluxx\Cards\Goals;

use Fluxx\Game\Utils;

class GoalGreatThemeSong extends GoalTwoKeepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Great Theme Song");
    $this->subtitle = clienttranslate("Music + Television");

    $this->keeper1 = 15;
    $this->keeper2 = 12;
  }
}
