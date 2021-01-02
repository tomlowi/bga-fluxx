<?php
namespace Fluxx\Cards\ActionCards;

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

  public function needsInteraction()
  {
    return false;
  }

  public function immediateEffectOnPlay($player)
  {
    $game = Utils::getGame();

    $handCards = $game->cards->getCardsInLocation("hand", $player);
    // current card should be excluded
    if ($handCards[$this->cardId] != null) {
      unset($handCards[$this->cardId]);
    }

    $countHandCards = count($handCards);
    // discard all hand cards
    $game->cards->moveCards(array_keys($handCards), "discard");
    // draw equal nr of new cards
    $game->drawExtraCards($player, $countHandCards);
  }
}
