<?php
namespace Fluxx\Cards\Creepers;

use Fluxx\Game\Utils;

class CreeperTaxes extends CreeperCard
{
    public function __construct($cardId, $uniqueId)
    {
        parent::__construct($cardId, $uniqueId);

        $this->name = clienttranslate("Taxes");
        $this->subtitle = clienttranslate("Place Immediately + Redraw");
        $this->description = clienttranslate(
            "You cannot win if you have this, unless the Goal says otherwise. If you have Money in play, you can discard it and this."
        );
    }

    public function preventsWinForGoal($goalCard)
    {
        $requiredForGoals = [152, 156];
        // Taxes is required to win with these specific goals:
        // All That is Certain (152), Your Tax Dollars at War (156)
        if (in_array($goalCard->getUniqueId(), $requiredForGoals))
            return false;

        return parent::preventsWinForGoal($goalCard);
    }

    public function onCheckResolveKeepersAndCreepers()
    {
        $game = Utils::getGame();
        // check who has Taxes in play now
        $cardTaxes = array_values(
            $game->cards->getCardsOfType("creeper", $this->uniqueId)
        )[0];
        // if nobody, nothing to do
        if ($cardTaxes["location"] != "keepers")
            return null;

        $taxes_player_id = $cardTaxes["location_arg"];
        // If same player has Money
        // => Taxes & Money evaporate each other and both are discarded

        $money_unique_id = 7;
        $cardMoney = array_values(
            $game->cards->getCardsOfType("keeper", $money_unique_id)
        )[0];

        if ($cardMoney["location"] == "keepers"
          && $cardMoney["location_arg"] == $taxes_player_id) {
            $game->cards->playCard($cardTaxes["id"]);
            $game->cards->playCard($cardMoney["id"]);

            $players = $game->loadPlayersBasicInfos();
            $taxes_player_name = $players[$taxes_player_id]["player_name"];

            $game->notifyAllPlayers(
                "keepersDiscarded",
                clienttranslate('<b>${card_name}</b> take all Money from ${player_name}'),
                [
                  "player_name" => $taxes_player_name,
                  "card_name" => $this->name,
                  "cards" => [$cardTaxes, $cardMoney],
                  "player_id" => $taxes_player_id,
                  "discardCount" => $game->cards->countCardInLocation("discard"),
                  "creeperCount" => Utils::getPlayerCreeperCount($taxes_player_id),
                ]
              );
        }

        return null;
    }
   
}