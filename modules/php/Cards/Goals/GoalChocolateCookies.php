<?php
namespace Fluxx\Cards\Goals;

use Fluxx\Game\Utils;

class GoalChocolateCookies extends GoalTwoKeepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Chocolate Cookies");
    $this->subtitle = clienttranslate("Chocolate + Cookies");

    $this->keeper1 = 4;
    $this->keeper2 = 5;
  }
}
