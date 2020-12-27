var isDebug =
  window.location.host == "studio.boardgamearena.com" ||
  window.location.hash.indexOf("debug") > -1;
var debug = isDebug ? console.info.bind(window.console) : function () {};

define(["dojo", "dojo/_base/declare", "ebg/core/gamegui"], (dojo, declare) => {
  return declare("customgame.game", ebg.core.gamegui, {
    /*
     * Constructor
     */
    constructor() {
      this._notifications = [];
    },

    /*
     * Detect if spectator or replay
     */
    isReadOnly() {
      return (
        this.isSpectator || typeof g_replayFrom != "undefined" || g_archive_mode
      );
    },
  });
});
