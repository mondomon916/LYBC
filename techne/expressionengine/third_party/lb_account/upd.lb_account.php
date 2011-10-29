<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * LeaveYourBusinesscard - by Raymond Manalo
 *
 * @package		LeaveYourBusinesscard
 * @author		LeaveYourBusinesscard Dev Team
 * @copyright	Copyright (c) 2010, Greenspacenyc
 * @link		http://www.greenspacenyc.org
 * @since		Version 1.0
 * @filesource
 */

// --------------------------------------------------------------------

class Lb_account_upd
{
	
	var $version = '1.0';
	
	/**
	 * Constructor
	 */
	function Lb_account_upd()
	{
		// Make a local reference to the ExpressionEngine super object
		$this->EE =& get_instance();
	}
	
	/**
	 * Module Installer
	 *
	 * @access	public
	 * @return	bool
	 */	
	function install()
	{
		$this->EE->load->dbforge();
		
		$data = array(
			'module_name' => 'Lb_account' ,
			'module_version' => $this->version,
			'has_cp_backend' => 'n',
			'has_publish_fields' => 'n'
		);

		$this->EE->db->insert('modules', $data);
		
		// Insert the actions
		
		/*$data = array(
			'class'		=> 'Hw_payments',
			'method'	=> 'donations_form'
		);

		$this->EE->db->insert('actions', $data);*/
		
	}
	
	/**
	 * Module Uninstaller
	 *
	 * @access	public
	 * @return	bool
	 */
	function uninstall()
	{
		$this->EE->load->dbforge();

		$this->EE->db->select('module_id');
		$query = $this->EE->db->get_where('modules', array('module_name' => 'Lb_account'));

		$this->EE->db->where('module_id', $query->row('module_id'));
		$this->EE->db->delete('module_member_groups');

		$this->EE->db->where('module_name', 'Lb_account');
		$this->EE->db->delete('modules');
		
		$this->EE->db->where('class', 'Lb_account');
		$this->EE->db->delete('actions');

		return TRUE;
	}
	
	/**
	 * Module Updater
	 *
	 * @access	public
	 * @return	bool
	 */	
	
	function update($current='')
	{
		return FALSE;
	}
	
	
}




/* END Class */

/* End of file upd.download.php */
/* Location: ./system/expressionengine/third_party/modules/download/upd.gsn_photos.php */