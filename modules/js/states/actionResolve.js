define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
  return declare("fluxx.states.actionResolve", null, {
    constructor() {
      this._notifications.push(["actionResolved", null]);

      this._listeners = [];
    },

    onEnteringStateActionResolve: function (args) {
      console.log("Entering state: ActionResolve", args);
    },

    onUpdateActionButtonsActionResolve: function (args) {
      console.log("Update Action Buttons: ActionResolve", args);

      if (this.isCurrentPlayerActive()) {
        method = this.updateActionButtonsActionResolve[args.action_type];
        method(this, args.action_args);
      }
    },

    updateActionButtonsActionResolve: {
      keepersExchange: function (that, args) {
        for (var player_id in that.keepersStock) {
          var stock = that.keepersStock[player_id];
          stock.setSelectionMode(1);
        }
        that.addActionButton(
          "button_confirm",
          _("Done"),
          "onResolveActionKeepersExchange"
        );
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
      discardSelection: function (that, args) {
        dojo.place('<div id="tmpDiscardStock"></div>', "tmpHand", "first");

        that.tmpDiscardStock = that.createCardStock("tmpDiscardStock", [
          "rule",
          "action",
        ]);

        that.addCardsToStock(that.tmpDiscardStock, args.discard);
        that.tmpDiscardStock.setSelectionMode(1);

        that._listeners["tmpDiscard"] = dojo.connect(
          that.tmpDiscardStock,
          "onChangeSelection",
          that,
          "onResolveActionCardSelection"
        );
      },
      rulesSelection: function (that, args) {
        for (var rule_type in that.rulesStock) {
          var stock = that.rulesStock[rule_type];
          stock.setSelectionMode(2);
        }
        that.addActionButton(
          "button_confirm",
          _("Done"),
          "onResolveActionRulesSelection"
        );
        dojo.attr("button_confirm", "data-count", args.toDiscardCount);
      },
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
      buttons: function (that, args) {
        for (var choice of args) {
          that.addActionButton(
            "button_" + choice.value,
            choice.label,
            "onResolveActionButtons"
          );
          dojo.attr("button_" + choice.value, "data-value", choice.value);
        }
      },

      TODO: function (that, args) {
        that.addActionButton(
          "button_0",
          _("Not implemented, ignore"),
          "onResolveActionButtons"
        );
      },
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

    onResolveActionButtons: function (ev) {
      var value = ev.target.getAttribute("data-value");

      var action = "resolveActionButtons";

      if (this.checkAction(action)) {
        this.ajaxAction(action, {
          value: value,
        });
      }
    },

    onResolveActionKeepersExchange: function (ev) {
      var myKeeper = this.keepersStock[this.player_id].getSelectedItems()[0];

      if (myKeeper === undefined) {
        this.showMessage(_("You must select one of your keepers"), "error");
        return;
      }

      var otherKeeper;

      for (var player_id in this.keepersStock) {
        if (player_id != this.player_id) {
          var stock = this.keepersStock[player_id];
          var items = stock.getSelectedItems();

          if (items.length > 0) {
            if (otherKeeper !== undefined) {
              this.showMessage(
                _("You must select only one other player's keeper"),
                "error"
              );
              return;
            }

            otherKeeper = items[0];
          }
        }
      }

      var action = "resolveActionKeepersExchange";

      if (this.checkAction(action)) {
        this.ajaxAction(action, {
          myKeeperId: myKeeper.id,
          otherKeeperId: otherKeeper.id,
        });
      }
    },

    onResolveActionRulesSelection: function (ev) {
      var toDiscardCount = parseInt(ev.target.getAttribute("data-count"));
      var rules = [];

      for (var rule_type in this.rulesStock) {
        var stock = this.rulesStock[rule_type];
        rules = rules.concat(stock.getSelectedItems());
      }

      if (rules.length > toDiscardCount) {
        this.showMessage(
          dojo.string.substitute(_("You can only pick up to ${nb} rules"), {
            nb: toDiscardCount,
          }),
          "error"
        );
        return;
      }

      var action = "resolveActionCardsSelection";
      var rules_id = rules.map(function (rule) {
        return rule.id;
      });

      if (this.checkAction(action)) {
        this.ajaxAction(action, {
          cards_id: rules_id.join(";"),
        });
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

      if (this.tmpDiscardStock !== undefined) {
        delete this.tmpDiscardStock;
      }
      dojo.destroy("tmpDiscardStock");
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
