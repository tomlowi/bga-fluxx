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
      var that = this;

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

    notif_cardsDrawn: function (notif) {
      for (var card of notif.args.cards) {
        this.handStock.addToStockWithId(card.type_arg, card.id, "deckCard");
      }
    },

    notif_cardsDrawnOther: function (notif) {
      this.setPlayerBoardHandCount(notif.args.player_id, notif.args.handCount);
      this.setDeckCount(notif.args.deckCount);
    },

    notif_keeperPlayed: function (notif) {
      var player_id = notif.args.player_id;
      this.playCard(player_id, notif.args.card, this.keepersStock[player_id]);
      this.setPlayerBoardHandCount(player_id, notif.args.handCount);
      this.setPlayerBoardKeepersCount(
        player_id,
        this.keepersStock[player_id].count()
      );
    },

    notif_goalsDiscarded: function (notif) {
      for (var card_id in notif.args.cards) {
        var card = notif.args.cards[card_id];
        this.discardCard(card, this.goalsStock);
      }
      this.setDiscardCount(notif.args.discardCount);
    },

    notif_goalPlayed: function (notif) {
      var player_id = notif.args.player_id;
      this.playCard(player_id, notif.args.card, this.goalsStock);
      this.setPlayerBoardHandCount(player_id, notif.args.handCount);
    },

    notif_rulesDiscarded: function (notif) {
      var ruleType = notif.args.ruleType;
      if (ruleType != "drawRule" && ruleType != "playRule") {
        ruleType = "others";
      }
      for (var card_id in notif.args.cards) {
        this.discardCard(notif.args.cards[card_id], this.rulesStock[ruleType]);
      }
      this.setDiscardCount(notif.args.discardCount);
    },

    notif_rulePlayed: function (notif) {
      var player_id = notif.args.player_id;

      var ruleType = notif.args.ruleType;
      if (ruleType != "drawRule" && ruleType != "playRule") {
        ruleType = "others";
      }

      this.playCard(player_id, notif.args.card, this.rulesStock[ruleType]);
      this.setPlayerBoardHandCount(player_id, notif.args.handCount);
    },

    notif_actionPlayed: function (notif) {
      var player_id = notif.args.player_id;
      if (this.isCurrentPlayerActive()) {
        this.discardCard(notif.args.card, this.handStock);
      } else {
        this.discardCard(notif.args.card, undefined, player_id);
      }
      this.setPlayerBoardHandCount(player_id, notif.args.handCount);
      this.setDiscardCount(notif.args.discardCount);
    },

    notif_reshuffle: function (notif) {
      // @TODO: hide deck when there is no card in it anymore
      console.log("RESHUFFLE", notif);
      this.setDeckCount(notif.args.deckCount);
      this.setDiscardCount(notif.args.discardCount);
      this.discardStock.removeAll();
    },
  });
});
