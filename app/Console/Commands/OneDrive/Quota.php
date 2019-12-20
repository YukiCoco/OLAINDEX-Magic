<?php

namespace App\Console\Commands\OneDrive;

use App\Service\CoreConstants;
use App\Utils\Tool;
use Illuminate\Console\Command;

class Quota extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'od:info';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'OneDriveGraph Info';

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
        $this->info(CoreConstants::LOGO);
        foreach (getOnedriveAccounts() as $key => $account) {
            $this->info('ID：' . $account->id . '  账号：' . $account->account_email);
        }
    }
}
