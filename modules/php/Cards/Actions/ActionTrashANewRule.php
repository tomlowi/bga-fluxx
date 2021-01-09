<?php
namespace Fluxx\Cards\Actions;

use Fluxx\Game\Utils;
use Fluxx\Cards\Rules\RuleCardFactory;
use fluxx;

class ActionTrashANewRule extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Trash a New Rule");
    $this->description = clienttranslate(
      "Select one of the New Rule cards in play and place it in the discard pile."
    );
  }

  public $interactionNeeded = "ruleSelection";

  public function immediateEffectOnPlay($player_id)
  {
    $game = Utils::getGame();
    $rulesInPlay = $game->cards->countCardInLocation("rules");
    if ($rulesInPlay == 0) {
      // no rules in play, this action does nothing
      return;
    }

    return parent::immediateEffectOnPlay($player_id);
  }

  public function resolvedBy($player_id, $args)
  {
    $game = Utils::getGame();

    $card = $args["card"];
    $card_definition = $game->getCardDefinitionFor($card);

    $card_location = $card["location"];

    if ($card_location != "rules") {
      Utils::throwInvalidUserAction(
        clienttranslate("You must select a new rule card in play on the table")
      );
    }

    $card_definition->immediateEffectOnDiscard($player_id);

    // remove this card from the table
    $game->cards->playCard($card["id"]);

    $game->notifyAllPlayers(
      "rulesDiscarded",
      clienttranslate('${player_name} trashed <b>${card_name}</b>'),
      [
        "player_name" => $game->getActivePlayerName(),
        "card_name" => $card_definition->getName(),
        "cards" => [$card],
        "discardCount" => $game->cards->countCardInLocation("discard"),
      ]
    );
  }
}
