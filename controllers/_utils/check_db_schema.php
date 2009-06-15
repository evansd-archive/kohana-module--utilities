<?php
class Check_DB_Schema_Controller extends Controller
{
	public function __construct()
	{
		// This controller should only be run from the command line
		if(PHP_SAPI != 'cli') Event::run('system.404');

		parent::__construct();
	}


	public function index()
	{
		$db = array();

		foreach(array('development', 'production') as $type)
		{
			$db[$type] = array();

			$conn = new Database($type);

			foreach($conn->list_tables() as $table)
			{
				$db[$type][$table] = $conn->list_fields($table);
			}
		}

		// If the databases are identical, exit with success status
		if($db['development'] === $db['production']) exit(0);

		echo "Database schema mismatch\n";

		// Exit with error status
		exit(1);

	}


	protected function diff(array $arr1, array $arr2)
	{
		return array_merge(array_diff_assoc($arr1, $arr2), array_diff_assoc($arr2, $arr1));
	}
}
