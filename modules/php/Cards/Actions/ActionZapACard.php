<?php
namespace Fluxx\Cards\Actions;

use Fluxx\Game\Utils;
use Fluxx\Cards\Rules\RuleCardFactory;
use fluxx;

class ActionZapACard extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Zap a Card!");
    $this->description = clienttranslate(
      "Choose any card in play, anywhere on the table (except for the Basic Rules) and add it to your hand."
    );
  }

  public function needsInteraction()
  {
    return true;
  }

  public function immediateEffectOnPlay($player)
  {
    // nothing now, needs to go to resolve action state
  }

  public function resolvedBy($player, $option, $cardIdsSelected)
  {
    // verify args has 1 card id, and it is a card in play
    // (or that no cards are in play and args is empty)
    $game = Utils::getGame();
    $keepersInPlay = $game->cards->countCardInLocation("keepers");
    $rulesInPlay = $game->cards->countCardInLocation("rules");
    $goalsInPlay = $game->cards->countCardInLocation("goals");
    if ($keepersInPlay + $rulesInPlay + $goalsInPlay == 0) {
      // no cards in play anywhere, this action does nothing
      return;
    }

    if (count($cardIdsSelected) != 1) {
      Utils::throwInvalidUserAction(
        fluxx::totranslate("You must select exactly 1 Card in play")
      );
    }

    $cardId = $cardIdsSelected[0];
    $cardSelected = $game->cards->getCard($cardId);
    $cardLocation = $cardSelected["location"];
    if (
      $cardSelected == null ||
      ($cardLocation != "keepers" &&
        $cardLocation != "rules" &&
        $cardLocation != "goals")
    ) {
      Utils::throwInvalidUserAction(
        fluxx::totranslate("You must select exactly 1 Card in play")
      );
    }

    // if a rule is taken back, its effect stops
    if ($cardLocation == "rules") {
      $rule = RuleCardFactory::getCard($cardId, $cardSelected["type_arg"]);
      $rule->immediateEffectOnDiscard($player);
    }

    // move this card from its current location to player hand
    $fromTarget = $cardSelected["location_arg"];
    $game->cards->moveCard($cardId, "hand", $player);
  }
}
