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
 * material.inc.php
 *
 * AbandonAllArtichokes game material description
 *
 * Here, you can describe the material of your game with PHP variables.
 *   
 * This file is loaded in your game logic class constructor, ie these variables
 * are available everywhere in your game logic code.
 *
 */


/*

Example:

$this->card_types = array(
    1 => array( "card_name" => ...,
                ...
              )
);

*/

if (!defined('VEGETABLE_BEET')) { // ensure this block is only invoked once, since it is included multiple times
   define("VEGETABLE_BEET", 1);
   define("VEGETABLE_BROCCOLI", 2);
   define("VEGETABLE_CARROT", 3);
   define("VEGETABLE_CORN", 4);
   define("VEGETABLE_EGGPLANT", 5);
   define("VEGETABLE_LEEK", 6);
   define("VEGETABLE_ONION", 7);
   define("VEGETABLE_PEAS", 8);
   define("VEGETABLE_PEPPER", 9);
   define("VEGETABLE_POTATO", 10);
   define("VEGETABLE_ARTICHOKE", 11);
}

// these definitions need to match the ones in the constructor in the JavaScript code
if (!defined('STOCK_GARDEN_ROW')) { // ensure this block is only invoked once, since it is included multiple times
   define("STOCK_GARDEN_ROW", 'garden_row');
   define("STOCK_HAND", 'hand');
   define("STOCK_PLAYED_CARD", 'played_card');
   define("STOCK_COMPOST", 'compost');
}

// these definitions need to match the ones in the constructr in the JavaScript code
if (!defined('NOTIFICATION_PLAYED_CARD')) { // ensure this block is only invoked once, since it is included multiple times
    define("NOTIFICATION_PLAYED_CARD", "played_card");
    define("NOTIFICATION_HARVESTED_CARD", "harvested_card");
    define("NOTIFICATION_COMPOSTED_CARD", "composted_card");
}

$this->vegetables = array(
    VEGETABLE_BEET => array( 'name' => clienttranslate('beet'),
                             'nametr' => self::_('beet') ),
    VEGETABLE_BROCCOLI => array( 'name' => clienttranslate('broccoli'),
                                 'nametr' => self::_('broccoli') ),
    VEGETABLE_CARROT => array( 'name' => clienttranslate('carrot'),
                               'nametr' => self::_('carrot') ),
    VEGETABLE_CORN => array( 'name' => clienttranslate('corn'),
                             'nametr' => self::_('corn') ),
    VEGETABLE_EGGPLANT => array( 'name' => clienttranslate('eggplant'),
                                 'nametr' => self::_('eggplant') ),
    VEGETABLE_LEEK => array( 'name' => clienttranslate('leek'),
                             'nametr' => self::_('leek') ),
    VEGETABLE_ONION => array( 'name' => clienttranslate('onion'),
                              'nametr' => self::_('onion') ),
    VEGETABLE_PEAS => array( 'name' => clienttranslate('peas'),
                             'nametr' => self::_('peas') ),
    VEGETABLE_PEPPER => array( 'name' => clienttranslate('pepper'),
                               'nametr' => self::_('pepper') ),
    VEGETABLE_POTATO => array( 'name' => clienttranslate('potato'),
                               'nametr' => self::_('potato') ),
    VEGETABLE_ARTICHOKE => array( 'name' => clienttranslate('artichoke'),
                                  'nametr' => self::_('artichoke') ),
);
