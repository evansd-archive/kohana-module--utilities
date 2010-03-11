<?php
class Pagination extends Pagination_Core
{
	/**
	 * @var  string  Optional hash fragment to be added to end of URL e.g, #results
	 */
	protected $fragment;
	
	/**
	 * @var  array   Any extra settings to be passed to the pagination view
	 */
	protected $extras = array();

	/**
	 * Accepts an ORM instance with query parameters set and returns the
	 * current page of results e.g.,
	 * 
	 *    $query = ORM::factory('customer')->where('status', 'approved);
	 *    $pages = new Pagination();
	 *    $results = $pages->paginate($query);
	 * 
	 * @param    object  ORM
	 * @return   object  ORM_Iterator
	 */
	public function paginate(ORM $query)
	{
		// Work out what page we're currently on and set the SQL offsets
		// appropriately.
		//
		// This is necessary because if you create a Pagination object
		// without specifying the total pages, then current page is
		// always set to 1.
		$this->determine_current_page()

		// Find the current page of results
		$results = $query->find_all($this->items_per_page, $this->sql_offset);

		// Set the correct total_item count
		$this->total_items = $query->count_last_query();

		// Re-initialize to ensure all values are set correctly
		$this->initialize();

		return $results;
	}
	
	/**
	 * Determines the current page, and sets the SQL offsets, in exactly
	 * the same way as the original pagination library, except that this
	 * method can be run **before** we know the total number of pages.
	 * 
	 * @return   void
	 */
	public function determine_current_page()
	{
		if ($this->query_string !== '')
		{
			$this->current_page = isset($_GET[$this->query_string]) ? (int) $_GET[$this->query_string] : 1;
		}
		else
		{
			$this->current_page = (int) URI::instance()->segment($this->uri_segment);
		}

		$this->current_page = $current_page = max(1, $this->current_page);

		// Reset the sql offset based on current page
		$this->sql_offset = (int) ($this->current_page - 1) * $this->items_per_page;
	}

	/**
	 * Extends the original `initialize()` method to append the hash
	 * fragment to the URL.
	 * 
	 * @return   void
	 */
	public function initialize($config = array())
	{
		parent::initialize($config);
		
		// Add the fragment to the URL, if set
		if( ! empty($this->fragment))
		{
			$this->url .= '#'.ltrim($this->fragment, '#');
		}
	}

}
