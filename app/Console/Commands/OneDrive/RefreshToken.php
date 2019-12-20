<?php
/*
 * @Author: your name
 * @Date: 2019-12-17 19:36:27
 * @LastEditTime : 2019-12-20 17:47:43
 * @LastEditors  : Please set LastEditors
 * @Description: In User Settings Edit
 * @FilePath: /onedrive/app/Console/Commands/OneDrive/RefreshToken.php
 */

namespace App\Console\Commands\OneDrive;

use App\Helpers\Tool;
use App\Http\Controllers\OauthController;
use Illuminate\Console\Command;

class RefreshToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'od:refresh {clientId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh Token';

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
        refresh_token(getOnedriveAccount($clientId));
    }
}
