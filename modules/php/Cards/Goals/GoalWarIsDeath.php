<?php
namespace Fluxx\Cards\Goals;

use Fluxx\Game\Utils;

class GoalWarIsDeath extends GoalTwoKeepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("War = Death");
    $this->subtitle = clienttranslate("War + Death");

    $this->keeper1 = 51;
    $this->keeper2 = 53;
  }
}
