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
    $drawCount = $this->countSwapPlaysForDraws($player_id);
    return $drawCount > 0;
  }

  public function immediateEffectOnPlay($player)
  {
    // nothing
  }

  public function immediateEffectOnDiscard($player)
  {
    // nothing
  }

  private function countSwapPlaysForDraws($player_id)
  {
    $game = Utils::getGame();
    // calculate how many cards player should still play
    $drawCount = Utils::calculateCardsLeftToPlayFor($player_id);
    return $drawCount;
  }

  public function freePlayInPlayerTurn($player_id)
  {
    $game = Utils::getGame();
    // calculate how many cards player should still play
    $drawCount = $this->countSwapPlaysForDraws($player_id);
    // draw as many cards as we could have still played
    if ($drawCount > 0)
    {
      $game->performDrawCards($player_id, $drawCount);
    }
    // force end of turn (set count cards played above 999)
    $game->setGameStateValue("playedCards", PLAY_COUNT_ALL + 1);

    return null;
  }
}
