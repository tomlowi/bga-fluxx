<?php
namespace Fluxx\Cards\Creepers;

use Fluxx\Game\Utils;

class CreeperWar extends CreeperCard
{
    public function __construct($cardId, $uniqueId)
    {
        parent::__construct($cardId, $uniqueId);

        $this->name = clienttranslate("War");
        $this->subtitle = clienttranslate("Place Immediately + Redraw");
        $this->description = clienttranslate(
            "You cannot win if you have this, unless the Goal says otherwise. If you have Peace, you must move it to another player."
        );
    }

    public function preventsWinForGoal($goalCard)
    {
        $requiredForGoals = [151, 156];
        // War is required to win with these specific goals:
        // War is Death (151), Your Tax Dollars at War (156)
        if (in_array($goalCard->getUniqueId(), $requiredForGoals))
            return false;

        return parent::preventsWinForGoal($goalCard);
    }

    public $interactionNeeded = "playerSelection";

    public function onCheckResolveKeepersAndCreepers()
    {
        // TODO : check if also Peace in same hand
        // => activate this player to resolve War
        return null;
    }

    public function resolvedBy($player_id, $args)
    {
        // TODO: args should contain other player id
        // > move Peace keeper to that player        
        return null;
    }    
}