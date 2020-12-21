/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * fluxx implementation : © Alexandre Spaeth <alexandre.spaeth@hey.com> & Julien Rossignol <tacotaco.dev@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * fluxx.js
 *
 * fluxx user interface script
 *
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

define([
  "dojo",
  "dojo/_base/declare",
  "ebg/core/gamegui",
  "ebg/counter",
  "ebg/stock",
], function (dojo, declare) {
  return declare("bgagame.fluxx", ebg.core.gamegui, {
    constructor: function () {
      this.CARD_WIDTH = 166;
      this.CARD_HEIGHT = 258;
      this.CARDS_SPRITES_PATH = g_gamethemeurl + "img/cards.png";
      this.CARDS_SPRITES_PER_ROW = 17;

      this.KEEPER_WIDTH = 83;
      this.KEEPER_HEIGHT = 129;
      this.KEEPERS_SPRITES_PATH = g_gamethemeurl + "img/keepers.png";
      this.KEEPERS_SPRITES_PER_ROW = 10;

      this.CARDS_TYPES = {
        keeper: { count: 19, spriteOffset: 0, materialOffset: 1 },
        goal: { count: 30, spriteOffset: 19, materialOffset: 101 },
        rule: { count: 27, spriteOffset: 19 + 30, materialOffset: 201 },
        action: { count: 23, spriteOffset: 19 + 30 + 27, materialOffset: 301 },
      };
    },

    /*
            setup:
            
            This method sets up the game user interface according to the current game 
            situation specified in parameters.
            
            The method is called each time the game interface is displayed to a player, ie:
            _ when the game starts
            _ when a player refreshes the game page (F5)
            
            "gamedatas" argument contains all datas retrieved by your "getAllDatas" PHP method.
        */
    setup: function (gamedatas) {
      // Setup all stocks and restore existing state
      this.handStock = this.createCardStock("handStock", 1, [
        "keeper",
        "goal",
        "rule",
        "action",
      ]);
      this.addCardsToStock(this.handStock, this.gamedatas.hand);

      this.rulesStock = this.createCardStock("rulesStock", 0, ["rule"]);
      this.addCardsToStock(this.rulesStock, this.gamedatas.rules);

      this.goalsStock = this.createCardStock("goalsStock", 0, ["goal"]);
      this.addCardsToStock(this.goalsStock, this.gamedatas.goals);

      this.keepersStock = {};
      for (var player_id in gamedatas.players) {
        // Setting up player keepers stocls
        this.keepersStock[player_id] = this.createKeepersStock(
          "keepersStock" + player_id
        );
        this.addCardsToStock(
          this.keepersStock[player_id],
          this.gamedatas.keepers[player_id]
        );

        // Setting up player boards
        var player_board_div = $("player_board_" + player_id);
        dojo.place(
          this.format_block("jstpl_player_board", {
            id: player_id,
          }),
          player_board_div
        );

        this.setPlayerBoardHandCount(
          player_id,
          this.gamedatas.handsCount[player_id]
        );
        this.setPlayerBoardKeepersCount(
          player_id,
          this.gamedatas.keepers[player_id].length
        );
      }

      dojo.connect(
        this.handStock,
        "onChangeSelection",
        this,
        "onSelectHandCard"
      );

      // Setup game notifications to handle (see "setupNotifications" method below)
      this.setupNotifications();

      console.log("Setup completed!");
    },

    ///////////////////////////////////////////////////
    //// Game & client states

    // onEnteringState: this method is called each time we are entering into a new game state.
    //                  You can use this method to perform some user interface changes at this moment.
    //
    onEnteringState: function (stateName, args) {
      this.currentState = stateName;

      console.log("Entering state: " + stateName);

      switch (stateName) {
        /* Example:
            
            case 'myGameState':
            
                // Show some HTML block at this game state
                dojo.style( 'my_html_block_id', 'display', 'block' );
                
                break;
           */

        case "dummmy":
          break;
      }
    },

    // onLeavingState: this method is called each time we are leaving a game state.
    //                 You can use this method to perform some user interface changes at this moment.
    //
    onLeavingState: function (stateName) {
      console.log("Leaving state: " + stateName);

      switch (stateName) {
        /* Example:
            
            case 'myGameState':
            
                // Hide the HTML block we are displaying only during this game state
                dojo.style( 'my_html_block_id', 'display', 'none' );
                
                break;
           */

        case "dummmy":
          break;
      }
    },

    // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
    //                        action status bar (ie: the HTML links in the status bar).
    //
    onUpdateActionButtons: function (stateName, args) {
      console.log("onUpdateActionButtons: " + stateName);

      if (this.isCurrentPlayerActive()) {
        switch (
          stateName
          /*               
                 Example:
 
                 case 'myGameState':
                    
                    // Add 3 action buttons in the action status bar:
                    
                    this.addActionButton( 'button_1_id', _('Button 1 label'), 'onMyMethodToCall1' ); 
                    this.addActionButton( 'button_2_id', _('Button 2 label'), 'onMyMethodToCall2' ); 
                    this.addActionButton( 'button_3_id', _('Button 3 label'), 'onMyMethodToCall3' ); 
                    break;
*/
        ) {
        }
      }
    },

    ////
    // Utility methods

    playKeeper: function (player_id, card) {
      console.log("Player", player_id, "plays keeper", card);
      if (this.isCurrentPlayerActive()) {
        this.keepersStock[player_id].addToStockWithId(
          card.type_arg,
          card.id,
          "handStock_item_" + card.id
        );
        this.handStock.removeFromStockById(card.id);
      } else {
        this.keepersStock[player_id].addToStockWithId(
          card.type_arg,
          card.id,
          "player_board_" + player_id
        );
      }
    },

    playCard: function (player_id, card_id) {
      var type = Math.floor(uniqueId / 100);
      var number = uniqueId % 100;
      var card_origin;

      if (player_id != this.player_id) {
        card_origin = "overall_player_board_" + player_id;
      } else {
        if ($("myhand_item_" + card_id)) {
          card_origin = $("myhand_item_" + card_id);
        }
      }

      if (type == this.cardtypes.keeper.id_type) {
        this.keepersStock[player_id].addToStockWithId(
          uniqueId,
          card_id,
          card_origin
        );
      }
      if (
        [
          this.cardtypes.baserule.id_type,
          this.cardtypes.goal.id_type,
          this.cardtypes.newrule.id_type,
        ].includes(type)
      ) {
        this.rulesSection.addToStockWithId(uniqueId, card_id, card_origin);
      }
      this.handStock.removeFromStockById(card_id);
    },

    createCardStock: function (elem, mode, types) {
      var stock = new ebg.stock();
      stock.create(this, $(elem), this.CARD_WIDTH, this.CARD_HEIGHT);
      stock.image_items_per_row = this.CARDS_SPRITES_PER_ROW;

      for (var type of types) {
        var count = this.CARDS_TYPES[type].count;
        var spriteOffset = this.CARDS_TYPES[type].spriteOffset;
        var materialOffset = this.CARDS_TYPES[type].materialOffset;

        for (var i = 0; i < count; i++) {
          stock.addItemType(
            materialOffset + i,
            materialOffset + i,
            this.CARDS_SPRITES_PATH,
            spriteOffset + i
          );
        }
      }

      stock.setSelectionMode(mode);
      return stock;
    },

    createKeepersStock: function (elem) {
      var stock = new ebg.stock();
      stock.create(this, $(elem), this.KEEPER_WIDTH, this.KEEPER_HEIGHT);
      stock.image_items_per_row = this.KEEPERS_SPRITES_PER_ROW;

      var count = this.CARDS_TYPES.keeper.count;
      var spriteOffset = this.CARDS_TYPES.keeper.spriteOffset;
      var materialOffset = this.CARDS_TYPES.keeper.materialOffset;

      for (var i = 0; i < count; i++) {
        stock.addItemType(
          materialOffset + i,
          materialOffset + i,
          this.KEEPERS_SPRITES_PATH,
          spriteOffset + i
        );
      }

      stock.setSelectionMode(1);
      return stock;
    },

    addCardsToStock: function (stock, cards) {
      for (var card_id in cards) {
        var card = cards[card_id];
        stock.addToStockWithId(card.type_arg, card.id);
      }
    },

    setPlayerBoardHandCount: function (player_id, count) {
      $("handCount" + player_id).innerHTML = count;
    },

    setPlayerBoardKeepersCount: function (player_id, count) {
      $("keepersCount" + player_id).innerHTML = count;
    },

    ///////////////////////////////////////////////////
    //// Player's action

    onSelectHandCard: function () {
      var items = this.handStock.getSelectedItems();

      console.log(items, this.currentState);

      if (items.length == 0) {
        return;
      }

      if (this.checkAction("playCard", true)) {
        // Play a card
        this.ajaxcall(
          "/" + this.game_name + "/" + this.game_name + "/playCard.html",
          {
            card_id: items[0].id,
            card_definition_id: items[0].type,
            lock: true,
          },
          this,
          function (result) {},
          function (is_error) {}
        );

        this.handStock.unselectAll();
      } else if (this.checkAction("discardCard")) {
        // Discard a card
      } else {
        this.handStock.unselectAll();
      }
    },

    onRuleSelectionChanged: function () {
      this.rulesSection.unselectAll();
    },

    onKeeperSelectionChanged: function () {
      for (const player_id in this.keepersStock) {
        this.keepersStock[player_id].unselectAll();
      }
    },

    ///////////////////////////////////////////////////
    //// Reaction to cometD notifications

    /*
            setupNotifications:
            
            In this method, you associate each of your game notifications with your local method to handle it.
            
            Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                  your fluxx.game.php file.
        
        */
    setupNotifications: function () {
      dojo.subscribe("cardDrawnCurrentPlayer", this, "notif_cardDrawn");
      dojo.subscribe("cardDrawnOther", this, "notif_cardDrawnOther");

      dojo.subscribe("keeperPlayed", this, "notif_keeperPlayed");

      dojo.subscribe("newScores", this, "notif_newScores");
    },

    notif_cardDrawn: function (notif) {
      for (var card of notif.args.cardsDrawn) {
        this.handStock.addToStockWithId(card.type_arg, card.id);
      }
    },

    notif_cardDrawnOther: function (notif) {
      this.setPlayerBoardHandCount(notif.args.player_id, notif.args.handCount);
    },

    notif_keeperPlayed: function (notif) {
      var player_id = notif.args.player_id;
      console.log("notif_keeperPlayed", notif);
      this.playKeeper(player_id, notif.args.card);
      this.setPlayerBoardHandCount(player_id, notif.args.handCount);
      this.setPlayerBoardKeepersCount(
        player_id,
        this.keepersStock[player_id].count()
      );
    },

    notif_newScores: function (notif) {
      // Update players' scores
      for (var player_id in notif.args.newScores) {
        this.scoreCtrl[player_id].toValue(notif.args.newScores[player_id]);
      }
    },
  });
});
