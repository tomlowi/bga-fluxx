<?php
namespace Fluxx\Cards\Actions;

use Fluxx\Game\Utils;

class ActionDiscardAndDraw extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Discard and Draw");
    $this->description = clienttranslate(
      "Discard your entire hand, then draw as many cards as you discarded. Do not count this card when determining how many cards to draw."
    );
  }

  public function immediateEffectOnPlay($player_id)
  {
    $game = Utils::getGame();

    $cards = $game->cards->getCardsInLocation("hand", $player_id);

    // discard all cards
    foreach ($cards as $card_id => $card) {
      $game->cards->playCard($card_id);
    }

    $game->notifyAllPlayers("handDiscarded", "", [
      "player_id" => $player_id,
      "cards" => $cards,
      "discardCount" => $game->cards->countCardInLocation("discard"),
      "handCount" => $game->cards->countCardInLocation("hand", $player_id),
    ]);

    // make sure we can't draw back this card itself (after reshuffle if deck would be empty)
    $game->cards->moveCard($this->getCardId(), "side", $player_id);

    // draw equal nr of new cards
    $game->performDrawCards($player_id, count($cards));

    // move this card itself back to the discard pile
    $game->cards->playCard($this->getCardId());
  }
}
