<?php
namespace Fluxx\Cards\Goals;

use Fluxx\Game\Utils;

class GoalCantBuyMeLove extends GoalTwoKeepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Can’t Buy Me Love");
    $this->subtitle = clienttranslate("Money + Love");

    $this->keeper1 = 1;
    $this->keeper2 = 13;
  }
}
