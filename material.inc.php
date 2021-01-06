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
 * material.inc.php
 *
 * fluxx game material description
 *
 * Here, you can describe the material of your game with PHP variables.
 *
 * This file is loaded in your game logic class constructor, ie these variables
 * are available everywhere in your game logic code.
 *
 */

$this->typesDefinitions = [
  [
    "label" => "keeper",
    "name" => self::_("keeper"),
    "nbCards" => 19,
  ],
  [
    "label" => "goal",
    "name" => self::_("goal"),
    "nbCards" => 30,
  ],
  [
    "label" => "rule",
    "name" => self::_("rule"),
    "nbCards" => 27,
  ],
  [
    "label" => "action",
    "name" => self::_("action"),
    "nbCards" => 23,
  ],
];
