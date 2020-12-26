define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
  return declare("fluxx.cardTrait", null, {
    constructor() {},

    onEnteringStatePlayCard: function (args) {
      console.log("Entering state: PlayCard");

      dojo.connect(
        this.handStock,
        "onChangeSelection",
        this,
        "onSelectHandPlayCard"
      );
    },

    onLeavingStatePlayCard: function () {
      console.log("Leaving state: PlayCard");
    },
    onUpdateActionButtonsPlayCard: function (args) {
      console.log("Update Action Buttons: PlayCard");
    },

    onSelectHandPlayCard: function () {
      var items = this.handStock.getSelectedItems();

      console.log("onSelectHandCard", items, this.currentState);

      if (items.length == 0) return;

      if (this.checkAction("playCard")) {
        // Play a card
        this.ajaxcall(
          "/" + this.game_name + "/" + this.game_name + "/playCard.html",
          {
            card_id: items[0].id,
            card_definition_id: items[0].type,
            lock: true,
          },
          this,
          function (result) {},
          function (is_error) {}
        );
      }

      this.handStock.unselectAll();
    },
  });
});
