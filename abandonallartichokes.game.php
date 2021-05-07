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
  * abandonallartichokes.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */


require_once(APP_GAMEMODULE_PATH.'module/table/table.game.php');

class AbandonAllArtichokes extends Table
{
	function __construct()
	{
        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
        parent::__construct();
        
        self::initGameStateLabels(array(
            //    "my_first_global_variable" => 10,
            //    "my_second_global_variable" => 11,
            //      ...
            //    "my_first_game_variant" => 100,
            //    "my_second_game_variant" => 101,
            //      ...
       ));        

        $this->cards = self::getNew("module.common.deck");
        $this->cards->init("card");
    }
	
    protected function getGameName()
    {
		// Used for translations and stuff. Please do not modify.
        return "abandonallartichokes";
    }	

    /*
        setupNewGame:
        
        This method is called only once, when a new game is launched.
        In this method, you must setup the game according to the game rules, so that
        the game is ready to be played.
    */
    protected function setupNewGame($players, $options = array())
    {    
        // Set the colors of the players with HTML color code
        // The default below is red/green/blue/orange/brown
        // The number of colors defined here must correspond to the maximum number of players allowed for the gams
        $gameinfos = self::getGameinfos();
        $default_colors = $gameinfos['player_colors'];
 
        // Create players
        // Note: if you added some extra field on "player" table in the database (dbmodel.sql), you can initialize it there.
        $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES ";
        $values = array();
        foreach($players as $player_id => $player)
        {
            $color = array_shift($default_colors);
            $values[] = "('".$player_id."','$color','".$player['player_canal']."','".addslashes($player['player_name'])."','".addslashes($player['player_avatar'])."')";
        }
        $sql .= implode($values, ',');
        self::DbQuery($sql);
        self::reattributeColorsBasedOnPreferences($players, $gameinfos['player_colors']);
        self::reloadPlayersBasicInfos();
        
        /************ Start the game initialization *****/

        // Init global values with their initial values
        //self::setGameStateInitialValue('my_first_global_variable', 0);
        
        // Init game statistics
        // (note: statistics used in this file must be defined in your stats.inc.php file)
        //self::initStat('table', 'table_teststat1', 0);    // Init a table statistics
        //self::initStat('player', 'player_teststat1', 0);  // Init a player statistics (for all players)

        // TODO: setup the initial game situation here
       
        // Create cards
        $cards = array();
        foreach($this->vegetables as $vegetable_id => $vegetable)
        {
            if ($vegetable_id != VEGETABLE_ARTICHOKE) {
                //                $cards[] = array('type' => $vegetable_id, 'type_arg' => 0, 'nbr' => 6);
                $cards[] = array('type' => VEGETABLE_CARROT, 'type_arg' => 0, 'nbr' => 6);
            } else {
                $cards[] = array('type' => $vegetable_id, 'type_arg' => 0, 'nbr' => 10 * count($players));
            }
        }
        $this->cards->createCards($cards, "garden_stack");

        $artichokes = $this->cards->getCardsOfType(VEGETABLE_ARTICHOKE);
        if (count($artichokes) != 10 * count($players)) {
            throw new feException(self::_("Internal error during setup, please report"), true);
        }
        $player_no = 0;
        foreach($players as $player_id => $player)
        {
            $get_id = function($n) { return $n['id']; };
            $player_artichokes = array_slice($artichokes, 10 * $player_no, 10);
            $player_artichokes_2 = array_map($get_id, $player_artichokes);
            $this->cards->moveCards($player_artichokes_2, "deck_" . $player_id, 0);
        }

        // garden row
        $this->cards->shuffle("garden_stack");
        $this->cards->pickCardsForLocation(5, "garden_stack", STOCK_GARDEN_ROW);
        // player hands
        foreach ($players as $player_id => $player) {
            $this->cards->pickCards(5, "deck_" . $player_id, $player_id);
        }
        // Activate first player (which is in general a good idea :))
        $this->activeNextPlayer();

        /************ End of the game initialization *****/
    }

    /*
        getAllDatas: 
        
        Gather all informations about current game situation (visible by the current player).
        
        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
    */
    protected function getAllDatas()
    {
        $result = array();
    
        $current_player_id = self::getCurrentPlayerId();    // !! We must only return informations visible by this player !!
    
        // Get information about players
        // Note: you can retrieve some extra field you added for "player" table in "dbmodel.sql" if you need it.
        $sql = "SELECT player_id id, player_score score FROM player ";
        $result['players'] = self::getCollectionFromDb($sql);
  
        // TODO: Gather all information about current game situation (visible by player $current_player_id).
        $result[STOCK_GARDEN_ROW] = $this->cards->getCardsInLocation(STOCK_GARDEN_ROW);
        $result[STOCK_HAND] = $this->cards->getPlayerHand($current_player_id);
        $result[STOCK_PLAYED_CARD] = $this->cards->getCardsInLocation(STOCK_PLAYED_CARD);
        $compost = $this->cards->getCardOnTop(STOCK_COMPOST);
        $result[STOCK_COMPOST] = $compost ? array($compost) : array();

        return $result;
    }

    /*
        getGameProgression:
        
        Compute and return the current game progression.
        The number returned must be an integer beween 0 (=the game just started) and
        100 (= the game is finished or almost finished).
    
        This method is called each time we are in a game state with the "updateGameProgression" property set to true 
        (see states.inc.php)
    */
    function getGameProgression()
    {
        // TODO: compute and return the game progression

        return 0;
    }

    function stRefillGardenRow() {
        $row_no = $this->cards->countCardInLocation(STOCK_GARDEN_ROW);
        // This should always be true
        if ($row_no < 5) {
            $this->cards->pickCardsForLocation(5 - $row_no, "garden_stack", STOCK_GARDEN_ROW);
        }

        $player_id = self::activeNextPlayer();
        self::giveExtraTime($player_id);
        
        $this->gamestate->nextState("");
    }

    function harvestCard($id) {
        self::checkAction("harvestCard");
        $card = $this->cards->getCard($id);
        if ($card == null || $card['location'] != STOCK_GARDEN_ROW) {
            throw new feException(self::_("You must select a card from the garden row"), true);
        }

        $this->cards->moveCard($id, STOCK_HAND, self::getCurrentPlayerId());

        self::notifyAllPlayers(NOTIFICATION_HARVESTED_CARD, clienttranslate('${player_name} harvested ${vegetable}'), array(
            // TODO: translate vegetable
            'vegetable' => $this->vegetables[$card['type']]['name'],
            'player_name' => self::getActivePlayerName(),
            'type' => $card['type'],
            'player_id' => self::getCurrentPlayerId(),
            'card_id' => $id,
        ));

        $this->gamestate->nextState("playCard");
    }

    function playCard($id) {
        self::checkAction("playCard");
        $card = $this->cards->getCard($id);
        if ($card == null || $card['location'] != STOCK_HAND || $card['location_arg'] != self::getCurrentPlayerId()) {
            throw new feException(self::_("You must play a card from your hand"), true);
        }

        if ($card['type'] != VEGETABLE_CARROT) {
            throw new feException(self::_("You must play a carrot from your hand"), true);
        }

        // find artichokes to compost
        $hand = $this->cards->getPlayerHand(self::getCurrentPlayerId());
        $art1 = null;
        $art2 = null;
        foreach ($hand as $card) {
            if ($card['type'] == VEGETABLE_ARTICHOKE) {
                if ($art1 == null) {
                    $art1 = $card;
                } else {
                    $art2 = $card;
                    break;
                }
            }
        }
        if ($art2 == null) {
            throw new feException(self::_("You must have two artichokes in hand to play a carrot"), true);
        }

        $this->cards->moveCard($id, STOCK_PLAYED_CARD);
        $card = $this->cards->getCard($id);

        self::notifyAllPlayers(NOTIFICATION_PLAYED_CARD, clienttranslate('${player_name} played ${vegetable}'), array(
            // TODO: translate vegetable
            'vegetable' => $this->vegetables[$card['type']]['name'],
            'player_name' => self::getActivePlayerName(),
            'type' => $card['type'],
            'player_id' => self::getCurrentPlayerId(),
            'card_id' => $id,
            'origin' => STOCK_HAND,
            'origin_arg' => self::getCurrentPlayerId(),
        ));

        // compost them
        $this->move_to_compost($art1);
        $this->move_to_compost($art2);
        $this->move_to_compost($card);

        $this->gamestate->nextState("pass");
        //$this->gamestate->nextState("playCard");
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////    

    /*
        In this space, you can put any utility methods useful for your game logic
    */



//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
//////////// 

    /*
        Each time a player is doing some game action, one of the methods below is called.
        (note: each method below must match an input method in abandonallartichokes.action.php)
    */

    /*
    
    Example:

    function playCard($card_id)
    {
        // Check that this is the player's turn and that it is a "possible action" at this game state (see states.inc.php)
        self::checkAction('playCard'); 
        
        $player_id = self::getActivePlayerId();
        
        // Add your game logic to play a card there 
        ...
        
        // Notify all players about the card played
        self::notifyAllPlayers("cardPlayed", clienttranslate('${player_name} plays ${card_name}'), array(
            'player_id' => $player_id,
            'player_name' => self::getActivePlayerName(),
            'card_name' => $card_name,
            'card_id' => $card_id
       ));
          
    }
    
    */

    
//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments
////////////

    /*
        Here, you can create methods defined as "game state arguments" (see "args" property in states.inc.php).
        These methods function is to return some additional information that is specific to the current
        game state.
    */

    /*
    
    Example for game state "MyGameState":
    
    function argMyGameState()
    {
        // Get some values from the current game situation in database...
    
        // return values:
        return array(
            'variable1' => $value1,
            'variable2' => $value2,
            ...
       );
    }    
    */

//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////

    /*
        Here, you can create methods defined as "game state actions" (see "action" property in states.inc.php).
        The action method of state X is called everytime the current game state is set to X.
    */
    
    /*
    
    Example for game state "MyGameState":

    function stMyGameState()
    {
        // Do some stuff ...
        
        // (very often) go to another gamestate
        $this->gamestate->nextState('some_gamestate_transition');
    }    
    */

//////////////////////////////////////////////////////////////////////////////
//////////// Zombie
////////////

    /*
        zombieTurn:
        
        This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
        You can do whatever you want in order to make sure the turn of this player ends appropriately
        (ex: pass).
        
        Important: your zombie code will be called when the player leaves the game. This action is triggered
        from the main site and propagated to the gameserver from a server, not from a browser.
        As a consequence, there is no current player associated to this action. In your zombieTurn function,
        you must _never_ use getCurrentPlayerId() or getCurrentPlayerName(), otherwise it will fail with a "Not logged" error message. 
    */

    function zombieTurn($state, $active_player)
    {
    	$statename = $state['name'];
    	
        if ($state['type'] === "activeplayer") {
            switch ($statename) {
                default:
                    $this->gamestate->nextState("zombiePass");
                	break;
            }

            return;
        }

        if ($state['type'] === "multipleactiveplayer") {
            // Make sure player is in a non blocking status for role turn
            $this->gamestate->setPlayerNonMultiactive($active_player, '');
            
            return;
        }

        throw new feException("Zombie mode not supported at this game state: ".$statename);
    }
    
///////////////////////////////////////////////////////////////////////////////////:
////////// DB upgrade
//////////

    /*
        upgradeTableDb:
        
        You don't have to care about this until your game has been published on BGA.
        Once your game is on BGA, this method is called everytime the system detects a game running with your old
        Database scheme.
        In this case, if you change your Database scheme, you just have to apply the needed changes in order to
        update the game database and allow the game to continue to run with your new version.
    
    */
    
    function upgradeTableDb($from_version)
    {
        // $from_version is the current version of this game database, in numerical form.
        // For example, if the game was running with a release of your game named "140430-1345",
        // $from_version is equal to 1404301345
        
        // Example:
//        if($from_version <= 1404301345)
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "ALTER TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB($sql);
//        }
//        if($from_version <= 1405061421)
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "CREATE TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB($sql);
//        }
//        // Please add your future database scheme changes here
//
//


    }

    function move_to_compost($card) {
        $id = $card['id'];
        $type = $card['type'];

        $this->cards->moveCard($id, STOCK_COMPOST);
        self::notifyAllPlayers(NOTIFICATION_COMPOSTED_CARD, clienttranslate('${player_name} composted ${vegetable}'), array(
            // TODO: translate vegetable
            'vegetable' => $this->vegetables[$type]['name'],
            'player_name' => self::getActivePlayerName(),
            'type' => $type,
            'player_id' => self::getCurrentPlayerId(),
            'card_id' => $id,
            'origin' => $card['location'],
            'origin_arg' => $card['location_arg'],
        ));
    }
}
