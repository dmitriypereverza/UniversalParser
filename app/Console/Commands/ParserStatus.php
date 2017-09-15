<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ParserStatus as Parser;

class ParserStatus extends Command
{
    protected $signature = 'parser:status {--on} {--off}';

    protected $description = 'ParsersConfig start';

    function __construct()
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
        $actionOn = $this->option('on');
        $actionOff = $this->option('off');
        if ($actionOn and $actionOff) {
            $this->error('Do not use --on and --off options together!');
            return;
        }

        $parserEnable = Parser::isEnable();
        if ($actionOn) {
            !$parserEnable && Parser::enable();
        }
        elseif ($actionOff) {
            $parserEnable && Parser::disable();
        }
        $this->info(sprintf('Parser: %s', Parser::isEnable() ? 'Active' : 'Disable'));
    }
}