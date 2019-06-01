<?php
/**
*
* @package Credits Page Extension
* @copyright (c) 2019 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace david63\creditspage\migrations;

use phpbb\db\migration\migration;

class version_2_1_0 extends migration
{
	/**
	* Add or update data in the database
	*
	* @return array Array of table data
	* @access public
	*/
	public function update_data()
	{
		// Add the config vars
		return array(
			array('config.add', array('cp_email', 1)),
			array('config.add', array('cp_homepage', 1)),
			array('config.add', array('cp_role', 1)),
			array('config.add', array('cp_show_navbar', 0)),

			// Add the ACP module
			array('module.add', array('acp', 'ACP_CAT_DOT_MODS', 'CREDITS_PAGE')),

			array('module.add', array(
				'acp', 'CREDITS_PAGE', array(
					'module_basename'	=> '\david63\creditspage\acp\acp_creditspage_module',
					'modes'				=> array('settings', 'manage'),
				),
			)),
		);
	}

	/**
	* Add the columns schema to the tables
	*
	* @return array Array of table schema
	* @access public
	*/
	public function update_schema()
	{
		return array(
			'add_columns'	=> array(
				$this->table_prefix . 'ext'	=> array(
					'ext_credits' => array('TINT:1', 0),
				),
			),
		);
	}

	/**
	* Drop the columns schema from the tables
	*
	* @return array Array of table schema
	* @access public
	*/
	public function revert_schema()
	{
		return array(
			'drop_columns'	=> array(
				$this->table_prefix . 'ext'	=> array(
					'ext_credits',
				),
			),
		);
	}
}
