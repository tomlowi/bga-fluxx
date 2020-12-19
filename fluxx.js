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
      console.log("fluxx constructor");

      this.cardwidth = 166;
      this.cardheight = 258;

      this.image_items_per_row = 12;

      this.cardsPath = "img/fluxx_cards.png";

      this.cardtypes = {
        baserule: {
          id_type: 1,
          nb_cards: 1,
        },
        keeper: {
          id_type: 2,
          nb_cards: 19,
        },
        goal: {
          id_type: 3,
          nb_cards: 30,
        },
        newrule: {
          id_type: 4,
          nb_cards: 27,
        },
        action: {
          id_type: 5,
          nb_cards: 23,
        },
      };
    },

    /*
            setup:
            
            This method must set up the game user interface according to current game situation specified
            in parameters.
            
            The method is called each time the game interface is displayed to a player, ie:
            _ when the game starts
            _ when a player refreshes the game page (F5)
            
            "gamedatas" argument contains all datas retrieved by your "getAllDatas" PHP method.
        */

    setup: function (gamedatas) {
      console.log("Starting game setup");

      this.keeperSections = [];

      // Setting up player boards
      for (var player_id in gamedatas.players) {
        var player = gamedatas.players[player_id];

        // TODO: Setting up players boards if needed
        this.keeperSections[player_id] = new ebg.stock();
        this.keeperSections[player_id].create(
          this,
          $("playertablecard_" + player_id),
          this.cardwidth,
          this.cardheight
        );
        this.keeperSections[
          player_id
        ].image_items_per_row = this.image_items_per_row;
      }

      // Player hand
      this.playerHand = new ebg.stock();
      this.playerHand.create(
        this,
        $("myhand"),
        this.cardwidth,
        this.cardheight
      );

      this.rulesSection = new ebg.stock();
      this.rulesSection.create(
        this,
        $("ruleSection"),
        this.cardwidth,
        this.cardheight
      );

      this.playerHand.image_items_per_row = this.image_items_per_row;
      this.rulesSection.image_items_per_row = this.image_items_per_row;

      var cardPos = 0;
      //// Create cards types:
      for (var [name, cardtype] of Object.entries(this.cardtypes)) {
        for (var cardNb = 1; cardNb <= cardtype.nb_cards; cardNb++) {
          var card_type_id = this.getCardUniqueId(cardtype.id_type, cardNb);

          var weight =
            type == this.cardtypes.baserule.id_type ? -1 : card_type_id;

          // Player Hand can contain all cards
          this.playerHand.addItemType(
            card_type_id,
            weight,
            g_gamethemeurl + this.cardsPath,
            cardPos
          );

          // Rule section can contain Goals, New rules and the base rule
          if (
            [
              this.cardtypes.baserule.id_type,
              this.cardtypes.goal.id_type,
              this.cardtypes.newrule.id_type,
            ].includes(cardtype.id_type)
          ) {
            this.rulesSection.addItemType(
              card_type_id,
              weight,
              g_gamethemeurl + this.cardsPath,
              cardPos
            );
          }

          // Keepers areas, only containing Keepers
          if (cardtype.id_type == this.cardtypes.keeper.id_type) {
            for (const player_id in this.keeperSections) {
              this.keeperSections[player_id].addItemType(
                card_type_id,
                weight,
                g_gamethemeurl + this.cardsPath,
                cardPos
              );
            }
          }

          cardPos++;
        }
      }

      for (var i in this.gamedatas.hand) {
        var card = this.gamedatas.hand[i];
        var type = card.type;
        var number = card.type_arg;
        this.playerHand.addToStockWithId(
          this.getCardUniqueId(type, number),
          card.id
        );
      }
      //Cards played on table
      for (i in this.gamedatas.keepers) {
        var card = this.gamedatas.keepers[i];
        var type = card.type;
        var number = card.type_arg;
        var player_id = card.location_arg;
        this.keeperSections[player_id].addToStockWithId(
          this.getCardUniqueId(type, number),
          card.id
        );
      }

      // New rules in effect
      for (i in this.gamedatas.rules) {
        console.log(this.gamedatas.rules[i]);
        var card = this.gamedatas.rules[i];
        var type = card.type;
        var number = card.type_arg;
        this.rulesSection.addToStockWithId(
          this.getCardUniqueId(type, number),
          card.id
        );
      }

      dojo.connect(
        this.playerHand,
        "onChangeSelection",
        this,
        "onPlayerHandSelectionChanged"
      );
      for (const player_id in this.keeperSections) {
        dojo.connect(
          this.keeperSections[player_id],
          "onChangeSelection",
          this,
          "onKeeperSelectionChanged"
        );
      }

      dojo.connect(
        this.rulesSection,
        "onChangeSelection",
        this,
        "onRuleSelectionChanged"
      );

      // Setup game notifications to handle (see "setupNotifications" method below)
      this.setupNotifications();

      console.log("Ending game setup");
    },

    ///////////////////////////////////////////////////
    //// Game & client states

    // onEnteringState: this method is called each time we are entering into a new game state.
    //                  You can use this method to perform some user interface changes at this moment.
    //
    onEnteringState: function (stateName, args) {
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

    ///////////////////////////////////////////////////
    //// Utility methods

    /*
        
            Here, you can defines some utility methods that you can use everywhere in your javascript
            script.
        
        */

    // Get card unique identifier based on its type and number
    getCardUniqueId: function (type, number) {
      uniqueId = type * 100 + parseInt(number);

      return uniqueId;
    },

    getCardSpritePosition: function (type, number) {
      var typeOffset = 0;
      for (
        var idtype = 1;
        idtype <= Object.keys(this.cardtypes).length;
        idtype++
      ) {
        if (idtype < type) {
          typeOffset += this.cardtypes[Object.keys(this.cardtypes)[idtype - 1]]
            .nb_cards;
        }
      }

      return typeOffset + number;
    },

    playCardOnTable: function (player_id, uniqueId, card_id) {
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
        this.keeperSections[player_id].addToStockWithId(
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
      this.playerHand.removeFromStockById(card_id);
    },

    ///////////////////////////////////////////////////
    //// Player's action

    /*
        
            Here, you are defining methods to handle player's action (ex: results of mouse click on 
            game objects).
            
            Most of the time, these methods:
            _ check the action is possible at this game state.
            _ make a call to the game server
        
        */

    onPlayerHandSelectionChanged: function () {
      var items = this.playerHand.getSelectedItems();
      var action = "playCard";

      if (items.length > 0) {
        if (this.checkAction(action, true)) {
          // Can play a card
          var card_id = items[0].id;
          var uniqueId = items[0].type;
          console.log("on playCard id: " + uniqueId);

          var ajax_id = card_id + "_" + uniqueId;

          //(this.player_id, uniqueId, card_id)
          this.ajaxcall(
            "/" +
              this.game_name +
              "/" +
              this.game_name +
              "/" +
              action +
              ".html",
            {
              card_id: card_id,
              card_unique_id: uniqueId,
              lock: true,
            },
            this,
            function (result) {},
            function (is_error) {}
          );

          this.playerHand.unselectAll();
        } else if (this.checkAction("discardCard")) {
          // Can discard a card
        } else {
          this.playerHand.unselectAll();
        }
      }
    },

    onRuleSelectionChanged: function () {
      this.rulesSection.unselectAll();
    },

    onKeeperSelectionChanged: function () {
      for (const player_id in this.keeperSections) {
        this.keeperSections[player_id].unselectAll();
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
      console.log("notifications subscriptions setup");

      dojo.subscribe("cardPlayed", this, "notif_cardPlayed");
      dojo.subscribe("cardDrawn", this, "notif_cardDrawn");
      dojo.subscribe("newScores", this, "notif_newScores");
    },

    notif_cardPlayed: function (notif) {
      this.playCardOnTable(
        notif.args.player_id,
        notif.args.card_unique_id,
        notif.args.card_id
      );
    },

    notif_cardDrawn: function (notif) {
      for (var card of notif.args.cardsDrawn) {
        this.playerHand.addToStockWithId(
          this.getCardUniqueId(card.type, card.type_arg),
          card.id
        );
      }
    },

    notif_newScores: function (notif) {
      // Update players' scores
      for (var player_id in notif.args.newScores) {
        this.scoreCtrl[player_id].toValue(notif.args.newScores[player_id]);
      }
    },
  });
});
