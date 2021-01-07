<?php
namespace Fluxx\Cards\Rules;

use Fluxx\Game\Utils;

class RulePartyBonus extends RuleCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Party Bonus");
    $this->subtitle = clienttranslate("Takes Instant Effect");
    $this->description = clienttranslate(
      "If someone has the Party on the table, all players draw 1 extra card and play 1 extra card during their turns."
    );
  }

  public function immediateEffectOnPlay($player)
  {
    // @TODO : set game state?
    // + draw extra card for current player if Party on the table
  }

  public function immediateEffectOnDiscard($player)
  {
    // @TODO : unset game state?
  }
}
