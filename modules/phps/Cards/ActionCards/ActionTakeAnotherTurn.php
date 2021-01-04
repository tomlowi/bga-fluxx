<?php
namespace Fluxx\Cards\ActionCards;

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

  public function needsInteraction()
  {
    return false;
  }

  public function immediateEffectOnPlay($player)
  {
    Utils::getGame()->incGameStateValue("anotherTurnMark", 1);
  }
}
