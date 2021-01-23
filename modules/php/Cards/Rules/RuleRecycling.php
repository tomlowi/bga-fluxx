<?php
namespace Fluxx\Cards\Rules;

use Fluxx\Game\Utils;

class RuleRecycling extends RuleCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Recycling");
    $this->subtitle = clienttranslate("Free Action");
    $this->description = clienttranslate(
      "Once during your turn, you may discard one of your Keepers from the table and draw 3 extra cards."
    );
  }

  public $interactionNeeded = "keeperSelectionSelf";

  public function canBeUsedInPlayerTurn($player_id)
  {
    return Utils::playerHasNotYetUsedRecycling();
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
    $game->setGameStateValue("playerTurnUsedRecycling", 1);    
    return parent::freePlayInPlayerTurn($player_id);
  }

  public function resolvedBy($player_id, $args)
  {
    // @TODO: Validate args contains 1 card, 
    // and it is a Keeper in play for this player
    // Discard it
    // Draw 3 cards (+ inflation bonus)
    // Notify to show discard and draws
  }
}
