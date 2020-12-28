<?php
namespace Fluxx\Cards\Goals;

use Fluxx\Game\Utils;

class GoalPartyTime extends GoalTwoKeepers
{
    public function __construct($cardId, $uniqueId)
    {
        parent::__construct($cardId, $uniqueId);

        $this->name = clienttranslate("Party Time");
        $this->subtitle = clienttranslate("The Party + Time");

        $this->keeper1 = 16;
        $this->keeper2 = 13;
    }
}
