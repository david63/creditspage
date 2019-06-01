<?php
/**
*
* @package Credits Page Extension
* @copyright (c) 2019 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace david63\creditspage\acp;

class acp_creditspage_module
{
	public $u_action;

	function main($id, $mode)
	{
		global $phpbb_container;

		$this->page_title = $phpbb_container->get('language')->lang('CREDITS_PAGE');


		switch ($mode)
		{
			case 'settings':
				$this->tpl_name = 'credits_page_settings';

				// Get an instance of the admin controller
				$admin_controller = $phpbb_container->get('david63.creditspage.admin.controller');
				$admin_controller->display_settings();
			break;

			case 'manage':
				$this->tpl_name = 'credits_page_manage';

				// Get an instance of the admin controller
				$admin_controller = $phpbb_container->get('david63.creditspage.admin.controller');
				$admin_controller->display_options();
			break;
		}
	}
}
