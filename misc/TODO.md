Possibly Later
===
- display other player's played cards somewhere/somehow differently?
- spectators get no message for eggplant passing
- show winning hand?/(all hands?)
- spread out discard pile? (#41702)
  - no, but perhaps tooltip with card counts?
- add label to displayed area to show what it displays (#41753)
- make some option player options instead of game options
- automatically pass cards for eggplant if only one choice (#42734)
- don't show trays for spectator (remove hand area too?)
- improve draw hands animation

Comments from players that will for now not be implemented
===
- you can only see in the log who selects who
  -> no good way to show it, since players have no real area on the board
     which is good because that leaves more space for the cards
- names for areas might be helpful
  -> keep display as simple as possible
- If there are <= 2 cards in my hand, automatically select those as
  the cards to pass
  -> some effort to implement, and I think it might be too confusing for players
- The positioning is not what I expected based on other BGA games. I
  expect my hand at the top, with the garden/common area below.
  -> matches more what it looks like in real game
- Use some divs to separate areas. You can use class="whiteblock" or
  something else.
  -> I'd like to keep it visually simple
- I will organize a player area like this: deck to the left, discard
  to the right and hand in between and not above. Played cards above
  the hand for display, I would also put the deck to the left, display
  in the middle and compost at the right side
  -> had it like this, but this is a better use of space
- move compost to top row -> not enough space, we want cards as big as possible
- when playing broccoli, show that you have enough artichoke cards
  -> not necessary, implementation checks this
- put the card count in the corner of piles to show how many cards
  -> numbers are in top right, only really useful for deck and that might add confusion
- discard searchable
  -> needs space, and you can already recognize what's inside by the card border
- optional: slower gameflow
  -> not sure what exactly we can do here
- checks on client side
  -> not necessary, play servers are fast enough and this way we don't
     have to implement the code twice
- Include options at the beginning of each turn to make game play
  smooth.
  -> not sure what to do here
- Include which cards can be played when players turn comes up.
  -> would mean implementing whole logic on javascript side again,
  and server tells you quickly enough if you try making invalid moves
- text in (garden row)/compost
  -> I don't think it brings much, compost is now brown
- Some additional animations would be helpful:
  - getting a new hand: card by card in quick succession from the
    personal deck. (You can make them flip if you want but it is not
    necessary.
  - the same for discarding the hand after ending the turn animating
    new card in display from deck
  -> done, except for the "one by one" which is hard when using Stock
- Can I suggest to have timer (ie 2 seconds) when playing leek, potatoes or beet
  -> no, only slows game down, see log; leek is shown already
- Is it possible to see all the discard piles ?
  -> no, not in rules
- For beet card can we see the 2 cards before compost or swapping ?
  -> no, see log
- Undo button needed for some actions.
  -> out of scope
