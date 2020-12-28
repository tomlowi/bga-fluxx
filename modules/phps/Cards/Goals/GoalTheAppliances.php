<?php
namespace Fluxx\Cards\Goals;

use Fluxx\Game\Utils;

class GoalTheAppliances extends GoalTwoKeepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("The Appliances");
    $this->subtitle = clienttranslate("The Toaster + Television");

    $this->keeper1 = 11;
    $this->keeper2 = 12;
  }
}
