define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
  return declare("fluxx.states.playCard", null, {
    constructor() {},

    onEnteringStatePlayCard: function (args) {
      console.log("Entering state: PlayCard", this.isCurrentPlayerActive());

      if (this.isCurrentPlayerActive()) {
        this.handStock.setSelectionMode(1);

        // Let's prevent registering this listener twice
        if (this._listener !== undefined) dojo.disconnect(this._listener);

        this._listener = dojo.connect(
          this.handStock,
          "onChangeSelection",
          this,
          "onSelectCardPlayCard"
        );
      }
    },

    onLeavingStatePlayCard: function () {
      console.log("Leaving state: PlayCard");

      if (this._listener !== undefined) {
        dojo.disconnect(this._listener);
        delete this._listener;
        this.handStock.setSelectionMode(0);
      }
    },
    onUpdateActionButtonsPlayCard: function (args) {
      console.log("Update Action Buttons: PlayCard");
    },

    onSelectCardPlayCard: function () {
      var action = "playCard";
      var items = this.handStock.getSelectedItems();

      console.log("onSelectHandPlayCard", items);

      if (items.length == 0) return;

      if (this.checkAction(action, true)) {
        // Play a card
        this.ajaxAction(action, {
          card_id: items[0].id,
          card_definition_id: items[0].type,
          lock: true,
        });
      }

      this.handStock.unselectAll();
    },
  });
});
