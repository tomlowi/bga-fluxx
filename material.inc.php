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

$this->types = [
  1 => [
    "name" => clienttranslate("baserule"),
    "nametr" => self::_("baserule"),
    "nbcards" => 1,
  ],
  2 => [
    "name" => clienttranslate("keeper"),
    "nametr" => self::_("keeper"),
    "nbcards" => 19,
  ],
  3 => [
    "name" => clienttranslate("goal"),
    "nametr" => self::_("goal"),
    "nbcards" => 30,
  ],
  4 => [
    "name" => clienttranslate("newrule"),
    "nametr" => self::_("newrule"),
    "nbcards" => 27,
  ],
  5 => [
    "name" => clienttranslate("action"),
    "nametr" => self::_("action"),
    "nbcards" => 23,
  ],
];

$this->id_label = [
  ////// Basic Rules
  101 => [
    "name" => clienttranslate("Basic Rules"),
    "description" => clienttranslate("Draw 1, Play 1"),
  ],

  ////// Keepers
  201 => ["name" => clienttranslate("Sleep"), "description" => ""],
  202 => ["name" => clienttranslate("The Brain"), "description" => ""],
  203 => ["name" => clienttranslate("Bread"), "description" => ""],
  204 => ["name" => clienttranslate("Chocolate"), "description" => ""],
  205 => ["name" => clienttranslate("Cookies"), "description" => ""],
  206 => ["name" => clienttranslate("Milk"), "description" => ""],
  207 => ["name" => clienttranslate("Money"), "description" => ""],
  208 => ["name" => clienttranslate("The Eye"), "description" => ""],
  209 => ["name" => clienttranslate("The Moon"), "description" => ""],
  210 => ["name" => clienttranslate("The Rocket"), "description" => ""],
  211 => ["name" => clienttranslate("The Toaster"), "description" => ""],
  212 => ["name" => clienttranslate("Television"), "description" => ""],
  213 => ["name" => clienttranslate("Time"), "description" => ""],
  214 => ["name" => clienttranslate("Dreams"), "description" => ""],
  215 => ["name" => clienttranslate("Music"), "description" => ""],
  216 => ["name" => clienttranslate("The Party"), "description" => ""],
  217 => ["name" => clienttranslate("The Sun"), "description" => ""],
  218 => ["name" => clienttranslate("Love"), "description" => ""],
  219 => ["name" => clienttranslate("Peace"), "description" => ""],

  ////// Goals
  301 => [
    "name" => clienttranslate("10 Cards in Hand"),
    "description" => clienttranslate(
      "If someone has 10 or more cards in his or her hand, then the player with the most cards in hand wins. <br/> In the event of a tie, continue playing until a clear winner emerges."
    ),
  ],
  302 => [
    "name" => clienttranslate("5 Keepers"),
    "description" => clienttranslate(
      "If someone has 5 or more Keepers on the table, then the player with the most Keepers in play wins. <br/> In the event of a tie, continue playing until a clear winner emerges."
    ),
  ],
  303 => [
    "name" => clienttranslate("The Appliances"),
    "description" => clienttranslate("The Toaster + Television"),
  ],
  304 => [
    "name" => clienttranslate("Baked Goods"),
    "description" => clienttranslate("Bread + Cookies"),
  ],
  305 => [
    "name" => clienttranslate("Bed Time"),
    "description" => clienttranslate("Sleep + Time"),
  ],
  306 => [
    "name" => clienttranslate("The Brain (No TV)"),
    "description" => clienttranslate(
      "If no one has Television on the table, the player with The Brain on the table wins."
    ),
  ],
  307 => [
    "name" => clienttranslate("Bread & Chocolate"),
    "description" => clienttranslate("Bread + Chocolate"),
  ],
  308 => [
    "name" => clienttranslate("Can’t Buy Me Love"),
    "description" => clienttranslate("Money + Love"),
  ],
  309 => [
    "name" => clienttranslate("Chocolate Cookies"),
    "description" => clienttranslate("Chocolate + Cookies"),
  ],
  310 => [
    "name" => clienttranslate("Chocolate Milk"),
    "description" => clienttranslate("Chocolate + Milk"),
  ],
  311 => [
    "name" => clienttranslate("Day Dreams"),
    "description" => clienttranslate("The Sun + Dreams"),
  ],
  312 => [
    "name" => clienttranslate("Dreamland"),
    "description" => clienttranslate("Sleep + Dreams"),
  ],
  313 => [
    "name" => clienttranslate("The Eye of the Beholder"),
    "description" => clienttranslate("The Eye + Love"),
  ],
  314 => [
    "name" => clienttranslate("Great Theme Song"),
    "description" => clienttranslate("Music + Television"),
  ],
  315 => [
    "name" => clienttranslate("Hearts & Minds"),
    "description" => clienttranslate("Love + The Brain"),
  ],
  316 => [
    "name" => clienttranslate("Hippyism"),
    "description" => clienttranslate("Peace + Love"),
  ],
  317 => [
    "name" => clienttranslate("Lullaby"),
    "description" => clienttranslate("Sleep + Music"),
  ],
  318 => [
    "name" => clienttranslate("Milk & Cookies"),
    "description" => clienttranslate("Milk + Cookies"),
  ],
  319 => [
    "name" => clienttranslate("The Mind’s Eye"),
    "description" => clienttranslate("The Brain + The Eye"),
  ],
  320 => [
    "name" => clienttranslate("Night & Day"),
    "description" => clienttranslate("The Sun + The Moon"),
  ],
  321 => [
    "name" => clienttranslate("Party Snacks"),
    "description" => clienttranslate("The Party + at least 1 food Keeper"),
  ],
  322 => [
    "name" => clienttranslate("Party Time!"),
    "description" => clienttranslate("The Party + Time"),
  ],
  323 => [
    "name" => clienttranslate("Rocket Science"),
    "description" => clienttranslate("The Rocket + The Brain"),
  ],
  324 => [
    "name" => clienttranslate("Rocket to the Moon"),
    "description" => clienttranslate("The Rocket + The Moon"),
  ],
  325 => [
    "name" => clienttranslate("Squishy Chocolate"),
    "description" => clienttranslate("Chocolate + The Sun"),
  ],
  326 => [
    "name" => clienttranslate("Time is Money"),
    "description" => clienttranslate("Time + Money"),
  ],
  327 => [
    "name" => clienttranslate("Toast"),
    "description" => clienttranslate("Bread + The Toaster"),
  ],
  328 => [
    "name" => clienttranslate("Turn it Up!"),
    "description" => clienttranslate("Music + The Party"),
  ],
  329 => [
    "name" => clienttranslate("Winning the Lottery"),
    "description" => clienttranslate("Dreams + Money"),
  ],
  330 => [
    "name" => clienttranslate("World Peace"),
    "description" => clienttranslate("Dreams + Peace"),
  ],

  ////// New Rules
  401 => [
    "name" => clienttranslate("Play 2"),
    "description" => clienttranslate(
      "Replaces Play Rule <br/> Play 2 cards per turn. <br/> If you have fewer than that, play all your cards."
    ),
  ],
  402 => [
    "name" => clienttranslate("Play 3"),
    "description" => clienttranslate(
      "Replaces Play Rule <br/> Play 3 cards per turn. <br/> If you have fewer than that, play all your cards."
    ),
  ],
  403 => [
    "name" => clienttranslate("Play 4"),
    "description" => clienttranslate(
      "Replaces Play Rule <br/> Play 4 cards per turn. <br/> If you have fewer than that, play all your cards."
    ),
  ],
  404 => [
    "name" => clienttranslate("Play All"),
    "description" => clienttranslate(
      "Replaces Play Rule <br/> Play all your cards per turn."
    ),
  ],
  405 => [
    "name" => clienttranslate("Play All But 1"),
    "description" => clienttranslate(
      "Replaces Play Rule <br/> Play all but 1 of your cards.<br/> If you started with no cards in your hand and only drew 1, draw an extra card."
    ),
  ],
  406 => [
    "name" => clienttranslate("Draw 2"),
    "description" => clienttranslate(
      "Replaces Draw Rule <br/> Draw 2 cards per turn. <br/> If you just played this card, draw extra cards as needed to reach 2 cards drawn."
    ),
  ],
  407 => [
    "name" => clienttranslate("Draw 3"),
    "description" => clienttranslate(
      "Replaces Draw Rule <br/> Draw 3 cards per turn. <br/> If you just played this card, draw extra cards as needed to reach 3 cards drawn."
    ),
  ],
  408 => [
    "name" => clienttranslate("Draw 4"),
    "description" => clienttranslate(
      "Replaces Draw Rule <br/> Draw 4 cards per turn. <br/> If you just played this card, draw extra cards as needed to reach 4 cards drawn."
    ),
  ],
  409 => [
    "name" => clienttranslate("Draw 5"),
    "description" => clienttranslate(
      "Replaces Draw Rule <br/> Draw 5 cards per turn. <br/> If you just played this card, draw extra cards as needed to reach 5 cards drawn."
    ),
  ],
  410 => [
    "name" => clienttranslate("Keeper Limit 2"),
    "description" => clienttranslate(
      'Replaces Keeper Limit <br/> If it isn\'t your turn, you can only have 2 Keepers in play. Discard extras immediately. You may acquire new Keepers during your turn as long as you discard down to 2 when your turn ends.'
    ),
  ],
  411 => [
    "name" => clienttranslate("Keeper Limit 3"),
    "description" => clienttranslate(
      'Replaces Keeper Limit <br/> If it isn\'t your turn, you can only have 3 Keepers in play. Discard extras immediately. You may acquire new Keepers during your turn as long as you discard down to 3 when your turn ends.'
    ),
  ],
  412 => [
    "name" => clienttranslate("Keeper Limit 4"),
    "description" => clienttranslate(
      'Replaces Keeper Limit <br/> If it isn\'t your turn, you can only have 4 Keepers in play. Discard extras immediately. You may acquire new Keepers during your turn as long as you discard down to 4 when your turn ends.'
    ),
  ],
  413 => [
    "name" => clienttranslate("Hand Limit 0"),
    "description" => clienttranslate(
      'Replaces Hand Limit <br/> If it isn\'t your turn, you can only have 0 cards in your hand. Discard extras immediately. During your turn, this rule does not apply to you, after your turn ends, discard down to 0 cards'
    ),
  ],
  414 => [
    "name" => clienttranslate("Hand Limit 1"),
    "description" => clienttranslate(
      'Replaces Hand Limit <br/> If it isn\'t your turn, you can only have 1 card in your hand. Discard extras immediately. During your turn, this rule does not apply to you, after your turn ends, discard down to 1 card'
    ),
  ],
  415 => [
    "name" => clienttranslate("Hand Limit 2"),
    "description" => clienttranslate(
      'Replaces Hand Limit <br/> If it isn\'t your turn, you can only have 2 cards in your hand. Discard extras immediately. During your turn, this rule does not apply to you, after your turn ends, discard down to 2 cards'
    ),
  ],
  416 => [
    "name" => clienttranslate("No-Hand Bonus"),
    "description" => clienttranslate(
      "Start-of-Turn Event <br/> If empty handed, draw 3 cards before observing the current draw rule."
    ),
  ],
  417 => [
    "name" => clienttranslate("Party Bonus"),
    "description" => clienttranslate(
      "Takes Instant Effect <br/> If one player has the Party on the table, all players draw 1 extra card and play 1 extra card during their turn."
    ),
  ],
  418 => [
    "name" => clienttranslate("Poor Bonus"),
    "description" => clienttranslate(
      "Takes Instant Effect <br/> If one player has fewer Keepers in play than anyone else, the number of cards drawn by this player is increased by 1. <br/> In the event of a tie, no player receives the bonus."
    ),
  ],
  419 => [
    "name" => clienttranslate("Rich Bonus"),
    "description" => clienttranslate(
      "Takes Instant Effect <br/> If one player has more Keepers in play than anyone else, the number of cards drawn by this player is increased by 1. <br/> In the event of a tie, no player receives the bonus."
    ),
  ],
  420 => [
    "name" => clienttranslate("Double Agenda"),
    "description" => clienttranslate(
      "Takes Instant Effect <br/> A second Goal can now be played. After this, whoever plays a new Goal must choose which of the current Goal to discard. You win if you satisfy either Goal."
    ),
  ],
  421 => [
    "name" => clienttranslate("First Play Random"),
    "description" => clienttranslate(
      "Takes Instant Effect <br/> The first card you play must be chosen at random from your hand by the player on your left. Ignore this rule if the current Rule card allow you to play only one card."
    ),
  ],
  422 => [
    "name" => clienttranslate("Get On With It!"),
    "description" => clienttranslate(
      "Free action <br/> Before your final play, if you are not empty handed, you may discard your entire hand and draw 3 cards. Your turn then ends immediately."
    ),
  ],
  423 => [
    "name" => clienttranslate("Goal Mill"),
    "description" => clienttranslate(
      "Free action <br/> Once during your turn, discard as many of your Goal cards as you choose, then draw that many cards."
    ),
  ],
  424 => [
    "name" => clienttranslate("Inflation"),
    "description" => clienttranslate(
      "Takes Instant Effect <br/> Any time a numeral is seen on another card,  add one to that numeral. For example, 1 becomes 2, while one remains one. Yes, this affects the Basic Rules."
    ),
  ],
  425 => [
    "name" => clienttranslate("Mystery Play"),
    "description" => clienttranslate(
      "Free action <br/> Once durung your turn you may take the top card from the draw pile and play it immediately."
    ),
  ],
  426 => [
    "name" => clienttranslate("Recycling"),
    "description" => clienttranslate(
      "Free action <br/> Once during your turn, you may discard one of your Keepers from the table and draw 3 extra cards."
    ),
  ],
  427 => [
    "name" => clienttranslate("Swap Plays for Draws"),
    "description" => clienttranslate(
      "Takes Instant Effect <br/> During yout yurn, you may decide to play no more cards and instead draw as many cards as you have plays remaining. If Play All, draw as many cards as you hold."
    ),
  ],

  ////// Actions
  501 => [
    "name" => clienttranslate("Trash a Keeper"),
    "description" => clienttranslate(
      "Take a Keeper from in front of any player and put it on the discard pile. <br/> If no one has any Keepers in play, nothing happends when you play this card."
    ),
  ],
  502 => [
    "name" => clienttranslate("Rotate Hands"),
    "description" => clienttranslate(
      "All players pass their hands to the player next to them. <br/> You decide which direction."
    ),
  ],
  503 => [
    "name" => clienttranslate("Rules Reset"),
    "description" => clienttranslate(
      "Reset to the Basic Rules. <br/> Discard all New Rule cards, and leave only the Basic Rules in play. <br/> Do not discard the current Goal."
    ),
  ],
  504 => [
    "name" => clienttranslate("Random Tax"),
    "description" => clienttranslate(
      "Take 1 card at random from the hand of each other player and add these cardsto your own hand."
    ),
  ],
  505 => [
    "name" => clienttranslate("Rock-Paper-Scissors Showdown"),
    "description" => clienttranslate(
      'Challenge another player to a 3-round Rock-Paper-Scissors tournament. <br/> Winner takes loser\'s entire hand of cards.'
    ),
  ],
  506 => [
    "name" => clienttranslate("Trash A New Rule"),
    "description" => clienttranslate(
      "Select one of the New Rule cards in play and place it in the discard pile."
    ),
  ],
  507 => [
    "name" => clienttranslate("Use What You Take"),
    "description" => clienttranslate(
      'Take a card at random form another player\'s hand, and play it.'
    ),
  ],
  508 => [
    "name" => clienttranslate("Zap a Card!"),
    "description" => clienttranslate(
      "Choose any card in play, anywhere on the table (except for the Basic Rules) and add it to your hand."
    ),
  ],
  509 => [
    "name" => clienttranslate("Discard and Draw"),
    "description" => clienttranslate(
      "Discard your entire hand, then draw as many cards as you discarded. <br/> Do not count this card when determining how many cards to draw."
    ),
  ],
  510 => [
    "name" => clienttranslate("Draw 2 and Use ‘Em"),
    "description" => clienttranslate(
      "Set your hand aside. <br/> Draw 2 cards, play them in any order you choose, then pick up your hand and continue with your turn. <br/> This card, and all cards played because of it, are counted as a single play."
    ),
  ],
  511 => [
    "name" => clienttranslate("Draw 3, Play 2 of Them"),
    "description" => clienttranslate(
      "Set your hand aside. <br/> Draw 3 cards and play 2 them. Discard the last card, then pick up your hand and continue with your turn. <br/> This card, and all cards played because of it, are counted as a single play."
    ),
  ],
  512 => [
    "name" => clienttranslate("Empty the Trash"),
    "description" => clienttranslate(
      "Start a new discard pile with this card and shuffle the rest of the discard pile back into the draw pile."
    ),
  ],
  513 => [
    "name" => clienttranslate("Everybody gets 1"),
    "description" => clienttranslate(
      "Set your hand aside. <br/> Count the number of players in the game (including yourself). Draw enough cards to give 1 to each player, and then distribute them evenly amongst all the players. <br/> You decide who gets what."
    ),
  ],
  514 => [
    "name" => clienttranslate("Exchange Keepers"),
    "description" => clienttranslate(
      "Pick any Keeper another player has on the table and exchange it for one you have on the table. <be/> If you have no Keepers in play, or if no one else has a Keeper, nothing happens."
    ),
  ],
  515 => [
    "name" => clienttranslate("Jackpot!"),
    "description" => clienttranslate("Draw 3 extra cards!"),
  ],
  516 => [
    "name" => clienttranslate("Let’s Do That Again!"),
    "description" => clienttranslate(
      'Seatch through the discard pile. Take any Action or New Rule card you wish and immediately play it. <br/> Anyone may look through the discard pile at any time, but the order of what\'s in the pile should never be changed.'
    ),
  ],
  517 => [
    "name" => clienttranslate("Let’s Simplify"),
    "description" => clienttranslate(
      "Discard your choice of up to half (rounded up) of the New Rule cards in play."
    ),
  ],
  518 => [
    "name" => clienttranslate("No Limits"),
    "description" => clienttranslate(
      "Discard all Hand and Keeper Limits currently in play."
    ),
  ],
  519 => [
    "name" => clienttranslate("Trade Hands"),
    "description" => clienttranslate(
      "Trade your hand for the hand of one of your opponents. <br/> This is one of those times when you can get something for nothing!"
    ),
  ],
  520 => [
    "name" => clienttranslate("Share the Wealth"),
    "description" => clienttranslate(
      "Gather up all the Keepers on the table, shuffle them together, and deal them back out to all players, starting with yourself. These go immediately into play in front of their new owners. <br/> Everyone will probably end up with a different number of Keepers in play than they started with."
    ),
  ],
  521 => [
    "name" => clienttranslate("Steal a Keeper"),
    "description" => clienttranslate(
      "Steal a Keeper from in front of another player, and add it to your collenction of Keepers on the table."
    ),
  ],
  522 => [
    "name" => clienttranslate("Take Another Turn"),
    "description" => clienttranslate(
      "Take another tuen as soon as you finish this one. <br/> The maximum number of turns you can take in a row using this card is two."
    ),
  ],
  523 => [
    "name" => clienttranslate("Today’s Special!"),
    "description" => clienttranslate(
      'Set your hand aside and draw 3 cards. If today is your birthday, play all 3 cards. If today is a holiday or special anniversary, play 2 of the cards. If it\'s just another day, play only 1 card. Discard the remainder.'
    ),
  ],
];
