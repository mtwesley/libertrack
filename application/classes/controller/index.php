<?php

class Controller_Index extends Controller {

  public function action_index() {
    $view = View::factory('main');
    
    $this->response->body($view);
  }
}