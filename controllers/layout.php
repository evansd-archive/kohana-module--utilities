<?php
abstract class Layout_Controller extends Controller
{
	public $template = 'templates/main';

	public $content;

	private $_path;
	private $_url;


	public function __construct()
	{
		parent::__construct();

		$this->content = new View;

		if($this->template)
		{
			$this->template = new View($this->template);
			$this->template->bind('content', $this->content);
		}

		// Render the template immediately after the controller method
		Event::add('system.post_controller', array($this, '_render'));
	}


	public function _render()
	{
		// If $content is a view object but doesn't have a view file defined ...
		if($this->content instanceof View AND ! $this->content->kohana_filename)
		{
			// ... use the default one based on controller and method name
			$this->content->set_filename($this->_path(Router::$method));
		}

		if($this->template instanceof View) // If there's a template defined ...
		{
			$this->template->render(TRUE); // ... render it
		}
		elseif($this->content instanceof View)
		{
			$this->content->render(TRUE); // ... otherwise just render the content view
		}

	}


	public function _url($segments = '', $protocol = NULL)
	{
		// Find the controller URL, if not already found and chached
		if ( ! isset($this->_url))
		{
			// Get the original (pre-routed) URL segments
			$original = Router::$segments;

			// Get the method name and arguments in a single list
			$arguments = array_merge((array) Router::$method, Router::$arguments);

			// Remove the method name and arguments from the original URL
			while($argument = array_pop($arguments))
			{
				if ($argument == end($original)) array_pop($original);
			}

			// What's left should be the controller URL
			$this->_url = join('/', $original);
		}

		if ($segments != '')
		{
			$segments = '/'.trim($segments, '/');
		}

		$url = $this->_url.$segments;

		return $protocol === NULL ? $url : url::site($url, $protocol);
	}


	public function _path($path = '')
	{
		// Find the controller path, if not already found and cached
		if ( ! isset($this->_path))
		{
			// Router::$controller_path is the full pathname of the controller
			// we just want the bits after the /controllers/ segment
			$this->_path = substr(Router::$controller_path, strrpos(Router::$controller_path, '/controllers/') + 13);

			// Chop off the last bit - the controller filename - to leave the controller directory
			$this->_path = array_slice(explode('/', $this->_path), 0, -1);

			// Add on the controller name
			$this->_path[] = Router::$controller;

			$this->_path = join('/', $this->_path);
		}

		if ($path != '')
		{
			$path = '/'.trim($path, '/');
		}

		return $this->_path.$path;
	}
}
