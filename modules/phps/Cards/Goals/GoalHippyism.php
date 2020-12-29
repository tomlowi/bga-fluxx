<?php
namespace Fluxx\Cards\Goals;

use Fluxx\Game\Utils;

class GoalHippyism extends GoalTwoKeepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Hippyism");
    $this->subtitle = clienttranslate("Peace + Love");

    $this->keeper1 = 19;
    $this->keeper2 = 18;
  }
}
