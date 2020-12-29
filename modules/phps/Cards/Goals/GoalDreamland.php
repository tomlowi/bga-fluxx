<?php
namespace Fluxx\Cards\Goals;

use Fluxx\Game\Utils;

class GoalDreamland extends GoalTwoKeepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Dreamland");
    $this->subtitle = clienttranslate("Sleep + Dreams");

    $this->keeper1 = 1;
    $this->keeper2 = 14;
  }
}
