<?php
namespace Fluxx\Cards\Actions;

use Fluxx\Game\Utils;
use fluxx;

class ActionMoveACreeper extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Move a Creeper");
    $this->description = clienttranslate(
      "Choose any Creeper in front of any player and move it to some other player."
    );
  }

  public $interactionNeeded = "keeperAndPlayerSelectionAny";

  public function immediateEffectOnPlay($player_id)
  {
    $game = Utils::getGame();

    $creeperCards = $game->cards->getCardsOfTypeInLocation(
      "creeper",
      null,
      "keepers",
      null
    );
    if (count($creeperCards) == 0) {
      // no creepers on the table for any player, this action does nothing
      return;
    }

    return parent::immediateEffectOnPlay($player_id);
  }

  public function resolvedBy($player_id, $args)
  {
    $game = Utils::getGame();

    $card = $args["card"];
    $selected_player_id = $args["selected_player_id"];

    $card_definition = $game->getCardDefinitionFor($card);

    $card_type = $card["type"];
    $card_location = $card["location"];
    $other_player_id = $card["location_arg"];

    if ($card_location != "keepers" || $card_type != "creeper") {
      Utils::throwInvalidUserAction(
        fluxx::totranslate("You must select a creeper card in play")
      );
    }

    if ($selected_player_id == $other_player_id) {
      Utils::throwInvalidUserAction(
        fluxx::totranslate("You must move the creeper to a player different from the current owner")
      );
    }

    // move this creeper to the selected player
    $game->cards->moveCard($card["id"], "keepers", $selected_player_id);

    $players = $game->loadPlayersBasicInfos();
    $other_player_name = $players[$other_player_id]["player_name"];
    $selected_player_name = $players[$selected_player_id]["player_name"];

    $game->notifyAllPlayers(
      "keepersMoved",
      clienttranslate(
        '${player_name} moved <b>${card_name}</b> from <b>${other_player_name}</b> to <b>${selected_player_name}</b>'
      ),
      [
        "player_name" => $game->getActivePlayerName(),
        "other_player_name" => $other_player_name,
        "selected_player_name" => $selected_player_name,
        "card_name" => $card_definition->getName(),
        "destination_player_id" => $selected_player_id,
        "origin_player_id" => $other_player_id,
        "cards" => [$card],
      ]
    );
  }
}
