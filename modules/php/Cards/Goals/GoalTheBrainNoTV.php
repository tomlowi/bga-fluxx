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

    $brain_keeper_card = array_values(
      $cards->getCardsOfType("keeper", $this->brain_keeper)
    )[0];

    // Someone needs to have the brain
    if ($brain_keeper_card["location"] != "keepers") {
      return null;
    }

    $tv_keeper_cards = $cards->getCardsOfTypeInLocation(
      "keeper",
      $this->tv_keeper,
      "keepers"
    );

    // If anyone has the TV, can't win
    if (count($tv_keeper_card) > 0) {
      return null;
    }

    // Else the player with the brain wins
    return $brain_keeper_card["location_arg"];
  }
}
