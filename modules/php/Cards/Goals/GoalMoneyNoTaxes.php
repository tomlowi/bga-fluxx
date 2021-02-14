<?php
namespace Fluxx\Cards\Goals;

use Fluxx\Game\Utils;

class GoalMoneyNoTaxes extends GoalCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->set = "creeperpack";
    $this->name = clienttranslate("Money (No Taxes)");
    $this->description = clienttranslate(
      "If no one has Taxes on the table, the player with Money on the table wins."
    );

    $this->money_keeper = 7;
    $this->taxes_creeper = 52;
  }

  public function goalReachedByPlayer()
  {
    $cards = Utils::getGame()->cards;

    $money_keeper_card = array_values(
      $cards->getCardsOfType("keeper", $this->money_keeper)
    )[0];

    // Someone needs to have money
    if ($money_keeper_card["location"] != "keepers") {
      return null;
    }

    $taxes_creeper_cards = $cards->getCardsOfTypeInLocation(
      "creeper",
      $this->taxes_creeper,
      "keepers"
    );

    // If anyone has Taxes, can't win
    if (count($taxes_creeper_cards) > 0) {
      return null;
    }

    // Else the player with money wins
    return $money_keeper_card["location_arg"];
  }
}
