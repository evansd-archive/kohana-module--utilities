<?php
class notices_Core
{
	public static function confirm($message)
	{
		notices::set('heading', $message);
		notices::set('class', 'confirmation');
	}
	
	
	public static function error($message)
	{
		notices::set('heading', $message);
		notices::set('class', 'error');
	}
	
	
	public static function errors($list)
	{
		notices::set('list', (array) $list);
		notices::set('class', 'error');
	}
		
	
	public static function set($key, $value)
	{
		// Get the notices array out of the session, blank array as default
		$notices = Session::instance()->get('notices', array());
		
		// If $value is an array we merge it with the existing values
		if(is_array($value)) 
		{
			$notices[$key] = isset($notices[$key]) ? (array) $notices[$key] : array();
			$value = array_values($value);
			$notices[$key] = array_merge($notices[$key], $value);
		}
		else
		{
			$notices[$key] = $value;
		}
		
		Session::instance()->set('notices', $notices);
	}
	
	
	public static function render($view = FALSE)
	{
		// Check for existence of session to avoid creating one if not required
		if (empty($_SESSION['session_id']) AND empty($_COOKIE[$name = Kohana::config('session.name')]) AND empty($_GET[$name]))
		{
			return;
		}
		
		$notices = Session::instance()->get_once('notices');
		
		if (empty($notices)) return;
		
		$view = empty($notices['view']) ? ($view ? $view : 'notices') : $notices['view'];
		
		return View::factory($view, $notices)->render();
	}
}
