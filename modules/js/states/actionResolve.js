define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
  return declare("fluxx.states.actionResolve", null, {
    constructor() {
      this._notifications.push(["actionResolved", null]);

      this._listenerKeepers = [];
    },

    onEnteringStateActionResolve: function (args) {
      console.log("Entering state: ActionResolve", args);
    },

    onUpdateActionButtonsActionResolve: function (args) {
      console.log("Update Action Buttons: ActionResolve", args);

      this.actionCardId = args.action_id;
      this.actionCardArg = args.action_arg;
      // @TODO: depending on specific Action Card, different selections to be made
      // for now, allow selections in Hand and all player's Keepers

      if (this.isCurrentPlayerActive()) {
        this.handStock.setSelectionMode(2);
        if (this._listenerHand !== undefined)
          dojo.disconnect(this._listenerHand);
        this._listenerHand = dojo.connect(
          this.handStock,
          "onChangeSelection",
          this,
          "onSelectCardForAction"
        );

        for (var player_id in this.keepersStock) {
          var stock = this.keepersStock[player_id];
          stock.setSelectionMode(2);

          if (this._listenerKeepers[player_id] !== undefined)
            dojo.disconnect(this._listenerKeepers[player_id]);
          this._listenerKeepers[player_id] = dojo.connect(
            stock,
            "onChangeSelection",
            this,
            "onSelectCardForAction"
          );
        }
      }

      this.addActionButton(
        "button_1",
        _("Do It (with selected cards)"),
        "onResolveActionWithSelectedCards"
      );
    },

    onUpdateActionButtonsForSpecificAction(actionCardArg) {
      switch(actionCardArg) {
        case 302: // Rotate Hands
          this.addActionButton(
            "button_1",
            _("Rotate Left"),
            "onResolveActionWithOption1"
          );
          this.addActionButton(
            "button_2",
            _("Rotate Right"),
            "onResolveActionWithOption2"
          );          
          break;
        default:
          this.addActionButton(
            "button_1",
            _("Do It (with selected cards)"),
            "onResolveActionWithSelectedCards"
          );
          break;
      }
    },

    onLeavingStateActionResolve: function () {
      console.log("Leaving state: ActionResolve");

      if (this._listenerHand !== undefined) {
        dojo.disconnect(this._listenerHand);
        delete this._listenerHand;
        this.handStock.setSelectionMode(0);
      }

      for (var player_id in this.keepersStock) {
        var stock = this.keepersStock[player_id];
        if (this._listenerKeepers[player_id] !== undefined) {
          dojo.disconnect(this._listenerKeepers[player_id]);
          delete this._listenerKeepers[player_id];
          stock.setSelectionMode(0);
        }
      }
    },

    onSelectCardForAction: function () {
      var action = "resolveAction";
      if (!this.checkAction(action, true)) {
        stock.unselectAll();
      }
    },

    onResolveActionWithSelectedCards: function () {
      this.onResolveActionWithSelections(0);
    },

    onResolveActionWithOption1: function () {
      this.onResolveActionWithSelections(1);
    },

    onResolveActionWithOption2: function () {
      this.onResolveActionWithSelections(2);
    },

    onResolveActionWithOption3: function () {
      this.onResolveActionWithSelections(3);
    },

    onResolveActionWithSelections: function (option_chosen) {
      var cards = [];

      var selectedInHand = this.handStock.getSelectedItems();
      cards = cards.concat(selectedInHand);
      for (var player_id in this.keepersStock) {
        var stock = this.keepersStock[player_id];
        var selectedInKeepers = stock.getSelectedItems();
        cards = cards.concat(selectedInKeepers);
      }

      var card_ids = cards.map(function (card) {
        return card.id;
      });

      console.log("resolve action with:", card_ids);
      this.ajaxAction("resolveActionWithCards", {
        option: option_chosen,
        card_ids: card_ids.join(";"),
      });
    },

    notif_actionResolved: function (notif) {
      var player_id = notif.args.player_id;
      var cards = notif.args.cards;

      // @TODO: depending on specific Action Card, different selections to be made
      // mulitple cards to be moved, or to be discarded, or hands switched, or ...

      // if (player_id == this.player_id) {
      //   this.discardCards(cards, this.handStock);
      // } else {
      //   this.discardCards(cards, undefined, player_id);
      // }

      // this.keepersCounter[player_id].toValue(
      //   this.keepersStock[player_id].count()
      // );
      // this.discardCounter.toValue(notif.args.discardCount);
    },
  });
});
