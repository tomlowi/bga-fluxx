<?php
namespace Fluxx\Cards\Goals;

use Fluxx\Game\Utils;

class GoalEyeOfTheBeholder extends GoalTwoKeepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("The Eye of the Beholder");
    $this->subtitle = clienttranslate("The Eye + Love");

    $this->keeper1 = 8;
    $this->keeper2 = 18;
  }
}
