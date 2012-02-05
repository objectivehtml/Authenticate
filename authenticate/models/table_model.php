<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Table Model
 */
class Table_model extends CI_Model
{
	/**
	 * @var array A list of field names that need to be backticked if used
	 */
	public $reserved_columns = array(
		'key',
	);
	
	public function __construct()
	{
		parent::__construct();
		
		$this->load->dbforge();
	}
	
	/**
	 * Update Tables
	 * 
	 * Updates tables based on an array of tables => fields => attributes
	 *
	 * array(
	 * 	table_name => array(
	 * 		field_name => array(
	 * 			attribute => value,
	 * 			attribute => value,
	 * 		),
	 * 		field_name => array( etc...
	 * 	),
	 * 	table_name => array( etc...
	 * 
	 * @param array $tables
	 * 
	 * @return void
	 */
	public function update_tables(array $tables)
	{
		foreach ($tables as $table_name => $fields)
		{
			$this->update_table($table_name, $fields);
		}
	}
	
	public function update_table($table_name, $fields)
	{
		//create the table if it doesn't exist yet
		if ( ! $this->db->table_exists($table_name))
		{
			$primary_key = NULL;
			
			//check for our custom key and primary_key attributes
			foreach ($fields as $field => $attributes)
			{
				if ( ! empty($attributes['primary_key']))
				{
					$primary_key = $field;
				}
				
				if ( ! empty($attributes['key']))
				{
					$this->dbforge->add_key($field);
				}
			}
			
			$this->dbforge->add_field($fields);
			
			if ( ! is_null($primary_key))
			{
				$this->dbforge->add_key($primary_key, TRUE);
			}
			
			$this->dbforge->create_table($table_name, TRUE);
		}
		else 
		{
			//get all the existing fields for this table
			$existing_fields = $this->table_to_array($table_name);
			
			//drop any existing fields that aren't in the table array
			foreach ($existing_fields as $field_name => $attributes)
			{	
				if ( ! isset($fields[$field_name]))
				{
					$this->dbforge->drop_column($table_name, $field_name);
				}
			}
			
			//traverse through table array's fields
			foreach ($fields as $field_name => $attributes)
			{
				//add the field if it doesn't already exist
				if ( ! isset($existing_fields[$field_name]))
				{
					$this->dbforge->add_column($table_name, array($field_name => $attributes));
				}
				//or modify the field if necessary
				else 
				{
					//only modify the column when this->table and existing table array (culled from table_to_array()) don't match
					if (count(array_diff($attributes, $existing_fields[$field_name])) !== 0)
					{
						$attributes['name'] = $field_name;
						
						$this->dbforge->modify_column($table_name, array($field_name => $attributes));
					}
				}
			}
		}
	}
	
	/**
	 * Table to array
	 *
	 * Creates an array suitable for use with dbforge
	 * 
	 * @param string $table_name
	 * 
	 * @return array
	 */
	public function table_to_array($table_name)
	{
		$query = $this->db->query("SHOW COLUMNS FROM `{$this->db->dbprefix}$table_name`");
		
		$fields = array();
		
		foreach ($query->result() as $row)
		{
			$field = array();
			
			if (preg_match('/^(.*) unsigned$/', $row->Type, $match))
			{
				$field['unsigned'] = TRUE;
				
				$row->Type = $match[1];
			}
			
			if (preg_match('/^(.*)\((\d+)\)$/', $row->Type, $match))
			{
				$field['constraint'] = (int) $match[2];
				
				$row->Type = $match[1];
			}
			
			if ($row->Null === 'YES')
			{
				$field['null'] = TRUE;
			}
			
			if (strpos($row->Extra, 'auto_increment') !== FALSE)
			{
				$field['auto_increment'] = TRUE;
			}
			
			if ($row->Key)
			{
				if ($row->Key === 'PRI')
				{
					$field['primary_key'] = TRUE;
				}
				else
				{
					$field['key'] = TRUE;
				}
			}
			
			if ( ! is_null($row->Default))
			{
				$field['default'] = $row->Default;
			}
			
			$field['type'] = $row->Type;
			
			$name = (in_array($row->Field, $this->reserved_columns)) ? "`{$row->Field}`" : $row->Field;
			
			$fields[$name] = $field;
		}
		
		return $fields;
	}
	
	public function tables_to_array(array $tables)
	{
		$tables_array = array();
		
		foreach ($tables as $table_name)
		{
			$tables_array[$table_name] = $this->table_to_array($table_name);
		}
		
		return $tables_array;
	}
}
