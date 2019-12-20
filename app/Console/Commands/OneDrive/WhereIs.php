<?php
namespace App\Console\Commands\OneDrive;

use App\Service\OneDrive;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class WhereIs extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'od:whereis {clientId : Onedrive Id} {id : Item ID}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Find The Item\'s Remote Path';

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
        $id = $this->argument('id');
        $clientId = $this->argument('clientId');
        refresh_token(getOnedriveAccount($clientId));
        $response = OneDrive::getInstance(getOnedriveAccount($clientId))->itemIdToPath($id);
        if ($response['errno'] === 0) {
            $this->info(Arr::get($response, 'data.path'));
        } else {
            $this->error($response['msg']);
            exit;
        }
    }
}
