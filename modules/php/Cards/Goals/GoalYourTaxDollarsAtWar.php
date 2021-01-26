<?php
namespace Fluxx\Cards\Goals;

use Fluxx\Game\Utils;

class GoalYourTaxDollarsAtWar extends GoalTwoCreepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Your Tax Dollars at War");
    $this->subtitle = clienttranslate("Taxes + War");

    $this->creeper1 = 52;
    $this->creeper2 = 51;
  }
}
