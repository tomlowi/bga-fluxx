<?php
namespace Fluxx\Cards\Goals;

use Fluxx\Game\Utils;

class GoalRocketScience extends GoalTwoKeepers
{
    public function __construct($cardId, $uniqueId)
    {
        parent::__construct($cardId, $uniqueId);

        $this->name = clienttranslate("Rocket Science");
        $this->subtitle = clienttranslate("The Rocket + The Brain");

        $this->keeper1 = 10;
        $this->keeper2 = 2;
    }
}
