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
 * fluxx.game.php
 *
 * This is the main file for your game logic.
 *
 * In this PHP file, you are going to defines the rules of the game.
 *
 */

require_once APP_GAMEMODULE_PATH . "module/table/table.game.php";
require_once "modules/php/constants.inc.php";

class fluxx extends Table
{
  public function __construct()
  {
    // Your global variables labels:
    //  Here, you can assign labels to global variables you are using for this game.
    //  You can use any number of global variables with IDs between 10 and 99.
    //  If your game has options (variants), you also have to associate here a label to
    //  the corresponding ID in gameoptions.inc.php.
    // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
    parent::__construct();

    self::initGameStateLabels([
      "drawRule" => 10,
      "playRule" => 11,
      "handLimit" => 12,
      "keepersLimit" => 13,
      "drawnCards" => 20,
      "playedCards" => 21,
    ]);
    $this->cards = self::getNew("module.common.deck");
    $this->cards->init("card");
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

    // We want to re-schuffle the discard pile in the deck automatically
    $this->cards->autoreshuffle = true;

    // @TODO: is this interesting?
    // $this->cards->autoreshuffle_trigger = [
    //   "obj" => $this,
    //   "method" => "deckAutoReshuffle",
    // ];

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

  public function drawCards($player_id, $drawCount)
  {
    $cardsDrawn = $this->cards->pickCards($drawCount, "deck", $player_id);
    self::incGameStateValue("drawnCards", $drawCount);

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

  public function playRuleCard($player_id, $card, $card_definition)
  {
    $ruleType = $card_definition["ruleType"];

    switch ($ruleType) {
      case "playRule":
        $location_arg = RULE_PLAY_RULE;
        $discardExisting = true;
        break;
      case "drawRule":
        $location_arg = RULE_DRAW_RULE;
        $discardExisting = true;
        break;
      case "keepersLimit":
        $location_arg = RULE_KEEPERS_LIMIT;
        $discardExisting = true;
        break;
      case "handLimit":
        $location_arg = RULE_HAND_LIMIT;
        $discardExisting = true;
        break;
      default:
        $location_arg = RULE_OTHERS;
        $discardExisting = false;
    }

    if ($discardExisting) {
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

    // We play the new play rule
    $this->cards->moveCard($card["id"], "rules", $location_arg);
    self::notifyAllPlayers(
      "rulePlayed",
      clienttranslate('${player_name} plays a new rule: <b>${card_name}</b>'),
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

    //@TODO: we apply the action card rule
  }

  public function deckReshuffle()
  {
    $this->cards->shuffle("deck");
  }
  public function deckAutoReshuffle()
  {
    // @TODO: get current player
    // self::notifyAllPlayers(
    //   "reshuffle",
    //   clienttranslate(
    //     '${player_name} reshuffles the discard pile into the deck to draw'
    //   ),
    //   [
    //     "player_id" => $player_id,
    //   ]
    // );
  }

  public function checkWin()
  {
    return;
    $rules = $this->cards->getCardsInLocation("rules");
    $goals = [];

    $players = self::loadPlayersBasicInfos();
    $winner_id = null;

    foreach ($rules as $card_id => $card) {
      if ($card["type"] == 3) {
        switch ($card["type_arg"]) {
          case 1:
            // If someone has 10 or more cards in his or her hand,
            // then the player with the most cards in hand wins.
            // In the event of a tie, continue playing until a clear winner emerges.
            $maxCards = -1;
            $cardCounts = [];
            foreach ($players as $player_id => $player) {
              // Count each player hand cards
              $nbCards = $this->cards->countCardsInLocation("hand", $player_id);
              if ($nbCards >= 10) {
                if ($nbCards > $maxCards) {
                  $cardCounts = [];
                  $maxCards = $nbCards;
                  $cardCounts[] = $player_id;
                }
                if ($nbCards == $maxCards) {
                  $cardCounts[] = $player_id;
                }
              }
            }
            if (count($cardCounts) == 1) {
              // We have one winner, no tie
              $winner_id = $cardCounts[0];
              $sql = "UPDATE player SET player_score=1  WHERE player_id='$winner_id'";
              self::DbQuery($sql);

              $newScores = self::getCollectionFromDb(
                "SELECT player_id, player_score FROM player",
                true
              );
              self::notifyAllPlayers("newScores", "", [
                "newScores" => $newScores,
              ]);

              self::notifyAllPlayers(
                "win",
                clienttranslate(
                  '${player_name} wins with ${nbr} cards in hand'
                ),
                [
                  "player_id" => $winner_id,
                  "player_name" => $players[$winner_id],
                  "nbr" => $maxCards,
                ]
              );
              $this->gamestate->nextState("endGame");
              return true;
            }
            break;

          case 2:
            // If someone has 5 or more Keepers on the table,
            // then the player with the most Keepers in play wins.
            // In the event of a tie, continue playing until a clear winner emerges.
            $maxkeepers = -1;
            $keeperCounts = [];

            foreach ($players as $player_id => $player) {
              // Count each player keepers
              $nbKeepers = $this->cards->countCardsInLocation(
                "keepers",
                $player_id
              );
              if ($nbKeepers >= 5) {
                if ($nbKeepers > $maxkeepers) {
                  $keeperCounts = [];
                  $maxkeepers = $nbKeepers;
                  $keeperCounts[] = $player_id;
                } elseif ($nbKeepers == $maxkeepers) {
                  $keeperCounts[] = $player_id;
                }
              }
            }

            if (count($keeperCounts) == 1) {
              // We have one winner, no tie
              $winner_id = $keeperCounts[0];
              $sql = "UPDATE player SET player_score=1  WHERE player_id='$winner_id'";
              self::DbQuery($sql);

              $newScores = self::getCollectionFromDb(
                "SELECT player_id, player_score FROM player",
                true
              );
              self::notifyAllPlayers("newScores", "", [
                "newScores" => $newScores,
              ]);

              self::notifyAllPlayers(
                "win",
                clienttranslate(
                  '${player_name} wins with ${nbr} keepers in play'
                ),
                [
                  "player_id" => $winner_id,
                  "player_name" => $players[$winner_id],
                  "nbr" => $maxkeepers,
                ]
              );
              $this->gamestate->nextState("endGame");
              return true;
            }

            break;
          case 3:
            // The Toaster + Television
            $winner_id = $this->checkTwoKeepersWin(11, 12);
            break;
          case 4:
            // Bread + Cookies
            $winner_id = $this->checkTwoKeepersWin(3, 5);
            break;
          case 5:
            // Sleep + Time
            $winner_id = $this->checkTwoKeepersWin(1, 13);
            break;
          case 6:
            // If no one has Television on the table, the player with The Brain on the table wins.
            $winner_id = $this->checkTwoKeepersWin(2, 12, true);
            break;
          case 7:
            // Bread + Chocolate
            $winner_id = $this->checkTwoKeepersWin(3, 4);
            break;
          case 8:
            // Money + Love
            $winner_id = $this->checkTwoKeepersWin(7, 18);
            break;
          case 9:
            // Chocolate + Cookies
            $winner_id = $this->checkTwoKeepersWin(4, 5);
            break;
          case 10:
            // Chocolate + Milk
            $winner_id = $this->checkTwoKeepersWin(4, 6);
            break;
          case 11:
            // The Sun + Dreams
            $winner_id = $this->checkTwoKeepersWin(17, 14);
            break;
          case 12:
            // Sleep + Dreams
            $winner_id = $this->checkTwoKeepersWin(1, 14);
            break;
          case 13:
            // The Eye + Love
            $winner_id = $this->checkTwoKeepersWin(8, 18);
            break;
          case 14:
            // Music + Television
            $winner_id = $this->checkTwoKeepersWin(15, 12);
            break;
          case 15:
            // Love + The Brain
            $winner_id = $this->checkTwoKeepersWin(18, 2);
            break;
          case 16:
            // Peace + Love
            $winner_id = $this->checkTwoKeepersWin(19, 18);
            break;
          case 17:
            // Sleep + Music
            $winner_id = $this->checkTwoKeepersWin(1, 15);
            break;
          case 18:
            // Milk + Cookies
            $winner_id = $this->checkTwoKeepersWin(6, 5);
            break;
          case 19:
            //The Brain + The Eye
            $winner_id = $this->checkTwoKeepersWin(2, 8);
            break;
          case 20:
            // The Sun + The Moon
            $winner_id = $this->checkTwoKeepersWin(17, 9);
            break;
          case 21:
            // The Party + at least 1 food Keeper (Bread, Chocolate, Cookies, Milk)
            $food_keepers_id = [3, 4, 5, 6];
            $i = 0;
            while ($i < count($food_keepers_id) && $winner_id == null) {
              $winner_id = $this->checkTwoKeepersWin(16, $food_keepers_id[$i]);
              $i++;
            }
            break;
          case 22:
            // The Party + Time
            $winner_id = $this->checkTwoKeepersWin(16, 13);
            break;
          case 23:
            // The Rocket + The Brain
            $winner_id = $this->checkTwoKeepersWin(10, 2);
            break;
          case 24:
            // The Rocket + The Moon
            $winner_id = $this->checkTwoKeepersWin(10, 9);
            break;
          case 25:
            // Chocolate + The Sun
            $winner_id = $this->checkTwoKeepersWin(4, 17);
            break;
          case 26:
            // Time + Money
            $winner_id = $this->checkTwoKeepersWin(13, 7);
            break;
          case 27:
            // Bread + The Toaster
            $winner_id = $this->checkTwoKeepersWin(3, 11);
            break;
          case 28:
            // Music + The Party
            $winner_id = $this->checkTwoKeepersWin(15, 16);
            break;
          case 29:
            // Dreams + Money
            $winner_id = $this->checkTwoKeepersWin(14, 7);
            break;
          case 30:
            // Dreams + Peace
            $winner_id = $this->checkTwoKeepersWin(14, 19);
            break;
        }

        if ($winner_id != null) {
          $winning_card = $card["type"] * 100 + ($card["type_arg"] - 0);

          $sql = "UPDATE player SET player_score=1  WHERE player_id='$winner_id'";
          self::DbQuery($sql);

          $newScores = self::getCollectionFromDb(
            "SELECT player_id, player_score FROM player",
            true
          );
          self::notifyAllPlayers("newScores", "", ["newScores" => $newScores]);

          self::notifyAllPlayers(
            "win",
            "",
            clienttranslate('${player_name} wins with <b>${card_name}</b>'),
            [
              "player_id" => $winner_id,
              "player_name" => $players[$winner_id],
              "card_name" => $this->id_label[$winning_card]["name"],
            ]
          );
          $this->gamestate->nextState("endGame");
          return true;
        }
      }
    }

    return false;
  }

  public function setFinalScore()
  {
  }

  public function checkTwoKeepersWin(
    $keeper_nbr_A,
    $keeper_nbr_B,
    $without_B = false
  ) {
    //getCardsOfTypeInLocation( $type, $type_arg=null, $location, $location_arg = null )
    $keeper_A = $this->cards->getCardsOfTypeInLocation(
      2,
      $keeper_nbr_A,
      "keepers",
      null
    );
    $keeper_B = $this->cards->getCardsOfTypeInLocation(
      2,
      $keeper_nbr_B,
      "keepers",
      null
    );

    if ($without_B) {
      if (count($keeper_A) != 0 && count($keeper_B) == 0) {
        return $keeper_A["location_arg"];
      } else {
        return null;
      }
    } else {
      if (count($keeper_A) != 0 && count($keeper_B) != 0) {
        $location_arg_A = null;
        $location_arg_B = null;
        foreach ($keeper_A as $card_id => $card) {
          $location_arg_A = $card["location_arg"];
        }
        foreach ($keeper_B as $card_id => $card) {
          $location_arg_B = $card["location_arg"];
        }

        if ($location_arg_B == $location_arg_B) {
          return $location_arg_A;
        } else {
          return null;
        }
      } else {
        return null;
      }
    }
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

    // TODO: properly handle states
    $this->drawCards($player_id, 1);
    //---
    // @TODO: remove return
    return;

    // A card has been played: do we have a new winner?
    if ($this->checkWin()) {
      $this->setFinalScore();
      $this->gamestate->nextState("endGame");
    }

    // @TODO: are we reaching a new hand or keeper limit for someone
    if (false) {
      $this->gamestate->nextState("enforceLimits");
      return;
    }

    $cardsCount = $this->cards->countCardsInLocation("hand", $player_id);

    // If this user cannot play anymore, move to the next state
    if ($cardsCount == 0) {
      $this->gamestate->nextstate("playedCard");
      return;
    }

    $playedCount = self::getGameStateValue("playedCards");
    $playRule = self::getGameStateValue("playRule");

    // Regular play rule
    if ($playRule > 0 and $playedCount >= $playRule) {
      $this->gamestate->nextstate("playedCard");
      return;
    }

    // All but one (or one and inflation)
    if ($playRule < 0 and $cardsCount <= -$playRule) {
      $this->gamestate->nextstate("playedCard");
      return;
    }
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
    $draw = self::getGameStateValue("drawRule");
    return ["nb" => $draw];
  }
  public function argHandLimit()
  {
    $handLimit = self::getGameStateValue("handLimit");
    return ["nb" => $handLimit];
  }
  public function argKeeperLimit()
  {
    $keeperLimit = self::getGameStateValue("keeperLimit");
    return ["nb" => $keeperLimit];
  }

  //////////////////////////////////////////////////////////////////////////////
  //////////// Game state actions
  ////////////

  public function stCardsDraw()
  {
    $player_id = self::getActivePlayerId();

    $drawRule = self::getGameStateValue("drawRule");
    $drawnCards = self::getGameStateValue("drawnCards");

    if ($drawnCards < $drawRule) {
      $this->drawCards($player_id, $drawRule - $drawnCards);
    }

    $this->gamestate->nextstate("playCards");
  }

  public function stHandLimit()
  {
    $handLimit = self::getGameStateValue("handLimit");
    $current_player_id = self::getCurrentPlayerId();
    $cardsInHand = $this->cards->countCardsInLocation(
      "hand",
      $current_player_id
    );
    if ($handLimit >= 0 and $cardsInHand > $handLimit) {
      $toDiscard = $handLimit - $cardsInHand;
      throw new BgaUserException(
        self::_("Not implemented: ") .
          "$current_player_id needs to discard $toDiscard"
      );
    }
    $this->gamestate->nextstate("");
  }

  public function stKeeperLimit()
  {
    $keeperLimit = self::getGameStateValue("keeperLimit");
    $current_player_id = self::getCurrentPlayerId();
    $keeperPlaced = $this->cards->countCardsInLocation(
      "keepers",
      $current_player_id
    );
    if ($keeperLimit >= 0 and $keeperPlaced > $keeperLimit) {
      $toDiscard = $keeperLimit - $keeperPlaced;
      throw new BgaUserException(
        self::_("Not implemented: ") .
          "$current_player_id needs to discard $toDiscard"
      );
    }
    $this->gamestate->nextstate("");
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
