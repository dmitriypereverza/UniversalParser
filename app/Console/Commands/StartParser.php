<?php

namespace App\Console\Commands;

use App\Parser\Parser;
use App\Parser\Scheduler;
use Illuminate\Console\Command;

class StartParser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parser:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parser start';

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
     * @return mixed
     */
    public function handle()
    {
        $this->line('Starting...');

        Scheduler::run(new Parser());
    }
}
