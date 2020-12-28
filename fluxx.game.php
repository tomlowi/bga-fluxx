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
      "/modules/phps/" .
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
require_once "modules/phps/constants.inc.php";

use Fluxx\Cards\ActionCards\ActionCardFactory;
use Fluxx\Cards\NewRules\RuleCardFactory;
use Fluxx\Cards\Goals\GoalCardFactory;

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
      "actionToResolve" => 22,
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

    // Create cards
    $cards = [];

    foreach ($this->cardsDefinitions as $cardId => $card) {
      // keeper, goal, rule, action

      $cards[] = ["type" => $card["type"], "type_arg" => $cardId, "nbr" => 1];
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
      "discard" => $this->cards->getCardOnTop("discard"),
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

  public function drawExtraCards($player_id, $drawCount)
  {
    $cardsDrawn = $this->cards->pickCards($drawCount, "deck", $player_id);

    // don't increment drawn counter here, extra cards drawn from actions etc
    // do not count
    //self::incGameStateValue("drawnCards", $drawCount);

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

  public function playKeeperCard($player_id, $card, $card_definition)
  {
    $this->cards->moveCard($card["id"], "keepers", $player_id);

    // Notify all players about the keeper played
    self::notifyAllPlayers(
      "keeperPlayed",
      clienttranslate('${player_name} plays keeper <b>${card_name}</b>'),
      [
        "i18n" => ["card_name"],
        "player_name" => self::getActivePlayerName(),
        "player_id" => $player_id,
        "card_name" => $card_definition["name"],
        "card" => $card,
        "handCount" => $this->cards->countCardInLocation("hand", $player_id),
      ]
    );
  }

  public function playGoalCard($player_id, $card, $card_definition)
  {
    $currentGoalCount = $this->cards->countCardInLocation("goals");
    $hasDoubleAgenda = count(
      $this->cards->getCardsOfTypeInLocation("rule", 220, "rules")
    );

    if (!$hasDoubleAgenda) {
      // We discard existing goals
      $cards = $this->cards->getCardsInLocation("goals");
      if ($cards) {
        $this->cards->moveAllCardsInLocation("goals", "discard");
        self::notifyAllPlayers("goalsDiscarded", "", [
          "cards" => $cards,
          "discardCount" => $this->cards->countCardInLocation("discard"),
        ]);
      }
    } else {
      // @TODO: handle double agenda rule
      die("Double agenda not implemented");
    }

    // We play the new goal
    $this->cards->moveCard($card["id"], "goals");

    // Notify all players about the goal played
    self::notifyAllPlayers(
      "goalPlayed",
      clienttranslate('${player_name} sets a new goal <b>${card_name}</b>'),
      [
        "i18n" => ["card_name"],
        "player_name" => self::getActivePlayerName(),
        "player_id" => $player_id,
        "card_name" => $card_definition["name"],
        "card" => $card,
        "handCount" => $this->cards->countCardInLocation("hand", $player_id),
      ]
    );
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
    if ($cards) {
      $this->cards->moveAllCardsInLocation("rules", "discard", $location_arg);
      self::notifyAllPlayers("rulesDiscarded", "", [
        "cards" => $cards,
        "ruleType" => $ruleType,
        "discardCount" => $this->cards->countCardInLocation("discard"),
      ]);
    }
  }

  public function playRuleCard($player_id, $card, $card_definition)
  {
    $ruleCard = RuleCardFactory::getCard($card["id"], $card["type_arg"]);
    $ruleType = $ruleCard->getRuleType();

    // Execute the immediate rule effect
    $ruleCard->playFromHand($player_id);

    $location_arg = $this->getLocationArgForRuleType($ruleType);
    $this->cards->moveCard($card["id"], "rules", $location_arg);

    // Notify of the new rule
    self::notifyAllPlayers(
      "rulePlayed",
      clienttranslate('${player_name} placed a new rule: <b>${card_name}</b>'),
      [
        "i18n" => ["card_name"],
        "player_name" => self::getActivePlayerName(),
        "card_name" => $card_definition["name"],
        "player_id" => $player_id,
        "ruleType" => $ruleType,
        "card" => $card,
        "handCount" => $this->cards->countCardInLocation("hand", $player_id),
      ]
    );
  }

  public function playActionCard($player_id, $card, $card_definition)
  {
    self::setGameStateValue("actionToResolve", -1);
    $actionCard = ActionCardFactory::getCard($card["id"], $card["type_arg"]);
    // execute the action immediate effect
    $stateTransition = $actionCard->playFromHand($player_id);

    // We play the new action card
    $this->cards->playCard($card["id"]);

    // Notify all players about the action played
    self::notifyAllPlayers(
      "actionPlayed",
      clienttranslate('${player_name} plays an action: <b>${card_name}</b>'),
      [
        "i18n" => ["card_name"],
        "player_name" => self::getActivePlayerName(),
        "player_id" => $player_id,
        "card_name" => $card_definition["name"],
        "card" => $card,
        "handCount" => $this->cards->countCardInLocation("hand", $player_id),
        "discardCount" => $this->cards->countCardInLocation("discard"),
      ]
    );

    if ($stateTransition != null) {
      // player must resolve the action before continuing to play more cards
      // action card that needs to be resolved has been set in GameStateValue "actionToResolve"
      $this->gamestate->nextstate($stateTransition);
    }
  }

  function checkPlayerShouldPlayMoreCards($player_id)
  {
    // current rule and nr of cards already played
    $playRule = self::getGameStateValue("playRule");
    $cardsPlayed = self::getGameStateValue("playedCards");

    // still cards in hand?
    $cards_in_hand = $this->cards->countCardInLocation("hand", $player_id);

    // is Play All But 1 in play ?
    // If not, did the player play enough cards already (or hand empty) ?
    if (
      ($playRule == -1 && $cards_in_hand == 1) ||
      ($playRule != -1 && $cardsPlayed >= $playRule) ||
      $cards_in_hand == 0
    ) {
      return false;
    }

    return true;
  }

  function prepareForNextPlayerTurn()
  {
    $player_id = self::getActivePlayerId();
    $players = self::loadPlayersBasicInfos();

    // active player has played all cards they can/must play
    self::notifyAllPlayers(
      "turnFinished",
      clienttranslate('${player_name} finished their turn'),
      [
        "player_id" => $player_id,
        "player_name" => $players[$player_id]["player_name"],
      ]
    );

    // reset everything for turn of next player
    self::setGameStateValue("playedCards", 0);
    $this->gamestate->nextstate("donePlayingCards");
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
          return null;
        }
        // this player is the winner, unless someone else also reached a next goal
        $winnerId = $goalReachedByPlayerId;
        $winningGoalCard = $goalCard->getName();
      }
    }

    if ($winnerId == null) {
      return null;
    }

    return [
      "winner" => $winnerId,
      "goal" => $winningGoalCard,
    ];
  }

  public function discardRule($ruleType)
  {
    // 1 : Play
    // 2 : Draw
    // 3 : Keeper limit
    // 4 : Hand limit

    $rules = $this->cards->getCardsInLocation("rules");
    $card_args = [];
    switch ($ruleType) {
      case 1:
        $card_args = [1, 2, 3, 4, 5];
        break;
      case 2:
        $card_args = [6, 7, 8, 9];
        break;
      case 3:
        $card_args = [10, 11, 12];
        break;
      case 4:
        $card_args = [13, 14, 15, 16];
        break;
      default:
        return;
    }

    foreach ($rules as $card_id => $card) {
      if (in_array($card["type_arg"], $card_args)) {
        $this->cards->moveCard($card_id, "discard");
      }
    }
  }

  //////////////////////////////////////////////////////////////////////////////
  //////////// Player actions
  ////////////

  /*
    Each time a player is doing some game action, one of the methods below is called.
    (note: each method below must match an input method in fluxx.action.php)
     */

  public function action_playCard($card_id, $card_definition_id)
  {
    // Check that this is the player's turn and that it is a "possible action" at this game state (see states.inc.php)
    self::checkAction("playCard");

    $player_id = self::getActivePlayerId();
    $card = $this->cards->getCard($card_id);
    $card_definition = $this->cardsDefinitions[$card_definition_id];

    if ($card["location"] != "hand" or $card["location_arg"] != $player_id) {
      throw new BgaUserException(self::_("You do not have this card in hand"));
    }

    $card_type = $card_definition["type"];

    switch ($card_type) {
      case "keeper":
        $this->playKeeperCard($player_id, $card, $card_definition);
        break;
      case "goal":
        $this->playGoalCard($player_id, $card, $card_definition);
        break;
      case "rule":
        $this->playRuleCard($player_id, $card, $card_definition);
        break;
      case "action":
        $this->playActionCard($player_id, $card, $card_definition);
        break;
      default:
        die("Not implemented: Card type $card_type does not exist");
        break;
    }

    self::incGameStateValue("playedCards", 1);

    // A card has been played: do we have a new winner?
    $this->checkWinConditions();

    // @TODO: are we reaching a new hand or keeper limit for someone
    if (false) {
      $this->gamestate->nextState("enforceLimits");
      return;
    }

    // check if the active player should continue to play more cards
    if (!$this->checkPlayerShouldPlayMoreCards($player_id)) {
      $this->prepareForNextPlayerTurn();
    } else {
      // else: just let player continue playing cards
      // but explicitly set state again to force args refresh
      $this->gamestate->nextstate("continuePlay");
    }
  }

  /*
   * Player discards a nr of cards for hand limit
   */
  function action_removeCardsFromHand($cards_id)
  {
    // multiple active state, so don't use checkAction or getActivePlayerId here!
    $this->gamestate->checkPossibleAction("discardHandCards");
    $playerId = self::getCurrentPlayerId();

    $args = self::argHandLimit();
    if (count($cards_id) != $args["nb"]) {
      throw new BgaUserException(
        self::_("Wrong number of cards. Expected: ") . $args["nb"]
      );
    }

    $cards = [];
    self::dump("discardingPlayer", $playerId);
    foreach ($cards_id as $card_id) {
      // Verify card was in player hand
      $card = $this->cards->getCard($card_id);
      if (
        $card == null ||
        $card["location"] != "hand" ||
        $card["location_arg"] != $playerId
      ) {
        throw new BgaUserException(
          self::_("Impossible discard: invalid card ") . $card_id
        );
      }

      $cards[$card["id"]] = $card;

      // Discard card
      $this->cards->playCard($card["id"]);
    }

    self::notifyAllPlayers("handDiscarded", "", [
      "player_id" => $playerId,
      "cards" => $cards,
      "discardCount" => $this->cards->countCardInLocation("discard"),
      "handCount" => $this->cards->countCardInLocation("hand", $playerId),
    ]);

    // Multiple active state: this player is done
    $this->gamestate->setPlayerNonMultiactive($playerId, "");
  }

  /*
   * Player discards a nr of cards for keeper limit
   */
  function action_removeKeepersFromPlay($cards_id)
  {
    // multiple active state, so don't use checkAction or getActivePlayerId here!
    $this->gamestate->checkPossibleAction("discardKeepers");
    $playerId = self::getCurrentPlayerId();

    $args = self::argKeeperLimit();
    if (count($cards_id) != $args["nb"]) {
      throw new BgaUserException(
        self::_("Wrong number of cards. Expected: ") . $args["nb"]
      );
    }

    $cards = [];
    foreach ($cards_id as $card_id) {
      // Verify card was in player hand
      $card = $this->cards->getCard($card_id);
      if (
        $card == null ||
        $card["location"] != "keepers" ||
        $card["location_arg"] != $playerId
      ) {
        throw new BgaUserException(
          self::_("Impossible discard: invalid card ") . $card_id
        );
      }

      $cards[$card["id"]] = $card;

      // Discard card
      $this->cards->playCard($card["id"]);
    }

    self::notifyAllPlayers("keepersDiscarded", "", [
      "player_id" => $playerId,
      "cards" => $cards,
      "discardCount" => $this->cards->countCardInLocation("discard"),
    ]);

    // Multiple active state: this player is done
    $this->gamestate->setPlayerNonMultiactive($playerId, "");
  }

  //////////////////////////////////////////////////////////////////////////////
  //////////// Game state arguments
  ////////////

  /*
    Here, you can create methods defined as "game state arguments" (see "args" property in states.inc.php).
    These methods function is to return some additional information that is specific to the current
    game state.
     */

  public function argsCardsDraw()
  {
    $drawRule = self::getGameStateValue("drawRule");
    return ["nb" => $drawRule];
  }
  public function argsPlayCards()
  {
    $playRule = self::getGameStateValue("playRule");
    $played = self::getGameStateValue("playedCards");
    if ($playRule == 200) {
      return ["nb" => "All"];
    }
    if ($playRule == -1) {
      return ["nb" => "All but 1"];
    }
    return ["nb" => $playRule - $played];
  }
  public function argResolveAction()
  {
    $actionCard = self::getGameStateValue("actionToResolve");
    return ["action" => $actionCard];
  }
  public function argHandLimit()
  {
    $handLimit = self::getGameStateValue("handLimit");

    $player_id = self::getCurrentPlayerId(); // multiple active state!
    $cardsInHand = $this->cards->countCardInLocation("hand", $player_id);

    return [
      "limit" => $handLimit,
      "nb" => $cardsInHand - $handLimit,
    ];
  }
  public function argKeeperLimit()
  {
    $keeperLimit = self::getGameStateValue("keepersLimit");

    $player_id = self::getCurrentPlayerId(); // multiple active state!
    $keepersInPlay = $this->cards->countCardInLocation("keepers", $player_id);

    return [
      "limit" => $keeperLimit,
      "nb" => $keepersInPlay - $keeperLimit,
    ];
  }

  //////////////////////////////////////////////////////////////////////////////
  //////////// Game state actions
  ////////////

  public function stCardsDraw()
  {
    $player_id = self::getActivePlayerId();

    $drawRule = self::getGameStateValue("drawRule");
    // entering this state, so this player can always draw for current draw rule
    $this->drawExtraCards($player_id, $drawRule);
    self::setGameStateValue("drawnCards", $drawRule);

    $this->gamestate->nextstate("goPlayCards");
  }

  function stResolveAction()
  {
    $player_id = self::getActivePlayerId();
    $players = self::loadPlayersBasicInfos();

    // @TODO: for now, just mark action as finished and continue play
    // this should actually be done as response to specific client actions
    // depending on the special action card that was played
    $actionCardId = self::getGameStateValue("actionToResolve");
    $actionCardRow = $this->cards->getCard($actionCardId);
    $actionCard = ActionCardFactory::getCard(
      $actionCardId,
      $actionCardRow["type_arg"]
    );
    $actionName = $actionCard->getName();

    self::notifyAllPlayers(
      "actionDone",
      clienttranslate('${player_name} finished action ${action_name}'),
      [
        "player_id" => $player_id,
        "player_name" => $players[$player_id]["player_name"],
        "action_name" => $actionName,
      ]
    );

    self::setGameStateValue("actionToResolve", -1);

    if (!$this->checkPlayerShouldPlayMoreCards($player_id)) {
      $this->prepareForNextPlayerTurn();
    } else {
      $this->gamestate->nextstate("resolvedAction");
    }
  }

  public function stHandLimit()
  {
    $handLimit = self::getGameStateValue("handLimit");
    if ($handLimit < 0) {
      // no active Hand Limit, nothing to do
      $this->gamestate->nextstate("");
    }

    $players = self::loadPlayersBasicInfos();
    // find all players with too much cards in hand
    $active_players = [];
    foreach ($players as $player_id => $player) {
      $cardsInHand = $this->cards->countCardInLocation("hand", $player_id);
      if ($cardsInHand > $handLimit) {
        $toDiscard = $handLimit - $cardsInHand;
        // this player must discard
        $active_players[] = $player_id;
      }
      // Check next player
    }

    // Activate all players that need to discard (if any)
    if (count($active_players) > 0) {
      $this->gamestate->setPlayersMultiactive($active_players, "", true);
    } else {
      $this->gamestate->nextstate("");
    }
  }

  public function stKeeperLimit()
  {
    $keeperLimit = self::getGameStateValue("keepersLimit");
    if ($keeperLimit < 0) {
      // no active Keeper Limit, nothing to do
      $this->gamestate->nextstate("");
    }

    $players = self::loadPlayersBasicInfos();
    // find all players with too much keepers in play
    $active_players = [];
    foreach ($players as $player_id => $player) {
      $keepersInPlay = $this->cards->countCardInLocation("keepers", $player_id);
      if ($keepersInPlay > $keeperLimit) {
        $toDiscard = $keeperLimit - $keepersInPlay;
        // this player must discard keepers
        $active_players[] = $player_id;
      }
      // Check next player
    }

    // Activate all players that need to remove keepers (if any)
    if (count($active_players) > 0) {
      $this->gamestate->setPlayersMultiactive($active_players, "", true);
    } else {
      $this->gamestate->nextstate("");
    }
  }

  public function stNextPlayer()
  {
    $player_id = self::activeNextPlayer();
    self::giveExtraTime($player_id);
    $this->gamestate->nextState("nextPlayer");
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
