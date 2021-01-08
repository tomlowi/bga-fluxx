<?php
namespace Fluxx\Cards\Actions;

use Fluxx\Game\Utils;
use fluxx;

class ActionLetsDoThatAgain extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Let’s Do That Again!");
    $this->description = clienttranslate(
      "Search through the discard pile. Take any Action or New Rule card you wish and immediately play it. Anyone may look through the discard pile at any time, but the order of what's in the pile should never be changed."
    );
  }

  public $interactionNeeded = "discardSelection";

  public function immediateEffectOnPlay($player_id)
  {
    $game = Utils::getGame();

    $rulesInDiscard = $game->cards->getCardsOfTypeInLocation(
      "rule",
      null,
      "discard"
    );

    $actionsInDiscard = $game->cards->getCardsOfTypeInLocation(
      "action",
      null,
      "discard"
    );

    if (count($rulesInDiscard) == 0 && count($actionsInDiscard) == 0) {
      // no rules or actions in the discard, this action does nothing
      $game->notifyAllPlayers(
        "",
        clienttranslate(
          "There are no rule or action cards in the discard pile!"
        )
      );

      return;
    }

    return parent::immediateEffectOnPlay($player_id);
  }

  public function resolveArgs()
  {
    $game = Utils::getGame();

    $rulesInDiscard = $game->cards->getCardsOfTypeInLocation(
      "rule",
      null,
      "discard"
    );

    $actionsInDiscard = $game->cards->getCardsOfTypeInLocation(
      "action",
      null,
      "discard"
    );

    return [
      "discard" => $rulesInDiscard + $actionsInDiscard,
    ];
  }

  public function resolvedBy($player_id, $args)
  {
    $game = Utils::getGame();

    $card = $args["card"];
    $card_definition = $game->getCardDefinitionFor($card);

    $cardType = $card["type"];

    if (
      $card["location"] != "discard" ||
      !in_array($cardType, ["rule", "action"])
    ) {
      Utils::throwInvalidUserAction(
        clienttranslate(
          "You must select an action or rule card in the discard pile"
        )
      );
    }

    // @TODO: Everybody gets 1
    // Challenges: we need to play the chosen card once we are back to the "playCard"
    // state

    // Maybe: Add chosen card in the game state, in order to execute it when
    // back to playCard state?
  }
}
