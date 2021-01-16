<?php
namespace Fluxx\Cards\Rules;

use Fluxx\Game\Utils;

class RuleRichBonus extends RuleCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Rich Bonus");
    $this->subtitle = clienttranslate("Takes Instant Effect");
    $this->description = clienttranslate(
      "If one player has more Keepers in play than anyone else, the number of cards played by this player is increased by 1. In the event of a tie, no player receives the bonus."
    );
  }

  public function immediateEffectOnPlay($player_id)
  {
    Utils::getGame()->setGameStateValue("activeRichBonus", 1);
    // +1 play will be accounted for automatically in PlayCardTrait,
    // when next checking if player should play more cards
  }

  public function immediateEffectOnDiscard($player_id)
  {
    Utils::getGame()->setGameStateValue("activeRichBonus", 0);
  }

  public static function notifyActiveFor($player_id)
  {
    $game = Utils::getGame();
    $game->notifyAllPlayers(
      "richBonus",
      clienttranslate('Rich Bonus active for ${player_name}'),
      [
        "player_id" => $player_id,
        "player_name" => $game->getActivePlayerName(),
      ]
    );
  }
}
