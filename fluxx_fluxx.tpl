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

<div id="tmpHand" class="whiteblock flx-hand"></div>

<div class="flx-player">
  <div class="whiteblock flx-hand">
    <h3>{MY_HAND}</h3>
    <div id="handStock"></div>
  </div>
</div>

<div id="flxTable" class="flx-table flx-table-{PLAYERS_COUNT}players">
  <!-- BEGIN keepers -->
  <div class="whiteblock flx-keepers flx-keepers-{PLAYER_RANK}">
    <h3 style="color: #{PLAYER_COLOR}">{PLAYER_NAME}</h3>
    <div id="keepersStock{PLAYER_ID}"></div>
  </div>
  <!-- END keepers -->

  <div id="flxTableCenter" class="whiteblock flx-center">
    <div class="flx-goal">
      <h3>{GOAL}</h3>
      <div id="goalsStock"></div>
    </div>

    <div class="flx-deck">
      <div id="deckCard" class="flx-card flx-deck-card">
        <div id="deckCount" class="flx-card-count"></div>
      </div>
      <div class="flx-discard">
        <div id="discardCount" class="flx-card-count"></div>
        <div id="discardStock"></div>
      </div>

      <a id="discardToggleBtn" href="#">{SHOW_DISCARD}</a>
    </div>

    <div class="flx-rules">
      <h3>{RULES}</h3>
      <div class="flx-card-stack">
        <div id="baseRuleCard" class="flx-card-stack-center"></div>
        <div id="drawRuleStock" class="flx-card-stack-left"></div>
        <div id="playRuleStock" class="flx-card-stack-right"></div>
      </div>
      <div id="keepersLimitStock"></div>
      <div id="handLimitStock"></div>
      <div id="othersStock"></div>
    </div>
  </div>
</div>

<script>
  var jstpl_player_board =
    '\
<div class="flx-board">\
  <div class="flx-board-hand">\
    <span id="handIcon${id}" class="flx-icons flx-icons-hand" aria-label="{HAND_COUNT}"></span>\
    <span id="handCount${id}"></span>\
  </div>\
  <div class="flx-board-keeper">\
    <span id="handIcon${id}" class="flx-icons flx-icons-keeper" aria-label="{KEEPERS_COUNT}"></span>\
    <span id="keepersCount${id}"></span>\
  </div>\
</div>';

  var jstpl_cardTooltip = `<div class="flx-card-tooltip">
		<div class=">flx-card" id="flx-card-tooltip-\${id}">
			<div class="card-front">
				<div class="card-name">\${name}</div>
				<div class="card-subtitle">\${subtitle}</div>
				<div class="card-description">\${description}</div>
				<div class="card-type">\${type}</div>
				<div class="card-background"></div>
			</div>
		</div>
</div>`;
</script>

{OVERALL_GAME_FOOTER}
