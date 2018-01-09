<?php

namespace App\Console\Commands;

use App\Models\Links;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class TreeCsv extends Command
{
    use DispatchesJobs;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tree:csv {fileName}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

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
        $fileName = $this->argument('fileName');
        $data = Links::getDownloadedLinks();
        if (count($data) == 0) {
            $this->warn('Downloaded links not found');
            return;
        }
        $filePath = 'storage/' . $fileName;
        $out = fopen($filePath, 'w');
        fputcsv($out, array_keys($data[1]));
        foreach($data as $line)
        {
            fputcsv($out, $line);
        }
        fclose($out);
        $this->line('CSV file was create in: ' . $filePath);

    }
}
