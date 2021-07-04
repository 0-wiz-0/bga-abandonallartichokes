{OVERALL_GAME_HEADER}

<script type="text/javascript">

var jstpl_player_board = '<div class="artichoke_deck_board">\
    <div class="artichoke_label_line"><div class="label artichoke_label"><img class="artichoke_icon" src="{GAMETHEMEURL}img/pile.svg"/></div><div id="deck_${id}">0</div></div>\
    <div class="artichoke_label_line"><div class="label artichoke_label"><img class="artichoke_icon" src="{GAMETHEMEURL}img/hand.svg"/></div><div id="hand_${id}">0</div></div>\
    <div class="artichoke_label_line"><div class="label artichoke_label"><img class="artichoke_icon" src="{GAMETHEMEURL}img/discard.svg"/></div><div id="discard_${id}">0</div></div>\
    <div class="artichoke_label_line artichoke_hidden" id="optional_artichokes_${id}"><div class="label artichoke_label"><img class="artichoke_icon" src="{GAMETHEMEURL}img/artichoke.svg"/></div><div id="artichokes_${id}">-</div></div>\
</div>';

var jstpl_fake_card = "<div id=\"${id}\" class=\"stockitem\" style=\"top:${top}px;left:${left}px;width:${width}px;height:${height}px;z-index:${position};background-image:url('${image}');\"></div>";

</script>

<div id="table">
  <div class="artichoke_flex_center">
    <div id="garden_area">
      <div id="garden_row"></div>
    </div>
  </div>
  <div id="displayed_card_area" class="artichoke_flex_center artichoke_hidden whiteblock">
    <div id="displayed_card"></div>
  </div>
  <div id="hand_area" class="artichoke_flex_center">
    <div id="hand" class="artichoke_space_right"></div>
    <div id="played_card"></div>
  </div>
  <div class="decks_area artichoke_flex_away">
    <div class="personal_decks artichoke_flex_center">
      <div id="tray_discard" class="artichoke_tray">
	<div id="discard" class="artichoke_cards_on_tray artichoke_cardmin artichoke_deck_space_right"></div>
	<span class="artichoke_tray_description" id="tray_discard_description">Discard Pile</span>
      </div>
      <div id="tray_deck" class="artichoke_tray">
	<div id="deck" class="artichoke_cards_on_tray artichoke_cardmin"></div>
	<span class="artichoke_tray_description" id="tray_deck_description">Deck</span>
      </div>
    </div>
    <div class="common_decks artichoke_flex_center">
      <div id="compost_area" class="artichoke_tray">
        <div id="compost" class="artichoke_cards_on_tray artichoke_cardmin"></div>
        <span class="artichoke_tray_description" id="tray_compost_description">Compost</span>
      </div>
      <div id="garden_stack_area" class="artichoke_tray">
        <div id="garden_stack" class="artichoke_cards_on_tray artichoke_cardmin"></div>
        <span class="artichoke_tray_description" id="tray_garden_stack_description">Garden Stack</span>
        <span id="garden_stack_counter">60</span>
      </div>
    </div>
  </div>
</div>

{OVERALL_GAME_FOOTER}
