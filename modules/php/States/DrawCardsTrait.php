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
      $game->performDrawCards($player_id, $drawNoHandBonus);
    }

    $drawRule = $game->getGameStateValue("drawRule");
    // Check for other draw bonuses
    if ($addInflation > 0) {
      RuleInflation::notifyActiveFor($player_id);
    }
    $partyBonus = Utils::calculatePartyBonus($player_id);
    $poorBonus = Utils::calculatePoorBonus($player_id);

    // entering this state, so this player can always draw for current draw rule
    $game->performDrawCards(
      $player_id,
      $drawRule + $addInflation + $partyBonus + $poorBonus
    );
    $game->setGameStateValue("drawnCards", $drawRule);

    // move to state where player is allowed to start playing cards
    $game->gamestate->nextstate("cardsDrawn");

    // check if the first play random rule is active
    // if so, the first card is already played automatically
    $this->checkFirstPlayRandom();
  }

  private function checkFirstPlayRandom()
  {
    $game = Utils::getGame();
    $firstPlayRandom = (0 != $game->getGameStateValue("activeFirstPlayRandom"));
    $playRule = $game->getGameStateValue("playRule");

    // Ignore this rule if the current Rule card allow you to play only one card
    if (!$firstPlayRandom || $playRule <= 1)
      return;
    
    // select random card from player hand (always something there, just drew cards)
    $player_id = $game->getActivePlayerId();
    $cardsInHand = $game->cards->getCardsInLocation("hand", $player_id);

    $i = bga_rand(0, count($cardsInHand) - 1);
    $card = array_values($cardsInHand)[$i];

    $game->notifyAllPlayers(
      "firstPlayRandom",
      clienttranslate('${player_name} must play first card random'),
      [
        "player_name" => $game->getActivePlayerName(),
        "player_id" => $player_id,
      ]
    );

    // note: be aware we can't have "checkAction" running here!
    // the *active* player has already changed, but the *current* player
    // is still the previous player that triggered its turn end
    // so "checkAction" would thrown "It is not your turn" to the current player
    // when trying to play the card for the active player

        // first card is a forced play, but in this case
    // it does count for the number of cards played
    $this->_action_playCard($card["id"], true);
    
  }
}
