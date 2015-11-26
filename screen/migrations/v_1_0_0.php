<?php
/**
*
* @package BB3 TOP SCREEN
* @copyright BB3.MOBi (c) 2015 Anvar http://apwa.ru
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace bb3top\screen\migrations;

class v_1_0_0 extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v31x\v313');
	}

	public function update_data()
	{
		return array(
			// Add configs
			array('config.add', array('top_screen_stats', 180)),
			array('config.add', array('top_screen_rating', 60)),
			array('config.add', array('top_screen_update', 30)),
			array('config.add', array('top_screen_link', 'http://mini.s-shot.ru/1300x960/{SCREEN_SIZE}/jpeg/?{SCREEN_DOMAIN}')),
			array('config.add', array('top_screen_extension', '.jpg')),
			array('config.add', array('top_screen_default', '')),

			// Add ACP modules
			array('module.add', array('acp', '', 'ACP_RATING')),
			array('module.add', array('acp', 'ACP_RATING', 'ACP_RATING_SCREEN')),
			array('module.add', array('acp', 'ACP_RATING_SCREEN', array(
				'module_basename'	=> '\bb3top\screen\acp\acp_screen',
				'module_langname'	=> 'ACP_SCREEN_SETTINGS',
				'module_mode'		=> 'default',
				'module_auth'		=> 'ext_bb3top/screen && acl_a_board',
			))),
		);
	}
}