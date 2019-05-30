<?php
/**
*
* @package Credits Page Extension
* @copyright (c) 2019 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace david63\creditspage\controller;

use phpbb\controller\helper;
use phpbb\template\template;
use phpbb\language\language;

class main_controller implements main_interface
{
	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\language\language */
	protected $language;

	/**
	* Constructor
	*
	* @param \phpbb\controller\helper	$helper		Helper object
	* @param \phpbb\template\template	$template	Template object
	* @param \phpbb\language\language	$language	Language object
	*
	* @return \david63\creditspage\controller\main_controller
	* @access public
	*/
	public function __construct(helper $helper, template $template, language $language)
	{
		$this->helper	= $helper;
		$this->template	= $template;
		$this->language	= $language;
	}

	/**
	* Controller for route /creditspage/{name}
	*
	* @param string		$name
	* @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
	*/
	public function creditoutput()
	{
		return $this->helper->render('credits_page.html', $this->language->lang('EXTENSION_CREDITS'));
	}
}
