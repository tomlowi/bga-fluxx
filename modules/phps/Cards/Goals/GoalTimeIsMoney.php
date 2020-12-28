<?php
namespace Fluxx\Cards\Goals;

use Fluxx\Game\Utils;

class GoalTimeIsMoney extends GoalTwoKeepers
{
    public function __construct($cardId, $uniqueId)
    {
        parent::__construct($cardId, $uniqueId);

        $this->name = clienttranslate("Time is Money");
        $this->subtitle = clienttranslate("Time + Money");

        $this->keeper1 = 13;
        $this->keeper2 = 7;
    }
}
