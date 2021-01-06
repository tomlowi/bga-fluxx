<?php
namespace Fluxx\Cards\Actions;

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

  public $interactionNeeded = "playerSelection";

  public function resolvedBy($player_id, $args)
  {
    $option = $args["option"];
    $cardIdsSelected = $args["cardIdsSelected"];
    // options: index or id of the player chosen ?

    // @TODO: Use What You Take
    // Challenges: select any of other players, then use the stolen card as if played from hand
    // and after all is done, current player needs to continue its turn
  }
}
