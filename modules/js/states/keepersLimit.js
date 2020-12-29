define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
  return declare("fluxx.states.keepersLimit", null, {
    constructor() {
      this._notifications.push(["keepersDiscarded", null]);
    },

    onEnteringStateKeepersLimit: function (args) {
      console.log("Entering state: KeepersLimit", args);
    },

    onUpdateActionButtonsKeepersLimit: function (args) {
      console.log("Update Action Buttons: KeepersLimit", args);

      var stock = this.keepersStock[this.player_id];
      if (this.isCurrentPlayerActive()) {
        stock.setSelectionMode(2);

        if (this._listener !== undefined) dojo.disconnect(this._listener);
        this._listener = dojo.connect(
          stock,
          "onChangeSelection",
          this,
          "onSelectCardKeepersLimit"
        );
        this.discardCountKeepersLimit = args._private.nb;

        this.addActionButton(
          "button_1",
          _("Discard selected"),
          "onRemoveCardsKeepersLimit"
        );
      }
    },

    onLeavingStateKeepersLimit: function () {
      var stock = this.keepersStock[this.player_id];
      console.log("Leaving state: KeepersLimit");

      if (this._listener !== undefined) {
        dojo.disconnect(this._listener);
        delete this._listener;
        stock.setSelectionMode(0);
      }
      delete this.discardCountKeepersLimit;
    },

    onSelectCardKeepersLimit: function () {
      var stock = this.keepersStock[this.player_id];

      var action = "discardKeepers";
      var items = stock.getSelectedItems();

      console.log("onSelectCardKeepers", items, this.currentState);

      if (items.length == 0) return;

      if (!this.checkAction(action, true)) {
        stock.unselectAll();
      }
    },

    onRemoveCardsKeepersLimit: function () {
      var cards = this.keepersStock[this.player_id].getSelectedItems();

      if (cards.length != this.discardCountKeepersLimit) {
        this.showMessage(
          _("You must discard the right amount of keepers!"),
          "error"
        );
        return;
      }

      var card_ids = cards.map(function (card) {
        return card.id;
      });

      console.log("discard from keepers:", card_ids);
      this.ajaxAction("discardKeepers", {
        card_ids: card_ids.join(";"),
      });
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
  });
});
