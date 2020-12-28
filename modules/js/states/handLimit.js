define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
  return declare("fluxx.states.handLimit", null, {
    constructor() {},

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
      this.ajaxAction("discardKeepers", {
        card_ids: card_ids.join(";"),
      });
    },
  });
});
