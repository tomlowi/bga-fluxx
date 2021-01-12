<?php
namespace Fluxx\Cards\Creepers;

use Fluxx\Cards\Card;
use Fluxx\Game\Utils;
/*
 * CreeperCard: simple class to handle all creeper cards
 */
class CreeperCard extends Card
{
  public function __construct($cardId, $uniqueId, $cardName, $subtitle, $description)
  {
    parent::__construct($cardId, $uniqueId);
    $this->name = $cardName;
    $this->subtitle = $subtitle;
    $this->description = $description;
  }
}
