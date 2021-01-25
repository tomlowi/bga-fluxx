define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
  return declare("fluxx.states.playRockPaperScissors", null, {
    constructor() {
      this._notifications.push(["resultRockPaperScissors", null]);

      this._listeners = [];
    },

    onEnteringStatePlayRockPaperScissors: function (args) {
      console.log("Entering state: playRockPaperScissors", args);
    },

    onUpdateActionButtonsPlayRockPaperScissors: function (args) {
      console.log("Update Action Buttons: ActionResolve", args);

      if (this.isCurrentPlayerActive()) {
        // @TODO: improve UX
        this.addActionButton(
          "button_rock",
          _("Rock"),
          "onSelectRockPaperScissors"
        );
        this.addActionButton(
          "button_paper",
          _("Paper"),
          "onSelectRockPaperScissors"
        );
        this.addActionButton(
          "button_scissors",
          _("Scissors"),
          "onSelectRockPaperScissors"
        );
        dojo.attr("button_rock", "data-value", "rock");
        dojo.attr("button_paper", "data-value", "paper");
        dojo.attr("button_scissors", "data-value", "scissors");
      }
    },

    onSelectRockPaperScissors: function (ev) {
      var value = ev.target.getAttribute("data-value");

      var action = "selectRockPaperScissors";

      if (this.checkAction(action)) {
        this.ajaxAction(action, {
          value: value,
        });
      }
    },

    onLeavingStatePlayRockPaperScissors: function () {
      console.log("Leaving state: PlayRockPaperScissors");
    },

    notif_resultRockPaperScissors: function (notif) {
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
