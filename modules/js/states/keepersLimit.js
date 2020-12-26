define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
  return declare("fluxx.states.keepersLimit", null, {
    constructor() {},

    onEnteringStateKeepersLimit: function (args) {
      var stock = this.keepersStock[this.player_id];
      console.log("Entering state: KeepersLimit");

      if (this.isCurrentPlayerActive()) {
        stock[this.player_id].setSelectionMode(2);
        this._event = dojo.connect(
          stock[this.player_id],
          "onChangeSelection",
          this,
          "onSelectCardKeepersLimit"
        );
      }
    },
    onLeavingStateKeepersLimit: function () {
      var stock = this.keepersStock[this.player_id];
      console.log("Leaving state: KeepersLimit");

      if (this.isCurrentPlayerActive()) {
        dojo.disconnect(this._event);
        delete this._event;
        stock.setSelectionMode(0);
      }
    },

    onUpdateActionButtonsKeepersLimit: function (args) {
      console.log("Update Action Buttons: KeepersLimit");
      this.addActionButton(
        "button_1",
        _("Discard selected"),
        "onRemoveCardsFromHand"
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

    onRemoveKeepersFromPlay: function () {
      var items = this.keepersStock[this.player_id].getSelectedItems();
      var arg_card_ids = "";
      for (var i in items) {
        console.log(
          "discard from keepers: " + items[i].id + ", type: " + items[i].type
        );
        arg_card_ids += items[i].id + ";";
      }
      var actionDiscard = "discardKeepers";
      this.ajaxAction(actionDiscard, {
        card_ids: arg_card_ids,
      });
    },
  });
});
