<?php
namespace Fluxx\Cards\Creepers;

use Fluxx\Game\Utils;
use fluxx;

class CreeperDeath extends CreeperCard
{
    public function __construct($cardId, $uniqueId)
    {
        parent::__construct($cardId, $uniqueId);

        $this->name = clienttranslate("Death");
        $this->subtitle = clienttranslate("Place Immediately + Redraw");
        $this->description = clienttranslate(
            "You cannot win if you have this, unless the Goal says otherwise. If you have this at the start of your turn, discard something else you have in play (a Keeper or Creeper). You may discard this anytime it stands alone."
        );
    }

    public function preventsWinForGoal($goalCard)
    {
        $requiredForGoals = [151, 152, 153];
        // Death is required to win with these specific goals:
        // War is Death (151), All That is Certain (152), Death by Chocolate (153)
        if (in_array($goalCard->getUniqueId(), $requiredForGoals))
            return false;

        return parent::preventsWinForGoal($goalCard);
    }

    public $interactionNeeded = "keeperSelectionSelf";

    public function onTurnStart()
    {
        $game = Utils::getGame();        
        // check who has Death in play now
        $cardDeath = array_values(
            $game->cards->getCardsOfType("creeper", $this->uniqueId)
        )[0];
        // if nobody, nothing to do
        if ($cardDeath["location"] != "keepers")
            return null;

        $active_player_id = $game->getActivePlayerId();
        $death_player_id = $cardDeath["location_arg"];        
        // Death is not with active player
        if ($active_player_id != $death_player_id)
            return null;
        // if Death already executed, nothing to do
        if (0 != $game->getGameStateValue("creeperTurnStartDeathExecuted"))
            return null;
        // Current player has Death and must still resolve it
        $game->setGameStateValue("creeperToResolvePlayerId", $death_player_id);
        $game->setGameStateValue("creeperToResolveCardId", $cardDeath["id"]);

        return parent::onCheckResolveKeepersAndCreepers($cardDeath);
    }

    public function onCheckResolveKeepersAndCreepers($lastPlayedCard)
    {
        return null;
    }

    public function resolvedBy($player_id, $args)
    {
        $game = Utils::getGame();
        
        $card = $args["card"];
        // Death itself can only be removed if no other keepers/creepers in play
        // And only if Death is the only one, nothing needs to be discarded
        $playersKeepersInPlay = $game->cards->countCardInLocation(
            "keepers",
            $player_id
        );

        if ($playersKeepersInPlay > 1) {
            if ($card == null) {
                Utils::throwInvalidUserAction(
                    fluxx::totranslate(
                    "You must select a keeper or creeper card you have in play"
                    )
                );
            } else if ($card["type_arg"] == $this->uniqueId) {
                Utils::throwInvalidUserAction(
                    fluxx::totranslate(
                    "You cannot discard Death if you have other keeper or creeper cards"
                    )
                );            
            }
        }

        if ($card == null) {
            // Player has only Death and decided to keep it
            $game->setGameStateValue("creeperTurnStartDeathExecuted", 1);
            return;
        }

        $card_definition = $game->getCardDefinitionFor($card);

        $card_type = $card["type"];
        $card_location = $card["location"];
        $origin_player_id = $card["location_arg"];

        // Death can only kill Keepers or Creepers in play for this player
        if ( ($card_type != "keeper" && $card_type != "creeper") ||
            $card_location != "keepers" || $origin_player_id != $player_id) {
            Utils::throwInvalidUserAction(
                fluxx::totranslate(
                "You must select a keeper or creeper card you have in play"
                )
            );
        }
    
        $game->setGameStateValue("creeperTurnStartDeathExecuted", 1);
        // move this keeper/creeper to the discard
        $game->cards->playCard($card["id"]);

        $game->notifyAllPlayers(
            "keepersDiscarded",
            clienttranslate('Death killed <b>${card_name}</b> from ${player_name}'),
            [
                "player_name" => $game->getActivePlayerName(),
                "card_name" => $card_definition->getName(),
                "cards" => [$card],
                "player_id" => $origin_player_id,
                "discardCount" => $game->cards->countCardInLocation("discard"),
                "creeperCount" => Utils::getPlayerCreeperCount($origin_player_id),
            ]
        );
    }
}