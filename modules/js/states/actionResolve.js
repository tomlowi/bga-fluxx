define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
  return declare("fluxx.states.actionResolve", null, {
    constructor() {
      this._notifications.push(["actionResolved", null]);

      this._listenerKeepers = [];
    },

    onEnteringStateActionResolve: function (args) {
      console.log("Entering state: ActionResolve", args);
    },

    needsDiscardPileVisible(actionCardArg) {
      switch (actionCardArg) {
        case "316": // LetsDoThatAgain
          return true;
      }
      return false;
    },

    onUpdateActionButtonsActionResolve: function (args) {
      console.log("Update Action Buttons: ActionResolve", args);

      this.actionCardId = args.action_id;
      this.actionCardArg = args.action_arg;
      // @TODO: depending on specific Action Card, different selections to be made
      // for now, allow selections in Hand and all player's Keepers

      if (this.isCurrentPlayerActive()) {
        this.discardStock.setSelectionMode(2);
        if (this.needsDiscardPileVisible(this.actionCardArg)) {
          this.discardStock.setOverlap(50);
        }

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

      this.onUpdateActionButtonsForSpecificAction(this.actionCardArg);
    },

    addOption1(msg) {
      this.addActionButton("button_1", msg, "onResolveActionWithOption1");
    },

    addOption2(msg) {
      this.addActionButton("button_2", msg, "onResolveActionWithOption2");
    },

    addOption3(msg) {
      this.addActionButton("button_3", msg, "onResolveActionWithOption3");
    },

    onUpdateActionButtonsForSpecificAction(actionCardArg) {
      switch (actionCardArg) {
        case "302": // Rotate Hands
          this.addOption1(_("Rotate Left"));
          this.addOption2(_("Rotate Right"));
          break;
        case "305": // RockPaperScissors
          this.addOption1(_("Rock"));
          this.addOption2(_("Paper"));
          this.addOption3(_("Scissors"));
          break;
        case "319": // TradeHand: select another player
          // @TODO: to be replaced with nice visual way of selecting other players
          this.addOption1(_("Opponent 1"));
          this.addOption2(_("Opponent 2"));
          this.addOption3(_("Opponent 3"));
          break;
        case "323": // Today Special
          this.addOption3(_("It's my Birthday!"));
          this.addOption2(_("Holiday or Anniversary"));
          this.addOption1(_("Just another day..."));
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

      this.discardStock.setSelectionMode(0);
      this.discardStock.setOverlap(0.00001);

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

      var selectedInDiscard = this.discardStock.getSelectedItems();
      cards = cards.concat(selectedInDiscard);

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
