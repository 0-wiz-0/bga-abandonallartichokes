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
            "cards_played_this_turn" => 10,
            "target_player" => 11,
            //    "my_first_global_variable" => 10,
            //    "my_second_global_variable" => 11,
            //      ...
            //    "my_first_game_variant" => 100,
            //    "my_second_game_variant" => 101,
            //      ...
       ));

        $this->cards = self::getNew("module.common.deck");
        $this->cards->init("card");
        $this->cards->autoreshuffle = true;
        $this->cards->autoreshuffle_custom = [
            'deck_1' => 'discard_1',
            'deck_2' => 'discard_2',
            'deck_3' => 'discard_3',
            'deck_4' => 'discard_4',
        ];
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
        $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES ";
        $values = array();
        $player_no = 1;
        foreach ($players as $player_id => $player)
        {
            $color = array_shift($default_colors);
            $values[] = "('" . $player_id . "','" . $color . "','" . $player['player_canal'] . "','" . addslashes($player['player_name']) . "','" . addslashes($player['player_avatar']) . "')";
            $player_no++;
        }
        $sql .= implode($values, ',');
        self::DbQuery($sql);
        self::reattributeColorsBasedOnPreferences($players, $gameinfos['player_colors']);
        self::reloadPlayersBasicInfos();

        /************ Start the game initialization *****/

        // Init global values with their initial values
        self::setGameStateInitialValue(GAME_STATE_CARDS_PLAYED_THIS_TURN, 0);

        // Init game statistics
        // (note: statistics used in this file must be defined in your stats.inc.php file)
        //self::initStat('table', 'table_teststat1', 0);    // Init a table statistics
        //self::initStat('player', 'player_teststat1', 0);  // Init a player statistics (for all players)

        // TODO: setup the initial game situation here

        // Create cards
        $cards = array();
        foreach ($this->vegetables as $vegetable_id => $vegetable)
        {
            if ($vegetable_id == VEGETABLE_ARTICHOKE) {
                $cards[] = array('type' => $vegetable_id, 'type_arg' => 0, 'nbr' => 10 * count($players));
            } else {
                //$cards[] = array('type' => $vegetable_id, 'type_arg' => 0, 'nbr' => 6);
                $cards[] = array('type' => VEGETABLE_CARROT, 'type_arg' => 0, 'nbr' => 6);
                $cards[] = array('type' => VEGETABLE_POTATO, 'type_arg' => 0, 'nbr' => 6);
                $cards[] = array('type' => VEGETABLE_LEEK, 'type_arg' => 0, 'nbr' => 6);
                $cards[] = array('type' => VEGETABLE_EGGPLANT, 'type_arg' => 0, 'nbr' => 6);
            }

        }
        $this->cards->createCards($cards, STOCK_GARDEN_STACK);

        $artichokes = $this->cards->getCardsOfType(VEGETABLE_ARTICHOKE);
        $i = 0;
        foreach ($players as $player_id => $player)
        {
            $player_artichokes = array_slice($artichokes, 10 * $i, 10);
            $artichoke_ids = array_map(function($n) { return $n['id']; }, $player_artichokes);
            $this->cards->moveCards($artichoke_ids, $this->player_deck($player_id), 0);
            $i++;
        }

        // garden row
        $this->cards->shuffle(STOCK_GARDEN_STACK);
        $this->refreshGardenRow(false);
        // player hands
        foreach ($players as $player_id => $player) {
            $this->cards->pickCards(5, $this->player_deck($player_id), $player_id);
        }

        // activate first player
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

        $player_id = self::getCurrentPlayerId();    // !! We must only return informations visible by this player !!

        // Get information about players
        $result['players'] = self::loadPlayersBasicInfos();

        $result[STOCK_GARDEN_ROW] = $this->cards->getCardsInLocation(STOCK_GARDEN_ROW);
        $result[STOCK_HAND] = $this->cards->getPlayerHand($player_id);
        $result[STOCK_PLAYED_CARD] = $this->cards->getCardsInLocation(STOCK_PLAYED_CARD);
        $compost = $this->cards->getCardOnTop(STOCK_COMPOST);
        $result[STOCK_COMPOST] = $compost ? array($compost) : array();
        $discard = $this->cards->getCardOnTop($this->player_discard($player_id));
        $result[STOCK_DISCARD] = $discard ? array($discard) : array();
        $result[STOCK_DISPLAYED_CARD] = $this->cards->getCardsInLocation(STOCK_DISPLAYED_CARD);

        $result['counters'] = array();
        foreach ($result['players'] as $player_id => $player) {
            $result['counters'][$player_id] = $this->get_counters($player_id);
        }

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

    function stEggplantInit() {
        $this->gamestate->setAllPlayersMultiactive();
    }

    function stEggPlantDone() {
        // move cards from limbos to player's hands and update state
        $players = self::loadPlayersBasicInfos();

        foreach ($players as $player_id => $value) {
            $target = self::getPlayerAfter($player_id);
            $card_ids = array_map(function($n) { return $n['id']; }, $this->cards->getCardsInLocation(STOCK_LIMBO, $player_id));
            $this->cards->moveCards($card_ids, STOCK_HAND, $target);
        }
        foreach ($players as $player_id => $value) {
            $discard = $this->cards->getCardOnTop($this->player_discard($player_id));
            $this->notify_one(NOTIFICATION_DREW_HAND, '', null, array(
                'cards' => $this->cards->getPlayerHand($player_id),
                'discard' => $discard ? array($discard) : array(),
                'player_id' => $player_id,
                'player_name' => self::GetCurrentPlayerName(),
            ));
        }

        $this->compost_played_card();
        $this->gamestate->nextState(STATE_PLAY_CARD);
    }

    function stNextPlayer() {
        $player_id = self::getCurrentPlayerId();
        // discard cards
        $this->cards->moveAllCardsInLocation(STOCK_HAND, $this->player_discard($player_id), $player_id, $player_id);
        // draw up to five cards
        $this->cards->pickCards(5, $this->player_deck($player_id), $player_id);
        $discard = $this->cards->getCardOnTop($this->player_discard($player_id));
        $this->notify_one(NOTIFICATION_DREW_HAND, '', null, array(
            'cards' => $this->cards->getPlayerHand($player_id),
            'discard' => $discard ? array($discard) : array(),
            'player_id' => $player_id,
            'player_name' => self::GetCurrentPlayerName(),
        ));
        // to update counters
        $this->notify_all(NOTIFICATION_UPDATE_COUNTERS, '');
        
        // check victory
        if (empty($this->cards->getCardsOfTypeInLocation(VEGETABLE_ARTICHOKE, null, STOCK_HAND, $player_id))) {
            // game over, player won!
            self::DbQuery("UPDATE player SET player_score=1 WHERE player_id='" . self::getActivePlayerId() . "'");
            $this->gamestate->nextState(STATE_END_GAME);
            // TODO: notification
            return;
        }

        // refill garden row
        $new_cards = $this->refreshGardenRow(true);
        self::notifyAllPlayers(NOTIFICATION_REFILLED_GARDEN_ROW, '', array (
            'new_cards' => $new_cards,
        ));

        // switch to next player
        $player_id = self::activeNextPlayer();
        self::giveExtraTime($player_id);
        self::setGameStateInitialValue(GAME_STATE_CARDS_PLAYED_THIS_TURN, 0);

        $this->gamestate->nextState(STATE_HARVEST);
    }

    function refreshGardenRow($notify) {
        $finished = false;
        $had_to_reshuffle = false;
        $row_before = $this->cards->getCardsInLocation(STOCK_GARDEN_ROW);
        while (!$finished) {
            $finished = true;
            $count = $this->cards->countCardInLocation(STOCK_GARDEN_ROW);
            if ($count < 5) {
                $this->cards->pickCardsForLocation(5 - $count, STOCK_GARDEN_STACK, STOCK_GARDEN_ROW);
            }
            $row_after = $this->cards->getCardsInLocation(STOCK_GARDEN_ROW);
            $counts = array_count_values(array_column($row_after, 'type'));
            foreach ($counts as $type => $count) {
                if ($count >= 4) {
                    if ($notify) {
                        $this->notify_all(NOTIFICATION_MESSAGE, clienttranslate('4 or more vegetables of the same type during refresh, replacing garden row'));
                    }
                    $card_ids = array_map(function($n) { return $n['id']; }, $this->cards->getCardsInLocation(STOCK_GARDEN_ROW));
                    $this->cards->moveCards($card_ids, STOCK_GARDEN_STACK);
                    $this->cards->shuffle(STOCK_GARDEN_STACK);
                    $had_to_reshuffle = true;
                    $finished = false;
                    break;
                }
            }
        }
        if ($had_to_reshuffle) {
            $new_cards_object = $this->cards->getCardsInLocation(STOCK_GARDEN_ROW);
            return array_values($new_cards_object);
        }
        $new_cards = [];
        foreach ($row_after as $key => $value) {
            if (!array_key_exists($key, $row_before)) {
                array_push($new_cards, $value);
            }
        }
        return $new_cards;
    }

    function harvestCard($id) {
        self::checkAction("harvestCard");
        $card = $this->cards->getCard($id);
        if ($card == null || $card['location'] != STOCK_GARDEN_ROW) {
            throw new BgaUserException(self::_("You must select a card from the garden row"));
        }

        $this->cards->moveCard($id, STOCK_HAND, self::getCurrentPlayerId());

        $this->notify_all(NOTIFICATION_CARD_MOVED, clienttranslate('${player_name} harvests ${vegetable}'), $card, array(
            'destination' => STOCK_HAND,
            'destination_arg' => self::getCurrentPlayerId(),
        ));

        $this->gamestate->nextState(STATE_PLAY_CARD);
    }

    function pass() {
        $this->gamestate->nextState(STATE_NEXT_PLAYER);
        self::notifyAllPlayers(NOTIFICATION_MESSAGE, clienttranslate('${player_name} ends turn'), array(
            'player_name' => self::getActivePlayerName(),
        ));
    }

    function playCard($id) {
        self::checkAction("playCard");
        $card = $this->cards->getCard($id);
        if ($card == null || $card['location'] != STOCK_HAND || $card['location_arg'] != self::getCurrentPlayerId()) {
            throw new BgaUserException(self::_("You must play a card from your hand"));
        }

        switch($card['type']) {
        case VEGETABLE_CARROT:
            $next_state = $this->playCarrot($id);
            break;
        case VEGETABLE_EGGPLANT:
            $next_state = $this->playEggplant($id);
            break;
        case VEGETABLE_LEEK:
            $next_state = $this->playLeek($id);
            break;
        case VEGETABLE_POTATO:
            $next_state = $this->playPotato($id);
            break;
        case VEGETABLE_ARTICHOKE:
            throw new BgaUserException(self::_("Artichokes can't be played"));
        default:
            throw new BgaUserException(self::_("This vegetable is not supported yet"));
        }

        self::incGameStateValue(GAME_STATE_CARDS_PLAYED_THIS_TURN, 1);

        if ($next_state) {
            $this->gamestate->nextState($next_state);
        }
    }

    function playCarrot($id) {
        // find artichokes to compost
        $hand = $this->cards->getPlayerHand(self::getCurrentPlayerId());
        if (self::getGameStateValue(GAME_STATE_CARDS_PLAYED_THIS_TURN) > 0) {
            throw new BgaUserException(self::_("You can't play a carrot after playing another card."));
        }
        $artichoke_1 = null;
        $artichoke_2 = null;
        foreach ($hand as $card) {
            if ($card['type'] == VEGETABLE_ARTICHOKE) {
                if ($artichoke_1 == null) {
                    $artichoke_1 = $card;
                } else {
                    $artichoke_2 = $card;
                    break;
                }
            }
        }
        if ($artichoke_2 == null) {
            throw new BgaUserException(self::_("You must have two artichokes in hand to play a carrot"));
        }

        $this->cards->moveCard($id, STOCK_PLAYED_CARD);
        $card = $this->cards->getCard($id);

        $this->notify_all(NOTIFICATION_CARD_MOVED, clienttranslate('${player_name} plays ${vegetable}'), $card, array(
                'origin' => STOCK_HAND,
                'origin_arg' => self::getCurrentPlayerId(),
                'destination' => STOCK_PLAYED_CARD,
        ));
        // compost them
        $this->cards->moveCard($artichoke_1['id'], STOCK_COMPOST);
        $this->notify_all(NOTIFICATION_CARD_MOVED, '', $artichoke_1, array( 'destination' => STOCK_COMPOST ));
        $this->cards->moveCard($artichoke_2['id'], STOCK_COMPOST);
        $this->notify_all(NOTIFICATION_CARD_MOVED, '', $artichoke_2, array( 'destination' => STOCK_COMPOST ));
        $this->cards->moveCard($card['id'], STOCK_COMPOST);
        $this->notify_all(NOTIFICATION_CARD_MOVED, clienttranslate('${player_name} composts carrot and two artichokes'), $card, array( 'destination' => STOCK_COMPOST ));

        return STATE_NEXT_PLAYER;
    }

    function playLeek($id) {
        $players = self::loadPlayersBasicInfos();

        $targets = array();
        foreach ($players as $player_id => $value) {
            if ($player_id == self::getCurrentPlayerId()) {
                continue;
            }
            if ($this->cards->countCardInLocation($this->player_deck($player_id)) + $this->cards->countCardInLocation($this->player_discard($player_id)) > 0) {
                array_push($targets, $player_id);
                break;
            }
        }
        if (count($targets) < 1) {
            throw new BgaUserException(self::_("Leek can only be played if an opponent has cards in the deck"));
        }

        $this->cards->moveCard($id, STOCK_PLAYED_CARD);
        $played_card = $this->cards->getCard($id);
        $this->notify_all(NOTIFICATION_CARD_MOVED, clienttranslate('${player_name} plays ${vegetable}'), $played_card, array(
            'origin' => STOCK_HAND,
            'origin_arg' => self::getCurrentPlayerId(),
            'destination' => STOCK_PLAYED_CARD,
        ));

        if (count($targets) == 1) {
            $this->notify_all(NOTIFICATION_MESSAGE, clienttranslate('[automatic] Only one valid target player for ${vegetable}'), $played_card);
            $target = array_pop($targets);
            $this->gamestate->nextState(STATE_LEEK_CHOOSE_OPPONENT);
            $this->leekChooseOpponent($target);
        } else {
            return STATE_LEEK_CHOOSE_OPPONENT;
        }
    }

    function playEggplant($id) {
        $players = self::loadPlayersBasicInfos();

        $hand = $this->cards->getPlayerHand(self::getCurrentPlayerId());
        foreach ($hand as $card) {
            if ($card['type'] == VEGETABLE_ARTICHOKE) {
                $artichoke = $card;
            }
        }
        if ($artichoke == null) {
            throw new BgaUserException(self::_("To play an eggplant you need an artichoke in your hand"));
        }

        // https://boardgamegeek.com/thread/2438217/eggplant-rule-question
        // says that no additional cards are needed
        // if (count($hand) < 4) {
        // throw new BgaUserException(self::_("To play an eggplant you have to have 3 other cards in your hand"));
        //}

        $this->cards->moveCard($id, STOCK_PLAYED_CARD);
        $played_card = $this->cards->getCard($id);
        $this->notify_all(NOTIFICATION_CARD_MOVED, clienttranslate('${player_name} plays ${vegetable}'), $played_card, array(
            'origin' => STOCK_HAND,
            'origin_arg' => self::getCurrentPlayerId(),
            'destination' => STOCK_PLAYED_CARD,
        ));

        $this->cards->moveCard($artichoke['id'], STOCK_COMPOST);
        $this->notify_all(NOTIFICATION_CARD_MOVED, clienttranslate('${player_name} composts ${vegetable}'), $artichoke, array( 'destination' => STOCK_COMPOST ));

        return STATE_EGGPLANT_CHOOSE_CARDS;
    }

    function eggplantChooseCards($card1, $card2) {
        $this->checkAction('eggplantChooseCards');
        $card_ids = array($card1, $card2);
        $player_id = $this->getCurrentPlayerId();

        $count = 0;
        foreach ($card_ids as $index => $card_id) {
            if ($card_id != null) {
                $card = $this->cards->getCard($card_id);
                if ($card == null || $card['location'] != STOCK_HAND || $card['location_arg'] != $player_id) {
                    throw BgaUserException(self::_("You must choose two cards from your hand (or as many as you can if you have fewer cards)"));
                }
                $count++;
            }
        }
        if ($count < 2 && $this->cards->countCardInLocation(STOCK_HAND, $player_id) != $count) {
            throw new BgaUserException(self::_("You must choose two cards from your hand (or as many as you can if you have fewer cards)"));
        }
        // pass to limbo for next player
        // show card as moved to correct player, but for this player only
        $opponent_id = self::getPlayerAfter($player_id);
        $opponent_name = $this->player_name($opponent_id);
        foreach ($card_ids as $index => $card_id) {
            $passed_card = $this->cards->getCard($card_id);
            $this->cards->moveCard($card_id, STOCK_LIMBO, $player_id);
            $this->notify_one(NOTIFICATION_CARD_MOVED, clienttranslate('${player_name} passes ${vegetable} to ${opponent_name}'), $passed_card, array(
                // 'origin' => STOCK_DECK,
                // 'origin_arg' => $player_id,
            'destination' => STOCK_DECK,
            'destination_arg' => $opponent_id,
            'opponent_name' => $opponent_name,
            ));
        }

        // TODO create and use notify_others instead
        $this->notify_all(NOTIFICATION_MESSAGE, clienttranslate('${player_name} passes ${count} cards to ${opponent_name}'), '', array(
            'count' => $count,
            'opponent_name' => $opponent_name
        ));
        $this->gamestate->setPlayerNonMultiactive($player_id, STATE_EGGPLANT_DONE);
    }

    function leekChooseOpponent($opponent_id) {
        self::checkAction("leekChooseOpponent");
        $picked_card = $this->cards->pickCardForLocation($this->player_deck($opponent_id), STOCK_DISPLAYED_CARD);
        if ($picked_card == null) {
            throw new BgaUserException(self::_("Leek can only be played on an opponent with cards in the deck"));
        }

        $players = self::loadPlayersBasicInfos();
        $this->notify_all(NOTIFICATION_CARD_MOVED, clienttranslate('${player_name} reveals ${vegetable} from the deck of ${opponent_name}'), $picked_card, array(
            'origin' => STOCK_DECK,
            'origin_arg' => $opponent_id,
            'destination' => STOCK_DISPLAYED_CARD,
            'opponent_name' => $players[$opponent_id]['player_name'],
        ));

        self::setGameStateValue(GAME_STATE_TARGET_PLAYER, $opponent_id);

        $this->gamestate->nextState(STATE_LEEK_TAKE_CARD);
    }

    function leekTakeCard($take_card) {
        $cards = $this->cards->getCardsInLocation(STOCK_DISPLAYED_CARD);
        $opponent_id = self::getGameStateValue(GAME_STATE_TARGET_PLAYER);
        if (count($cards) != 1) {
            throw new BgaVisibleSystemException(self::_("Incorrect number of displayed cards for leek"));
        }
        $player_id = self::getCurrentPlayerId();
        if ($take_card) {
            $picked_card = $this->cards->pickCard(STOCK_DISPLAYED_CARD, $player_id);
            $this->notify_all(NOTIFICATION_CARD_MOVED, clienttranslate('${player_name} takes ${vegetable}'), $picked_card, array(
                'origin' => STOCK_DISPLAYED_CARD,
                'destination' => STOCK_HAND,
                'destination_arg' => $player_id
            ));
        } else {
            $card = array_pop($cards);
            $this->cards->moveCard($card['id'], $this->player_discard($opponent_id));
            $this->notify_all(NOTIFICATION_CARD_MOVED, clienttranslate('${player_name} declines to take ${vegetable}'), $card, array(
                'origin' => STOCK_DISPLAYED_CARD,
                'destination' => STOCK_DISCARD,
                'destination_arg' => $opponent_id,
            ));
        }

        $this->discard_played_card();

        self::setGameStateValue(GAME_STATE_TARGET_PLAYER, 0);

        $this->gamestate->nextState(STATE_PLAY_CARD);
    }

    function playPotato($id) {
        // look at top card of deck
        $player_id = self::getCurrentPlayerId();
        $picked_card = $this->cards->pickCardForLocation($this->player_deck($player_id), STOCK_DISPLAYED_CARD);
        if ($picked_card == null) {
            throw new BgaUserException(self::_("You must have cards in your deck to play a potato"));
        }

        $this->cards->moveCard($id, STOCK_PLAYED_CARD);
        $played_card = $this->cards->getCard($id);
        $this->notify_all(NOTIFICATION_CARD_MOVED, clienttranslate('${player_name} plays ${vegetable}'), $played_card, array(
            'origin' => STOCK_HAND,
            'origin_arg' => self::getCurrentPlayerId(),
            'destination' => STOCK_PLAYED_CARD,
        ));

        $this->notify_all(NOTIFICATION_CARD_MOVED, clienttranslate('${player_name} reveals ${vegetable} from their deck'), $picked_card, array(
            'origin' => STOCK_DECK,
            'origin_arg' => self::getCurrentPlayerId(),
            'destination' => STOCK_DISPLAYED_CARD,
        ));

        if ($picked_card['type'] == VEGETABLE_ARTICHOKE) {
            $this->cards->moveCard($picked_card['id'], STOCK_COMPOST);
            $this->notify_all(NOTIFICATION_CARD_MOVED, clienttranslate('${player_name} composts ${vegetable}'), $picked_card, array( 'destination' => STOCK_COMPOST ));
        } else {
            $this->cards->moveCard($picked_card['id'], $this->player_discard($player_id));
            $this->notify_all(NOTIFICATION_CARD_MOVED, clienttranslate('${player_name} discards ${vegetable}'), $picked_card, array( 'destination' => STOCK_DISCARD, 'destination_arg' => $player_id ));
        }
        $this->cards->moveCard($played_card['id'], $this->player_discard($player_id));
        $this->notify_all(NOTIFICATION_CARD_MOVED, clienttranslate('${player_name} discards ${vegetable}'), $played_card, array( 'destination' => STOCK_DISCARD, 'destination_arg' => $player_id ));

        return STATE_PLAY_CARD;
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

        throw new BgaVisibleSystemException("Zombie mode not supported at this game state: ".$statename);
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

    function compost_played_card() {
        $player_id = self::getActivePlayerId();
        $played_card = $this->get_played_card_id();
        $this->cards->moveCard($played_card['id'], STOCK_COMPOST);
        $this->notify_all(NOTIFICATION_CARD_MOVED, clienttranslate('${player_name} composts ${vegetable}'), $played_card, array(
            'player_id' => $player_id,
            'origin' => STOCK_PLAYED_CARD,
            'destination' => STOCK_COMPOST,
        ));
    }

    function discard_played_card() {
        $player_id = self::getActivePlayerId();
        $played_card = $this->get_played_card_id();
        $this->cards->moveCard($played_card['id'], $this->player_discard($player_id));
        $this->notify_all(NOTIFICATION_CARD_MOVED, clienttranslate('${player_name} discards ${vegetable}'), $played_card, array(
            'player_id' => $player_id,
            'origin' => STOCK_PLAYED_CARD,
            'destination' => STOCK_DISCARD,
            'destination_arg' => $player_id,
        ));
    }

    function get_played_card_id() {
        $played_cards = $this->cards->getCardsInLocation(STOCK_PLAYED_CARD);
        if (count($played_cards) != 1) {
            throw new BgaVisibleSystemException(self::_("Incorrect number of played cards"));
        }
        return array_pop($played_cards);
    }
        
    function player_deck($player_id) {
        return "deck_" . $this->player_no($player_id);
    }

    function player_discard($player_id) {
        return "discard_" . $this->player_no($player_id);
    }

    function player_name($player_id) {
        $sql = "SELECT player_name FROM player WHERE player_id = " . $player_id;
        return self::getUniqueValueFromDB($sql);
    }

    function player_no($player_id) {
        $sql = "SELECT player_no FROM player WHERE player_id = " . $player_id;
        return self::getUniqueValueFromDB($sql);
    }

    function notify_all($type, $message, $card = null, $arguments = array()) {
        $this->notify_backend(true, $type, $message, $card, $arguments);
    }

    function notify_one($type, $message, $card = null, $arguments = array()) {
        $this->notify_backend(false, $type, $message, $card, $arguments);
    }

    function notify_backend($all, $type, $message, $card, $arguments) {
        $this->set_if_not_set($arguments, 'player_id', self::getCurrentPlayerId());
        $this->set_if_not_set($arguments, 'player_name', $this->player_name($arguments['player_id']));
        if ($card != null) {
            // TODO: translate vegetable name
            $this->set_if_not_set($arguments, 'vegetable', $this->vegetables[$card['type']]['name']);
            $this->set_if_not_set($arguments, 'card', $card);
            $this->set_if_not_set($arguments, 'origin', $card['location']);
            $this->set_if_not_set($arguments, 'origin_arg', $card['location_arg']);
        }
        $arguments['counters'] = array();
        foreach (self::loadPlayersBasicInfos() as $player_id => $player) {
            $arguments['counters'][$player_id] = $this->get_counters($player_id);
        }
        if ($all) {
            self::notifyAllPlayers($type, $message, $arguments);
        } else {
            self::notifyPlayer($arguments['player_id'], $type, $message, $arguments);
        }
    }

    function set_if_not_set(&$array, $key, $value) {
        if (!array_key_exists($key, $array)) {
            $array[$key] = $value;
        }
    }

    function get_counters($player_id) {
        return array(
            'deck' => $this->cards->countCardInLocation($this->player_deck($player_id)),
            'hand' => $this->cards->countCardInLocation(STOCK_HAND, $player_id),
            'discard' => $this->cards->countCardInLocation($this->player_discard($player_id)),
        );
    }
}
