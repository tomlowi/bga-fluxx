<?php
namespace Fluxx\Cards\ActionCards;

use Fluxx\Game\Utils;
use Fluxx\Cards\NewRules\RuleCardFactory;

class ActionRulesReset extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Rules Reset");
    $this->description = clienttranslate(
      "Reset to the Basic Rules. Discard all New Rule cards, and leave only the Basic Rules in play. Do not discard the current Goal."
    );
  }

  public function needsInteraction()
  {
    return false;
  }

  public function immediateEffectOnPlay($player)
  {
    $rulesInPlay = Utils::getGame()->cards->getCardsInLocation("rules");
    foreach ($rulesInPlay as $card_id => $card) {
      $rule = RuleCardFactory::getCard($card_id, $card["type_arg"]);
      $rule->immediateEffectOnDiscard($player);

      // playCard = move to top of discard pile
      Utils::getGame()->cards->playCard($card_id);
    }
  }
}
