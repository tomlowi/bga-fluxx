<?php
namespace Fluxx\Cards\Creepers;

use Fluxx\Cards\Card;
use Fluxx\Game\Utils;
/*
 * CreeperCard: simple class to handle all creeper cards
 */
class CreeperCard extends Card
{
  public function __construct(
    $cardId,
    $uniqueId
  ) {
    parent::__construct($cardId, $uniqueId);
  }

  // Creepers in play globally prevent the player winning with almost all basic Goals
  public function preventsWinForGoal($goalCard) {
    return true;
  }

  // @TODO: some Creepers have special side effects like moving/discarding other Keepers
}
