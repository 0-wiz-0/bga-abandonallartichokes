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

$this->vegetables = array(
    1 => array( 'name' => clienttranslate('beet'),
                'nametr' => self::_('beet') ),
    2 => array( 'name' => clienttranslate('broccoli'),
                'nametr' => self::_('broccoli') ),
    3 => array( 'name' => clienttranslate('carrot'),
                'nametr' => self::_('carrot') ),
    4 => array( 'name' => clienttranslate('corn'),
                'nametr' => self::_('corn') ),
    5 => array( 'name' => clienttranslate('eggplant'),
                'nametr' => self::_('eggplant') ),
    6 => array( 'name' => clienttranslate('leek'),
                'nametr' => self::_('leek') ),
    7 => array( 'name' => clienttranslate('onion'),
                'nametr' => self::_('onion') ),
    8 => array( 'name' => clienttranslate('peas'),
                'nametr' => self::_('peas') ),
    9 => array( 'name' => clienttranslate('pepper'),
                'nametr' => self::_('pepper') ),
    10 => array( 'name' => clienttranslate('potato'),
                 'nametr' => self::_('potato') ),
    11 => array( 'name' => clienttranslate('artichoke'),
                 'nametr' => self::_('artichoke') ),
);

$this->artichoke_id = 11;
