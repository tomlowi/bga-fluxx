<?php
namespace Fluxx\States;

use Fluxx\Game\Utils;
use Fluxx\Cards\Rules\RuleInflation;
use Fluxx\Cards\Rules\RulePartyBonus;
use Fluxx\Cards\Rules\RulePoorBonus;

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

    if ($cardsInHand == 0 && $hasNoHandBonus) {
      $drawNoHandBonus = 3 + $addInflation;
      $game->performDrawCards($player_id, $drawNoHandBonus, true);
    }

    $drawRule = $game->getGameStateValue("drawRule");
    // Check for other draw bonuses
    if ($addInflation > 0) {
      RuleInflation::notifyActiveFor($player_id);
    }
    $partyBonus = Utils::calculatePartyBonus($player_id);
    $poorBonus = Utils::calculatePoorBonus($player_id);

    $cardsToDraw = $drawRule + $addInflation + $partyBonus + $poorBonus;
    // PlayAllBut1: If you started with no cards in your hand and only drew 1, draw an extra card.
    // => don't apply inflation on this "extra"
    if ($cardsInHand == 0 && $cardsToDraw <= 1 + $addInflation) {
      $playRule = $game->getGameStateValue("playRule");
      if ($playRule == -1) {
        $cardsToDraw += 1;
      }      
    }    

    // entering this state, so this player can always draw for current draw rule
    // postpone creepers to be resolved until after all cards drawn
    $game->performDrawCards(
      $player_id,
      $cardsToDraw,
      true
    );
    $game->setGameStateValue("drawnCards", $drawRule);

    // count statistics start of turn for this player
    self::incStat(1, "turns_number", $player_id);

    // move to state where player is allowed to start playing cards
    $game->gamestate->nextstate("cardsDrawn");

    $game->setGameStateValue("creeperForcedRecheckNeeded", 1);
  }
}
