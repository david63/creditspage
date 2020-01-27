<?php
/**
*
* @package Credits Page Extension
* @copyright (c) 2019 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace david63\creditspage\core;

use phpbb\db\driver\driver_interface;
use phpbb\extension\manager;
use phpbb\language\language;
use phpbb\exception\version_check_exception;

/**
* functions
*/
class functions
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\extension\manager */
	protected $phpbb_extension_manager;

	/** @var string custom tables */
	protected $tables;

	/** @var \phpbb\language\language */
	protected $language;

	/**
	* Constructor for creditspage
	*
	* @param \phpbb_db_driver				$db							The db connection
	* @param \phpbb\extension\manager		$phpbb_extension_manager	Extension manager
	* @param array							$tables						phpBB db tables
	* @param \phpbb\language\language		$language					Language object
	*
	* @access public
	*/
	public function __construct(driver_interface $db, manager $phpbb_extension_manager, $tables, language $language)
	{
		$this->db			= $db;
		$this->ext_manager	= $phpbb_extension_manager;
		$this->tables		= $tables;
		$this->language		= $language;

		$this->namespace	= __NAMESPACE__;
	}

	/**
	* Get the extension's namespace
	*
	* @return $extension_name
	* @access public
	*/
	public function get_ext_namespace($mode = 'php')
	{
		// Let's extract the extension name from the namespace
		$extension_name = substr($this->namespace, 0, -(strlen($this->namespace) - strrpos($this->namespace, '\\')));

		// Now format the extension name
		switch ($mode)
		{
			case 'php':
				$extension_name = str_replace('\\', '/', $extension_name);
			break;

			case 'twig':
				$extension_name = str_replace('\\', '_', $extension_name);
			break;
		}

		return $extension_name;
	}

	/**
	* Check if there is an updated version of the extension
	*
	* @return $new_version
	* @access public
	*/
	public function version_check()
	{
		if ($this->get_meta('host') == 'www.phpbb.com')
		{
			$port 	= 'https://';
			$stable	= null;
		}
		else
		{
			$port 	= 'http://';
			$stable = 'unstable';
		}

		// Can we access the version srver?
		if (@fopen($port . $this->get_meta('host') . $this->get_meta('directory') . '/' . $this->get_meta('filename'), 'r'))
		{
			try
			{
				$md_manager 	= $this->ext_manager->create_extension_metadata_manager($this->get_ext_namespace());
				$version_data	= $this->ext_manager->version_check($md_manager, true, false, $stable);
			}
			catch (version_check_exception $e)
			{
				$version_data['current'] = 'fail';
			}
		}
		else
		{
			$version_data['current'] = 'fail';
		}

		return $version_data;
	}

	/**
	* Get a meta_data key value
	*
	* @return $meta_data
	* @access public
	*/
	public function get_meta($data)
	{
		$meta_data	= '';
		$md_manager = $this->ext_manager->create_extension_metadata_manager($this->get_ext_namespace());

		foreach (new \RecursiveIteratorIterator(new \RecursiveArrayIterator($md_manager->get_metadata('all'))) as $key => $value)
		{
			if ($data === $key)
			{
				$meta_data = $value;
			}
		}

		return $meta_data;
	}
	/**
	* Get the meta data for the enabled extensions
	*
	* @return $enabled_extension_meta_data
	* @access public
	*/
	public function enabled_extension_meta_data()
	{
		$enabled_extension_meta_data = array();

		foreach ($this->ext_manager->all_enabled() as $name => $location)
		{
			$md_manager = $this->ext_manager->create_extension_metadata_manager($name);
			$meta_data	= $md_manager->get_metadata('all');

			// Let's validate the meta data
			if (!array_key_exists('description', $meta_data) || $meta_data['description'] == '')
			{
				$meta_data['description'] = $this->language->lang('NO_DESCRIPTION');
			}

			if (!array_key_exists('time', $meta_data))
			{
				$meta_data['time'] = '';
			}

			$enabled_extension_meta_data[$name] = array(
				'META_AUTHORS'		=> $meta_data['authors'],
				'META_DESCRIPTION' 	=> $meta_data['description'],
				'META_DISPLAY_NAME'	=> $meta_data['extra']['display-name'],
				'META_NAME'			=> $meta_data['name'],
				'META_PHP'			=> $meta_data['require']['php'],
				'META_PHPBB'		=> $meta_data['extra']['soft-require']['phpbb/phpbb'],
				'META_TIME'			=> $meta_data['time'],
				'META_VERSION' 		=> $meta_data['version'],
			);
		}

		return $enabled_extension_meta_data;
	}

	/**
	* Get the extension permission values from the database
	*
	* @return $ext_data
	* @access public
	*/
	public function get_credit_values()
	{
		$ext_data = array();

		// Get the credits values from the database
		$sql = 'SELECT ext_name, ext_credits
			FROM ' . $this->tables['ext'] . '
			WHERE ext_active = 1';

		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$ext_data[$row['ext_name']] = $row['ext_credits'];
		}

		$this->db->sql_freeresult($result);

		return $ext_data;
	}

	/**
	* Sort helper for the table containing the metadata about the extensions.
	*/
	public function sort_extension_meta_data_table($val1, $val2)
	{
		return strnatcasecmp($val1['META_DISPLAY_NAME'], $val2['META_DISPLAY_NAME']);
	}

	/**
	* Get a tiny url for a link
	*
	* @return $data
	* @access public
	*/
	function get_tiny_url($url)
	{
		// Check if cURL is available on the server
		if (function_exists('curl_version'))
		{
			$curl_handle = curl_init();
			curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 5);
			curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl_handle, CURLOPT_URL, 'http://tinyurl.com/api-create.php?url=' . $url);

			$data = curl_exec($curl_handle);
			curl_close($curl_handle);

			return $data;
		}
		else
		{
			// If not then return original url
			return $url;
		}
	}
}
