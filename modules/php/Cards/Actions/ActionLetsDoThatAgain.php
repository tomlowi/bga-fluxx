<?php
namespace Fluxx\Cards\Actions;

use Fluxx\Game\Utils;
use fluxx;

class ActionLetsDoThatAgain extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Let’s Do That Again!");
    $this->description = clienttranslate(
      "Search through the discard pile. Take any Action or New Rule card you wish and immediately play it. Anyone may look through the discard pile at any time, but the order of what's in the pile should never be changed."
    );
  }

  public $interactionNeeded = "discardSelection";

  private function getRuleCardsInDiscard()
  {
    $game = Utils::getGame();
    $rulesInDiscard = $game->cards->getCardsOfTypeInLocation(
      "rule",
      null,
      "discard"
    );
    return $rulesInDiscard;
  }

  private function getActionCardsInDiscard()
  {
    $game = Utils::getGame();
    $actionsInDiscard = $game->cards->getCardsOfTypeInLocation(
      "action",
      null,
      "discard"
    );
    // have to remove LetsDoThatAgain itself
    // and also exclude any "Temp Hand" cards that are still being resolved
    $tmpHand1CardUniqueId = $game->getGameStateValue("tmpHand1Card");
    $tmpHand2CardUniqueId = $game->getGameStateValue("tmpHand2Card");
    $tmpHand3CardUniqueId = $game->getGameStateValue("tmpHand3Card");
    foreach ($actionsInDiscard as $card_id => $card) {
      $actionCardUniqueId = $card["type_arg"];
      if ($actionCardUniqueId == $this->getUniqueId()
          || $actionCardUniqueId == $tmpHand1CardUniqueId
          || $actionCardUniqueId == $tmpHand2CardUniqueId
          || $actionCardUniqueId == $tmpHand3CardUniqueId
          ) {
        unset($actionsInDiscard[$card["id"]]);
      }
    }

    return $actionsInDiscard;
  }

  public function immediateEffectOnPlay($player_id)
  {
    $game = Utils::getGame();

    $rulesInDiscard = $this->getRuleCardsInDiscard();
    $actionsInDiscard = $this->getActionCardsInDiscard();

    if (count($rulesInDiscard) == 0 && count($actionsInDiscard) == 0) {
      // no rules or actions in the discard, this action does nothing
      $game->notifyAllPlayers(
        "actionIgnored",
        clienttranslate(
          'There are no rule or action cards in the discard pile!'
        ), ["player_id" => $player_id]
      );

      return;
    }

    return parent::immediateEffectOnPlay($player_id);
  }

  public function resolveArgs()
  {
    $game = Utils::getGame();

    $rulesInDiscard = $this->getRuleCardsInDiscard();
    $actionsInDiscard = $this->getActionCardsInDiscard();

    return [
      "discard" => $rulesInDiscard + $actionsInDiscard,
    ];
  }

  public function resolvedBy($player_id, $args)
  {
    $game = Utils::getGame();

    $card = $args["card"];
    $card_definition = $game->getCardDefinitionFor($card);

    $cardType = $card["type"];

    if (
      $card["location"] != "discard" ||
      !in_array($cardType, ["rule", "action"])
    ) {
      Utils::throwInvalidUserAction(
        fluxx::totranslate(
          "You must select an action or rule card in the discard pile"
        )
      );
    }

    $game->notifyPlayer($player_id, "cardsDrawn", "", [
      "cards" => [$card],
    ]);
    $game->notifyAllPlayers(
      "actionDone",
      clienttranslate(
        '${player_name} took <b>${card_name}</b> from the discard pile (and must play it)'
      ),
      [
        "i18n" => ["card_name"],
        "card_name" => $card_definition->getName(),
        "player_name" => $game->getActivePlayerName(),
      ]
    );

    // We move this card in the player's hand
    $game->cards->moveCard($card["id"], "hand", $player_id);

    // And we mark it as the next "forcedCard"
    $game->setGameStateValue("forcedCard", $card["id"]);
  }
}
