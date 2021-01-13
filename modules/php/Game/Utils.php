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

  public static function isPartyInPlay()
  {
    $party_keeper_card_id = 16;
    $party_keeper_card = self::getGame()->cards->getCard($party_keeper_card_id);
    return $party_keeper_card["location"] == "keepers";
  }

  private static function getAllPlayersKeeperCount()
  {
    // We cannot just use "countCardsByLocationArgs" here because it doesn't return
    // any value for players without keepers
    $players = Utils::getGame()->loadPlayersBasicInfos();
    $cards = Utils::getGame()->cards;

    $keeperCounts = [];
    foreach ($players as $player_id => $player) {
      // Count each player keepers
      $nbKeepers = $cards->countCardInLocation("keepers", $player_id);
      $keeperCounts[$player_id] = $nbKeepers;
    }

    return $keeperCounts;
  }

  public static function hasLeastKeepers($active_player_id)
  {
    $keepersCounts = self::getAllPlayersKeeperCount();

    $activeKeepersCount = $keepersCounts[$active_player_id];

    unset($keepersCounts[$active_player_id]);

    // no ties, only 1 player should have the least
    foreach ($keepersCounts as $player_id => $keepersCount) {
      if ($keepersCount <= $activeKeepersCount) {
        return false;
      }
    }
    return true;
  }

  public static function hasMostKeepers($active_player_id)
  {
    $keepersCounts = self::getAllPlayersKeeperCount();

    $activeKeepersCount = $keepersCounts[$active_player_id];

    unset($keepersCounts[$active_player_id]);

    // no ties, only 1 player should have the most
    foreach ($keepersCounts as $player_id => $keepersCount) {
      if ($keepersCount >= $activeKeepersCount) {
        return false;
      }
    }
    return true;
  }
}
