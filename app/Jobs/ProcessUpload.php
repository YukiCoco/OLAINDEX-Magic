<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Service\OneDrive;
use Illuminate\Support\Arr;
use App\Utils\Tool;
use App\Models\OfflineDlFile;
use Illuminate\Support\Facades\Log;

class ProcessUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $payload;
    protected $result;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    /**
     * @description:
     * @param array local remote chuck clientId
     * @return:
     */
    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $local = $this->payload['local'];
        $remote = $this->payload['remote'];
        $chuck = $this->payload['chuck']?: 3276800;
        $clientId = $this->payload['clientId'];
        $gid = $this->payload['gid'];
        $account = getOnedriveAccount($clientId);
        if(!file_exists($local)){
            $this->result['errno'] = 1;
            $this->result['message'] = '文件不存在或无权限访问';
            return $this->result; //也有可能是因为权限问题。
        }
        refresh_token($account);
        $file_size = OneDrive::getInstance($account)->readFileSize($local);
        if ($file_size < 4194304) {
            return $this->upload($local, $remote,$gid);
        }
        return $this->uploadBySession($local, $remote, $gid, $chuck);
    }

    /**
     * @param $local
     * @param $remote
     *
     * @throws \ErrorException
     */
    public function upload($local, $remote,$gid)
    {
        $uploadfile = OfflineDlFile::where('gid',$gid)->first();
        $uploadfile->status = 'uploading';
        $content = file_get_contents($local);
        $file_name = basename($local);
        $clientId = $this->payload['clientId'];
        $response = OneDrive::getInstance(getOnedriveAccount($clientId))->uploadByPath($remote . $file_name, $content);
        $uploadfile->name = basename($local);
        //$uploadfile->path = $local;
        $uploadfile->status = 'success';
        $uploadfile->progress = '100%';
        $uploadfile->save();
        unlink($local);
        return $response;
    }

    /**
     * @param     $local
     * @param     $remote
     * @param int $chuck
     *
     * @throws \ErrorException
     */
    public function uploadBySession($local, $remote,$gid, $chuck = 3276800)
    {
        ini_set('memory_limit', '-1');
        $clientId = $this->payload['clientId'];
        $account = getOnedriveAccount($clientId);
        $file_size = OneDrive::getInstance($account)->readFileSize($local);
        $file_name = basename($local);
        $target_path = Tool::getAbsolutePath($remote);
        $url_response = OneDrive::getInstance($account)->createUploadSession($target_path . $file_name);
        //保存进度
        $uploadfile = OfflineDlFile::where('gid',$gid)->first();
        $uploadfile->name = $file_name;
        //$uploadfile->path = $local;

        if ($url_response['errno'] === 0) {
            $url = Arr::get($url_response, 'data.uploadUrl');
        } else {
            $uploadfile->error = $url_response['msg'];
            exit;
        }
        $uploadfile->status = 'uploading';
        $done = false;
        $offset = 0;
        $length = $chuck;
        $firstTime = gettimeofday()['sec'];
        while (!$done) {
            $retry = 0;
            $response = OneDrive::getInstance($account)->uploadToSession(
                $url,
                $local,
                $offset,
                $length
            );
            if ($response['errno'] === 0) {
                $data = $response['data'];
                if (!empty($data['nextExpectedRanges'])) {
                    $ranges = explode('-', $data['nextExpectedRanges'][0]);
                    $offset = (int)$ranges[0];
                    $status = @floor($offset / $file_size * 100) . '%';
                    $uploadfile->progress = $status;
                    $timeSpend = gettimeofday()['sec'] - $firstTime;
                    $uploadfile->speed = Tool::convertSize(floor($offset / $timeSpend));
                    $done = false;
                } elseif (!empty($data['@content.downloadUrl'])
                    || !empty($data['id'])
                ) {
                    $uploadfile->progress = '100%';
                    $done = true; //完成
                } else {
                    $retry++;
                    if ($retry <= 3) {
                        sleep(10);
                    } else {
                        $uploadfile->error = 'Upload Failed!';
                        Log::error("Upload Failed!");
                        OneDrive::getInstance($account)->deleteUploadSession($url);
                        break;
                    }
                }
            } else {
                return $response;
                OneDrive::getInstance($account)->deleteUploadSession($url);
                break;
            }
            $uploadfile->save(); //保存任务
        }
        $uploadfile->status = 'success';
        $uploadfile->save();
        unlink($local);
        return $response;
    }
}
