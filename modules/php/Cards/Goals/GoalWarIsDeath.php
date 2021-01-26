<?php
namespace Fluxx\Cards\Goals;

use Fluxx\Game\Utils;

class GoalWarIsDeath extends GoalTwoCreepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("War = Death");
    $this->subtitle = clienttranslate("War + Death");

    $this->creeper1 = 51;
    $this->creeper2 = 53;
  }
}
