<?php
namespace Fluxx\Cards\Actions;

use Fluxx\Game\Utils;
use fluxx;

class ActionTradeHands extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Trade Hands");
    $this->description = clienttranslate(
      "Trade your hand for the hand of one of your opponents. This is one of those times when you can get something for nothing!"
    );
  }

  public $interactionNeeded = "playerSelection";

  public function immediateEffectOnPlay($player)
  {
    // nothing now, needs to go to resolve action state
  }

  public function resolvedBy($player_id, $option, $cardIdsSelected)
  {
    $game = Utils::getGame();
    $players = $game->loadPlayersBasicInfos();

    $player_name = $players[$player_id]["player_name"];
    $selected_player_id = $option;
    $selected_player_name = $players[$selected_player_id]["player_name"];

    $selected_player_hand = $game->cards->getCardsInLocation(
      "hand",
      $selected_player_id
    );
    $player_hand = $game->cards->getCardsInLocation("hand", $player_id);

    $game->cards->moveCards(
      array_keys($selected_player_hand),
      "hand",
      $player_id
    );
    $game->cards->moveCards(
      array_keys($player_hand),
      "hand",
      $selected_player_id
    );
    $game->notifyPlayer($player_id, "cardsSentToPlayer", "", [
      "cards" => $player_hand,
      "player_id" => $selected_player_id,
    ]);
    $game->notifyPlayer($player_id, "cardsReceivedFromPlayer", "", [
      "cards" => $selected_player_hand,
      "player_id" => $selected_player_id,
    ]);
    $game->notifyPlayer($selected_player_id, "cardsSentToPlayer", "", [
      "cards" => $selected_player_hand,
      "player_id" => $player_id,
    ]);
    $game->notifyPlayer($selected_player_id, "cardsReceivedFromPlayer", "", [
      "cards" => $player_hand,
      "player_id" => $player_id,
    ]);

    $game->notifyAllPlayers(
      "actionDone",
      clienttranslate(
        '${player_name} trades hands with ${selected_player_name}'
      ),
      [
        "player_name" => $player_name,
        "selected_player_name" => $selected_player_name,
      ]
    );
    $game->sendHandCountNotifications();
  }
}
