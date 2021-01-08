define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
  return declare("fluxx.states.actionResolve", null, {
    constructor() {
      this._notifications.push(["actionResolved", null]);

      this._listeners = [];
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
      this.action_type = args.action_type;

      if (this.isCurrentPlayerActive()) {
        method = this.updateActionButtonsActionResolve[this.action_type];
        if (method !== undefined) {
          method(this, args);
        } else {
          console.log("TODO");
        }
      }
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

    updateActionButtonsActionResolve: {
      keepersExchange: function (that, args) {
        for (var player_id in that.keepersStock) {
          var stock = that.keepersStock[player_id];
          stock.setSelectionMode(1);

          if (that._listeners["keepers_" + player_id] !== undefined) {
            dojo.disconnect(that._listeners["keepers_" + player_id]);
          }
          that._listeners["keepers_" + player_id] = dojo.connect(
            stock,
            "onChangeSelection",
            that,
            "onSelectCardForAction"
          );
        }
      },
      keeperSelection: function (that, args) {
        for (var player_id in that.keepersStock) {
          if (player_id != that.player_id) {
            var stock = that.keepersStock[player_id];
            stock.setSelectionMode(1);

            if (that._listeners["keepers_" + player_id] !== undefined) {
              dojo.disconnect(that._listeners["keepers_" + player_id]);
            }
            that._listeners["keepers_" + player_id] = dojo.connect(
              stock,
              "onChangeSelection",
              that,
              "onResolveActionCardSelection"
            );
          }
        }
      },
      playerSelection: function (that, args) {
        // @TODO: to be replaced with nice visual way of selecting other players
        for (var player_id in that.players) {
          if (player_id != that.player_id) {
            that.addActionButton(
              "button_" + player_id,
              that.players[player_id]["name"],
              "onResolveActionPlayerSelection"
            );
            dojo.attr("button_" + player_id, "data-player-id", player_id);
          }
        }
      },
      discardSelection: function (that, args) {},
      rulesSelection: function (that, args) {},
      ruleSelection: function (that, args) {
        for (var rule_type in that.rulesStock) {
          var stock = that.rulesStock[rule_type];
          stock.setSelectionMode(1);

          if (that._listeners["rules_" + rule_type] !== undefined) {
            dojo.disconnect(that._listeners["rules_" + rule_type]);
          }
          that._listeners["rules_" + rule_type] = dojo.connect(
            stock,
            "onChangeSelection",
            that,
            "onResolveActionCardSelection"
          );
        }
      },
      cardSelection: function (that, args) {
        that.goalsStock.setSelectionMode(1);
        if (that._listeners["goal"] !== undefined) {
          dojo.disconnect(that._listeners["goal"]);
        }
        that._listeners["goal"] = dojo.connect(
          that.goalsStock,
          "onChangeSelection",
          that,
          "onResolveActionCardSelection"
        );

        for (var player_id in that.keepersStock) {
          var stock = that.keepersStock[player_id];
          stock.setSelectionMode(1);

          if (that._listeners["keepers_" + player_id] !== undefined) {
            dojo.disconnect(that._listeners["keepers_" + player_id]);
          }
          that._listeners["keepers_" + player_id] = dojo.connect(
            stock,
            "onChangeSelection",
            that,
            "onResolveActionCardSelection"
          );
        }

        for (var rule_type in that.rulesStock) {
          var stock = that.rulesStock[rule_type];
          stock.setSelectionMode(1);

          if (that._listeners["rules_" + rule_type] !== undefined) {
            dojo.disconnect(that._listeners["rules_" + rule_type]);
          }
          that._listeners["rules_" + rule_type] = dojo.connect(
            stock,
            "onChangeSelection",
            that,
            "onResolveActionCardSelection"
          );
        }
      },
      direction: function (that, args) {
        that.addActionButton(
          "button_0",
          _("To the left"),
          "onResolveActionDirection"
        );
        that.addActionButton(
          "button_1",
          _("To the right"),
          "onResolveActionDirection"
        );
        dojo.attr("button_0", "data-direction-id", 0);
        dojo.attr("button_1", "data-direction-id", 1);
      },
      todaysSpecial: function (that, args) {},
    },

    onResolveActionPlayerSelection: function (ev) {
      var player_id = ev.target.getAttribute("data-player-id");

      var action = "resolveActionPlayerSelection";

      if (this.checkAction(action)) {
        this.ajaxAction(action, {
          player_id: player_id,
        });
      }
    },

    onResolveActionCardSelection: function (control_name, item_id) {
      var stock = this._allStocks[control_name];

      var action = "resolveActionCardSelection";
      var items = stock.getSelectedItems();

      if (items.length == 0) return;

      console.log("onResolveActionCardSelection", items);

      if (this.checkAction(action)) {
        // Play a card
        this.ajaxAction(action, {
          card_id: items[0].id,
          card_definition_id: items[0].type,
          lock: true,
        });
      }

      stock.unselectAll();
    },

    onResolveActionDirection: function (ev) {
      var direction = ev.target.getAttribute("data-direction-id");

      var action = "resolveActionDirection";

      if (this.checkAction(action)) {
        this.ajaxAction(action, {
          direction: direction,
        });
      }
    },

    onUpdateActionButtonsForSpecificAction: function (actionCardArg) {
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

      this.handStock.setSelectionMode(0);
      this.goalsStock.setSelectionMode(0);

      for (var player_id in this.keepersStock) {
        var stock = this.keepersStock[player_id];
        stock.setSelectionMode(0);
      }

      for (var rule_type in this.rulesStock) {
        var stock = this.rulesStock[rule_type];
        stock.setSelectionMode(0);
      }

      for (var listener_id in this._listeners) {
        dojo.disconnect(this._listeners[listener_id]);
        delete this._listeners[listener_id];
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
