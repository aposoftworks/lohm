<?php

namespace Aposoftworks\LOHM\Commands;

//Laravel
use Illuminate\Console\Command;

//Classes
use Aposoftworks\LOHM\Classes\Facades\LOHM;

//Helpers
use Aposoftworks\LOHM\Classes\Helpers\NameBuilder;
use Aposoftworks\LOHM\Classes\Helpers\DatabaseHelper;
use Aposoftworks\LOHM\Classes\Helpers\DirectoryHelper;

class DiffCommand extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analyze:diff';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Analyze the differences between data from the database and the current migration';

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
    public function handle () {
        $this->line("");

        //Get all files
        $allphpfiles = collect(DirectoryHelper::listFiles(config("lohm.default_table_directory")));

        $allphpfiles->filter(function ($file) {
            return NameBuilder::isMigration($file);
        });

        //Print the migrations
        $allphpfiles->each(function ($file) {
            LOHM::queue($file);
        });

        $queues = LOHM::queues();

        for ($i = 0; $i < count($queues); $i++) {
            DatabaseHelper::diffTable($this, $queues[$i]["table"]);
        }
    }
}
