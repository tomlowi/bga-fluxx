<?php
namespace Fluxx\Cards\Actions;

use Fluxx\Game\Utils;
use fluxx;

class ActionDraw2AndUseEm extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Draw 2 and Use ‘Em");
    $this->description = clienttranslate(
      "Set your hand aside. Draw 2 cards, play them in any order you choose, then pick up your hand and continue with your turn. This card, and all cards played because of it, are counted as a single play."
    );
  }

  public $interactionNeeded = "TODO";

  public function immediateEffectOnPlay($player)
  {
    // nothing now, needs to go to resolve action state
  }

  public function resolvedBy($player, $option, $cardIdsSelected)
  {
    // options: none ?

    // @TODO: Draw 2 and Use ‘Em
    // Challenges: current hand needs to be set aside and player gets special turn with these 2 cards
    // this will probably require an entirely separate state?
    // and after all is done, current player needs to continue its turn

    Utils::getGame()->performDrawCards($player, 2);
  }
}
