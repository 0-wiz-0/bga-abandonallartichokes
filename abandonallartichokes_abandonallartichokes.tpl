{OVERALL_GAME_HEADER}

<script type="text/javascript">

var jstpl_player_board = '<div class="deck_board">\
    <span>deck</span><span id="deck_${id}">\${deck_count}</span>\
    <span>hand</span><span id="hand_${id}">\${hand_count}</span>\
    <span>discard</span><span id="discard_${id}">\${discard_count}</span>\
</div>';

</script>

<div id="table">
     <div id="garden_area">
       <!-- BEGIN garden_area -->
       {DESCRIPTION}
       <div id="garden_row"></div>
       <!-- END garden_area -->
     </div>
     <div id="played_card_area">
       <!-- BEGIN played_card_area -->
       {DESCRIPTION}
       <div id="played_card"></div>
       <!-- END played_card_area -->
     </div>
     <div id="hand_area">
       <!-- BEGIN hand_area -->
       {DESCRIPTION}
       <div id="hand"></div>
       <!-- END hand_area -->
     </div>
     <div id="compost_area">
       <!-- BEGIN compost_area -->
       {DESCRIPTION}
       <div id="compost"></div>
       <!-- END compost_area -->
     </div>
</div>

{OVERALL_GAME_FOOTER}
