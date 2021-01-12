<?php
namespace Fluxx\Cards\Goals;

use Fluxx\Game\Utils;

class GoalYourTaxDollarsAtWar extends GoalTwoKeepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Your Tax Dollars at War");
    $this->subtitle = clienttranslate("Taxes + War");

    $this->keeper1 = 52;
    $this->keeper2 = 51;
  }
}
