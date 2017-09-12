<?php

namespace App\Console\Commands;

use App\Parser\ParsersConfig;
use Illuminate\Console\Command;

class getConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parser:config';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get parser config';

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
        $parser = new ParsersConfig();
        $this->line($parser->getTextConfig());

    }
}
