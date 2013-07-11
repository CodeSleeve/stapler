<?php namespace Codesleeve\Stapler\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use DB, View, File;

class FastenCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'stapler:fasten';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Generate a migration for adding stapler file fields to a database table.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		try {
			# see if this throws an error or not
			DB::table($this->argument('table'))->first();

			# create the migration file since we found the table in database
			$this->createMigration();

		} catch(Exception $e) {
			$this->error("No such table found in database: " . $this->argument('table'));
		}
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('table', InputArgument::REQUIRED, 'The name of the database table the file fields will be added to.'),
			array('attachment', InputArgument::REQUIRED, 'The name of the corresponding stapler attachment.'),
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array();
	}

	/**
	 * Create a new migration.
	 *
	 * @return void
	 */
	public function createMigration()
	{
		$data = ['table' => $this->argument('table'), 'attachment' => $this->argument('attachment')];
		$prefix = date('Y_m_d_His');
		$path = app_path() . '/database/migrations';

		if (!is_dir($path)) mkdir($path);

		$fileName  = $path . '/' . $prefix . '_add_' . $data['attachment'] . '_fields_to_' . $data['table'] . '_table.php';
		$data['className'] = 'Add' . ucfirst($data['attachment']) . 'FieldsTo' . ucfirst($data['table']) . 'Table';

		# generate the migration file
		$migration = View::make('stapler::migration', $data)->render();

		# save the migration file
		File::put($fileName, $migration);
		$this->info("created migration: $fileName");
	}

}