<?php

namespace Fluxx\Cards\Rules;
use Fluxx\Cards\CardFactory;
/*
 * RuleCardFactory: how to create Rule Cards
 */
class RuleCardFactory extends CardFactory
{
  public static function getCardFullClassName($uniqueId)
  {
    $name = "Fluxx\Cards\Rules\\" . self::$classes[$uniqueId];
    return $name;
  }

  public static function listCardDefinitions()
  {
    $ruleDefinitions = [];
    foreach (self::$classes as $definitionId => $class) {
      $card = self::getCard(0, $definitionId);

      $ruleDefinitions[$definitionId] = [
        "type" => "rule",
        "ruleType" => $card->getRuleType(),
        "name" => $card->getName(),
        "subtitle" => $card->getSubtitle(),
        "description" => $card->getDescription(),
      ];
    }
    return $ruleDefinitions;
  }

  /*
   * cardClasses : for each card Id, the corresponding class name
   */
  public static $classes = [
    201 => "RulePlay2",
    202 => "RulePlay3",
    203 => "RulePlay4",
    204 => "RulePlayAll",
    205 => "RulePlayAllBut1",
    206 => "RuleDraw2",
    207 => "RuleDraw3",
    208 => "RuleDraw4",
    209 => "RuleDraw5",
    210 => "RuleKeeperLimit2",
    211 => "RuleKeeperLimit3",
    212 => "RuleKeeperLimit4",
    213 => "RuleHandLimit0",
    214 => "RuleHandLimit1",
    215 => "RuleHandLimit2",
    216 => "RuleNoHandBonus",
    217 => "RulePartyBonus",
    218 => "RulePoorBonus",
    219 => "RuleRichBonus",
    220 => "RuleDoubleAgenda",
    221 => "RuleFirstPlayRandom",
    222 => "RuleGetOnWithIt",
    223 => "RuleGoalMill",
    224 => "RuleInflation",
    225 => "RuleMysteryPlay",
    226 => "RuleRecycling",
    227 => "RuleSwapPlaysForDraws",
  ];
}
