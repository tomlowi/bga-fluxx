<?php
namespace Fluxx\Cards\Rules;

use Fluxx\Game\Utils;

class RulePartyBonus extends RuleCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Party Bonus");
    $this->subtitle = clienttranslate("Takes Instant Effect");
    $this->description = clienttranslate(
      "If someone has the Party on the table, all players draw 1 extra card and play 1 extra card during their turns."
    );
  }

  public function immediateEffectOnPlay($player_id)
  {
    Utils::getGame()->setGameStateValue("activePartyBonus", 1);

    // if Party already on the table, immediately draw extra
    Utils::checkForPartyBonus($player_id);

    // +1 play will be accounted for automatically in PlayCardTrait,
    // when next checking if player should play more cards
  }

  public function immediateEffectOnDiscard($player_id)
  {
    Utils::getGame()->setGameStateValue("activePartyBonus", 0);
  }

  public static function notifyActiveFor($player_id, $onDraw)
  {
    $game = Utils::getGame();

    $msg = $onDraw
      ? clienttranslate('Party Bonus draw for ${player_name}')
      : clienttranslate('Party Bonus play for ${player_name}');

    // only log play once per turn, otherwise clutters log after each card played
    $alreadyLogged = $game->getGameStateValue("playerTurnLoggedPartyBonus");
    if (!$onDraw && $alreadyLogged != 0) {
      return;
    }

    $game->notifyAllPlayers("partyBonus", $msg, [
      "player_id" => $player_id,
      "player_name" => $game->getActivePlayerName(),
    ]);

    if (!$onDraw) {
      $game->setGameStateValue("playerTurnLoggedPartyBonus", 1);
    }    
  }
}
