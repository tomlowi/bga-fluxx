<?php
namespace Fluxx\Cards\ActionCards;

use Fluxx\Game\Utils;
use fluxx;

class ActionTrashAKeeper extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Trash a Keeper");
    $this->description = clienttranslate(
      "Take a Keeper from in front of any player and put it on the discard pile. <br/> If no one has any Keepers in play, nothing happens when you play this card."
    );
  }

  public function needsInteraction()
  {
    return true;
  }

  public function immediateEffectOnPlay($player)
  {
    // nothing now, needs to go to resolve action state
  }

  public function resolvedBy($player, $args)
  {
    self::dump("====resolve args====", $args);
    // verify args has 1 card id, and it is a keeper in play
    // (or that no keepers are in play and args is empty)
    $game = Utils::getGame();
    $keepersInPlay = $game->cards->countCardInLocation("keepers");
    if ($keepersInPlay == 0) 
    { // no keepers in play anywhere, this action does nothing
      return;
    }

    if (count($args) != 1)
    {
      Utils::throwInvalidUserAction(fluxx::totranslate("You must select exactly 1 Keeper card in play"));
    }

    $cardId = $args[0];
    $cardSelected = $game->cards->getCard($cardId);
    if ($cardSelected == null || $cardSelected["location"] != "keepers")
    {
      Utils::throwInvalidUserAction(fluxx::totranslate("You must select exactly 1 Keeper card in play"));
    }

    // discard this keeper from play
    $fromPlayer = $cardSelected["location_arg"];
    $game->removeCardFromPlay($player, $cardId, $cardSelected["type"], $fromPlayer);
    
  }
}
