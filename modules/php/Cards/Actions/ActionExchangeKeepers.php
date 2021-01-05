<?php
namespace Fluxx\Cards\Actions;

use Fluxx\Game\Utils;
use fluxx;

class ActionExchangeKeepers extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Exchange Keepers");
    $this->description = clienttranslate(
      "Pick any Keeper another player has on the table and exchange it for one you have on the table. <be/> If you have no Keepers in play, or if no one else has a Keeper, nothing happens."
    );
  }

  public $interactionNeeded = "keepersExchange";

  public function immediateEffectOnPlay($player)
  {
    // nothing now, needs to go to resolve action state
  }

  public function resolvedBy($player, $option, $cardIdsSelected)
  {
    // verify args has 2 card ids:
    // 1 = keeper in play of this player
    // 1 = keeper in play of another player
    // (or that no keepers are in play and args is empty)
    $game = Utils::getGame();
    $keepersInPlay = $game->cards->countCardInLocation("keepers");
    $keepersOfPlayer = $game->cards->countCardInLocation("keepers", $player);
    if (
      $keepersInPlay == 0 ||
      $keepersOfPlayer == 0 ||
      $keepersInPlay == $keepersOfPlayer
    ) {
      // no keepers in play anywhere, or current player has no keepers,
      // or current player is only with keepers => this action does nothing
      return;
    }

    if (count($cardIdsSelected) != 2) {
      Utils::throwInvalidUserAction(
        fluxx::totranslate(
          "You must select exactly 2 Keeper cards, 1 of yours and 1 of another player"
        )
      );
    }

    $card1Id = $cardIdsSelected[0];
    $card1Selected = $game->cards->getCard($card1Id);
    $card1Player = $card1Selected["location_arg"];

    $card2Id = $cardIdsSelected[1];
    $card2Selected = $game->cards->getCard($card2Id);
    $card2Player = $card2Selected["location_arg"];

    // verify these cards are valid keepers to switch
    if (
      $card1Selected == null ||
      $card1Selected["location"] != "keepers" ||
      $card2Selected == null ||
      $card2Selected["location"] != "keepers" ||
      ($card1Player != $player && $card2Player != $player) ||
      ($card1Player == $player && $card2Player == $player)
    ) {
      Utils::throwInvalidUserAction(
        fluxx::totranslate(
          "You must select exactly 2 Keeper cards, 1 of yours and 1 of another player"
        )
      );
    }

    // switch the keeper locations
    $game->cards->moveCard($card1Id, "keepers", $card2Player);
    $game->cards->moveCard($card2Id, "keepers", $card1Player);
  }
}
