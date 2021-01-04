<?php
namespace Fluxx\Cards\ActionCards;

use Fluxx\Game\Utils;
use Fluxx\Cards\NewRules\RuleCardFactory;

class ActionNoLimits extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("No Limits");
    $this->description = clienttranslate(
      "Discard all Hand and Keeper Limits currently in play."
    );
  }

  public function needsInteraction()
  {
    return false;
  }

  public function immediateEffectOnPlay($player)
  {
    $game = Utils::getGame();
    $handLimits = $game->cards->getCardsInLocation("rules", RULE_HAND_LIMIT);
    $keeperLimits = $game->cards->getCardsInLocation(
      "rules",
      RULE_KEEPERS_LIMIT
    );

    $rulesToDiscard = array_merge($handLimits, $keeperLimits);
    foreach ($rulesToDiscard as $card_id => $card) {
      $rule = RuleCardFactory::getCard($card_id, $card["type_arg"]);
      $rule->immediateEffectOnDiscard($player);

      // playCard = move to top of discard pile
      $game->cards->playCard($card_id);
    }
  }
}
