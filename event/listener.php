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

	/**
	* Constructor for listener
	*
	* @param \phpbb\auth\auth 						$auth			Auth object
	* @param \phpbb\template\template				$template		Template object
	* @param \phpbb\user                			$user			User object
	* @param \phpbb\controller\helper				$helper			Helper object
	* @param \david63\creditspage\core\creditspage	creditspage		Methods for the extension
	*
	* @return \david63\creditspage\event\listener
	* @access public
	*/
	public function __construct(auth $auth, template $template, user $user, helper $helper, creditspage $creditspage)
	{
		$this->auth			= $auth;
		$this->template		= $template;
		$this->user			= $user;
		$this->helper		= $helper;
		$this->creditspage	= $creditspage;
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
			'core.page_footer'	=> 'page_footer',
			'core.user_setup'	=> 'load_language_on_setup',
		);
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
			if ($this->user->data['user_type'] == USER_FOUNDER || ($this->auth->acl_get('a_') && ($ext_data[$block_vars['META_NAME']] == 4 || $ext_data[$block_vars['META_NAME']] == 5 || $ext_data[$block_vars['META_NAME']] == 6 || $ext_data[$block_vars['META_NAME']] == 7) || ($this->auth->acl_get('m_') && ($ext_data[$block_vars['META_NAME']] == 2 || $ext_data[$block_vars['META_NAME']] == 3 || $ext_data[$block_vars['META_NAME']] == 6 || $ext_data[$block_vars['META_NAME']] == 7)) || ($this->auth->acl_get('u_') && ($ext_data[$block_vars['META_NAME']] == 1 || $ext_data[$block_vars['META_NAME']] == 3 || $ext_data[$block_vars['META_NAME']] == 7))))
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
