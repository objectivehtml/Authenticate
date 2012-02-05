<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

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
 
require 'libraries/Email_Template.php';

class Authenticate_mcp {
	
	public $consumer_key, $consumer_secret;
	
	public function __construct()
	{
		$this->EE =& get_instance();
		
		$this->EE->load->library('theme_loader', array(__CLASS__));
		
		
		$this->EE->cp->add_to_head('
		<script type="text/javascript">
			var AutoEmailGlobal = {
				url: {
					getCategories: \''.$this->_url('get_categories', TRUE).'\',
					getMemberGroups: \''.$this->_url('get_member_groups', TRUE).'\',
					getStatuses: \''.$this->_url('get_statuses', TRUE).'\'
				}
			}
		</script>');
		
		$this->EE->theme_loader->javascript('auto_email');
		$this->EE->theme_loader->css('auto_email');
        
		$this->EE->load->driver('channel_data');
	}
	
	public function index()
	{
		$vars = array();
		
		$this->EE->cp->set_variable('cp_page_title', 'Auto E-mail Templates');
		
		$this->EE->cp->set_right_nav(array(
			'Create New Template' => $this->_url('create_template')
		));
		
		return $this->EE->load->view('settings', $vars, TRUE);
	}
	
	public function create_template()
	{
		$vars = array(
			'template' => new Email_Template()	
		);
		
		$this->EE->cp->set_variable('cp_page_title', 'Create New Template');
		
		$this->EE->cp->set_right_nav(array(
			'Back to Home' => $this->_url('index')
		));

		return $this->EE->load->view('template', $vars, TRUE);
	}
	
	public function edit_template()
	{
		$vars = array();
		
		$this->EE->cp->set_variable('cp_page_title', 'Edit Template');
		
		$this->EE->cp->set_right_nav(array(
			'Back to Home' => $this->_url('index')
		));

		return $this->EE->load->view('template', $vars, TRUE);
	}
	
	public function get_categories()
	{
		$channel_id	= $this->EE->input->get_post('channel_id');
		$channel	= $this->EE->channel_data->get_channel($channel_id);
		
		$cat_group	= $this->EE->channel_data->get_category_group($channel->row('cat_group'));
		
		$this->EE->load->library('api');
		$this->EE->api->instantiate('channel_categories');
		
		$category_tree = $this->EE->api_channel_categories->category_tree($cat_group->row('group_id'));
		
		var_dump($category_tree);exit();
		
		return $this->EE->output->send_ajax_response($category_tree);
	}
	
	public function get_channel()
	{
		$channel_id	= $this->EE->input->get_post('channel_id');
		
		$response 	= $this->EE->channel_data->get_channel($channel_id)->result_array();
		
		return $this->EE->output->send_ajax_response($response);
	}
	
	public function get_statuses()
	{
		$channel_id	= $this->EE->input->get_post('channel_id');
		
		$response 	= $this->EE->channel_data->get_channel_statuses($channel_id)->result_array();
		
		return $this->EE->output->send_ajax_response($response);
	}
		
	public function save_settings()
	{
		
	}
	
	private function _url($method = 'index', $useAmp = FALSE)
	{
		$amp = !$useAmp ? AMP : '&';
		
		return str_replace(AMP, $amp, BASE . $amp . 'C=addons_modules' .$amp . 'M=show_module_cp' . $amp . 'module=Auto_email' . $amp . 'method=' . $method);
	}
	
	private function _current_url($append = '', $value = '')
	{
		$url = (!empty($_SERVER['HTTPS'])) ? 'https://'.$_SERVER['SERVER_NAME'] : 'http://'.$_SERVER['SERVER_NAME'];
		
		if(!empty($append))
			$url .= '?'.$append.'='.$value;
		
		return $url;
	}
	
}