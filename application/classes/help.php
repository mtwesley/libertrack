<?php

class Help extends Notify {

  protected static $view = 'help';

  public static function msg($title, $message, $session = FALSE) {
    parent::msg($message, $title, $session);
  }

  public static function render() {
    parent::render();
  }
}