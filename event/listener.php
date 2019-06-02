<?php
/**
*
* @package Credits Page Extension
* @copyright (c) 2019 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace david63\creditspage\event;

/**
* @ignore
*/
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use phpbb\config\config;
use phpbb\auth\auth;
use phpbb\template\template;
use phpbb\user;
use phpbb\controller\helper;
use david63\creditspage\core\creditspage;

/**
* Event listener
*/
class listener implements EventSubscriberInterface
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \david63\creditspage\core\creditspage */
	protected $creditspage;

	/** @var string custom constants */
	protected $cpconstants;

	/**
	* Constructor for listener
	*
	* @param \phpbb\config\config					$config			Config object
	* @param \phpbb\auth\auth 						$auth			Auth object
	* @param \phpbb\template\template				$template		Template object
	* @param \phpbb\user                			$user			User object
	* @param \phpbb\controller\helper				$helper			Helper object
	* @param \david63\creditspage\core\creditspage	creditspage		Methods for the extension
	* @param array	                            	$constants		phpBB constants
	*
	* @return \david63\creditspage\event\listener
	* @access public
	*/
	public function __construct(config $config, auth $auth, template $template, user $user, helper $helper, creditspage $creditspage, $cpconstants)
	{
		$this->config		= $config;
		$this->auth			= $auth;
		$this->template		= $template;
		$this->user			= $user;
		$this->helper		= $helper;
		$this->creditspage	= $creditspage;
		$this->constants	= $cpconstants;
	}

	/**
	* Assign functions defined in this class to event listeners in the core
	*
	* @return array
	* @static
	* @access public
	*/
	static public function getSubscribedEvents()
	{
		return array(
			'core.page_footer'			=> 'page_footer',
			'core.user_setup'			=> 'load_language_on_setup',
			'core.page_header_after'	=> 'page_header',
		);
	}

	public function page_header($event)
	{
		// Can we show the nav bar link?
		if ($this->auth->acl_get('u_') && ($this->config['cp_show_navbar'] & $this->constants['cpuser']) || $this->auth->acl_get('m_') && ($this->config['cp_show_navbar'] & $this->constants['cpmod']) || $this->auth->acl_get('a_') && ($this->config['cp_show_navbar'] & $this->constants['cpadmin']))
		{
			$this->template->assign_var('SHOW_CP_LINK', true);
		}
	}

	/**
	* Set the template variables
	*
	* @param $event
	*
	* @return null
	* @access public
	*/
	public function page_footer($event)
	{
		$ext_data = $this->creditspage->get_credit_values();

		$enabled_extension_meta_data = $this->creditspage->enabled_extension_meta_data();
		uasort($enabled_extension_meta_data, array($this->creditspage, 'sort_extension_meta_data_table'));

		foreach ($enabled_extension_meta_data as $name => $block_vars)
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
						'AUTHOR'		=> $data['name'],
						'EMAIL'			=> (array_key_exists('email', $data)) ? $data['email'] : '',
						'HOMEPAGE'		=> (array_key_exists('homepage', $data)) ? $data['homepage'] : '',
						'ROLE'			=> (array_key_exists('role', $data)) ? $data['role'] : '',
					));
				}
			}
		}

		$this->template->assign_vars(array(
			'CP_EMAIL'		=> $this->config['cp_email'],
			'CP_HIDE_ALL'	=> $this->config['cp_hide_all'],
			'CP_HOMEPAGE'	=> $this->config['cp_homepage'],
			'CP_ROLE'		=> $this->config['cp_role'],

			'U_CREDITS_PAGE' => $this->helper->route('david63_creditspage_creditoutput'),
		));
	}

	/**
	* Load common credits page language files during user setup
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function load_language_on_setup($event)
	{
		// load the language files
		$lang_set_ext	= $event['lang_set_ext'];
		$lang_set_ext[]	= array(
			'ext_name' => 'david63/creditspage',
			'lang_set' => 'credits_page',
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}
}
