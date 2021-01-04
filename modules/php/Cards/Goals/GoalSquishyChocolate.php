<?php
namespace Fluxx\Cards\Goals;

use Fluxx\Game\Utils;

class GoalSquishyChocolate extends GoalTwoKeepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Squishy Chocolate");
    $this->subtitle = clienttranslate("Chocolate + The Sun");

    $this->keeper1 = 4;
    $this->keeper2 = 17;
  }
}
