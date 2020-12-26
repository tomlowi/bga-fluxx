define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
  return declare("fluxx.states.handLimit", null, {
    constructor() {},

    onEnteringStateHandLimit: function (args) {
      console.log("Entering state: HandLimit");

      if (this.isCurrentPlayerActive()) {
        this.handStock.setSelectionMode(2);

        dojo.connect(
          this.handStock,
          "onChangeSelection",
          this,
          "onSelectCardHandLimit"
        );
      }
    },

    onLeavingStateHandLimit: function () {
      console.log("Leaving state: HandLimit");

      if (this.isCurrentPlayerActive()) {
        this.handStock.setSelectionMode(0);
        dojo.disconnect(this.handStock, "onChangeSelection");
      }
    },

    onUpdateActionButtonsHandLimit: function (args) {
      console.log("Update Action Buttons: HandLimit");
      this.addActionButton(
        "button_1",
        _("Discard selected"),
        "onRemoveCardsFromHand"
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

    onRemoveCardsFromHand: function () {
      var items = this.handStock.getSelectedItems();
      var arg_card_ids = "";
      for (var i in items) {
        console.log(
          "discard from hand: " + items[i].id + ", type: " + items[i].type
        );
        arg_card_ids += items[i].id + ";";
      }
      var actionDiscard = "discardCards";
      this.ajaxAction(actionDiscard, {
        card_ids: arg_card_ids,
      });
    },
  });
});
