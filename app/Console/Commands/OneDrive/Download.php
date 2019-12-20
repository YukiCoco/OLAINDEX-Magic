<?php

namespace App\Console\Commands\OneDrive;

use App\Service\OneDrive;
use Illuminate\Console\Command;

class Download extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'od:download
                            {clientId : Onedrive Id}
                            {remote? : Download Remote Path}
                            {--id= : Download Remote File ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download File';

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
        $remote = $this->argument('remote');
        $id = $this->option('id');
        $clientId = $this->argument('clientId');
        refresh_token(getOnedriveAccount($clientId));
        if ($id) {
            $response = OneDrive::getInstance(getOnedriveAccount($clientId))->getItem($id);
        } else {
            if (empty($remote)) {
                exit('Parameters Missing!');
            }
            $response = OneDrive::getInstance(getOnedriveAccount($clientId))->getItemByPath($remote);
        }
        if ($response['errno'] === 0) {
            $download = $response['data']['@microsoft.graph.downloadUrl'] ??
                exit('404 NOT FOUND');
            $this->info("Download Link:\n{$download}");
        } else {
            $this->warn("Failed!\n{$response['msg']} ");
        }
    }
}
