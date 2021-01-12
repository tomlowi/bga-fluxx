<?php
namespace Fluxx\Cards\Actions;

use Fluxx\Game\Utils;
use Fluxx\Cards\Rules\RuleCardFactory;

class ActionCreeperSweeper extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Creeper Sweeper");
    $this->description = clienttranslate(
      "All Creepers in play are discarded."
    );
  }

  public function immediateEffectOnPlay($player_id)
  {
    $game = Utils::getGame();
    
    $creeperCards = $game->cards->getCardsOfTypeInLocation(
      "creeper", null, "keepers", null);

    foreach ($creeperCards as $card_id => $card) {
      $this->cards->playCard($card_id);
    }

    if ($cards) {
      self::notifyAllPlayers("keepersDiscarded", "", [
        "cards" => $cards,
        "discardCount" => $this->cards->countCardInLocation("discard"),
      ]);
    }
  }
}
