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

    if ($cardsInHand == 0 && $hasNoHandBonus) {
      $drawNoHandBonus = 3 + $addInflation;
      $game->performDrawCards($player_id, $drawNoHandBonus);
    }

    $drawRule = $game->getGameStateValue("drawRule");
    // Check for other draw bonuses
    $addInflation = Utils::getActiveInflation() ? 1 : 0;
    if ($addInflation > 0) {
      RuleInflation::notifyActiveFor($player_id);
    }
    $partyBonus =
      Utils::getActivePartyBonus()
      && Utils::playerHasNotYetPartiedInTurn()
      && Utils::isPartyInPlay()
        ? 1 + $addInflation
        : 0;
    if ($partyBonus > 0) {
      RulePartyBonus::notifyActiveFor($player_id, true);
      Utils::getGame()->setGameStateValue("playerTurnUsedPartyBonus", 1);
    }
    $poorBonus = 
      Utils::getActivePoorBonus() 
      && Utils::playerHasNotYetBeenPoorInTurn()
      && Utils::hasLeastKeepers($player_id)
        ? 1 + $addInflation
        : 0;
    if ($poorBonus > 0) {
      RulePoorBonus::notifyActiveFor($player_id);
      Utils::getGame()->setGameStateValue("playerTurnUsedPoorBonus", 1);
    }

    // entering this state, so this player can always draw for current draw rule
    $game->performDrawCards(
      $player_id,
      $drawRule + $addInflation + $partyBonus + $poorBonus
    );
    $game->setGameStateValue("drawnCards", $drawRule);

    $game->gamestate->nextstate("cardsDrawn");
  }
}
