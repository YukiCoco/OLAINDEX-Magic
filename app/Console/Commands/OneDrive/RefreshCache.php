<?php

namespace App\Console\Commands\OneDrive;

use App\Service\OneDrive;
use App\Utils\Tool;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Artisan;
use Cache;
use Illuminate\Support\Facades\Artisan as FacadesArtisan;
use Illuminate\Support\Facades\Cache as FacadesCache;

class RefreshCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'od:cache {clientId : Onedrive Id} {path? : Target path to cache}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cache Dir';

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
        $path = $this->argument('path');

        $this->getRecursive(Tool::getOriginPath($path));
    }

    /**
     * @param $path
     *
     * @return mixed
     * @throws \ErrorException
     */
    public function getChildren($path)
    {
        $clientId = $this->argument('clientId');
        refresh_token(getOnedriveAccount($clientId));
        $response = OneDrive::getInstance(getOnedriveAccount($clientId))->getItemListByPath(
            $path,
            '?select=id,eTag,name,size,lastModifiedDateTime,file,image,folder,'
            . 'parentReference,@microsoft.graph.downloadUrl&expand=thumbnails'
        );
        return $response['errno'] === 0 ? $response['data'] : null;
    }

    /**
     * @param $path
     *
     * @throws \ErrorException
     */
    public function getRecursive($path)
    {
        set_time_limit(0);
        $this->info($path);
        $data = $this->getChildren($path);
        if (is_array($data)) {
            FacadesCache::put(
                'one:list:' . $path,
                $data,
                setting('expires')
            );
        } else {
            exit('Cache Error!');
        }
        foreach ((array)$data as $item) {
            if (Arr::has($item, 'folder')) {
                $this->getRecursive($path . $item['name'] . '/');
            }
        }
    }
}
