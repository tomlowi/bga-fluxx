<?php
namespace Fluxx\Cards\Goals;

use Fluxx\Game\Utils;

class GoalTurnItUp extends GoalTwoKeepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Turn it Up!");
    $this->subtitle = clienttranslate("Music + The Party");

    $this->keeper1 = 15;
    $this->keeper2 = 16;
  }
}
