{OVERALL_GAME_HEADER}

<script type="text/javascript">

var jstpl_player_board = '<div class="artichoke_deck_board">\
    <div><span class="label artichoke_label">deck</span><span id="deck_${id}">0</span></div>\
    <div><span class="label artichoke_label">hand</span><span id="hand_${id}">0</span></div>\
    <div><span class="label artichoke_label">discard</span><span id="discard_${id}">0</span></div>\
</div>';

var jstpl_fake_card = "<div id=\"${id}\" class=\"stockitem\" style=\"top:${top}px;left:${left}px;width:${width}px;height:${height}px;z-index:${position};background-image:url('${image}');\"></div>";

</script>

<div id="table">
  <div id="garden_area">
    <div id="garden_row"></div>
  </div>
  <div id="common_area" class="artichoke_flex_row">
    <div id="played_card_area">
      <div id="played_card"></div>
    </div>
    <div id="displayed_card_area">
      <div id="displayed_card"></div>
    </div>
    <div id="compost_area">
      <div id="compost"></div>
    </div>
  </div>
  <div id="hand_area">
    <div id="hand"></div>
  </div>
  <div id="player_area" class="artichoke_flex_row">
    <div id="draw_pile_area">
	 <div id="deck"></div>
    </div>
    <div id="discard_area">
      <div id="discard"></div>
    </div>
  </div>
</div>

{OVERALL_GAME_FOOTER}
