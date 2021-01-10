<?php
namespace Fluxx\Cards\Rules;

use Fluxx\Game\Utils;

class RuleFirstPlayRandom extends RuleCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("First Play Random");
    $this->subtitle = clienttranslate("Takes Instant Effect");
    $this->description = clienttranslate(
      "The first card you play must be chosen at random from your hand by the player on your left. Ignore this rule if the current Rule card allow you to play only one card."
    );
  }

  public function immediateEffectOnPlay($player_id)
  {
    Utils::getGame()->setGameStateValue("activeFirstPlayRandom", 1);
  }

  public function immediateEffectOnDiscard($player_id)
  {
    Utils::getGame()->setGameStateValue("activeFirstPlayRandom", 0);
  }
}
