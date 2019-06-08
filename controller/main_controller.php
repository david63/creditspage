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
use phpbb\auth\auth;
use phpbb\template\template;
use phpbb\user;
use phpbb\language\language;
use phpbb\controller\helper;
use phpbb\pagination;
use david63\creditspage\core\functions;

class main_controller implements main_interface
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \pagination */
	protected $pagination;

	/** @var \david63\creditspage\core\functions */
	protected $creditspage;

	/** @var string custom constants */
	protected $cpconstants;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string phpBB extension */
	protected $php_ext;

	/**
	* Constructor for listener
	*
	* @param \phpbb\config\config					$config				Config object
	* @param \phpbb\request\request					$request			Request object
	* @param \phpbb\auth\auth 						$auth				Auth object
	* @param \phpbb\template\template				$template			Template object
	* @param \phpbb\user                			$user				User object
	* @param \phpbb\language\language				$language			Language object
	* @param \phpbb\controller\helper				$helper				Helper object
	* @param \pagination\pagination             	$pagination			Pagination object
	* @param \david63\creditspage\core\functions	functions			Functions for the extension
	* @param array	                            	$constants			phpBB constants
	* @param string 				            	$phpbb_root_path	phpBB root path
	* @param string                            		$php_ext			phpBB file extension

	*
	* @return \david63\creditspage\controller\main_controller
	* @access public
	*/
	public function __construct(config $config, request $request, auth $auth, template $template, user $user, language $language, helper $helper, pagination $pagination, functions $functions, $cpconstants, $phpbb_root_path, $php_ext)
	{
		$this->config			= $config;
		$this->request			= $request;
		$this->auth				= $auth;
		$this->template			= $template;
		$this->user				= $user;
		$this->language			= $language;
		$this->helper			= $helper;
		$this->pagination		= $pagination;
		$this->functions		= $functions;
		$this->constants		= $cpconstants;
		$this->phpbb_root_path	= $phpbb_root_path;
		$this->php_ext			= $php_ext;
	}

	/**
	* Controller for creditspage
	*
	* @param string		$name
	* @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
	*/
	public function creditoutput()
	{
		// Start initial var setup
		$start	= $this->request->variable('start', 0);

		$ext_data = $this->functions->get_credit_values();

		$enabled_extension_meta_data = $this->functions->enabled_extension_meta_data();
		uasort($enabled_extension_meta_data, array($this->functions, 'sort_extension_meta_data_table'));

		// Display the extensions
		$loop = 1;

		foreach (array_slice($enabled_extension_meta_data, $start) as $name => $block_vars)
		{
			if ($loop <= $this->config['cp_items_page'] )
			{
				// Let's decide who can see what - Founders see everything
				if ($this->user->data['user_type'] == $this->constants['user_founder'] || ($this->auth->acl_get('u_') && ($ext_data[$block_vars['META_NAME']] & $this->constants['cpuser']) || $this->auth->acl_get('m_') && ($ext_data[$block_vars['META_NAME']] & $this->constants['cpmod']) || $this->auth->acl_get('a_') && ($ext_data[$block_vars['META_NAME']] & $this->constants['cpadmin'])))
				{
					$this->template->assign_block_vars('credit_row', array(
						'DESCRIPTION'	=> $block_vars['META_DESCRIPTION'],
						'DISPLAY_NAME'	=> $block_vars['META_DISPLAY_NAME'],
						'VERSION'		=> $block_vars['META_VERSION'],
					));

					foreach ($block_vars['META_AUTHORS'] as $key => $data)
					{
						$this->template->assign_block_vars('credit_row.author', array(
							'AUTHOR'	=> $data['name'],
							'EMAIL'		=> (array_key_exists('email', $data)) ? $data['email'] : '',
							'HOMEPAGE'	=> (array_key_exists('homepage', $data)) ? $this->functions->get_tiny_url($data['homepage']) : '',
							'ROLE'		=> (array_key_exists('role', $data)) ? $data['role'] : '',
						));
					}
				}
				$loop ++;
			}
		}

		// Pagination
		$extension_count = 0;

		// Get user count of extensions
		foreach ($enabled_extension_meta_data as $name => $block_vars)
		{
			// Let's see how many this user can see
			if ($this->user->data['user_type'] == $this->constants['user_founder'] || ($this->auth->acl_get('u_') && ($ext_data[$block_vars['META_NAME']] & $this->constants['cpuser']) || $this->auth->acl_get('m_') && ($ext_data[$block_vars['META_NAME']] & $this->constants['cpmod']) || $this->auth->acl_get('a_') && ($ext_data[$block_vars['META_NAME']] & $this->constants['cpadmin'])))
			{
				$extension_count ++;
			}
		}

		$action	= $this->helper->route('david63_creditspage_creditoutput', array('start' => $start));
		$start	= $this->pagination->validate_start($start, $this->config['cp_items_page'], $extension_count);
		$this->pagination->generate_template_pagination($action, 'pagination', 'start', $extension_count, $this->config['cp_items_page'], $start);

		$this->template->assign_vars(array(
			'CP_EMAIL'			=> $this->config['cp_email'],
			'CP_HOMEPAGE'		=> $this->config['cp_homepage'],
			'CP_ROLE'	   		=> $this->config['cp_role'],

			'NAMESPACE'			=> $this->functions->get_ext_namespace('twig'),

			'S_DISPLAY_JUMPBOX'	=> true,

			'TOTAL_EXTENSIONS'	=> $extension_count,

			'U_ACTION'			=> $action,
		));

		make_jumpbox(append_sid("{$this->phpbb_root_path}viewforum.$this->php_ext"));

		return $this->helper->render('credits_page.html', $this->language->lang('EXTENSION_CREDITS'));
	}
}
