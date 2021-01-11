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
 * fluxx.game.php
 *
 * This is the main file for your game logic.
 *
 * In this PHP file, you are going to defines the rules of the game.
 *
 */

$swdNamespaceAutoload = function ($class) {
  $classParts = explode("\\", $class);
  if ($classParts[0] == "Fluxx") {
    array_shift($classParts);
    $file =
      dirname(__FILE__) .
      "/modules/php/" .
      implode(DIRECTORY_SEPARATOR, $classParts) .
      ".php";
    if (file_exists($file)) {
      require_once $file;
    } else {
      var_dump("Impossible to load fluxx class : $class");
    }
  }
};
spl_autoload_register($swdNamespaceAutoload, true, true);

require_once APP_GAMEMODULE_PATH . "module/table/table.game.php";
require_once "modules/php/constants.inc.php";

use Fluxx\Cards\Keepers\KeeperCardFactory;
use Fluxx\Cards\Goals\GoalCardFactory;
use Fluxx\Cards\Rules\RuleCardFactory;
use Fluxx\Cards\Actions\ActionCardFactory;

class fluxx extends Table
{
  public static $instance = null;
  public function __construct()
  {
    // Your global variables labels:
    //  Here, you can assign labels to global variables you are using for this game.
    //  You can use any number of global variables with IDs between 10 and 99.
    //  If your game has options (variants), you also have to associate here a label to
    //  the corresponding ID in gameoptions.inc.php.
    // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
    parent::__construct();
    self::$instance = $this;

    self::initGameStateLabels([
      "drawRule" => 10,
      "playRule" => 11,
      "handLimit" => 12,
      "keepersLimit" => 13,
      "drawnCards" => 20,
      "playedCards" => 21,
      "lastGoalBeforeDoubleAgenda" => 30,
      "actionToResolve" => 40,
      "anotherTurnMark" => 41,
      "forcedCard" => 42,
      "optionCreeperPack" => 101,
    ]);
    $this->cards = self::getNew("module.common.deck");
    $this->cards->init("card");
    // We want to re-schuffle the discard pile in the deck automatically
    $this->cards->autoreshuffle = true;

    $this->cards->autoreshuffle_trigger = [
      "obj" => $this,
      "method" => "deckAutoReshuffle",
    ];
  }

  public static function get()
  {
    return self::$instance;
  }

  // Exposing protected method for translations in modules
  public static function totranslate($text)
  {
    return self::_($text);
  }

  protected function getGameName()
  {
    // Used for translations and stuff. Please do not modify.
    return "fluxx";
  }

  /*
    setupNewGame:

    This method is called only once, when a new game is launched.
    In this method, you must setup the game according to the game rules, so that
    the game is ready to be played.
     */
  protected function setupNewGame($players, $options = [])
  {
    // Set the colors of the players with HTML color code
    // The default below is red/green/blue/orange/brown
    // The number of colors defined here must correspond to the maximum number of players allowed for the gams
    $gameinfos = self::getGameinfos();
    $default_colors = $gameinfos["player_colors"];

    // Create players
    // Note: if you added some extra field on "player" table in the database (dbmodel.sql), you can initialize it there.
    $sql =
      "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES ";
    $values = [];
    foreach ($players as $player_id => $player) {
      $color = array_shift($default_colors);
      $values[] =
        "('" .
        $player_id .
        "','$color','" .
        $player["player_canal"] .
        "','" .
        addslashes($player["player_name"]) .
        "','" .
        addslashes($player["player_avatar"]) .
        "')";
    }
    $sql .= implode($values, ",");
    self::DbQuery($sql);
    self::reattributeColorsBasedOnPreferences(
      $players,
      $gameinfos["player_colors"]
    );
    self::reloadPlayersBasicInfos();

    /************ Start the game initialization *****/

    // Init global values with their initial values
    self::setGameStateInitialValue("drawRule", 1); // TODO: compute from table
    self::setGameStateInitialValue("playRule", 1); // TODO: compute from table
    self::setGameStateInitialValue("handLimit", -1); // TODO: compute from table
    self::setGameStateInitialValue("keepersLimit", -1); // TODO: compute from table
    self::setGameStateInitialValue("drawnCards", 0);
    self::setGameStateInitialValue("playedCards", 0);
    self::setGameStateInitialValue("anotherTurnMark", 0);
    self::setGameStateInitialValue("lastGoalBeforeDoubleAgenda", -1);
    self::setGameStateInitialValue("forcedCard", -1);

    // Create cards
    $cards = [];

    foreach ($this->getAllCardsDefinitions() as $definitionId => $card) {
      // keeper, goal, rule, action

      $cards[] = [
        "type" => $card["type"],
        "type_arg" => $definitionId,
        "nbr" => 1,
      ];
    }

    $this->cards->createCards($cards, "deck");

    // Shuffle deck to start
    $this->cards->shuffle("deck");

    // Each player starts the game with 3 cards
    foreach ($players as $player_id => $player) {
      $cards = $this->cards->pickCards(3, "deck", $player_id);
    }

    // @TODO: Init game statistics
    // (note: statistics used in this file must be defined in your stats.inc.php file)
    //self::initStat( 'table', 'table_teststat1', 0 );    // Init a table statistics
    //self::initStat( 'player', 'player_teststat1', 0 );  // Init a player statistics (for all players)

    // Activate first player
    $this->activeNextPlayer();
  }

  /*
    getAllDatas:

    Gather all informations about current game situation (visible by the current player).

    The method is called each time the game interface is displayed to a player, ie:
    _ when the game starts
    _ when a player refreshes the game page (F5)
     */
  protected function getAllDatas()
  {
    // We must only return informations visible by this player !!
    $current_player_id = self::getCurrentPlayerId();

    // Get information about players
    $sql = "SELECT player_id id, player_score score FROM player";
    $players = self::getCollectionFromDb($sql);

    $result = [
      "players" => $players,
      "cardsDefinitions" => $this->getAllCardsDefinitions(),
      "hand" => $this->cards->getCardsInLocation("hand", $current_player_id),
      "rules" => [
        "drawRule" => $this->cards->getCardsInLocation("rules", RULE_DRAW_RULE),
        "playRule" => $this->cards->getCardsInLocation("rules", RULE_PLAY_RULE),
        "handLimit" => $this->cards->getCardsInLocation(
          "rules",
          RULE_HAND_LIMIT
        ),
        "keepersLimit" => $this->cards->getCardsInLocation(
          "rules",
          RULE_KEEPERS_LIMIT
        ),
        "others" => $this->cards->getCardsInLocation("rules", RULE_OTHERS),
      ],
      "goals" => $this->cards->getCardsInLocation("goals"),
      "keepers" => [],
      "handsCount" => [],
      "discard" => $this->cards->getCardsInLocation("discard"),
      "deckCount" => $this->cards->countCardInLocation("deck"),
      "discardCount" => $this->cards->countCardInLocation("discard"),
    ];

    foreach ($players as $player_id => $player) {
      $result["keepers"][$player_id] = $this->cards->getCardsInLocation(
        "keepers",
        $player_id
      );
      $result["handsCount"][$player_id] = $this->cards->countCardInLocation(
        "hand",
        $player_id
      );
    }

    return $result;
  }

  /*
    getGameProgression:

    Compute and return the current game progression.
    The number returned must be an integer beween 0 (=the game just started) and
    100 (= the game is finished or almost finished).

    This method is called each time we are in a game state with the "updateGameProgression" property set to true
    (see states.inc.php)
     */
  public function getGameProgression()
  {
    // @TODO: compute and return the game progression

    return 0;
  }

  //////////////////////////////////////////////////////////////////////////////
  //////////// Utility functions
  ////////////

  /*
    In this space, you can put any utility methods useful for your game logic
   */

  /*
   * Get specific card definition for a card row
   */
  public function getCardDefinitionFor($card)
  {
    $cardType = $card["type"];

    switch ($cardType) {
      case "keeper":
        return KeeperCardFactory::getCard($card["id"], $card["type_arg"]);
      case "goal":
        return GoalCardFactory::getCard($card["id"], $card["type_arg"]);
      case "rule":
        return RuleCardFactory::getCard($card["id"], $card["type_arg"]);
      case "action":
        return ActionCardFactory::getCard($card["id"], $card["type_arg"]);
      default:
        return null;
    }
  }

  /*
   * Returns all cards definitions using factories
   */

  function getAllCardsDefinitions()
  {
    $keepers = KeeperCardFactory::listCardDefinitions();
    $goals = GoalCardFactory::listCardDefinitions();
    $rules = RuleCardFactory::listCardDefinitions();
    $actions = ActionCardFactory::listCardDefinitions();

    return $keepers + $goals + $rules + $actions;
  }

  /*
   * Returns player Id based on simultaneous game option state
   */
  function getPlayerIdForAction()
  {
    $state = $this->gamestate->state();
    if ($state["type"] == "multipleactiveplayer") {
      return self::getCurrentPlayerId();
    }
    return self::getActivePlayerId();
  }

  /*
   * Return an array of players in natural turn order starting
   * with the current player. This can be used to build the player
   * tables in the same order as the player boards,
   * and for actions that need the players in order.
   */
  public function getPlayersInOrder()
  {
    $result = [];

    $players = self::loadPlayersBasicInfos();
    $next_player = self::getNextPlayerTable();
    $player_id = self::getCurrentPlayerId();

    // Check for spectator
    if (!key_exists($player_id, $players)) {
      $player_id = $next_player[0];
    }

    // Build array starting with current player
    for ($i = 0; $i < count($players); $i++) {
      $result[] = $player_id;
      $player_id = $next_player[$player_id];
    }

    return $result;
  }

  public function performDrawCards($player_id, $drawCount)
  {
    $cardsDrawn = $this->cards->pickCards($drawCount, "deck", $player_id);

    // don't increment drawn counter here, extra cards drawn from actions etc
    // do not count

    self::notifyPlayer($player_id, "cardsDrawn", "", [
      "cards" => $cardsDrawn,
    ]);

    self::notifyAllPlayers(
      "cardsDrawnOther",
      clienttranslate('${player_name} draws <b>${drawCount}</b> card(s)'),
      [
        "player_name" => self::getActivePlayerName(),
        "drawCount" => $drawCount,
        "player_id" => $player_id,
        "handCount" => $this->cards->countCardInLocation("hand", $player_id),
        "deckCount" => $this->cards->countCardInLocation("deck"),
      ]
    );

    // check victory: some goals can also be triggered when extra cards drawn
    $this->checkWinConditions();
  }

  public function sendHandCountNotifications()
  {
    $players = self::loadPlayersBasicInfos();
    $handsCount = [];

    foreach ($players as $player_id => $player) {
      $handsCount[$player_id] = $this->cards->countCardInLocation(
        "hand",
        $player_id
      );
    }

    self::notifyAllPlayers("handCountUpdate", "", [
      "handsCount" => $handsCount,
    ]);
  }

  protected function getLocationArgForRuleType($ruleType)
  {
    switch ($ruleType) {
      case "playRule":
        $location_arg = RULE_PLAY_RULE;
        break;
      case "drawRule":
        $location_arg = RULE_DRAW_RULE;
        break;
      case "keepersLimit":
        $location_arg = RULE_KEEPERS_LIMIT;
        break;
      case "handLimit":
        $location_arg = RULE_HAND_LIMIT;
        break;
      default:
        $location_arg = RULE_OTHERS;
    }

    return $location_arg;
  }

  public function discardRuleCardsForType($ruleType)
  {
    $location_arg = $this->getLocationArgForRuleType($ruleType);

    // We discard the conflicting rule cards
    $cards = $this->cards->getCardsInLocation("rules", $location_arg);
    $player_id = self::getCurrentPlayerId();

    foreach ($cards as $card_id => $card) {
      $rule = RuleCardFactory::getCard($card_id, $card["type_arg"]);
      $rule->immediateEffectOnDiscard($player_id);
      $this->cards->playCard($card_id);
    }

    if ($cards) {
      self::notifyAllPlayers("rulesDiscarded", "", [
        "cards" => $cards,
        "discardCount" => $this->cards->countCardInLocation("discard"),
      ]);
    }
  }

  public function discardCardsFromLocation($cards_id, $location, $location_arg)
  {
    $cards = [];
    foreach ($cards_id as $card_id) {
      // Verify card is in the right location
      $card = $this->cards->getCard($card_id);
      if (
        $card == null ||
        $card["location"] != $location ||
        $card["location_arg"] != $location_arg
      ) {
        BgaUserException(
          self::_("Impossible discard: invalid card ") . $card_id
        );
      }

      $cards[$card["id"]] = $card;

      // Discard card
      $this->cards->playCard($card["id"]);
    }
    return $cards;
  }

  public function deckAutoReshuffle()
  {
    self::notifyAllPlayers("reshuffle", "", [
      "deckCount" => $this->cards->countCardInLocation("deck"),
      "discardCount" => $this->cards->countCardInLocation("discard"),
    ]);
  }

  public function checkWinConditions()
  {
    $winnerInfo = $this->checkCurrentGoalsWinner();
    if ($winnerInfo == null) {
      return;
    }

    // We have one winner, no tie
    $winnerId = $winnerInfo["winner"];
    $winningGoal = $winnerInfo["goal"];

    // set final score
    $sql = "UPDATE player SET player_score=1  WHERE player_id='$winnerId'";
    self::DbQuery($sql);

    $newScores = self::getCollectionFromDb(
      "SELECT player_id, player_score FROM player",
      true
    );
    self::notifyAllPlayers("newScores", "", [
      "newScores" => $newScores,
    ]);

    $players = self::loadPlayersBasicInfos();
    self::notifyAllPlayers(
      "win",
      clienttranslate('${player_name} wins with goal <b>${goal_name}</b>'),
      [
        "player_id" => $winnerId,
        "player_name" => $players[$winnerId]["player_name"],
        "goal_name" => $winningGoal,
      ]
    );

    $this->gamestate->nextState("endGame");
  }

  public function checkCurrentGoalsWinner()
  {
    $winnerId = null;
    $winningGoalCard = null;
    $goals = $this->cards->getCardsInLocation("goals");
    foreach ($goals as $card_id => $card) {
      $goalCard = GoalCardFactory::getCard($card["id"], $card["type_arg"]);

      $goalReachedByPlayerId = $goalCard->goalReachedByPlayer();
      if ($goalReachedByPlayerId != null) {
        // some player reached this goal
        if ($winnerId != null && $goalReachedByPlayerId != $winnerId) {
          // if multiple goals reached by different players, keep playing
          return;
        }
        // this player is the winner, unless someone else also reached a next goal
        $winnerId = $goalReachedByPlayerId;
        $winningGoalCard = $goalCard->getName();
      }
    }

    if ($winnerId == null) {
      return;
    }

    return [
      "winner" => $winnerId,
      "goal" => $winningGoalCard,
    ];
  }

  //////////////////////////////////////////////////////////////////////////////
  //////////// Player actions
  ////////////

  /*
    Each time a player is doing some game action, one of the methods below is called.
    (note: each method below must match an input method in fluxx.action.php)
     */

  /*
   * Player discards a goal after double agenda
   */
  public function action_discardGoal($card_id)
  {
    self::checkAction("discardGoal");
    $player_id = self::getActivePlayerId();
    $card = $this->cards->getCard($card_id);

    $lastPlayedGoal = self::getGameStateValue("lastGoalBeforeDoubleAgenda");

    if ($card["id"] == $lastPlayedGoal) {
      throw new BgaUserException(
        self::_("You cannot discard the goal card you just played.")
      );
    }

    if ($card["location"] != "goals") {
      throw new BgaUserException(self::_("This goal is not in play."));
    }

    // Discard card
    $this->cards->playCard($card["id"]);

    self::notifyAllPlayers("goalsDiscarded", "", [
      "cards" => [$card],
      "discardCount" => $this->cards->countCardInLocation("discard"),
    ]);

    self::setGameStateValue("lastGoalBeforeDoubleAgenda", -1);
    $this->gamestate->nextstate("");
  }

  //////////////////////////////////////////////////////////////////////////////
  //////////// Game state arguments
  ////////////

  /*
    Here, you can create methods defined as "game state arguments" (see "args" property in states.inc.php).
    These methods function is to return some additional information that is specific to the current
    game state.
     */

  use Fluxx\States\DrawCardsTrait;
  use Fluxx\States\PlayCardTrait;
  use Fluxx\States\HandLimitTrait;
  use Fluxx\States\KeepersLimitTrait;
  use Fluxx\States\ResolveActionTrait;

  //////////////////////////////////////////////////////////////////////////////
  //////////// Game state actions
  ////////////

  public function st_goalCleaning()
  {
    $hasDoubleAgenda = count(
      $this->cards->getCardsOfTypeInLocation("rule", 220, "rules")
    );
    $existingGoalCount = $this->cards->countCardInLocation("goals");

    $expectedCount = $hasDoubleAgenda + 1;

    if ($existingGoalCount <= $expectedCount) {
      // We already have the proper number of goals, proceed to play
      $this->gamestate->nextstate("");
      return;
    }
  }

  public function st_nextPlayer()
  {
    // special case: current player received another turn
    $anotherTurnMark = self::getGameStateValue("anotherTurnMark");
    $player_id = -1;
    if ($anotherTurnMark == 1) {
      // Take Another Turn can only be used once (two turns in a row)
      self::setGameStateValue("anotherTurnMark", 2);
      $player_id = self::getActivePlayerId();
      self::notifyAllPlayers(
        "turnFinished",
        clienttranslate('${player_name} can take another turn!'),
        [
          "player_id" => self::getActivePlayerId(),
          "player_name" => self::getCurrentPlayerName(),
        ]
      );
    } else {
      self::setGameStateValue("anotherTurnMark", 0);
      self::notifyAllPlayers(
        "turnFinished",
        clienttranslate('${player_name} finished their turn.'),
        [
          "player_id" => self::getActivePlayerId(),
          "player_name" => self::getCurrentPlayerName(),
        ]
      );
      $player_id = self::activeNextPlayer();
    }

    // reset everything for turn of next player
    self::setGameStateValue("playedCards", 0);
    self::giveExtraTime($player_id);
    $this->gamestate->nextState("");
  }

  //////////////////////////////////////////////////////////////////////////////
  //////////// Zombie
  ////////////

  /*
    zombieTurn:

    This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
    You can do whatever you want in order to make sure the turn of this player ends appropriately
    (ex: pass).

    Important: your zombie code will be called when the player leaves the game. This action is triggered
    from the main site and propagated to the gameserver from a server, not from a browser.
    As a consequence, there is no current player associated to this action. In your zombieTurn function,
    you must _never_ use getCurrentPlayerId() or getCurrentPlayerName(), otherwise it will fail with a "Not logged" error message.
     */

  public function zombieTurn($state, $active_player)
  {
    $statename = $state["name"];

    if ($state["type"] === "activeplayer") {
      switch ($statename) {
        default:
          $this->gamestate->nextState("zombiePass");
          break;
      }

      return;
    }

    if ($state["type"] === "multipleactiveplayer") {
      // Make sure player is in a non blocking status for role turn
      $this->gamestate->setPlayerNonMultiactive($active_player, "");

      return;
    }

    throw new feException(
      "Zombie mode not supported at this game state: " . $statename // NOI18N
    );
  }

  ///////////////////////////////////////////////////////////////////////////////////:
  ////////// DB upgrade
  //////////

  /*
    upgradeTableDb:

    You don't have to care about this until your game has been published on BGA.
    Once your game is on BGA, this method is called everytime the system detects a game running with your old
    Database scheme.
    In this case, if you change your Database scheme, you just have to apply the needed changes in order to
    update the game database and allow the game to continue to run with your new version.

     */

  public function upgradeTableDb($from_version)
  {
    // $from_version is the current version of this game database, in numerical form.
    // For example, if the game was running with a release of your game named "140430-1345",
    // $from_version is equal to 1404301345

    // Example:
    //        if( $from_version <= 1404301345 )
    //        {
    //            // ! important ! Use DBPREFIX_<table_name> for all tables
    //
    //            $sql = "ALTER TABLE DBPREFIX_xxxxxxx ....";
    //            self::applyDbUpgradeToAllDB( $sql );
    //        }
    //        if( $from_version <= 1405061421 )
    //        {
    //            // ! important ! Use DBPREFIX_<table_name> for all tables
    //
    //            $sql = "CREATE TABLE DBPREFIX_xxxxxxx ....";
    //            self::applyDbUpgradeToAllDB( $sql );
    //        }
    //        // Please add your future database scheme changes here
    //
    //
  }
}
