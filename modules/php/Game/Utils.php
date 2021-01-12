<?php
namespace Fluxx\Game;
use fluxx;

class Utils
{
  public static function getGame()
  {
    return fluxx::get();
  }

  public static function throwInvalidUserAction($msg)
  {
    throw new \BgaUserException($msg);
  }

  public static useCreeperPackExpansion()
  {
    return 1 == self::getGame()->getGameStateValue("optionCreeperPack");
  }
}
