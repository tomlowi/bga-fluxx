<?php
namespace Fluxx\Cards\Rules;

use Fluxx\Game\Utils;

class RulePoorBonus extends RuleCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Poor Bonus");
    $this->subtitle = clienttranslate("Takes Instant Effect");
    $this->description = clienttranslate(
      "If one player has fewer Keepers in play than anyone else, the number of cards drawn by this player is increased by 1. In the event of a tie, no player receives the bonus."
    );
  }

  public function immediateEffectOnPlay($player_id)
  {
    Utils::getGame()->setGameStateValue("activePoorBonus", 1);

    // if player is poor, immediately draw extra
    Utils::recheckForPoorBonus($player_id);
  }

  public function immediateEffectOnDiscard($player_id)
  {
    Utils::getGame()->setGameStateValue("activePoorBonus", 0);
  }

  public static function notifyActiveFor($player_id)
  {
    $game = Utils::getGame();
    $game->notifyAllPlayers(
      "poorBonus",
      clienttranslate('Poor Bonus active for ${player_name}'),
      [
        "player_id" => $player_id,
        "player_name" => $game->getActivePlayerName(),
      ]
    );
  }
}
