<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Abstract controller class for automatic rendering of template and content view
 * based on controller and action name.
 *
 * @package    Utilities
 * @author     David Evans
 * @copyright  (c) 2009-2010 David Evans
 * @license    MIT-style
 */
abstract class Layout_Controller extends Controller
{
	/**
	 * @var  string  Page template path (transformed into view object by constructor)
	 */
	public $template = 'templates/main';
	
	/**
	 * @var  object  Page content view
	 */
	public $content;
	
	/**
	 * @var  boolean  Auto render template
	 **/
	public $auto_render = TRUE;
	
	// Cached controller path and URL
	private $_path;
	private $_url;
	
	/**
	 * If auto_render is enabled, sets up a template and content view.
	 * 
	 * The template will have a `$content` variable which is bound to the
	 * content view. When the template is rendered it should at some
	 * point render `$content`.
	 * 
	 * The content view will not yet have an associated view file, this
	 * will be assigned when `->render()` is called.
	 * 
	 * @return  void
	 */
	public function __construct()
	{
		parent::__construct();

		if ($this->auto_render === TRUE)
		{
			$this->content = new View();
			
			if ($this->template)
			{
				$this->template = new View($this->template);
				$this->template->bind('content', $this->content);
			}
			
			// Render the template immediately after the controller method
			Event::add('system.post_controller', array($this, '_auto_render'));
		}
	}
	
	/**
	 * If auto_render is enabled and no response has yet been sent, selects
	 * a content view based on the controller and action name, and then 
	 * renders the template.
	 * 
	 * @return  void
	 */
	public function _auto_render()
	{
		if ($this->auto_render === TRUE AND ! ob_get_length())
		{
			$this->render();
		}
	}
	
	/**
	 * Sets an appropriate view file for the content view and then
	 * renders the template (or just renders the content view if no
	 * template is set).
	 * 
	 * @param   string   view to render (relative to controller path)
	 * @param   array    variables to set in the view
	 * @return  void
	 */
	protected function render($view_name = NULL, $data = NULL)
	{
		// Allow caller to pass just the $data argument, omitting
		// the $view_name
		if (func_num_args() == 1 AND is_array($view_name))
		{
			$data = $view_name;
			$view_name = NULL;
		}
		
		// Set the view data
		if ( ! empty($data))
		{
			$this->content->set($data);
		}
		
		// If no view name is supplied and the content view does not
		// already have an associated file we use the default which is:
		// <controller-directory>/<controller-name>/<action>
		if ($view_name === NULL
		    AND $this->content instanceof View
		    AND ! $this->content->kohana_filename)
		{
			$path = $this->add_controller_path(Router::$method);
			
			// Special case for the default action. If the standard view
			// does not exist we use the view at:
			// <controller-directory>/<controller-name>
			if (Router::$method === 'index' AND ! Kohana::find_file('views', $path))
			{
				$path = $this->add_controller_path('');
			}
			
			$this->content->set_filename($path);
		}
		
		// If a view name is supplied, add the controller path to get
		// the full view path, and set is as the content view
		elseif ($view_name !== NULL)
		{
			$this->content->set_filename($this->add_controller_path($view_name));
		}
		
		$view = $this->template ? $this->template : $this->content;
		
		echo is_scalar($view) ? $view : $view->__toString();
	}
	
	/**
	 * Injects a new 'sub-template' into the existing template.
	 * 
	 * Note that any view name you supply will be treated as relative to
	 * the controller path. If you expect your controller to be subclassed
	 * and want to ensure that you always refer to the same view, prefix
	 * the view name with a slash which will stop it being treated as
	 * relative.
	 * 
	 * @param   mixed   view object or view name (relative to controller path)
	 * @param   array   variables to set in the view
	 * @return  object  new 'sub-template' view
	 */
	public function extend_template($view, $data = NULL)
	{
		// If a view name is supplied, transform it into a view object
		if (is_string($view))
		{
			$path = $this->add_controller_path($view);
			$view = new View($path);
		}
		
		// Set the view data
		if ( ! empty($data))
		{
			$view->set($data);
		}
		
		// Find the current innermost template, i.e., the template whose
		// $content variable refers to the $this->content view
		$template = $this->template;
		
		while ($template->content !== $this->content)
		{
			$template = $template->content;
		}
		
		// Inject the new view between the previous innermost template
		// and the ->content view, making it the new innermost
		// template
		$template->bind('content', $view);
		$view->bind('content', $this->content);
		
		return $view;
	}
	
	/**
	 * Adds the current controller directory and controller name to the
	 * supplied path.
	 * 
	 * If the path has a leading slash then the controller path will **not**
	 * be added, but the leading slash will be trimmed.
	 * 
	 * @param   string   Controller-relative path (or path with leading slash)
	 * @return  string   Resource path, for use with `Kohana::find_file`
	 */
	protected function add_controller_path($path)
	{
		// Find the controller path, if not already found and cached
		if ( ! isset($this->_path))
		{
			// Router::$controller_path is the full pathname of the controller
			// we just want the bits after the /controllers/ segment
			$this->_path = substr(Router::$controller_path, strrpos(Router::$controller_path, '/controllers/') + 13);

			// Chop off the last bit (the controller filename) to leave the controller directory
			$this->_path = array_slice(explode('/', $this->_path), 0, -1);

			// Add on the controller name
			$this->_path[] = Router::$controller;

			$this->_path = join('/', $this->_path);
		}

		// If path is already 'absolute' then trim slashes and return it
		if (isset($path[0]) AND $path[0] == '/')
		{
			return trim($path, '/');
		}
		
		// Otherwise, prefix the controller directory and name
		else
		{
			return trim($this->_path.'/'.$path, '/');
		}
	}
	
	/**
	 * Alias of `add_controller_path()`, provided to support legacy API
	 * 
	 * @param   string   Controller-relative path (or path with leading slash)
	 * @return  string   Resource path, for use with `Kohana::find_file`
	 */
	public function _path($path = '')
	{
		return $this->add_controller_path($path);
	}

	/**
	 * Adds the current controller URL to the supplied relative URL.
	 * 
	 * If `$protocol` is NULL, the resulting URL will **not** be passed
	 * through `url::site()`. For any other values, the URL and protocol
	 * will be passed to `url::site()`.
	 * 
	 * The default value of FALSE produces full site URLs with a leading
	 * slash but no protocol or domain, which is what you want most of
	 * the time.
	 * 
	 * @param   string   Controller-relative URL
	 * @param   mixed    URL protocol
	 * @return  string   Full URL
	 */
	public function _url($segments = '', $protocol = FALSE)
	{
		// Find the controller URL, if not already found and cached
		if ( ! isset($this->_url))
		{
			// Get the original (pre-routed) URL segments
			$original = Router::$segments;

			// Get the method name and arguments in a single list
			$arguments = array_merge((array) Router::$method, Router::$arguments);

			// Remove the method name and arguments from the original URL
			while(($argument = array_pop($arguments)) !== NULL)
			{
				if ($argument === end($original)) array_pop($original);
			}

			// What's left should be the controller URL
			$this->_url = join('/', $original);
		}

		$url = rtrim($this->_url.'/'.$segments, '/');

		return $protocol !== NULL ? url::site($url, $protocol) : $url;
	}
}
