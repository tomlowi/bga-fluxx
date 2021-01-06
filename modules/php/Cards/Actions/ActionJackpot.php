<?php
namespace Fluxx\Cards\Actions;

use Fluxx\Game\Utils;

class ActionJackpot extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Jackpot!");
    $this->description = clienttranslate("Draw 3 extra cards!");
  }

  public function immediateEffectOnPlay($player_id)
  {
    Utils::getGame()->performDrawCards($player, 3);
  }
}
