<?php

namespace Fluxx\Cards\Keepers;
use Fluxx\Cards\CardFactory;
/*
 * KeeperCardFactory: how to create Keeper Cards
 */
class KeeperCardFactory extends CardFactory
{
  private static $classes = null;

  public static function listCardDefinitions()
  {
    $keeperDefinitions = [];
    foreach (self::getClasses() as $definitionId => $class) {
      $keeperDefinitions[$definitionId] = [
        "type" => "keeper",
        "set" => "base",
        "name" => $class["name"],
      ];
    }
    return $keeperDefinitions;
  }

  public static function getCard($cardId, $cardDefinitionId)
  {
    $cardDefinition = self::getClasses()[$cardDefinitionId];
    return new KeeperCard($cardId, $cardDefinitionId, $cardDefinition["name"]);
  }

  /*
   * cardClasses : for each card Id, the corresponding class name
   * no need for separate Keeper Card files, Keepers have no game logic
   */
  public static function getClasses()
  {
    if (self::$classes == null) {
      self::$classes = [
        1 => ["name" => clienttranslate("Sleep")],
        2 => ["name" => clienttranslate("The Brain")],
        3 => ["name" => clienttranslate("Bread")],
        4 => ["name" => clienttranslate("Chocolate")],
        5 => ["name" => clienttranslate("Cookies")],
        6 => ["name" => clienttranslate("Milk")],
        7 => ["name" => clienttranslate("Money")],
        8 => ["name" => clienttranslate("The Eye")],
        9 => ["name" => clienttranslate("The Moon")],
        10 => ["name" => clienttranslate("The Rocket")],
        11 => ["name" => clienttranslate("The Toaster")],
        12 => ["name" => clienttranslate("Television")],
        13 => ["name" => clienttranslate("Time")],
        14 => ["name" => clienttranslate("Dreams")],
        15 => ["name" => clienttranslate("Music")],
        16 => ["name" => clienttranslate("The Party")],
        17 => ["name" => clienttranslate("The Sun")],
        18 => ["name" => clienttranslate("Love")],
        19 => ["name" => clienttranslate("Peace")],
      ];
    }

    return self::$classes;
  }
}
