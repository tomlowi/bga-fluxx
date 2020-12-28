<?php
namespace Fluxx\Cards\Goals;

use Fluxx\Game\Utils;

class GoalHeartsAndMinds extends GoalTwoKeepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Hearts & Minds");
    $this->subtitle = clienttranslate("Love + The Brain");

    $this->keeper1 = 18;
    $this->keeper2 = 2;
  }
}
