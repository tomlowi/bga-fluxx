<?php
namespace Fluxx\Cards\Actions;

use Fluxx\Game\Utils;
use fluxx;

class ActionTodaysSpecial extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Today’s Special!");
    $this->description = clienttranslate(
      "Set your hand aside and draw 3 cards. If today is your birthday, play all 3 cards. If today is a holiday or special anniversary, play 2 of the cards. If it's just another day, play only 1 card. Discard the remainder."
    );
  }

  public $interactionNeeded = "buttons";

  public function resolveArgs()
  {
    return [
      ["value" => "birthday", "label" => clienttranslate("It's my Birthday!")],
      [
        "value" => "holiday",
        "label" => clienttranslate("Holiday or Anniversary"),
      ],
      ["value" => "none", "label" => clienttranslate("Just another day...")],
    ];
  }

  public function resolvedBy($player_id, $args)
  {
    $addInflation = Utils::getActiveInflation() ? 1 : 0;

    $value = $args["value"];
    $nrCardsToDraw = 3 + $addInflation;

    switch ($value) {
      case "birthday":
        $nrCardsToPlay = 3;
        break;
      case "holiday":
        $nrCardsToPlay = 2;
        break;
      default:
        $nrCardsToPlay = 1;
    }

    $nrCardsToPlay += $addInflation;

    // @TODO: Today’s Special!
    // Challenges: current hand needs to be set aside and player gets special turn with these cards
    // this will probably require an entirely separate state?
    // and after all is done, current player needs to continue its turn

    // This is similar to "draw 2 and play them" and "draw 3, play 2" and must probably have a similar solution

    Utils::getGame()->performDrawCards($player_id, $nrCardsToDraw);
  }
}
