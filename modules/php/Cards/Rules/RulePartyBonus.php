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
    // if Party already on the table, draw extra card for current player
    if (Utils::isPartyInPlay())
    {
      $addInflation = Utils::getActiveInflation() ? 1 : 0;

      $partyBonus = 1 + $addInflation;
      RulePartyBonus::notifyActiveFor($player_id);
      Utils::getGame()->performDrawCards($player_id, $partyBonus);
    }

    // +1 play will be accounted for automatically in PlayCardTrait,
    // when next checking if player should play more cards
  }

  public function immediateEffectOnDiscard($player_id)
  {
    Utils::getGame()->setGameStateValue("activePartyBonus", 0);
  }

  public static function notifyActiveFor($player_id)
  {
    $game = Utils::getGame();
    $game->notifyAllPlayers(
      "partyBonus",
      clienttranslate('Party Bonus active for ${player_name}'),
      [
        "player_id" => $player_id,
        "player_name" => $game->getActivePlayerName(),
      ]
    );
  }
}
