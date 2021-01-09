<?php
namespace Fluxx\Cards\Actions;

use Fluxx\Game\Utils;
use fluxx;

class ActionUseWhatYouTake extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Use What You Take");
    $this->description = clienttranslate(
      "Take a card at random form another player's hand, and play it."
    );
  }

  public $interactionNeeded = "playerSelection";

  public function resolvedBy($player_id, $args)
  {
    $game = Utils::getGame();
    $players = $game->loadPlayersBasicInfos();

    $player_name = $players[$player_id]["player_name"];
    $selected_player_id = $args["selected_player_id"];
    $selected_player_name = $players[$selected_player_id]["player_name"];

    $cards = $game->cards->getCardsInLocation("hand", $selected_player_id);
    $cardsCount = count($cards);

    if ($cardsCount == 0) {
      // No card to steal, nothing to do
      return null;
    }

    $i = bga_rand(0, $cardsCount - 1);
    $card = array_values($cards)[$i];
    $card_definition = $game->getCardDefinitionFor($card);

    $game->notifyPlayer($player_id, "cardsSentToPlayer", "", [
      "cards" => [$card],
      "player_id" => $selected_player_id,
    ]);
    $game->notifyPlayer($player_id, "cardsReceivedFromPlayer", "", [
      "cards" => [$card],
      "player_id" => $selected_player_id,
    ]);
    $game->notifyAllPlayers(
      "actionDone",
      clienttranslate(
        '${player_name} took ${card_name} from <b>${selected_player_name}</b>\'s hand (and must play it)'
      ),
      [
        "card_name" => $card_definition->getName(),
        "player_name" => $player_name,
        "selected_player_name" => $selected_player_name,
      ]
    );
    $game->sendHandCountNotifications();

    // @TODO: Use What You Take
    // Challenges: we need to play the chosen card once we are back to the "playCard"
    // state

    // Maybe: Add chosen card in the game state, in order to execute it when
    // back to playCard state?

    // This is similar to "let's do that again", so we shoud probably have a common solution
  }
}
