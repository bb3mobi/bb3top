<?php

/**
* @package phpBB3.1
* @copyright Anvar (c) 2015 bb3.mobi
*/

namespace bb3top\country\migrations;

class v_1_0_0 extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v310\dev');
	}

	public function update_schema()
	{
		return array(
		);
	}

	public function revert_schema()
	{
		return array(
		);
	}

	public function update_data()
	{
		return array(
			// Add ACP modules
			array('module.add', array('acp', '', 'ACP_RATING')),
			array('module.add', array('acp', 'ACP_RATING', 'ACP_RATING_COUNTRY')),
			array('module.add', array('acp', 'ACP_RATING_COUNTRY', array(
				'module_basename'	=> '\bb3top\country\acp\acp_country',
				'module_langname'	=> 'ACP_COUNTRY_SETTINGS',
				'module_mode'		=> 'manage',
				'module_auth'		=> 'ext_bb3top/country && acl_a_board',
			))),
		);
	}
}
