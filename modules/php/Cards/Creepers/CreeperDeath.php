<?php
namespace Fluxx\Cards\Creepers;

use Fluxx\Game\Utils;

class CreeperDeath extends CreeperCard
{
    public function __construct($cardId, $uniqueId)
    {
        parent::__construct($cardId, $uniqueId);

        $this->name = clienttranslate("Death");
        $this->subtitle = clienttranslate("Place Immediately + Redraw");
        $this->description = clienttranslate(
            "You cannot win if you have this, unless the Goal says otherwise. If you have this at the start of your turn, discard something else you have in play (a Keeper or Creeper). You may discard this anytime it stands alone."
        );
    }

    public function preventsWinForGoal($goalCard)
    {
        $requiredForGoals = [151, 152, 153];
        // Death is required to win with these specific goals:
        // War is Death (151), All That is Certain (152), Death by Chocolate (153)
        if (in_array($goalCard->getUniqueId(), $requiredForGoals))
            return false;

        return parent::preventsWinForGoal($goalCard);
    }
}