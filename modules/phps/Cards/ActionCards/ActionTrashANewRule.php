<?php
namespace Fluxx\Cards\ActionCards;

use Fluxx\Game\Utils;
use Fluxx\Cards\NewRules\RuleCardFactory;
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
    // verify args has 1 card id, and it is a Rule in play
    // (or that no rules are in play and args is empty)
    $game = Utils::getGame();
    $rulesInPlay = $game->cards->countCardInLocation("rules");
    if ($rulesInPlay == 0) {
      // no rules in play anywhere, this action does nothing
      return;
    }

    if (count($cardIdsSelected) != 1) {
      Utils::throwInvalidUserAction(
        fluxx::totranslate("You must select exactly 1 New Rule card in play")
      );
    }

    $cardId = $cardIdsSelected[0];
    $cardSelected = $game->cards->getCard($cardId);
    if ($cardSelected == null || $cardSelected["location"] != "rules") {
      Utils::throwInvalidUserAction(
        fluxx::totranslate("You must select exactly 1 New Rule card in play")
      );
    }

    // discard this rule from play
    $rule = RuleCardFactory::getCard($cardId);
    $rule->immediateEffectOnDiscard($player);

    $fromTarget = $cardSelected["location_arg"];
    $game->removeCardFromPlay(
      $player,
      $cardId,
      $cardSelected["type"],
      $fromTarget
    );
  }
}
