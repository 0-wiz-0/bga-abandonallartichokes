<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * AbandonAllArtichokes implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on https://boardgamearena.com.
 * See http://en.doc.boardgamearena.com/Studio for more information.
 * -----
 *
 * abandonallartichokes.action.php
 *
 * AbandonAllArtichokes main action entry point
 *
 *
 * In this file, you are describing all the methods that can be called from your
 * user interface logic (javascript).
 *
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.ajaxcall( "/abandonallartichokes/abandonallartichokes/myAction.html", ...)
 *
 */


  class action_abandonallartichokes extends APP_GameAction
  {
      // Constructor: please do not modify
      public function __default()
      {
          if( self::isArg( 'notifwindow') )
          {
              $this->view = "common_notifwindow";
              $this->viewArgs['table'] = self::getArg( "table", AT_posint, true );
          }
          else
          {
              $this->view = "abandonallartichokes_abandonallartichokes";
              self::trace( "Complete reinitialization of board game" );
          }
      }

      public function beetChooseOpponent() {
          self::setAjaxMode();
          $opponent_id = self::getArg("opponent_id", AT_posint, true);

          $this->game->beetChooseOpponent($opponent_id);
          self::ajaxResponse();
      }
  	
      public function cornTakeCard() {
          self::setAjaxMode();
          $id = self::getArg("id", AT_posint, true);

          $this->game->cornTakeCard($id);
          self::ajaxResponse();
      }

      public function eggplantChooseCards() {
          self::setAjaxMode();
          $card1 = self::getArg("card1", AT_posint, false);
          $card2 = self::getArg("card2", AT_posint, false);
          $cards = array();
          if ($card1 != null) {
              $cards[] = $card1;
          }
          if ($card2 != null) {
              $cards[] = $card2;
          }
          $this->game->eggplantChooseCards($cards);
          self::ajaxResponse();
      }

      public function harvestCard() {
          self::setAjaxMode();
          $id = self::getArg("id", AT_posint, true);

          $this->game->harvestCard($id);
          self::ajaxResponse();
      }

      public function leekChooseOpponent() {
          self::setAjaxMode();
          $opponent_id = self::getArg("opponent_id", AT_posint, true);

          $this->game->leekChooseOpponent($opponent_id);
          self::ajaxResponse();
      }

      public function leekTakeCard() {
          self::setAjaxMode();
          $take_card = self::getArg("take_card", AT_bool, true);

          $this->game->leekTakeCard($take_card);
          self::ajaxResponse();
      }

      public function pass() {
          self::setAjaxMode();
          $this->game->pass();
          self::ajaxResponse();
      }

      public function onionChooseOpponent() {
          self::setAjaxMode();
          $opponent_id = self::getArg("opponent_id", AT_posint, true);

          $this->game->onionChooseOpponent($opponent_id);
          self::ajaxResponse();
      }

      public function peasChooseOpponent() {
          self::setAjaxMode();
          $opponent_id = self::getArg("opponent_id", AT_posint, true);

          $this->game->peasChooseOpponent($opponent_id);
          self::ajaxResponse();
      }

      public function peasTakeCard() {
          self::setAjaxMode();
          $id = self::getArg("id", AT_posint, true);

          $this->game->peasTakeCard($id);
          self::ajaxResponse();
      }

      public function pepperTakeCard() {
          self::setAjaxMode();
          $id = self::getArg("id", AT_posint, true);

          $this->game->pepperTakeCard($id);
          self::ajaxResponse();
      }

      public function playCard() {
          self::setAjaxMode();
          $id = self::getArg("id", AT_posint, true);

          $this->game->playCard($id);
          self::ajaxResponse();
      }

  }

