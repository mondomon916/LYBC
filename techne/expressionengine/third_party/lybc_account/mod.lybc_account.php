<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Greenspacenyc - by Greenspacenyc
 *
 * @package		Greenspacenyc
 * @author		Greenspacenyc Dev Team
 * @copyright	Copyright (c) 2010, Greenspacenyc
 * @link		http://www.greenspacenyc.org
 * @since		Version 1.0
 * @filesource
 */

// --------------------------------------------------------------------

class Lybc_account 
{

	var $return_data	= '';
	
	/**
	 * Constructor
	 * */
	function Lybc_account()
	{
		// Create a local reference of the EE class
		
		$this->EE =& get_instance();
		
		// load CI libraries / helpers etc
		$this->EE->load->library('form_validation');
		//$this->EE->load->helper('url');
		
		$this->from_emails = 'krcolpitts@yahoo.com';
		$this->bcc_emails = array('raymond.r.manalo@gmail.com','krcolpitts@yahoo.com');
		
		$this->settings['sandbox'] = TRUE;
		$this->settings['key'] = 'MD-1avD80a38P';
		
		error_reporting(E_ALL);
		@ini_set('display_errors', 1);
		
	}
	
	/**
     * Emails any errors
     */
	function _email($data=array())
	{
		// Send Email
		// Load the text helper
		$this->EE->load->helper('text');
		$this->EE->load->library('email');
		
		$config['mailtype'] = 'html';
		$this->EE->email->initialize($config);
		
		$this->EE->email->wordwrap = true;
		isset($data['from']) ? 
			$this->EE->email->from($data['from'], 'LeaveYourBusinessCard.com') : 
			$this->EE->email->from('sys@leaveyourbusinesscard.com', 'LeaveYourBusinessCard.com');
		isset($data['to']) ? 
			$this->EE->email->to($data['to']) : 
			$this->EE->email->to($this->bcc_emails);
		$this->EE->email->subject($data['subject']);
		$this->EE->email->message(entities_to_ascii($data['message']));
		$r = $this->EE->email->send();
		
		// If there was a problem with sending the email
		if(!$r)
		{
			return 'There was an error processing your request.';
		}
	}
	
	/**
	 * Parse url path for https
	 */
	function _parse_url_path($path_str='{path=/online-donations/checkout}',$return_https=FALSE)
	{
		$pieces = explode("=",$path_str);
		
		if(sizeof($pieces) > 1)
		{
			if($return_https === TRUE)
			{
				$url = 'https://';
				$url .= $_SERVER['HTTP_HOST'] . str_replace('}','',$pieces[1]);
				
				return $url;
			}
			
			return str_replace('}','',$pieces[1]);
			
		}
		
		else
		{
			if($return_https === TRUE)
			{
				$url = 'https://';
				$url .= $_SERVER['HTTP_HOST'] . $path_str;
				
				return $url;
			}
			
			$url = 'http://';
			$url .= $_SERVER['HTTP_HOST'] . $path_str;
			
			return $url;
		}
		
		return FALSE;
	}
	
	/**
	 * Salt and hash encryption
	 * @param string the string to use as the salt
	 */
	function md5_salt($string) {
	    $chars = str_split('~`!@#$%^&*()[]{}-_\/|\'";:,.+=<>?');
	    $keys = array_rand($chars, 6);
	
	    foreach($keys as $key) {
	        $hash['salt'][] = $chars[$key];
	    }
	
	    $hash['salt'] = implode('', $hash['salt']);
	    $hash['salt'] = md5($hash['salt']);
	    $hash['string'] = md5($hash['salt'].$string.$hash['salt']);
	    return $hash;
	}
	
	/**
	 * Creates the CSRF token
	 * @param string the form being generated
	 * @param string the cookie already generate from a form
	 * @return returns void
	 */
	function _csrf_token($string='')
	{
		// Use cookies since EE
		// Does not support the CI session library
		
		$tok = $this->md5_salt($string);
		$tok = $tok['string'];
		
		// If the session is not set
		// Create the session variable
		
		if($this->EE->input->cookie('csrf_token') === FALSE)
		{
			$this->EE->functions->set_cookie('csrf_token', $tok, time()+24*60*60);
		}
		
		// If the token doesn't already exists then lets add it to the list
		// We only generate a new token if a new form was requested
		
		else
		{
			$this->EE->functions->set_cookie('csrf_token', $tok, time()+24*60*60);
			//$this->_unset_csrf_token();
		}
		
		return $tok;
	}
	
	/**
	 * Removes the CSRF token
	 */
	function _unset_csrf_token()
	{
		$this->EE->functions->set_cookie('csrf_token', '',  time()-60*60*24*365);
		
		return TRUE;
	}
	
	/**
	 * Check the CSRF TOKEN
	 */
	function _check_csrf_token()
	{
		
		// If the token was not passed or the session was not set
		// Kick 'em out!
		
		if($this->EE->input->cookie('csrf_token') === FALSE) 
		{
			redirect('/');	
			
			return FALSE;
		}
		
		// By pass token check with this string 0ad30a0ccf3835b0bfa34987b96764d7
		
		else if($this->EE->input->post('csrf_token', TRUE) === '0ad30a0ccf3835b0bfa34987b96764d7')
		{
			
		}
		
		else if ($this->EE->input->post('csrf_token', TRUE) === FALSE)
		{
			redirect('/');	
			
			return FALSE;
		}
		
		// The token was passed
		// But do they match?...
		
		else if($this->EE->input->post('csrf_token', TRUE))
		{
			// Check if the tokens match
			// If not kick 'em out!
			
			if($this->EE->input->post('csrf_token', TRUE) !== $this->EE->input->cookie('csrf_token'))
			{
				redirect('/');
				
				return FALSE;
			}
		}
		
		// Checks pass
		
		else
		{
			return TRUE;
		}
	}
	
	/**
	 * Build the form tags
	 */
	private function _form_tags($post_url='', $name='', $id='', $class_style='', $variables=array(), $https=FALSE)
	{
		// Get the HTTPS
		
		//$post_url = $this->_parse_url_path($post_url,$https);
		
		$output = '';
		$output .= '<form action="'.$post_url.'" method="post" name="'.$name.'" id="'.$id.'" class="'.$class_style.'">';
		
		// Create CSRF token
		$output .= '<input type="hidden" name="csrf_token" value="'.$this->_csrf_token($this->md5_salt('kyrmls')).'">';
		
		$output .= $this->EE->TMPL->parse_variables_row($this->EE->TMPL->tagdata, $variables);
		$output .= '</form>';
		
		return $output;
	}
	
	/**
	 * Set validation rules
	 */
	private function _set_validation_rules()
	{
		$validation_rules = array(
			array(
				'field'   => 'name',
				'label'   => 'name',
				'rules'   => 'required|trim'
			)
			,array(
				'field'   => 'email',
				'label'   => 'email',
				'rules'   => 'required'
			)
			,array(
				'field'   => 'password',
				'label'   => 'password',
				'rules'   => 'required'
			)
		);
		
		return $validation_rules;
	}
	
	/**
	 * Get the post vars
	 */
	private function _get_post_vars()
	{
		// Build data array of posted values
		// Clean the post vars
		
		$data['full_name'] = $this->EE->input->post('full-name', TRUE);
  		$data['username'] = $this->EE->input->post('username', TRUE);
  		$data['password'] = $this->EE->functions->hash(stripslashes($this->EE->input->post('password', TRUE)));
  		$data['unique_id']	= $this->EE->functions->random('encrypt');
  		$data['join_date']	= $this->EE->localize->now;
  		$data['email']		= $this->EE->input->post('email', TRUE);
  		$data['screen_name'] = $this->EE->input->post('username', TRUE);
  		$data['ip_address']  = $this->EE->input->ip_address();
  		$data['language']	= ($this->EE->config->item('deft_lang')) ? $this->EE->config->item('deft_lang') : 'english';
		$data['time_format'] = ($this->EE->config->item('time_format')) ? $this->EE->config->item('time_format') : 'us';
		$data['timezone']	= ($this->EE->config->item('default_site_timezone') && $this->EE->config->item('default_site_timezone') != '') ? $this->EE->config->item('default_site_timezone') : $this->EE->config->item('server_timezone');
		$data['daylight_savings'] = ($this->EE->config->item('default_site_dst') && $this->EE->config->item('default_site_dst') != '') ? $this->EE->config->item('default_site_dst') : $this->EE->config->item('daylight_savings');		
		$data['authcode'] = $this->EE->functions->random('alnum', 10);
		
		return $data;
	}
	
	/**
	 * Get the post vars into an array
	 */
	private function _get_post_vars_array()
	{
		$data = array();
		
		foreach($_POST as $key => $value)
		{
			$data[$key] = $this->EE->input->post($key,TRUE);
		}
		
		return $data;
	}
	
	/**
	 * Insert the new member
	 */
	private function _insert_member($data=array())
	{
		//Insert the new member
		//Use insert_string() to escape_str on the data automatically
		
  		$ins = $this->EE->db->query($this->EE->db->insert_string('exp_members', $data));
  		var_dump($data);
  		var_dump($ins);
  		
		$member_id = $this->EE->db->insert_id();
		
		var_dump($member_id);
		
		//Insert the member data
		
		$mdata = array('member_id' => $member_id);
		$ins = $this->EE->db->query($this->EE->db->insert_string('exp_member_data', $mdata));
		
		return $member_id;
	}
	
	/**
	 * SQL for the member login
	 */
	private function _sql_member_login($data)
	{
		$sql = "SELECT 
	  				M.password, 
	  				M.unique_id, 
	  				M.member_id, 
	  				M.group_id
					FROM	exp_members AS M 
					INNER JOIN exp_member_groups AS G ON M.group_id = G.group_id";
		
		$sql .= "
					WHERE  	username = '".$this->EE->db->escape_str($data['username'])."'
					AND		G.site_id = '".$this->EE->db->escape_str($this->EE->config->item('site_id'))."'
					AND		password = '".$this->EE->db->escape_str($this->EE->functions->hash($data['password']))."'
					";
		
		return $sql;
	}
	
	/**
	 * Member to login
	 */
	private function _sign_in($data)
	{
		$sql = $this->_sql_member_login($data);
		
		$query = $this->EE->db->query($sql);
		
		if ($query->num_rows() == 0)
		{
			// Return an error message to the template
			
			$this->EE->lang->loadfile('lybc_account');
			
			$errors = array(
				'error_message' => $this->EE->lang->line('lybc_account_invalid')
			);
			
			return $errors;
		}
		
		return $query->result_array();
	}
	
	/**
	 * Create email authorization message
	 */
	public function email_authorization_message($data)
	{
		
		//Send Authorization Email
		//$action_id = $this->EE->functions->fetch_action_id('Gsn_registration', 'activate_member');
		$action_class = 'Lybc_registration';
		$action_method = 'activate_member';
		$data['site_name'] = isset($data['site_name']) ? $data['site_name'] : 'LeaveYourBusinessCard.com';
		$data['tagline'] = isset($data['tagline']) ? $data['tagline'] : 'LeaveYourBusinessCard is an online resource which creates an easy and convenient method to promote and market any business of any size.';
		$data['action_id'] = $this->EE->functions->fetch_action_id($action_class, $action_method) === FALSE ? '': $this->EE->functions->fetch_action_id($action_class, $action_method);
		$data['authcode'] = $this->EE->functions->random('alnum', 10);
		$data['notify_address'] = $data['email'] != '' ? $data['email'] : 'raymond.r.manalo@gmail.com';
		$data['email_title'] = isset($data['title']) ? $data['title'] : 'Welcome to LeaveYourBusinessCard';
		
		$activation_link = $this->EE->functions->fetch_site_index(0, 0).QUERY_MARKER.'ACT='.$data['action_id'].'&id='.$data['authcode'];
		
		$email_html = "
			<p>". $data['full_name'] ." <br /> (". $data['username'].") </p>
			<h2>Welcome to " . $data['site_name'] . "</h2>
			<p>
				" . $data['tagline'] . "
			</p>
			<h3>Get started</h3>
			<ul>
				<li>
					Lorem Ipsum is simply dummy text of the printing and typesetting industry. 
					Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, 
					when an unknown printer
				</li>
				<li>
					Lorem Ipsum is simply dummy text of the printing and typesetting industry. 
					Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, 
					when an unknown printer
				</li>
				<li>
					Lorem Ipsum is simply dummy text of the printing and typesetting industry. 
					Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, 
					when an unknown printer
				</li>
			</ul>
			<h3>How it works</h3>
			<ul>
				<li>
					Create a member profile by signing up with LeaveYourBusinessCard.com. 
					Simply fill in the required areas with your business's information, choose your membership type and you're one step closer to becoming a valued member.
				</li>
				<li>
					The next step is to upload an image of your business card to be posted on your member profile page. 
					Most file types are accepted for the upload process.
				</li>
				<li>
					Now you have finished creating your member profile. You will now have the ability to post any and all information to advertise your services to potential clients. 
					Good luck with your business and thank you for becoming a valued member!
				</li>
			</ul>
			<p>
				<a href='$activation_link'>Activate</a> your account now and login with your email or username.
			</p>
			<p>Kyle Colpitts and the LeaveYourBusinessCard team</p>
			<em>
				Please do not reply to this message. It was sent from an unmonitored email address.
				For general inquires or to request support please visit us at <a href=''>LeaveYourBusinessCard Support</a>
			</em>
		"; 
					 
		$data['email_message'] = $email_html;
		
		return $data;
	}
	
	/**
	 * Send the authorization email
	 */
	public function send_authorization_email($data)
	{
		$this->EE->load->helper('url');
		
		$msg_data = $this->email_authorization_message($data);
		
		//var_dump($msg_data);
		
		// Send Email
		// Load the text helper
		$this->EE->load->helper('text');
		$this->EE->load->library('email');
		
		$config['mailtype'] = 'html';
		$this->EE->email->initialize($config);
		
		$this->EE->email->wordwrap = true;
		$this->EE->email->debug = true;
		$this->EE->email->from($this->from_emails, 'LeaveYourBusinessCard.com');
		$this->EE->email->to($msg_data['notify_address']);
		//$this->EE->email->bcc($this->bcc_emails);
		$this->EE->email->subject($msg_data['email_title']);
		$this->EE->email->message(entities_to_ascii($msg_data['email_message']));
		$this->EE->email->Send();
		
		//$return_url = $this->EE->input->post('return',TRUE);
				
		//var_dump($return_url);
		
		//redirect('/leavebusinesscardcom/sign-up-thank-you');
	}
	
	/**
	 * Register the new user
	 */
	public function register()	
	{
		// Get the data from the post
		
		$data = $this->_get_post_vars();
		
  		//var_dump($data);
		
  		// Insert the new member
  		
  		$this->_insert_member($data);
  		
  		// Send the authorization email
  		
  		$this->send_authorization_email($data);
  		
	}
	
	/**
	 * Check form validation
	 */
	private function _check_validation($post_url, $name, $id, $class_style, $variables)
	{
		// Errors return back to the form
		// set_value() form_error() and validation_errors() are part of the CI form_validation library
		
		$variables = array(
			'errors' => validation_errors()
		);
		
		$output = $this->_form_tags($post_url, $name, $id, $class_style, $variables, TRUE);
	
		return $output;
	}
	
	/**
	 * Set the login cookies
	 */
	private function _set_login_cookies($query, $d)
	{
		
	  		$ui = $query[0]['unique_id'];
			$member_id = $query[0]['member_id'];
			$pw = $d['password'];
			$un = $d['username'];
	  		
	  		
			$expire = 60*60*24*182;

			$this->EE->functions->set_cookie($this->EE->session->c_expire , time()+$expire, $expire);
			$this->EE->functions->set_cookie($this->EE->session->c_uniqueid , $ui, $expire);
			$this->EE->functions->set_cookie($this->EE->session->c_password , $pw,  $expire);
	  		
	  		/** ----------------------------------------
			/**  Create a new session
			/** ----------------------------------------*/
			
			$this->EE->session->create_new_session($member_id);
			$this->EE->session->userdata['username']  = $un;
			
			if ($this->EE->config->item('user_session_type') == 'cs' OR $this->EE->config->item('user_session_type') == 's')
			{
				$this->EE->session->sdata['session_id'] = $this->EE->functions->random();
				$this->EE->session->sdata['member_id']  = $member_id;
				$this->EE->session->sdata['last_activity'] = $this->EE->localize->now;
				$this->EE->session->sdata['site_id']	= $this->EE->config->item('site_id');
	
				$this->EE->functions->set_cookie($this->EE->session->c_session , $this->EE->session->sdata['session_id'], $this->EE->session->session_length);
	
				$this->EE->db->query($this->EE->db->insert_string('exp_sessions', $this->EE->session->sdata));
			}
			
			/** ----------------------------------------
			/**  Update existing session variables
			/** ----------------------------------------*/
	
			$this->EE->session->userdata['username']  = $un;
			$this->EE->session->userdata['member_id'] = $member_id;
	
			/** ----------------------------------------
			/**  Update stats
			/** ----------------------------------------*/
	
			$cutoff		= $this->EE->localize->now - (15 * 60);
	
			$this->EE->db->query("DELETE FROM exp_online_users WHERE site_id = '".$this->EE->db->escape_str($this->EE->config->item('site_id'))."' AND ((ip_address = '".$this->EE->input->ip_address()."' AND member_id = '0') OR date < $cutoff)");
	
			$data = array(
							'member_id'		=> $this->EE->session->userdata('member_id'),
							'name'			=> ($this->EE->session->userdata['screen_name'] == '') ? $this->EE->session->userdata['username'] : $this->EE->session->userdata['screen_name'],
							'ip_address'	=> $this->EE->input->ip_address(),
							'date'			=> $this->EE->localize->now,
							'anon'			=> 'y',
							'site_id'		=> $this->EE->config->item('site_id')
						);
	
			$this->EE->db->query($this->EE->db->update_string('exp_online_users', $data, array("ip_address" => $this->EE->input->ip_address(), "member_id" => $data['member_id'])));
			
			return;
	}
	
	/**
	 * Swap vars
	 */
	private function _swap_vars($str='',$data=array())
	{
		return $this->EE->functions->var_swap($str, $data);
	}
	
	/**
	 * Update data
	 */
	private function _update_data($table='', $id='', $data=array(), $where='member_id')
	{
		$this->EE->db->where($where, $id);
		$this->EE->db->update($table, $data);
		
		return TRUE;
	}
	
	/**
	 * Check logged in
	 */
	private function _check_logged_in()
	{
		$this->EE->load->helper('url');
		
		if($this->EE->session->userdata('member_id') == 0)
		{
		
			$this->EE->lang->loadfile('lybc_account');
				
			//$this->EE->session->set_flashdata('redirect_message', $this->EE->lang->line('lybc_account_unauthorized'));
			//$this->EE->session->set_flashdata('unauthorized', $this->EE->lang->line('lybc_account_member_redirect'));
			
			$f[] = $this->_prep_flash_message('redirect_message', 'lybc_account_unauthorized');
			$f[] = $this->_prep_flash_message('unauthorized', 'lybc_account_member_redirect');
			
			$this->_set_flash_message($f);
			
			// Member is not logged in
			// Send them to members page
			// With flash data
			
			$this->EE->load->helper('url');
		
			redirect('/site/sign-in');
		}
		
		return TRUE;
	}
	
	/**
	 * Edit profile form
	 */
	public function edit_profile_form()
	{
		$return_url = $this->EE->TMPL->fetch_param('return');
		$name = $this->EE->TMPL->fetch_param('name');
		$id = $this->EE->TMPL->fetch_param('id');
		$class_style = $this->EE->TMPL->fetch_param('class');
		$post_url = $this->EE->TMPL->fetch_param('post') == FALSE ? $_SERVER['PHP_SELF'] : $this->EE->TMPL->fetch_param('post');
		
		// Are we logged in?
		
		$this->_check_logged_in();
		
		$variables = array();
		
		if($this->EE->input->post('form-submit') == 'form-submit')
		{
			// Form was submitted
			
			$data = $this->_get_post_vars_array();
			
			//$this->_pr($data);
			
			// Update the database
			
			$data = array(
				'bio' => $data['bio']
				,'location' => $data['location']
				,'occupation' => $data['occupation']
				,'avatar_filename' => $data['thumb-src']
			);
			
			$this->_update_data('exp_members', $this->EE->session->userdata('member_id'), $data);
			
			$this->EE->lang->loadfile('lybc_account');
			
			$variables = $this->get_member_data($this->EE->session->userdata('member_id'));
			
			$variables = $this->_set_query_template_vars($variables);
			
			$variables['success_message'] = $this->EE->lang->line('lybc_account_edit_profile');
			
			$output = $this->_form_tags($post_url, $name, $id, $class_style, $variables, TRUE);
			
			return $output;
			
		}
		
		else 
		{
			
			$variables = $this->get_member_data($this->EE->session->userdata('member_id'));
			
			$variables = $this->_set_query_template_vars($variables);
			
			$variables['avatar_filename'] = $variables['avatar_filename'] != '' ?
												$variables['avatar_filename'] :
												$this->_parse_url_path('/i/lybc-uploadimage.png') ;
			
			$output = $this->_form_tags($post_url, $name, $id, $class_style, $variables, TRUE);
			
			return $output;
		}
	}
	
	/**
	 * Sign into account 
	 */
	public function sign_in_form()
	{
		$return_url = $this->EE->TMPL->fetch_param('return');
		$name = $this->EE->TMPL->fetch_param('name');
		$id = $this->EE->TMPL->fetch_param('id');
		$class_style = $this->EE->TMPL->fetch_param('class');
		$post_url = $this->EE->TMPL->fetch_param('post') == FALSE ? $_SERVER['PHP_SELF'] : $this->EE->TMPL->fetch_param('post');
		
		$variables = array();
		
		if($this->EE->input->post('form-submit') == 'form-submit')
		{
			$post_vars = $this->_get_post_vars_array();
			
			$sign_in = $this->_sign_in($post_vars);
			
			//$this->_pr($sign_in);
			
			if(isset($sign_in['error_message']))
			{
				$variables = $sign_in;
				
				$output = $this->_form_tags($post_url, $name, $id, $class_style, $variables, TRUE);
			
				return $output;
			}
			
			else 
			{
			
				$data = array(
					'password' => $this->EE->functions->hash($this->EE->input->post('password'))
					,'username' => $this->EE->input->post('username')
				);
				
				$this->_set_login_cookies($sign_in, $data);
				
				$this->EE->load->helper('url');
				
				$member_id = $sign_in[0]['member_id'];
				
				redirect('/member/detail/'.$member_id);
				
				return TRUE;
			}
		}
		
		else
		{
			// Output return the form
			// And parse the data array for the variables within the tag
			
			$output = $this->_form_tags($post_url, $name, $id, $class_style, $variables, TRUE);
			
			return $output;
		}
	}
	
	/**
	 * Sign up form
	 */
	public function sign_up_form()
	{
		// fetch params
		$return_url = $this->EE->TMPL->fetch_param('return');
		$name = $this->EE->TMPL->fetch_param('name');
		$id = $this->EE->TMPL->fetch_param('id');
		$class_style = $this->EE->TMPL->fetch_param('class');
		$post_url = $this->EE->TMPL->fetch_param('post') == FALSE ? $_SERVER['PHP_SELF'] : $this->EE->TMPL->fetch_param('post');
		//$return_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
		
		//var_dump($return_url);
		
		$variables = array();
		
		if($this->EE->input->post('form-submit') == 'form-submit')
		{
			// Form submitted
			// Check the form validation
			
			$this->EE->form_validation->set_rules($this->_set_validation_rules());
			
			if($this->EE->form_validation->run() == FALSE)
			{
				// Errors return back to the form
				
				$output = $this->_check_validation($post_url, $name, $id, $class_style, $variables);
			
				return $output;
			}
			
			else 
			{
				// Submitted without errors
				// Process the registration
				
				$this->register();
				
				
			}
		}
		
		else
		{
			// Output return the form
			// And parse the data array for the variables within the tag
			
			$output = $this->_form_tags($post_url, $name, $id, $class_style, $variables, TRUE);
			
			return $output;
		}
		
		return TRUE;
	}
	
	/**
	 * Logout
	 */
	public function logout()
	{
		/** ----------------------------------------
		/**  Kill the session and cookies
		/** ----------------------------------------*/
		
		$this->EE->db->where('site_id', $this->EE->config->item('site_id'));
		$this->EE->db->where('ip_address', $this->EE->input->ip_address());
		$this->EE->db->where('member_id', $this->EE->session->userdata('member_id'));
		$this->EE->db->delete('online_users');		
		
		$this->EE->db->where('session_id', $this->EE->session->userdata['session_id']);
		$this->EE->db->delete('sessions');		
		
		$this->EE->functions->set_cookie($this->EE->session->c_uniqueid);
		$this->EE->functions->set_cookie($this->EE->session->c_password);
		$this->EE->functions->set_cookie($this->EE->session->c_session);
		$this->EE->functions->set_cookie($this->EE->session->c_expire);
		$this->EE->functions->set_cookie($this->EE->session->c_anon);
		$this->EE->functions->set_cookie('read_topics');
		$this->EE->functions->set_cookie('tracker');
		
		//$this->EE->functions->set_cookie('uniqueid', '',  time()-60*60*24*365);
		//$this->EE->functions->set_cookie('userhash', '',  time()-60*60*24*365);
		
		$return_url = $this->EE->functions->fetch_site_index();
		$return_url = '/member/logout';
		
		$this->EE->load->helper('url');
		
		redirect($return_url);
		
		return true;
	}
	
	/**
	 * Get Member data
	 */
	public function get_member_data($id='')
	{
		// Use the id to get the member data
		
		$query = $this->EE->db->get_where('exp_members', array('member_id' => $this->EE->db->escape_str($id)));
		
		return $query->result_array();
	}
	
	/**
	 * Get Members
	 */
	public function get_members()
	{
		$query = $this->EE->db->get('exp_members');
		
		return $query->result_array();
	}
	
	/**
	 * Set query template vars
	 */
	private function _set_query_template_vars($query=array())
	{
		$data = array();
		
		foreach($query as $row)
		{
			foreach($row as $key => $value)
			{
				$data[$key] = $value;
			}
		}
		
		return $data;
	}
	
	/**
	 * Get Member Id
	 */
	public function get_member_id()
	{
		if($this->EE->TMPL->fetch_param('author_id') !== FALSE)
		{
			$id = $this->EE->TMPL->fetch_param('author_id');
		}
		
		elseif($this->EE->uri->segment(3) !== FALSE) 
		{
			$id = $this->EE->uri->segment(3);
		}
		
		else 
		{
			return FALSE;
		}
		
		return $id;
	}
	
	/**
	 * Member Redirect
	 */
	public function member_redirect()
	{
		$this->EE->lang->loadfile('lybc_account');
			
		//$this->EE->session->set_flashdata('redirect_message', $this->EE->lang->line('lybc_account_unauthorized'));
		//$this->EE->session->set_flashdata('unauthorized', $this->EE->lang->line('lybc_account_member_redirect'));
		
		$f[] = $this->_prep_flash_message('redirect_message', 'lybc_account_unauthorized');
		$f[] = $this->_prep_flash_message('unauthorized', 'lybc_account_member_redirect');
		
		$this->_set_flash_message($f);
		
		// Member is not logged in
		// Send them to members page
		// With flash data
		
		$this->EE->load->helper('url');
	
		redirect('/profiles');
	}
	
	/**
	 * Member Profile 
	 */
	public function member_profile()
	{	
		//var_dump($this->EE->session->userdata('member_id'));
		
		// If the member is logged in and looking at the member profile
		
		if($this->EE->session->userdata('member_id') != 0)
		{
			
			//echo 'if';
			// Member is logged in
			
			// Get Member Data
			
			//$this->EE->uri->segment(3);
			//$this->EE->session->userdata('member_id');
			
			if($this->EE->TMPL->fetch_param('login') == 'true')
			{
				$variable_row = $this->get_member_data($this->EE->session->userdata('member_id'));
			}
			
			else 
			{
				$variable_row = $this->get_member_data($this->EE->uri->segment(3));
			}
			
			$variable_row[]['member_id'] = $this->EE->session->userdata('member_id');
			
			//$this->_pr($variable_row);
			
			$variable_row = $this->_set_query_template_vars($variable_row);
			
			//$variable_row = array();
			
			return $this->_set_template_vars($variable_row);
		}
		
		// If the member is not logged in this is the snippet from the home page
		
		else
		{
			// If there is a no-redirect param then lets not redirect
			// Or show any error message
			
			// Get Member Data
			
			$id = $this->get_member_id();
			
			//var_dump($id);
			
			// If there is no id then redirect to view member profiles
			
			if($id === FALSE)
			{
				//$this->member_redirect();
				
				return $this->_set_template_vars($data=array());
			}
			
			$variable_row = $this->get_member_data($id);
			
			//$this->_pr($variable_row);
			
			//var_dump(sizeof($variable_row));
			
			if(sizeof($variable_row) == 0)
			{
				$this->member_redirect();
			}
			
			$variable_row = $this->_set_query_template_vars($variable_row);
			
			//$variable_row = array();
			
			return $this->_set_template_vars($variable_row);
			
		}
		
	}
	
	/**
	 * Members loop
	 */
	public function members()
	{
		$query = $this->get_members();
		
		$this->_pr($query);
	}
	
	/**
	 * Get fields
	 */
	private function get_fields($channel_id='')
	{
		$r_data = array();
		$query = $this->EE->db->get('exp_channel_fields');
		
		foreach($query->result_array() as $row)
		{
			$r_data[] = array(
				'field_id' => $row['field_id']
				,'field_name' => $row['field_name']
				,'field_content_type' => $row['field_content_type']
				,'field_fmt' => $row['field_fmt']
				,'group_id' => $row['group_id']
			);
		}
		
		return $r_data;
	}
	
	/**
	 * Prep field data
	 */
	private function _prep_field_data($fields=array(), $post=array())
	{
		foreach($fields as $row)
		{
			$data['field_id_'.$row['field_id']] = $this->_map_field_data($row['field_name'], $post, $row['group_id']);
			$data['field_ft_'.$row['field_id']] = $row['field_fmt'];
		}
		
		//$data['entry_date'] = time(); 
		$data['entry_date'] = $this->EE->localize->set_human_time($this->EE->localize->now); 
		                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 
		$data['title'] = isset($post['image-title']) ? 
			$post['image-title'] : 
			(isset($post['title']) ? $post['title'] : '');
		
		$data['entry_id'] = 0;
		
		return $data;
	}
	
	/**
	 * Get image orientation
	 */
	private function _get_image_orientation($width=0)
	{
		return $width < 300 ? 'Portrait' : 'Landscape';
	}
	
	/**
	 * Map field data
	 */
	private function _map_field_data($data_name='', $post=array(), $dir='1')
	{
		switch($data_name)
		{
			case 'card_image':
				return '{filedir_'.$dir.'}'.$post['thumb-file'];
				break;
			case 'card_description':
				return $post['image-description'];
				break;
			case 'card_rating':
				return '1';
				break;
			case 'card_orientation':
				return $this->_get_image_orientation($post['thumb-width']);
				break;
		}
		
		return;
		
	}
	
	/**
	 * Get the channel fields
	 */
	function get_channel_fields($field_group, $fields = array())
	{
		if (count($fields) > 0)
		{
			$this->db->select(implode(',', $fields));
		}

		$this->EE->db->from('channel_fields');
		$this->EE->db->where('group_id', $field_group);
		$this->EE->db->order_by('field_order');
		
		return $this->EE->db->get();
	}
	
	/**
	 * Set field settings
	 */
	function set_field_settings($field_group_id)
	{
		$this->EE->api->instantiate(array('channel_fields'));
		
		// Get the channel fields
		
		$field_query = $this->get_channel_fields($field_group_id);
		
		// Get the daylight savings time
		
		$dst_enabled = $this->EE->session->userdata('daylight_savings');
		$dst_enabled = ( ! isset($_POST['dst_enabled'])) ? 'n' :  $dst_enabled;
		
		// Loop the channel fields
		
		foreach ($field_query->result_array() as $row)
		{
			$field_data = '';
			$field_dt = '';
			$field_fmt	= $row['field_fmt'];

			// Settings that need to be prepped	
					
			$settings = array(
				'field_instructions'	=> trim($row['field_instructions']),
				'field_text_direction'	=> ($row['field_text_direction'] == 'rtl') ? 'rtl' : 'ltr',
				'field_fmt'				=> $field_fmt,
				'field_dt'				=> $field_dt,
				'field_data'			=> $field_data,
				'field_name'			=> 'field_id_'.$row['field_id'],
				'dst_enabled'			=> $dst_enabled
			);

			$ft_settings = array();

			if (isset($row['field_settings']) && strlen($row['field_settings']))
			{
				$ft_settings = unserialize(base64_decode($row['field_settings']));
			}

			$settings = array_merge($row, $settings, $ft_settings);
			
			$r = $this->EE->api_channel_fields->set_settings($row['field_id'], $settings);
			
		}
		
	}
	
	/**
	 * Submit an entry thru the api
	 */
	public function submit_entry($channel_id, $data)
	{
		$this->EE->load->library('api');
		$this->EE->api->instantiate(array('channel_entries'));
		
		if($this->EE->api_channel_entries->submit_new_entry($channel_id, $data) === FALSE)
		{
			return FALSE;
		}
		
		return TRUE;
	}
	
	/**
	 * Save the upload form
	 */
	private function _upload_form()
	{
		$this->EE->load->library('api');
		$this->EE->api->instantiate('channel_entries');
		
		$fields = $this->get_fields(1);
		
		$post = $this->_get_post_vars_array();
		
		// Create data with fields
		
		$data = $this->_prep_field_data($fields, $post);
		
		// Update field settings
		
		$field_settings = $this->set_field_settings($fields[0]['group_id']);
		
		// Save the entry
		
		$save = $this->submit_entry($fields[0]['group_id'], $data);
		
		return TRUE;
		
	}
	
	/**
	 * Upload Form
	 */
	public function upload_form()
	{
		if($this->EE->input->post('form-submit') != FALSE)
		{
			$this->EE->lang->loadfile('lybc_account');
			
			// Form submit
			
			$upd = $this->_upload_form();
			
			if($upd == TRUE)
			{
				$data = array(
					'success_message' => $this->EE->lang->line('lybc_account_upload')
				);
			}
			
			else 
			{
				$data = array(
					'error_message' => $this->EE->lang->line('lybc_account_upload_error')
				);
			}
			
			return $this->_set_template_vars($data);
		}
		
		else 
		{
			//$this->create_mod_action();
		
			$variables = array();
		
			return $this->_form_tags('/upload', 'upload-form', 'upload-form', '', $variables, FALSE);
					
		}
		
		
	}
	
	/**
	 * Upload an image
	 * {AID:Lybc_account:upload_image}
	 */
	function upload_image($data=array())
	{
		$upload_dir = $this->EE->input->get_post('d') != '' ?
						'images/avatars/uploads/' :
						'i/cards/';
		
		// Upload config 
		
		$host_path = $this->settings['sandbox'] == TRUE ?
						$this->_parse_url_path('/') :
						$this->_parse_url_path('/') ;
		
		$upload_path = $this->settings['sandbox'] == TRUE ? 
							'/Users/ray/Documents/workspace/Leaveyourbusinesscard/' . $upload_dir :
							'/home/leave16/public_html/' . $upload_dir;
		
		//var_dump($host_path);
		
		$config['upload_path'] = $upload_path;
		$config['allowed_types'] = 'gif|jpg|png';
		
		$this->EE->load->library('upload', $config);
		
		if(!$this->EE->upload->do_upload())
		{
			$error = array('error' => $this->EE->upload->display_errors());
			
			$assoc_array = $config;
			
			// Create an array map for errors
			
			$new_array = array_map(create_function('$key, $value', 'return $key.":".$value." # ";'), array_keys($assoc_array), array_values($assoc_array));
			
			$this->_email($data = array(
				'subject' => 'Upload picture error::' . $error['error']
				,'message' => 'Upload picture error - ' . $error['error'] . ' ' . implode($new_array)
			));
			
			//echo '';
			
			die();
		}
		else
		{
			// Uncomment to test real upload
			$data = array('upload_data' => $this->EE->upload->data());
		
			
			/*$data = array(
				'upload_data' => array(
					'full_path' => $upload_path . 'BusinessCard2.gif'
					,'file_name' => 'BusinessCard2.gif'
				)
			)*/;
			
			/*
			 * Array
				(
				    [file_name]    => mypic.jpg
				    [file_type]    => image/jpeg
				    [file_path]    => /path/to/your/upload/
				    [full_path]    => /path/to/your/upload/jpg.jpg
				    [raw_name]     => mypic
				    [orig_name]    => mypic.jpg
				    [client_name]  => mypic.jpg
				    [file_ext]     => .jpg
				    [file_size]    => 22.2
				    [is_image]     => 1
				    [image_width]  => 800
				    [image_height] => 600
				    [image_type]   => jpeg
				    [image_size_str] => width="800" height="200"
				)
			 * */
			//var_dump($data);
		}
		
		//$image_src = $this->EE->functions->create_url('i/fundraising/'.$data['upload_data']['file_name']);
		
		$image_src = $host_path . $upload_dir . $data['upload_data']['file_name'];
		
		//$image_src = $this->EE->functions->create_url('i/cards/BusinessCard2.gif');
		
		// Return a json string
		
		$arr = array(
			'img_src' => $image_src
			,'img_name' => $data['upload_data']['file_name']
			,'img_width' => $data['upload_data']['image_width']
			,'img_height' => $data['upload_data']['image_height']
		);
		
		echo json_encode($arr);
		
		die();
		
		//return $return_arr;
	}
	
	/**
	 * Update emails
	 */
	function update_emails()
	{
		if($this->EE->input->post('form-submit') != FALSE)
		{
			// Email kyle with a person's email
			
			//var_dump( $this->_get_post_vars_array());
			
			$data = $this->_get_post_vars_array();
			
			$e_data = array(
				'from' => 'info@leaveyourbusinesscard.com'
				,'subject' => 'LeaveYourBusinesscard - A new user email'
				,'message' => 'A new user signed up for an email update -- ' . $data['email-update']
				,'to' => $data['email-update']
			);
			
			// Send email
			
			$this->_email($e_data);
			
			$variables = array(
				'success_message' => 'Thank You! You will receive an email to try out the site.'
			);
			
			return $this->_set_template_vars($variables);
		}
		
		else
		{
			// Display the form
			
			$variables = array();
		
			return $this->_form_tags('/site/index-landing', 'update-email-form', 'update-email-form', '', $variables, FALSE);
			
		}
	}
	
	/**
	 * Create an action
	 */
	public function create_mod_action($class='Lybc_account',$method='upload_image')
	{
		$data = array(
			'class'		=> $class ,
			'method'	=> $method
		);
		//insert into actions
		$this->EE->db->insert('actions', $data);
		
		return;
	}
	
	/**
	 * Fetch action id
	 */
	public function fetch_action_id($class='Lybc_account', $method='upload_image')
	{
		
		return $this->EE->functions->fetch_action_id($this->EE->TMPL->fetch_param('class', 'Lybc_account'), $this->EE->TMPL->fetch_param('method', 'upload_image'));
		
		/*var_dump($base_url);
		
		
		$this->EE->db->select('');
		$this->EE->db->from('actions');
		
		
		$query = $this->EE->db->get();
		
		var_dump($query->result());*/
	}
	
	/**
	 * Fetch the action url
	 */
	public function fetch_action_url()
	{
		$post_url = $this->_parse_url_path('/',FALSE);
		
		$act_id = $this->fetch_action_id();
		
		$dir = $this->EE->TMPL->fetch_param('dir');
		
		return $post_url.QUERY_MARKER.'ACT='.$act_id.AMP.'d='.$dir;
	}
	
	/**
	 * Encrypt string
	 */
	private function _encrypt_string($string='')
	{
		$this->EE->load->library('encrypt');
		
		return $this->EE->encrypt->encode($string, $this->settings['key']);
	}
	
	/**
	 * Decode string
	 */
	private function _decode_string($string='')
	{
		$this->EE->load->library('encrypt');
		
		return $this->EE->encrypt->decode($string, $this->settings['key']);
		
	}
	
	/**
	 * Prep flash data
	 */
	private function _prep_flash_message($name, $line)
	{
		$t = array(
			'name' => $name
			,'line' => $line
		);
		
		return $t;
	}
	
	/**
	 * Set flash messages
	 * @param object $data the data array('name' => 'flash data name',line' => 'lang line')
	 */
	private function _set_flash_message($data=array())
	{
		$this->EE->lang->loadfile('lybc_account');
		
		foreach($data as $rec)
		{
			$this->EE->session->set_flashdata($rec['name'], $this->EE->lang->line($rec['line']));
		}
		
		return;
	}
	
	/**
	 * Get flash messages
	 */
	private function _get_flash_message($data=array(),$return_string=TRUE)
	{	
		$this->EE->lang->loadfile('lybc_account');
		
		$str_msg = '';
		
		// Create a string and an array
		
		foreach($data as $rec)
		{
			$str_msg .= $this->EE->session->flashdata($rec['name']);
			$d[] = $this->EE->session->flashdata($rec['name']);
		}
		
		if($return_string == TRUE)
		{
			return $str_msg;
		}
		
		else
		{
			return $d;
		}
		
	}
	
	/**
	 * Flash messages
	 */
	public function flash_messages()
	{
		$f[] = $this->_prep_flash_message('redirect_message', 'lybc_account_unauthorized');
		$f[] = $this->_prep_flash_message('unauthorized', 'lybc_account_member_redirect');
		
		$message = $this->_get_flash_message($f,TRUE);
		
		$variables = array(
			'flash_message' => $message
		);
		
		$output = $this->_set_template_vars($variables);
		
		return $output;
	}
	
	/**
	 * Set and parse the template vars
	 */
	private function _set_template_vars($data=array())
	{
		$output = '';
		$output .= $this->EE->TMPL->parse_variables_row($this->EE->TMPL->tagdata, $data);
		
		return $output;
	}
	
	  /**
	   * Debug function for printing the content of an object
	   *
	   * @param mixes $obj
	   */
	  function _pr($obj) {
    echo '<pre style="word-wrap: break-word">';
    if ( is_object($obj) )
      print_r($obj);
    elseif ( is_array($obj) )
      print_r($obj);
    else
      echo $obj;
    echo '</pre>';
  }
	
}


/* End of file mod.download.php */
/* Location: ./system/expressionengine/third_party/download/mod.download.php */