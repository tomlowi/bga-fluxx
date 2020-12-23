<?php

namespace Fluxx\Cards\ActionCards;

/*
 * ActionCardFactory: how to create Action Cards 
 */
class ActionCardFactory extends CardFactory
{
    public function getCardFullClassName()	 { 
        $name = "Fluxx\Cards\ActionCards\\".self::$classes[$unique_id];
    }

    /*
	 * cardClasses : for each card Id, the corresponding class name
	 */
	public static $classes = [
        501 => 'ActionTrashKeeper',
        502 => 'ActionDummy',
        503 => 'ActionDummy',
        504 => 'ActionDummy',
        505 => 'ActionDummy',
        506 => 'ActionDummy',
        507 => 'ActionDummy',
        508 => 'ActionDummy',
        509 => 'ActionDummy',
        510 => 'ActionDummy',
        511 => 'ActionDummy',
        512 => 'ActionDummy',
        513 => 'ActionDummy',
        514 => 'ActionDummy',
        515 => 'ActionJackpot',
        516 => 'ActionDummy',
        517 => 'ActionDummy',
        518 => 'ActionDummy',
        519 => 'ActionDummy',
        520 => 'ActionDummy',
        521 => 'ActionDummy',
        522 => 'ActionDummy',
        523 => 'ActionDummy'

    ];    
    
}