<?php
namespace Fluxx\States;

use Fluxx\Game\Utils;

trait DrawCardsTrait
{
  public function st_drawCards()
  {
    $game = Utils::getGame();
    $player_id = $game->getActivePlayerId();
    $cards = $game->cards;

    // Check if this player is empty handed and the "no-hand-bonus" is in play
    $hasNoHandBonus = Utils::getActiveNoHandBonus();
    $cardsInHand = $cards->countCardInLocation("hand", $player_id);

    $addInflation = Utils::getActiveInflation() ? 1 : 0;
    $partyBonus =
      Utils::getActivePartyBonus() && Utils::isPartyInPlay()
        ? 1 + $addInflation
        : 0;

    if ($cardsInHand == 0 && $hasNoHandBonus) {
      $drawNoHandBonus = 3 + $addInflation;
      $game->performDrawCards($player_id, $drawNoHandBonus);
    }

    // entering this state, so this player can always draw for current draw rule
    $game->performDrawCards(
      $player_id,
      $drawRule + $addInflation + $partyBonus
    );
    $game->setGameStateValue("drawnCards", $drawRule);

    $game->gamestate->nextstate("cardsDrawn");
  }
}
