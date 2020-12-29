<?php
namespace Fluxx\Cards\Goals;

use Fluxx\Game\Utils;

class GoalChocolateMilk extends GoalTwoKeepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Chocolate Milk");
    $this->subtitle = clienttranslate("Chocolate + Milk");

    $this->keeper1 = 4;
    $this->keeper2 = 6;
  }
}
