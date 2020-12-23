<?php

namespace Fluxx\Cards;


/*
 * CardFactory: how to create Cards 
 */
abstract class CardFactory extends \APP_GameClass
{
    public static function getCard($cardRow) {
		return self::resToObject($cardRow);
	}

    function getCardUniqueId($card) {
        return ($card['type'] * 100) + ($card['type_arg']-0);
    }

    // to be set by derived factories for specific Card types
    public function getCardFullClassName()	 { return null; }

    private static function resToObject($cardRow) {
		$unique_id = self::getCardUniqueId($cardRow);
		$name = getCardFullClassName($unique_id);
		$card = new $name($cardRow['id'], $unique_id);
		return $card;
    }
    
}