<?php

/**
* @package phpBB3.1
* @copyright Anvar (c) 2015 bb3.mobi
*/

namespace bb3top\rating\migrations;

class v_1_0_1 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['rating_version']) && version_compare($this->config['rating_version'], '1.0.1', '>=');
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v310\dev');
	}

	public function update_schema()
	{
		return array(
		'add_tables' => array(
				$this->table_prefix . 'rating' => array(
					'COLUMNS' => array(
						'top_id'			=> array('UINT:11', null, 'auto_increment'),
						'cat_id'			=> array('UINT', 0),
						'topic_id'			=> array('UINT', 0),
						'user_id'			=> array('UINT', 0),
						'top_url'			=> array('VCHAR:30', ''),
						'top_online'		=> array('USINT', 0),
						'top_hits'			=> array('UINT', 0),
						'top_hosts'			=> array('UINT', 0),
						'top_in'			=> array('USINT', 0),
						'top_out'			=> array('USINT', 0),
						'top_hits_all'		=> array('UINT', 0),
						'top_hosts_all'		=> array('UINT', 0),
						'top_in_all'		=> array('UINT', 0),
						'top_out_all'		=> array('UINT', 0),
						'top_hits_before'	=> array('USINT', 0),
						'top_hosts_before'	=> array('USINT', 0),
						'top_in_before'		=> array('USINT', 0),
						'top_out_before'	=> array('USINT', 0),
						'top_icon_img'		=> array('VCHAR:30', ''),
						'top_icon_big'		=> array('VCHAR:30', ''),
						'top_icon_small'	=> array('VCHAR:30', ''),
						'top_color1'		=> array('VCHAR:6', ''),
						'top_color2'		=> array('VCHAR:6', ''),
						'top_time_add'		=> array('TIMESTAMP', 0),
						'top_type'			=> array('TINT:1', 0),
					),
					'PRIMARY_KEY'	=> 'top_id',
					'KEYS'			=> array(
						'cat_id'	=> array('INDEX', 'cat_id'),
						'top_hosts'	=> array('INDEX', 'top_hosts'),
					),
				),
				$this->table_prefix . 'rating_cat' => array(
					'COLUMNS'	=> array(
						'cat_id'			=> array('UINT', null, 'auto_increment'),
						'cat_title'			=> array('VCHAR:60', ''),
						'cat_desc'			=> array('VCHAR:256', ''),
						'cat_icon_img'		=> array('VCHAR:30', ''),
						'cat_top_site'		=> array('USINT', 0),
						'cat_top_new'		=> array('USINT', 0),
						'cat_top_type'		=> array('TINT:1', 0),
						'cat_order'			=> array('UINT:5', 0),
					),
					'PRIMARY_KEY'	=> 'cat_id',
					'KEYS'			=> array(
						'cat_order'	=> array('INDEX', 'cat_order'),
					),
				),
				$this->table_prefix . 'rating_icon' => array(
					'COLUMNS'	=> array(
						'file'		=> array('VCHAR:30', 0),
						'type'		=> array('VCHAR:10', 0),
						'cat_id'	=> array('UINT', 0),
						'color'		=> array('VCHAR:6', ''),
						'position'	=> array('VCHAR:6', ''),
					),
				),
				$this->table_prefix . 'rating_click' => array(
					'COLUMNS'	=> array(
						'top_id'		=> array('UINT', 0),
						'top_time'		=> array('TIMESTAMP', 0),
						'top_type'		=> array('VCHAR:3', 0),
						'top_ip'		=> array('VCHAR:16', 0),
						'top_device'	=> array('VCHAR:30', ''),
						'top_prov_id'	=> array('UINT', 0),
						'top_count'		=> array('UINT', 0),
					),
					'PRIMARY_KEY'	=> 'top_id',
					'KEYS'	=> array(
						'top_time'	=> array('INDEX', 'top_time'),
						'top_ip'	=> array('INDEX', 'top_ip'),
					),
				),
				$this->table_prefix . 'rating_online' => array(
					'COLUMNS'	=> array(
						'top_id'		=> array('UINT', 0),
						'top_time'		=> array('TIMESTAMP', 0),
						'top_ip'		=> array('VCHAR:16', 0),
						'top_device'	=> array('VCHAR:30', ''),
						'top_prov_id'	=> array('UINT', 0),
					),
					'PRIMARY_KEY'	=> 'top_id',
					'KEYS'	=> array(
						'top_time'	=> array('INDEX', 'top_time'),
						'top_ip'	=> array('INDEX', 'top_ip'),
					),
				),
				$this->table_prefix . 'rating_hits' => array(
					'COLUMNS'	=> array(
						'top_id'		=> array('UINT', 0),
						'top_time'		=> array('TIMESTAMP', 0),
						'top_ip'		=> array('VCHAR:16', 0),
						'top_device'	=> array('VCHAR:30', ''),
						'top_prov_id'	=> array('UINT', 0),
						'top_count'		=> array('UINT', 0),
					),
					'PRIMARY_KEY'	=> 'top_id',
					'KEYS'	=> array(
						'top_time'	=> array('INDEX', 'top_time'),
						'top_ip'	=> array('INDEX', 'top_ip'),
					),
				),
				$this->table_prefix . 'rating_ip' => array(
					'COLUMNS'	=> array(
						'ip_id'			=> array('UINT', null, 'auto_increment'),
						'ip_start'		=> array('VCHAR:16', 0),
						'ip_finish'		=> array('VCHAR:16', 0),
						'ip_prov_id'	=> array('UINT', 0),
					),
					'PRIMARY_KEY'	=> 'ip_id',
					'KEYS'	=> array(
						'ip_prov_id'	=> array('INDEX', 'ip_prov_id'),
					),
				),
				$this->table_prefix . 'rating_provider' => array(
					'COLUMNS'	=> array(
						'prov_id'		=> array('UINT', null, 'auto_increment'),
						'prov_name'		=> array('VCHAR:16', 0),
						'prov_country'	=> array('VCHAR:50', 0),
						'prov_lang'		=> array('VCHAR:3', 0),
					),
					'PRIMARY_KEY'	=> 'prov_id',
					'KEYS'	=> array(
						'prov_id'	=> array('UNIQUE', 'prov_id'),
					),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_tables'	=> array(
				$this->table_prefix . 'rating',
				$this->table_prefix . 'rating_cat',
				$this->table_prefix . 'rating_icon',
				$this->table_prefix . 'rating_count',
				$this->table_prefix . 'rating_online',
				$this->table_prefix . 'rating_hits',
				$this->table_prefix . 'rating_ip',
				$this->table_prefix . 'rating_provider',
			),
		);
	}

	public function update_data()
	{
		return array(
			// Add configs
			array('config.add', array('top_rating_name', '')),
			array('config.add', array('top_rating_desc', '')),
			array('config.add', array('top_rating_anounce', '')),
			array('config.add', array('top_rating_forum', '0')),
			array('config.add', array('top_rating_type', '0')),
			array('config.add', array('top_rating_new', '1')),
			array('config.add', array('top_per_page', '10')),
			array('config.add', array('top_desc_lenght', '100')),
			array('config.add', array('top_platform_new', '')),
			array('config.add', array('top_platform_time', '')),
			array('config.add', array('top_rating_index', '')),
			array('config.add', array('top_rating_integrate', '')),

			// Add config cron
			array('config.add', array('top_rating_gc', '86400', 0)),
			array('config.add', array('top_rating_last_gc', '0', 1)),
			array('config.add', array('top_rating_between', '1')),

			// Current version
			array('config.add', array('rating_version', '1.0.1')),

			// Add UCP modules
			array('module.add', array('ucp', '', 'UCP_RATING')),
			array('module.add', array('ucp', 'UCP_RATING', array(
				'module_basename'	=> '\bb3top\rating\ucp\ucp_rating',
				'module_langname'	=> 'UCP_RATING_MAIN',
				'module_mode'		=> 'main',
				'module_auth'		=> 'ext_bb3top/rating',
			))),
			array('module.add', array('ucp', 'UCP_RATING', array(
				'module_basename'	=> '\bb3top\rating\ucp\ucp_rating',
				'module_langname'	=> 'UCP_RATING_MANAGE',
				'module_mode'		=> 'manage',
				'module_auth'		=> 'ext_bb3top/rating',
			))),

			// Add ACP modules
			array('module.add', array('acp', '', 'ACP_RATING')),
			array('module.add', array('acp', 'ACP_RATING', 'ACP_RATING_MANAGEMENT')),
			array('module.add', array('acp', 'ACP_RATING_MANAGEMENT', array(
				'module_basename'	=> '\bb3top\rating\acp\acp_rating',
				'module_langname'	=> 'ACP_RATING_SETTINGS',
				'module_mode'		=> 'config',
				'module_auth'		=> 'ext_bb3top/rating && acl_a_board',
			))),
			array('module.add', array('acp', 'ACP_RATING_MANAGEMENT', array(
				'module_basename'	=> '\bb3top\rating\acp\acp_rating',
				'module_langname'	=> 'ACP_RATING_MANAGE_CAT',
				'module_mode'		=> 'manage',
				'module_auth'		=> 'ext_bb3top/rating && acl_a_board',
			))),
			array('module.add', array('acp', 'ACP_RATING_MANAGEMENT', array(
				'module_basename'	=> '\bb3top\rating\acp\acp_rating',
				'module_langname'	=> 'ACP_RATING_COUNTS',
				'module_mode'		=> 'counts',
				'module_auth'		=> 'ext_bb3top/rating && acl_a_board',
			))),
		);
	}

	public function revert_data()
	{
		return array(
			// Remove config
			array('config.remove', array('top_rating_name')),
			array('config.remove', array('top_rating_desc')),
			array('config.remove', array('top_rating_anounce')),
			array('config.remove', array('top_rating_forum')),
			array('config.remove', array('top_rating_type')),
			array('config.remove', array('top_rating_new')),
			array('config.remove', array('top_per_page')),
			array('config.remove', array('top_desc_lenght')),
			array('config.remove', array('top_platform_new')),
			array('config.remove', array('top_platform_time')),
			array('config.remove', array('top_rating_index')),
			array('config.remove', array('top_rating_integrate')),
			array('config.remove', array('top_rating_gc')),
			array('config.remove', array('top_rating_last_gc')),
			array('config.remove', array('top_rating_between')),
			array('config.remove', array('rating_version')),

			// Remove modules
			array('module.remove', array('ucp', '', 'UCP_RATING')),
			array('module.remove', array('ucp', 'UCP_RATING', 'UCP_RATING_MAIN')),
			array('module.remove', array('ucp', 'UCP_RATING', 'UCP_RATING_MANAGE')),
			array('module.remove', array('acp', '', 'ACP_RATING')),
			array('module.remove', array('acp', 'ACP_RATING', 'ACP_RATING_MANAGEMENT')),
			array('module.remove', array('acp', 'ACP_RATING_MANAGEMENT', 'ACP_RATING_SETTINGS')),
			array('module.remove', array('acp', 'ACP_RATING_MANAGEMENT', 'ACP_RATING_MANAGE_CAT')),
			array('module.remove', array('acp', 'ACP_RATING_MANAGEMENT', 'ACP_RATING_COUNTS')),
		);
	}
}
