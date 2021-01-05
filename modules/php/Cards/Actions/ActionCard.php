<?php
namespace Fluxx\Cards\Actions;

use Fluxx\Cards\Card;
use Fluxx\Game\Utils;
/*
 * ActionCard: base class to handle actions cards
 */
class ActionCard extends Card
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);
  }

  // Indicates this Action can be handled without client-side player interactions
  public $interactionNeeded = null;

  // Implements the immediate effect when this action is played
  public function immediateEffectOnPlay($player)
  {
  }

  /**
   * playFromHand : default function to execute when a Card is played from hand.
   * return: null if the game should continue the play loop,
   * or "state Transition Name" if another state need to be called
   */
  public function playFromHand($player)
  {
    // Execute the immediate effect
    $this->immediateEffectOnPlay($player);

    if ($this->interactionNeeded != null) {
      Utils::getGame()->setGameStateValue(
        "actionToResolve",
        $this->getCardId()
      );
      return "resolveActionCard";
    }
    return null;
  }

  /**
   * resolvedBy : default function to execute when interactive decisions
   * for the played Action card have been resolved (passed in via args).
   * return: null if the game should continue the play loop,
   * or "state Transition Name" if another state need to be called
   */
  public function resolvedBy($player, $option, $cardIdsSelected)
  {
    return null;
  }
}
