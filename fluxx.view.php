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
 * fluxx.view.php
 *
 * This is your "view" file.
 *
 * The method "build_page" below is called each time the game interface is displayed to a player, ie:
 * _ when the game starts
 * _ when a player refreshes the game page (F5)
 *
 * "build_page" method allows you to dynamically modify the HTML generated for the game interface. In
 * particular, you can set here the values of variables elements defined in fluxx_fluxx.tpl (elements
 * like {MY_VARIABLE_ELEMENT}), and insert HTML block elements (also defined in your HTML template file)
 *
 * Note: if the HTML of your game interface is always the same, you don't have to place anything here.
 *
 */

require_once APP_BASE_PATH . "view/common/game.view.php";

class view_fluxx_fluxx extends game_view
{
    public function getGameName()
    {
        return "fluxx";
    }
    public function build_page($viewArgs)
    {
        // Get players & players number
        $players = $this->game->loadPlayersBasicInfos();
        $players_nbr = count($players);

        /*********** Place your code below:  ************/

        $template = self::getGameName() . "_" . self::getGameName();

        // This will inflate our player block with actual player data
        $this->page->begin_block($template, "player");
        foreach ($players as $player_id => $info) {
            $this->page->insert_block(
                "player",
                array(
                    "PLAYER_ID" => $player_id,
                    "PLAYER_NAME" => $players[$player_id]['player_name'],
                    "PLAYER_COLOR" => $players[$player_id]['player_color'],
                ));

        }
        $this->tpl["MY_HAND"] = self::_("My hand");

        /*********** Do not change anything below this line  ************/
    }
}
