<?php
/**
*
* @package BB3 TOP Screen
* @copyright (c) BB3.Mobi 2015 Anvar
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace bb3top\screen\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var string phpbb_root_path */
	protected $phpbb_root_path;

	public function __construct(\phpbb\config\config $config, \phpbb\template\template $template, $phpbb_root_path)
	{
		$this->config = $config;
		$this->template = $template;
		$this->phpbb_root_path = $phpbb_root_path;
	}

	static public function getSubscribedEvents()
	{
		return array(
			'bb3top.rating_modify_top_row'	=> 'top_row_template',
			'bb3top.rating_modify_stat_row'	=> 'stat_row_add',
		);
	}

	public function top_row_template($event)
	{
		if ($this->config['top_screen_rating'])
		{
			$row = $event['row'];
			$top_domain = $this->gethost($row['top_url']);
			$top_screen = $this->top_sceenshots($top_domain, 'rate_' . $row['top_id'], $this->config['top_screen_rating']);
			$top_row = array(
				'TOP_SCREEN' => $top_screen['img'],
				'TOP_SCREEN_WIDTH' => $top_screen['width'],
				'TOP_SCREEN_HEIGHT' => $top_screen['height']
			);
			$event['top_row'] += $top_row;
		}
	}

	public function stat_row_add($event)
	{
		if ($this->config['top_screen_stats'])
		{
			$row = $event['row'];
			$top_domain = $this->gethost($row['top_url']);
			$top_screen = $this->top_sceenshots($top_domain, $row['top_id'], $this->config['top_screen_stats']);
			$this->template->assign_vars(array(
				'TOP_SCREEN'		=> $top_screen['img'],
				'TOP_SCREEN_WIDTH'	=> $top_screen['width'],
				'TOP_SCREEN_HEIGHT'	=> $top_screen['height'],
				)
			);
		}
	}

	private function top_sceenshots($site_domain, $id, $size = 200)
	{
		$image_url = $this->phpbb_root_path . 'images/screen/' . $id . $this->config['top_screen_extension'];
		$top_screen_link = $this->config['top_screen_link'];
		$top_screen_link = str_replace('{SCREEN_DOMAIN}', $site_domain, $top_screen_link);
		$top_screen_link = str_replace('{SCREEN_SIZE}', $size, $top_screen_link);

		if (!file_exists($image_url))
		{
			@$fp = fopen($image_url, 'w');
			@fwrite($fp, file_get_contents($top_screen_link));
			@fclose($fp);
		}
		else
		{
			if ($this->config['top_screen_update'] && file_exists($top_screen_link))
			{
				$file_time = @filemtime($image_url);
				if (time() >= $file_time + (86400*$this->config['top_screen_update']))
				{
					@unlink($image_url);
				}
			}
		}

		$imagesize = @getimagesize($image_url);
		$image_board_url = generate_board_url() . '/images/screen/' . $id . $this->config['top_screen_extension'];
		return array(
			'img' => (isset($imagesize[2]) ? $image_board_url : $this->config['top_screen_default']),
			'width' => (isset($imagesize[0]) ? $imagesize[0] : $size),
			'height' => (isset($imagesize[1]) ? $imagesize[1] : $size),
		);
	}

	private function gethost($address)
	{
		$parseurl = parse_url(trim($address));
		return trim($parseurl['host'] ? $parseurl['host'] : array_shift(explode('/', $parseurl['path'], 2)));
	}
}
