<?php
namespace Fluxx\Cards\Rules;

use Fluxx\Game\Utils;

class RuleRecycling extends RuleCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Recycling");
    $this->subtitle = clienttranslate("Free Action");
    $this->description = clienttranslate(
      "Once during your turn, you may discard one of your Keepers from the table and draw 3 extra cards."
    );
  }

  public $interactionNeeded = "keeperSelectionSelf";

  public function canBeUsedInPlayerTurn($player_id)
  {
    $game = Utils::getGame();
    return Utils::playerHasNotYetUsedRecycling()
      && $game->cards->countCardInLocation("keepers", $player_id) > 0;
  }

  public function immediateEffectOnPlay($player)
  {
    // nothing
  }

  public function immediateEffectOnDiscard($player)
  {
    // nothing
  }

  public function freePlayInPlayerTurn($player_id)
  {
    $game = Utils::getGame();
    $game->setGameStateValue("playerTurnUsedRecycling", 1);    
    return parent::freePlayInPlayerTurn($player_id);
  }

  public function resolvedBy($player_id, $args)
  {
    // Validate args contains 1 card, 
    // and it is a Keeper in play for this player
    $myKeeper = $args["card"];

    if (
      $myKeeper["location"] != "keepers" ||
      $myKeeper["location_arg"] != $player_id
    ) {
      Utils::throwInvalidUserAction(
        fluxx::totranslate(
          "You must select exactly 1 of your Keeper cards in play"
        )
      );
    }

    $card_definition = $game->getCardDefinitionFor($card);
    // Discard it
    $game->cards->playCard($card["id"]);

    $game->notifyAllPlayers(
      "keepersDiscarded",
      clienttranslate('${player_name} recycled <b>${card_name}</b>'),
      [
        "player_name" => $game->getActivePlayerName(),
        "card_name" => $card_definition->getName(),
        "cards" => [$card],
        "player_id" => $player_id,
        "discardCount" => $game->cards->countCardInLocation("discard"),
      ]
    );

    // Draw 3 cards (+ inflation)    
    $addInflation = Utils::getActiveInflation() ? 1 : 0;
    $drawCount = 3 + $addInflation;
    $game->performDrawCards($player_id, $drawCount);

  }
}
