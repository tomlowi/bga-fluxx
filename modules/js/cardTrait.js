define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
  return declare("fluxx.cardTrait", null, {
    constructor() {
      this._notifications.push(
        ["cardsDrawn", null],
        ["cardsDrawnOther", null],
        ["keeperPlayed", null],
        ["goalsDiscarded", null],
        ["goalPlayed", null],
        ["rulesDiscarded", null],
        ["rulePlayed", null],
        ["actionPlayed", null],
        ["handDiscarded", null],
        ["keepersDiscarded", null],
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
      this.handCounter[notif.args.player_id].toValue(notif.args.handCount);
      this.deckCounter.toValue(notif.args.deckCount);
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

    notif_reshuffle: function (notif) {
      // @TODO: hide deck when there is no card in it anymore
      console.log("RESHUFFLE", notif);
      this.deckCounter.toValue(notif.args.deckCount);
      this.discardCounter.toValue(notif.args.discardCount);
      this.discardStock.removeAll();
    },
  });
});
