<?php
/***************************************************************************
*
* @package TOP Reating phpBB
* @version $Id: acp_reating.php, v 1.0.0
* @copyright (c) 2015 Anvar (apwa.ru)
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
***************************************************************************/

namespace bb3top\rating\acp;

/**
* @package module_install
*/
class acp_rating_info
{
	function module()
	{
		return array(
			'filename'		=> '\bb3top\rating\acp\acp_rating',
			'title'			=> 'ACP_RATING_MANAGEMENT',
			'version'		=> '1.0.0',
			'modes'			=> array(
				'config'	=> array(
					'title'			=> 'ACP_RATING_SETTINGS',
					'auth'			=> 'ext_bb3top/rating && acl_a_board',
					'cat' 			=> array('ACP_RATING_MANAGEMENT'),
				),
				'manage'	=> array(
					'title'			=> 'ACP_RATING_MANAGE_CAT',
					'auth'			=> 'ext_bb3top/rating && acl_a_board',
					'cat' 			=> array('ACP_RATING_MANAGEMENT'),
				),
				'counts'	=> array(
					'title'			=> 'ACP_RATING_COUNTS',
					'auth'			=> 'ext_bb3top/rating && acl_a_board',
					'cat' 			=> array('ACP_RATING_MANAGEMENT'),
				),
			),
		);
	}
}
