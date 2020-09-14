<?php

class MyModel {
  const PK = 'id';
  protected $id;
  protected $tableName;
  protected $dbh; // PDO
  protected $sth; // PDOStatement
  protected $errorMessage;
  protected $result;
  protected $updateAtControl = true;
  protected $updateAtField = 'updated_at';
  protected $updateAtFormat = 'Y-m-d H:i:s';

  function __construct() {
  }

  function destroy($options=null) {
    $id = $this->id;
    $table = $this->tableName;

    $params = null;

    if (empty($options)) {
      $options = [self::PK => $id];
    }

    $where = self::parseWhere($options, $params);
    $sql = "
      DELETE FROM $table
      $where
    ";

    $destroy = $this->setup($params, $sql);
    if (!$destroy) {
      return false;
    }
    return true;
  }

  function parseUpdateWhere($values, &$params=null) {
    $result = '';
    $where = '';

    if (!isset($params)) {
      $params = self::parseParams($values);
    }
    
    $pk = self::PK;
    $id = is_array($params) 
      && 
        (array_key_exists($pk, $params) 
        ? $params[$pk] 
        : false
      );
    if (!$id && $this->id) {
      $id = $this->id;
      $params[$pk] = $this->id;
    }
    if ($id) {
      $where = "$pk = :$pk";
    }

    if ($where) {
      $result = "
        WHERE
          $where
        ";
    }
    return $result;
  } 

  function parseDeleteWhere($values, $options) {
    return $this->parseUpdateWhere($values, $options);
  }
  
  function update($values, $options=null) {
    $table = $this->tableName;
    
    $data = $values;
    if ($this->updateAtControl && !array_key_exists($this->updateAtField, $data)) {
      $data[$this->updateAtField] = date($this->updateAtFormat);
    }
    $updateFields = self::parseUpdateFields($data);
    $params = self::parseParams($data);

    $where = $this->parseUpdateWhere($values, $params);

    $sql = "
      UPDATE $table 
      SET
        $updateFields
      $where
    ";

    $update = $this->setup($params, $sql)->result;
    if (!$update) {
      return false;
    }

    if ($this->id) {
      $result = $this->findByPk($this->id, $options);
    }
    else {
      // ATENÇÃO: TESTAR!
      $result = $this->findAll($options);
    }
    if (is_array($result)) {
      unset($result['hash']);
    }

    return $result;
  }

  static function parseUpdateFields($data) {
    $fields = array_keys($data);
    $elements = [];
    foreach ($fields as $field) {
      $elements[] = "$field = :$field";
    }
    $result = implode(',', $elements);
    return $result;
  }

  function create($values, $options=null) {
    $table = $this->tableName;

    $fields = self::parseFieldsFromValues($values);
    $dataValues = self::parseDataValues($values);
    $params = self::parseParams($values);

    $sql = "
      INSERT INTO $table
      ($fields)
      VALUES
      ($dataValues)
    ";

    $result = $this->setup($params, $sql)->result;
    return $result;
  }

  static function parseDataValues($values) {
    $data = [];
    foreach (array_keys($values) as $element) {
      $data[] = ":$element"; 
    }
   return implode(",", $data); 
  }

  static function parseFieldsFromValues($values) {
    return implode(",", array_keys($values)); 
  }

  static function parseAttributes($options) {
    $select = "*";
    $fields = [];
    if (is_array($options)) {
      if (isset($options['attributes'])) {
        if (is_array($options['attributes'])) {
          $fields = $options['attributes'];
          $select = implode(",", $options['attributes']);
        }
        elseif (is_string($options['attributes'])) {
          $fields = explode(",", $options['attributes']);

          array_walk($fields, function(&$el) {
            $el = trim($el);
          });

          $select = $options['attributes'];
        }
      }
    }
    $result = [];
    $result['fields'] = $fields;
    $result['select'] = $select;
    return $result;
  }

  function findOne($options) {
    $parse = $this::parseOptions($options);
    extract($parse);

    $tableName = $this->tableName;
    $sql = "
      SELECT $select
      FROM $tableName
      $where
      LIMIT 1
    ";

    $fetch = $this->fetch($params, $sql);
    $result = $fetch ? $fetch : null;

    if (is_array($fetch) && array_key_exists(self::PK, $fetch)) {
      $this->id = $fetch[self::PK];
    }

    return $result;
  }
  
  static function parseData($options) {
    $data = $options;
    if (is_string($options)) {
      $pre = explode(",", $options);
      $data = [];
      foreach($pre as $element) {
        $def = explode("=", $element);
        if (!isset($def[0]) 
        || !isset($def[1])
        ) {
          $data = null;
        break;
        }
        $field = trim($def[0]);
        $value = trim(
          //isset($def[1]) 
          //? 
          $def[1] 
          //: ''
        );
        $data[$field] = $value;
      }
    }
    
    if (!is_array($data)) {
      trigger_error('Invalid $options parameter in MyModel->findOne()');
    }
    return $data;
  }
  
  static function parseSelect($options) {
    $attributes = self::parseAttributes($options);
    return $attributes['select'];
  }
  
  static function parseFieldsFromOptions($options) {
    $attributes = self::parseAttributes($options);
    return $attributes['fields'];
  }
  
  static function parseWhere($options, &$params=null) {

    function array_in_array($array) {
      if (!is_array($array)) {
        return null;
      }
      foreach ($array as $element) {
        if (is_array($element)) {
          return true;
        }
      }
      return false;
    }

    function _parseWhere($w, &$params=null, $op='and') {
      $result = "";
      if (is_array($w)) {
        $conds = [];

        $ops = [
          'and' => "AND",
          'or' => "OR",
        ];

        $opComp = '=';
        $opsComp = [
          'eq' => '=',
          'ne' => '!=',
        ];
        foreach ($w as $index => $element) {
          if (array_key_exists($index, $ops)) {
            $conds[] = "("._parseWhere($element, $params, $index).")";
          }
          else {
            $opSub = !array_key_exists($index, $opsComp) ? $opComp : $opsComp[$index];
            if (is_array($element)) {
              if (array_in_array($element)) {
                $conds[] = "("._parseWhere($element, $params, $index).")";
              }
              else {
                foreach ($element as $field=>$value) {
                  $comp = "$field $opSub ?";
                  $params[] = $value;
                  $conds[] = $comp;
                }
              }
            }
            else {
              $comp = "$index $opSub ?";
              $params[] = $element;
              $conds[] = $comp;
            }
          } 
        }
        $opResult = isset($ops[$op]) ? $ops[$op] : 'AND';
        $result = implode(" $opResult ", $conds);
      }
      elseif (is_string($w)) {
        $result = $w;
      }
      return $result;
    } 

    $data = self::parseData($options);
    
    $opKey = 'and';
    $opValue = 'AND';

    $where = null;
    $params = [];
    
    if (is_array($options)) {
      $ops = [
        'where' => 'and',
        'and' => 'and',
        'or' => 'or'
      ];
      foreach ($ops as $opIndex => $opElement) {
        if (array_key_exists($opIndex, $options)) {
          $w = $options[$opIndex];
          $where = _parseWhere($w, $params, $opElement);
          break;
        }
      }
    }

    if ($where===null) {
      $fields = array_keys($data);
      if (!in_array("attributes", $fields)) {
        $values = array_values($data);
      
        $we = [];
        $params = [];
        foreach ($fields as $index => $element) {
          $we[] = "$element = :$element";
          $params[":$element"] = $values[$index];
        }
        $where = implode(" $opValue ", $we);
      }
    }

    $result = $where ? "WHERE $where" : "";
    return $result;
  }
  
  static function parseParams($values) {
    $result = [];

    $keys = array_keys($values);
    
    foreach ($keys as $key) {
      $result[":$key"] = $values[$key];
    }
    return $result;
  }
  
  static function parseOptions($options) {
    $attributes = self::parseAttributes($options);
    extract($attributes);

    $params = [];
    $where = self::parseWhere($options, $params);

    $parse = [
      'fields' => $fields,
      'select' => $select,
      'where' => $where,
      'params' => $params
    ];
    return $parse;
  }

  function findAll($options=null) {
    $select = self::parseSelect($options);
    $table = $this->tableName;
    $params = null;
    $where = self::parseWhere($options, $params);

    $sql = "
      SELECT $select
      FROM $table
      $where
    ";
    
    $data = $this->fetchAll($params, $sql);
    return $data;
  }

  function findByPk($param, $options=null) {
    $id = $param;
    $this->id = $id;

    $select = "*";
    $table = $this->tableName;
    $pk = self::PK;

    $sql = "
      SELECT $select
      FROM $table
      WHERE $pk = :id
    ";
    
    $params = array(
      ':id' => $id
    );

    $data = $this->fetch($params, $sql);
    return $data;
  }

  function errorMessage() {
    return $this->errorMessage;
  }

  function on_mysql() {
    return $this->driverName() == 'mysql';
  }

  function on_pgsql() {
    return $this->driverName() == 'pgsql';
  }
  
  function on_firebird() {
    return $this->driverName() == 'firebird';
  }
  
  function on_sqlite() {
    return $this->driverName() == 'sqlite';
  }

  function driverName() {
    return $this->dbh->getAttribute(PDO::ATTR_DRIVER_NAME);
  }

  function fetchAll($params=null, $sql=null) {
    $this->setup($params, $sql);
    $value = $this->sth->fetchAll();
    return $value;
  }

  function fetch($params=null, $sql=null) {
    $this->setup($params, $sql);
    $value = $this->sth->fetch();
    return $value;
  }

  function setup($params=null, $sql=null) {
    if (!$this->dbh) {
      $this->connect();
    }
    if (!$this->dbh) {
      return false;
    }
    if ($sql || !$this->sth) {
      $this->prepare($sql);
    }
    $this->execute($params);
    return $this;
  }

  function execute($params) {
    $result = false;
    try {
      ob_start();
      if ($this->sth) {
        $result = $this->sth->execute($params);
      }
      $ob = strip_tags(ob_get_clean());
    }
    catch (Exception $e) {
      $this->errorMessage = "output: $ob".' | $params: '.json_encode($params)." | ".$e->getMessage();
    }
    $this->result = $result;
    return $this;
  }

  function prepare($sql) {
    $this->sth = $this->dbh->prepare($sql, array(
      PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY
    ));
    //$this->sth = $this->dbh->prepare($sql);

    $this->sth->setFetchMode(PDO::FETCH_ASSOC);

    return $this;
  }

  function connect() {
    require dirname(__FILE__)."/.config.php";
    $dbdriver = DB_DRIVER; 
    $dbhost = DB_HOST;
    $dbname = DB_NAME;

    switch ($dbdriver) {
      default:
        $dsn = "mysql:host=$dbhost;dbname=$dbname";

        $options = array(
          PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
        );
        
        try {
          $this->dbh = new PDO($dsn, DB_USER, DB_PASS, $options);
        }
        catch (Exception $e) {
          //$this->errorInfo = $e->errorInfo();
          //$this->errorInfoPdoCode = $e->errorInfo()[0];
          //$this->errorInfoDriverCode = $e->errorInfo()[1];
          //$this->errorInfoMessage = $e->errorInfo()[2];
          $this->errorMessage = $e->getMessage();
        }
      break;
    }
    return $this;
  }
}