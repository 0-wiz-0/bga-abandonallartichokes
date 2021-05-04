# Deck

The deck consists of 10 artichokes per player and 6 of each other
vegetable.

The players start with a personal deck of 10 artichokes, 5 in hand.

There is a garden row of 5 cards (of vegetables), refilled from the
garden stack after each player's turn.

## Locations

- garden deck (location "garden")
  - garden stack (location_arg 0)
  - garden row (location_arg 1)
- community compost pile (location "compost")
- player deck (location $player_id)
  - draw pile (location_arg 0)
  - hand (location_arg 1)
  - discard pile (location_arg 2)

# Turn overview

During a players turn:
- (garden row is refilled - automatic)
  - 4 of the same kind -> shuffle row into deck, draw 5 new cards
- harvest (draw) a card from the market into hand (not optional)
  -> "choose card from market"
- play zero or more card(s) from hand; some cards cause extra states
  - (beet: random cards drawn; no state necessary)
  - (broccoli: compost artichoke if three or more artichokes in hand; no state necessary)
  - (carrot + 2 artichokes: as only action: compost all three cards; no state necessary)
  - corn + artichoke: player chooses another card from the market (goes on top of the deck)
    -> "market chooser", but on top of deck instead of hand
  - eggplant + artichoke: compost both, all players pass two cards to the left
    -> "everyone choose two cards from hand"
  - leek: choose an opponent, show top card of draw pile (opponent must have cards outside of hand) - player chooses to keep it (into hand) or not (opponent's discard pile)
    -> "choose opponent"; then "take card or not"
  - onion + artichoke: choose an opponent that gets the onion
    -> "choose opponent"
  - peas: player gets two cards, keeps one, gives one to an opponent (both go the resp. discard piles)
    -> "choose one of two cards" then -> "choose player"
  - pepper: choose a card from discard pile (must have cards) to put on top of deck
    -> "choose card from discard pile"
  - (potato: show top card of draw deck, compost if artichoke, else discard; no state necessary)
  after each card is played, it is put into the player's discard pile
- (after all cards are played, the rest are discarded - automatic)
- (draw 5 cards from personal deck - automatic)
  - if less than 5 available, draw all of them
  - if no artichokes - WIN!
- next player's turn

List of states
-----

- player_start
- harvest
- play cards
- corn_market
- eggplant_everyone
- leek_choose_opponent
- leek_take_card
- onion_choose_opponent
- peas_choose_card
- peas_choose_opponent
- pepper_choose_card
- player_finish
- not_my_turn
