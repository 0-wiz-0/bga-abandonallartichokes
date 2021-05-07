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
    "dojo","dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter",
    "ebg/stock"
],
function (dojo, declare) {
    return declare("bgagame.abandonallartichokes", ebg.core.gamegui, {
	constructor: function() {
            console.log('abandonallartichokes constructor');

            this.cardwidth = 150;
            this.cardheight = 200;
	    // the values must be the same in
	    // - gamedatas
	    // - HTML *.tpl file (div id)
	    // - stock constructor (below)
	    // - php code (material.inc.php)
	    // TODO: make this nicer
	    this.Stock = {
		GardenRow: 'garden_row',
		Hand: 'hand',
		PlayedCard: 'played_card',
		Compost: 'compost',
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
        
        setup: function(gamedatas) {
            console.log( "Starting game setup" );

	    // TODO: remove
            console.log(gamedatas);
            // Setting up player boards
            for( var player_id in gamedatas.players )
            {
                var player = gamedatas.players[player_id];
                // TODO: Setting up players boards if needed
            }

	    const stock_constructor = [
		{ name: this.Stock.GardenRow, callback: 'onGardenRowSelect', selectionMode: 1 },
		{ name: this.Stock.Hand, callback: 'onPlayerHandSelectionChanged', selectionMode: 1 },
		{ name: this.Stock.PlayedCard, callback: null, selectionMode: 0 },
		{ name: this.Stock.Compost, callback: null, selectionMode: 0 },
	    ];

	    this.stock = new Object();
	    for (var stock_entry of stock_constructor) {
		console.log(this.stock);
		console.log(stock_entry);
		this.stock[stock_entry.name] = this.setupCardStocks(stock_entry.name, stock_entry.callback);
		this.stock[stock_entry.name].setSelectionMode(stock_entry.selectionMode);
		this.addCardsToStock(this.stock[stock_entry.name], this.gamedatas[stock_entry.name]);
	    }

	    console.log("stock");
	    console.log(this.stock);
            // TODO: Set up your game interface here, according to "gamedatas"
            
 
            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();

            console.log( "Ending game setup" );
        },
       

        ///////////////////////////////////////////////////
        //// Game & client states

        // Initialize a card stock
        // Arguments: div id, function which occurs when the card selection changes
        setupCardStocks: function(id, selectionChangeFunctionName) {
            var stock = new ebg.stock();
            stock.create(this, $(id), this.cardwidth, this.cardheight);
            for (var vegetable_id = 1; vegetable_id < 12; vegetable_id++) {
                stock.addItemType(vegetable_id, vegetable_id, g_gamethemeurl + 'img/' + vegetable_id + '.png', vegetable_id);
            }
	    if (selectionChangeFunctionName != null) {
		dojo.connect(stock, 'onChangeSelection', this, selectionChangeFunctionName);
	    }
            return stock;
        },

	// Add an array of server cards to a particular stock
        addCardsToStock: function(stock, cards) {
	    stock.removeAll();
            Object.values(cards).forEach(function(card) {
                stock.addToStockWithId(card.type, card.id);
            });
	},

	onPlayerHandSelectionChanged: function(control_name, item_id) {
	    var items = this.stock[this.Stock.Hand].getSelectedItems();

	    if (items.length > 0) {
		// TODO: this will break for eggplant
                if( this.checkAction('playCard', true)) {
                    var card_id = items[0].id;

                    this.ajaxcall("/abandonallartichokes/abandonallartichokes/playCard.html", { 
                        id: card_id,
			lock: true
                    }, this, function(result) {  }, function (is_error) { } );                        

                    this.stock[this.Stock.Hand].unselectAll();
                }
		else {
		    // TODO: report error
		}
	    }
            else
            {
                // TODO: report error
            }                
	},

	onGardenRowSelect: function(control_name, item_id) {
	    var items = this.stock[this.Stock.GardenRow].getSelectedItems();

	    if (items.length > 0) {
                if( this.checkAction('harvestCard', true)) {
                    var card_id = items[0].id;

                    this.ajaxcall("/abandonallartichokes/abandonallartichokes/harvestCard.html", { 
                        id: card_id,
			lock: true
                    }, this, function(result) {  }, function (is_error) { } );                        

                    this.stock[this.Stock.GardenRow].unselectAll();
                }
		else {
		    // TODO: report error
		}
	    }
            else
            {
                // TODO: report error
            }                
	},

        // onEnteringState: this method is called each time we are entering into a new game state.
        //                  You can use this method to perform some user interface changes at this moment.
        //
        onEnteringState: function( stateName, args )
        {
            console.log( 'Entering state: '+stateName );
            
            switch( stateName )
            {
            
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
        onLeavingState: function( stateName )
        {
            console.log( 'Leaving state: '+stateName );
            
            switch( stateName )
            {
            
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

        // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
        //                        action status bar (ie: the HTML links in the status bar).
        //        
        onUpdateActionButtons: function( stateName, args )
        {
            console.log( 'onUpdateActionButtons: '+stateName );
                      
            if( this.isCurrentPlayerActive() )
            {            
                switch( stateName )
                {
/*               
                 Example:
 
                 case 'myGameState':
                    
                    // Add 3 action buttons in the action status bar:
                    
                    this.addActionButton( 'button_1_id', _('Button 1 label'), 'onMyMethodToCall1' ); 
                    this.addActionButton( 'button_2_id', _('Button 2 label'), 'onMyMethodToCall2' ); 
                    this.addActionButton( 'button_3_id', _('Button 3 label'), 'onMyMethodToCall3' ); 
                    break;
*/
                }
            }
        },        

        ///////////////////////////////////////////////////
        //// Utility methods
        
        /*
        
            Here, you can defines some utility methods that you can use everywhere in your javascript
            script.
        
        */


        ///////////////////////////////////////////////////
        //// Player's action
        
        /*
        
            Here, you are defining methods to handle player's action (ex: results of mouse click on 
            game objects).
            
            Most of the time, these methods:
            _ check the action is possible at this game state.
            _ make a call to the game server
        
        */
        
        /* Example:
        
        onMyMethodToCall1: function( evt )
        {
            console.log( 'onMyMethodToCall1' );
            
            // Preventing default browser reaction
            dojo.stopEvent( evt );

            // Check that this action is possible (see "possibleactions" in states.inc.php)
            if( ! this.checkAction( 'myAction' ) )
            {   return; }

            this.ajaxcall( "/abandonallartichokes/abandonallartichokes/myAction.html", { 
                                                                    lock: true, 
                                                                    myArgument1: arg1, 
                                                                    myArgument2: arg2,
                                                                    ...
                                                                 }, 
                         this, function( result ) {
                            
                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)
                            
                         }, function( is_error) {

                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)

                         } );        
        },        
        
        */

        
        ///////////////////////////////////////////////////
        //// Reaction to cometD notifications

        /*
            setupNotifications:
            
            In this method, you associate each of your game notifications with your local method to handle it.
            
            Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                  your abandonallartichokes.game.php file.
        
        */
        setupNotifications: function()
        {
            console.log( 'notifications subscriptions setup' );
            
            // TODO: here, associate your game notifications with local methods
            
            // Example 1: standard notification handling
            // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
            
            // Example 2: standard notification handling + tell the user interface to wait
            //            during 3 seconds after calling the method in order to let the players
            //            see what is happening in the game.
            // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
            // this.notifqueue.setSynchronous( 'cardPlayed', 3000 );
            //
	    dojo.subscribe('harvestCard', this, "notif_harvestCard");
	    dojo.subscribe('compostCard', this, "notif_compostCard");
        },  

	notif_harvestCard: function(notification) {
	    console.log('harvestCard notification');
	    console.log(notification);
	    if (notification.args.player_id == this.player_id) {
		this.stock[this.Stock.Hand].addToStockWithId(notification.args.type, notification.args.card_id, this.Stock.GardenRow + '_item_' + notification.args.card_id);
		this.stock[this.Stock.GardenRow].removeFromStockById(notification.args.card_id, this.Stock.Hand);
	    } else {
		this.stock[this.Stock.GardenRow].removeFromStockById(notification.args.card_id);
	    }
	},

	notif_compostCard: function(notification) {
	    console.log('compostCard notification');
	    console.log(notification);
	    //this.playerHand.addToStockWithId(notification.args.type, notification.args.card_id, 'garden_row_item_' + notification.args.card_id);
	    //this.gardenRow.removeFromStockById(notification.args.card_id, 'myhand');
	},

        // TODO: from this point and below, you can write your game notifications handling methods
        
        /*
        Example:
        
        notif_cardPlayed: function( notif )
        {
            console.log( 'notif_cardPlayed' );
            console.log( notif );
            
            // Note: notif.args contains the arguments specified during you "notifyAllPlayers" / "notifyPlayer" PHP call
            
            // TODO: play the card in the user interface.
        },    
        
        */
   });             
});
