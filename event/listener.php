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
use phpbb\controller\helper;
use david63\creditspage\core\functions;

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

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \david63\creditspage\core\functions */
	protected $creditspage;

	/** @var string custom constants */
	protected $cpconstants;

	/**
	* Constructor for listener
	*
	* @param \phpbb\config\config					$config			Config object
	* @param \phpbb\auth\auth 						$auth			Auth object
	* @param \phpbb\template\template				$template		Template object
	* @param \phpbb\controller\helper				$helper			Helper object
	* @param \david63\creditspage\core\functions	functions		Functions for the extension
	* @param array	                            	$constants		phpBB constants
	*
	* @return \david63\creditspage\event\listener
	* @access public
	*/
	public function __construct(config $config, auth $auth, template $template, helper $helper, functions $functions, $cpconstants)
	{
		$this->config		= $config;
		$this->auth			= $auth;
		$this->template		= $template;
		$this->helper		= $helper;
		$this->functions	= $functions;
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
			'core.page_header_after'	=> 'page_header',
			'core.page_footer'			=> 'page_footer',
			'core.user_setup'			=> 'load_language_on_setup',
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
		$this->template->assign_var('U_CREDITS_PAGE', $this->helper->route('david63_creditspage_creditoutput'));
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
			'ext_name' => $this->functions->get_ext_namespace(),
			'lang_set' => 'common',
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}
}
