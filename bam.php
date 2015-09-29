<?php

$my_path = dirname(__FILE__);

class Schema
{
  public $name;
  public $enums = array();
  public $tables = array();
}

class Table
{
  public $name;
  public $fields = array();
  public $primary_key;
  public $indices = array();
}

class Field
{
  public $name;
  public $type;
  public $default;
  public $accessibility;
  public $constraints = array();
  public $visibility = array();
}

class Constraint
{
  public $type;
  public $condition;
  public $negate;
}

class Visibility
{
  public $default;
  public $groups; # array(group=>, visible=>)
}

class Permission
{
  public $create;
  public $read;
  public $update;
  public $delete;
}

class Match
{
  public $user_type;
  public $user_field;
  public $model_field;
  public $op;
}

class Permissions
{
  public $default;
  public $groups; # array(group=>, permission=>)
  public $match; # array(match=>, permission=>)
}

class Index
{
  public $name;
  public $type;
  public $unique;
  public $fields = array();
}

# I'm sure this is terrible for a variety of reasons, but it
# makes my life _so_ much easier right now.  Eventually this should
# be removed
$xml = simplexml_load_file($argv[1]);
if ($xml === FALSE) {
  throw new Exception("Error processing XML: " . var_export(libxml_get_errors (), true));
}

function sxml_map($fcn, $sxml)
{
  $a = array();
  foreach($sxml as $e)
  {
    $tmp = $fcn($e);
    if (is_object($tmp) && property_exists($tmp, 'name'))
    {
      $a[$tmp->name] = $tmp;
    }
    else
    {
      $a[] = $tmp;
    }
  }
  return $a;
}

$stringify = function($e) { return (string) $e; };

function sxml_hash_extract($sxml)
{
  $a = array();
  foreach($sxml as $k=>$e)
  {
    $a[$k] = (string)$e;
  }
  return $a;
}

function get_or_null($f, $a)
{
  if (array_key_exists($f, $a))
  {
    return $a[$f];
  }
  return NULL;
}

$process_field = function($field) use ($stringify)
{
  $attr = sxml_hash_extract($field->attributes());

  $f = new Field;
  $f->name = $attr['name'];
  $f->type = $field->getName();
  $f->default = get_or_null('default', $attr);
  $f->accessibility = $attr['accessibility'];

  $f->update_current_timestamp = string_to_bool(get_or_null('update_current_timestamp', $attr));
  $f->default_current_timestamp = string_to_bool(get_or_null('default_current_timestamp', $attr));

  $klass = 'BAM_' . ucwords($f->type);
  if (class_exists($klass))
  {
    $a = new $klass;
    if (method_exists($a, 'reduce'))
    {
      $a->reduce($f, $attr);
    }
  }
  return $f;
};


$lookup = function($t) { return function($k) use ($t) { return $t[$k]; }; };

$pull = function($f) { return function($t) use ($f) { return $t->$f; }; };

function string_to_bool($s) { return in_array(strtolower($s), array('true', 't')); }

$process_index = function($table, $suffix = 'idx') use ($stringify, $lookup, $pull)
{
  return function($index) use ($stringify, $table, $lookup, $pull, $suffix)
  {
    $attr = $index->attributes();

    $i = new Index;

    $i->unique = string_to_bool((string) $attr['unique']);
    $i->type = (string) $attr['type'];
    $i->fields = array_map(
                   $lookup($table->fields),
                   sxml_map($stringify, $index->xpath('dimension'))
                 );

    $i->name = $table->name . 
               '_' . 
               join('_', array_map($pull('name'), $i->fields)) . 
               '_' .
               $suffix;

    return $i;
  };
};

$process_table = function($table) use ($process_field, $process_index)
{
  $attr = $table->attributes();

  $t = new Table;
  $t->name = (string) $attr['name'];
  $t->fields = sxml_map($process_field, $table->fields->children());

  $t->primary_key = sxml_map($process_index($t, 'pk'), $table->xpath('indices/primary'));
  $t->primary_key = reset($t->primary_key);

  $t->indices = sxml_map($process_index($t), $table->xpath('indices/index'));

  return $t;
};

$process_schema = function($schema) use ($process_table)
{
  $attr = $schema->attributes();

  $s = new Schema;
  $s->name = (string) $attr['name'];
  $s->tables = sxml_map($process_table, $schema->xpath('table'));

  return $s;
};



var_dump(sxml_map($process_schema, $xml->xpath('schema')));
