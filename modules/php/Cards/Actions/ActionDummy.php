<?php
namespace Fluxx\Cards\Actions;

use Fluxx\Game\Utils;
/*
 * ActionDummy: for not yet implemented actions
 */
class ActionDummy extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);
  }

  public function immediateEffectOnPlay($player)
  {
    $cardUniqueId = $this->uniqueId;

    Utils::getGame()->notifyAllPlayers(
      "actionNotImplemented",
      clienttranslate('Action <b>${unique_id}<b> not yet implemented'),
      [
        "unique_id" => $cardUniqueId,
      ]
    );

    return parent::immediateEffectOnPlay($player);
  }
}
