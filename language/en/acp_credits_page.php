<?php
/**
*
* @package Credits Page Extension
* @copyright (c) 2019 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

/// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, array(
	'AUTHOR_ATTRIBUTES'				=> 'Author attributes',
	'AUTHOR_NAME'					=> 'Author',
	'AUTHOR_ROLE'					=> 'Role',

	'CREDITS_PAGE'					=> 'Extension Credits Page',
	'CREDITS_PAGE_EXPLAIN'			=> 'Here you can select wihch user type can view each extension credit.',
	'CREDIT_PAGE_OPTIONS'			=> 'Options',
	'CREDITS_PAGE_MANAGE_EXPLAIN'	=> 'Here you can select the options for the Extension Credits page.',

	'CP_EMAIL'			   			=> 'Author email',
	'CP_EMAIL_EXPLAIN'				=> 'Show the email address of the author - if present.',
	'CP_HOMEPAGE'					=> 'Author homepage',
	'CP_HOMEPAGE_EXPLAIN'			=> 'Show the homepage of the author - if present.',
	'CP_ITEMS_PAGE'					=> 'Items per page',
	'CP_ITEMS_PAGE_EXPLAIN'			=> 'Select the number of extension credits to be displayed on each page.',
	'CP_ROLE'						=> 'Author role',
	'CP_ROLE_EXPLAIN'				=> 'Show the role of the author - if present.',

	'HYPHEN_SEPARATOR'				=> ' - ',

	'OPTIONS'						=> 'Options',
	'OPTIONS_EXPLAIN'				=> 'Click on an “Extension name” to display the details of that extension.',

	'PHP'							=> 'PHP',
	'PHPBB'							=> 'phpBB',

	'REQUIRES'						=> 'Requires',
));
