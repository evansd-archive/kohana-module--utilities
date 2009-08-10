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
		// If a file hasn't been specified for the view we attempt to determine
		// it automatically based on controller and method name
		if($this->content instanceof View AND ! $this->content->kohana_filename)
		{
			// Default view file is views/<controller-name>/<method-name>.php
			$view = $this->_path(Router::$method);

			// For index method we also allow views/<controller>.php if the index.php
			// file doesn't exist
			if(Router::$method == 'index' AND ! Kohana::find_file('views', $view))
			{
				$view = $this->_path();
			}

			$this->content->set_filename($view);
		}

		// If there's a template defined ...
		if($this->template instanceof View)
		{
			// ... render it
			$this->template->render(TRUE);
		}

		// ... otherwise just render the content view
		elseif($this->content instanceof View)
		{
			$this->content->render(TRUE);
		}

	}


	public function _url($segments = '', $protocol = FALSE)
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

		return $protocol !== NULL ? url::site($url, $protocol) : $url;
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
