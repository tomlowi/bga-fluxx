<?php
namespace Fluxx\Cards\Actions;

use Fluxx\Game\Utils;
use fluxx;

class ActionRockPaperScissors extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Rock-Paper-Scissors Showdown");
    $this->description = clienttranslate(
      "Challenge another player to a 3-round Rock-Paper-Scissors tournament. Winner takes loser's entire hand of cards."
    );
  }

  public $needsInteraction = true;

  public function immediateEffectOnPlay($player)
  {
    // nothing now, needs to go to resolve action state
  }

  public function resolvedBy($player, $option, $cardIdsSelected)
  {
    // options: 1 = Rock, 2 = Paper, 3 = Scissors

    // @TODO: Rock-Paper-Scissors Showdown
    // winner takes over hand cards from loser
    // Challenges: this will probably require an entirely separate state?
    // and after all is done, current player needs to continue its turn
  }
}
