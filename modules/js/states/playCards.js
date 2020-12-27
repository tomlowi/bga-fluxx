define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
  return declare("fluxx.states.playCards", null, {
    constructor() {},

    onEnteringStatePlayCards: function (args) {
      console.log("Entering state: PlayCards", this.isCurrentPlayerActive());

      if (this.isCurrentPlayerActive()) {
        this.handStock.setSelectionMode(1);

        this._event = dojo.connect(
          this.handStock,
          "onChangeSelection",
          this,
          "onSelectCardPlayCards"
        );
      }
    },

    onLeavingStatePlayCards: function () {
      console.log("Leaving state: PlayCards");

      if (this.isCurrentPlayerActive()) {
        dojo.disconnect(this._event);
        delete this._event;
        this.handStock.setSelectionMode(0);
      }
    },
    onUpdateActionButtonsPlayCards: function (args) {
      console.log("Update Action Buttons: PlayCards");
    },

    onSelectCardPlayCards: function () {
      var action = "playCard";
      var items = this.handStock.getSelectedItems();

      console.log("onSelectHandPlayCards", items);

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
