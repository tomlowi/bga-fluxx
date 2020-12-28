<?php
namespace Fluxx\Cards\Goals;

use Fluxx\Game\Utils;

class GoalTheBrainNoTV extends GoalCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("The Brain (No TV)");
    $this->description = clienttranslate(
      "If no one has Television on the table, the player with The Brain on the table wins."
    );

    $this->brain_keeper = 2;
    $this->tv_keeper = 12;
  }

  public function goalReachedByPlayer()
  {
    $cards = Utils::getGame()->cards;

    $brain_keeper_card = $cards->getCard($this->brain_keeper);

    // Someone needs to have the brain
    if ($brain_keeper_card['location'] != 'keepers') {
      return null;
    }

    $tv_keeper_card = $cards->getCard($this->tv_keeper);

    // Noone needs to have the TV
    if ($tv_keeper_card['location'] == 'keepers') {
      return null;
    }

    // Then the player with the brain wins
    return $brain_keeper_card['location_arg']
  }
}
