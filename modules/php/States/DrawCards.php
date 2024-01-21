<?php
namespace Fluxx\States;

trait DrawCardsTrait
{
  public function st_drawCards()
  {
    $game = Utils::getGame();
    $player_id = $game->getActivePlayerId();
    $cards = $game->cards;

    // Check if this player is empty handed and the "no-hand-bonus" is in play
    $hasNoHandBonus =
      count($cards->getCardsOfTypeInLocation("rule", 216, "rules")) > 0;
    $cardsInHand = $cards->countCardInLocation("hand", $player_id);

    if ($cardsInHand == 0 && $hasNoHandBonus) {
      $game->performDrawCards($player_id, 3);
    }

    $drawRule = $game->getGameStateValue("drawRule");

    // entering this state, so this player can always draw for current draw rule
    $game->performDrawCards($player_id, $drawRule);
    $game->setGameStateValue("drawnCards", $drawRule);

    $game->gamestate->nextstate("cardsDrawn");
  }
}
