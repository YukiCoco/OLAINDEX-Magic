<?php

namespace App\Console\Commands\OneDrive;

use App\Service\OneDrive;
use Illuminate\Console\Command;

class CreateFolder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'od:mkdir
                            {clientId : Onedrive Id}
                            {name : Folder Name}
                            {remote : Remote Path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create New Folder';

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
        $name = $this->argument('name');
        $remote = $this->argument('remote');
        $clientId = $this->argument('clientId');
        refresh_token(getOnedriveAccount($clientId));
        $response = OneDrive::getInstance(getOnedriveAccount($clientId))->mkdirByPath($name, $remote);
        $this->call('cache:clear');
        $response['errno'] === 0 ? $this->info('Folder Created!') : $this->warn("Failed!\n{$response['msg']} ");
    }
}
