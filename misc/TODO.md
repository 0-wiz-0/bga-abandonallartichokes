Possibly Later
===
- rhubarb
- reduce overlap for hand on big displays? (window.screen.availWidth)
- display other player's played cards somewhere/somehow differently?

New comments
===
- Some additional animations would be helpful:
  - getting a new hand: card by card in quick succession from the
    personal deck. (You can make them flip if you want but it is not
    necessary.
  - the same for discarding the hand after ending the turn animating
    new card in display from deck
- Include which cards can be played when players turn comes up. Also,
  include options at the beginning of each turn to make game play
  smooth.
- The way the cards overlap, sometimes you want to read a card that is
  partially obscured. When possible, I think it would be better if the
  cards do not overlap.
- I've used https://iconify.design/ in two of my games, which is a
  JavaScript library that handles loading icons from a huge
  catalog. If you don't want the overhead of the JS library, you could
  also use their site to search for and download icons as .svg image
  files (you can even re-color the icon before you download the
  image).

  I have the same 3 things in the panel for one of my games and I
  think these are the icons I used, but of course there are many
  choices among their catalog of free icons.
  https://iconify.design/icon-sets/mdi/hand-right.html
  https://iconify.design/icon-sets/mdi/cards.html
  https://iconify.design/icon-sets/mdi/shuffle-variant.html

  I'm not confident my usage of these icons in my game is the best
  example. Since you seem to have more available space in your player
  panel than me, I'd recommend keeping the word in addition to adding
  a small icon, perhaps.
- when potato reshuffles, discard pile is not removed
- spectators get no message for eggplant passing
- card move for shuffle/animation at turn end
- garden pile # in garden area
- message for reshuffle & draw hand
- explicit mention who was chosen as target of card
  - log
  - (message?)
- show winning hand/(all hands?)
- compost background
- text in (garden row)/compost
- spread out discard pile? (#41702)
  - no, but perhaps tooltip with card counts?
- mark displayed cards as other players' discard pile etc (#41753)
- add option for public artichoke count (#41540)

Waiting on others
===
- Corn graphics issue (white box)
- German graphics - how to integrate them?
- Tableau graphics

Fixed bugs - to check
===
+ bug in translation: vegetable names not translated

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
