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

  g_gamethemeurl + "modules/js/game.js",

  g_gamethemeurl + "modules/js/cardTrait.js",

  g_gamethemeurl + "modules/js/states/playCards.js",
  g_gamethemeurl + "modules/js/states/handLimit.js",
  g_gamethemeurl + "modules/js/states/keepersLimit.js",
], function (dojo, declare) {
  return declare(
    "bgagame.fluxx",
    [customgame.game, fluxx.cardTrait, fluxx.states.playCards],
    {
      constructor: function () {
        this._notifications = [];

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
          action: {
            count: 23,
            spriteOffset: 19 + 30 + 27,
            materialOffset: 301,
          },
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
        this.handStock = this.createCardStock("handStock", [
          "keeper",
          "goal",
          "rule",
          "action",
        ]);
        this.addCardsToStock(this.handStock, this.gamedatas.hand);

        this.discardStock = this.createCardStock("discardStock", [
          "keeper",
          "goal",
          "rule",
          "action",
        ]);
        if (this.gamedatas.discard) {
          this.addCardsToStock(this.discardStock, [this.gamedatas.discard]);
        }
        this.discardStock.setOverlap(0.00001);
        this.discardStock.item_margin = 0;

        this.deckCounter = new ebg.counter();
        this.deckCounter.create("deckCount");
        this.discardCounter = new ebg.counter();
        this.discardCounter.create("discardCount");
        this.deckCounter.toValue(this.gamedatas.deckCount);
        this.discardCounter.toValue(this.gamedatas.discardCount);

        this.rulesStock = {};

        this.rulesStock.drawRule = this.createCardStock("drawRuleStock", [
          "rule",
        ]);
        this.rulesStock.playRule = this.createCardStock("playRuleStock", [
          "rule",
        ]);
        this.rulesStock.others = this.createCardStock("othersStock", ["rule"]);
        this.addCardsToStock(
          this.rulesStock.drawRule,
          this.gamedatas.rules.drawRule
        );
        this.addCardsToStock(
          this.rulesStock.playRule,
          this.gamedatas.rules.playRule
        );
        this.addCardsToStock(
          this.rulesStock.others,
          this.gamedatas.rules.handLimit
        );
        this.addCardsToStock(
          this.rulesStock.others,
          this.gamedatas.rules.keepersLimit
        );
        this.addCardsToStock(
          this.rulesStock.others,
          this.gamedatas.rules.others
        );

        this.goalsStock = this.createCardStock("goalsStock", ["goal"]);
        this.addCardsToStock(this.goalsStock, this.gamedatas.goals);
        this.goalsStock.setOverlap(50, 0);

        this.keepersStock = {};
        this.handCounter = {};
        this.keepersCounter = {};
        for (var player_id in gamedatas.players) {
          // Setting up player keepers stocls
          this.keepersStock[player_id] = this.createKeepersStock(
            "keepersStock" + player_id,
            0
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

          this.handCounter[player_id] = new ebg.counter();
          this.keepersCounter[player_id] = new ebg.counter();

          this.handCounter[player_id].create("handCount" + player_id);
          this.keepersCounter[player_id].create("keepersCount" + player_id);

          this.handCounter[player_id].toValue(
            this.gamedatas.handsCount[player_id]
          );
          this.keepersCounter[player_id].toValue(
            this.keepersStock[player_id].count()
          );
        }

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
          case "playCards":
            this.onEnteringStatePlayCards(args);
            break;

          case "handLimit":
            this.onEnteringStateHandLimit(args);
            break;

          case "keeperLimit":
            this.onEnteringStateKeepersLimit(args);
            break;

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
          case "playCards":
            this.onLeavingStatePlayCards();
            break;

          case "handLimit":
            this.onLeavingStateHandLimit();
            break;

          case "keeperLimit":
            this.onLeavingStateKeepersLimit();
            break;

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
          switch (stateName) {
            case "playCards":
              this.onUpdateActionButtonsPlayCards(args);
              break;
            case "handLimit":
              this.onUpdateActionButtonsHandLimit(args);
              break;
            case "keeperLimit":
              this.onUpdateActionButtonsKeepersLimit(args);
              break;
          }
        }
      },

      ////
      // Utility methods
      ajaxAction: function (action, args) {
        if (!args) {
          args = [];
        }
        if (!args.hasOwnProperty("lock")) {
          args.lock = true;
        }
        var name = this.game_name;
        this.ajaxcall(
          "/" + name + "/" + name + "/" + action + ".html",
          args,
          this,
          function (result) {},
          function (is_error) {}
        );
      },

      createCardStock: function (elem, types) {
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

        stock.setSelectionMode(0);
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

        stock.setSelectionMode(2);
        return stock;
      },

      addCardsToStock: function (stock, cards) {
        for (var card_id in cards) {
          var card = cards[card_id];
          stock.addToStockWithId(card.type_arg, card.id);
        }
      },
      setupNotifications() {
        console.log(this._notifications);
        this._notifications.forEach((notif) => {
          var functionName = "notif_" + notif[0];

          dojo.subscribe(notif[0], this, functionName);
          if (notif[1] != null) {
            this.notifqueue.setSynchronous(notif[0], notif[1]);
          }
        });

        // TODO: useful?
        dojo.subscribe("newScores", this, "notif_newScores");
      },

      notif_newScores: function (notif) {
        // Update players' scores
        for (var player_id in notif.args.newScores) {
          this.scoreCtrl[player_id].toValue(notif.args.newScores[player_id]);
        }
      },
    }
  );
});
