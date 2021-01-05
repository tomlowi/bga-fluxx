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

    $game->cards->moveAllCardsInLocation("discard", "deck");
    $game->cards->shuffle("deck");

    // The current card needs to be put in the new discard pile
    $card_id = self::getCardId();
    $game->cards->playCard($card_id);

    $card = $game->cards->getCard($card_id);

    $game->notifyAllPlayers("reshuffle", "", [
      "deckCount" => $game->cards->countCardInLocation("deck"),
      "discardCount" => $game->cards->countCardInLocation("discard"),
      "exceptionCards" => [$card],
    ]);
  }
}
