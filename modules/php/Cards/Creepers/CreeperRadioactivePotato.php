<?php
namespace Fluxx\Cards\Creepers;

use Fluxx\Game\Utils;

class CreeperRadioactivePotato extends CreeperCard
{
    public function __construct($cardId, $uniqueId)
    {
        parent::__construct($cardId, $uniqueId);

        $this->name = clienttranslate("Radioactive Potato");
        $this->subtitle = clienttranslate("Place Immediately + Redraw");
        $this->description = clienttranslate(
            "You cannot win if you have this card. Any time the Goal changes, move this card in the counter-turn direction."
        );
    }

    public function preventsWinForGoal($goalCard) {
        return parent::preventsWinForGoal($goalCard);
    }
}