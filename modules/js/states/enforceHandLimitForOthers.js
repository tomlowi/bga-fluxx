define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
  return declare("fluxx.states.enforceHandLimitForOthers", null, {
    onEnteringStateEnforceHandLimitForOthers: function (args) {
      console.log("Entering state: EnforceHandLimitForOthers", args);
    },

    onUpdateActionButtonsEnforceHandLimitForOthers: function (args) {
      console.log("Update Action Buttons: EnforceHandLimitForOthers", args);

      if (this.isCurrentPlayerActive()) {
        this.handStock.setSelectionMode(2);

        // Prevent registering this listener twice
        if (this._listener !== undefined) dojo.disconnect(this._listener);

        this._listener = dojo.connect(
          this.handStock,
          "onChangeSelection",
          this,
          "onSelectCardEnforceHandLimitForOthers"
        );

        this._discardCount = args._private.count;

        this.addActionButton(
          "button_1",
          _("Discard selected"),
          "onRemoveCardsEnforceHandLimitForOthers"
        );
      }
    },

    onLeavingStateEnforceHandLimitForOthers: function () {
      console.log("Leaving state: EnforceHandLimitForOthers");

      if (this._listener !== undefined) {
        dojo.disconnect(this._listener);
        delete this._listener;
      }
      this.handStock.setSelectionMode(0);
      delete this._discardCount;
    },

    onSelectCardEnforceHandLimitForOthers: function () {
      var action = "discardHandCards";
      var items = this.handStock.getSelectedItems();

      console.log("onSelectHandCard", items, this.currentState);

      if (items.length == 0) return;

      if (!this.checkAction(action, true)) {
        this.handStock.unselectAll();
      }
    },

    onRemoveCardsEnforceHandLimitForOthers: function () {
      var cards = this.handStock.getSelectedItems();

      if (cards.length != this._discardCount) {
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
  });
});
