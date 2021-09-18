<?php
namespace Fluxx\Cards\Actions;

use Fluxx\Game\Utils;
use fluxx;

class ActionEverybodyGets1 extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Everybody Gets 1");
    $this->description = clienttranslate(
      "Set your hand aside. Count the number of players in the game (including yourself). Draw enough cards to give 1 to each player, and then distribute them evenly amongst all the players. You decide who gets what."
    );

    $this->help =
      clienttranslate(
        "Select what you want to distribute to this player. Click the button when finished."
      );
  }

  public $interactionNeeded = "tmpCardsSelectionForPlayer";

  public function immediateEffectOnPlay($player_id)
  {
    $addInflation = Utils::getActiveInflation() ? 1 : 0;
    $cardsPerPlayer = 1 + $addInflation;

    $game = Utils::getGame();
    $players_ordered = $game->getPlayersInOrderForActivePlayer();

    $cardsToDraw = count($players_ordered) * $cardsPerPlayer;
    // there must be enough available cards to draw, otherwise this action can't do anything
    // and remember we can't redraw this card itself (as it is still being resolved)
    $countAvailable = ($game->cards->countCardInLocation("discard") - 1)
      + $game->cards->countCardInLocation("deck");
    if ($countAvailable < $cardsToDraw) {
      $game->notifyAllPlayers(
        "actionIgnored",
        clienttranslate(
          'Not enough available cards to draw for <b>${card_name}</b>'
        ), [
          "i18n" => ["card_name"],
          "player_id" => $player_id,
          "card_name" => $this->getName(),
          ]
      );
      return null;
    }

    // make sure we can't draw back this card itself (after reshuffle if deck would be empty)
    $game->cards->moveCard($this->getCardId(), "side", $player_id);

    $tmpCards = $game->performDrawCards(
      $player_id,
      $cardsToDraw,
      true, // $postponeCreeperResolve
      true
    ); // $temporaryDraw
    $tmpCardIds = array_column($tmpCards, "id");

    // move cards to temporary select location and let player choose who gets what
    $game->cards->moveCards($tmpCardIds, "tmpSelectCards", $player_id);

    // move this card itself back to the discard pile
    $game->cards->playCard($this->getCardId());

    return parent::immediateEffectOnPlay($player_id);
  }

  public function resolveArgs()
  {
    $addInflation = Utils::getActiveInflation() ? 1 : 0;
    $cardsPerPlayer = 1 + $addInflation;

    $game = Utils::getGame();
    $tmpCards = $game->cards->getCardsInLocation("tmpSelectCards");

    $players_ordered = $game->getPlayersInOrderForActivePlayer();
    $playerCount = count($players_ordered);
    $remainingCardCount = count($tmpCards) / $cardsPerPlayer;
    // active player starts with selecting cards for themself, then around the table
    $forPlayerId = $players_ordered[$playerCount - $remainingCardCount];

    return [
      "cards" => $tmpCards,
      "cardsPerPlayer" => $cardsPerPlayer,
      "forPlayerId" => $forPlayerId,
    ];
  }

  public function resolvedBy($player_id, $args)
  {
    // move selected cards to the selected player
    $cards = $args["cards"];
    $resolveArgs = $this->resolveArgs();

    // verify arguments
    if (count($cards) != $resolveArgs["cardsPerPlayer"]) {
      Utils::throwInvalidUserAction(
        fluxx::totranslate("Wrong number of cards. Expected: ") .
          $resolveArgs["cardsPerPlayer"]
      );
    }

    foreach ($cards as $card) {
      if ($card == null || $card["location"] != "tmpSelectCards") {
        Utils::throwInvalidUserAction(
          fluxx::totranslate(
            "You can only select cards from the temporary hand"
          )
        );
      }
    }

    $selected_player_id = $resolveArgs["forPlayerId"];

    $game = Utils::getGame();
    $game->cards->moveCards(
      array_column($cards, "id"),
      "hand",
      $selected_player_id
    );

    $game->notifyPlayer($selected_player_id, "cardsDrawn", "", [
      "cards" => $cards,
    ]);

    // repeat this state for next player, as long as there are more cards to distribute
    $countToDistribute = $game->cards->countCardInLocation(
      "tmpSelectCards",
      $player_id
    );
    if ($countToDistribute > 0) {
      return parent::immediateEffectOnPlay($player_id);
    } else {
      // we gave cards to other players: check for hand limits
      $game->sendHandCountNotifications();
      return "handsExchangeOccured";
    }
  }
}
