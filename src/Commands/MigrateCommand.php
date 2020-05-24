<?php

namespace Aposoftworks\LOHM\Commands;

//Laravel

use Aposoftworks\LOHM\Classes\Concrete\ConcreteTable;
use Illuminate\Console\Command;

//Classes
use Aposoftworks\LOHM\Classes\Facades\LOHM;

//Helpers
use Aposoftworks\LOHM\Classes\Helpers\NameBuilder;
use Aposoftworks\LOHM\Classes\Helpers\DirectoryHelper;

class MigrateCommand extends Command {
    protected $queries = 0;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate the current version into the database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $allphpfiles = collect(DirectoryHelper::listFiles(config("lohm.default_table_directory")));

        $allphpfiles->filter(function ($file) {
            return NameBuilder::isMigration($file);
        });

        $this->line("");
		$this->line("Queuing migrations");

        //Add migrations to queue
        $allphpfiles->each(function ($file) {
            LOHM::queue($file);
        });

        $this->line("Running migrations");

        //Run migrations
        LOHM::migrate()->each(function ($migration) {
            $update = $migration();

            $this->queries += $update;
		});

        $this->line("");
        $this->info("Migrations ran successfully");
        $this->line($this->queries." queries ran");
    }
}
