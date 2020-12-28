<?php
namespace Fluxx\Cards\Goals;

use Fluxx\Game\Utils;

class GoalWinningTheLottery extends GoalTwoKeepers
{
    public function __construct($cardId, $uniqueId)
    {
        parent::__construct($cardId, $uniqueId);

        $this->name = clienttranslate("Winning the Lottery");
        $this->subtitle = clienttranslate("Dreams + Money");

        $this->keeper1 = 14;
        $this->keeper2 = 7;
    }
}
