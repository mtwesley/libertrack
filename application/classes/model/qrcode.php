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

  public function image($filename = FALSE, $size = 2, $margin = 2) {
    return QRCode::png($this->qrcode, $filename, QR_ECLEVEL_H, $size, $margin);
  }

}
