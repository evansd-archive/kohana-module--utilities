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
	
	
	/*
	*	If no matching method is defined, but an appropriately named view exists
	*	then we load that view as $this->content and render the template
	*/
	public function __call($method, $args)
	{
		// find the view name that corresponds to this method call
		$view = $this->_path().'/'.$method.( count($args) ? '/'.join('/', $args) : '');
		
		if(Kohana::find_file('views', $view, FALSE)) // if it exists ...
		{
			$this->content->set_filename($view); // ... display it
		}
		else
		{
			parent::__call($method, $args);
		}
	}
	
	
	public function _render()
	{
		// if the content view hasn't been set yet ...		
		if($this->content instanceof View AND ! $this->content->kohana_filename)
		{
			// ... use the default one
			$this->content->set_filename($this->_path().'/'.Router::$method);
		}
		
		if($this->template instanceof View) // if there's a template defined ...
		{
			$this->template->render(TRUE); // ... render it 
		}
		elseif($this->content instanceof View)
		{
			$this->content->render(TRUE); // ... otherwise just render the content view
		}
		
	}
	
	
	public function _url($extra = '')
	{
		if ( ! isset($this->_url))
		{
			// Get the original (pre-routed) url segments
			$segments = Router::$segments;
			
			// Get the method name and arguments in a single list
			$arguments = array_merge((array) Router::$method, Router::$arguments);
			
			// Remove the method name and arguments from the original url
			while($argument = array_pop($arguments))
			{	
				if ($argument == end($segments)) array_pop($segments);
			}
			
			// What's left should be the controller url
			$this->_url = join('/', $segments);
		}
		
		if ($extra != '')
		{
			$extra = '/'.trim($extra, '/');
		}
		
		return $this->_url.$extra;
	}
	
	
	public function _path()
	{
		// Router::$controller_path is the full pathname of the controller
		// we just want the bits after the /controllers/ segment
		$path = substr(Router::$controller_path, strrpos(Router::$controller_path, '/controllers/') + 13);
		
		// chop off the last bit - the controller filename - to leave the controller directory
		$path = array_slice(explode('/', $path), 0, -1);
		
		// add on the controller name
		$path[] = Router::$controller;
		
		return join('/', $path);
	}
}
