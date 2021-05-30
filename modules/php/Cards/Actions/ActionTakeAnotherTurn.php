<?php
namespace Fluxx\Cards\Actions;

use Fluxx\Game\Utils;

class ActionTakeAnotherTurn extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Take Another Turn");
    $this->description = clienttranslate(
      "Take another turn as soon as you finish this one. The maximum number of turns you can take in a row using this card is two."
    );
  }

  public function immediateEffectOnPlay($player)
  {
    $game = Utils::getGame();
    $checkUsed = $game->getGameStateValue("anotherTurnMark");
    if ($checkUsed > 1) {

      $game->notifyAllPlayers(
        "takeAnotherTurnLimit",
        clienttranslate('${card_name} can not be used again, maximum two turns in a row'),
        [
          "player_id" => $player,
          "card_name" => $this->getName(),
        ]
      );

      return;
    }

    $game->setGameStateValue("anotherTurnMark", 1);
  }
}
