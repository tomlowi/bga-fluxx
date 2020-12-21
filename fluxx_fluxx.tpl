{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- fluxx implementation : © Alexandre Spaeth <alexandre.spaeth@hey.com> & Julien Rossignol <tacotaco.dev@gmail.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------

    fluxx_fluxx.tpl
-->

<div class="whiteblock hand">
  <h3>{MY_HAND}</h3>
  <div id="handStock"></div>
</div>

<div class="whiteblock keepers">
  <h3 style="color: #{CURRENT_PLAYER_COLOR}">{MY_KEEPERS}</h3>
  <div id="keepersStock{CURRENT_PLAYER_ID}"></div>
</div>

<div class="whiteblock rules">
  <h3>Current rules</h3>
  <div id="baseRuleCard"></div>
  <div id="drawRuleCard"></div>
  <div id="playRuleCard"></div>
  <div id="keeperRuleCard"></div>
  <div id="rulesStock"></div>
</div>

<div class="whiteblock goal">
  <h3>Goal</h3>
  <div id="goalsStock"></div>
</div>

<!-- BEGIN keepers -->
<div class="whiteblock keepers">
  <h3 style="color: #{PLAYER_COLOR}">{PLAYER_NAME}</h3>
  <div id="keepersStock{PLAYER_ID}"></div>
</div>
<!-- END keepers -->

{OVERALL_GAME_FOOTER}
