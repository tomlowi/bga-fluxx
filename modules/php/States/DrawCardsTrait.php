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

    // entering this state, so this player can always draw for current draw rule
    // postpone creepers to be resolved until after all cards drawn
    $game->performDrawCards(
      $player_id,
      $drawRule + $addInflation + $partyBonus + $poorBonus,
      true
    );
    $game->setGameStateValue("drawnCards", $drawRule);

    // move to state where player is allowed to start playing cards
    $game->gamestate->nextstate("cardsDrawn");

    // check if any creepers must be resolved because of the cards just drawn
    // (e.g. player had Peace on the table and has just drawn War)
    if ($game->checkCreeperResolveNeeded(null)) {
      return;
    }

  }


}
