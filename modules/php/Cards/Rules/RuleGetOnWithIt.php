<?php
namespace Fluxx\Cards\Rules;

use Fluxx\Game\Utils;

class RuleGetOnWithIt extends RuleCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Get On With It!");
    $this->subtitle = clienttranslate("Free Action");
    $this->description = clienttranslate(
      "Before your final play, if you are not empty handed, you may discard your entire hand and draw 3 cards. Your turn then ends immediately."
    );
  }

  public function canBeUsedInPlayerTurn($player_id)
  {
    return true;
  }
  
  public function immediateEffectOnPlay($player)
  {
    // @TODO
  }

  public function immediateEffectOnDiscard($player)
  {
    // nothing
  }
}
