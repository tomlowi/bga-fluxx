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

  public static function hasActiveDoubleAgenda()
  {
    return 0 != self::getGame()->getGameStateValue("hasDoubleAgenda");
  }

  public static function getActiveInflation()
  {
    return self::getGame()->getGameStateValue("activeInflation");
  }

  public static function getActiveNoHandBonus()
  {
    return self::getGame()->getGameStateValue("activeNoHandBonus");
  }

  public static function getActivePartyBonus()
  {
    return self::getGame()->getGameStateValue("activePartyBonus");
  }

  public static function getActivePoorBonus()
  {
    return self::getGame()->getGameStateValue("activePoorBonus");
  }

  public static function getActiveRichBonus()
  {
    return self::getGame()->getGameStateValue("activeRichBonus");
  }

  public static function getActiveFirstPlayRandom()
  {
    return self::getGame()->getGameStateValue("activeFirstPlayRandom");
  }

}
