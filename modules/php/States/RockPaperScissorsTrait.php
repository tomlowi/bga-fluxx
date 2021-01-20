<?php
namespace Fluxx\States;

use Fluxx\Game\Utils;


if (!defined("RPS_OPTION_NONE")) {
  define("RPS_OPTION_NONE", 0);
  define("RPS_OPTION_ROCK", 1);
  define("RPS_OPTION_PAPER", 2);
  define("RPS_OPTION_SCISSORS", 3);
}

trait RockPaperScissorsTrait
{

  private function checkChallengerWins($challenger, $challenged)
  {
    // Rock beats Scissors
    if ($challenger == RPS_OPTION_ROCK || $challenged == RPS_OPTION_SCISSORS)
      return true;
    // Scissors beats Paper
    if ($challenger == RPS_OPTION_SCISSORS || $challenged == RPS_OPTION_PAPER)
      return true;
    // Paper beats Rock
    if ($challenger == RPS_OPTION_PAPER || $challenged == RPS_OPTION_ROCK)
      return true;

    return false;
  }

  public function st_rockPaperScissorsShowdown()
  {
    $challenger_player_id = self::getGameStateValue("rpsChallengerId");
    $challenged_player_id = self::getGameStateValue("rpsChallengedId");

    $challenger_choice = self::getGameStateValue("rpsChallengerChoice");
    $challenged_choice = self::getGameStateValue("rpsChallengedChoice");

    self::dump("====choices====", [$challenger_choice, $challenged_choice]);

    $gamestate = Utils::getGame()->gamestate;
    // waiting for distinct player choices
    if ($challenger_choice == $challenged_choice)
    {      
      $gamestate->setPlayersMultiactive([$challenger_player_id, $challenged_player_id], "", true);
      return;
    }

    $maxWins = 0;
    $challengerWins = $this->checkChallengerWins($challenger_choice, $challenged_choice);
    if ($challengerWins)
    {
      $maxWins = self::incGameStateValue("rpsChallengerWins", 1);
    }
    else
    {
      $maxWins = self::incGameStateValue("rpsChallengedWins", 1);
    }

    self::dump("====maxWins====", $maxWins);
    // as long as neither has won the best of 3, keep playing
    if ($maxWins < 2)
    {
      $gamestate->setPlayersMultiactive([$challenger_player_id, $challenged_player_id], "", true);
      return;
    }      

    // determine the winner and loser
    $winning_player_id = $challenger_player_id;
    $losing_player_id = $challenged_player_id;
    if (!$challengerWins)
    {
      $winning_player_id = $challenged_player_id;
      $losing_player_id = $challenger_player_id;
    }
    // move all cards from loser hand to winner hand
    $game = Utils::getGame();
    $loserHand = $game->cards->getCardsInLocation(
      "hand",
      $losing_player_id
    );    

    $game->notifyPlayer($losing_player_id, "cardsSentToPlayer", "", [
      "cards" => $loserHand,
      "player_id" => $winning_player_id,
    ]);
    $game->notifyPlayer($winning_player_id, "cardsReceivedFromPlayer", "", [
      "cards" => $loserHand,
      "player_id" => $losing_player_id,
    ]);
    $game->cards->moveCards(array_keys($loserHand), "hand", $winning_player_id);

    // done, go back to normal play cards state
    $game->gamestate->nextstate("rockPaperScissorsFinished");
  }


  public function arg_rockPaperScissorsShowdown()
  {
    $challenger_wins = self::getGameStateValue("rpsChallengerWins");
    $challenged_wins = self::getGameStateValue("rpsChallengedWins");
    
    return [
      "challenger_wins" => $challenger_wins,
      "challenged_wins" => $challenged_wins,
      "action_type" => "buttonsRockPaperScissors",
      "action_args" => [
            ["value" => RPS_OPTION_ROCK, "label" => clienttranslate("Rock")],
            ["value" => RPS_OPTION_PAPER, "label" => clienttranslate("Paper")],
            ["value" => RPS_OPTION_SCISSORS, "label" => clienttranslate("Scissors")],
          ]
    ];
  }

  /*
   * Player made their choice for the current RockPaperScissors round
   */
  public function action_resolveActionButtonsRockPaperScissors($option)
  {
    $game = Utils::getGame();

    self::checkAction("resolveActionButtonsRockPaperScissors");
    $player_id = self::getActivePlayerId();

    $challenger_player_id = self::getGameStateValue("rpsChallengerId");
    $challenged_player_id = self::getGameStateValue("rpsChallengedId");

    // register the choice and wait for other player
    if ($player_id == $challenger_player_id)
    {
      self::setGameStateValue("rpsChallengerChoice", $option);
    } 
    else if ($player_id == $challenger_player_id)
    {
      self::setGameStateValue("rpsChallengedChoice", $option);
    }

    self::dump("====PlayerChoice====", [$player_id, $option]);
    // TODO: game hangs after 2nd player chooses
    // need separate "game" type state to check for win/next round instead of doing this in the same state?
    $game->gamestate->setPlayerNonMultiactive($player_id, "rockPaperScissorsCheckNextRound");
  }

}
