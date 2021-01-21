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
  private function getOptionLabels()
  {
    $options = [];
    
    $options[RPS_OPTION_ROCK] = clienttranslate("Rock");
    $options[RPS_OPTION_PAPER] = clienttranslate("Paper");
    $options[RPS_OPTION_SCISSORS] = clienttranslate("Scissors");

    return $options;
  }

  private function checkChallengerWins($challenger, $challenged)
  {
    // Rock beats Scissors
    if ($challenger == RPS_OPTION_ROCK && $challenged == RPS_OPTION_SCISSORS)
      return true;
    // Scissors beats Paper
    if ($challenger == RPS_OPTION_SCISSORS && $challenged == RPS_OPTION_PAPER)
      return true;
    // Paper beats Rock
    if ($challenger == RPS_OPTION_PAPER && $challenged == RPS_OPTION_ROCK)
      return true;

    return false;
  }

  public function st_nextRoundRockPaperScissors()
  {
    $options = $this->getOptionLabels();
    $challenger_choice = self::getGameStateValue("rpsChallengerChoice");
    $challenged_choice = self::getGameStateValue("rpsChallengedChoice");    

    // need distinct player choices, otherwies tie
    if ($challenger_choice == $challenged_choice)
    {
      self::notifyAllPlayers(
        "rockPaperScissorsRound",
        clienttranslate('Tie: ${challenger_choice} challenged ${challenged_choice}, try again'),
        [
          "challenger_choice" => $options[$challenger_choice],
          "challenged_choice" => $options[$challenged_choice],
        ]
      );

      $this->gamestate->nextstate("rockPaperScissorsContinue");
      return;
    }

    $challenger_player_id = self::getGameStateValue("rpsChallengerId");
    $challenged_player_id = self::getGameStateValue("rpsChallengedId");

    $maxWins = 0;
    $winning_player_id = -1;
    $losing_player_id = -1;
    // determine the winner and loser
    $challengerWins = $this->checkChallengerWins($challenger_choice, $challenged_choice);
    if ($challengerWins)
    {
      $maxWins = self::incGameStateValue("rpsChallengerWins", 1);
      $winning_player_id = $challenger_player_id;
      $losing_player_id = $challenged_player_id;
    }
    else
    {
      $maxWins = self::incGameStateValue("rpsChallengedWins", 1);
      $winning_player_id = $challenged_player_id;
      $losing_player_id = $challenger_player_id;
    }

    $players = self::loadPlayersBasicInfos();    
    self::notifyAllPlayers(
      "rockPaperScissorsRound",
      clienttranslate('${player_name} wins: ${challenger_choice} challenged ${challenged_choice}'),
      [
        "player_id" => $winning_player_id,
        "player_name" => $players[$winning_player_id]["player_name"],
        "challenger_choice" => $options[$challenger_choice],
        "challenged_choice" => $options[$challenged_choice],
      ]
    );

    // as long as neither has won the best of 3, keep playing (next round)
    if ($maxWins < 2)
    {
      $this->gamestate->nextstate("rockPaperScissorsContinue");
      return;
    }

    // it's done! give all hand cards of loser to winner
    self::notifyAllPlayers(
      "rockPaperScissorsRound",
      clienttranslate('${player_name} wins the Rock-Paper-Scissors showdown'),
      [
        "player_id" => $winning_player_id,
        "player_name" => $players[$winning_player_id]["player_name"],        
      ]
    );    

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

    $game->sendHandCountNotifications();

    // done, go back to normal play cards state
    $this->gamestate->nextstate("rockPaperScissorsFinished");
  }

  public function st_actionResolveRockPaperScissors()
  {
    // activate the 2 players that need to battle it out
    $challenger_player_id = self::getGameStateValue("rpsChallengerId");
    $challenged_player_id = self::getGameStateValue("rpsChallengedId");

    $this->gamestate->setPlayersMultiactive([$challenger_player_id, $challenged_player_id], 
        "rockPaperScissorsContinue", true);
  }


  public function arg_actionResolveRockPaperScissors()
  {
    $challenger_wins = self::getGameStateValue("rpsChallengerWins");
    $challenged_wins = self::getGameStateValue("rpsChallengedWins");

    $action_args = [];
    foreach ($this->getOptionLabels() as $option_value => $option_label) {
      $action_args[] = ["value" => $option_value, "label" => $option_label];
    }
    
    return [
      "challenger_wins" => $challenger_wins,
      "challenged_wins" => $challenged_wins,
      "action_type" => "buttonsRockPaperScissors",
      "action_args" => $action_args
    ];
  }

  /*
   * Player made their choice for the current RockPaperScissors round
   */
  public function action_resolveActionButtonsRockPaperScissors($option)
  {
    self::checkAction("resolveActionButtonsRockPaperScissors");
    $player_id = self::getCurrentPlayerId();

    $challenger_player_id = self::getGameStateValue("rpsChallengerId");
    $challenged_player_id = self::getGameStateValue("rpsChallengedId");

    // register the choice and wait for other player
    if ($player_id == $challenger_player_id)
    {
      self::setGameStateValue("rpsChallengerChoice", $option);
    } 
    else if ($player_id == $challenged_player_id)
    {
      self::setGameStateValue("rpsChallengedChoice", $option);
    }

    // if both players made their choice, go to state to check continue/winner
    $this->gamestate->setPlayerNonMultiactive($player_id, "rockPaperScissorsCheckNextRound");
  }

}
