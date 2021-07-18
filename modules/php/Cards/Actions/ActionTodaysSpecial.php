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

  public function immediateEffectOnPlay($player_id)
  {
    $game = Utils::getGame();
    $addInflation = Utils::getActiveInflation() ? 1 : 0;

    $cardsToDraw = 3 + $addInflation;
    // there must be enough available cards to draw, otherwise this action can't do anything
    // and remember we can't redraw this card itself (as it is still being resolved)
    $countAvailable = ($game->cards->countCardInLocation("discard") - 1)
      + $game->cards->countCardInLocation("deck");
    if ($countAvailable < $cardsToDraw) {
      $game->notifyAllPlayers(
        "actionIgnored",
        clienttranslate(
          'Not enough available cards to draw for <b>${card_name}</b>'
        ), [
          "i18n" => ["card_name"],
          "player_id" => $player_id,
          "card_name" => $this->getName(),
          ]
      );
      return null;
    }

    // else: go to normal resolve action phase
    return parent::immediateEffectOnPlay($player_id);
  }

  public function resolvedBy($player_id, $args)
  {
    $game = Utils::getGame();
    $addInflation = Utils::getActiveInflation() ? 1 : 0;

    $value = $args["value"];
    $nrCardsToDraw = 3;
    $choiceLabel = "";

    switch ($value) {
      case "birthday":
        $nrCardsToPlay = 3;
        $choiceLabel = clienttranslate("It's my Birthday!");
        break;
      case "holiday":
        $nrCardsToPlay = 2;
        $choiceLabel = clienttranslate("Holiday or Anniversary");
        break;
      default:
        $nrCardsToPlay = 1;
        $choiceLabel = clienttranslate("Just another day...");
    }

    $nrCardsToPlay;

    // notify about choice made
    $players = $game->loadPlayersBasicInfos();
    $game->notifyAllPlayers(
      "todayIsSpecial",
      clienttranslate('${player_name} says: ${today_choice}'),
      [
        "i18n" => ["today_choice"],
        "player_id" => $player_id,
        "player_name" => $players[$player_id]["player_name"],
        "today_choice" => $choiceLabel,
      ]
    );

    // determine temp hand to be used
    $tmpHandActive = Utils::getActiveTempHand();
    $tmpHandNext = $tmpHandActive + 1;

    $tmpHandLocation = "tmpHand" . $tmpHandNext;
    // Draw for temp hand

    // make sure we can't draw back this card itself (after reshuffle if deck would be empty)
    $game->cards->moveCard($this->getCardId(), "side", $player_id);

    $tmpCards = $game->performDrawCards(
      $player_id,
      $nrCardsToDraw + $addInflation,
      true, // $postponeCreeperResolve
      true
    ); // $temporaryDraw
    $tmpCardIds = array_column($tmpCards, "id");
    // Must Play a certain nr of them, depending on the choice made
    $game->setGameStateValue(
      $tmpHandLocation . "ToPlay",
      $nrCardsToPlay + $addInflation
    );
    $game->setGameStateValue($tmpHandLocation . "Card", $this->getUniqueId());

    // move cards to temporary hand location
    $game->cards->moveCards($tmpCardIds, $tmpHandLocation, $player_id);

    // move this card itself back to the discard pile
    $game->cards->playCard($this->getCardId());

    // done: next play run will detect temp hand active
  }
}
