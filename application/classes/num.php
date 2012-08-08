<?php

class Num extends Kohana_Num {

  /**
	 * Converts a byte size number to a file size value. File sizes are defined in
	 * the format: SB, where S is the size (1, 8.5, 300, etc.) and B is the
	 * byte unit (KB, MB, GB, etc.). All valid byte units are defined in
	 * Num::$byte_units
	 *
	 *     echo Num::unbytes('204800');  // 200K
	 *     echo Num::unbytes('5242880');  // 5MB
	 *     echo Num::unbytes('1000');  // 1KB
	 *     echo Num::unbytes('2684354560'); // 2.5GB
	 *
	 * @param   string   byte size
   * @param   string   precision unit for SB rounding (default = 2)
	 * @return  float
	 */
	public static function unbytes($bytes, $precision = 2) {
    $units = array(
      'B',
      'KB',
      'MB',
      'GB',
      'TB'
    );

    $bytes = max($bytes, 0);
    $pow   = min(floor(($bytes ? log($bytes) : 0) / log(1024)), count($units) - 1);

    $bytes /= pow(1024, $pow);

    return round($bytes, $precision) . $units[$pow];
  }


}