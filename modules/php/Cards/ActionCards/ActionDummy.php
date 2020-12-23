<?php
namespace Fluxx\Cards\ActionCards;

use Fluxx\Game\Utils;
/*
 * ActionDummy: for not yet implemented actions
 */
class ActionDummy extends ActionCard
{
    public function __construct($cardId, $uniqueId)
	{
        parent::__construct($cardId, $uniqueId);
    }

    public function needsInteraction()	 { return false; }

	public function immediateEffectOnPlay($player) { 
        
        Utils::getGame()::notifyAllPlayers( "actionNotImplemented", 
            clienttranslate( 'Action <b>${unique_id}<b> not yet implemented' ), [
            'unique_id' => $this->getUniqueId()
        ]) );

        return parent::immediateEffectOnPlay($player);
    }
}