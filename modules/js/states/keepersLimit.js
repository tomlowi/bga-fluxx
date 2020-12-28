define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
  return declare("fluxx.states.keepersLimit", null, {
    constructor() {},

    onEnteringStateKeepersLimit: function (args) {
      console.log("Entering state: KeepersLimit");
    },
    onLeavingStateKeepersLimit: function () {
      var stock = this.keepersStock[this.player_id];
      console.log("Leaving state: KeepersLimit");

      if (this._listener !== undefined) {
        dojo.disconnect(this._listener);
        delete this._listener;
        stock.setSelectionMode(0);
      }
    },

    onUpdateActionButtonsKeepersLimit: function (args) {
      console.log("Update Action Buttons: KeepersLimit");

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
      }

      this.addActionButton(
        "button_1",
        _("Discard selected"),
        "onRemoveCardsKeepersLimit"
      );
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
      var arg_card_ids = "";
      for (var card of items) {
        console.log(
          "discard from keepers: " + card.id + ", type: " + card.type
        );
        arg_card_ids += card.id + ";";
      }
      this.ajaxAction("discardKeepers", {
        card_ids: arg_card_ids,
      });
    },
  });
});
