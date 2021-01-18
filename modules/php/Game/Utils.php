<?php
namespace Fluxx\Game;
use Fluxx\Cards\Rules\RulePartyBonus;
use Fluxx\Cards\Rules\RulePoorBonus;
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

  public static function useCreeperPackExpansion()
  {
    return 1 == self::getGame()->getGameStateValue("optionCreeperPack");
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
    $party_keeper_card = array_values(
      self::getGame()->cards->getCardsOfType("keeper", $party_keeper_card_id)
    )[0];
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

  public static function playerHasNotYetPartiedInTurn()
  {
    // Party bonus can only be scored once by the same player in one turn.
    return 0 == Utils::getGame()->getGameStateValue("playerTurnUsedPartyBonus");
  }

  public static function playerHasNotYetBeenPoorInTurn()
  {
    // Poor bonus can only be scored once by the same player in one turn.
    return 0 == Utils::getGame()->getGameStateValue("playerTurnUsedPoorBonus");
  }

  public static function calculatePartyBonus($player_id)
  {
    $partyBonus = 0;

    if (
      Utils::getActivePartyBonus() &&
      Utils::playerHasNotYetPartiedInTurn() &&
      Utils::isPartyInPlay()
    ) {
      $addInflation = Utils::getActiveInflation() ? 1 : 0;

      $partyBonus = 1 + $addInflation;
      RulePartyBonus::notifyActiveFor($player_id, true);      
      Utils::getGame()->setGameStateValue("playerTurnUsedPartyBonus", 1);
    }

    return $partyBonus;
  }

  public static function checkForPartyBonus($player_id)
  {
    $partyBonus = Utils::calculatePartyBonus($player_id);
    if ($partyBonus > 0)
    {
      Utils::getGame()->performDrawCards($player_id, $partyBonus);
    }
  }

  public static function calculatePoorBonus($player_id)
  {
    $poorBonus = 0;

    if (
      Utils::getActivePoorBonus() &&
      Utils::playerHasNotYetBeenPoorInTurn() &&
      Utils::hasLeastKeepers($player_id)
    ) {
      $addInflation = Utils::getActiveInflation() ? 1 : 0;

      $poorBonus = 1 + $addInflation;
      RulePoorBonus::notifyActiveFor($player_id);
      Utils::getGame()->setGameStateValue("playerTurnUsedPoorBonus", 1);
    }

    return $poorBonus;
  }

  public static function checkForPoorBonus($player_id)
  {
    $poorBonus = Utils::calculatePoorBonus($player_id);
    if ($poorBonus > 0)
    {
      Utils::getGame()->performDrawCards($player_id, $poorBonus);
    }
  }
}
