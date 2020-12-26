<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * fluxx implementation : © Alexandre Spaeth <alexandre.spaeth@hey.com> & Julien Rossignol <tacotaco.dev@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * states.inc.php
 *
 * fluxx game states description
 *
 */

/*
Game state machine is a tool used to facilitate game developpement by doing common stuff that can be set up
in a very easy way from this configuration file.

Please check the BGA Studio presentation about game state to understand this, and associated documentation.
 
Summary:

States types:
_ activeplayer: in this type of state, we expect some action from the active player.
_ multipleactiveplayer: in this type of state, we expect some action from multiple players (the active players)
_ game: this is an intermediary state where we don't expect any actions from players. Your game logic must decide what is the next game state.
_ manager: special type for initial and final state

Arguments of game states:
_ name: the name of the GameState, in order you can recognize it on your own code.
_ description: the description of the current game state is always displayed in the action status bar on
the top of the game. Most of the time this is useless for game state with "game" type.
_ descriptionmyturn: the description of the current game state when it's your turn.
_ type: defines the type of game states (activeplayer / multipleactiveplayer / game / manager)
_ action: name of the method to call when this game state become the current game state. Usually, the
action method is prefixed by "st" (ex: "stMyGameStateName").
_ possibleactions: array that specify possible player actions on this step. It allows you to use "checkAction"
method on both client side (Javacript: this.checkAction) and server side (PHP: self::checkAction).
_ transitions: the transitions are the possible paths to go from a game state to another. You must name
transitions in order to use transition names in "nextState" PHP method, and use IDs to
specify the next game state for each transition.
_ args: name of the method to call to retrieve arguments for this gamestate. Arguments are sent to the
client side to be used on "onEnteringState" or to set arguments in the gamestate description.
_ updateGameProgression: when specified, the game progression is updated (=> call to your getGameProgression
method).
 */

//    !! It is not a good idea to modify this file when a game is running !!

$machinestates = [
  // The initial state. Please do not modify.
  1 => [
    "name" => "gameSetup",
    "description" => "",
    "type" => "manager",
    "action" => "stGameSetup",
    "transitions" => ["" => 10],
  ],

  10 => [
    "name" => "cardsDraw",
    "description" => "",
    "type" => "game",
    "action" => "stCardsDraw",
    // "args" => "argsCardsDraw",
    "transitions" => ["playCards" => 11, "endGame" => 99],
  ],

  11 => [
    "name" => "playCard",
    "description" => clienttranslate('${actplayer} must play a card'),
    "descriptionmyturn" => clienttranslate('${you} must play a card'),
    "type" => "activeplayer",
    "possibleactions" => ["playCard"],
    "transitions" => [
      "enforceLimits" => 13,
      "playedCard" => 13,
      "endGame" => 99,
    ],
  ],

  13 => [
    "name" => "handLimit",
    "description" => clienttranslate('${actplayer} must discard ${nb} cards'),
    "descriptionmyturn" => clienttranslate('${you} must discard ${nb} cards'),
    "type" => "activeplayer",
    "args" => "argHandLimit",
    "action" => "stHandLimit",
    "possibleactions" => ["discardCard"],
    "transitions" => ["" => 13],
  ],

  14 => [
    "name" => "keeperLimit",
    "description" => clienttranslate('${actplayer} discard play ${nb} keepers'),
    "descriptionmyturn" => clienttranslate('${you} discard play ${nb} keepers'),
    "type" => "activeplayer",
    "args" => "argKeeperLimit",
    "action" => "stKeeperLimit",
    "possibleactions" => ["discardKeeper"],
    "transitions" => ["" => 14],
  ],

  15 => [
    "name" => "nextPlayer",
    "description" => "",
    "type" => "game",
    "action" => "stNextPlayer",
    "updateGameProgression" => true,
    "transitions" => ["nextPlayer" => 10],
  ],

  /*
Examples:

2 => array(
"name" => "nextPlayer",
"description" => '',
"type" => "game",
"action" => "stNextPlayer",
"updateGameProgression" => true,
"transitions" => array( "endGame" => 99, "nextPlayer" => 10 )
),

10 => array(
"name" => "playerTurn",
"description" => clienttranslate('${actplayer} must play a card or pass'),
"descriptionmyturn" => clienttranslate('${you} must play a card or pass'),
"type" => "activeplayer",
"possibleactions" => array( "playCard", "pass" ),
"transitions" => array( "playCard" => 2, "pass" => 2 )
),

 */

  // Final state.
  // Please do not modify (and do not overload action/args methods).
  99 => [
    "name" => "gameEnd",
    "description" => clienttranslate("End of game"),
    "type" => "manager",
    "action" => "stGameEnd",
    "args" => "argGameEnd",
  ],
];
