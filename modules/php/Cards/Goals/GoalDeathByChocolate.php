<?php
namespace Fluxx\Cards\Goals;

use Fluxx\Game\Utils;

class GoalDeathByChocolate extends GoalTwoKeepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Death By Chocolate");
    $this->subtitle = clienttranslate("Death + Chocolate");

    $this->keeper1 = 53;
    $this->keeper2 = 4;
  }
}
