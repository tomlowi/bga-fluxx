<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * fluxx implementation : © Alexandre Spaeth <alexandre.spaeth@hey.com> & Iwan Tomlow <iwan.tomlow@gmail.com> & Julien Rossignol <tacotaco.dev@gmail.com>
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

// Define Constants
if (!defined("STATE_GAME_SETUP")) {
  define("STATE_GAME_SETUP", 1);
  define("STATE_GAME_END", 99);
  define("STATE_DRAW_CARDS", 10);
  define("STATE_PLAY_CARD", 20);
  define("STATE_ENFORCE_HAND_LIMIT_OTHERS", 21);
  define("STATE_ENFORCE_KEEPERS_LIMIT_OTHERS", 22);
  define("STATE_ENFORCE_HAND_LIMIT_SELF", 23);
  define("STATE_ENFORCE_KEEPERS_LIMIT_SELF", 24);
  define("STATE_GOAL_CLEANING", 25);
  define("STATE_RESOLVE_ACTION", 30);
  define("STATE_RESOLVE_ROCKPAPERSCISSORS", 31);
  define("STATE_NEXT_PLAYER", 90);
}

$machinestates = [
  // The initial state. Please do not modify.
  STATE_GAME_SETUP => [
    "name" => "gameSetup",
    "description" => "",
    "type" => "manager",
    "action" => "stGameSetup",
    "transitions" => ["" => STATE_DRAW_CARDS],
  ],

  STATE_DRAW_CARDS => [
    "name" => "drawCards",
    "description" => "",
    "type" => "game",
    "action" => "st_drawCards",
    "transitions" => [
      "cardsDrawn" => STATE_PLAY_CARD,
      "endGame" => STATE_GAME_END,
    ],
  ],

  STATE_PLAY_CARD => [
    "name" => "playCard",
    "description" => clienttranslate('${actplayer} must play ${count} card(s)'),
    "descriptionmyturn" => clienttranslate('${you} must play ${count} card(s)'),
    "type" => "activeplayer",
    "action" => "st_playCard",
    "args" => "arg_playCard",
    "possibleactions" => ["playCard", "pass"],
    "transitions" => [
      "handLimitRulePlayed" => STATE_ENFORCE_HAND_LIMIT_OTHERS,
      "keepersLimitRulePlayed" => STATE_ENFORCE_KEEPERS_LIMIT_OTHERS,
      "keepersExchangeOccured" => STATE_ENFORCE_KEEPERS_LIMIT_OTHERS,
      "endOfTurn" => STATE_ENFORCE_HAND_LIMIT_SELF,
      "doubleAgendaRule" => STATE_GOAL_CLEANING,

      "resolveActionCard" => STATE_RESOLVE_ACTION,
      "continuePlay" => STATE_PLAY_CARD,
      "endGame" => STATE_GAME_END,
    ],
  ],

  STATE_ENFORCE_HAND_LIMIT_OTHERS => [
    "name" => "enforceHandLimitForOthers",
    "description" => clienttranslate(
      'Some players must discard cards for Hand Limit ${limit}'
    ),
    "descriptionmyturn" => clienttranslate(
      '${you} must discard ${_private.count} cards for Hand Limit ${limit}'
    ),
    "type" => "multipleactiveplayer",
    "args" => "arg_enforceHandLimitForOthers",
    "action" => "st_enforceHandLimitForOthers",
    "possibleactions" => ["discardHandCards"],
    "transitions" => ["" => STATE_PLAY_CARD],
  ],

  STATE_ENFORCE_KEEPERS_LIMIT_OTHERS => [
    "name" => "enforceKeepersLimitForOthers",
    "description" => clienttranslate(
      'Some players must remove keepers for Keeper Limit ${limit}'
    ),
    "descriptionmyturn" => clienttranslate(
      '${you} must remove ${_private.count} keepers for Keeper Limit ${limit}'
    ),
    "type" => "multipleactiveplayer",
    "args" => "arg_enforceKeepersLimitForOthers",
    "action" => "st_enforceKeepersLimitForOthers",
    "possibleactions" => ["discardKeepers"],
    "transitions" => ["" => STATE_PLAY_CARD],
  ],

  STATE_ENFORCE_HAND_LIMIT_SELF => [
    "name" => "enforceHandLimitForSelf",
    "description" => clienttranslate(
      '${actplayer} must discard card(s) for Hand Limit ${limit}'
    ),
    "descriptionmyturn" => clienttranslate(
      '${you} must discard ${_private.count} card(s) for Hand Limit ${limit}'
    ),
    "type" => "activeplayer",
    "args" => "arg_enforceHandLimitForSelf",
    "action" => "st_enforceHandLimitForSelf",
    "possibleactions" => ["discardHandCards"],
    "transitions" => ["" => STATE_ENFORCE_KEEPERS_LIMIT_SELF],
  ],

  STATE_ENFORCE_KEEPERS_LIMIT_SELF => [
    "name" => "enforceKeepersLimitForSelf",
    "description" => clienttranslate(
      '${actplayer} must remove keepers(s) for Keeper Limit ${limit}'
    ),
    "descriptionmyturn" => clienttranslate(
      '${you} must remove ${_private.count} keeper(s) for Keeper Limit ${limit}'
    ),
    "type" => "activeplayer",
    "args" => "arg_enforceKeepersLimitForSelf",
    "action" => "st_enforceKeepersLimitForSelf",
    "possibleactions" => ["discardKeepers"],
    "transitions" => ["" => STATE_NEXT_PLAYER],
  ],

  STATE_GOAL_CLEANING => [
    "name" => "goalCleaning",
    "description" => clienttranslate('${actplayer} must discard a goal'),
    "descriptionmyturn" => clienttranslate('${you} must discard a goal'),
    "type" => "activeplayer",
    "action" => "st_goalCleaning",
    "possibleactions" => ["discardGoal"],
    "transitions" => ["" => STATE_PLAY_CARD],
  ],

  STATE_RESOLVE_ACTION => [
    "name" => "actionResolve",
    "description" => clienttranslate(
      '${actplayer} must resolve their action: ${action_name}'
    ),
    "descriptionmyturn" => clienttranslate(
      '${you} must resolve your action: ${action_name}'
    ),
    "type" => "activeplayer",
    "args" => "arg_resolveAction",
    //"action" => "st_resolveAction",
    "possibleactions" => [
      "resolveAction",
      "resolveActionPlayerSelection",
      "resolveActionCardSelection",
      "resolveActionCardsSelection",
      "resolveActionKeepersExchange",
      "resolveActionButtons",
    ],
    "transitions" => [
      "resolvedAction" => STATE_PLAY_CARD,
      "handsExchangeOccured" => STATE_ENFORCE_HAND_LIMIT_OTHERS,
      "keepersExchangeOccured" => STATE_ENFORCE_KEEPERS_LIMIT_OTHERS,
      "rulesChanged" => STATE_GOAL_CLEANING,
      "endGame" => STATE_GAME_END,
      "specialActionRockPaperScissors" => STATE_RESOLVE_ROCKPAPERSCISSORS,
    ],
  ],

  STATE_RESOLVE_ROCKPAPERSCISSORS => [
    "name" => "actionResolveRockPaperScissors",
    "description" => clienttranslate(
      'Players must play Rock Paper Scissors (${challenger_wins} - ${challenged_wins})'
    ),
    "descriptionmyturn" => clienttranslate(
      '${you} must play Rock Paper Scissors (${challenger_wins} - ${challenged_wins})'
    ),
    "type" => "multipleactiveplayer",
    "args" => "arg_rockPaperScissorsShowdown",
    "action" => "st_rockPaperScissorsShowdown",
    "possibleactions" => ["resolveActionButtonsRockPaperScissors"],
    "transitions" => [
      "rockPaperScissorsCheckNextRound" => STATE_RESOLVE_ROCKPAPERSCISSORS,
      "rockPaperScissorsFinished" => STATE_PLAY_CARD
    ],
  ],

  STATE_NEXT_PLAYER => [
    "name" => "nextPlayer",
    "description" => "",
    "type" => "game",
    "action" => "st_nextPlayer",
    "updateGameProgression" => true,
    "transitions" => ["" => STATE_DRAW_CARDS],
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
  STATE_GAME_END => [
    "name" => "gameEnd",
    "description" => clienttranslate("End of game"),
    "type" => "manager",
    "action" => "stGameEnd",
    "args" => "argGameEnd",
  ],
];
