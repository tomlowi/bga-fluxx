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
    302 => "ActionTrashAKeeper",
    303 => "ActionTrashAKeeper",
    304 => "ActionTrashAKeeper",
    305 => "ActionTrashAKeeper",
    306 => "ActionTrashAKeeper",
    307 => "ActionTrashAKeeper",
    308 => "ActionTrashAKeeper",
    309 => "ActionTrashAKeeper",
    310 => "ActionTrashAKeeper",
    311 => "ActionTrashAKeeper",
    312 => "ActionTrashAKeeper",
    313 => "ActionTrashAKeeper",
    314 => "ActionTrashAKeeper",
    315 => "ActionJackpot",
    316 => "ActionTrashAKeeper",
    317 => "ActionTrashAKeeper",
    318 => "ActionTrashAKeeper",
    319 => "ActionTrashAKeeper",
    320 => "ActionTrashAKeeper",
    321 => "ActionTrashAKeeper",
    322 => "ActionTrashAKeeper",
    323 => "ActionTrashAKeeper",
  ];
}
