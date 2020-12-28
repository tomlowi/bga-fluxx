<?php
namespace Fluxx\Cards\Goals;

use Fluxx\Game\Utils;

class GoalTwoKeepers extends GoalCard
{
    public function __construct($cardId, $uniqueId)
    {
        parent::__construct($cardId, $uniqueId);
        $this->keeper1 = -1;
        $this->keeper2 = -1;
    }

    public function goalReachedByPlayer()
    {
        $winner_id = $this->checkTwoKeepersWin($this->keeper1, $this->keeper2);

        return $winner_id;
    }

    function checkTwoKeepersWin(
        $keeper_nbr_A,
        $keeper_nbr_B,
        $without_B = false
    ) {
        $cards = Utils::getGame()->cards;

        $keeper_A = $cards->getCardsOfTypeInLocation(
            "keeper",
            $keeper_nbr_A,
            "keepers",
            null
        );
        $keeper_B = $cards->getCardsOfTypeInLocation(
            "keeper",
            $keeper_nbr_B,
            "keepers",
            null
        );

        if ($without_B) {
            // win if player has keeper A and keeper B is not in play anywhere
            if (count($keeper_A) != 0 && count($keeper_B) == 0) {
                $location_arg_A = null; // location_arg = player id
                foreach ($keeper_A as $card_id => $card) {
                    $location_arg_A = $card["location_arg"];
                }
                return $location_arg_A;
            } else {
                return null;
            }
        } else {
            // win if same player has both keeper A and keeper B
            if (count($keeper_A) != 0 && count($keeper_B) != 0) {
                $location_arg_A = null;
                $location_arg_B = null;
                foreach ($keeper_A as $card_id => $card) {
                    $location_arg_A = $card["location_arg"];
                }
                foreach ($keeper_B as $card_id => $card) {
                    $location_arg_B = $card["location_arg"];
                }
                // Check if keeper A and B are in same hand?
                if ($location_arg_B == $location_arg_A) {
                    return $location_arg_A;
                } else {
                    return null;
                }
            } else {
                return null;
            }
        }
    }
}
