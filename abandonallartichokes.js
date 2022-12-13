/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * AbandonAllArtichokes implementation : © Thomas Klausner <tk@giga.or.at> & Roja Maschajekhi <roja@roja.co.at>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * abandonallartichokes.js
 *
 * AbandonAllArtichokes user interface script
 *
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

define([
        "dojo", "dojo/_base/declare",
        "ebg/core/gamegui",
        "ebg/counter",
        "ebg/stock"
    ],
    function (dojo, declare) {
        return declare("bgagame.abandonallartichokes", ebg.core.gamegui, {
            constructor: function () {
                // console.log('abandonallartichokes constructor');

		// css spritesheet properties
		// when changing this, update #garden_area 'width' in the css file
		// also update artichoke_cardmin
                this.cardwidth = 140;
                this.cardheight = 210;

		this.hand_default_overlap = 70;

		// When using spritesheets, we can't use high-resolution card images
		// There are only around 15 images, so we use single files instead
		// this.image_items_per_row = 4;
		// this.spritesheet = 'img/spritesheet100.jpg';
                // the values must be the same in
                // - gamedatas
                // - HTML *.tpl file (div id)
                // - stock constructor (below)
                // - php code (material.inc.php)
                this.Stock = {
                    GardenStack: 'garden_stack',
                    GardenRow: 'garden_row',
                    Hand: 'hand',
                    Deck: 'deck',
                    Discard: 'discard',
                    DisplayedCard: 'displayed_card',
                    PlayedCard: 'played_card',
                    Compost: 'compost',
                    // Limbo: 'limbo', // unused in frontend
                };
                // this needs to match the names in material.inc.php
                this.Notification = {
                    CardMoved: "card_moved",
                    DrewHand: "drew_hand",
                    MultipleCardsMoved: "multiple_cards_moved",
                    RefilledGardenRow: "refilled_garden_row",
                    Reshuffled: "reshuffled",
                    UpdateCounters: "update_counters",
                    Victory: "victory",
                };
                // this needs to match the values in abandonallartichokes.action.php and states.inc.php
                this.AjaxActions = {
		    CornTakeCard: 'cornTakeCard',
                    BeetChooseOpponent: 'beetChooseOpponent',
                    EggplantChooseCards: 'eggplantChooseCards',
                    Harvest: 'harvestCard',
                    LeekChooseOpponent: 'leekChooseOpponent',
                    LeekTakeCard: 'leekTakeCard',
                    OnionChooseOpponent: 'onionChooseOpponent',
                    Pass: 'pass',
                    PeasChooseOpponent: 'peasChooseOpponent',
                    PeasTakeCard: 'peasTakeCard',
                    PepperTakeCard: 'pepperTakeCard',
                    PlayCard: 'playCard',
		    RhubarbHarvestCard: 'rhubarbHarvestCard',
                };
                this.CardBackId = 1;

                //vegetable types => numbers match define in material.inc.php
                this.Vegetables = {
                    BEET: 1,
                    BROCCOLI: 2,
                    CARROT: 3,
                    CORN: 4,
                    EGGPLANT: 5,
                    LEEK: 6,
                    ONION: 7,
                    PEAS: 8,
                    PEPPER: 9,
                    POTATO: 10,
                    ARTICHOKE1: 11,
                    ARTICHOKE2: 12,
                    ARTICHOKE3: 13,
                    ARTICHOKE4: 14,
                    ARTICHOKE5: 15,
		    RHUBARB: 16,
		    BACK: 17,
                };

                this.VegetablesColors = {
                    '': {
                        1: '#9f2645', // BEET
                        2: '#33762f', // BROCCOLI
                        3: '#ef9131', // CARROT
                        4: '#f3d80d', // CORN
                        5: '#7c3883', // EGGPLANT
                        6: '#6e8d51', // LEEK
                        7: '#b66d36', // ONION
                        8: '#72b146', // PEAS
                        9: '#f3b637', // PEPPER
                        10: '#a27c3e', // POTATO
                        11: '#c9d336', //ARTICHOKE1
                        12: '#c9d336', //ARTICHOKE2
                        13: '#c9d336', //ARTICHOKE3
                        14: '#c9d336', //ARTICHOKE4
                        15: '#c9d336', //ARTICHOKE5
                        16: '#cb4f6b', // RHUBARB
                        17: '',
                    },

                    'de_': {
                        1: '#c3294d', // BEET
                        2: '#185f1d', // BROCCOLI
                        3: '#ee7934', // CARROT
                        4: '#fed853', // CORN
                        5: '#6c166f', // EGGPLANT
                        6: '#749c3b', // LEEK
                        7: '#bf702b', // ONION
                        8: '#439239', // PEAS
                        9: '#e0bd49', // PEPPER
                        10: '#cfb05e', // POTATO
                        11: '#c9d336', //ARTICHOKE1
                        12: '#c9d336', //ARTICHOKE2
                        13: '#c9d336', //ARTICHOKE3
                        14: '#c9d336', //ARTICHOKE4
                        15: '#c9d336', //ARTICHOKE5
                        16: '#7f0f1d', // RHUBARB
                        17: '',
                    },
                }
            },

            /*
                setup:

                This method must set up the game user interface according to current game situation specified
                in parameters.

                The method is called each time the game interface is displayed to a player, ie:
                _ when the game starts
                _ when a player refreshes the game page (F5)

                "gamedatas" argument contains all datas retrieved by your "getAllDatas" PHP method.
            */

            setup: function (gamedatas) {
                // console.log("Starting game setup");
                // console.log(gamedatas);

                // var lang = dojo.config.locale.substr(0, 2);
                var lang = _("english");

                if (lang == 'deutsch' || lang == 'french') {
                    this.preventpreload('');
                    this.lang_prefix = 'de_';
                    document.getElementsByTagName('html')[0].dataset.style = 'dark';
                } else {
                    this.preventpreload('de');
                    this.lang_prefix = '';
                    document.getElementsByTagName('html')[0].dataset.style = 'light';
                }

                this.counter = {};
                // Setting up player boards
                for (var player_id in gamedatas.players) {
                    var player_board_div = $('player_board_' + player_id);
                    dojo.place(this.format_block('jstpl_player_board', {id: player_id}), player_board_div);

                    this.counter[player_id] = {};
                    this.createCounter(player_id, 'hand');
                    this.createCounter(player_id, 'deck');
                    this.createCounter(player_id, 'discard');
		    if (this.gamedatas.artichoke_count_option > 0) {
			// only used in 'artichoke counts' option
			this.createCounter(player_id, 'artichokes');
			dojo.removeClass('optional_artichokes_' + player_id, 'artichoke_hidden');
		    } else {
			dojo.addClass('optional_artichokes_' + player_id, 'artichoke_hidden');
		    }
		    this.addTooltip('label_hand_' + player_id, dojo.string.substitute( _("Number of cards in ${player_name}'s hand"), {
			player_name: this.gamedatas.players[player_id].name }), "");
		    this.addTooltip('label_deck_' + player_id, dojo.string.substitute( _("Number of cards in ${player_name}'s deck"), {
			player_name: this.gamedatas.players[player_id].name }), "");
		    this.addTooltip('label_discard_' + player_id, dojo.string.substitute( _("Number of cards in ${player_name}'s discard pile"), {
			player_name: this.gamedatas.players[player_id].name }), "");
                }

		// garden stack counter
                this.counter.garden_stack = new ebg.counter();
                this.counter.garden_stack.create('garden_stack_counter');
		        this.counter.garden_stack.setValue(this.gamedatas.counters.garden_stack);

                const stock_constructor = [
                    {name: this.Stock.GardenRow, callback: 'onGardenRowSelect', selectionMode: 1},
                    {name: this.Stock.Hand, callback: 'onPlayerHandSelect', selectionMode: 2, weights: true},
                    {name: this.Stock.DisplayedCard, callback: 'onDisplayedCardSelect', selectionMode: 1},
                    {name: this.Stock.PlayedCard, callback: null, selectionMode: 0},
                    {name: this.Stock.Discard, callback: null, selectionMode: 0, overlap: 1},
                    {name: this.Stock.Compost, callback: null, selectionMode: 0, overlap: 1},
                ];

                const extraClasses = 'artichoke_card';
                this.stock = {};
                for (var stock_entry of stock_constructor) {
                    this.stock[stock_entry.name] = this.setupCardStocks(stock_entry.name, stock_entry.callback, this.lang_prefix, stock_entry.weights);
                    this.stock[stock_entry.name].setSelectionMode(stock_entry.selectionMode);
                    this.stock[stock_entry.name].setSelectionAppearance('class');
		    this.stock[stock_entry.name].extraClasses = extraClasses;
		    if (stock_entry.overlap) this.stock[stock_entry.name].setOverlap(stock_entry.overlap);
                    this.stock[stock_entry.name].autowidth = true;
		    // automatically add tooltips
		    this.stock[stock_entry.name].onItemCreate = dojo.hitch(this, 'setupNewCard');
                    this.addCardsToStock(this.stock[stock_entry.name], this.gamedatas[stock_entry.name]);
                }


                this.makeStockWithCardBack(this.Stock.Deck, extraClasses);
                this.updateDecks();
                this.makeStockWithCardBack(this.Stock.GardenStack, extraClasses);
                this.updateGardenStack();


		this.stock[this.Stock.Hand].setOverlap(this.hand_default_overlap);

		this.showDisplayedArea();
                this.setupNotifications();

		// replace hard-coded text with translations
		$('tray_discard_description').innerHTML = _("Discard Pile");
		$('tray_deck_description').innerHTML = _("Deck");
		$('tray_compost_description').innerHTML = _("Compost");
		$('tray_garden_stack_description').innerHTML = _("Garden Stack");

                // console.log(this);
                // console.log("Ending game setup");
            },

        makeStockWithCardBack: function(stockName, extraClasses) {
            // draw deck is special, we only show card backs
            this.stock[stockName] = new ebg.stock();
            this.stock[stockName].create(this, $(stockName), this.cardwidth, this.cardheight);
            this.stock[stockName].image_items_per_row = this.image_items_per_row;
            this.stock[stockName].setOverlap(1);
            this.stock[stockName].setSelectionMode(0);
            this.stock[stockName].extraClasses = extraClasses;
            this.stock[stockName].autowidth = true;
            this.stock[stockName].addItemType(this.CardBackId, 0, g_gamethemeurl + 'img/' + this.lang_prefix + (this.Vegetables.BACK - 1) + '.jpg');
        },

	    preventpreload: function(lang) {
                var prefix = lang + '_';
                if (lang == '') {
		    prefix = '';
		}
		// include card back
		for (var vegetable_id = 1; vegetable_id <= 17; vegetable_id++) {
		    this.dontPreloadImage(prefix + (vegetable_id - 1) + '.jpg')
		}
            },

            createCounter: function (player_id, name) {
                this.counter[player_id][name] = new ebg.counter();
                this.counter[player_id][name].create(name + "_" + player_id);
                this.counter[player_id][name].setValue(this.gamedatas.counters[player_id][name]);
            },

            ///////////////////////////////////////////////////
            //// Game & client states

            // Initialize a card stock
            // Arguments: div id, function which occurs when the card selection changes
            setupCardStocks: function (id, selectionChangeFunctionName, lang_prefix = '', weights = false) {
                var stock = new ebg.stock();
                stock.create(this, $(id), this.cardwidth, this.cardheight);
		stock.image_items_per_row = this.image_items_per_row;
                for (var vegetable_id = 1; vegetable_id <= 16; vegetable_id++) {
		    // 1-10: main vegetables, 11-15: artichokes, 16: rhubarb
                    //stock.addItemType(vegetable_id, 0, g_gamethemeurl + this.spritesheet, vegetable_id - 1);
		    // if weight is true, make artichokes lighter, so the top = most visible card is a vegetable
		    stock.addItemType(vegetable_id, weights && (vegetable_id < 11 || vegetable_id > 15) ? 1 : 0, g_gamethemeurl + 'img/' + lang_prefix + (vegetable_id - 1) + '.jpg');
                }
                if (selectionChangeFunctionName != null) {
                    dojo.connect(stock, 'onChangeSelection', this, selectionChangeFunctionName);
                }
                return stock;
            },

            // Add an array of server cards to a particular stock
            addCardsToStock: function (stock, cards) {
                stock.removeAll();
                Object.values(cards).forEach(function (card) {
		    // for five types of artichokes, add type_arg
                    stock.addToStockWithId(parseInt(card.type) + parseInt(card.type_arg), card.id);
                });
            },

            onDisplayedCardSelect: function (control_name, item_id) {
                var items = this.stock[this.Stock.DisplayedCard].getSelectedItems();
                if (items.length > 0) {
                    if (this.isCurrentPlayerActive()) {
                        if (this.checkAction('leekTakeCard', true)) {
                            // usability feature: click on displayed card to take it after playing leek
                            this.changeState(this.AjaxActions.LeekTakeCard, {take_card: true});
                        } else if (this.checkAction('peasTakeCard', true)) {
                            this.changeState(this.AjaxActions.PeasTakeCard, {id: items[0].id});
                        } else if (this.checkAction('pepperTakeCard', true)) {
                            this.changeState(this.AjaxActions.PepperTakeCard, {id: items[0].id});
                        }
                    } else {
                        this.showMessage(_("You can't select cards from the display area now."), "error");
                    }
                    this.stock[this.Stock.DisplayedCard].unselectAll();
                }
            },

            onPlayerHandSelect: function (control_name, item_id) {
                var items = this.stock[this.Stock.Hand].getSelectedItems();

                if (items.length > 0) {
                    if (this.checkAction('playCard', true)) {
                        var card_id = items[0].id;
                        this.changeState(this.AjaxActions.PlayCard, {id: card_id});
                        this.stock[this.Stock.Hand].unselectAll();
                    } else if (this.checkAction('eggplantChooseCards', true)) {
                        // just let the user select cards, will be checked on action button press
                    } else {
                        this.showMessage(_("You can't play cards from your hand now."), "error");
                        this.stock[this.Stock.Hand].unselectAll();
                    }
                }
            },

            onGardenRowSelect: function (control_name, item_id) {
                var items = this.stock[this.Stock.GardenRow].getSelectedItems();

                if (items.length > 0) {
                    if (this.checkAction('harvestCard', true)) {
                        var card_id = items[0].id;
                        this.changeState(this.AjaxActions.Harvest, {id: card_id});
		    } else if (this.checkAction('cornTakeCard', true)) {
			var card_id = items[0].id;
			this.changeState(this.AjaxActions.CornTakeCard, {id: card_id});
		    } else if (this.checkAction('rhubarbHarvestCard', true)) {
			var card_id = items[0].id;
			this.changeState(this.AjaxActions.RhubarbHarvestCard, {id: card_id});
                    } else {
                        this.showMessage(_("You can't harvest cards now."), "error");
                    }
                    this.stock[this.Stock.GardenRow].unselectAll();
                }
            },

            // onEnteringState: this method is called each time we are entering into a new game state.
            //                  You can use this method to perform some user interface changes at this moment.
            //
            onEnteringState: function (stateName, args) {
                // console.log('Entering state: ' + stateName);

                switch (stateName) {

                    /* Example:

                    case 'myGameState':

                        // Show some HTML block at this game state
                        dojo.style( 'my_html_block_id', 'display', 'block' );

                        break;
                   */


                    case 'dummmy':
                        break;
                }
            },

            // onLeavingState: this method is called each time we are leaving a game state.
            //                 You can use this method to perform some user interface changes at this moment.
            //
            onLeavingState: function (stateName) {
                // console.log('Leaving state: ' + stateName);

                switch (stateName) {

                    /* Example:

                    case 'myGameState':

                        // Hide the HTML block we are displaying only during this game state
                        dojo.style( 'my_html_block_id', 'display', 'none' );

                        break;
                   */


                    case 'dummmy':
                        break;
                }
            },

            onUpdateActionButtons: function (stateName, args) {
                // console.log('onUpdateActionButtons: ' + stateName);
		// console.log(args);

                if (this.isCurrentPlayerActive()) {
                    switch (stateName) {
                    case this.AjaxActions.PlayCard:
                        this.addActionButton('pass', _('End turn'), 'onPass');
                        break;
                    case this.AjaxActions.EggplantChooseCards:
                        this.addActionButton('confirm', _('Confirm cards to pass on to next player'), 'onEggplantConfirm');
                        break;
                    case this.AjaxActions.BeetChooseOpponent:
                    case this.AjaxActions.LeekChooseOpponent:
                    case this.AjaxActions.OnionChooseOpponent:
                    case this.AjaxActions.PeasChooseOpponent:
                        for (var player_id of args.target_ids) {
                            this.addActionButton('player_' + this.gamedatas.players[player_id].player_no,
						 dojo.string.substitute( _('Choose ${player_name}'), { player_name: this.gamedatas.players[player_id].name }),
						 this.onChooseOpponent.bind(this, stateName, player_id));
                        }
                        break;
                    case this.AjaxActions.LeekTakeCard:
                        this.addActionButton('leekTake', _('Take card'), 'onLeekTake');
                        this.addActionButton('leekLeave', _('Give card back'), 'onLeekDecline');
                        break;
                    }
                }
            },

            onEggplantConfirm: function () {
                selected_cards = this.stock[this.Stock.Hand].getSelectedItems();
                all_cards = this.stock[this.Stock.Hand].getAllItems();
                if (selected_cards.length == 2 || (selected_cards.length < 2 && selected_cards.length == all_cards.length)) {
                    card_id1 = null;
                    card_id2 = null;
                    if (selected_cards.length > 0) {
                        card_id1 = selected_cards[0].id;
                        if (selected_cards.length > 1) {
                            card_id2 = selected_cards[1].id;
                        }
                    }
                    this.changeState(this.AjaxActions.EggplantChooseCards, {card1: card_id1, card2: card_id2});
                } else {
                    this.showMessage(_("You must choose two cards from your hand (or as many as you can if you have fewer cards)"), "error");
                }
            },

            onChooseOpponent: function (action, opponent_id) {
                this.changeState(action, { opponent_id: opponent_id});
            },

            hasCards: function (player_id) {
                return this.counter[player_id]['deck'].getValue() > 0 || this.counter[player_id]['discard'].getValue() > 0;
            },

            onPass: function () {
                this.changeState(this.AjaxActions.Pass);
            },

            onLeekTake: function () {
                this.changeState(this.AjaxActions.LeekTakeCard, {take_card: true});
            },

            onLeekDecline: function () {
                this.changeState(this.AjaxActions.LeekTakeCard, {take_card: false});
            },

	    // Screen Width change
	  /*  onScreenWidthChange: function() {
	    //this.hand_default_overlap = 100;
	     },*/

            // Notifications
            setupNotifications: function () {
                // console.log('notifications subscriptions setup');

                dojo.subscribe(this.Notification.CardMoved, this, "notif_cardMoved");
                dojo.subscribe(this.Notification.DrewHand, this, "notif_drewHand");
                dojo.subscribe(this.Notification.MultipleCardsMoved, this, "notif_multipleCardsMoved");
                dojo.subscribe(this.Notification.RefilledGardenRow, this, "notif_refilledGardenRow");
                dojo.subscribe(this.Notification.UpdateCounters, this, "notif_updateCounters");
                dojo.subscribe(this.Notification.Reshuffled, this, "notif_reshuffled");
                dojo.subscribe(this.Notification.Victory, this, "notif_victory");

		this.notifqueue.setSynchronous(this.Notification.Reshuffled, 500);
		this.notifqueue.setSynchronous(this.Notification.DrewHand, 500);
		this.notifqueue.setSynchronous(this.Notification.CardMoved, 600);
            },

            notif_cardMoved: function (notification) {
                // console.log(this.Notification.CardMoved + ' notification');
                // console.log(notification);
		// for five types of artichokes, add type_arg
		notification.args.card.type = parseInt(notification.args.card.type) + parseInt(notification.args.card.type_arg);
                this.showCardPlay(notification.args.player_id,
                    notification.args.origin, notification.args.origin_arg,
                    notification.args.destination, notification.args.destination_arg,
                    notification.args.card, notification.args.counters);
		if (notification.args.garden_stack_counter) {
		    this.counter.garden_stack.setValue(notification.args.garden_stack_counter);
		}
		this.updateGardenStack();
            },

	    notif_multipleCardsMoved: function (notification) {
                // console.log(this.Notification.MultipleCardsMoved + ' notification');
                //console.log(notification);
		// array
                for (var card of notification.args.moved_cards) {
		    // for five types of artichokes, add type_arg
		    card.type = parseInt(card.type) + parseInt(card.type_arg);
                    this.showCardPlay(notification.args.player_id,
				      notification.args.origin, notification.args.origin_arg,
				      notification.args.destination, notification.args.destination_arg,
				      card, notification.args.counters);
		}

		if (notification.args.garden_stack_counter) {
		    this.counter.garden_stack.setValue(notification.args.garden_stack_counter);
		    this.updateGardenStack();
		}
            },

            notif_drewHand: function (notification) {
                // console.log(this.Notification.DrewHand + ' notification');
                // console.log(notification);
		// move all items from hand to discard
		// this is an array!
		for (var card of this.stock[this.Stock.Hand].getAllItems()) {
		    this.moveVisibleToVisible(this.Stock.Hand, this.Stock.Discard, card);
		}
		// move all new cards into hand
		// this is an object!
		for (var keys in notification.args.cards) {
		    this.moveVisibleToVisible(this.Stock.Deck, this.Stock.Hand, notification.args.cards[keys]);
		}
                this.updateCounter(notification.args.counters);
            },

            notif_refilledGardenRow: function (notification) {
                // console.log(this.Notification.RefilledGardenRow + ' notification');
                // console.log(notification);
                if (notification.args.new_cards.length >= 5) {
                    this.stock[this.Stock.GardenRow].removeAllTo(this.Stock.GardenStack);
                }
                for (var card of notification.args.new_cards) {
                    this.stock[this.Stock.GardenRow].addToStockWithId(parseInt(card.type), card.id, this.Stock.GardenStack);
                }
                if (notification.args.garden_stack_counter) {
                    this.counter.garden_stack.setValue(notification.args.garden_stack_counter);
                }
                this.updateGardenStack();
            },

            notif_reshuffled: function () {
		// move all cards from discard to deck
		for (var card of this.stock[this.Stock.Discard].getAllItems()) {
		    this.moveVisibleToVisible(this.Stock.Discard, this.Stock.Deck, card);
		}
		// update counters comes in separate notification
            },

            notif_updateCounters: function (notification) {
                // console.log(this.Notification.UpdateCounters + ' notification');
                // console.log(notification);
                this.updateCounter(notification.args.counters);
            },

	    notif_victory: function (notification) {
                // console.log(this.Notification.UpdateCounters + ' notification');
                // console.log(notification);
		this.scoreCtrl[notification.args.player_id].setValue(1);
	    },

            // Utility functions

            changeState: function (targetState, args = {}) {
                args.lock = true;
                this.ajaxcall("/abandonallartichokes/abandonallartichokes/" + targetState + ".html", args,
			      this, function (result) { }, function (is_error) { });
            },

	    showDisplayedArea: function (force = false) {
		if (force || this.stock[this.Stock.DisplayedCard].count() > 0) {
		    dojo.removeClass('displayed_card_area', 'artichoke_hidden');
		} else {
		    dojo.addClass('displayed_card_area', 'artichoke_hidden');
		}
            },

            showCardPlay: function (player_id, from, from_arg, to, to_arg, card, counters) {
		// if something is moving to displayed card area, show area
		if (to == this.Stock.DisplayedCard) {
		    this.showDisplayedArea(true);
		}
		// do the normal card move
                if (this.isVisible(from, from_arg)) {
                    if (this.isVisible(to, to_arg)) {
                        this.moveVisibleToVisible(from, to, card);
                    } else {
                        this.moveVisibleToPanel(from, to_arg, card);
                    }
                } else {
                    if (this.isVisible(to, to_arg)) {
                        this.movePanelToVisible(from_arg, to, card);
                    }
                }
		// if there are no more cards in the display area, hide it
		setTimeout(() => this.showDisplayedArea(), 1000);

                this.updateCounter(counters);
            },

            isVisible: function (location, location_arg) {
                switch (location) {
                case this.Stock.GardenStack:
                case this.Stock.GardenRow:
                case this.Stock.DisplayedCard:
                case this.Stock.PlayedCard:
                case this.Stock.Compost:
                    return true;
                case this.Stock.Deck:
                case this.Stock.Discard:
                case this.Stock.Hand:
		    if (this.isSpectator) {
			return false;
		    }
                    if (location_arg == this.player_id) {
                        return true;
                    }
                    return false;
                default:
                    // console.log("unhandled case '" + location + "' in isVisible()");
                    return false;
                }
            },

            moveVisibleToVisible: function (from, to, card) {
                if (from == this.Stock.Deck || from == this.Stock.GardenStack) {
                    // the draw deck or the gardenStack only shows a card back, so we can not move from a specific card
                    // also, we don't have to remove it
                    this.stock[to].addToStockWithId(card.type, card.id, from);
                } else if (to == this.Stock.Deck || to == this.Stock.GardenStack) { // visibility already checked
                    //this.slideToObject(from + '_item_' + card.id, to);
                    this.stock[from].removeFromStockById(card.id, to);
                } else {
		    // TODO: this produces two animations: one from 'from' to 'to' (wanted)
		    // but the removeFromStockById adds another one from the current card location to the left part of the area
		    // how to remoe the second animation?
		    // setting 'to' gives another additional animation
		    // setting 'noupdate' just leaves the card there (and nothing removes it)
		    this.stock[to].addToStockWithId(card.type, card.id, from + '_item_' + card.id);
                    this.stock[from].removeFromStockById(card.id);
		    // This does not look good either, because the target appears before the move animation
		    //this.stock[from].removeFromStockById(card.id, to);
		    //this.stock[to].addToStockWithId(card.type, card.id);

                }
            },

            moveVisibleToPanel: function (from, player_id, card) {
		this.slideToObject(from + '_item_' + card.id, 'player_board_' + player_id);
                this.stock[from].removeFromStockById(card.id, 'player_board_' + player_id);
            },

            movePanelToVisible: function (player_id, to, card) {
                this.stock[to].addToStockWithId(card.type, card.id, 'player_board_' + player_id);
            },

            updateCounter: function (counters) {
                for (var player_id in counters) {
                    this.counter[player_id].hand.setValue(counters[player_id].hand);
                    this.counter[player_id].deck.setValue(counters[player_id].deck);
                    this.counter[player_id].discard.setValue(counters[player_id].discard);
		    if (this.gamedatas.artichoke_count_option > 0) {
			this.counter[player_id].artichokes.setValue(counters[player_id].artichokes);
		    }
                }
                this.updateDecks();
            },

            updateDecks: function () {
		// for spectators, do nothing
		if (this.isSpectator) return;
            this.updateDecksAccordingToCounter(this.counter[this.player_id].deck.getValue(), this.Stock.Deck);
                // clean out discard if no cards there
                if (this.counter[this.player_id].discard.getValue() == 0) {
                    this.stock[this.Stock.Discard].removeAll();
                }
            },

        updateGardenStack: function () {
            this.updateDecksAccordingToCounter(this.counter.garden_stack.getValue(), this.Stock.GardenStack);
	    remaining = this.counter.garden_stack.getValue() > 10 ? 10 : this.counter.garden_stack.getValue();
	    if (remaining < 10) {
		offset = 66 + 6 * (10 - remaining);
		$('garden_stack_counter').style.right = offset + 'px';
	    }
           /* this.stock[this.Stock.GardenStack].removeAll();
            const gardenStackCounter = this.gamedatas.counters.garden_stack >= 10? 10 : this.gamedatas.counters.garden_stack;
            for (i = 0; i <= gardenStackCounter; i++) {
                this.stock[this.Stock.GardenStack].addToStock(this.CardBackId);
            }*/
        },

        //util function
        updateDecksAccordingToCounter: function(counter, stockName) {
            const counterWithLimit = counter >= 10? 10 : counter;
            //deck_target = this.counter[this.player_id].deck.getValue();
            let stockCount = this.stock[stockName].count();
            while (stockCount < counterWithLimit) {
                this.stock[stockName].addToStock(this.CardBackId);
                stockCount++;
            }
            if (stockCount > counterWithLimit) {
                if (counterWithLimit == 0) {
                    this.stock[stockName].removeAll();
                } else {
                    while (stockCount > counterWithLimit) {
                        this.stock[stockName].removeFromStock(this.CardBackId);
                        stockCount--;
                    }
                }
            }
        },

	    setupNewCard: function(card_div, card_type_id, card_id) {
		const cardTypeId = Number(card_type_id);
		const fullText = this.getVegetableInfoText(cardTypeId);
		if (cardTypeId >= this.Vegetables.ARTICHOKE1 && cardTypeId != this.Vegetables.RHUBARB) {
                    this.addTooltip(card_div.id, fullText, "");
		} else {
                    this.addTooltip(card_div.id, "", fullText);
		}

		const fullTextSplit = fullText.split('<hr/>');
		const veggieName = fullTextSplit[0];
		const veggieDescription = fullTextSplit[1];
		dojo.place(`
                <div class="name ${[11, 12, 13, 14, 15].includes(cardTypeId) ? 'artichoke' : ''}" style="color: ${this.VegetablesColors[this.lang_prefix][cardTypeId]};">${veggieName}</div>
                <div class="description ${[1, 2, 5].includes(cardTypeId) ? 'light-text' : ''}">${veggieDescription}</div>
            `, card_div.id);
	    },

            getVegetableInfoText: function(type) {
		const typeNo = parseInt(type);
                switch (typeNo) {
                case this.Vegetables.BEET:
                    return _("<b>BEET</b><hr/>You and an opponent each reveal a random card. Compost both if Artichokes, otherwise swap them.")
                case this.Vegetables.BROCCOLI:
                    return _("<b>BROCCOLI</b><hr/>Compost an Artichoke, if your hand has three or more Artichokes.")
                case this.Vegetables.CARROT:
                    return _("<b>CARROT</b><hr/>As your only play action, compost exactly two Artichokes along with this card.")
                case this.Vegetables.CORN:
                    return _("<b>CORN</b><hr/>Play this card with an Artichoke. Then put a card from the Garden Row on top of your Deck.")
                case this.Vegetables.EGGPLANT:
                    return _("<b>EGGPLANT</b><hr/>Compost an Artichoke, along with this card. All players pass two cards to the left.")
                case this.Vegetables.LEEK:
		    return _("<b>LEEK</b><hr/>Reveal the top card of an opponent's Deck. Put it into your hand or on top of their Discard Pile.")
		case this.Vegetables.ONION:
                    return _("<b>ONION</b><hr/>Compost an Artichoke. Put this card on top of another player's Discard Pile.")
                case this.Vegetables.PEAS:
                    return _("<b>PEAS</b><hr/>Reveal two cards from the Garden Stack. Put one on your Discard pile, the other on an opponent's.")
                case this.Vegetables.PEPPER:
                    return _("<b>PEPPER</b><hr/>Put a card from your Discard Pile on top of your Deck.")
                case this.Vegetables.POTATO:
                    return _("<b>POTATO</b><hr/>Reveal the top card of your Deck. Compost if Artichoke, otherwise discard it.")
                case this.Vegetables.ARTICHOKE1:
		    return _("<b>ARTICHOKE</b><hr/>Don't break my heart!")
                case this.Vegetables.ARTICHOKE2:
		    return _("<b>ARTICHOKE</b><hr/>Did you know that I have thorns?!")
                case this.Vegetables.ARTICHOKE3:
		    return _("<b>ARTICHOKE</b><hr/>My stem is itching!")
                case this.Vegetables.ARTICHOKE4:
		    return _("<b>ARTICHOKE</b><hr/>Okey dokey!")
                case this.Vegetables.ARTICHOKE5:
                    return _("<b>ARTICHOKE</b><hr/>Looking forward to being abandoned by you!")
                case this.Vegetables.RHUBARB:
                    return _("<b>RHUBARB</b><hr/>Compost this card to refresh the Garden Row, then harvest a card. (Place old cards under Garden Stack.)")
                }
            },
        });
    });
