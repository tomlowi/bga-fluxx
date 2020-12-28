<?php
namespace Fluxx\Cards\Goals;

use Fluxx\Game\Utils;

class GoalLullaby extends GoalTwoKeepers
{
    public function __construct($cardId, $uniqueId)
    {
        parent::__construct($cardId, $uniqueId);

        $this->name = clienttranslate("Lullaby");
        $this->subtitle = clienttranslate("Sleep + Music");

        $this->keeper1 = 1;
        $this->keeper2 = 15;
    }
}
