<?php
namespace Fluxx\Cards\Goals;

use Fluxx\Game\Utils;

class GoalPartySnacks extends GoalTwoKeepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Party Snacks");
    $this->subtitle = clienttranslate("The Party + at least 1 food Keeper");

    $this->party_keeper = 16;
    $this->food_keepers = [3, 4, 5, 6];
  }

  private function goalReachedByPlayerBasic()
  {
    $i = 0;
    $winner_id = null;
    while ($i < count($this->food_keepers) && $winner_id == null) {
      $winner_id = $this->checkTwoKeepersWin(
        $this->party_keeper,
        $this->food_keepers[$i]
      );
      $i++;
    }

    return $winner_id;
  }

  private function goalReachedByPlayerWithInflation()
  {
    $cards = Utils::getGame()->cards;
    $party_keeper_card = array_values(
      $cards->getCardsOfType("keeper", $this->party_keeper)
    )[0];
    // Someone needs to have Party
    if ($party_keeper_card["location"] != "keepers") {
      return null;
    }
    $possibleWinner = $party_keeper_card["location_arg"];
    // count how many food/snacks are also with this same player
    $i = 0;
    $food_count = 0;    
    while ($i < count($this->food_keepers)) {
      $food_keeper_card = array_values(
        $cards->getCardsOfType("keeper", $this->food_keepers[$i])
      )[0];

      if ($food_keeper_card["location"] == "keepers"
            && $food_keeper_card["location_arg"] == $possibleWinner) {
          $food_count++;
      }

      $i++;
    }

    if ($food_count >= 2) {
      return $possibleWinner;
    }    

    return null;
  }

  public function goalReachedByPlayer()
  {
    $activeInflation = Utils::getActiveInflation();

    if ($activeInflation) {
      // Inflation: need to have Party + any 2 snacks!
      return $this->goalReachedByPlayerWithInflation();
    } else {
      // Basic: win with Party + 1 snack
      return $this->goalReachedByPlayerBasic();
    }
  }
}
