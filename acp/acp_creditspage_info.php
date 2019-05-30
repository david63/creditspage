<?php
/**
*
* @package Credits Page Extension
* @copyright (c) 2019 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace david63\creditspage\acp;

class acp_creditspage_info
{
	function module()
	{
		return array(
			'filename'	=> '\david63\creditspage\acp\acp_creditspage_module',
			'title'		=> 'CREDITS_PAGE',
			'modes'		=> array(
				'manage'	=> array('title' => 'CREDITS_PAGE_MANAGE', 'auth' => 'ext_david63/creditspage && acl_a_board', 'cat' => array('CREDITS_PAGE')),
			),
		);
	}
}
