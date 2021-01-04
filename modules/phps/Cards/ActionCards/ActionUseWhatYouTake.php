<?php
namespace Fluxx\Cards\ActionCards;

use Fluxx\Game\Utils;
use fluxx;

class ActionUseWhatYouTake extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Use What You Take");
    $this->description = clienttranslate(
      "Take a card at random form another player's hand, and play it."
    );
  }

  public function needsInteraction()
  {
    return true;
  }

  public function immediateEffectOnPlay($player)
  {
    // nothing now, needs to go to resolve action state
  }

  public function resolvedBy($player, $option, $cardIdsSelected)
  {
    // options: index or id of the player chosen ?

    // @TODO: Use What You Take
    // Challenges: select any of other players, then use the stolen card as if played from hand
    // and after all is done, current player needs to continue its turn
  }
}
