<?php
namespace Fluxx\Cards\Goals;

use Fluxx\Game\Utils;

class GoalBreadAndChocolate extends GoalTwoKeepers
{
    public function __construct($cardId, $uniqueId)
    {
        parent::__construct($cardId, $uniqueId);

        $this->name = clienttranslate("Bread & Chocolate");
        $this->subtitle = clienttranslate("Bread + Chocolate");

        $this->keeper1 = 3;
        $this->keeper2 = 4;
    }
}
