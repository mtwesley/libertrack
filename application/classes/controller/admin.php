<?php

class Controller_Admin extends Controller {

  public function action_index() {
    $this->response->body(View::factory('header'));
  }

  public function action_operators() {
    $id = $this->request->param('id');
    $body = View::factory('header')->render();

    $model = ORM::factory('operator', $id);
    $form = Formo::form()
      ->orm('load', $model, array('sites', 'user_id', 'timestamp'), true)
      ->add($id ? 'update' : 'save', 'submit');

    if ($form->sent($_POST) and $form->load($_POST)) {
      try {
        $id ? $model->update() : $model->save();
        $body .= '<p>Operator saved.</p>';
      } catch (Exception $e) {
        $body .= '<p>Unable to save operator.</p>';
      }
    }

    $body .= $form->render();

    if ($id === null) {

      $operators = ORM::factory('operator')->find_all()->as_array();
      $body .= '<table border="1">';
      $body .= '<tr>';
      $body .= '<td><strong>tin</strong></td>';
      $body .= '<td><strong>name</strong></td>';
      $body .= '<td><strong>contact</strong></td>';
      $body .= '<td><strong>address</strong></td>';
      $body .= '<td><strong>email</strong></td>';
      $body .= '<td><strong>phone</strong></td>';
      $body .= '<td></td>';
      $body .= '</tr>';
      foreach ($operators as $operator) {
        $body .= '<tr>';
        $body .= '<td>'.$operator->tin.'</td>';
        $body .= '<td>'.$operator->name.'</td>';
        $body .= '<td>'.$operator->contact.'</td>';
        $body .= '<td>'.$operator->address.'</td>';
        $body .= '<td>'.$operator->email.'</td>';
        $body .= '<td>'.$operator->phone.'</td>';
        $body .= '<td>'.HTML::anchor('/admin/operators/'.$operator->id, 'edit').'</td>';
        $body .= '</tr>';
      }
      $body .= '</table>';

    }

    $this->response->body($body);
  }

  public function action_sites() {
    $id = $this->request->param('id');
    $body = View::factory('header')->render();

    $model = ORM::factory('site', $id);
    $form = Formo::form()
      ->orm('load', $model, array('blocks', 'printjobs', 'invoices', 'user_id', 'timestamp'), true)
      ->add($id ? 'update' : 'save', 'submit');

    if ($form->sent($_POST) and $form->load($_POST)) {
      try {
        $id ? $model->update() : $model->save();
        $body .= '<p>Site saved.</p>';
      } catch (Exception $e) {
        $body .= '<p>Unable to save site.</p>';
      }
    }

    $body .= $form->render();

    if ($id === null) {

      $sites = ORM::factory('site')->find_all()->as_array();
      $body .= '<table border="1">';
      $body .= '<tr>';
      $body .= '<td><strong>type</strong></td>';
      $body .= '<td><strong>reference</strong></td>';
      $body .= '<td><strong>name</strong></td>';
      $body .= '<td><strong>operator</strong></td>';
      $body .= '<td></td>';
      $body .= '</tr>';
      foreach ($sites as $site) {
        $body .= '<tr>';
        $body .= '<td>'.$site->type.'</td>';
        $body .= '<td>'.$site->reference.'</td>';
        $body .= '<td>'.$site->name.'</td>';
        $body .= '<td>'.$site->operator->name.'</td>';
        $body .= '<td>'.HTML::anchor('/admin/sites/'.$site->id, 'edit').'</td>';
        $body .= '</tr>';
      }
      $body .= '</table>';

    }

    $this->response->body($body);
  }

  public function action_blocks() {
    $id = $this->request->param('id');
    $body = View::factory('header')->render();

    $model = ORM::factory('block', $id);
    $form = Formo::form()
      ->orm('load', $model, array('user_id', 'timestamp'), true)
      ->add($id ? 'update' : 'save', 'submit');

    if ($form->sent($_POST) and $form->load($_POST)) {
      try {
        $id ? $model->update() : $model->save();
        $body .= '<p>Block saved.</p>';
      } catch (Exception $e) {
        $body .= '<p>Unable to save block.</p>';
      }
    }

    $body .= $form->render();

    if ($id === null) {

      $blocks = ORM::factory('block')->find_all()->as_array();
      $body .= '<table border="1">';
      $body .= '<tr>';
      $body .= '<td><strong>opeator</strong></td>';
      $body .= '<td><strong>site</strong></td>';
      $body .= '<td><strong>coordinates</strong></td>';
      $body .= '<td></td>';
      $body .= '</tr>';
      foreach ($blocks as $block) {
        $body .= '<tr>';
        $body .= '<td>'.$block->site->operator->name.'</td>';
        $body .= '<td>'.$block->site->name.'</td>';
        $body .= '<td>'.$block->coordinates.'</td>';
        $body .= '<td>'.HTML::anchor('/admin/blocks/'.$block->id, 'edit').'</td>';
        $body .= '</tr>';
      }
      $body .= '</table>';

    }

    $this->response->body($body);
  }

}
