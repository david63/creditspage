<?php
/**
*
* @package Credits Page Extension
* @copyright (c) 2019 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace david63\creditspage\core;

use phpbb\db\driver\driver_interface;
use phpbb\extension\manager;

/**
* creditspage
*/
class creditspage
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\extension\manager */
	protected $phpbb_extension_manager;

	/** @var string custom tables */
	protected $tables;

	/**
	* Constructor for creditspage
	*
	* @param \phpbb_db_driver				$db							The db connection
	* @param \phpbb\extension\manager		$phpbb_extension_manager	Extension manager
	* @param array							$tables						phpBB db tables
	*
	* @access public
	*/
	public function __construct(driver_interface $db, manager $phpbb_extension_manager, $tables)
	{
		$this->db			= $db;
		$this->ext_manager	= $phpbb_extension_manager;
		$this->tables		= $tables;
	}

	/**
	* Display the user privacy data
	*
	* @return null
	* @access public
	*/
	public function enabled_extension_meta_data()
	{
		$enabled_extension_meta_data = array();

		foreach ($this->ext_manager->all_enabled() as $name => $location)
		{
			$md_manager = $this->ext_manager->create_extension_metadata_manager($name);
			$meta_data	= $md_manager->get_metadata('all');

			$enabled_extension_meta_data[$name] = array(
				'META_AUTHORS'		=> $meta_data['authors'],
				'META_DESCRIPTION' 	=> $meta_data['description'],
				'META_DISPLAY_NAME'	=> $meta_data['extra']['display-name'],
				'META_NAME'			=> $meta_data['name'],
				'META_VERSION' 		=> $meta_data['version'],
			);
		}

		return $enabled_extension_meta_data;
	}

	public function get_credit_values()
	{
		$ext_data = array();

		// Get the credits values from the database
		$sql = 'SELECT ext_name, ext_credits
			FROM ' . $this->tables['ext'] . '
			WHERE ext_active = 1';

		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$ext_data[$row['ext_name']] = $row['ext_credits'];
		}

		$this->db->sql_freeresult($result);

		return $ext_data;
	}

	/**
	* Sort helper for the table containing the metadata about the extensions.
	*/
	public function sort_extension_meta_data_table($val1, $val2)
	{
		return strnatcasecmp($val1['META_DISPLAY_NAME'], $val2['META_DISPLAY_NAME']);
	}
}
