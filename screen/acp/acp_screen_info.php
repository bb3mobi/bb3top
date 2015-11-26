<?php
/**
*
* @package BB3 TOP Screen
* @copyright BB3.MOBi (c) 2015 Anvar http://apwa.ru
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace bb3top\screen\acp;

class acp_screen_info
{
	function module()
	{
		return array(
			'filename'	=> '\bb3top\screen\acp\acp_screen',
			'title'		=> 'ACP_RATING_SCREEN',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'default'	=> array('title' => 'ACP_SCREEN_SETTINGS', 'auth' => 'ext_bb3top/screen && acl_a_board', 'cat' => array('ACP_RATING_SCREEN')),
			),
		);
	}
}
