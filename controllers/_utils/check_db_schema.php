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
		if($db['development'] === $db['production'])
		{
			exit(0);
		}
		// Otherwise exit with error status
		else
		{
			echo "Database schema mismatch\n";
			exit(1);
		}
	}
}
