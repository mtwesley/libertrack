<?php

class Model_Report extends ORM {

  protected $_belongs_to = array(
    'user'  => array()
  );

  protected $_has_many = array(
    'files' => array()
  );


  public static $types = array(
    'SUMMARY' => array(
      'name'   => 'Data Analysis Report',
      'models' => array('site', 'operator', 'species')
    ),
    'CSV' => array(
      'name'   => 'Data Import Report',
      'models' => array('csv')
    ),
    'DATA' => array(
      'name'   => 'Data Analysis Report',
      'models' => array('ssf', 'tdf', 'ldf')
    ),
    'BARCODE' => array(
      'name'   => 'Barcode Review Report',
      'models' => array('site', 'ssf', 'tdf', 'ldf')
    )
  );

  public static $models = array(
    'operator' => array(
      'name'  => 'Operator',
      'table' => 'operators',
      'fields' => array(
        'tin'  => array(
          'name' => 'Operator TIN'
        ),
        'name' => array(
          'name' => 'Operator Name'
        )
      ),
      'models' => array(
        'site' => array(
          'type' => 'one_to_many',
          'local_field'   => 'id',
          'foreign_field' => 'operator_id'
        ),
        'ssf' => array(
          'type' => 'one_to_many',
          'local_field'   => 'id',
          'foreign_field' => 'operator_id',
        ),
        'tdf' => array(
          'type' => 'one_to_many',
          'local_field'   => 'id',
          'foreign_field' => 'operator_id'
        ),
        'ldf' => array(
          'type' => 'one_to_many',
          'local_field'   => 'id',
          'foreign_field' => 'operator_id'
        ),
        'specs' => array(
          'type' => 'one_to_many',
          'local_field'   => 'id',
          'foreign_field' => 'operator_id'
        ),
      )
    ),
    'site' => array(
      'name'   => 'Site',
      'table'  => 'sites',
      'fields' => array(
        'name' => array(
          'name' => 'Site Name'
        ),
        'type' => array(
          'name' => 'Site Type'
        ),
      ),
      'models' => array(
        'operator' => array(
          'type '=> 'many_to_one',
          'local_field'   => 'operator_id',
          'foreign_field' => 'id'
        ),
        'block' => array(
          'type' => 'one_to_many',
          'local_field'   => 'id',
          'foreign_field' => 'site_id'
        ),
        'ssf' => array(
          'type' => 'one_to_many',
          'local_field'   => 'id',
          'foreign_field' => 'site_id',
        ),
        'tdf' => array(
          'type' => 'one_to_many',
          'local_field'   => 'id',
          'foreign_field' => 'site_id'
        ),
        'ldf' => array(
          'type' => 'one_to_many',
          'local_field'   => 'id',
          'foreign_field' => 'site_id'
        ),
        'specs' => array(
          'type' => 'one_to_many',
          'local_field'   => 'id',
          'foreign_field' => 'site_id'
        ),
      )
    ),
    'block' => array(
      'name'  => 'Block',
      'table' => 'blocks',
      'fields' => array(
        'name' => array(
          'name' => 'Block Name'
        )
      ),
      'models' => array(
        'site' => array(
          'type' => 'many_to_one',
          'local_field'   => 'site_id',
          'foreign_field' => 'id'
        ),
        'ssf' => array(
          'type' => 'one_to_many',
          'local_field'   => 'id',
          'foreign_field' => 'block_id',
        ),
        'tdf' => array(
          'type' => 'one_to_many',
          'local_field'   => 'id',
          'foreign_field' => 'block_id'
        ),
      )
    ),
    'ssf' => array(
      'name'   => 'Stock Survey',
      'table'  => 'ssf_data',
      'fields' => array(
        'survey_line' => array(
          'name' => 'Survey Line'
        ),
        'cell_number' => array(
          'name' => 'Cell Number'
        ),
        'tree_map_number' => array(
          'name' => 'Tree Map Number'
        ),
        'diameter' => array(
          'name' => 'Diameter',
          'aggregates' => TRUE
        ),
        'height' => array(
          'name' => 'Height',
          'aggregates' => TRUE
        ),
        'volume' => array(
          'name' => 'Volume',
          'aggregates' => TRUE
        ),
        'create_date' => array(
          'name' => 'Date',
          'type' => 'date'
        ),
        'timestamp' => array(
          'name' => 'Uploaded',
          'type' => 'date'
        ),
        'status' => array(
          'name' => 'Status',
          'type' => 'data_status'
        ),
        'inspected' => array(
          'name' => 'Inspected',
          'type' => 'bool',
          'cast' => 'bool',
          'sql'  => "select id from ssf_verification where ssf_verification.barcode_id = :table.barcode_id"
        ),
        'is_verified' => array(
          'name' => 'Verified',
          'type' => 'bool',
          'cast' => 'bool',
          'sql'  => "select id from ssf_verification where ssf_verification.barcode_id = :table.barcode_id and ssf_verification.status = 'A'"
        ),
        'is_checked' => array(
          'name' => 'Checked',
          'type' => 'bool',
          'cast' => 'bool',
          'sql'  => "status <> 'P'"
        ),
        'has_passed' => array(
          'name' => 'Passed',
          'type' => 'bool',
          'cast' => 'bool',
          'sql'  => "status = 'A'"
        ),
        'has_failed' => array(
          'name' => 'Failed',
          'type' => 'bool',
          'cast' => 'bool',
          'sql'  => "status = 'R'"
        )
      ),
      'models' => array(
        'operator' => array(
          'type' => 'many_to_one',
          'local_field'   => 'operator_id',
          'foreign_field' => 'id'
        ),
        'site' => array(
          'type' => 'many_to_one',
          'local_field'   => 'site_id',
          'foreign_field' => 'id'
        ),
        'block' => array(
          'type' => 'many_to_one',
          'local_field'   => 'block_id',
          'foreign_field' => 'id'
        ),
        'species' => array(
          'type' => 'many_to_one',
          'local_field'   => 'species_id',
          'foreign_field' => 'id'
        ),
        'barcode' => array(
          'type' => 'one_to_one',
          'local_field'   => 'barcode_id',
          'foreign_field' => 'id'
        ),
        'tdf' => array(
          'type' => 'one_to_one',
          'local_field'   => 'barcode_id',
          'foreign_field' => 'tree_barcode_id'
        )
      )
    ),
    'tdf' => array(
      'name'   => 'Tree Data',
      'table'  => 'tdf_data',
      'fields' => array(
        'survey_line' => array(
          'name' => 'Survey Line'
        ),
        'cell_number' => array(
          'name' => 'Cell Number'
        ),
        'diameter' => array(
          'name' => 'Diameter',
          'aggregates' => TRUE
        ),
        'length' => array(
          'name' => 'Length',
          'aggregates' => TRUE
        ),
        'volume' => array(
          'name' => 'Volume',
          'aggregates' => TRUE
        ),
        'create_date' => array(
          'name' => 'Date',
          'type' => 'date'
        ),
        'timestamp' => array(
          'name' => 'Uploaded',
          'type' => 'date'
        ),
        'status' => array(
          'name' => 'Status',
          'type' => 'data_status'
        ),
        'inspected' => array(
          'name' => 'Inpsected',
          'type' => 'bool',
          'cast' => 'bool',
          'sql'  => "select id from tdf_verification where tdf_verification.barcode_id = :table.barcode_id"
        ),
        'is_verified' => array(
          'name' => 'Verified',
          'type' => 'bool',
          'cast' => 'bool',
          'sql'  => "select id from tdf_verification where tdf_verification.barcode_id = :table.barcode_id and tdf_verification.status = 'A'"
        ),
        'is_checked' => array(
          'name' => 'Checked',
          'type' => 'bool',
          'cast' => 'bool',
          'sql'  => "status <> 'P'"
        ),
        'has_passed' => array(
          'name' => 'Passed',
          'type' => 'bool',
          'cast' => 'bool',
          'sql'  => "status = 'A'"
        ),
        'has_failed' => array(
          'name' => 'Failed',
          'type' => 'bool',
          'cast' => 'bool',
          'sql'  => "status = 'R'"
        )
      ),
      'models' => array(
        'operator' => array(
          'type' => 'many_to_one',
          'local_field'   => 'operator_id',
          'foreign_field' => 'id'
        ),
        'site' => array(
          'type' => 'many_to_one',
          'local_field'   => 'site_id',
          'foreign_field' => 'id'
        ),
        'block' => array(
          'type' => 'many_to_one',
          'local_field'   => 'block_id',
          'foreign_field' => 'id'
        ),
        'species' => array(
          'type' => 'many_to_one',
          'local_field'   => 'species_id',
          'foreign_field' => 'id'
        ),
        'barcode' => array(
          'type' => 'one_to_one',
          'local_field'   => 'barcode_id',
          'foreign_field' => 'id'
        ),
        'ssf' => array(
          'type' => 'one_to_one',
          'local_field'   => 'tree_barcode_id',
          'foreign_field' => 'barcode_id'
        ),
        'ldf' => array(
          'type' => 'one_to_one',
          'local_field'   => 'barcode_id',
          'foreign_field' => 'parent_barcode_id'
        )
      )
    ),
    'ldf' => array(
      'name'   => 'Log Data',
      'table'  => 'ldf_data',
      'fields' => array(
        'diameter' => array(
          'name' => 'Diameter',
          'aggregates' => TRUE
        ),
        'length' => array(
          'name' => 'Length',
          'aggregates' => TRUE
        ),
        'volume' => array(
          'name' => 'Volume',
          'aggregates' => TRUE
        ),
        'create_date' => array(
          'name' => 'Date',
          'type' => 'date'
        ),
        'timestamp' => array(
          'name' => 'Uploaded',
          'type' => 'date'
        ),
        'status' => array(
          'name' => 'Status',
          'type' => 'data_status'
        ),
        'inspected' => array(
          'name' => 'Inspected',
          'type' => 'bool',
          'cast' => 'bool',
          'sql'  => "select id from ldf_verification where ldf_verification.barcode_id = :table.barcode_id"
        ),
        'is_verified' => array(
          'name' => 'Verified',
          'type' => 'bool',
          'cast' => 'bool',
          'sql'  => "select id from ldf_verification where ldf_verification.barcode_id = :table.barcode_id and ldf_verification.status = 'A'"
        ),
        'is_checked' => array(
          'name' => 'Checked',
          'type' => 'bool',
          'cast' => 'bool',
          'sql'  => "status <> 'P'"
        ),
        'has_passed' => array(
          'name' => 'Passed',
          'type' => 'bool',
          'cast' => 'bool',
          'sql'  => "status = 'A'"
        ),
        'has_failed' => array(
          'name' => 'Failed',
          'type' => 'bool',
          'cast' => 'bool',
          'sql'  => "status = 'R'"
        )
      ),
      'models' => array(
        'operator' => array(
          'type' => 'many_to_one',
          'local_field'   => 'operator_id',
          'foreign_field' => 'id'
        ),
        'site' => array(
          'type' => 'many_to_one',
          'local_field'   => 'site_id',
          'foreign_field' => 'id'
        ),
        'species' => array(
          'type' => 'many_to_one',
          'local_field'   => 'species_id',
          'foreign_field' => 'id'
        ),
        'barcode' => array(
          'type' => 'one_to_one',
          'local_field'   => 'barcode_id',
          'foreign_field' => 'id'
        ),
        'tdf' => array(
          'type' => 'one_to_one',
          'local_field'   => 'tree_barcode_id',
          'foreign_field' => 'barcode_id'
        ),
        'specs' => array(
          'type' => 'one_to_one',
          'local_field'   => 'barcode_id',
          'foreign_field' => 'barcode_id'
        ),
      )
    ),
    'specs' => array(
      'name'   => 'Shipment Specification',
      'table'  => 'specs_data',
      'fields' => array(
        'diameter' => array(
          'name' => 'Diameter',
          'aggregates' => TRUE
        ),
        'length' => array(
          'name' => 'Length',
          'aggregates' => TRUE
        ),
        'volume' => array(
          'name' => 'Volume',
          'aggregates' => TRUE
        ),
        'create_date' => array(
          'name' => 'Date',
          'type' => 'date'
        ),
        'timestamp' => array(
          'name' => 'Uploaded',
          'type' => 'date'
        ),
        'status' => array(
          'name' => 'Status',
          'type' => 'data_status'
        ),
        'inspected' => array(
          'name' => 'Inpsected',
          'type' => 'bool',
          'cast' => 'bool',
          'sql'  => "select id from ldf_verification where ldf_verification.barcode_id = :table.barcode_id"
        ),
        'is_verified' => array(
          'name' => 'Verified',
          'type' => 'bool',
          'cast' => 'bool',
          'sql'  => "select id from ldf_verification where ldf_verification.barcode_id = :table.barcode_id and ldf_verification.status = 'A'"
        ),
        'is_checked' => array(
          'name' => 'Checked',
          'type' => 'bool',
          'cast' => 'bool',
          'sql'  => "status <> 'P'"
        ),
        'has_passed' => array(
          'name' => 'Passed',
          'type' => 'bool',
          'cast' => 'bool',
          'sql'  => "status = 'A'"
        ),
        'has_failed' => array(
          'name' => 'Failed',
          'type' => 'bool',
          'cast' => 'bool',
          'sql'  => "status = 'R'"
        )
      ),
      'models' => array(
        'operator' => array(
          'type' => 'many_to_one',
          'local_field'   => 'operator_id',
          'foreign_field' => 'id'
        ),
        'species' => array(
          'type' => 'many_to_one',
          'local_field'   => 'species_id',
          'foreign_field' => 'id'
        ),
        'barcode' => array(
          'type' => 'one_to_one',
          'local_field'   => 'barcode_id',
          'foreign_field' => 'id'
        ),
        'ldf' => array(
          'type' => 'one_to_one',
          'local_field'   => 'barcode_id',
          'foreign_field' => 'barcode_id'
        ),
      )
    ),
    'barcode' => array(
      'name'   => 'Barcode',
      'table'  => 'barcodes',
      'fields' => array(
        'barcode' => array('name' => 'Barcode'),
        'type' => array('name' => 'Barcode Type'),
        'status'  => array(
          'name'  => 'Status',
          'type'  => 'barcode_activity',
          'array' => TRUE,
          'sql'   => "select array_agg(activity) from barcode_activity where barcode_activity.barcode_id = :table.id"
        ),
        'is_locked' => array(
          'name' => 'Locked',
          'type' => 'bool',
          'cast' => 'bool',
          'sql'  => "select id from barcode_locks where barcode_locks.barcode_id = :table.id"
        ),
        'is_in_progress' => array(
          'name' => 'In Progress',
          'type' => 'bool',
          'cast' => 'bool',
          'sql'  => "select id from barcode_activity where barcode_activity.barcode_id = :table.id and barcode_activity.activity = 'I'"
        ),
        'is_on_hold' => array(
          'name' => 'On Hold / Pending Investigation',
          'type' => 'bool',
          'cast' => 'bool',
          'sql'  => "select id from barcode_activity where barcode_activity.barcode_id = :table.id and barcode_activity.activity = 'H'"
        ),
        'is_st_invoiced' => array(
          'name' => 'Stumpage Fee Invoiced',
          'type' => 'bool',
          'cast' => 'bool',
          'sql'  => "select id from barcode_activity where barcode_activity.barcode_id = :table.id and barcode_activity.activity = 'T'"
        ),
        'is_exf_invoiced' => array(
          'name' => 'Export Fee Invoiced',
          'type' => 'bool',
          'cast' => 'bool',
          'sql'  => "select id from barcode_activity where barcode_activity.barcode_id = :table.id and barcode_activity.activity = 'X'"
        ),
        'is_declared_for_export' => array(
          'name' => 'Declared for Export',
          'type' => 'bool',
          'cast' => 'bool',
          'sql'  => "select id from barcode_activity where barcode_activity.barcode_id = :table.id and barcode_activity.activity = 'D'"
        ),
        'is_exported' => array(
          'name' => 'Exported',
          'type' => 'bool',
          'cast' => 'bool',
          'sql'  => "select id from barcode_activity where barcode_activity.barcode_id = :table.id and barcode_activity.activity = 'E'"
        ),
        'is_loaded_on_vessel' => array(
          'name' => 'Loaded on Vessel',
          'type' => 'bool',
          'cast' => 'bool',
          'sql'  => "select id from barcode_activity where barcode_activity.barcode_id = :table.id and barcode_activity.activity = 'O'"
        ),
        'is_short_shipped' => array(
          'name' => 'Short Shipped',
          'type' => 'bool',
          'cast' => 'bool',
          'sql'  => "select id from barcode_activity where barcode_activity.barcode_id = :table.id and barcode_activity.activity = 'S'"
        ),
        'is_sold_locally' => array(
          'name' => 'Sold Locally',
          'type' => 'bool',
          'cast' => 'bool',
          'sql'  => "select id from barcode_activity where barcode_activity.barcode_id = :table.id and barcode_activity.activity = 'Y'"
        ),
        'is_abandoned' => array(
          'name' => 'Abandoned',
          'type' => 'bool',
          'cast' => 'bool',
          'sql'  => "select id from barcode_activity where barcode_activity.barcode_id = :table.id and barcode_activity.activity = 'A'"
        ),
        'is_lost' => array(
          'name' => 'Lost',
          'type' => 'bool',
          'cast' => 'bool',
          'sql'  => "select id from barcode_activity where barcode_activity.barcode_id = :table.id and barcode_activity.activity = 'L'"
        ),
        'is_seized' => array(
          'name' => 'Seized',
          'type' => 'bool',
          'cast' => 'bool',
          'sql'  => "select id from barcode_activity where barcode_activity.barcode_id = :table.id and barcode_activity.activity = 'Z'"
        ),
      )
    ),
    'species' => array(
      'name'   => 'Species',
      'table'  => 'species',
      'fields' => array(
        'code' => array('name' => 'Species Code'),
        'class' => array('name' => 'Species Class'),
        'botanic_name' => array('name' => 'Botanic Name'),
        'trade_name' => array('name' => 'Trade Name'),
      ),
      'models' => array(
        'ssf' => array(
          'type' => 'one_to_many',
          'local_field'   => 'id',
          'foreign_field' => 'species_id',
        ),
        'tdf' => array(
          'type' => 'one_to_many',
          'local_field'   => 'id',
          'foreign_field' => 'species_id'
        ),
        'ldf' => array(
          'type' => 'one_to_many',
          'local_field'   => 'id',
          'foreign_field' => 'species_id'
        ),
      )
    ),
  );

  public static $aggregates = array(
    'list'   => 'LIST',
    'unique' => 'UNIQUE',
    'sum'    => 'SUM',
    'count'  => 'COUNT',
    'min'    => 'MINIMUM',
    'max'    => 'MAXIMUM',
    'avg'    => 'AVERAGE',
  );

  public static $filters = array(
    'equals'       => 'EQUALS',
    'not_equals'   => 'DOES NOT EQUAL',
    'contains'     => 'CONTAINS',
    'not_contains' => 'DOES NOT CONTAIN',
    'begins'       => 'BEGINS WITH',
    'ends'         => 'ENDS WITH',
    'between'      => 'IS BETWEEN',
    'greater_than' => 'IS GREATER THAN',
    'less_than'    => 'IS LESS THAN',
    'true'         => 'TRUE',
    'false'        => 'FALSE',
    'null'         => 'EMPTY',
    'not_null'     => 'NOT EMPTY',
  );

  public static function field_values($model, $field, $field_model, $default = FALSE) {
    if ($model == $field_model) {
      $type = 'default';
      if ($default) return 'value';
      else $array = array('value');
    } else {
      $type  = self::$models[$model]['models'][$field_model]['type'];
      $array = array();
    }

    switch ($type) {
      case 'many_to_one':
      case 'one_to_one':
        return $default ? 'value' : array('value');

      case 'many_to_many':
      case 'one_to_many':
      default:
        $aggregates = self::$models[$field_model]['fields'][$field]['aggregates'];
        if ($aggregates and is_array($aggregates)) return $default ? reset($aggregates) : array_merge($aggregates, $array);
        else if ($aggregates) return $default ? reset(array_keys(self::$aggregates)) : array_merge(array_keys(self::$aggregates), $array);
        else return $default ? 'list' : array_merge(array('count', 'list', 'unique'), $array);
    }
  }

  public static function field_query($model, $field, $filters = array()) {
    $field = array_merge(self::$models[$field['model']]['fields'][$field['field']], $field);
    $sql = $field['sql'] ? '('.str_replace(':table', self::$models[$field['model']]['table'], $field['sql']).')' : $field['field'];
    switch ($field['value']) {
      case 'list':
        $_field = DB::expr("array_to_string(array_agg($sql::text), ',')"); break;

      case 'unique':
        $_field = DB::expr("array_to_string(array_agg(distinct $sql::text), ',')"); break;

      case 'count':
      case 'sum':
      case 'min':
      case 'max':
      case 'avg':
        $_field = DB::expr("{$field['value']}($sql)"); break;

      case 'value':
      default:
        $_field = $sql; break;
    }

    if ($field['cast']) $_field = "($field)::{$field['cast']}";
    if ($model == $field['model']) return $_field;

    $query = DB::select($_field)
      ->from(self::$models[$field['model']]['table'])
      ->where("model.".self::$models[$model]['models'][$field['model']]['local_field'], '=', DB::expr('"'.self::$models[$field['model']]['table'].'"."'.self::$models[$model]['models'][$field['model']]['foreign_field'].'"'));

    foreach ($filters as $filter)
      if (($filter['model'] == $field['model']) and
          ($filter['field'] == $field['field'])) switch ($filter['filter']) {

        case 'equals':
          $query->where(self::$models[$field['model']]['table'].".".$filter['field'], 'IN', (array) $filter['values']); break;

        case 'not_equals':
          $query->where(self::$models[$field['model']]['table'].".".$filter['field'], 'NOT IN', (array) $filter['values']); break;

        case 'contains':
          $query->where(self::$models[$field['model']]['table'].".".$filter['field'], 'ILIKE', "%".implode(',', (array) $filter['values'])."%"); break;

        case 'not_contains':
          $query->where(self::$models[$field['model']]['table'].".".$filter['field'], 'NOT ILIKE', "%".implode(',', (array) $filter['values'])."%"); break;

        case 'begins':
          $query->where(self::$models[$field['model']]['table'].".".$filter['field'], 'ILIKE', implode(',', (array) $filter['values'])."%"); break;

        case 'ends':
          $query->where(self::$models[$field['model']]['table'].".".$filter['field'], 'ILIKE', "%".implode(',', (array) $filter['values'])); break;

        case 'between':
          $query->where(self::$models[$field['model']]['table'].".".$filter['field'], 'BETWEEN', (array) $filter['values']); break;

        case 'greater_than':
          $query->where(self::$models[$field['model']]['table'].".".$filter['field'], '>', reset($filter['values'])); break;

        case 'less_than':
          $query->where(self::$models[$field['model']]['table'].".".$filter['field'], '<', reset($filter['values'])); break;

        case 'true':
          $query->where(self::$models[$field['model']]['table'].".".$filter['field'], '=', TRUE); break;

        case 'false':
          $query->where(self::$models[$field['model']]['table'].".".$filter['field'], '=', FALSE); break;

        case 'null':
          $query->where(self::$models[$field['model']]['table'].".".$filter['field'], 'IS', NULL); break;

        case 'not_null':
          $query->where(self::$models[$field['model']]['table'].".".$filter['field'], 'IS NOT', NULL); break;
      }

    return $query;
  }

  public static function create_report_number($type) {
    return DB::query(Database::SELECT, "SELECT nextval('s_reports_{$type}_number') number")
      ->execute()
      ->get('number');
  }

  public function set($column, $value) {
    switch ($column) {
      case 'tables':
      case 'fields':
      case 'filters':
      case 'order':
        if (is_array($value)) {
          // prepare for db
          $_value = $value;
          sort($_value);
          ksort($_value);
          $value = serialize($value);
        }
        else if (!is_string($value)) $value = NULL;
      default:
        parent::set($column, $value);
    }
  }

  public function __get($column) {
    switch ($column) {
      case 'number':
        $value = parent::__get($column);
        return $value ? SGS::numberify($value) : NULL;

      case 'tables':
      case 'fields':
      case 'filters':
      case 'order':
        $value = parent::__get($column);
        return is_string($value) ? unserialize($value) : $value;

      case 'is_draft':
        return parent::__get($column) == 't' ? TRUE : FALSE;

      default:
        return parent::__get($column);
    }
  }

  public function delete() {
    $this->unset_data();
    parent::delete();
  }

  public function query() {
    $field_array = array();
    foreach ($fields as $field) {
      $field_array[] = array($report::field_query($model, $field, $filters), $field['name']);
    }

    $query = DB::select_array($field_array)
      ->from(array($report::$models[$model]['table'], 'model'));

    foreach ($filters as $filter) if ($filter['model'] == $model) switch ($filter['filter']) {
      case 'equals':
        $query->where("model.".$filter['field'], 'IN', (array) $filter['values']); break;

      case 'not_equals':
        $query->where("model.".$filter['field'], 'NOT IN', (array) $filter['values']); break;

      case 'contains':
        $query->where("model.".$filter['field'], 'ILIKE', "%".$filter['values']."%"); break;

      case 'not_contains':
        $query->where("model.".$filter['field'], 'NOT ILIKE', "%".$filter['values']."%"); break;

      case 'begins':
        $query->where("model.".$filter['field'], 'ILIKE', $filter['values']."%"); break;

      case 'ends':
        $query->where("model.".$filter['field'], 'ILIKE', "%".$filter['values']); break;

      case 'between':
        $query->where("model.".$filter['field'], 'BETWEEN', (array) $filter['values']); break;

      case 'greater_than':
        $query->where("model.".$filter['field'], '>', reset($filter['values'])); break;

      case 'less_than':
        $query->where("model.".$filter['field'], '<', reset($filter['values'])); break;
    }

    if ($order) $query->order_by($order['sort'], $order['direction'] ?: NULL);
    if ($limit) $query->limit($limit);
  }
}
