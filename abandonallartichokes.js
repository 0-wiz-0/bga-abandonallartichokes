/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * AbandonAllArtichokes implementation : © <Your name here> <Your email address here>
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
                console.log('abandonallartichokes constructor');

		// css spritesheet properties
                this.cardwidth = 150;
                this.cardheight = 225;
		this.image_items_per_row = 4;
		this.spritesheet = 'img/spritesheet150.jpg';
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
                    RefilledGardenRow: "refilled_garden_row",
                    UpdateCounters: "update_counters",
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
                };
                this.CardBackId = 1;

                //vegetable types => numbers match define in material.inc.php; this also corresponds to image order in the css sprite
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
		    BACK: 16,
                };
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
                console.log("Starting game setup");

                // TODO: remove
                console.log(gamedatas);

                this.counter = {};
                // Setting up player boards
                for (var player_id in gamedatas.players) {
                    var player = gamedatas.players[player_id];
                    var player_board_div = $('player_board_' + player_id);
                    dojo.place(this.format_block('jstpl_player_board', {id: player_id}), player_board_div);

                    this.counter[player_id] = {};
                    this.createCounter(player_id, 'hand');
                    this.createCounter(player_id, 'deck');
                    this.createCounter(player_id, 'discard');
                }

                const stock_constructor = [
                    {name: this.Stock.GardenRow, callback: 'onGardenRowSelect', selectionMode: 1},
                    {name: this.Stock.Hand, callback: 'onPlayerHandSelect', selectionMode: 2},
                    {name: this.Stock.DisplayedCard, callback: 'onDisplayedCardSelect', selectionMode: 1},
                    {name: this.Stock.PlayedCard, callback: null, selectionMode: 0},
                    {name: this.Stock.Discard, callback: null, selectionMode: 0, overlap: 1},
                    {name: this.Stock.Compost, callback: null, selectionMode: 0, overlap: 1},
                ];

                const extraClasses = 'card';
                this.stock = {};
                for (var stock_entry of stock_constructor) {
                    this.stock[stock_entry.name] = this.setupCardStocks(stock_entry.name, stock_entry.callback);
                    this.stock[stock_entry.name].setSelectionMode(stock_entry.selectionMode);
                    this.stock[stock_entry.name].setSelectionAppearance('class');
		    this.stock[stock_entry.name].extraClasses = extraClasses;
		    if (stock_entry.overlap) this.stock[stock_entry.name].setOverlap(stock_entry.overlap);
                    this.stock[stock_entry.name].autowidth = true;
		    // automatically add tooltips
		    this.stock[stock_entry.name].onItemCreate = dojo.hitch(this, 'setupNewCard');
                    this.addCardsToStock(this.stock[stock_entry.name], this.gamedatas[stock_entry.name]);
                }

                // draw deck is special, we only show card backs
                this.stock[this.Stock.Deck] = new ebg.stock();
                this.stock[this.Stock.Deck].create(this, $(this.Stock.Deck), this.cardwidth, this.cardheight);
		this.stock[this.Stock.Deck].image_items_per_row = this.image_items_per_row;
                this.stock[this.Stock.Deck].setSelectionMode(0);
                this.stock[this.Stock.Deck].setOverlap(1);
                this.stock[this.Stock.Deck].extraClasses = extraClasses;
                this.stock[this.Stock.Deck].autowidth = true;
                this.stock[this.Stock.Deck].addItemType(this.CardBackId, 0, g_gamethemeurl + this.spritesheet, this.Vegetables.BACK - 1);
                this.updateDecks();

                this.setupNotifications();

                console.log(this);
                console.log("Ending game setup");
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
            setupCardStocks: function (id, selectionChangeFunctionName) {
                var stock = new ebg.stock();
                stock.create(this, $(id), this.cardwidth, this.cardheight);
		stock.image_items_per_row = this.image_items_per_row;
                for (var vegetable_id = 1; vegetable_id < 16; vegetable_id++) {
		    // 1-10: main vegetables, 11-15: artichokes
                    stock.addItemType(vegetable_id, 0, g_gamethemeurl + this.spritesheet, vegetable_id - 1);
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
                console.log('Entering state: ' + stateName);

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
                console.log('Leaving state: ' + stateName);

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
                console.log('onUpdateActionButtons: ' + stateName);
		console.log(args);

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
						 _('Choose ') + this.gamedatas.players[player_id].name,
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

            // Notifications
            setupNotifications: function () {
                console.log('notifications subscriptions setup');

                dojo.subscribe(this.Notification.CardMoved, this, "notif_cardMoved");
                dojo.subscribe(this.Notification.DrewHand, this, "notif_drewHand");
                dojo.subscribe(this.Notification.RefilledGardenRow, this, "notif_refilledGardenRow");
                dojo.subscribe(this.Notification.UpdateCounters, this, "notif_updateCounters");
            },

            notif_cardMoved: function (notification) {
                console.log(this.Notification.CardMoved + ' notification');
                console.log(notification);
		// for five types of artichokes, add type_arg
		notification.args.card.type = parseInt(notification.args.card.type) + parseInt(notification.args.card.type_arg);
                this.showCardPlay(notification.args.player_id,
                    notification.args.origin, notification.args.origin_arg,
                    notification.args.destination, notification.args.destination_arg,
                    notification.args.card, notification.args.counters);
            },

            notif_drewHand: function (notification) {
                console.log(this.Notification.DrewHand + ' notification');
                console.log(notification);
                this.stock[this.Stock.Hand].removeAll();
                this.addCardsToStock(this.stock[this.Stock.Hand], notification.args.cards);
                this.stock[this.Stock.Discard].removeAll();
                this.addCardsToStock(this.stock[this.Stock.Discard], notification.args.discard);
                this.updateCounter(notification.args.counters);
            },

            notif_refilledGardenRow: function (notification) {
                console.log(this.Notification.RefilledGardenRow + ' notification');
                console.log(notification);
                if (notification.args.new_cards.length >= 5) {
                    this.stock[this.Stock.GardenRow].removeAll();
                }
                for (var card of notification.args.new_cards) {
                    this.stock[this.Stock.GardenRow].addToStockWithId(card.type, card.id);
                }
            },

            notif_updateCounters: function (notification) {
                console.log(this.Notification.UpdateCounters + ' notification');
                console.log(notification);
                this.updateCounter(notification.args.counters);
            },

            // Utility functions

            changeState: function (targetState, args = {}) {
                args.lock = true;
                this.ajaxcall("/abandonallartichokes/abandonallartichokes/" + targetState + ".html", args,
			      this, function (result) { }, function (is_error) { });
            },

            showCardPlay: function (player_id, from, from_arg, to, to_arg, card, counters) {
                if (this.isVisible(from, from_arg)) {
                    if (this.isVisible(to, to_arg)) {
                        this.moveVisibleToVisible(from, to, card);
                    } else {
                        this.moveVisibleToPanel(from, player_id, card);
                    }
                } else {
                    if (this.isVisible(to, to_arg)) {
                        this.movePanelToVisible(player_id, to, card);
                    }
                }

                this.updateCounter(counters);
            },

            isVisible: function (location, location_arg) {
                switch (location) {
                    case this.Stock.GardenStack:
                        return false;
                    case this.Stock.GardenRow:
                    case this.Stock.DisplayedCard:
                    case this.Stock.PlayedCard:
                    case this.Stock.Compost:
                        return true;
                    case this.Stock.Deck:
                    case this.Stock.Discard:
                    case this.Stock.Hand:
                        if (location_arg == this.player_id) {
                            return true;
                        }
                        return false;
                    default:
                        console.log("unhandled case '" + location + "' in isVisible()");
                        return false;
                }
            },

            moveVisibleToVisible: function (from, to, card) {
                if (from == this.Stock.Deck) {
                    // the draw deck only shows a card back, so we can not move from a specific card
                    // also, we don't have to remove it
                    this.stock[to].addToStockWithId(card.type, card.id, from);
                } else if (to == this.Stock.Deck) { // visibility already checked
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
                }
                this.updateDecks();
            },

            updateDecks: function () {
              	//update deck according to counter
		deck_target = this.counter[this.player_id].deck.getValue();
		deck_status = this.stock[this.Stock.Deck].count();
		while (deck_status < deck_target) {
		    this.stock[this.Stock.Deck].addToStock(this.CardBackId);
		    deck_status++;
		}
		if (deck_status > deck_target) {
		    if (deck_target == 0) {
			this.stock[this.Stock.Deck].removeAll();
		    } else {
			while (deck_status > deck_target) {
			    this.stock[this.Stock.Deck].removeFromStock(this.CardBackId);
			    deck_status--;
			}
		    }
		}
                // clean out discard if no cards there
                if (this.counter[this.player_id].discard.getValue() == 0) {
                    this.stock[this.Stock.Discard].removeAll();
                }
            },

	    setupNewCard: function(card_div, card_type_id, card_id) {
		if (card_type_id >= this.Vegetables.ARTICHOKE1) {
		    this.addTooltip(card_div.id, this.getVegetableInfoText(card_type_id), "");
		} else {
		    this.addTooltip(card_div.id, "", this.getVegetableInfoText(card_type_id));
		}
	    },

            getVegetableInfoText: function(type) {
		const typeNo = parseInt(type);
                switch (typeNo) {
                case this.Vegetables.BEET:
                    return _("You and an opponent each reveal a random card. Compost both if Artichokes, otherwise swap them.")
                case this.Vegetables.BROCCOLI:
                    return _("Compost an Artichoke, if your hand has three or more Artichokes.")
                case this.Vegetables.CARROT:
                    return _("As your only play action, compost exactly two Artichokes along with this card.")
                case this.Vegetables.CORN:
                    return _("Play this card with an Artichoke. Then put a card from the Garden Row on top of your Deck.")
                case this.Vegetables.EGGPLANT:
                    return _("Compost an Artichoke, along with this card. All players pass two cards to the left.")
                case this.Vegetables.LEEK:
		    return _("Reveal the top card of an opponent's Deck. Put it into your hand or on top of their Discard Pile.")
		case this.Vegetables.ONION:
                    return _("Compost an Artichoke. Put this card on top of another player's Discard Pile.")
                case this.Vegetables.PEAS:
                    return _("Reveal two cards from the Garden Stack. Put one on your Discard pile, the other on an opponent's.")
                case this.Vegetables.PEPPER:
                    return _("Put a card from your Discard Pile on top of your Deck.")
                case this.Vegetables.POTATO:
                    return _("Reveal the top card of your Deck. Compost if Artichoke, otherwise discard it.")
                case this.Vegetables.ARTICHOKE1:
		    return _("Don't break my heart!")
                case this.Vegetables.ARTICHOKE2:
		    return _("Did you know that I have thorns?!")
                case this.Vegetables.ARTICHOKE3:
		    return _("My stem is itching!")
                case this.Vegetables.ARTICHOKE4:
		    return _("Okey dokey!")
                case this.Vegetables.ARTICHOKE5:
                    return _("Looking forward to being abandoned by you!")
                }
            },
        });
    });
