<?php
namespace Fluxx\Cards\Goals;

use Fluxx\Game\Utils;

class GoalAllThatIsCertain extends GoalTwoCreepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("All That Is Certain");
    $this->subtitle = clienttranslate("Death + Taxes");

    $this->creeper1 = 53;
    $this->creeper2 = 52;
  }
}
