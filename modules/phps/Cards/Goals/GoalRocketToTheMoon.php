<?php
namespace Fluxx\Cards\Goals;

use Fluxx\Game\Utils;

class GoalRocketToTheMoon extends GoalTwoKeepers
{
    public function __construct($cardId, $uniqueId)
    {
        parent::__construct($cardId, $uniqueId);

        $this->name = clienttranslate("Rocket to the Moon");
        $this->subtitle = clienttranslate("The Rocket + The Moon");

        $this->keeper1 = 10;
        $this->keeper2 = 9;
    }
}
