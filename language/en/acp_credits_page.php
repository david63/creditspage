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
	$lang = array();
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

	'CREDITS_PAGE'					=> 'Extension Credits Page',
	'CREDITS_PAGE_EXPLAIN'			=> 'Here you can select wihch user type can view each extension credit.',
	'CREDIT_PAGE_OPTIONS'			=> 'Options',
	'CREDITS_PAGE_MANAGE_EXPLAIN'	=> 'Here you can select the options for the Extension Credits page.',

	'CP_EMAIL'			   			=> 'Author email',
	'CP_EMAIL_EXPLAIN'				=> 'Show the email address of the author - if present.',
	'CP_HOMEPAGE'					=> 'Author homepage',
	'CP_HOMEPAGE_EXPLAIN'			=> 'Show the homepage of the author - if present.',
	'CP_ROLE'						=> 'Author role',
	'CP_ROLE_EXPLAIN'				=> 'Show the role of the author - if present.',

	'NEW_VERSION'					=> 'New Version',
	'NEW_VERSION_EXPLAIN'			=> 'There is a newer version of this extension available.',

	'OPTIONS_EXPLAIN'				=> 'Click on an “Extension name” to display the details of that extension.',

	'VERSION'						=> 'Version',
));

// Donate
$lang = array_merge($lang, array(
	'DONATE'					=> 'Donate',
	'DONATE_EXTENSIONS'			=> 'Donate to my extensions',
	'DONATE_EXTENSIONS_EXPLAIN'	=> 'This extension, as with all of my extensions, is totally free of charge. If you have benefited from using it then please consider making a donation by clicking the PayPal donation button opposite - I would appreciate it. I promise that there will be no spam nor requests for further donations, although they would always be welcome.',

	'PAYPAL_BUTTON'				=> 'Donate with PayPal button',
	'PAYPAL_TITLE'				=> 'PayPal - The safer, easier way to pay online!',
));
