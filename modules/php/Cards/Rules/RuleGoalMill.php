<?php
namespace Fluxx\Cards\Rules;

use Fluxx\Game\Utils;

class RuleGoalMill extends RuleCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Goal Mill");
    $this->subtitle = clienttranslate("Free Action");
    $this->description = clienttranslate(
      "Once during your turn, discard as many of your Goal cards as you choose, then draw that many cards."
    );
  }

  public $interactionNeeded = "handCardsSelection";

  public function canBeUsedInPlayerTurn($player_id)
  {    
    return Utils::playerHasNotYetUsedGoalMill();
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
    $game->setGameStateValue("playerTurnUsedGoalMill", 1);    
    return parent::freePlayInPlayerTurn($player_id);
  }

  public function resolvedBy($player_id, $args)
  {
    // @TODO: validate all cards are goals in hand of player
    // discard them
    // draw equal number
    // notify about discard and draws
  }
}
