<?php
namespace Fluxx\Cards\ActionCards;

use Fluxx\Game\Utils;

class ActionEverybodyGets1 extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Everybody gets 1");
    $this->description = clienttranslate(
      "Set your hand aside. Count the number of players in the game (including yourself). Draw enough cards to give 1 to each player, and then distribute them evenly amongst all the players. You decide who gets what."
    );
  }

  public function needsInteraction()
  {
    return false;
  }

  public function immediateEffectOnPlay($player)
  {
    $game = Utils::getGame();
    $players_ordered = $game->getPlayersInOrder();
    for ($i = 1; $i <= count($players_ordered); $i++) {
      $game->drawExtraCards($players_ordered[$i - 1], 1);
    }
  }

  public function resolvedBy($player, $option, $cardIdsSelected)
  {
    // @TODO: Everybody gets 1
    // Challenges: we need to show the drawn cards somewhere separately,
    // and allow player to choose which card goes to which player
  }
}
