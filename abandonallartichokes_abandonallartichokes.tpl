{OVERALL_GAME_HEADER}

<script type="text/javascript">

var jstpl_player_board = '<div class="artichoke_deck_board">\
    <div class="artichoke_label_line"><div class="label artichoke_label"><img class="artichoke_icon" src="{GAMETHEMEURL}img/pile.svg"/></div><div id="deck_${id}">0</div></div>\
    <div class="artichoke_label_line"><div class="label artichoke_label"><img class="artichoke_icon" src="{GAMETHEMEURL}img/hand.svg"/></div><div id="hand_${id}">0</div></div>\
    <div class="artichoke_label_line"><div class="label artichoke_label"><img class="artichoke_icon" src="{GAMETHEMEURL}img/discard.svg"/></div><div id="discard_${id}">0</div></div>\
</div>';

var jstpl_fake_card = "<div id=\"${id}\" class=\"stockitem\" style=\"top:${top}px;left:${left}px;width:${width}px;height:${height}px;z-index:${position};background-image:url('${image}');\"></div>";

</script>

<div id="table">
  <div class="artichoke_flex_center">
    <div id="garden_area">
      <span id="garden_stack_counter">60</span>
      <div id="garden_row"></div>
    </div>
  </div>
  <div id="displayed_card_area" class="artichoke_hidden">
    <div id="displayed_card"></div>
  </div>
  <div id="hand_area" class="artichoke_flex_center">
    <div id="hand" class="artichoke_space_right"></div>
    <div id="played_card"></div>
  </div>
  <div id="player_area" class="artichoke_flex_away">
    <div id="left_part" class="artichoke_flex_center">
      <div id="discard" class="artichoke_cardmin artichoke_deck_space_right"></div>
      <div id="deck" class="artichoke_cardmin"></div>
    </div>
    <div id="compost" class="artichoke_cardmin"></div>
  </div>
</div>

{OVERALL_GAME_FOOTER}
