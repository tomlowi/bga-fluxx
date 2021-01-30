<?php
namespace Fluxx\Cards\Creepers;

use Fluxx\Game\Utils;

class CreeperTaxes extends CreeperCard
{
    public function __construct($cardId, $uniqueId)
    {
        parent::__construct($cardId, $uniqueId);

        $this->name = clienttranslate("Taxes");
        $this->subtitle = clienttranslate("Place Immediately + Redraw");
        $this->description = clienttranslate(
            "You cannot win if you have this, unless the Goal says otherwise. If you have Money in play, you can discard it and this."
        );
    }

    public function preventsWinForGoal($goalCard)
    {
        $requiredForGoals = [152, 156];
        // Taxes is required to win with these specific goals:
        // All That is Certain (152), Your Tax Dollars at War (156)
        if (in_array($goalCard->getUniqueId(), $requiredForGoals))
            return false;

        return parent::preventsWinForGoal($goalCard);
    }
}