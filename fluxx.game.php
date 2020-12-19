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

require_once APP_GAMEMODULE_PATH . 'module/table/table.game.php';

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

        self::initGameStateLabels(array(
            "cardDrawn" => 10,
            "cardPlayed" => 11,
            "cardToDraw" => 20,
            "cardToPlay" => 21,
            "handLimit" => 22,
            "keeperLimit" => 23,
        ));
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
    protected function setupNewGame($players, $options = array())
    {
        // Set the colors of the players with HTML color code
        // The default below is red/green/blue/orange/brown
        // The number of colors defined here must correspond to the maximum number of players allowed for the gams
        $gameinfos = self::getGameinfos();
        $default_colors = $gameinfos['player_colors'];

        // Create players
        // Note: if you added some extra field on "player" table in the database (dbmodel.sql), you can initialize it there.
        $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES ";
        $values = array();
        foreach ($players as $player_id => $player) {
            $color = array_shift($default_colors);
            $values[] = "('" . $player_id . "','$color','" . $player['player_canal'] . "','" . addslashes($player['player_name']) . "','" . addslashes($player['player_avatar']) . "')";
        }
        $sql .= implode($values, ',');
        self::DbQuery($sql);
        self::reattributeColorsBasedOnPreferences($players, $gameinfos['player_colors']);
        self::reloadPlayersBasicInfos();

        /************ Start the game initialization *****/

        // Init global values with their initial values
        self::setGameStateInitialValue('cardDrawn', 0);
        self::setGameStateInitialValue('cardPlayed', 0);
        self::setGameStateInitialValue('cardToPlay', 1);
        self::setGameStateInitialValue('cardToDraw', 1);
        self::setGameStateInitialValue('handLimit', -1);
        self::setGameStateInitialValue('keeperLimit', -1);

        // Create cards
        $cards = array();
        foreach ($this->types as $type_id => $type) {
            // action, keeper, objective, newrule, baserule
            for ($number = 1; $number <= $this->types[$type_id]['nbcards']; $number++) {
                $cards[] = array('type' => $type_id, 'type_arg' => $number, 'nbr' => 1);
            }
        }

        $this->cards->createCards($cards, 'deck');
        $this->cards->autoreshuffle = true;
        $this->cards->autoreshuffle_trigger = array('obj' => $this, 'method' => 'deckAutoReshuffle');
        $sql = "SELECT card_id FROM card WHERE card_type = 1 AND card_type_arg = 1";
        $result = self::DbQuery($sql);
        $row = mysql_fetch_assoc($result);
        $this->cards->moveCard($row['card_id'], 'rules');

        // Shuffle deck
        $this->cards->shuffle('deck');
        // Deal 3 cards to each players
        $players = self::loadPlayersBasicInfos();
        foreach ($players as $player_id => $player) {
            $cards = $this->cards->pickCards(3, 'deck', $player_id);
        }

        // Init game statistics
        // (note: statistics used in this file must be defined in your stats.inc.php file)
        //self::initStat( 'table', 'table_teststat1', 0 );    // Init a table statistics
        //self::initStat( 'player', 'player_teststat1', 0 );  // Init a player statistics (for all players)

        // TODO: setup the initial game situation here

        // Activate first player (which is in general a good idea :) )
        $this->activeNextPlayer();

        /************ End of the game initialization *****/
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
        $result = array();

        $current_player_id = self::getCurrentPlayerId(); // !! We must only return informations visible by this player !!

        // Get information about players
        // Note: you can retrieve some extra field you added for "player" table in "dbmodel.sql" if you need it.
        $sql = "SELECT player_id id, player_score score FROM player ";
        $result['players'] = self::getCollectionFromDb($sql);

        // Cards in player hand
        $result['hand'] = $this->cards->getCardsInLocation('hand', $current_player_id);

        // Cards in the players keeper section
        $result['keepers'] = $this->cards->getCardsInLocation('keepers');

        // Cards in the rules & goal section
        $result['rules'] = $this->cards->getCardsInLocation('rules');

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
        // TODO: compute and return the game progression

        return 0;
    }

//////////////////////////////////////////////////////////////////////////////
    //////////// Utility functions
    ////////////

    /*
    In this space, you can put any utility methods useful for your game logic
     */

    public function drawCards($draw)
    {
        $cardsDrawn = $this->cards->pickCards($draw, 'deck', $player_id);
        $previouslyDrawn = self::getGameStateValue('cardDrawn');
        self::setGameStateValue('cardDrawn', $previouslyDrawn + $draw);

        self::notifyPlayer($player_id, 'cardDrawn', '', array('cardsDrawn' => $cardsDrawn));

        self::notifyAllPlayers("numberCardsDrawn", clienttranslate('${player_name} draws <b>${nb}</b> card(s)'), array(
            'player_id' => $player_id,
            'player_name' => self::getActivePlayerName(),
            'nb' => $draw,
        ));
    }

    public function deckAutoReshuffle()
    {
        self::notifyAllPlayers(
            'reshuffle',
            clienttranslate('${player_name} reshuffles the discard pile into the deck to draw'),
            array(
                'player_id' => $player_id,
            )
        );
    }

    public function checkCardInHand($card_id, $player_id)
    {
        $cards = $this->cards->getPlayerHand($player_id);

        return $cards[$card_id] != null;
    }

    public function checkWin()
    {
        $rules = $this->cards->getCardsInLocation('rules');
        $goals = array();

        $players = self::loadPlayersBasicInfos();
        $winner_id = null;

        foreach ($rules as $card_id => $card) {
            if ($card['type'] == 3) {

                switch ($card['type_arg']) {
                    case 1:
                        // If someone has 10 or more cards in his or her hand,
                        // then the player with the most cards in hand wins.
                        // In the event of a tie, continue playing until a clear winner emerges.
                        $maxCards = -1;
                        $cardCounts = array();
                        foreach ($players as $player_id => $player) {
                            // Count each player hand cards
                            $nbCards = $this->cards->countCardsInLocation('hand', $player_id);
                            if ($nbCards >= 10) {
                                if ($nbCards > $maxCards) {
                                    $cardCounts = array();
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

                            $newScores = self::getCollectionFromDb("SELECT player_id, player_score FROM player", true);
                            self::notifyAllPlayers("newScores", '', array('newScores' => $newScores));

                            self::notifyAllPlayers("win", clienttranslate('${player_name} wins with ${nbr} cards in hand', array(
                                'player_id' => $winner_id,
                                'player_name' => $players[$winner_id],
                                'nbr' => $maxCards,
                            )));
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
                            $nbKeepers = $this->cards->countCardsInLocation('keepers', $player_id);
                            if ($nbKeepers >= 5) {
                                if ($nbKeepers > $maxkeepers) {
                                    $keeperCounts = array();
                                    $maxkeepers = $nbKeepers;
                                    $keeperCounts[] = $player_id;
                                } else if ($nbKeepers == $maxkeepers) {
                                    $keeperCounts[] = $player_id;
                                }

                            }
                        }

                        if (count($keeperCounts) == 1) {
                            // We have one winner, no tie
                            $winner_id = $keeperCounts[0];
                            $sql = "UPDATE player SET player_score=1  WHERE player_id='$winner_id'";
                            self::DbQuery($sql);

                            $newScores = self::getCollectionFromDb("SELECT player_id, player_score FROM player", true);
                            self::notifyAllPlayers("newScores", '', array('newScores' => $newScores));

                            self::notifyAllPlayers(
                                "win",
                                "",
                                clienttranslate(
                                    '${player_name} wins with ${nbr} keepers in play',
                                    array(
                                        'player_id' => $winner_id,
                                        'player_name' => $players[$winner_id],
                                        'nbr' => $maxkeepers,
                                    )
                                )
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
                    $winning_card = ($card['type'] * 100) + ($card['type_arg'] - 0);

                    $sql = "UPDATE player SET player_score=1  WHERE player_id='$winner_id'";
                    self::DbQuery($sql);

                    $newScores = self::getCollectionFromDb("SELECT player_id, player_score FROM player", true);
                    self::notifyAllPlayers("newScores", '', array('newScores' => $newScores));

                    self::notifyAllPlayers("win", "", clienttranslate('${player_name} wins with <b>${card_name}</b>', array(
                        'player_id' => $winner_id,
                        'player_name' => $players[$winner_id],
                        'card_name' => $this->id_label[$winning_card]['name'],
                    )));
                    $this->gamestate->nextState("endGame");
                    return true;
                }
            }
        }

        return false;

    }

    public function checkTwoKeepersWin($keeper_nbr_A, $keeper_nbr_B, $without_B = false)
    {
        //getCardsOfTypeInLocation( $type, $type_arg=null, $location, $location_arg = null )
        $keeper_A = $this->cards->getCardsOfTypeInLocation(2, $keeper_nbr_A, 'keepers', null);
        $keeper_B = $this->cards->getCardsOfTypeInLocation(2, $keeper_nbr_B, 'keepers', null);

        if ($without_B) {
            if (count($keeper_A) != 0 && count($keeper_B) == 0) {
                return $keeper_A['location_arg'];
            } else {
                return null;
            }
        } else {

            if (count($keeper_A) != 0 && count($keeper_B) != 0) {
                $location_arg_A = null;
                $location_arg_B = null;
                foreach ($keeper_A as $card_id => $card) {
                    $location_arg_A = $card['location_arg'];
                }
                foreach ($keeper_B as $card_id => $card) {
                    $location_arg_B = $card['location_arg'];
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

        $rules = $this->cards->getCardsInLocation('rules');
        $card_args = array();
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
            if (in_array($card['type_arg'], $card_args)) {
                $this->cards->moveCard($card_id, 'discard');
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

    public function playCard($card_id, $card_unique_id)
    {
        // Check that this is the player's turn and that it is a "possible action" at this game state (see states.inc.php)
        self::checkAction('playCard');

        $player_id = self::getActivePlayerId();
        $card = $this->cards->getCard($card_id);
        $card_name = $this->id_label[$card_unique_id]['name'];

        if ($this->checkCardInHand($card_id, $player_id) == false) {

            self::notifyPlayer($player_id, 'cardNotPresent', 'You do not have card <b>' . $card_name . '</b>', array());
            return;
        }
        $card_type = $card['type'];

        switch ($card_type) {
            case 2:
                $this->cards->moveCard($card_id, 'keepers', $player_id);
                break;
            case 3:
            case 4:
                switch ($card['type_arg']) {
                    case 1:
                        //// Play 2 ////

                        // Replaces Play Rule
                        $this->discardRule(1);
                        // Play 2 cards per turn.
                        self::setGameStateValue('cardToPlay', 2);
                        break;
                    case 2:
                        //// Play 3 ////

                        // Replaces Play Rule
                        $this->discardRule(1);
                        // Play 3 cards per turn.
                        self::setGameStateValue('cardToPlay', 3);
                        break;
                    case 3:
                        //// Play 4 ////

                        // Replaces Play Rule
                        $this->discardRule(1);
                        // Play 4 cards per turn.
                        self::setGameStateValue('cardToPlay', 4);
                        break;
                    case 4:
                        //// Play All ////

                        // Replaces Play Rule
                        $this->discardRule(1);
                        // Play all cards per turn.
                        self::setGameStateValue('cardToPlay', 200);
                        break;
                    case 5:
                        //// Play All but 1 ////

                        // Replaces Play Rule
                        $this->discardRule(1);
                        // Play all cards per turn.
                        self::setGameStateValue('cardToPlay', -1);
                        break;
                    case 6:
                        //// Draw 2 ////

                        // Replaces Draw Rule
                        $this->discardRule(2);

                        $drawn = $self::getGameStateValue('cardDrawn');
                        if ($drawn < 2) {
                            $this->drawCards(2 - $drawn);
                        }

                        // Play all cards per turn.
                        self::setGameStateValue('cardToDraw', 2);
                        break;
                    case 7:
                        //// Draw 3 ////

                        // Replaces Draw Rule
                        $this->discardRule(2);

                        $drawn = $self::getGameStateValue('cardDrawn');
                        if ($drawn < 3) {
                            $this->drawCards(3 - $drawn);
                        }

                        // Play all cards per turn.
                        self::setGameStateValue('cardToDraw', 3);
                        break;
                    case 8:
                        //// Draw 4 ////

                        // Replaces Draw Rule
                        $this->discardRule(2);

                        $drawn = $self::getGameStateValue('cardDrawn');
                        if ($drawn < 4) {
                            $this->drawCards(4 - $drawn);
                        }

                        // Play all cards per turn.
                        self::setGameStateValue('cardToDraw', 4);
                        break;
                    case 9:
                        //// Draw 5 ////

                        // Replaces Draw Rule
                        $this->discardRule(2);

                        $drawn = $self::getGameStateValue('cardDrawn');
                        if ($drawn <= 5) {
                            $this->drawCards(5 - $drawn);
                        }

                        // Play all cards per turn.
                        self::setGameStateValue('cardToDraw', 5);
                        break;
                    case 10:
                        //
                        break;
                    case 11:
                        //
                        break;
                    case 12:
                        //
                        break;
                    case 13:
                        //
                        break;
                    case 14:
                        //
                        break;
                    case 15:
                        //
                        break;
                    case 16:
                        //
                        break;
                    case 17:
                        //
                        break;
                    case 18:
                        //
                        break;
                    case 19:
                        //
                        break;
                    case 20:
                        //
                        break;
                    case 21:
                        //
                        break;
                    case 22:
                        //
                        break;
                    case 23:
                        //
                        break;
                    case 24:
                        //
                        break;
                    case 25:
                        //
                        break;
                    case 26:
                        //
                        break;
                    case 27:
                        //
                        break;
                }

                $this->cards->moveCard($card_id, 'rules');
                break;
            case 5:
                $this->cards->moveCard($card_id, 'discard');
                break;
            default:
                throw new BgaUserException(self::_("Not implemented: ") . "Card type $card_type does not exist");
                break;
        }

        // Add your game logic to play a card there
        $cardsToPlay = self::getGameStateValue('cardToPlay');
        $cardsPlayed = self::getGameStateValue('cardPlayed');

        self::setGameStateValue('cardPlayed', ($cardsPlayed + 1));

        // Notify all players about the card played
        self::notifyAllPlayers("cardPlayed", clienttranslate('${player_name} plays <b>${card_name}<b>'), array(
            'player_id' => $player_id,
            'player_name' => self::getActivePlayerName(),
            'card_name' => $card_name,
            'card_id' => $card_id,
            'card_unique_id' => $card_unique_id,
        ));

        $cards_in_hand = $this->cards->countCardsInLocation('hand', $player_id);

        // Has the game been won ?
        if (!$this->checkWin()) {
            // If not, is Play All But 1 in play ?
            // If not, did the player play enough cards (or hand empty) ?
            if (($cardsToPlay == -1 && $cards_in_hand == 1)
                || $cardsPlayed + 1 >= $cardsToPlay
                || $cards_in_hand == 0) {
                self::setGameStateValue('cardPlayed', 0);
                $this->gamestate->nextstate("playedCards");
            }
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

    public function argDrawCards()
    {
        $draw = self::getGameStateValue('cardToDraw');
        return array('nb' => $draw);
    }
    public function argPlayCards()
    {
        $max = self::getGameStateValue('cardToPlay');
        $played = self::getGameStateValue('cardPlayed');
        if ($max == 200) {
            return array('nb' => 'All your');
        }
        if ($max == -1) {
            return array('nb' => 'All but 1 of your');
        }
        return array('nb' => $max - $played);
    }
    public function argHandLimit()
    {
        $handLimit = self::getGameStateValue('handLimit');
        return array('nb' => $handLimit);
    }
    public function argKeeperLimit()
    {
        $keeperLimit = self::getGameStateValue('keeperLimit');
        return array('nb' => $keeperLimit);
    }

//////////////////////////////////////////////////////////////////////////////
    //////////// Game state actions
    ////////////

    /*
    Here, you can create methods defined as "game state actions" (see "action" property in states.inc.php).
    The action method of state X is called everytime the current game state is set to X.
     */

    public function stDrawCards()
    {
        $player_id = self::getActivePlayerId();

        self::setGameStateValue('cardDrawn', 0);
        $draw = self::getGameStateValue('cardToDraw');

        $this->drawCards($draw);

        $this->gamestate->nextstate("drawnCards");

    }

    public function stHandLimit()
    {
        $handLimit = self::getGameStateValue('handLimit');
        $current_player_id = self::getCurrentPlayerId();
        $cardsInHand = $this->cards->countCardsInLocation('hand', $current_player_id);
        if ($handLimit >= 0 and $cardsInHand > $handLimit) {
            $toDiscard = $handLimit - $cardsInHand;
            throw new BgaUserException(self::_("Not implemented: ") . "$current_player_id needs to discard $toDiscard");
        }
        $this->gamestate->nextstate("");

    }

    public function stKeeperLimit()
    {
        $keeperLimit = self::getGameStateValue('keeperLimit');
        $current_player_id = self::getCurrentPlayerId();
        $keeperPlaced = $this->cards->countCardsInLocation('keepers', $current_player_id);
        if ($keeperLimit >= 0 and $keeperPlaced > $keeperLimit) {
            $toDiscard = $keeperLimit - $keeperPlaced;
            throw new BgaUserException(self::_("Not implemented: ") . "$current_player_id needs to discard $toDiscard");
        }
        $this->gamestate->nextstate("");

    }

    public function stNextPlayer()
    {
        $player_id = self::activeNextPlayer();
        self::giveExtraTime($player_id);
        $this->gamestate->nextState('nextPlayer');
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
        $statename = $state['name'];

        if ($state['type'] === "activeplayer") {
            switch ($statename) {
                default:
                    $this->gamestate->nextState("zombiePass");
                    break;
            }

            return;
        }

        if ($state['type'] === "multipleactiveplayer") {
            // Make sure player is in a non blocking status for role turn
            $this->gamestate->setPlayerNonMultiactive($active_player, '');

            return;
        }

        throw new feException("Zombie mode not supported at this game state: " . $statename);
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
