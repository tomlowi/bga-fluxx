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

  public function immediateEffectOnPlay($player_id)
  {
    $game = Utils::getGame();

    $game->cards->moveAllCardsInLocation("discard", "deck");

    $remainingDiscards = [];
    // any TempHand action cards still active are still "floating discards",
    // so they should not be shuffled back in, but remain in discard
    for ($i = 3; $i >= 1; $i--) {
      $tmpHandLocation = "tmpHand" . $i;
      $tmpHandCardUniqueId = $game->getGameStateValue($tmpHandLocation . "Card");

      if ($tmpHandCardUniqueId > 0) {
        $tmpHandCard = array_values($game->cards->getCardsOfType("action", $tmpHandCardUniqueId))[0];
        $game->cards->playCard($tmpHandCard["id"]);
        array_push($remainingDiscards, $tmpHandCard);
      }  
    }

    // Also the current card needs to be put in the new discard pile
    $card_id = self::getCardId();
    $game->cards->playCard($card_id);
    array_push($remainingDiscards, $game->cards->getCard($card_id));

    // And then we reshuffle
    $game->cards->shuffle("deck");

    $game->notifyAllPlayers("reshuffle", "", [
      "deckCount" => $game->cards->countCardInLocation("deck"),
      "discardCount" => $game->cards->countCardInLocation("discard"),
      "exceptionCards" => $remainingDiscards
    ]);
  }
}
