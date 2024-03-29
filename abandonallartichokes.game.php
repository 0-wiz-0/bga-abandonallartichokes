<?php
 /**
  *------
  * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
  * AbandonAllArtichokes implementation : © Thomas Klausner <tk@giga.or.at> & Roja Maschajekhi <roja@roja.co.at>
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
            GAME_STATE_CARDS_PLAYED_THIS_TURN => 10,
            GAME_STATE_TARGET_PLAYER => 11,
            GAME_STATE_PLAYED_CARROT_THIS_TURN => 12,
            GAME_STATE_AUTOMATIC_PLAYER_DECISIONS => 100,
            GAME_STATE_AUTOMATIC_TURN_END => 101,
            GAME_STATE_RHUBARB => 105,
            GAME_STATE_ARTICHOKE_COUNTS => 110,
            GAME_STATE_AUTOMATIC_CARD_DECISIONS => 199,
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
        $this->cards->autoreshuffle_trigger = array('obj' => $this, 'method' => 'deck_auto_reshuffle');
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
        // The number of colors defined here must correspond to the maximum number of players allowed for the game
        // TODO check
        $gameinfos = self::getGameinfos();
        $default_colors = $gameinfos['player_colors'];

        // Create players
        $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar, player_score) VALUES ";
        $values = array();
        $player_no = 1;
        foreach ($players as $player_id => $player)
        {
            $color = array_shift($default_colors);
            $values[] = "('" . $player_id . "','" . $color . "','" . $player['player_canal'] . "','" . addslashes($player['player_name']) . "','" . addslashes($player['player_avatar']) . "','0')";
            $player_no++;
        }
        $sql .= implode(',', $values);
        self::DbQuery($sql);
        self::reattributeColorsBasedOnPreferences($players, $gameinfos['player_colors']);
        self::reloadPlayersBasicInfos();

        /************ Start the game initialization *****/

        // Init global values with their initial values
        self::setGameStateInitialValue(GAME_STATE_CARDS_PLAYED_THIS_TURN, 0);
        self::setGameStateInitialValue(GAME_STATE_TARGET_PLAYER, 0);
        self::setGameStateInitialValue(GAME_STATE_PLAYED_CARROT_THIS_TURN, 0);

        //self::setGameStateInitialValue(GAME_STATE_AUTOMATIC_PLAYER_DECISIONS, 1);
        //self::setGameStateInitialValue(GAME_STATE_AUTOMATIC_TURN_END, 1);
        self::setGameStateInitialValue(GAME_STATE_AUTOMATIC_CARD_DECISIONS, 0);

        // Init game statistics
        self::initStat('player', 'artichokes_composted', 0);
        self::initStat('player', 'artichoke_count', 0);
        self::initStat('player', 'card_count', 0);
        self::initStat('player', 'number_of_turns', 0);
        self::initStat('player', 'composted_by_beet', 0);
        self::initStat('player', 'composted_by_broccoli', 0);
        self::initStat('player', 'composted_by_carrot', 0);
        self::initStat('player', 'composted_by_eggplant', 0);
        self::initStat('player', 'composted_by_onion', 0);
        self::initStat('player', 'composted_by_potato', 0);

        // Create cards
        $cards = array();
        foreach (array_keys($this->vegetables) as $vegetable_id) {
            if ($vegetable_id == VEGETABLE_ARTICHOKE) {
                // type_arg is used for selecting one of the 5 artichoke pictures, see frontend
                $cards[] = array('type' => $vegetable_id, 'type_arg' => 0, 'nbr' => 2 * count($players));
                $cards[] = array('type' => $vegetable_id, 'type_arg' => 1, 'nbr' => 2 * count($players));
                $cards[] = array('type' => $vegetable_id, 'type_arg' => 2, 'nbr' => 2 * count($players));
                $cards[] = array('type' => $vegetable_id, 'type_arg' => 3, 'nbr' => 2 * count($players));
                $cards[] = array('type' => $vegetable_id, 'type_arg' => 4, 'nbr' => 2 * count($players));
            } else if ($vegetable_id != VEGETABLE_RHUBARB || self::getGameStateValue(GAME_STATE_RHUBARB) > 0) {
                // for testing only; list at least two!
                //if (!in_array($vegetable_id, array(VEGETABLE_BEET, VEGETABLE_LEEK, VEGETABLE_ONION, VEGETABLE_PEAS))) {
                //     continue;
                // }
                $cards[] = array('type' => $vegetable_id, 'type_arg' => 0, 'nbr' => 6);
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

        $hand = $this->cards->getPlayerHand($player_id);
        // add cards from limbo because they are still in hand, just not in our database
        $limbo = $this->cards->getCardsInLocation(STOCK_LIMBO, $player_id);
        $result[STOCK_GARDEN_ROW] = $this->cards->getCardsInLocation(STOCK_GARDEN_ROW);
        $result[STOCK_HAND] = array_merge($hand, $limbo);
        $result[STOCK_PLAYED_CARD] = $this->cards->getCardsInLocation(STOCK_PLAYED_CARD);
        $compost = $this->cards->getCardOnTop(STOCK_COMPOST);
        $result[STOCK_COMPOST] = $compost ? array($compost) : array();
        $result[STOCK_DISCARD] = $this->cards->getCardsInLocation($this->player_discard($player_id));
        $result[STOCK_DISPLAYED_CARD] = $this->cards->getCardsInLocation(STOCK_DISPLAYED_CARD);

        $result['counters'] = array();
        foreach (array_keys($result['players']) as $player_id) {
            $result['counters'][$player_id] = $this->get_counters($player_id);
        }
        $result['counters']['garden_stack'] = $this->cards->countCardInLocation(STOCK_GARDEN_STACK);

        $result['artichoke_count_option'] = self::getGameStateValue(GAME_STATE_ARTICHOKE_COUNTS);

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
        $players = self::loadPlayersBasicInfos();

        $artichoke_percentages = array();
        foreach (array_keys($players) as $player_id) {
            $counts = $this->count_cards_and_artichokes($player_id);
            $artichoke_percentages[] = $counts['artichoke_count'] / ($counts['card_count'] > 0 ? $counts['card_count'] : 1);
        }

        // has a player won yet?
        $sql = "SELECT MAX(player_score) FROM player;";
        if (self::getUniqueValueFromDB($sql) > 0) {
            return 100;
        }

        return max(0, min(100 * (1 - min($artichoke_percentages)), 100));
    }

    function stZombieUndo() {
        // discard played card
        $this->discard_played_cards();
        // discard any displayed cards
        $displayed_cards = $this->cards->getCardsInLocation(STOCK_DISPLAYED_CARD);
        foreach ($displayed_cards as $id => $card) {
            $this->cards->moveCard($id, STOCK_COMPOST);
            $this->notify_all(NOTIFICATION_CARD_MOVED, clienttranslate('Composting card on display for player who quit'), $card, array( 'destination' => STOCK_COMPOST ));
        }
        $this->pass();
    }

    function stEggplantInit() {
        $this->gamestate->setAllPlayersMultiactive();
    }

    function stEggPlantDone() {
        // move cards from limbos to player's hands and update state
        $players = self::loadPlayersBasicInfos();

        foreach (array_keys($players) as $source_id) {
            $target_id = self::getPlayerAfter($source_id);
            $cards = $this->cards->getCardsInLocation(STOCK_LIMBO, $source_id);
            $card_ids = array_map(function($n) { return $n['id']; }, $cards);
            // restore cards from limbo before passing them on
            $this->cards->moveCards($card_ids, STOCK_HAND, $source_id);
            $cards = $this->cards->getCards($card_ids);
            // pass them on
            $this->cards->moveCards($card_ids, STOCK_HAND, $target_id);
            $this->notify_one($source_id, NOTIFICATION_MULTIPLE_CARDS_MOVED, '', null, array(
                'moved_cards' => array_values($cards),
                'origin' => STOCK_HAND,
                'origin_arg' => $source_id,
                'destination' => STOCK_HAND,
                'destination_arg' => $target_id,
            ));
            $this->notify_one($target_id, NOTIFICATION_MULTIPLE_CARDS_MOVED, '', null, array(
                'moved_cards' => array_values($cards),
                'origin' => STOCK_HAND,
                'origin_arg' => $source_id,
                'destination' => STOCK_HAND,
                'destination_arg' => $target_id,
                'player_name2' => $this->player_name($source_id),
            ));
            foreach ($cards as $passed_card) {
                $this->notify_one($source_id, NOTIFICATION_MESSAGE, clienttranslate('You pass ${vegetable} to ${player_name2}'), $passed_card, array(
                    'player_name2' => $this->player_name($target_id),
                ));
                $this->notify_one($target_id, NOTIFICATION_MESSAGE, clienttranslate('You receive ${vegetable} from ${player_name2}'), $passed_card, array(
                    'player_name2' => $this->player_name($source_id),
                ));
            }
            $this->notify_others(array($source_id, $target_id), NOTIFICATION_MESSAGE, clienttranslate('${player_name} passes ${count} cards to ${player_name2}'), null, array(
                'count' => count($cards),
                'player_name' => $this->player_name($source_id),
                'player_name2' => $this->player_name($target_id),
            ));
        }

        $this->compost_played_card();

        $this->gamestate->nextState(STATE_PLAYED_CARD);
    }

    function stNextPlayer() {
        $player_id = self::getActivePlayerId();
        // discard cards
        $this->cards->moveAllCardsInLocation(STOCK_HAND, $this->player_discard($player_id), $player_id, $player_id);
        // draw up to five cards
        $this->cards->pickCards(5, $this->player_deck($player_id), $player_id);
        $this->notify_one($player_id, NOTIFICATION_DREW_HAND, clienttranslate('${player_name} draws cards'), null, array(
            'cards' => $this->cards->getPlayerHand($player_id),
            'discard' => $this->cards->getCardsInLocation($this->player_discard($player_id)),
            'player_id' => $player_id,
            'player_name' => self::GetActivePlayerName(),
        ));
        // to update counters
        $this->notify_all(NOTIFICATION_UPDATE_COUNTERS, '');
        self::incStat(1, 'number_of_turns', $player_id);

        // check victory
        if (empty($this->cards->getCardsOfTypeInLocation(VEGETABLE_ARTICHOKE, null, STOCK_HAND, $player_id))) {
            // game over, player won!
            self::DbQuery("UPDATE player SET player_score=1 WHERE player_id='" . self::getActivePlayerId() . "'");
            $this->notify_all(NOTIFICATION_VICTORY, clienttranslate('${player_name} wins!'), null, array(
                'player_id' => $player_id,
                'player_name' => self::GetActivePlayerName(),
            ));
            $this->update_statistics();
            $this->gamestate->nextState(STATE_END_GAME);
            return;
        }

        // refill garden row
        $this->refreshGardenRow();

        // switch to next player
        $player_id = self::activeNextPlayer();
        self::giveExtraTime($player_id);
        self::setGameStateValue(GAME_STATE_CARDS_PLAYED_THIS_TURN, 0);
        self::setGameStateValue(GAME_STATE_TARGET_PLAYER, 0);
        self::setGameStateValue(GAME_STATE_PLAYED_CARROT_THIS_TURN, 0);

        if (count($this->cards->getCardsInLocation(STOCK_GARDEN_ROW)) > 0) {
            $this->gamestate->nextState(STATE_HARVEST);
        } else {
            // we do not check for automatic turn end here because it would be too confusing -- you don't see
            // your cards long enough to notice that.
            $this->gamestate->nextState(STATE_PLAY_CARD);
        }
    }

    function stPlayedCard() {
        $this->discard_played_cards();

        self::incGameStateValue(GAME_STATE_CARDS_PLAYED_THIS_TURN, 1);

        if (self::getGameStateValue(GAME_STATE_PLAYED_CARROT_THIS_TURN) > 0) {
            $this->notify_all(NOTIFICATION_MESSAGE, clienttranslate('${player_name} played carrot and ends turn'));
            $this->gamestate->nextState(STATE_NEXT_PLAYER);
        } else if (self::getGameStateValue(GAME_STATE_AUTOMATIC_TURN_END) > 0 && $this->count_playable_cards(self::getActivePlayerId()) == 0) {
            $this->notify_all(NOTIFICATION_MESSAGE, clienttranslate('No playable cards left in hand, ${player_name} ends turn'), null, array(
                'player_id' => self::getActivePlayerId(),
            ));
            $this->gamestate->nextState(STATE_NEXT_PLAYER);
        } else {
            $this->gamestate->nextState(STATE_PLAY_CARD);
        }
    }

    function refreshGardenRow($notify_players = true) {
        // limit number of tries; especially in testing and at end of game there might be impossible cases
        $loop_count = 0;
        $finished = false;
        $had_to_reshuffle = false;
        $row_before = $this->cards->getCardsInLocation(STOCK_GARDEN_ROW);
        $garden_stack_count_before = $this->cards->countCardInLocation(STOCK_GARDEN_STACK);
        while (!$finished) {
            $loop_count++;
            $finished = true;
            $count = $this->cards->countCardInLocation(STOCK_GARDEN_ROW);
            if ($count < 5) {
                $this->cards->pickCardsForLocation(5 - $count, STOCK_GARDEN_STACK, STOCK_GARDEN_ROW);
            }
            $garden_stack_count_after = $this->cards->countCardInLocation(STOCK_GARDEN_STACK);
            // no point reshuffling if there is nothing left in the garden row
            if ($garden_stack_count_after > 0 && $loop_count < 5) {
                $row_after = $this->cards->getCardsInLocation(STOCK_GARDEN_ROW);
                $counts = array_count_values(array_column($row_after, 'type'));
                foreach ($counts as $count) {
                    if ($count >= 4) {
                        if ($notify_players) {
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
        }
        if ($loop_count >= 5) {
            $this->notify_all(NOTIFICATION_MESSAGE, clienttranslate('Multiple reshuffles did not reduce the number of vegetables of the same type below 4, giving up'));
        }
        if ($garden_stack_count_before > 0 && $garden_stack_count_after == 0) {
            $this->notify_all(NOTIFICATION_MESSAGE, clienttranslate('Garden stack is empty now'));
        }
        if ($had_to_reshuffle) {
            $new_cards_object = $this->cards->getCardsInLocation(STOCK_GARDEN_ROW);
            $new_cards = array_values($new_cards_object);
        } else {
            $new_cards = [];
            $row_after = $this->cards->getCardsInLocation(STOCK_GARDEN_ROW);
            foreach ($row_after as $key => $value) {
                if (!array_key_exists($key, $row_before)) {
                    array_push($new_cards, $value);
                }
            }
        }
        if ($notify_players) {
            self::notifyAllPlayers(NOTIFICATION_REFILLED_GARDEN_ROW, '', array (
                'new_cards' => $new_cards,
                'garden_stack_counter' => $this->cards->countCardInLocation(STOCK_GARDEN_STACK),
            ));
        }
    }

    function harvestCard($id) {
        self::checkAction("harvestCard");
        $card = $this->cards->getCard($id);
        if ($card == null || $card['location'] != STOCK_GARDEN_ROW) {
            throw new BgaUserException(self::_("You must select a card from the garden row"));
        }

        $this->cards->moveCard($id, STOCK_HAND, self::getActivePlayerId());

        $this->notify_all(NOTIFICATION_CARD_MOVED, clienttranslate('${player_name} harvests ${vegetable}'), $card, array(
            'destination' => STOCK_HAND,
            'destination_arg' => self::getActivePlayerId(),
        ));

        if (self::getGameStateValue(GAME_STATE_AUTOMATIC_TURN_END) > 0 && $this->count_playable_cards(self::getActivePlayerId()) == 0) {
            $this->notify_all(NOTIFICATION_MESSAGE, clienttranslate('No playable cards left in hand, ${player_name} ends turn'));
            $this->gamestate->nextState(STATE_NEXT_PLAYER);
        } else {
            $this->gamestate->nextState(STATE_PLAY_CARD);
        }
    }

    function pass() {
        self::checkAction("pass");
        self::notifyAllPlayers(NOTIFICATION_MESSAGE, clienttranslate('${player_name} ends turn'), array( 'player_name' => self::getActivePlayerName() ));
        $this->gamestate->nextState(STATE_NEXT_PLAYER);
    }

    function playCard($id) {
        self::checkAction("playCard");
        $card = $this->cards->getCard($id);
        if ($card == null || $card['location'] != STOCK_HAND || $card['location_arg'] != self::getActivePlayerId()) {
            throw new BgaUserException(self::_("You must play a card from your hand"));
        }

        $this->card_must_be_playable(self::getActivePlayerId(), $id, true);

        $play_actions = array (
            VEGETABLE_ARTICHOKE => 'playArtichoke',
            VEGETABLE_BEET => 'playBeet',
            VEGETABLE_BROCCOLI => 'playBroccoli',
            VEGETABLE_CARROT => 'playCarrot',
            VEGETABLE_CORN => 'playCorn',
            VEGETABLE_EGGPLANT => 'playEggplant',
            VEGETABLE_LEEK => 'playLeek',
            VEGETABLE_ONION => 'playOnion',
            VEGETABLE_PEAS => 'playPeas',
            VEGETABLE_PEPPER => 'playPepper',
            VEGETABLE_POTATO => 'playPotato',
            VEGETABLE_RHUBARB => 'playRhubarb',
        );
        $name = $play_actions[$card['type']];
        if ($name == null) {
            throw new BgaVisibleSystemException(self::_("This vegetable is not supported yet"));
        }
        $this->$name($id);
    }

    function playArtichoke() {
        throw new BgaUserException(self::_("Artichokes can't be played"));
    }

    function playBeet($id) {
        $target_args = $this->arg_beetOpponents();
        $target_ids = $target_args['target_ids'];

        $this->play_card($id, true);
        $this->gamestate->nextState(STATE_BEET_CHOOSE_OPPONENT);

        if (self::getGameStateValue(GAME_STATE_AUTOMATIC_PLAYER_DECISIONS) > 0 && count($target_ids) == 1) {
            $target_id = array_pop($target_ids);
            $this->beetChooseOpponent($target_id);
        }
    }

    function beetChooseOpponent($opponent_id) {
        self::checkAction("beetChooseOpponent");
        $opponent_hand = $this->cards->getPlayerHand($opponent_id);
        if (count($opponent_hand) < 1) {
            throw new BgaUserException(self::_("Beet can only be played on an opponent with cards in hand"));
        }
        $opponent_card = $this->cards->getCard(array_rand($opponent_hand, 1));
        $this->notify_all(NOTIFICATION_MESSAGE, clienttranslate('${player_name} chooses ${player_name2} as target for ${vegetable}'), null,
                          array( 'vegetable' => $this->vegetables[VEGETABLE_BEET]['name'],
                                 'player_name2' => $this->player_name($opponent_id),
                          ));
        $hand = $this->cards->getPlayerHand(self::getActivePlayerId());
        $card = $this->cards->getCard(array_rand($hand, 1));
        $this->beetHandleDrawnCards($card, $opponent_card, $opponent_id);

        $this->gamestate->nextState(STATE_PLAYED_CARD);
    }

    function arg_beetOpponents() {
        $opponents = $this->get_opponent_ids();
        $target_ids = array();
        foreach ($opponents as $opponent_id) {
            if ($this->cards->countCardInLocation(STOCK_HAND, $opponent_id) > 0) {
                array_push($target_ids, $opponent_id);
            }
        }
        return array ( 'target_ids' => $target_ids );
    }

    function beetHandleDrawnCards($card, $opponent_card, $opponent_id) {
        if ($card['type'] == VEGETABLE_ARTICHOKE && $opponent_card['type'] == VEGETABLE_ARTICHOKE) {
            $this->compost_artichoke($card, self::getActivePlayerId(), true, VEGETABLE_BEET);
            $this->compost_artichoke($opponent_card, $opponent_id, true, VEGETABLE_BEET);
        }
        else {
            $this->cards->moveCard($card['id'], STOCK_HAND, $opponent_id);
            $this->cards->moveCard($opponent_card['id'], STOCK_HAND, self::getActivePlayerId());
            $this->notify_all(NOTIFICATION_CARD_MOVED,  clienttranslate('${player_name} gives ${vegetable} to ${player_name2}'),
                $card, array( 'destination' => STOCK_HAND,
                              'destination_arg' => $opponent_id,
                              'player_name2' => $this->player_name($opponent_id)));
            $this->notify_all(NOTIFICATION_CARD_MOVED, clienttranslate('${player_name2} gives ${vegetable} to ${player_name}'),
                              $opponent_card, array( 'destination' => STOCK_HAND,
                                                     'destination_arg' => self::getActivePlayerId(),
                                                     'player_name2' => $this->player_name($opponent_id)));
        }
    }

    function playBroccoli($id) {
        $hand = $this->cards->getPlayerHand(self::getActivePlayerId());
        foreach ($hand as $card) {
            if ($card['type'] == VEGETABLE_ARTICHOKE) {
                $artichoke = $card;
                break;
            }
        }

        $this->play_card($id, false);

        $this->compost_artichoke($artichoke, self::getActivePlayerId(), false, VEGETABLE_BROCCOLI);

        $this->notify_all(NOTIFICATION_MESSAGE, clienttranslate('${player_name} plays broccoli and composts artichoke'));

        $this->gamestate->nextState(STATE_PLAYED_CARD);
    }

    function playCarrot($id) {
        // find artichokes to compost
        $hand = $this->cards->getPlayerHand(self::getActivePlayerId());
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

        $this->play_card($id, false);

        // compost carrot and both artichokes
        $this->compost_artichoke($artichoke_1, self::getActivePlayerId(), false, VEGETABLE_CARROT);
        $this->compost_artichoke($artichoke_2, self::getActivePlayerId(), false, VEGETABLE_CARROT);
        $card = $this->cards->getCard($id);
        $this->cards->moveCard($card['id'], STOCK_COMPOST);
        $this->notify_all(NOTIFICATION_CARD_MOVED, clienttranslate('${player_name} plays carrot and composts it and two artichokes'), $card, array( 'destination' => STOCK_COMPOST ));

        self::setGameStateValue(GAME_STATE_PLAYED_CARROT_THIS_TURN, 1);

        $this->gamestate->nextState(STATE_PLAYED_CARD);
    }

    function playCorn($id) {
        $hand = $this->cards->getPlayerHand(self::getActivePlayerId());
        $artichoke = null;
        foreach ($hand as $card) {
            if ($card['type'] == VEGETABLE_ARTICHOKE) {
                $artichoke = $card;
                break;
            }
        }

        $player_id = self::getActivePlayerId();

        $this->play_card($id, false);

        $this->cards->moveCard($artichoke['id'], $this->player_discard($player_id));
        $this->notify_all(NOTIFICATION_CARD_MOVED, clienttranslate('${player_name} plays corn and discards artichoke'), $artichoke, array(
            'destination' => STOCK_DISCARD,
            'destination_arg' => $player_id,
        ));

        $this->gamestate->nextState(STATE_CORN_TAKE_CARD);
    }

    function cornTakeCard($id) {
        self::checkAction("cornTakeCard");
        $card = $this->cards->getCard($id);
        if ($card == null || $card['location'] != STOCK_GARDEN_ROW) {
            throw new BgaVisibleSystemException(self::_("Choose a card from the garden row"));
        }

        $player_id = self::getActivePlayerId();
        $this->cards->insertCardOnExtremePosition($id, $this->player_deck($player_id), true);
        $this->notify_all(NOTIFICATION_CARD_MOVED, clienttranslate('${player_name} takes ${vegetable} and puts it on top of the deck'), $card, array(
            'destination' => STOCK_DECK,
            'destination_arg' => $player_id,
        ));

        $this->gamestate->nextState(STATE_PLAYED_CARD);
    }

    function playEggplant($id) {
        $hand = $this->cards->getPlayerHand(self::getActivePlayerId());
        $artichoke = null;
        foreach ($hand as $card) {
            if ($card['type'] == VEGETABLE_ARTICHOKE) {
                $artichoke = $card;
                break;
            }
        }

        $this->play_card($id);

        $this->compost_artichoke($artichoke, self::getActivePlayerId(), true, VEGETABLE_EGGPLANT);

        $this->gamestate->nextState(STATE_EGGPLANT_CHOOSE_CARDS);
    }

    function eggplantChooseCards($card_ids) {
        $this->checkAction('eggplantChooseCards');
        $player_id = $this->getCurrentPlayerId();

        $count = 0;
        foreach ($card_ids as $card_id) {
            if ($card_id != null) {
                $card = $this->cards->getCard($card_id);
                if ($card == null || $card['location'] != STOCK_HAND || $card['location_arg'] != $player_id) {
                    throw new BgaUserException(self::_("You must choose two cards from your hand (or as many as you can if you have fewer cards)"));
                }
                $count++;
            }
        }
        if ($count < 2 && $this->cards->countCardInLocation(STOCK_HAND, $player_id) != $count) {
            throw new BgaUserException(self::_("You must choose two cards from your hand (or as many as you can if you have fewer cards)"));
        }
        // pass to limbo for next player
        foreach ($card_ids as $card_id) {
            $this->cards->moveCard($card_id, STOCK_LIMBO, $player_id);
        }

        $this->gamestate->setPlayerNonMultiactive($player_id, STATE_EGGPLANT_DONE);
    }

    function playLeek($id) {
        $target_args = $this->arg_leekOpponents();
        $target_ids = $target_args['target_ids'];

        $this->play_card($id);

        $this->gamestate->nextState(STATE_LEEK_CHOOSE_OPPONENT);

        if (self::getGameStateValue(GAME_STATE_AUTOMATIC_PLAYER_DECISIONS) > 0 && count($target_ids) == 1) {
            $target_id = array_pop($target_ids);
            $this->leekChooseOpponent($target_id);
        }
    }

    function arg_leekOpponents() {
        $opponents = $this->get_opponent_ids();
        $target_ids = array();
        foreach ($opponents as $opponent_id) {
            if ($this->cards->countCardInLocation($this->player_deck($opponent_id)) > 0 ||
                $this->cards->countCardInLocation($this->player_discard($opponent_id)) > 0) {
                array_push($target_ids, $opponent_id);
            }
        }
        return array ( 'target_ids' => $target_ids );
    }

    function leekChooseOpponent($opponent_id) {
        self::checkAction("leekChooseOpponent");
        $players = self::loadPlayersBasicInfos();
        $this->notify_all(NOTIFICATION_MESSAGE, clienttranslate('${player_name} chooses ${player_name2} as target for ${vegetable}'), null,
                          array( 'vegetable' => $this->vegetables[VEGETABLE_LEEK]['name'],
                                 'player_name2' => $players[$opponent_id]['player_name'],
                          ));
        $picked_card = $this->cards->pickCardForLocation($this->player_deck($opponent_id), STOCK_DISPLAYED_CARD);
        if ($picked_card == null) {
            throw new BgaUserException(self::_("Leek can only be played on an opponent with cards in the deck"));
        }

        $this->notify_all(NOTIFICATION_CARD_MOVED, clienttranslate('${player_name} reveals ${vegetable} from the deck of ${player_name2}'), $picked_card, array(
            'origin' => STOCK_DECK,
            'origin_arg' => $opponent_id,
            'destination' => STOCK_DISPLAYED_CARD,
            'player_name2' => $players[$opponent_id]['player_name'],
        ));

        self::setGameStateValue(GAME_STATE_TARGET_PLAYER, $opponent_id);

        $this->gamestate->nextState(STATE_LEEK_TAKE_CARD);
    }

    function leekTakeCard($take_card) {
        self::checkAction("leekTakeCard");
        $cards = $this->cards->getCardsInLocation(STOCK_DISPLAYED_CARD);
        $opponent_id = self::getGameStateValue(GAME_STATE_TARGET_PLAYER);
        if (count($cards) != 1) {
            throw new BgaVisibleSystemException(self::_("Incorrect number of displayed cards for leek"));
        }
        $player_id = self::getActivePlayerId();
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
            $this->notify_all(NOTIFICATION_CARD_MOVED, clienttranslate('${player_name} returns ${vegetable} to ${player_name2}'), $card, array(
                'origin' => STOCK_DISPLAYED_CARD,
                'destination' => STOCK_DISCARD,
                'destination_arg' => $opponent_id,
                'player_name2' => $this->player_name($opponent_id),
            ));
        }

        self::setGameStateValue(GAME_STATE_TARGET_PLAYER, 0);

        $this->gamestate->nextState(STATE_PLAYED_CARD);
    }

    function playOnion($id) {
        $hand = $this->cards->getPlayerHand(self::getActivePlayerId());
        foreach ($hand as $card) {
            if ($card['type'] == VEGETABLE_ARTICHOKE) {
                $artichoke = $card;
                break;
            }
        }

        $targets = $this->arg_allOpponents();
        $target_ids = $targets['target_ids'];
        // for testing in solo-mode
        if (count($target_ids) == 0) {
            throw new BgaVisibleSystemException(self::_("Onion can only be played when you have an opponent"));
        }

        $this->play_card($id, false);

        $this->notify_all(NOTIFICATION_MESSAGE, clienttranslate('${player_name} plays onion and composts artichoke'), null, array( 'player_name' => self::getActivePlayerName() ));
        $this->compost_artichoke($artichoke, self::getActivePlayerId(), false, VEGETABLE_ONION);

        $this->gamestate->nextState(STATE_ONION_CHOOSE_OPPONENT);

        if (self::getGameStateValue(GAME_STATE_AUTOMATIC_PLAYER_DECISIONS) > 0 && count($target_ids) == 1) {
            $target_id = array_pop($target_ids);
            $this->onionChooseOpponent($target_id);
        }
    }

    function onionChooseOpponent($opponent_id) {
        self::checkAction("onionChooseOpponent");
        $opponent_name = $this->player_name($opponent_id);
        if ($opponent_id == self::getActivePlayerId() || $opponent_name == null) {
            throw new BgaVisibleSystemException(self::_("Invalid target player"));
        }

        $played_card = $this->get_played_card();

        $this->cards->moveCard($played_card['id'], $this->player_discard($opponent_id));
        $this->notify_all(NOTIFICATION_CARD_MOVED, clienttranslate('${player_name} gives ${vegetable} to ${player_name2}'), $played_card, array(
            'destination' => STOCK_DISCARD,
            'destination_arg' => $opponent_id,
            'player_name2' => $opponent_name,
        ));

        $this->gamestate->nextState(STATE_PLAYED_CARD);
    }

    function playPeas($id) {
        $this->play_card($id);

        $this->cards->pickCardsForLocation(2, STOCK_GARDEN_STACK, STOCK_DISPLAYED_CARD);
        $displayed_cards = $this->cards->getCardsInLocation(STOCK_DISPLAYED_CARD);
        $available_types = array();
        $cards = array_values($displayed_cards);
        $this->notify_all(NOTIFICATION_MULTIPLE_CARDS_MOVED, '', null, array(
                'moved_cards' => $cards,
                'origin' => STOCK_GARDEN_STACK,
                'destination' => STOCK_DISPLAYED_CARD,
                'garden_stack_counter' => $this->cards->countCardInLocation(STOCK_GARDEN_STACK),
        ));

        $this->gamestate->nextState(STATE_PEAS_TAKE_CARD);

        foreach ($displayed_cards as $id => $card) {
            $available_types[$card['type']] = $id;
        }
        if (self::getGameStateValue(GAME_STATE_AUTOMATIC_CARD_DECISIONS) > 0 && count($available_types) == 1) {
            $types = array_keys($available_types);
            $type = array_pop($types);
            $this->notify_all(NOTIFICATION_MESSAGE, clienttranslate('Only cards of type ${vegetable} in display area'), null,
                              array( 'vegetable' => $this->vegetables[$type]['name']));
            $card_id = array_pop($available_types);
            $this->peasTakeCard($card_id);
        }
    }

    function peasTakeCard($id) {
        self::checkAction("peasTakeCard");
        if ($id == null) {
            throw new BgaVisibleSystemException(self::_("You must take a card from the display"));
        }
        $card = $this->cards->getCard($id);
        if ($card == null || $card['location'] != STOCK_DISPLAYED_CARD) {
            throw new BgaVisibleSystemException(self::_("You must take a card from the display"));
        }
        $player_id = self::getActivePlayerId();
        $this->cards->moveCard($id, $this->player_discard($player_id));
        $this->notify_all(NOTIFICATION_CARD_MOVED, clienttranslate('${player_name} takes ${vegetable}'), $card, array(
            'destination' => STOCK_DISCARD,
            'destination_arg' => $player_id,
        ));

        $targets = $this->arg_allOpponents();
        $target_ids = $targets['target_ids'];

        $this->gamestate->nextState(STATE_PEAS_CHOOSE_OPPONENT);

        if (self::getGameStateValue(GAME_STATE_AUTOMATIC_PLAYER_DECISIONS) > 0 && count($target_ids) == 1) {
            $target_id = array_pop($target_ids);
            $this->peasChooseOpponent($target_id);
        }
    }

    function arg_allOpponents() {
        $target_ids = $this->get_opponent_ids();
        return array ( 'target_ids' => $target_ids );
    }

    function peasChooseOpponent($opponent_id) {
        self::checkAction("peasChooseOpponent");
        $players = self::loadPlayersBasicInfos();
        if ($players[$opponent_id] == null) {
            throw new BgaVisibleSystemException(self::_("You must pick a player at the table"));
        }
        $presents = $this->cards->getCardsInLocation(STOCK_DISPLAYED_CARD);
        if (count($presents) != 1) {
            throw new BgaVisibleSystemException(self::_("Incorrect number of displayed cards"));
        }
        $present = array_pop($presents);

        $this->cards->moveCard($present['id'], $this->player_discard($opponent_id));
        $this->notify_all(NOTIFICATION_CARD_MOVED, clienttranslate('${player_name} gives ${vegetable} to ${player_name2}'), $present, array(
            'destination' => STOCK_DISCARD,
            'destination_arg' => $opponent_id,
            'player_name2' => $players[$opponent_id]['player_name'],
        ));

        $this->gamestate->nextState(STATE_PLAYED_CARD);
    }

    function playPepper($id) {
        $player_id = self::getActivePlayerId();
        $discarded_cards = $this->cards->getCardsInLocation($this->player_discard($player_id));

        $this->play_card($id);

        $available_types = array();
        // collect one card per type, doesn't matter which
        foreach ($discarded_cards as $id => $card) {
            $available_types[$card['type']] = $id;
        }

        if (self::getGameStateValue(GAME_STATE_AUTOMATIC_CARD_DECISIONS) > 0 && count($available_types) == 1) {
            $types = array_keys($available_types);
            $type = array_pop($types);
            $this->notify_all(NOTIFICATION_MESSAGE, clienttranslate('Only cards of type ${vegetable} in discard pile'), null,
                              array( 'vegetable' => $this->vegetables[$type]['name']));
            $card_id = array_pop($available_types);
            $this->cards->moveCard($card_id, STOCK_DISPLAYED_CARD);
            $card = $this->cards->getCard($card_id);
            $this->notify_all(NOTIFICATION_CARD_MOVED, '', $card, array(
                'origin' => STOCK_DISCARD,
                'origin_arg' => $player_id,
                'destination' => STOCK_DISPLAYED_CARD,
            ));
            $this->gamestate->nextState(STATE_PEPPER_TAKE_CARD);
            $this->pepperTakeCard($card_id);
            return;
        }

        // move cards to display
        $cards = array();
        foreach ($available_types as $type => $id) {
            array_push($cards, $this->cards->getCard($id));
        }
        $this->notify_all(NOTIFICATION_MULTIPLE_CARDS_MOVED, '', null, array(
                'moved_cards' => $cards,
                'origin' => STOCK_DISCARD,
                'origin_arg' => $player_id,
                'destination' => STOCK_DISPLAYED_CARD,
        ));

        foreach ($available_types as $type => $id) {
            $this->cards->moveCard($id, STOCK_DISPLAYED_CARD);
        }
        $this->gamestate->nextState(STATE_PEPPER_TAKE_CARD);
    }

    function pepperTakeCard($id) {
        self::checkAction("pepperTakeCard");
        if ($id == null) {
            throw new BgaVisibleSystemException(self::_("You must take a card from the display to put on deck"));
        }
        $card = $this->cards->getCard($id);
        if ($card == null || $card['location'] != STOCK_DISPLAYED_CARD) {
            throw new BgaVisibleSystemException(self::_("You must take a card from the display to put on deck"));
        }
        $player_id = self::getActivePlayerId();
        // move chosen card to deck
        $this->cards->insertCardOnExtremePosition($id, $this->player_deck($player_id), true);
        $this->notify_all(NOTIFICATION_CARD_MOVED, clienttranslate('${player_name} puts ${vegetable} on top of the deck'), $card, array(
            'destination' => STOCK_DECK,
            'destination_arg' => $player_id,
        ));
        // move other cards to discard
        $displayed_cards = $this->cards->getCardsInLocation(STOCK_DISPLAYED_CARD);
        $player_discard = $this->player_discard($player_id);
        foreach ($displayed_cards as $id => $card) {
            $this->cards->moveCard($id, $player_discard);
        }
        $this->notify_all(NOTIFICATION_MULTIPLE_CARDS_MOVED, '', null, array(
                'moved_cards' => array_values($displayed_cards),
                'origin' => STOCK_DISPLAYED_CARD,
                'destination' => STOCK_DISCARD,
                'destination_arg' => $player_id,
        ));

        $this->gamestate->nextState(STATE_PLAYED_CARD);
    }

    function playPotato($id) {
        // look at top card of deck
        $player_id = self::getActivePlayerId();
        $picked_card = $this->cards->pickCardForLocation($this->player_deck($player_id), STOCK_DISPLAYED_CARD);

        $this->play_card($id, false);

        $this->notify_all(NOTIFICATION_CARD_MOVED, clienttranslate('${player_name} plays potato and reveals ${vegetable} from their deck'), $picked_card, array(
            'origin' => STOCK_DECK,
            'origin_arg' => self::getActivePlayerId(),
            'destination' => STOCK_DISPLAYED_CARD,
        ));

        if ($picked_card['type'] == VEGETABLE_ARTICHOKE) {
            $this->compost_artichoke($picked_card, self::getActivePlayerId(), true, VEGETABLE_POTATO);
        } else {
            $this->cards->moveCard($picked_card['id'], $this->player_discard($player_id));
            $this->notify_all(NOTIFICATION_CARD_MOVED, clienttranslate('${player_name} discards ${vegetable}'), $picked_card, array( 'destination' => STOCK_DISCARD, 'destination_arg' => $player_id ));
        }

        $this->gamestate->nextState(STATE_PLAYED_CARD);
    }

    function playRhubarb($id) {
        $this->play_card($id, false);

        $this->notify_all(NOTIFICATION_MESSAGE, clienttranslate('Refreshing garden row for rhubarb'));
        $garden_row = $this->cards->getCardsInLocation(STOCK_GARDEN_ROW);
        foreach (array_keys($garden_row) as $id) {
            $this->cards->insertCardOnExtremePosition($id, STOCK_GARDEN_STACK, false);
        }
        $this->refreshGardenRow();

        $this->gamestate->nextState(STATE_RHUBARB_HARVEST_CARD);
    }

    function rhubarbHarvestCard($id) {
        self::checkAction("rhubarbHarvestCard");
        $card = $this->cards->getCard($id);
        if ($card == null || $card['location'] != STOCK_GARDEN_ROW) {
            throw new BgaVisibleSystemException(self::_("Choose a card from the garden row"));
        }

        $this->cards->moveCard($id, STOCK_HAND, self::getActivePlayerId());

        $this->notify_all(NOTIFICATION_CARD_MOVED, clienttranslate('${player_name} harvests ${vegetable}'), $card, array(
            'destination' => STOCK_HAND,
            'destination_arg' => self::getActivePlayerId(),
        ));

        $this->compost_played_card();

        $this->gamestate->nextState(STATE_PLAYED_CARD);
    }

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
            case 'harvest':
                // randomly harvest one card
                $garden_row = $this->cards->getCardsInLocation(STOCK_GARDEN_ROW);
                if (count($garden_row) > 0) {
                    // if there are no cards in hand, player won already
                    // with '1', array_rand returns a single value, the key
                    $choice = array_rand($garden_row, 1);
                    $this->harvestCard($choice);
                } else {
                    // not sure how we got here, try undo
                    $this->gamestate->nextState(STATE_ZOMBIE_UNDO);
                }
                break;
            case 'playCard':
                // pass
                $this->pass();
                break;
            case 'leekTakeCard':
                // pass card back to opponent
                $this->leekTakeCard(false);
                break;
            default:
                $this->notify_all(NOTIFICATION_MESSAGE, clienttranslate('Trying to undo incomplete action for player who quit'));
                $this->gamestate->nextState(STATE_ZOMBIE_UNDO);
            }

            return;
        }

        if ($state['type'] === "multipleactiveplayer") {
            if ($statename == 'eggplantChooseCards') {
                $hand = $this->cards->getCardsInLocation(STOCK_HAND, $active_player);
                // with '2', array_rand returns an array of keys
                $choice_ids = array_rand($hand, 2);
                $this->eggplantChooseCards($choice_ids);
            } else {
                throw new BgaVisibleSystemException(self::_("Zombie mode not supported at this multipleactiveplayer game state: ") . $statename);
            }
            return;
        }

        throw new BgaVisibleSystemException(self::_("Zombie mode not supported at this game state: ") . $statename);
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

    function deck_auto_reshuffle($deck) {
        $player_no = substr($deck, 5);
        $player_id = $this->player_id_for_player_no($player_no);
        $this->notify_one($player_id, NOTIFICATION_RESHUFFLED, '');
        $this->notify_all(NOTIFICATION_UPDATE_COUNTERS, clienttranslate('${player_name} shuffled the discard pile into the deck'), null, array('player_id' => $player_id));
    }

    function compost_played_card() {
        $player_id = self::getActivePlayerId();
        $played_card = $this->get_played_card();
        $this->cards->moveCard($played_card['id'], STOCK_COMPOST);
        $this->notify_all(NOTIFICATION_CARD_MOVED, clienttranslate('${player_name} composts ${vegetable}'), $played_card, array(
            'player_id' => $player_id,
            'origin' => STOCK_PLAYED_CARD,
            'destination' => STOCK_COMPOST,
        ));
    }

    function compost_artichoke($card, $player_id, $notify_message, $vegetable) {
        if ($card['type'] != VEGETABLE_ARTICHOKE) {
            throw new BgaVisibleSystemException(self::_("Trying to compost non-artichoke with special artichoke composter"));
        }
        $this->cards->moveCard($card['id'], STOCK_COMPOST);
        $this->notify_all(NOTIFICATION_CARD_MOVED, $notify_message ? clienttranslate('${player_name} composts ${vegetable}') : '', $card,
                          array( 'destination' => STOCK_COMPOST, 'player_id' => $player_id ));
        self::incStat(1, 'artichokes_composted', $player_id);
        switch ($vegetable) {
        case VEGETABLE_BEET:
            self::incStat(1, 'composted_by_beet', $player_id);
            break;
        case VEGETABLE_BROCCOLI:
            self::incStat(1, 'composted_by_broccoli', $player_id);
            break;
        case VEGETABLE_CARROT:
            self::incStat(1, 'composted_by_carrot', $player_id);
            break;
        case VEGETABLE_EGGPLANT:
            self::incStat(1, 'composted_by_eggplant', $player_id);
            break;
        case VEGETABLE_ONION:
            self::incStat(1, 'composted_by_onion', $player_id);
            break;
        case VEGETABLE_POTATO:
            self::incStat(1, 'composted_by_potato', $player_id);
            break;
        }
    }

    function discard_played_cards() {
        $player_id = self::getActivePlayerId();
        $played_card = $this->get_played_card(false);
        $last_card = null;
        while ($played_card) {
            $this->cards->moveCard($played_card['id'], $this->player_discard($player_id));
            $last_card = $played_card;
            $this->notify_all(NOTIFICATION_CARD_MOVED, '', $played_card, array(
                'player_id' => $player_id,
                'origin' => STOCK_PLAYED_CARD,
                'destination' => STOCK_DISCARD,
                'destination_arg' => $player_id,
            ));
            $played_card = $this->get_played_card(false);
        }
        return $last_card;
    }

    function card_must_be_playable($player_id, $id, $throw_error = true) {
        $card = $this->cards->getCard($id);
        if ($card == null) {
            return false;
        }
        $reason = null;
        switch ($card['type']) {
        case VEGETABLE_BEET:
            $target_args = $this->arg_beetOpponents();
            $target_ids = $target_args['target_ids'];
            // TODO: check if this test is correct
            if ($this->cards->countCardInLocation(STOCK_HAND, $player_id) <= 1) {
                $reason = self::_("Beet can only be played if you have cards in your hand");
                break;
            }
            if (count($target_ids) < 1) {
                $reason = self::_("Beet can only be played if an opponent has cards in hand");
                break;
            }
            return true;
        case VEGETABLE_BROCCOLI:
            if ($this->artichokes_in_player_hand($player_id) < 3) {
                $reason = self::_("To play a broccoli you need 3 artichokes in your hand");
                break;
            }
            return true;
        case VEGETABLE_CARROT:
            if (self::getGameStateValue(GAME_STATE_CARDS_PLAYED_THIS_TURN) > 0) {
                $reason = self::_("You can't play a carrot after playing another card.");
                break;
            }
            if ($this->artichokes_in_player_hand($player_id) < 2) {
                $reason = self::_("You must have two artichokes in hand to play a carrot");
                break;
            }
            return true;
        case VEGETABLE_CORN:
            if ($this->cards->countCardInLocation(STOCK_GARDEN_ROW) == 0) {
                $reason = self::_("You can not play corn if there are no cards in the garden row");
                break;
            }
            if ($this->artichokes_in_player_hand($player_id) < 1) {
                $reason = self::_("To play a corn you need an artichoke in your hand");
                break;
            }
            return true;
        case VEGETABLE_EGGPLANT:
            if ($this->artichokes_in_player_hand($player_id) < 1) {
                $reason = self::_("To play an eggplant you need an artichoke in your hand");
                break;
            }
            // https://boardgamegeek.com/thread/2438217/eggplant-rule-question
            // says that no additional cards are needed
            // if (count($hand) < 4) {
            // throw new BgaUserException(self::_("To play an eggplant you have to have 3 other cards in your hand"));
            //}
            return true;
        case VEGETABLE_LEEK:
            $target_args = $this->arg_leekOpponents();
            $target_ids = $target_args['target_ids'];
            if (count($target_ids) < 1) {
                $reason = self::_("Leek can only be played if an opponent has cards in the deck");
                break;
            }
            return true;
        case VEGETABLE_ONION:
            if ($this->artichokes_in_player_hand($player_id) < 1) {
                $reason = self::_("To play an onion you need an artichoke in your hand");
                break;
            }
            return true;
        case VEGETABLE_PEAS:
            $players = self::loadPlayersBasicInfos();
            // for testing in solo-mode
            if (count($players) < 2) {
                $reason = self::_("Peas can only be played when you have an opponent");
                break;
            }
            if ($this->cards->countCardInLocation(STOCK_GARDEN_STACK) < 2) {
                $reason = self::_("Peas can only be played when there are two cards in the garden stack");
                break;
            }
            return true;
        case VEGETABLE_PEPPER:
            $discarded_cards = $this->cards->countCardInLocation($this->player_discard($player_id));
            if ($discarded_cards < 1) {
                $reason = self::_("Pepper can only be played when you have cards in your discard pile");
                break;
            }
            return true;
        case VEGETABLE_POTATO:
            $available_cards = $this->cards->countCardInLocation($this->player_deck($player_id)) + $this->cards->countCardInLocation($this->player_discard($player_id));
            if ($available_cards < 1) {
                $reason = self::_("You must have cards in your deck to play a potato");
                break;
            }
            return true;
        case VEGETABLE_RHUBARB:
            if ($this->cards->countCardInLocation(STOCK_GARDEN_ROW) + $this->cards->countCardInLocation(STOCK_GARDEN_STACK) == 0) {
                $reason = self::_("You can not play rhubarb if there are no cards in the garden row and garden stack");
                break;
            }
            return true;
        }

        if ($reason && $throw_error) {
            throw new BgaUserException($reason);
        }
        return false;
    }

    function artichokes_in_player_hand($player_id) {
        $hand = $this->cards->getPlayerHand($player_id);
        $artichoke_count = 0;
        foreach ($hand as $card) {
            if ($card['type'] == VEGETABLE_ARTICHOKE) {
                $artichoke_count++;
            }
        }
        return $artichoke_count;
    }

    function play_card($id, $notify = true) {
        $played_card = $this->cards->getCard($id);
        $this->cards->moveCard($id, STOCK_PLAYED_CARD);
        $this->notify_all(NOTIFICATION_CARD_MOVED, $notify ? clienttranslate('${player_name} plays ${vegetable}') : '', $played_card, array(
            'destination' => STOCK_PLAYED_CARD,
        ));
        return $this->cards->getCard($id);
    }

    function get_played_card($must_be_one = true) {
        $played_cards = $this->cards->getCardsInLocation(STOCK_PLAYED_CARD);
        if (count($played_cards) != 1 && $must_be_one) {
            throw new BgaVisibleSystemException(self::_("Incorrect number of played cards"));
        }
        return array_pop($played_cards);
    }

    function get_opponent_ids() {
        $player_id = self::getActivePlayerId();
        $players = self::loadPlayersBasicInfos();
        $target_ids = array();
        foreach (array_keys($players) as $opponent_id) {
            if ($player_id == $opponent_id) {
                continue;
            }
            array_push($target_ids, $opponent_id);
        }
        return $target_ids;
    }

    function update_statistics() {
        $players = self::loadPlayersBasicInfos();
        foreach (array_keys($players) as $player_id) {
            $counts = $this->count_cards_and_artichokes($player_id);
            self::setStat($counts['card_count'], 'card_count', $player_id);
            self::setStat($counts['artichoke_count'], 'artichoke_count', $player_id);
        }
    }

    function count_cards_and_artichokes($player_id) {
        $deck = $this->player_deck($player_id);
        $discard = $this->player_discard($player_id);
        $card_count = $this->cards->countCardInLocation($deck) +
                    $this->cards->countCardInLocation($discard) +
                    $this->cards->countCardInLocation(STOCK_HAND, $player_id);
        $artichoke_count = count($this->cards->getCardsOfTypeInLocation(VEGETABLE_ARTICHOKE, null, $deck)) +
                         count($this->cards->getCardsOfTypeInLocation(VEGETABLE_ARTICHOKE, null, $discard)) +
                         count($this->cards->getCardsOfTypeInLocation(VEGETABLE_ARTICHOKE, null, STOCK_HAND, $player_id));
        return array('card_count' => $card_count, 'artichoke_count' => $artichoke_count);
    }

    function count_playable_cards($player_id) {
        $hand = $this->cards->getCardsInLocation(STOCK_HAND, $player_id);
        $playable_card_count = 0;
        foreach ($hand as $card) {
            if ($this->card_must_be_playable($player_id, $card['id'], false)) {
                $playable_card_count++;
            }
        }
        return $playable_card_count;
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

    function player_id_for_player_no($player_no) {
        $sql = "SELECT player_id FROM player WHERE player_no = " . $player_no;
        return self::getUniqueValueFromDB($sql);
    }

    function notify_all($type, $message, $card = null, $arguments = array()) {
        $this->notify_backend(null, $type, $message, $card, $arguments);
    }

    function notify_one($player_id, $type, $message, $card = null, $arguments = array()) {
        $this->notify_backend($player_id, $type, $message, $card, $arguments);
    }

    function notify_others($excluded_player_ids, $type, $message, $card = null, $arguments = array()) {
        $players = self::loadPlayersBasicInfos();
        foreach (array_keys($players) as $player_id) {
            if (!in_array($player_id, $excluded_player_ids)) {
                $this->notify_backend($player_id, $type, $message, $card, $arguments);
            }
        }
    }

    function notify_backend($target_player_id, $type, $message, $card, $arguments) {
        $this->set_if_not_set($arguments, 'player_id', self::getCurrentPlayerId());
        $this->set_if_not_set($arguments, 'player_name', $this->player_name($arguments['player_id']));
        if ($card != null) {
            $this->set_if_not_set($arguments, 'vegetable', $this->vegetables[$card['type']]['name']);
            $this->set_if_not_set($arguments, 'card', $card);
            $this->set_if_not_set($arguments, 'origin', $card['location']);
            $this->set_if_not_set($arguments, 'origin_arg', $card['location_arg']);
        }
        if (array_key_exists('vegetable', $arguments)) {
            $arguments['i18n'] = array('vegetable'); // make sure 'vegetable' gets translated
        }
        $arguments['counters'] = array();
        $players = self::loadPlayersBasicInfos();
        foreach (array_keys($players) as $player_id) {
            $arguments['counters'][$player_id] = $this->get_counters($player_id);
        }
        if ($target_player_id != null) {
            self::notifyPlayer($target_player_id, $type, $message, $arguments);
        } else {
            self::notifyAllPlayers($type, $message, $arguments);
        }
    }

    function set_if_not_set(&$array, $key, $value) {
        if (!array_key_exists($key, $array)) {
            $array[$key] = $value;
        }
    }

    function get_counters($player_id) {
        $result = array(
            'deck' => $this->cards->countCardInLocation($this->player_deck($player_id)),
            'hand' => $this->cards->countCardInLocation(STOCK_HAND, $player_id) + $this->cards->countCardInLocation(STOCK_LIMBO, $player_id),
            'discard' => $this->cards->countCardInLocation($this->player_discard($player_id)),
        );
        if (self::getGameStateValue(GAME_STATE_ARTICHOKE_COUNTS) > 0) {
            $counts = $this->count_cards_and_artichokes($player_id);
            $result['artichokes'] = $counts['artichoke_count'];
        }
        return $result;
    }
}
