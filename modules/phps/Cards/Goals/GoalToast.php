<?php
namespace Fluxx\Cards\Goals;

use Fluxx\Game\Utils;

class GoalToast extends GoalTwoKeepers
{
    public function __construct($cardId, $uniqueId)
    {
        parent::__construct($cardId, $uniqueId);

        $this->name = clienttranslate("Toast");
        $this->subtitle = clienttranslate("Bread + The Toaster");

        $this->keeper1 = 3;
        $this->keeper2 = 11;
    }
}
