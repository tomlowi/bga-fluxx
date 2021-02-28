<?php
namespace Fluxx\Cards\Rules;

use Fluxx\Game\Utils;

class RuleMysteryPlay extends RuleCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Mystery Play");
    $this->subtitle = clienttranslate("Free Action");
    $this->description = clienttranslate(
      "Once during your turn, you may take the top card from the draw pile and play it immediately."
    );
  }

  public function canBeUsedInPlayerTurn($player_id)
  {
    return Utils::playerHasNotYetUsedMysteryPlay();
  }

  public function immediateEffectOnPlay($player)
  {
    // nothing
  }

  public function immediateEffectOnDiscard($player)
  {
    // nothing
  }

  public function freePlayInPlayerTurn($player_id)
  {
    $game = Utils::getGame();
    $game->setGameStateValue("playerTurnUsedMysteryPlay", 1);

    // draw top card (this is moved to hand automatically)
    $card = $game->cards->pickCard("deck", $player_id);
    // if no more cards to draw, nothing happens
    if ($card == null) {
      return;
    }

    $game->notifyPlayer($player_id, "cardsDrawn", "", [
      "cards" => [$card],
    ]);

    // And we mark it as the next "forcedCard" to play
    $game->setGameStateValue("forcedCard", $card["id"]);
  }
}
