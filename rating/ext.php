<?php

// this file is not really needed, when empty it can be ommitted
// however you can override the default methods and add custom
// installation logic

namespace bb3top\rating;

class ext extends \phpbb\extension\base
{
	protected $add_ons = array(
		'bb3top/country',
		'bb3top/screen',
	);
	/**
	* Single enable step that installs any included migrations
	*
	* @param mixed $old_state State returned by previous call of this method
	* @return mixed Returns false after last step, otherwise temporary state
	*/
	function enable_step($old_state)
	{
		switch ($old_state)
		{
			case '': // Empty means nothing has run yet
				// Disable list of official extensions
				$extensions = $this->container->get('ext.manager');
				$configured = $extensions->all_disabled();
				//var_dump($configured);
				foreach ($this->add_ons as $var)
				{
					if (array_key_exists($var, $configured))
					{
						$extensions->enable($var);
					}
				}
				return true;
			break;
			default:
				// Run parent enable step method
				return parent::enable_step($old_state);
			break;
		}
	}
	/**
	* Single disable step that does nothing
	*
	* @param mixed $old_state State returned by previous call of this method
	* @return mixed Returns false after last step, otherwise temporary state
	*/
	function disable_step($old_state)
	{
		switch ($old_state)
		{
			case '': // Empty means nothing has run yet
				// Disable list of official extensions
				$extensions = $this->container->get('ext.manager');
				foreach ($this->add_ons as $var)
				{
					$extensions->disable($var);
				}
				return true;
			break;
			default:
				// Run parent disable step method
				return parent::disable_step($old_state);
			break;
		}
	}
}
