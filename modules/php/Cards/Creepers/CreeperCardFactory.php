<?php

namespace Fluxx\Cards\Creepers;
use Fluxx\Cards\CardFactory;
use Fluxx\Game\Utils;
/*
 * CreeperCardFactory: how to create Creeper Cards
 */
class CreeperCardFactory extends CardFactory
{
  public static function getCardFullClassName($uniqueId)
  {
    $name = "Fluxx\Cards\Creepers\\" . self::$classes[$uniqueId];    
    return $name;
  }

  public static function listCardDefinitions()
  {
    $creeperDefinitions = [];

    if (Utils::useCreeperPackExpansion()) {
      foreach (self::$classes as $definitionId => $class) {
        $card = self::getCard(0, $definitionId);

        $creeperDefinitions[$definitionId] = [
          "type" => "creeper",
          "name" => $card->getName(),
          "subtitle" => $card->getSubtitle(),
          "description" => $card->getDescription(),
        ];
      }
    }

    return $creeperDefinitions;
  }

  /*
   * cardClasses : for each card Id, the corresponding class name
   * no need for separate Creeper Card files, Creepers have no game logic
   */
  public static $classes = [
    51 => "CreeperWar",
    52 => "CreeperTaxes",
    53 => "CreeperDeath",
    54 => "CreeperRadioactivePotato",
  ];
}
