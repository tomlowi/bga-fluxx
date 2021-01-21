<?php

namespace Fluxx\Cards\Actions;
use Fluxx\Cards\CardFactory;
use Fluxx\Game\Utils;
/*
 * ActionCardFactory: how to create Action Cards
 */
class ActionCardFactory extends CardFactory
{
  public static function getCardFullClassName($uniqueId)
  {
    if (array_key_exists($uniqueId, self::$classesCreeperPack)) {
      $name = "Fluxx\Cards\Actions\\" . self::$classesCreeperPack[$uniqueId];
    } else {
      $name = "Fluxx\Cards\Actions\\" . self::$classes[$uniqueId];
    }
    return $name;
  }

  public static function listCardDefinitions()
  {
    $actionDefinitions = [];

    $cardClasses = self::$classes;
    if (Utils::useCreeperPackExpansion()) {
      $cardClasses += self::$classesCreeperPack;
    }

    foreach ($cardClasses as $definitionId => $class) {
      $card = self::getCard(0, $definitionId);

      $actionDefinitions[$definitionId] = [
        "type" => "action",
        "name" => $card->getName(),
        "description" => $card->getDescription(),
      ];
    }
    return $actionDefinitions;
  }

  /*
   * cardClasses : for each card Id, the corresponding class name
   */
  public static $classes = [
    301 => "ActionTrashAKeeper",
    302 => "ActionRotateHands",
    303 => "ActionRulesReset",
    304 => "ActionRandomTax",
    305 => "ActionRockPaperScissors",
    306 => "ActionTrashANewRule",
    307 => "ActionUseWhatYouTake",
    308 => "ActionZapACard",
    309 => "ActionDiscardAndDraw",
    310 => "ActionDraw2AndUseEm",
    311 => "ActionDraw3Play2",
    312 => "ActionEmptyTheTrash",
    313 => "ActionEverybodyGets1",
    314 => "ActionExchangeKeepers",
    315 => "ActionJackpot",
    316 => "ActionLetsDoThatAgain",
    317 => "ActionLetsSimplify",
    318 => "ActionNoLimits",
    319 => "ActionTradeHands",
    320 => "ActionShareTheWealth",
    321 => "ActionStealAKeeper",
    322 => "ActionTakeAnotherTurn",
    323 => "ActionTodaysSpecial",
  ];

  public static $classesCreeperPack = [
    351 => "ActionStealSomething",
    352 => "ActionTrashSomething",
    353 => "ActionCreeperSweeper",
    354 => "ActionMoveACreeper",
  ];
}
