<?php
/***************************************************************************
*
* @package TOP Reating phpBB
* @version $Id: acp_country.php, v 1.0.0
* @copyright (c) 2015 Anvar (apwa.ru)
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
***************************************************************************/

namespace bb3top\country\acp;

/**
* @package module_install
*/
class acp_country_info
{
	function module()
	{
		return array(
			'filename'		=> '\bb3top\country\acp\acp_country',
			'title'			=> 'ACP_COUNTRY_MANAGEMENT',
			'version'		=> '1.0.0',
			'modes'			=> array(
				'manage'	=> array(
					'title'			=> 'ACP_RATING_SETTINGS',
					'auth'			=> 'ext_bb3top/country && acl_a_board',
					'cat' 			=> array('ACP_COUNTRY_MANAGEMENT'),
				),
			),
		);
	}
}
