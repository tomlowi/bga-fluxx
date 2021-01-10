<?php
namespace Fluxx\Cards\Rules;

use Fluxx\Game\Utils;

class RuleInflation extends RuleCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Inflation");
    $this->subtitle = clienttranslate("Takes Instant Effect");
    $this->description = clienttranslate(
      "Any time a numeral is seen on another card, add one to that numeral. For example, 1 becomes 2, while one remains one. Yes, this affects the Basic Rules."
    );
  }

  public function immediateEffectOnPlay($player_id)
  {
    Utils::getGame()->setGameStateValue("activeInflation", 1);
    // Draw rule is adapted immediately, so current player draws an extra card
    Utils::getGame()->performDrawCards($player_id, 1);
  }

  public function immediateEffectOnDiscard($player_id)
  {
    Utils::getGame()->setGameStateValue("activeInflation", 0);
  }

  public static function notifyActiveFor($player_id)
  {
    $game = Utils::getGame();
    $game->notifyAllPlayers(
      "inflation",
      clienttranslate('Inflation active for ${player_name}'),
      [
        "player_id" => $player_id,
        "player_name" => $game->getActivePlayerName(),
      ]
    );
  }  
}
