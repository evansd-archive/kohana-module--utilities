<?php
class notices_Core
{
	public static function confirm($message, $group = 'default')
	{
		notices::set('heading', $message, $group);
		notices::set('class', 'confirmation', $group);
	}
	
	
	public static function error($message, $group = 'default')
	{
		notices::set('heading', $message, $group);
		notices::set('class', 'error', $group);
	}
	
	
	public static function errors($list, $group = 'default')
	{
		notices::set('list', (array) $list, $group);
		notices::set('class', 'error', $group);
	}
		
	
	public static function set($key, $value, $group = 'default')
	{
		// Get the notices array out of the session, blank array as default
		$notices = Session::instance()->get('notices', array());
		
		if( ! isset($notices[$group]))
		{
			$notices[$group] = array();
		}
		
		// If $value is an array we merge it with the existing values
		if(is_array($value)) 
		{
			// Make sure the appropriate key is set and is an array
			$notices[$group][$key] = isset($notices[$group][$key]) ? (array) $notices[$group][$key] : array();
			$value = array_values($value);
			$notices[$group][$key] = array_merge($notices[$group][$key], $value);
		}
		else
		{
			$notices[$group][$key] = $value;
		}
		
		Session::instance()->set('notices', $notices);
	}
	
	
	public static function render($group = 'default')
	{
		if ( ! notices::session_exists()) return;
		
		$notices = Session::instance()->get_once('notices');
		
		if ( ! empty($notices) AND ! empty($notices[$group]))
		{
			$view = empty($notices[$group]['view']) ? 'notices' : $notices[$group]['view'];
			return View::factory($view, $notices[$group])->render();
		}
	}
	
	// Check whether session exists without creating one
	public static function session_exists()
	{
		if ( ! empty($_SESSION['session_id']))
		{
			return TRUE;
		}
		else
		{
			$session_name = Kohana::config('session.name');
			return ( ! empty($_COOKIE[$session_name]) OR ! empty($_GET[$session_name]));
		}
	}
}
