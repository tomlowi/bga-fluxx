<?php
namespace Fluxx\Cards\ActionCards;

use Fluxx\Game\Utils;
use fluxx;

class ActionStealAKeeper extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Steal a Keeper");
    $this->description = clienttranslate(
      "Steal a Keeper from in front of another player, and add it to your collection of Keepers on the table."
    );
  }

  public function needsInteraction()
  {
    return true;
  }

  public function immediateEffectOnPlay($player)
  {
    // nothing now, needs to go to resolve action state
  }

  public function resolvedBy($player, $option, $cardIdsSelected)
  {
    // verify args has 1 card id, and it is a keeper in play
    // (or that no keepers are in play and args is empty)
    $game = Utils::getGame();
    $keepersInPlay = $game->cards->countCardInLocation("keepers");
    if ($keepersInPlay == 0) {
      // no keepers in play anywhere, this action does nothing
      return;
    }

    if (count($cardIdsSelected) != 1) {
      Utils::throwInvalidUserAction(
        fluxx::totranslate(
          "You must select exactly 1 Keeper card from in front of another player"
        )
      );
    }

    $cardId = $cardIdsSelected[0];
    $cardSelected = $game->cards->getCard($cardId);
    if (
      $cardSelected == null ||
      $cardSelected["location"] != "keepers" ||
      $cardSelected["location_arg"] == $player
    ) {
      Utils::throwInvalidUserAction(
        fluxx::totranslate(
          "You must select exactly 1 Keeper from in front of another player"
        )
      );
    }

    // move this keeper to the current player
    $game->cards->moveCard($cardId, "keepers", $player);
  }
}
