define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
  return declare("fluxx.cardTrait", null, {
    constructor() {
      this._notifications.push(
        ["cardsDrawn", null],
        ["cardsDrawnOther", null],
        ["keeperPlayed", 500],
        ["goalsDiscarded", 500],
        ["goalPlayed", null],
        ["rulesDiscarded", 500],
        ["rulePlayed", null],
        ["actionPlayed", 500],
        ["handDiscarded", 500],
        ["keepersDiscarded", 500],
        ["cardsReceivedFromPlayer", 500],
        ["cardsSentToPlayer", null],
        ["keepersMoved", 500],
        ["cardFromTableToHand", null],
        ["handCountUpdate", null],
        ["reshuffle", null]
      );
    },

    playCard: function (player_id, card, destinationStock) {
      if (this.isCurrentPlayerActive()) {
        destinationStock.addToStockWithId(
          card.type_arg,
          card.id,
          this.handStock.getItemDivId(card.id)
        );
        this.handStock.removeFromStockById(card.id);
      } else {
        destinationStock.addToStockWithId(
          card.type_arg,
          card.id,
          "player_board_" + player_id
        );
      }
    },

    discardCard: function (card, stock, player_id) {
      // The new card should be on top (=first) in the discard pile
      this.discardStock.changeItemsWeight({
        [card.type_arg]: this.discardStock.count() + 1000,
      });

      var origin;
      if (typeof stock !== "undefined") {
        origin = stock.getItemDivId(card.id);
      } else if (typeof player_id !== "undefined") {
        origin = "player_board_" + player_id;
      }

      this.discardStock.addToStockWithId(card.type_arg, card.id, origin);

      if (typeof stock !== "undefined") {
        stock.removeFromStockById(card.id);
      }
    },

    discardCards: function (cards, stock, player_id) {
      var that = this;
      var cards_array = [];
      for (var card_id in cards) {
        cards_array.push(cards[card_id]);
      }

      var count = 0;
      cards_array.forEach((card) => {
        if (player_id !== undefined) {
          setTimeout(function () {
            that.discardCard(card, stock, player_id);
          }, count++ * 250);
        } else {
          that.discardCard(card, stock, player_id);
        }
      });
    },

    notif_cardsDrawn: function (notif) {
      for (var card of notif.args.cards) {
        this.handStock.addToStockWithId(card.type_arg, card.id, "deckCard");
      }
    },

    notif_cardsDrawnOther: function (notif) {
      var player_id = notif.args.player_id;

      console.log(player_id, this.player_id);

      if (player_id != this.player_id) {
        this.slideTemporaryObject(
          '<div class="flx-card flx-deck-card"></div>',
          "flxTable",
          "deckCard",
          "player_board_" + player_id
        );
      }

      this.handCounter[player_id].toValue(notif.args.handCount);
      this.deckCounter.toValue(notif.args.deckCount);

      if (notif.args.deckCount == 0) {
        dojo.addClass("deckCard", "flx-deck-empty");
      } else {
        dojo.removeClass("deckCard", "flx-deck-empty");
      }
    },

    notif_keeperPlayed: function (notif) {
      var player_id = notif.args.player_id;
      this.playCard(player_id, notif.args.card, this.keepersStock[player_id]);
      this.handCounter[player_id].toValue(notif.args.handCount);
      this.keepersCounter[player_id].toValue(
        this.keepersStock[player_id].count()
      );
    },

    notif_goalsDiscarded: function (notif) {
      this.discardCards(notif.args.cards, this.goalsStock);
      this.discardCounter.toValue(notif.args.discardCount);
    },

    notif_goalPlayed: function (notif) {
      var player_id = notif.args.player_id;
      this.playCard(player_id, notif.args.card, this.goalsStock);
      this.handCounter[player_id].toValue(notif.args.handCount);
    },

    notif_rulesDiscarded: function (notif) {
      var ruleType = notif.args.ruleType;
      if (ruleType != "drawRule" && ruleType != "playRule") {
        ruleType = "others";
      }
      this.discardCards(notif.args.cards, this.rulesStock[ruleType]);
      this.discardCounter.toValue(notif.args.discardCount);
    },

    notif_rulePlayed: function (notif) {
      var player_id = notif.args.player_id;

      var ruleType = notif.args.ruleType;
      if (ruleType != "drawRule" && ruleType != "playRule") {
        ruleType = "others";
      }

      this.playCard(player_id, notif.args.card, this.rulesStock[ruleType]);
      this.handCounter[player_id].toValue(notif.args.handCount);
    },

    notif_actionPlayed: function (notif) {
      var player_id = notif.args.player_id;
      var card = notif.args.card;
      var handCount = notif.args.handCount;
      var discardCount = notif.args.discardCount;

      if (this.isCurrentPlayerActive()) {
        this.discardCard(card, this.handStock);
      } else {
        this.discardCard(card, undefined, player_id);
      }
      this.handCounter[player_id].toValue(handCount);
      this.discardCounter.toValue(discardCount);
    },

    notif_handDiscarded: function (notif) {
      var player_id = notif.args.player_id;
      var cards = notif.args.cards;

      if (player_id == this.player_id) {
        this.discardCards(cards, this.handStock);
      } else {
        this.discardCards(cards, undefined, player_id);
      }

      this.handCounter[player_id].toValue(notif.args.handCount);
      this.discardCounter.toValue(notif.args.discardCount);
    },

    notif_keepersDiscarded: function (notif) {
      var player_id = notif.args.player_id;
      var cards = notif.args.cards;

      this.discardCards(cards, this.keepersStock[player_id]);

      this.keepersCounter[player_id].toValue(
        this.keepersStock[player_id].count()
      );
      this.discardCounter.toValue(notif.args.discardCount);
    },

    notif_cardsReceivedFromPlayer: function (notif) {
      var player_id = notif.args.player_id;
      var cards = notif.args.cards;

      for (var card_id in cards) {
        var card = cards[card_id];
        this.handStock.addToStockWithId(
          card.type_arg,
          card.id,
          "player_board_" + player_id
        );
      }
    },

    notif_cardsSentToPlayer: function (notif) {
      var player_id = notif.args.player_id;
      var cards = notif.args.cards;

      for (var card_id in cards) {
        var card = cards[card_id];
        this.handStock.removeFromStockById(
          card.id,
          "player_board_" + player_id,
          true
        );
      }
      this.handStock.updateDisplay();
    },

    notif_keepersMoved: function (notif) {
      var player_id = notif.args.player_id;
      var other_player_id = notif.args.other_player_id;
      var cards = notif.args.cards;

      var originStock = this.keepersStock[other_player_id];
      var destinationStock = this.keepersStock[player_id];

      for (var card_id in cards) {
        var card = cards[card_id];
        destinationStock.addToStockWithId(
          card.type_arg,
          card.id,
          originStock.getItemDivId(card.id)
        );
        originStock.removeFromStockById(card.id);
      }
      this.keepersCounter[player_id].toValue(destinationStock.count());
      this.keepersCounter[other_player_id].toValue(originStock.count());
    },

    notif_handCountUpdate: function (notif) {
      var handsCount = notif.args.handsCount;
      for (var player_id in handsCount) {
        this.handCounter[player_id].toValue(handsCount[player_id]);
      }
    },

    notif_reshuffle: function (notif) {
      console.log("RESHUFFLE", notif);
      this.deckCounter.toValue(notif.args.deckCount);
      dojo.removeClass("deckCard", "flx-deck-empty");

      this.discardCounter.toValue(notif.args.discardCount);

      var exceptionCards = notif.args.exceptionCards;
      if (exceptionCards === undefined) {
        this.discardStock.removeAll();
      } else {
        var exceptionCardsType = exceptionCards.map(function (card) {
          return card.type_arg;
        });
        for (var card of this.discardStock.getAllItems()) {
          if (exceptionCardsType.indexOf(card.type) == -1) {
            this.discardStock.removeFromStockById(card.id, "deckCard", true);
          }
        }
        this.discardStock.updateDisplay();
      }
    },

    notif_cardFromTableToHand: function (notif) {
      var player_id = notif.args.player_id;
      var card = notif.args.card;

      var originStock;

      var card_definition = this.cardsDefinitions[card.type_arg];

      console.log(card);
      console.log(card_definition);

      switch (card.location) {
        case "keepers":
          originStock = this.keepersStock[card.location_arg];
          break;

        case "rules":
          if (card_definition.ruleType == "playRule") {
            originStock = this.rulesStock.playRule;
          } else if (card_definition.ruleType == "drawRule") {
            originStock = this.rulesStock.drawRule;
          } else {
            originStock = this.rulesStock.others;
          }
          break;

        case "goals":
          originStock = this.goalsStock;
          break;

        default:
          return;
      }

      if (player_id == this.player_id) {
        this.handStock.addToStockWithId(
          card.type_arg,
          card.id,
          originStock.getItemDivId(card.id)
        );
        originStock.removeFromStockById(card.id);
      } else {
        originStock.removeFromStockById(card.id, "player_board_" + player_id);
      }

      // Update the hand and keepers counts
      this.handCounter[player_id].toValue(notif.args.handCount);

      if (card.location == "keepers") {
        this.keepersCounter[card.location_arg].toValue(
          this.keepersStock[card.location_arg].count()
        );
      }
    },
  });
});
