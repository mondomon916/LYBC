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

class Lb_account {

	function Lb_account()
	{
		$this->EE =& get_instance();
	}
	
	function signup_form()
	{
		$return_url = "";
		$name = "";
		$id = "";
		$class_style = "";
		$post_url = "";
		
		// If submitted
		
		if($this->EE->input->post('submit') === 'Sign-Up')
		{
			// Get the post variables
			
			$first_name = $this->EE->input->post('first_name');
			$last_name = $this->EE->input->post('last_name');
			$email = $this->EE->input->post('email');
			$username = $this->EE->input->post('username');
			$password = $this->EE->input->post('password');
			
			$this->_check_user($username, $password);
		}
		
		// Show form
		
		else 
		{
			
			$variables = array();
			
			$output = '';
			$output .= '<form action="'.$post_url.'" method="post" name="'.$name.'" id="'.$id.'" class="'.$class_style.'">';
			$output .= $this->EE->TMPL->parse_variables_row($this->EE->TMPL->tagdata, $variables);
			$output .= '</form>';
			
			return $output;
		}
	}
	
	function _check_user($un, $pw)
	{
		$sql = "SELECT 
  				M.password, 
  				M.unique_id, 
  				M.member_id, 
  				M.group_id
				FROM	exp_members AS M 
				INNER JOIN exp_member_groups AS G ON M.group_id = G.group_id
				WHERE  	username = '".$this->EE->db->escape_str($un)."'
				AND		G.site_id = '".$this->EE->db->escape_str($this->EE->config->item('site_id'))."'
				AND		password = '".$this->EE->db->escape_str($pw)."'
				AND		M.group_id = '5'
				";
  		
  		$query = $this->EE->db->query($sql);
  		
	}
	
	/**
	 * Debug function for printing the content of an object
	 *
	 * @param mixes $obj
	 */
	public function _pr($obj) {
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
// END CLASS

/* End of file mcp.module_name.php */
/* Location: ./system/expressionengine/third_party/modules/module_name/mcp.module_name.php */
