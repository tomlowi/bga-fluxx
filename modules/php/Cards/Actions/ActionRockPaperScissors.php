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

  public $interactionNeeded = "buttons";

  public function resolveArgs()
  {
    return [
      ["value" => "rock", "label" => clienttranslate("Rock")],
      ["value" => "paper", "label" => clienttranslate("Paper")],
      ["value" => "scissors", "label" => clienttranslate("Scissors")],
    ];
  }

  public function immediateEffectOnPlay($player_id)
  {
    // nothing now, needs to go to resolve action state
    return parent::immediateEffectOnPlay($player_id);
  }

  public function resolvedBy($player_id, $args)
  {
    $choice = $args["value"];

    // @TODO: Rock-Paper-Scissors Showdown
    // winner takes over hand cards from loser
    // Challenges: this will probably require an entirely separate state?
    // and after all is done, current player needs to continue its turn
  }
}
