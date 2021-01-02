<?php

namespace Fluxx\Cards\ActionCards;
use Fluxx\Cards\CardFactory;
/*
 * ActionCardFactory: how to create Action Cards
 */
class ActionCardFactory extends CardFactory
{
  public static function getCardFullClassName($uniqueId)
  {
    $name = "Fluxx\Cards\ActionCards\\" . self::$classes[$uniqueId];
    return $name;
  }

  /*
   * cardClasses : for each card Id, the corresponding class name
   */
  public static $classes = [
    301 => "ActionTrashAKeeper",
    302 => "ActionRotateHands",
    303 => "ActionRulesReset",
    304 => "ActionRandomTax",
    305 => "ActionDummy", // @TODO: Rock-Paper-Scissors
    306 => "ActionRandomTax",
    307 => "ActionRandomTax",
    308 => "ActionRandomTax",
    309 => "ActionRandomTax",
    310 => "ActionRandomTax",
    311 => "ActionRandomTax",
    312 => "ActionRandomTax",
    313 => "ActionRandomTax",
    314 => "ActionRandomTax",
    315 => "ActionJackpot",
    316 => "ActionDummy",
    317 => "ActionDummy",
    318 => "ActionDummy",
    319 => "ActionDummy",
    320 => "ActionDummy",
    321 => "ActionDummy",
    322 => "ActionDummy",
    323 => "ActionDummy",
  ];
}
