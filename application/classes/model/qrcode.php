<?php

class Model_QRCode extends ORM {

  protected $_belongs_to = array(
    'user' => array()
  );

  public static function fields() {
    return array(
      'qrcode' => 'QR Code',
    );
  }

  public function image($filename = FALSE) {
    return QRCode::png($this->qrcode, $filename, QR_ECLEVEL_H, 2, 1);
  }

}
