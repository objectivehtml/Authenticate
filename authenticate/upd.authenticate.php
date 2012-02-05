<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Authenticate
 * 
 * @package		Authenticate
 * @author		Justin Kimbrell
 * @copyright	Copyright (c) 2012, Justin Kimbrell
 * @link 		http://www.objectivehtml.com/authenticate
 * @version		1.0
 * @build		20120204
 */
 
class Authenticate_upd {

    public $version = '1.0';
	public $mod_name;
	public $ext_name;
	public $mcp_name;
	
	private $tables = array(
		/*
		'auto_email_settings'	=> array(
			'key'	=> array(
				'type'				=> 'varchar',
				'constraint'		=> 100,
				'primary_key'		=> TRUE
			),
			'value'	=> array(
				'type'			=> 'text'
			)
		),
		'auto_email_channels' 	=> array(
			'id'	=> array(
				'type'				=> 'int',
				'constraint'		=> 100,
				'primary_key'		=> TRUE,
				'auto_increment'	=> TRUE
			),
			'channel_id' => array(
				'type'			=> 'int',
				'constraint' 	=> 100
			),
			'categories' => array(
				'type'	=> 'TEXT'
			),
			'member_groups' => array(
				'type'	=> 'TEXT'
			),
			'statuses' => array(
				'type'	=> 'TEXT'
			),
			'message'	=> array(
				'type'	=> 'LONGTEXT'
			),
			'email_settings' => array(
				'type'	=> 'LONGTEXT'
			)
		)
		*/
	);
	
	private $actions = array(
		/*
		array(
		    'class'     => 'Auto_email_mcp',
		    'method'    => 'save_settings'
		)
		*/
	);
	
	private $hooks = array(
		array('member_member_logout', 'member_member_logout')
	);
	
    public function __construct()
    {
        // Make a local reference to the ExpressionEngine super object
        $this->EE =& get_instance();
        
        $this->mod_name 	= str_replace('_upd', '', __CLASS__);
        $this->ext_name		= $this->mod_name . '_ext';
        $this->mcp_name		= $this->mod_name . '_mcp';
    }
	
	public function install()
	{	
		$this->EE->load->dbforge();
		
		//create tables from $this->tables array
		$this->EE->load->model('table_model');
		
		$this->EE->table_model->update_tables($this->tables);
		
		$data = array(
	        'module_name' 		 => $this->mod_name,
	        'module_version' 	 => $this->version,
	        'has_cp_backend' 	 => 'n',
	        'has_publish_fields' => 'n'
	    );
	    	
	    $this->EE->db->insert('modules', $data);
	    	    	    
		foreach ($this->hooks as $row)
		{
			$this->EE->db->insert(
				'extensions',
				array(
					'class' 	=> $this->ext_name,
					'method' 	=> $row[0],
					'hook' 		=> ( ! isset($row[1])) ? $row[0] : $row[1],
					'settings' 	=> ( ! isset($row[2])) ? '' : $row[2],
					'priority' 	=> ( ! isset($row[3])) ? 10 : $row[3],
					'version' 	=> $this->version,
					'enabled' 	=> 'y',
				)
			);
		}
		
		foreach($this->actions as $action)
			$this->EE->db->insert('actions', $action);
		
		$this->_set_defaults();
				
		return TRUE;
	}
	
	public function update($current = '')
	{
	    return TRUE;
	}
	
	public function uninstall()
	{
		$this->EE->load->dbforge();
		
		$this->EE->db->delete('modules', array('module_name' => $this->mod_name));
		$this->EE->db->delete('extensions', array('class' => $this->ext_name));		
		$this->EE->db->delete('actions', array('class' => $this->mod_name));
		
		$this->EE->db->delete('actions', array('class' => $this->mod_name));
		$this->EE->db->delete('actions', array('class' => $this->mcp_name));
		
		foreach(array_keys($this->tables) as $table)
		{
			$this->EE->dbforge->drop_table($table);
		}
			
		return TRUE;
	}
	
	private function _set_defaults()
	{ 

	}
}