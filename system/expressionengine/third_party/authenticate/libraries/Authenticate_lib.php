<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Authenticate
 * 
 * @package		Authenticate
 * @subpackage	Libraries
 * @author		Justin Kimbrell
 * @copyright	Copyright (c) 2012, Justin Kimbrell
 * @link 		http://www.objectivehtml.com/authenticate
 * @version		1.1.0
 * @build		20120627
 */
 
class Authenticate_lib {
	
	public function __construct()
	{
		$this->EE =& get_instance();
		
		$this->EE->load->library('auth');
	}
	
	public function login($auth_id, $auth_pass, $type = 'username')
	{
		$is_active = TRUE;
		
		if(!$this->is_active($type, $auth_id))
		{
			$this->EE->base_form->set_error(lang('authenticate_member_not_active'));
			
			$is_active = FALSE;	
			$return = FALSE;
		}
		else
		{
			switch($type)
			{
				case 'email':
					$return = $this->EE->auth->authenticate_email($auth_id, $auth_pass);
					break;
				case 'username':
					$return = $this->EE->auth->authenticate_username($auth_id, $auth_pass);
					break;
				case 'id':
					$return = $this->EE->auth->authenticate_id($auth_id, $auth_pass);
					break;
			}
		}
		
		return array(
			'member'    => $return,
			'is_active' => $is_active	
		);
	}
	
	public function is_active($type, $value)
	{
		//if(config_item('req_mbr_activation') != 'none')
		//{
			$always_disallowed = array(4);

			$member = $this->EE->db->get_where('members', array($type => $value));
			
			if (in_array($member->row('group_id'), $always_disallowed))
			{
				return FALSE;
				//return $this->EE->output->show_user_error('general', lang('authenticate_account_not_active'));
			}				
		//}
		
		return TRUE;
	}
	
	public function forgot_password()
	{
		// Is user banned?
		if ($this->EE->session->userdata('is_banned') === TRUE)
		{
			$this->EE->base_form->set_error(lang('authenticate_invalid_email'));
		}

		// Error trapping
		if ( ! $address = $this->EE->input->post('email'))
		{
			$this->EE->base_form->set_error(lang('authenticate_invalid_email'));
		}

		$this->EE->load->helper('email');

		if ( ! valid_email($address))
		{
			$this->EE->base_form->set_error(lang('authenticate_invalid_email'));
		}

		$address = strip_tags($address);

		// Fetch user data
		$query = $this->EE->db->select('member_id, username')
							  ->where('email', $address)
							  ->get('members');

		if ($query->num_rows() == 0)
		{
			$this->EE->base_form->set_error(lang('authenticate_invalid_email'));
		}

		$member_id = $query->row('member_id') ;
		$username  = $query->row('username') ;

		// Kill old data from the reset_password field

		$time = time() - (60*60*24);

		$this->EE->db->where('date <', $time)
					 ->or_where('member_id', $member_id)
					 ->delete('reset_password');

		// Create a new DB record with the temporary reset code
		$rand = $this->EE->functions->random('alnum', 8);

		$data = array('member_id' => $member_id, 'resetcode' => $rand, 'date' => time());

		$this->EE->db->query($this->EE->db->insert_string('exp_reset_password', $data));

		// Buid the email message

		if ($this->EE->input->get_post('FROM') == 'forum')
		{
			if ($this->EE->input->get_post('board_id') !== FALSE && 
				is_numeric($this->EE->input->get_post('board_id')))
			{
				$query = $this->EE->db->select('board_forum_url, board_id, board_label')
									  ->where('board_id', $this->EE->input->get_post('board_id'))
									  ->get('forum_boards');
			}
			else
			{
				$query = $this->EE->db->select('board_forum_url, board_id, board_label')
									  ->where('board_id', (int) 1)
									  ->get('forum_boards');
			}

			$return		= $query->row('board_forum_url') ;
			$site_name	= $query->row('board_label') ;
			$board_id	= $query->row('board_id') ;
		}
		else
		{
			$site_name	= stripslashes($this->EE->config->item('site_name'));
			$return 	= $this->EE->config->item('site_url');
		}

		$forum_id = ($this->EE->input->get_post('FROM') == 'forum') ? '&r=f&board_id='.$board_id : '';

		$swap = array(
						'name'		=> $username,
						'reset_url'	=> $this->EE->functions->fetch_site_index(0, 0).QUERY_MARKER.'ACT='.$this->EE->functions->fetch_action_id('Member', 'reset_password').'&id='.$rand.$forum_id,
						'site_name'	=> $site_name,
						'site_url'	=> $return
					 );

		$template = $this->EE->functions->fetch_email_template('forgot_password_instructions');
		$email_tit = $this->_var_swap($template['title'], $swap);
		$email_msg = $this->_var_swap($template['data'], $swap);
		
		// Instantiate the email class

		$this->EE->load->library('email');
		$this->EE->email->wordwrap = true;
		$this->EE->email->from($this->EE->config->item('webmaster_email'), $this->EE->config->item('webmaster_name'));
		$this->EE->email->to($address);
		$this->EE->email->subject($email_tit);
		$this->EE->email->message($email_msg);

		if ( ! $this->EE->email->send())
		{		
			$this->EE->base_form->set_error(lang('authenticate_error_sending_email'));
		}
	}
	
	private function _var_swap($str, $data)
	{
		if ( ! is_array($data))
		{
			return FALSE;
		}

		foreach ($data as $key => $val)
		{
			$str = str_replace('{'.$key.'}', $val, $str);
		}

		return $str;
	}
}