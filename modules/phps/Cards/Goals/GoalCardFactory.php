<?php

namespace Fluxx\Cards\Goals;
use Fluxx\Cards\CardFactory;
/*
 * GoalCardFactory: how to create Goal Cards
 */
class GoalCardFactory extends CardFactory
{
    public static function getCardFullClassName($uniqueId)
    {
        $name = "Fluxx\Cards\Goals\\" . self::$classes[$uniqueId];
        return $name;
    }

    /*
     * cardClasses : for each card Id, the corresponding class name
     */
    public static $classes = [
        101 => "Goal10CardsInHand",
        102 => "Goal5Keepers",
        103 => "GoalTheAppliances",
        104 => "GoalBakedGoods",
        105 => "GoalTwoKeepers",
        106 => "GoalTheBrainNoTV",
        107 => "GoalTwoKeepers",
        108 => "GoalTwoKeepers",
        109 => "GoalTwoKeepers",
        110 => "GoalTwoKeepers",
        111 => "GoalTwoKeepers",
        112 => "GoalTwoKeepers",
        113 => "GoalTwoKeepers",
        114 => "GoalTwoKeepers",
        115 => "GoalTwoKeepers",
        116 => "GoalTwoKeepers",
        117 => "GoalTwoKeepers",
        118 => "GoalTwoKeepers",
        119 => "GoalTwoKeepers",
        120 => "GoalTwoKeepers",
        121 => "GoalOneKeeperWithAnyOf",
        122 => "GoalTwoKeepers",
        123 => "GoalTwoKeepers",
        124 => "GoalTwoKeepers",
        125 => "GoalTwoKeepers",
        126 => "GoalTwoKeepers",
        127 => "GoalTwoKeepers",
        128 => "GoalTwoKeepers",
        129 => "GoalTwoKeepers",
        130 => "GoalTwoKeepers",
    ];
}
