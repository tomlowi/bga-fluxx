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

  public $interactionNeeded = "playerSelection";

  public function immediateEffectOnPlay($player_id)
  {
    // nothing now, needs to go to resolve action state
    return parent::immediateEffectOnPlay($player_id);
  }

  public function resolvedBy($player_id, $args)
  {
    //$choice = $args["value"];
    $game = Utils::getGame();
    $challenged_player_id = $args["selected_player_id"];

    // @TODO: Rock-Paper-Scissors Showdown
    // winner takes over hand cards from loser
    // Challenges: this will probably require an entirely separate state?
    // and after all is done, current player needs to continue its turn

    $game->setGameStateValue("rpsChallengerId", $player_id);
    $game->setGameStateValue("rpsChallengedId", $challenged_player_id);
    $game->setGameStateValue("rpsChallengerChoice", 0);
    $game->setGameStateValue("rpsChallengedChoice", 0);
    $game->setGameStateValue("rpsChallengerWins", 0);
    $game->setGameStateValue("rpsChallengedWins", 0);

    return "specialActionRockPaperScissors";
  }
}
