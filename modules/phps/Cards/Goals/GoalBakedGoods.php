<?php
namespace Fluxx\Cards\Goals;

use Fluxx\Game\Utils;

class GoalBakedGoods extends GoalTwoKeepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Baked Goods");
    $this->subtitle = clienttranslate("Bread + Cookies");

    $this->keeper1 = 3;
    $this->keeper2 = 5;
  }
}
