<?php

namespace Fluxx\Cards\Goals;
use Fluxx\Cards\CardFactory;
/*
 * GoalCardFactory: how to create Goal Cards
 */
class GoalCardFactory extends CardFactory
{
  public static function getCardFullClassName($uniqueId)
  {
    $name = "Fluxx\Cards\Goals\\" . self::$classes[$uniqueId];
    return $name;
  }

  public static function listCardDefinitions()
  {
    $goalDefinitions = [];
    foreach (self::$classes as $definitionId => $class) {
      $card = self::getCard(0, $definitionId);

      $goalDefinitions[$definitionId] = [
        "type" => "goal",
        "name" => $card->getName(),
        "subtitle" => $card->getSubtitle(),
      ];
    }
    return $goalDefinitions;
  }

  /*
   * cardClasses : for each card Id, the corresponding class name
   */
  public static $classes = [
    101 => "Goal10CardsInHand",
    102 => "Goal5Keepers",
    103 => "GoalTheAppliances",
    104 => "GoalBakedGoods",
    105 => "GoalBedTime",
    106 => "GoalTheBrainNoTV",
    107 => "GoalBreadAndChocolate",
    108 => "GoalCantBuyMeLove",
    109 => "GoalChocolateCookies",
    110 => "GoalChocolateMilk",
    111 => "GoalDayDreams",
    112 => "GoalDreamland",
    113 => "GoalEyeOfTheBeholder",
    114 => "GoalGreatThemeSong",
    115 => "GoalHeartsAndMinds",
    116 => "GoalHippyism",
    117 => "GoalLullaby",
    118 => "GoalMilkAndCookies",
    119 => "GoalTheMindsEye",
    120 => "GoalNightAndDay",
    121 => "GoalPartySnacks",
    122 => "GoalPartyTime",
    123 => "GoalRocketScience",
    124 => "GoalRocketToTheMoon",
    125 => "GoalSquishyChocolate",
    126 => "GoalTimeIsMoney",
    127 => "GoalToast",
    128 => "GoalTurnItUp",
    129 => "GoalWinningTheLottery",
    130 => "GoalWorldPeace",
  ];
}
