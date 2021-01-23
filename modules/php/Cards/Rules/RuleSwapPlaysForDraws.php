<?php
namespace Fluxx\Cards\Rules;

use Fluxx\Game\Utils;

class RuleSwapPlaysForDraws extends RuleCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Swap Plays for Draws");
    $this->subtitle = clienttranslate("Takes Instant Effect");
    $this->description = clienttranslate(
      "During your yurn, you may decide to play no more cards and instead draw as many cards as you have plays remaining. If Play All, draw as many cards as you hold."
    );
  }

  public function canBeUsedInPlayerTurn($player_id)
  {
    return true;
  }

  public function immediateEffectOnPlay($player)
  {
    // nothing
  }

  public function immediateEffectOnDiscard($player)
  {
    // nothing
  }

  public function freePlayInPlayerTurn($player_id)
  {
    $game = Utils::getGame();
    // calculate how many cards player should still play
    $leftToPlay = Utils::calculateCardsLeftToPlayFor($player_id, false);
    $drawCount = $leftToPlay;
    if ($leftToPlay >= PLAY_COUNT_ALL)
    { // Play All > draw as many as cards in hand
      $drawCount = $game->cards->countCardInLocation("hand", $player_id);
    } 
    elseif ($leftToPlay < 0)
    { // Play All but 1 > draw as many as cards in hand minus the leftover
      $handCount = $game->cards->countCardInLocation("hand", $player_id);
      $drawCount = $handCount + $leftToPlay; // ok, $leftToPlay is negative here
    }
    // draw as many cards as we could have still played
    $game->performDrawCards($player_id, $drawCount);

    // force end of turn (set count cards played above 999)
    $game->setGameStateValue("playedCards", PLAY_COUNT_ALL + 1);

    return null;
  }
}
