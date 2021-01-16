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

  public $interactionNeeded = "keeperSelection";

  public function immediateEffectOnPlay($player_id)
  {
    $game = Utils::getGame();
    
    $creeperCards = $game->cards->getCardsOfTypeInLocation(
      "creeper", null, "keepers", null);
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
    $card_definition = $game->getCardDefinitionFor($card);

    $card_type = $card["type"];
    $card_location = $card["location"];
    $other_player_id = $card["location_arg"];

    if ($card_location != "keepers" || $card_type != "creeper") {
      Utils::throwInvalidUserAction(
        fluxx::totranslate(
          "You must select a creeper card in play"
        )
      );
    }

    // move this creeper to the current player
    // @TODO: should actually move to a selected player
    $game->cards->moveCard($card["id"], "keepers", $player_id);

    $players = $game->loadPlayersBasicInfos();
    $other_player_name = $players[$other_player_id]["player_name"];

    $game->notifyAllPlayers(
      "keepersMoved",
      clienttranslate(
        '${player_name} stole <b>${card_name}</b> from <b>${other_player_name}</b>'
      ),
      [
        "player_name" => $game->getActivePlayerName(),
        "other_player_name" => $other_player_name,
        "card_name" => $card_definition->getName(),
        "destination_player_id" => $player_id,
        "origin_player_id" => $other_player_id,
        "cards" => [$card],
      ]
    );
  }
}
