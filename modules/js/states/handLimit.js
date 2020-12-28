define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
  return declare("fluxx.states.handLimit", null, {
    constructor() {},

    onEnteringStateHandLimit: function (args) {
      console.log("Entering state: HandLimit");
    },

    onLeavingStateHandLimit: function () {
      console.log("Leaving state: HandLimit");

      if (this._listener !== undefined) {
        dojo.disconnect(this._listener);
        delete this._listener;
        this.handStock.setSelectionMode(0);
      }
    },

    onUpdateActionButtonsHandLimit: function (args) {
      console.log("Update Action Buttons: HandLimit");

      if (this.isCurrentPlayerActive()) {
        this.handStock.setSelectionMode(2);

        // Let's prevent registering this listener twice
        if (this._listener !== undefined) dojo.disconnect(this._listener);

        this._listener = dojo.connect(
          this.handStock,
          "onChangeSelection",
          this,
          "onSelectCardHandLimit"
        );
      }

      this.addActionButton(
        "button_1",
        _("Discard selected"),
        "onRemoveCardsHandLimit"
      );
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
      var arg_card_ids = "";
      for (var card in cards) {
        console.log("discard from hand: " + card.id + ", type: " + card.type);
        arg_card_ids += card.id + ";";
      }
      this.ajaxAction("discardCards", {
        card_ids: arg_card_ids,
      });
    },
  });
});
