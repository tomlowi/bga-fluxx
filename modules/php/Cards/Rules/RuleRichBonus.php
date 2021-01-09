<?php
namespace Fluxx\Cards\Rules;

use Fluxx\Game\Utils;

class RuleRichBonus extends RuleCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Rich Bonus");
    $this->subtitle = clienttranslate("Takes Instant Effect");
    $this->description = clienttranslate(
      "If one player has more Keepers in play than anyone else, the number of cards played by this player is increased by 1. In the event of a tie, no player receives the bonus."
    );
  }

  public function immediateEffectOnPlay($player)
  {
    Utils::getGame()->setGameStateValue("activeRichBonus", 1);
    // @TODO: allow play extra card if current player is Rich
  }

  public function immediateEffectOnDiscard($player)
  {
    Utils::getGame()->setGameStateValue("activeRichBonus", 0);
  }
}
