<?php
namespace Fluxx\Cards\ActionCards;

use Fluxx\Game\Utils;
use fluxx;

class ActionTodaysSpecial extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Today’s Special!");
    $this->description = clienttranslate(
      "Set your hand aside and draw 3 cards. If today is your birthday, play all 3 cards. If today is a holiday or special anniversary, play 2 of the cards. If it's just another day, play only 1 card. Discard the remainder."
    );
  }

  public function needsInteraction()
  {
    return true;
  }

  public function immediateEffectOnPlay($player)
  {
    // nothing now, needs to go to resolve action state
  }

  public function resolvedBy($player, $option, $cardIdsSelected)
  {
    // options: 3 = Birthday, 2 = Holiday/Anniversary, 1 = Just another day
    $nrCardsToDraw = 3;
    $nrCardsToPlay = $option;

    // @TODO: Today’s Special!
    // Challenges: current hand needs to be set aside and player gets special turn with these cards
    // this will probably require an entirely separate state?
    // and after all is done, current player needs to continue its turn

    Utils::getGame()->drawExtraCards($player, $nrCardsToDraw);
  }
}
