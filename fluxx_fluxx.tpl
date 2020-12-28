{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- fluxx implementation : © Alexandre Spaeth <alexandre.spaeth@hey.com> & Iwan Tomlow <iwan.tomlow@gmail.com> & Julien Rossignol <tacotaco.dev@gmail.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------

    fluxx_fluxx.tpl
-->

<div class="flx_player">
  <div class="whiteblock flx_hand">
    <h3>{MY_HAND}</h3>
    <div id="handStock"></div>
  </div>

  <div class="whiteblock flx_keepers">
    <h3 style="color: #{CURRENT_PLAYER_COLOR}">{MY_KEEPERS}</h3>
    <div id="keepersStock{CURRENT_PLAYER_ID}"></div>
  </div>
</div>

<div class="flx_table">
  <div class="whiteblock flx_deck">
    <div id="deckCard" class="flx_card flx_deck-card"></div>
    <div id="deckCount" class="flx_card-count"></div>
    <div id="discardStock"></div>
    <div id="discardCount" class="flx_card-count"></div>
  </div>

  <div class="whiteblock flx_goal">
    <h3>{GOAL}</h3>
    <div id="goalsStock"></div>
  </div>

  <div class="whiteblock flx_rules">
    <h3>{RULES}</h3>
    <div class="flx_card-stack">
      <div id="baseRuleCard" class="flx_card-stack-center"></div>
      <div id="drawRuleStock" class="flx_card-stack-left"></div>
      <div id="playRuleStock" class="flx_card-stack-right"></div>
    </div>
    <div id="keepersLimitStock"></div>
    <div id="handLimitStock"></div>
    <div id="othersStock"></div>
  </div>
</div>

<div class="flx_other_players">
  <!-- BEGIN keepers -->
  <div class="whiteblock flx_keepers">
    <h3 style="color: #{PLAYER_COLOR}">{PLAYER_NAME}</h3>
    <div id="keepersStock{PLAYER_ID}"></div>
  </div>
  <!-- END keepers -->
</div>

<script>
  var jstpl_player_board =
    '\
<div class="flx_board">\
  <div class="flx_board_hand">\
    <span id="handIcon${id}" class="flx_icons flx_icons_hand" aria-label="{HAND_COUNT}"></span>\
    <span id="handCount${id}"></span>\
  </div>\
  <div class="flx_board_keeper">\
    <span id="handIcon${id}" class="flx_icons flx_icons_keeper" aria-label="{KEEPERS_COUNT}"></span>\
    <span id="keepersCount${id}"></span>\
  </div>\
</div>';
</script>

{OVERALL_GAME_FOOTER}
