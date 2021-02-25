<?php
namespace Fluxx\Cards\Creepers;

use Fluxx\Game\Utils;
use fluxx;

class CreeperWar extends CreeperCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("War");
    $this->subtitle = clienttranslate("Place Immediately + Redraw");
    $this->description = clienttranslate(
      "You cannot win if you have this, unless the Goal says otherwise. If you have Peace, you must move it to another player."
    );

    $this->help = clienttranslate(
      "Choose the player you want to move Peace to."
    );

    $this->peace_unique_id = 19;
  }

  public function preventsWinForGoal($goalCard)
  {
    $requiredForGoals = [151, 156];
    // War is required to win with these specific goals:
    // War is Death (151), Your Tax Dollars at War (156)
    if (in_array($goalCard->getUniqueId(), $requiredForGoals)) {
      return false;
    }

    return parent::preventsWinForGoal($goalCard);
  }

  public $interactionNeeded = "playerSelection";

  public function onCheckResolveKeepersAndCreepers($lastPlayedCard)
  {
    $game = Utils::getGame();
    // check who has War in play now
    $cardWar = array_values(
      $game->cards->getCardsOfType("creeper", $this->uniqueId)
    )[0];
    // if nobody, nothing to do
    if ($cardWar["location"] != "keepers") {
      return null;
    }

    $war_player_id = $cardWar["location_arg"];
    // If same player has Peace
    // => Peace must be move to another player

    $cardPeace = array_values(
      $game->cards->getCardsOfType("keeper", $this->peace_unique_id)
    )[0];

    if (
      $cardPeace["location"] == "keepers" &&
      $cardPeace["location_arg"] == $war_player_id
    ) {
      $game->setGameStateValue("creeperToResolvePlayerId", $war_player_id);
      $game->setGameStateValue("creeperToResolveCardId", $cardWar["id"]);

      return parent::onCheckResolveKeepersAndCreepers($lastPlayedCard);
    }

    return null;
  }

  public function resolvedBy($player_id, $args)
  {
    $game = Utils::getGame();
    $selected_player_id = $args["selected_player_id"];

    // move Peace keeper to the selected player
    $game = Utils::getGame();
    $cardPeace = array_values(
      $game->cards->getCardsOfType("keeper", $this->peace_unique_id)
    )[0];

    $card_location = $cardPeace["location"];
    $from_player_id = $cardPeace["location_arg"];

    if ($card_location != "keepers" || $from_player_id != $player_id) {
      Utils::throwInvalidUserAction(
        fluxx::totranslate("You don't have Peace in play")
      );
    }

    if ($selected_player_id == $from_player_id) {
      Utils::throwInvalidUserAction(
        fluxx::totranslate("You must move Peace to another player")
      );
    }

    // move Peace keeper to the selected player
    $game->cards->moveCard($cardPeace["id"], "keepers", $selected_player_id);

    $players = $game->loadPlayersBasicInfos();
    $selected_player_name = $players[$selected_player_id]["player_name"];

    $game->notifyAllPlayers(
      "keepersMoved",
      clienttranslate(
        '<b>${card_name}</b> drives away Peace to ${player_name2}'
      ),
      [
        "player_name2" => $selected_player_name,
        "card_name" => $this->name,
        "destination_player_id" => $selected_player_id,
        "origin_player_id" => $player_id,
        "cards" => [$cardPeace],
        "destination_creeperCount" => Utils::getPlayerCreeperCount(
          $selected_player_id
        ),
        "origin_creeperCount" => Utils::getPlayerCreeperCount($player_id),
      ]
    );

    return null;
  }
}
