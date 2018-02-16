<?php

namespace App\Console\Commands;
use App\Models\RefModels;
use Illuminate\Console\Command;
use Symfony\Component\Console\Exception\LogicException;

class LoadModelListCsv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'csv:load_model_list {filePath}';

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

        $csvAttributeKeys = [
           'brand_id' => 1,
           'model_id' => 3,
           'body_id' => 5,
           'generation_id' => 7,
           'engine_id' => 9,
           'parse_link' => 10
        ];
        $filePath = $this->argument('filePath');
        if (!file_exists($filePath)) {
            throw new LogicException(sprintf("File %s doesn't exist", $filePath));
        }

        $fp = file($filePath, FILE_SKIP_EMPTY_LINES);
        $bar = $this->output->createProgressBar(count($fp));
        $bar->setRedrawFrequency(100);
        $bar->start();
        $out = fopen($filePath, 'r');
        while (($line = fgetcsv($out, 0, ',')) !== FALSE) {
            $refModel = new RefModels();
            foreach ($csvAttributeKeys as $arrt => $key) {
                $refModel->{$arrt} = $line[$key] ?? null;
            }
            $refModel->save();
            $bar->advance();
        }
        $bar->finish();
        $this->line("\nAll rows was load");
    }
}
