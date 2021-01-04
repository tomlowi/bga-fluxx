<?php
namespace Fluxx\Cards\Actions;

use Fluxx\Game\Utils;

class ActionEmptyTheTrash extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Empty the Trash");
    $this->description = clienttranslate(
      "Start a new discard pile with this card and shuffle the rest of the discard pile back into the draw pile."
    );
  }

  public function immediateEffectOnPlay($player)
  {
    $game = Utils::getGame();

    // current card is not yet discarded, so no worries there
    $game->cards->moveAllCardsInLocation("discard", "deck");
    $game->cards->shuffle("deck");
  }
}
