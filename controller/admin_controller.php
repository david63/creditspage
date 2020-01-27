<?php
/**
*
* @package Credits Page Extension
* @copyright (c) 2019 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace david63\creditspage\controller;

use phpbb\config\config;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\user;
use phpbb\language\language;
use phpbb\log\log;
use phpbb\db\driver\driver_interface;
use david63\creditspage\core\functions;

/**
* Admin controller
*/
class admin_controller implements admin_interface
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \david63\creditspage\core\functions */
	protected $functions;

	/** @var string custom constants */
	protected $cpconstants;

	/** @var string custom tables */
	protected $tables;

	/** @var string Custom form action */
	protected $u_action;

	/**
	* Constructor for admin controller
	*
	* @param \phpbb\config\config					$config			Config object
	* @param \phpbb\request\request					$request		Request object
	* @param \phpbb\template\template				$template		Template object
	* @param \phpbb\user							$user			User object
	* @param \phpbb\language\language				$language		Language object
	* @param \phpbb\log\log							$log			Log object
	* @param \phpbb_db_driver						$db				The db connection
	* @param \david63\creditspage\core\functions	functions		Functiond for the extension
	* @param array	                            	$constants		phpBB constants
	* @param array									$tables			phpBB db tables
	*
	* @return \david63\creditspage\controller\admin_controller
	* @access public
	*/
	public function __construct(config $config, request $request, template $template, user $user, language $language, log $log, driver_interface $db, functions $functions, $cpconstants, $tables)
	{
		$this->config		= $config;
		$this->request		= $request;
		$this->template		= $template;
		$this->user			= $user;
		$this->language		= $language;
		$this->log			= $log;
		$this->db			= $db;
		$this->functions	= $functions;
		$this->constants	= $cpconstants;
		$this->tables		= $tables;
	}

	/**
	* Display the options a user can configure for this extension
	*
	* @return null
	* @access public
	*/
	public function display_settings()
	{
		// Add the language files
		$this->language->add_lang('acp_credits_page', $this->functions->get_ext_namespace());
		$this->language->add_lang('acp_common', $this->functions->get_ext_namespace());

		// Create a form key for preventing CSRF attacks
		add_form_key($this->constants['form_key']);

		$back = false;

		// Is the form being submitted?
		if ($this->request->is_set_post('submit'))
		{
			// Is the submitted form is valid?
			if (!check_form_key($this->constants['form_key']))
			{
				trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			// If no errors, process the form data
			// Set the options the user configured
			$this->set_options();

			// Add option settings change action to the admin log
			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'CREDITS_PAGE_OPTIONS_LOG');

			// Option settings have been updated and logged
			// Confirm this to the user and provide link back to previous page
			trigger_error($this->language->lang('CONFIG_UPDATED') . adm_back_link($this->u_action));
		}

		// Template vars for header panel
		$version_data	= $this->functions->version_check();

		$this->template->assign_vars(array(
			'DOWNLOAD'			=> (array_key_exists('download', $version_data)) ? '<a class="download" href =' . $version_data['download'] . '>' . $this->language->lang('NEW_VERSION_LINK') . '</a>' : '',

			'HEAD_TITLE'		=> $this->language->lang('CREDITS_PAGE'),
			'HEAD_DESCRIPTION'	=> $this->language->lang('CREDITS_PAGE_EXPLAIN'),

			'NAMESPACE'			=> $this->functions->get_ext_namespace('twig'),

			'S_BACK'			=> $back,
			'S_VERSION_CHECK'	=> (array_key_exists('current', $version_data)) ? $version_data['current'] : false,

			'VERSION_NUMBER'	=> $this->functions->get_meta('version'),
		));

		$ext_data = $this->functions->get_credit_values();

		$enabled_extension_meta_data = $this->functions->enabled_extension_meta_data();
		uasort($enabled_extension_meta_data, array($this->functions, 'sort_extension_meta_data_table'));

		foreach ($enabled_extension_meta_data as $name => $block_vars)
		{
			$this->template->assign_block_vars('credit_row', array(
				'DESCRIPTION'	=> $block_vars['META_DESCRIPTION'],
				'DISPLAY_NAME'	=> $block_vars['META_DISPLAY_NAME'],

				'META_KEY'		=> rand(), // Create a unique key for the js script

				'NAME' 			=> $block_vars['META_NAME'],

				'TIME'			=> $block_vars['META_TIME'],

				'OPT_SET'		=> $ext_data[$block_vars['META_NAME']],

				'PHP'			=> $block_vars['META_PHP'],
				'PHPBB'			=> $block_vars['META_PHPBB'],

				'S_TIME_SET'	=> ($block_vars['META_TIME']) ? true : false,

				'VERSION'		=> $block_vars['META_VERSION'],
			));

			foreach ($block_vars['META_AUTHORS'] as $key => $data)
			{
				$this->template->assign_block_vars('credit_row.author', array(
					'AUTHOR'		=> $data['name'],
					'EMAIL'			=> (array_key_exists('email', $data)) ? $data['email'] : '',
					'HOMEPAGE'		=> (array_key_exists('homepage', $data)) ? $data['homepage'] : '',
					'ROLE'			=> (array_key_exists('role', $data)) ? $data['role'] : '',
				));
			}
		}

		$this->template->assign_vars(array(
			'OPT_ADM' => $this->constants['cpadmin'],
			'OPT_MOD' => $this->constants['cpmod'],
			'OPT_USR' => $this->constants['cpuser'],
		));
	}

	/**
	* Set the options a user can configure
	*
	* @return null
	* @access protected
	*/
	protected function set_options()
	{
		$ext_vars	= $this->request->variable_names();
		$ext_ary	= array();
		$opts_set	= 0;

		// Need only the extension variables here
		foreach ($ext_vars as $key => $data)
		{
			// Get the key/value data
			if (substr($data, 0, 3) == 'ext')
			{
				$opts_set	|= substr($data, -1);
				$ext_key 	= substr($data, 4);
				$ext_key	= substr($ext_key, 0, -2);

				// Combine the values
				if (array_key_exists($ext_key, $ext_ary))
				{
					$ext_ary[$ext_key] |= substr($data, -1);
				}
				else
				{
					$ext_ary[$ext_key] = substr($data, -1);
				}
			}
		}

		// Let's reset the credits field in the extensions table
		$sql = 'UPDATE ' . $this->tables['ext'] . '
				SET ext_credits = 0';

		$this->db->sql_query($sql);

		// Update the extensions table in the database
		foreach ($ext_ary as $key => $value)
		{
			$sql = 'UPDATE ' . $this->tables['ext'] . '
				SET ext_credits = ' . (int) $value . "
				WHERE ext_name = '$key'";

			$this->db->sql_query($sql);
		}

		$this->config->set('cp_show_navbar', $opts_set);
	}

	/**
	* Display the options a user can configure for this extension
	*
	* @return null
	* @access public
	*/
	public function display_options()
	{
		// Add the language files
		$this->language->add_lang('acp_credits_page', $this->functions->get_ext_namespace());
		$this->language->add_lang('acp_common', $this->functions->get_ext_namespace());

		// Create a form key for preventing CSRF attacks
		add_form_key($this->constants['form_key']);

		$back = false;

		// Is the form being submitted?
		if ($this->request->is_set_post('submit'))
		{
			// Is the submitted form is valid?
			if (!check_form_key($this->constants['form_key']))
			{
				trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			// If no errors, process the form data
			// Set the options the user configured
			$this->set_manage_options();

			// Add option settings change action to the admin log
			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'CREDITS_PAGE_MANAGE_LOG');

			// Option settings have been updated and logged
			// Confirm this to the user and provide link back to previous page
			trigger_error($this->language->lang('CONFIG_UPDATED') . adm_back_link($this->u_action));
		}

		// Template vars for header panel
		$version_data	= $this->functions->version_check();

		$this->template->assign_vars(array(
			'DOWNLOAD'			=> (array_key_exists('download', $version_data)) ? '<a class="download" href =' . $version_data['download'] . '>' . $this->language->lang('NEW_VERSION_LINK') . '</a>' : '',

			'HEAD_TITLE'		=> $this->language->lang('CREDITS_PAGE'),
			'HEAD_DESCRIPTION'	=> $this->language->lang('CREDITS_PAGE_MANAGE_EXPLAIN'),

			'NAMESPACE'			=> $this->functions->get_ext_namespace('twig'),

			'S_BACK'			=> $back,
			'S_VERSION_CHECK'	=> (array_key_exists('current', $version_data)) ? $version_data['current'] : false,

			'VERSION_NUMBER'	=> $this->functions->get_meta('version'),
		));

		$this->template->assign_vars(array(
			'CP_EMAIL'		=> isset($this->config['cp_email']) ? $this->config['cp_email'] : '',
			'CP_HOMEPAGE'	=> isset($this->config['cp_homepage']) ? $this->config['cp_homepage'] : '',
			'CP_ITEMS_PAGE'	=> isset($this->config['cp_items_page']) ? $this->config['cp_items_page'] : '',
			'CP_ROLE'		=> isset($this->config['cp_role']) ? $this->config['cp_role'] : '',

			'U_ACTION'		=> $this->u_action,
		));
	}

	/**
	* Set the options a user can configure
	*
	* @return null
	* @access protected
	*/
	protected function set_manage_options()
	{
		$this->config->set('cp_email', $this->request->variable('cp_email', 0));
		$this->config->set('cp_homepage', $this->request->variable('cp_homepage', 1));
		$this->config->set('cp_items_page', $this->request->variable('cp_items_page', 25));
		$this->config->set('cp_role', $this->request->variable('cp_role', 1));
	}

	/**
	* Set page url
	*
	* @param string $u_action Custom form action
	* @return null
	* @access public
	*/
	public function set_page_url($u_action)
	{
		return $this->u_action = $u_action;
	}
}
