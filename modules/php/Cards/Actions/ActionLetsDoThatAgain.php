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
    return parent::immediateEffectOnPlay($player_id);
    // nothing now, needs to go to resolve action state
    // @TODO: send notification to open up the discard pile and make it visible?
  }

  public function resolvedBy($player_id, $args)
  {
    $option = $args["option"];
    $cardIdsSelected = $args["cardIdsSelected"];

    // verify args has 1 card id, and it is an Action or Rule card in the discard
    // (or that none are available in the discard pile)
    $game = Utils::getGame();
    $actionsInDiscard = $game->cards->getCardsOfTypeInLocation(
      "action",
      null,
      "discard"
    );
    $rulesInDiscard = $game->cards->getCardsOfTypeInLocation(
      "rule",
      null,
      "discard"
    );
    if ($actionsInDiscard == 0 && $rulesInDiscard == 0) {
      // nothing available in discard, this action does nothing
      return;
    }

    if (count($cardIdsSelected) != 1) {
      Utils::throwInvalidUserAction(
        fluxx::totranslate(
          "You must select exactly 1 Action or New Rule card from discard pile"
        )
      );
    }

    $cardId = $cardIdsSelected[0];
    $cardSelected = $game->cards->getCard($cardId);
    $cardType = $cardSelected["type"];
    if (
      $cardSelected == null ||
      $cardSelected["location"] != "discard" ||
      ($cardType != "action" && $cardType != "rule")
    ) {
      Utils::throwInvalidUserAction(
        fluxx::totranslate(
          "You must select exactly 1 Action or New Rule card from discard pile"
        )
      );
    }

    // temporary move to hand so it can be played again
    $game->cards->moveCard($cardId, "hand", $player_id);
    if ($cardType == "action") {
      // @TODO: check how this interrupts the game state of the current action
      $game->playActionCard($player_id, $cardSelected);
    } elseif ($cardType == "rule") {
      $game->playRuleCard($player_id, $cardSelected);
    }
  }
}
