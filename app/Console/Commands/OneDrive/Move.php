<?php

namespace App\Console\Commands\OneDrive;

use App\Service\OneDrive;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class Move extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'od:mv
                            {clientId : Onedrive Id}
                            {origin : Origin Path}
                            {target : Target Path}
                            {--rename= : Rename}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Move Item';

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
     * @throws \ErrorException
     */
    public function handle()
    {
        $clientId = $this->argument('clientId');
        $this->info('开始移动...');
        $this->info('Please waiting...');
        $origin = $this->argument('origin');
        $_origin = OneDrive::getInstance(getOnedriveAccount($clientId))->pathToItemId($origin);
        $origin_id = $_origin['errno'] === 0 ? Arr::get($_origin, 'data.id')
            : exit('Origin Path Abnormal');
        $target = $this->argument('target');
        $_target = OneDrive::getInstance(getOnedriveAccount($clientId))->pathToItemId($target);
        $target_id = $_origin['errno'] === 0 ? Arr::get($_target, 'data.id')
            : exit('Target Path Abnormal');
        $rename = $this->option('rename') ?: '';
        $response = OneDrive::getInstance(getOnedriveAccount($clientId))->move($origin_id, $target_id, $rename);
        $response['errno'] === 0 ? $this->info('Move Success!')
            : $this->warn("Failed!\n{$response['msg']} ");
    }
}
