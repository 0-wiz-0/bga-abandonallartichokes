Possibly Later
===
- rhubarb
- checks on client side
- reduce overlap for hand on big displays? (window.screen.availWidth)

Cleanups/Bugs
===
- debug animation issues
  - when moving from visible to visible, cards are moved, then their
    shadows are removed from where they were before
  - when potato/broccoli is played, for spectator, the card moves
    to played area then fades out, instead of two animations

Comments from players that will for now not be implemented
===
- you can only see in the log who selects who
- names for areas might be helpful
- If there are <= 2 cards in my hand, automatically select those as
  the cards to pass
- The positioning is not what I expected based on other BGA games. I
  expect my hand at the top, with the garden/common area below.

New comments
===
- Use some divs to separate areas. You can use class="whiteblock" or
  something else.
- I will organize a player area like this: deck to the left, discard
  to the right and hand in between and not above. Played cards above
  the hand for display, I would also put the deck to the left, display
  in the middle and compost at the right side.
- Some additional animations would be helpful:
  - getting a new hand: card by card in quick succession from the
    personal deck. (You can make them flip if you want but it is not
    necessary.
  - the same for discarding the hand after ending the turn animating
    new card in display from deck
