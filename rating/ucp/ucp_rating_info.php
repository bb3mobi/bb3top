<?php
/***************************************************************************
*
* @package TOP Reating phpBB
* @version $Id: ucp_reating.php, v 1.0.0
* @copyright (c) 2015 Anvar (apwa.ru)
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
***************************************************************************/

namespace bb3top\rating\ucp;

/**
* @package module_install
*/
class ucp_rating_info
{
	function module()
	{
		return array(
			'filename'	=> '\bb3top\rating\ucp\ucp_rating',
			'title'		=> 'UCP_RATING',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'main'	=> array(
					'title'		=> 'UCP_RATING_MAIN',
					'auth'		=> 'ext_bb3top/rating',
					'cat'		=> array('UCP_RATING')
				),
				'manage'	=> array(
					'title'		=> 'UCP_RATING_MANAGE',
					'auth'		=> 'ext_bb3top/rating',
					'cat'		=> array('UCP_RATING')
				),
			),
		);
	}
}
