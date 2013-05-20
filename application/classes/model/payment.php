<?php

class Model_Payment extends ORM {

  protected $_belongs_to = array(
    'invoice' => array(),
    'user'    => array()
  );

  protected $_table_name = 'invoice_payments';

}
