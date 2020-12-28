<?php
namespace Fluxx\Cards\Goals;

use Fluxx\Game\Utils;

class GoalTheMindsEye extends GoalTwoKeepers
{
    public function __construct($cardId, $uniqueId)
    {
        parent::__construct($cardId, $uniqueId);

        $this->name = clienttranslate("The Mind’s Eye");
        $this->subtitle = clienttranslate("The Brain + The Eye");

        $this->keeper1 = 2;
        $this->keeper2 = 8;
    }
}
