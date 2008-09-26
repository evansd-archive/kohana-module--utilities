<?php
class Pagination extends Pagination_Core
{
	public function paginate(ORM $model)
	{
		// re-extract current page
		// this will be have been set to 1 if the original total_items count was 0
		if ($this->query_string !== '')
		{
			$this->current_page = isset($_GET[$this->query_string]) ? (int) $_GET[$this->query_string] : 1;
		}
		else
		{
			$this->current_page = (int) URI::instance()->segment($this->uri_segment);
		}
		
		$this->current_page = max(1, $this->current_page);
		
		// reset the sql offset based on current page
		$this->sql_offset = (int) ($this->current_page - 1) * $this->items_per_page;
		
		// find the current page of results
		$results = $model->find_all($this->items_per_page, $this->sql_offset);
		
		// set the correct total_item count
		$thsis->total_items = $model->count_last_query();
		
		// re-initialize to ensure all values are set correctly
		$this->initialize();
		
		return $results;
	}
	
}
