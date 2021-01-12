<?php

namespace Fluxx\Cards\Creepers;
use Fluxx\Cards\CardFactory;
use Fluxx\Game\Utils;
/*
 * CreeperCardFactory: how to create Creeper Cards
 */
class CreeperCardFactory extends CardFactory
{
  private static $classes = null;

  public static function listCardDefinitions()
  {
    $creeperDefinitions = [];

    if (Utils::useCreeperPackExpansion())
    {
        foreach (self::getClasses() as $definitionId => $class) {
            $creeperDefinitions[$definitionId] = [
                "type" => "creeper",
                "name" => $class["name"],
            ];
        }
    }

    return $creeperDefinitions;
  }

  public static function getCard($cardId, $cardDefinitionId)
  {
    $cardDefinition = self::getClasses()[$cardDefinitionId];
    return new CreeperCard($cardId, $cardDefinitionId, 
        $cardDefinition["name"],
        $cardDefinition["subtitle"],
        $cardDefinition["description"]
    );
  }

  /*
   * cardClasses : for each card Id, the corresponding class name
   * no need for separate Creeper Card files, Creepers have no game logic
   */
  public static function getClasses()
  {
    if (self::$classes == null) {
      self::$classes = [
        51 => [
          "name" => clienttranslate("War"),
          "subtitle" => clienttranslate("Place Immediately + Redraw"),
          "description" => clienttranslate("You cannot win if you have this, unless the Goal says otherwise. If you have Peace, you must move it to another player."),
        ],
        52 => [
          "name" => clienttranslate("Taxes"),
          "subtitle" => clienttranslate("Place Immediately + Redraw"),
          "description" => clienttranslate("You cannot win if you have this, unless the Goal says otherwise. If you have Money in play, you can discard it and this."),
        ],
        53 => [
          "name" => clienttranslate("Death"),
          "subtitle" => clienttranslate("Place Immediately + Redraw"),
          "description" => clienttranslate("You cannot win if you have this, unless the Goal says otherwise. If you have this at the start of your turn, discard something else you have in play (a Keeper or Creeper). You may discard this anytime it stands alone."),
        ],
        54 => [
          "name" => clienttranslate("Radioactive Potato"),
          "subtitle" => clienttranslate("Place Immediately + Redraw"),
          "description" => clienttranslate("You cannot win if you have this card. Any time the Goal changes, move this card in the counter-turn direction."),
        ],
      ];
    }

    return self::$classes;
  }
}
