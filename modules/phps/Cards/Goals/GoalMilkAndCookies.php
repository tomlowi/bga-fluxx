<?php
namespace Fluxx\Cards\Goals;

use Fluxx\Game\Utils;

class GoalMilkAndCookies extends GoalTwoKeepers
{
    public function __construct($cardId, $uniqueId)
    {
        parent::__construct($cardId, $uniqueId);

        $this->name = clienttranslate("Milk & Cookies");
        $this->subtitle = clienttranslate("Milk + Cookies");

        $this->keeper1 = 6;
        $this->keeper2 = 5;
    }
}
