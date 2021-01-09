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

  public static function getActiveDoubleAgenda()
  {
    return 0 != self::getGame()->getGameStateValue("activeDoubleAgenda");
  }

  public static function getActiveInflation()
  {
    return 0 != self::getGame()->getGameStateValue("activeInflation");
  }

  public static function getActiveNoHandBonus()
  {
    return 0 != self::getGame()->getGameStateValue("activeNoHandBonus");
  }

  public static function getActivePartyBonus()
  {
    return 0 != self::getGame()->getGameStateValue("activePartyBonus");
  }

  public static function getActivePoorBonus()
  {
    return 0 != self::getGame()->getGameStateValue("activePoorBonus");
  }

  public static function getActiveRichBonus()
  {
    return 0 != self::getGame()->getGameStateValue("activeRichBonus");
  }

  public static function getActiveFirstPlayRandom()
  {
    return 0 != self::getGame()->getGameStateValue("activeFirstPlayRandom");
  }

}
