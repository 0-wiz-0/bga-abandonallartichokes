<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * AbandonAllArtichokes implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 * 
 * states.inc.php
 *
 * AbandonAllArtichokes game states description
 *
 */

/*
   Game state machine is a tool used to facilitate game developpement by doing common stuff that can be set up
   in a very easy way from this configuration file.

   Please check the BGA Studio presentation about game state to understand this, and associated documentation.

   Summary:

   States types:
   _ activeplayer: in this type of state, we expect some action from the active player.
   _ multipleactiveplayer: in this type of state, we expect some action from multiple players (the active players)
   _ game: this is an intermediary state where we don't expect any actions from players. Your game logic must decide what is the next game state.
   _ manager: special type for initial and final state

   Arguments of game states:
   _ name: the name of the GameState, in order you can recognize it on your own code.
   _ description: the description of the current game state is always displayed in the action status bar on
                  the top of the game. Most of the time this is useless for game state with "game" type.
   _ descriptionmyturn: the description of the current game state when it's your turn.
   _ type: defines the type of game states (activeplayer / multipleactiveplayer / game / manager)
   _ action: name of the method to call when this game state become the current game state. Usually, the
             action method is prefixed by "st" (ex: "stMyGameStateName").
   _ possibleactions: array that specify possible player actions on this step. It allows you to use "checkAction"
                      method on both client side (Javacript: this.checkAction) and server side (PHP: self::checkAction).
   _ transitions: the transitions are the possible paths to go from a game state to another. You must name
                  transitions in order to use transition names in "nextState" PHP method, and use IDs to
                  specify the next game state for each transition.
   _ args: name of the method to call to retrieve arguments for this gamestate. Arguments are sent to the
           client side to be used on "onEnteringState" or to set arguments in the gamestate description.
   _ updateGameProgression: when specified, the game progression is updated (=> call to your getGameProgression
                            method).
*/

//    !! It is not a good idea to modify this file when a game is running !!

// this needs to match the states in the .js file
if (!defined('STATE_END_GAME')) { // ensure this block is only invoked once, since it is included multiple times
   define("STATE_NEXT_PLAYER", 2);
   define("STATE_HARVEST", 3);
   define("STATE_PLAY_CARD", 4);
   define("STATE_LEEK_CHOOSE_OPPONENT", 5);
   define("STATE_LEEK_TAKE_CARD", 6);
   define("STATE_EGGPLANT_CHOOSE_CARDS", 7);
   define("STATE_EGGPLANT_DONE", 8);
   define("STATE_END_GAME", 99);
}
 
$machinestates = array(

    // The initial state. Please do not modify.
    1 => array(
        "name" => "gameSetup",
        "description" => "",
        "type" => "manager",
        "action" => "stGameSetup",
        "transitions" => array( "" => STATE_HARVEST)
    ),
    
    STATE_NEXT_PLAYER => array(
        "name" => "nextPlayer",
        "description" => clienttranslate('Player cleanup and refilling garden row'),
        "type" => "game",
        "action" => "stNextPlayer",
        "transitions" => array(STATE_HARVEST => STATE_HARVEST, STATE_END_GAME => STATE_END_GAME)
    ),

    STATE_HARVEST => array(
        "name" => "harvest",
        "description" => clienttranslate('${actplayer} must harvest a card from the garden row'),
        "descriptionmyturn" => clienttranslate('${you} must harvest a card from the garden row'),
        "type" => "activeplayer",
        "possibleactions" => array("harvestCard"),
        "transitions" => array(STATE_PLAY_CARD => STATE_PLAY_CARD),
    ),

    STATE_PLAY_CARD => array(
        "name" => "playCard",
        "description" => clienttranslate('${actplayer} must play a card or end their turn'),
        "descriptionmyturn" => clienttranslate('${you} must play a card or end your turn'),
        "type" => "activeplayer",
        "possibleactions" => array("playCard", "pass"),
        "transitions" => array(STATE_PLAY_CARD => STATE_PLAY_CARD,
                               STATE_NEXT_PLAYER => STATE_NEXT_PLAYER,
                               STATE_LEEK_CHOOSE_OPPONENT => STATE_LEEK_CHOOSE_OPPONENT,
                               STATE_LEEK_TAKE_CARD => STATE_LEEK_TAKE_CARD,
                               STATE_EGGPLANT_CHOOSE_CARDS => STATE_EGGPLANT_CHOOSE_CARDS),
    ),

    STATE_LEEK_CHOOSE_OPPONENT => array(
        "name" => "leekChooseOpponent",
        "description" => clienttranslate('${actplayer} must choose an opponent'),
        "descriptionmyturn" => clienttranslate('${you} must choose an opponent'),
        "type" => "activeplayer",
        "possibleactions" => array("leekChooseOpponent"),
        "transitions" => array(STATE_LEEK_TAKE_CARD => STATE_LEEK_TAKE_CARD),
    ),

    STATE_LEEK_TAKE_CARD => array(
        "name" => "leekTakeCard",
        "description" => clienttranslate('${actplayer} must take card or decline to take it'),
        "descriptionmyturn" => clienttranslate('${you} must take card or decline to take it'),
        "type" => "activeplayer",
        "possibleactions" => array("leekTakeCard"),
        "transitions" => array(STATE_PLAY_CARD => STATE_PLAY_CARD),
    ),

    STATE_EGGPLANT_CHOOSE_CARDS => array(
        "name" => "eggplantChooseCards",
        "description" => clienttranslate('${actplayer} must choose two cards to pass on'),
        "descriptionmyturn" => clienttranslate('${you} must choose two cards to pass on'),
        "action" => "stEggplantInit",
        "type" => "multipleactiveplayer",
        "possibleactions" => array("eggplantChooseCards"),
        "transitions" => array(STATE_EGGPLANT_DONE => STATE_EGGPLANT_DONE),
    ),

    STATE_EGGPLANT_DONE => array(
        "name" => "eggplantDone",
        "description" => clienttranslate('Passing cards for eggplant'),
        "type" => "game",
        "action" => "stEggplantDone",
        "transitions" => array(STATE_PLAY_CARD => STATE_PLAY_CARD),
    ),

    /*
    Examples:
    
    2 => array(
        "name" => "nextPlayer",
        "description" => '',
        "type" => "game",
        "action" => "stNextPlayer",
        "updateGameProgression" => true,   
        "transitions" => array( "endGame" => 99, "nextPlayer" => 10 )
    ),
    
    10 => array(
        "name" => "playerTurn",
        "description" => clienttranslate('${actplayer} must play a card or pass'),
        "descriptionmyturn" => clienttranslate('${you} must play a card or pass'),
        "type" => "activeplayer",
        "possibleactions" => array( "playCard", "pass" ),
        "transitions" => array( "playCard" => 2, "pass" => 2 )
    ), 

*/    
   
    // Final state.
    // Please do not modify (and do not overload action/args methods).
    STATE_END_GAME => array(
        "name" => "gameEnd",
        "description" => clienttranslate("End of game"),
        "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"
    )

);



