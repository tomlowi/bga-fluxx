<?php
namespace Fluxx\Cards\Goals;

use Fluxx\Game\Utils;

class GoalBedTime extends GoalTwoKeepers
{
    public function __construct($cardId, $uniqueId)
    {
        parent::__construct($cardId, $uniqueId);

        $this->name = clienttranslate("Bed Time");
        $this->subtitle = clienttranslate("Sleep + Time");

        $this->keeper1 = 1;
        $this->keeper2 = 13;
    }
}
