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

  public static function getActiveInflation()
  {
    return self::getGame()->getGameStateValue("activeInflation");
  }

  public static function hasActiveDoubleAgenda()
  {
    return 0 != self::getGame()->getGameStateValue("hasDoubleAgenda");
  }
}
