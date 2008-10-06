<?php
class Sortable_Core
{

	protected $order = array();
	protected $query_string = 'orderby';
	protected $url;
	protected $templates = array();
	
	
	
	public function factory($config = array())
	{
		return new Sortable($config);
	}
	
	
	public function __construct($config = array())
	{
		$config += (array) Kohana::config('sortable', FALSE, FALSE);
		
		// Assign config values to the object
		foreach ($config as $key => $value)
		{
			if (property_exists($this, $key))
			{
				$this->$key = $value;
			}
		}
		
			
		// Extract current ordering
		$this->current_ordering = isset($_GET[$this->query_string]) ?  $_GET[$this->query_string] : '';

		// Insert {order} placeholder
		$_GET[$this->query_string] = '{order}';

		// Create full URL
		$this->url = url::site(Router::$current_uri).'?'.str_replace('%7Border%7D', '{order}', http_build_query($_GET));

		// Reset ordering
		$_GET[$this->query_string] = $this->current_ordering;
		
		foreach(explode(';', $_GET[$this->query_string]) as $column)
		{
			if (($column = trim($column)) == '')
			{
				continue;
			}
			
			if (substr($column, -5) == ':desc')
			{
				$column = substr($column, 0, -5);
				$direction = 'DESC';
			}
			else
			{
				$direction = 'ASC';
			}
			
			if (FALSE AND is_array($this->allowed_columns) AND ! in_array($column, $this->allowed_columns))
			{
				continue;
			}
			
			if ( ! isset($this->order[$column]))
			{
				$this->order[$column] = $direction;
			}
			
		}
	}
	
	
	public function toggle($title, $column = NULL)
	{
		// If no column is supplied, guess it from the title
		if ($column === NULL)
		{
			$column = str_replace(' ', '_', strtolower($title));
		}
		
		if (isset($this->order[$column]))
		{
			$direction = ($this->order[$column] === 'DESC') ? 'ASC' : 'DESC';
			$template = $this->templates[$this->order[$column]];
		}
		else
		{
			$direction = 'ASC';
			$template = $this->templates['NULL'];
		}
		
		$template = str_replace(array('{url}', '{title}'), array($this->build_url($column, $direction), $title), $template);
		
		return $template;
	
	}
	
	
	public function build_url($column = NULL, $direction = NULL)
	{
		$query = array();
		
		if ($column !== NULL)
		{
			$query[] = $column.(($direction == 'DESC') ? ':desc' : '');
		}
		
		foreach($this->order as $key => $value)
		{
			if ($key !== $column)
			{
				$query[] = $key.(($value == 'DESC') ? ':desc' : '');
			}
		}
		

		$query = join(';', $query);
		
		return str_replace('{order}', $query, $this->url);
	}
	
	
	/**
	 * Magically gets a sortable variable.
	 *
	 * @param   string  variable key
	 * @return  mixed   variable value if the key is found
	 * @return  void    if the key is not found
	 */
	public function __get($key)
	{
		if (isset($this->$key))
			return $this->$key;
	}
	
}
