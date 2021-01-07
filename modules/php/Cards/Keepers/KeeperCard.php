<?php
namespace Fluxx\Cards\Keepers;

use Fluxx\Cards\Card;
use Fluxx\Game\Utils;
/*
 * KeeperCard: simple class to handle all keeper cards
 */
class KeeperCard extends Card
{
  public function __construct($cardId, $uniqueId, $cardName)
  {
    parent::__construct($cardId, $uniqueId);
    $this->name = $cardName;
  }
}
