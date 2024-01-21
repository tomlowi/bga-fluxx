<?php
namespace Fluxx\Cards\Rules;

use Fluxx\Game\Utils;
/*
 * RuleDraw: base class for all Rule cards that adapt the current Draw rule
 */
class RuleDraw extends RuleCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);
  }

  public function getRuleType()
  {
    return "drawRule";
  }

  protected $drawCount;

  public function immediateEffectOnPlay($player_id)
  {
    $game = Utils::getGame();
    // current Draw Rule is changed immediately
    // active player might draw extra if they drew less at their turn start
    $oldValue = $game->getGameStateValue("drawnCards");
    // discard any other draw rules
    $game->discardRuleCardsForType("drawRule");
    // set new draw rule
    $game->setGameStateValue("drawRule", $this->drawCount);

    // make sure this card that is "in play" doesn't count for goal "10 cards in hand"
    $game->cards->moveCard($this->getCardId(), "side", $player_id);

    // draw extra cards for the difference
    if ($this->drawCount - $oldValue > 0) {
      $game->performDrawCards($player_id, $this->drawCount - $oldValue);
      $game->setGameStateValue("drawnCards", $this->drawCount);
    }
  }

  public function immediateEffectOnDiscard($player_id)
  {
    // reset to Basic Draw Rule
    Utils::getGame()->setGameStateValue("drawRule", 1);
  }
}
