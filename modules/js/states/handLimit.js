define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
  return declare("fluxx.states.handLimit", null, {
    constructor() {
      this._notifications.push(["handDiscarded", null]);
    },
    onEnteringStateHandLimit: function (args) {
      console.log("Entering state: HandLimit", args);
    },

    onUpdateActionButtonsHandLimit: function (args) {
      console.log("Update Action Buttons: HandLimit", args);

      if (this.isCurrentPlayerActive()) {
        this.handStock.setSelectionMode(2);

        // Prevent registering this listener twice
        if (this._listener !== undefined) dojo.disconnect(this._listener);

        this._listener = dojo.connect(
          this.handStock,
          "onChangeSelection",
          this,
          "onSelectCardHandLimit"
        );

        this.discardCountHandLimit = args.nb;
      }

      this.addActionButton(
        "button_1",
        _("Discard selected"),
        "onRemoveCardsHandLimit"
      );
    },

    onLeavingStateHandLimit: function () {
      console.log("Leaving state: HandLimit");

      if (this._listener !== undefined) {
        dojo.disconnect(this._listener);
        delete this._listener;
        this.handStock.setSelectionMode(0);
      }
      delete this.discardCountHandLimit;
    },

    onSelectCardHandLimit: function () {
      var action = "discardHandCards";
      var items = this.handStock.getSelectedItems();

      console.log("onSelectHandCard", items, this.currentState);

      if (items.length == 0) return;

      if (!this.checkAction(action, true)) {
        this.handStock.unselectAll();
      }
    },

    onRemoveCardsHandLimit: function () {
      var cards = this.handStock.getSelectedItems();

      if (cards.length != this.discardCountHandLimit) {
        this.showMessage(
          _("You must discard the right amount of cards!"),
          "error"
        );
        return;
      }

      var card_ids = cards.map(function (card) {
        return card.id;
      });

      console.log("discard from hand:", card_ids);
      this.ajaxAction("discardHandCards", {
        card_ids: card_ids.join(";"),
      });
    },

    notif_handDiscarded: function (notif) {
      var player_id = notif.args.player_id;
      var cards = notif.args.cards;

      if (this.isCurrentPlayerActive()) {
        this.discardCards(cards, this.handStock);
      } else {
        this.discardCards(cards, undefined, player_id);
      }

      this.handCounter[player_id].toValue(notif.args.handCount);
      this.discardCounter.toValue(notif.args.discardCount);
    },
  });
});
