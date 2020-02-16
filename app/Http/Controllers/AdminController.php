<?php

namespace App\Http\Controllers;

use App\Utils\Tool;
use App\Jobs\RefreshCache;
use App\Models\OnedriveAccount;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Artisan;
use Auth;
use Hash;
use Illuminate\Support\Facades\Log;
use App\Jobs\ProcessUpload;
use App\Models\OfflineDlFile;
use App\Utils\Aria2;
use ErrorException;
use Illuminate\Support\Arr;

/**
 * 后台管理操作
 * Class AdminController
 *
 * @package App\Http\Controllers
 */
class AdminController extends Controller
{
    /**
     * ManageController constructor.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'verify.installation'])->except('offlineUpload');
    }

    /**
     * 基础设置
     *
     * @param Request $request
     * @return Factory|RedirectResponse|View
     */
    public function basic(Request $request)
    {
        if (!$request->isMethod('post')) {
            return view(config('olaindex.theme') . 'admin.basic');
        }
        $data = $request->except('_token');
        Setting::batchUpdate($data);
        Tool::showMessage('保存成功！');

        return redirect()->back();
    }

    /**
     * 显示设置
     *
     * @param Request $request
     * @return Factory|RedirectResponse|View
     */
    public function show(Request $request)
    {
        if (!$request->isMethod('post')) {
            return view(config('olaindex.theme') . 'admin.show');
        }
        $data = $request->except('_token');

        Setting::batchUpdate($data);
        Tool::showMessage('保存成功！');

        return redirect()->back();
    }

    /**
     * 密码设置
     *
     * @param Request $request
     *
     * @return Factory|RedirectResponse|View
     */
    public function profile(Request $request)
    {
        if (!$request->isMethod('post')) {
            return view(config('olaindex.theme') . 'admin.profile');
        }
        /* @var $user User */
        $user = Auth::user();
        $oldPassword = $request->get('old_password');
        $password = $request->get('password');
        $passwordConfirm = $request->get('password_confirm');

        if (!Hash::check($oldPassword, $user->password)) {
            Tool::showMessage('请确保原密码的准确性！', false);

            return redirect()->back();
        }
        if ($password !== $passwordConfirm) {
            Tool::showMessage('两次密码不一致', false);

            return redirect()->back();
        }

        $saved = User::query()->update([
            'id' => $user->id,
            'password' => bcrypt($password),
        ]);

        $msg = $saved ? '密码修改成功' : '请稍后重试';
        Tool::showMessage($msg, $saved);
        return redirect()->back();
    }

    /**
     * 缓存清理
     *
     * @return RedirectResponse
     */
    public function clear(): RedirectResponse
    {
        Artisan::call('cache:clear');
        Tool::showMessage('清理成功');

        return redirect()->route('admin.basic');
    }

    /**
     * 刷新缓存
     *
     * @return RedirectResponse
     */
    public function refresh(): RedirectResponse
    {
        if (setting('queue_refresh', 0)) {
            RefreshCache::dispatch()
                ->delay(Carbon::now()->addSeconds(5))
                ->onQueue('olaindex')
                ->onConnection('database');
            Tool::showMessage('后台正在刷新，请继续其它任务...');
        } else {
            Artisan::call('od:cache');
            Tool::showMessage('刷新成功');
        }
        return redirect()->route('admin.basic');
    }

    /**
     * 账号绑定
     *
     * @param Request $request
     *
     * @return Factory|RedirectResponse|View
     */
    public function bind(Request $request)
    {
        if (!$request->isMethod('post')) {
            return view(config('olaindex.theme') . 'admin.bind');
        }
        if ($request->type == "delete") { //解除绑定
            OnedriveAccount::destroy((int) $request->id);
        } elseif ($request->type == "update") { //更新名称
            OnedriveAccount::where('id', (int) $request->id)->update(['nick_name' => $request->nick_name]);
        }
        Tool::showMessage('修改成功！');
        return redirect()->route('admin.bind');
    }

    public function newBind()
    {
        return view(config('olaindex.theme') . 'admin.newdrive');
    }

    public function createBind(Request $request)
    {
        $client_id = $request->get('client_id');
        $client_secret = $request->get('client_secret');
        $redirect_uri = $request->get('redirect_uri');
        $account_type = $request->get('account_type');
        if (empty($client_id) || empty($client_secret) || empty($redirect_uri)) {
            Tool::showMessage('参数请填写完整', false);
            return redirect()->back();
        }
        // 写入配置
        $data = [
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'redirect_uri' => $redirect_uri,
            'account_type' => $account_type,
        ];
        session($data);
        return redirect()->route('oauth');
    }

    public function usage()
    {
        return view(config('olaindex.theme') . 'admin.usage');
    }

    // public function offlineDownload(Request $request)
    // {
    //     $aria2Url = 'http://' . setting('rpc_url') . ':' . setting('rpc_port') . '/jsonrpc';
    //     $aria2Token = 'token:' . setting('rpc_token');
    //     //GET
    //     if ($request->isMethod('get')) {
    //         $filesInfo = [];
    //         $offlineDlfiles = OfflineDlFile::all()->toArray();
    //         foreach ($offlineDlfiles as $key => $offlineDlfile) {
    //             $newFile = array();
    //             $newFile['name'] = $offlineDlfile['name'];
    //             $newFile['action'] = 'disabled';
    //             $newFile['gid'] = $offlineDlfile['gid'];
    //             $newFile['status'] = $offlineDlfile['status'];
    //             $newFile['progress'] = $offlineDlfile['progress'];
    //             //上传项目
    //             if ($offlineDlfile['status'] == 'uploading') {
    //                 $newFile['speed'] = $offlineDlfile['speed'];
    //                 array_push($filesInfo,$newFile);
    //             } else if
    //             ($offlineDlfile['status'] == 'success'){
    //                 $newFile['speed'] = 0;
    //                 array_push($filesInfo,$newFile);
    //             }
    //         }
    //         //显示下载项
    //         try{
    //             $aria2 = new Aria2($aria2Url, $aria2Token);
    //             $dlResponse = $aria2->tellActive(
    //                 [
    //                     'gid',
    //                     'totalLength',
    //                     'completedLength',
    //                     'downloadSpeed',
    //                     'bittorrent',
    //                     'files'
    //                 ]
    //             );
    //         }catch(ErrorException $e){
    //             Tool::showMessage('出现错误：未配置Aria2或连接出错', false);
    //             return redirect()->route('admin.basic');
    //         }
    //         if ($aria2->error['error']) {
    //             Tool::showMessage('出现错误：' . $aria2->error['msg'], false);
    //             return redirect()->route('admin.basic');
    //         }
    //         foreach ($dlResponse['result'] as $key => $file) {
    //             $newFile = array();
    //             $newFile['gid'] = $file['gid'];
    //             $newFile['speed'] = Tool::convertSize($file['downloadSpeed']);
    //             if($file['totalLength'] != 0){ //算的比西方记者还快导致除以零
    //                 $newFile['progress'] = floor(($file['completedLength'] / $file['totalLength']) * 100) . '%';
    //             } else{
    //                 $newFile['progress'] = '0%';
    //             }
    //             $newFile['status'] = 'downloading';
    //             if(Arr::has($file,'bittorrent')){
    //                 Log::debug($dlResponse);
    //                 if(Arr::has($file['bittorrent'],'info')){ //磁链
    //                     $newFile['name'] = $file['bittorrent']['info']['name'];
    //                 } else{ //磁链
    //                     $newFile['name'] = $file['files'][0]['path'];
    //                 }
    //             } else{
    //                 $newFile['name'] = basename($file['files'][0]['path']);
    //             }
    //             $newFile['action'] = 'pause';
    //             array_push($filesInfo,$newFile);
    //         }
    //         //显示暂停项
    //         $dlResponse = $aria2->tellWaiting(
    //             0,999,
    //             [
    //                 'gid',
    //                 'totalLength',
    //                 'completedLength',
    //                 'bittorrent',
    //                 'files'
    //             ]
    //         );
    //         if ($aria2->error['error']) {
    //             Tool::showMessage('出现错误：' . $aria2->error['msg'], false);
    //             return redirect()->route('admin.basic');
    //         }
    //         foreach ($dlResponse['result'] as $key => $file) {
    //             $newFile = array();
    //             $newFile['gid'] = $file['gid'];
    //             $newFile['speed'] = '0';
    //             if($file['totalLength'] != 0){ //算的比西方记者还快导致除以零
    //                 $newFile['progress'] = floor(($file['completedLength'] / $file['totalLength']) * 100) . '%';
    //             } else{
    //                 $newFile['progress'] = '0%';
    //             }
    //             $newFile['status'] = 'waiting';
    //             if(Arr::has($file,'bittorrent')){
    //                 if(Arr::has($file['bittorrent'],'info')){
    //                     Log::debug($dlResponse);
    //                     $newFile['name'] = $file['bittorrent']['info']['name']; //我死了
    //                 }
    //                 $newFile['name'] = basename($file['files'][0]['path']);
    //             } else{
    //                 $newFile['name'] = basename($file['files'][0]['path']);
    //             }
    //             $newFile['action'] = 'unpause';
    //             array_push($filesInfo,$newFile);
    //         }
    //         return view(config('olaindex.theme') . 'admin.offlineDownload', compact(['filesInfo']));
    //     }
    //     //POST
    //     $aria2 = new Aria2($aria2Url, $aria2Token);
    //     //接受数据
    //     $path = $request->path;
    //     $url = $request->url;
    //     $clientId = $request->client_id;
    //     //下载
    //     $dlResponse = $aria2->addUri([$url]);
    //     if ($aria2->error['error']) {
    //         Tool::showMessage('出现错误：' . $aria2->error['msg'], false);
    //         return redirect()->route('admin.offlinedl.download');
    //     }
    //     //存入数据库
    //     $offlineDlfile = new OfflineDlFile();
    //     if(strstr($url,'magnet:?xt=urn:btih:')){
    //         $offlineDlfile->name = 'magnet';
    //     } else{
    //         $offlineDlfile->name = basename($url);
    //     }
    //     Log::debug($dlResponse);
    //     Log::debug($aria2->tellStatus($dlResponse['result'],['following','gid','followedBy']));
    //     $offlineDlfile->gid = $dlResponse['result'];
    //     $offlineDlfile->upload_path = $path;
    //     $offlineDlfile->client_id = $clientId;
    //     $offlineDlfile->status = 'downloading';
    //     $offlineDlfile->save();
    //     Tool::showMessage('开始下载任务');
    //     return redirect()->route('admin.offlinedl.download');
    // }

    // public function offlineUpload(Request $request)
    // {
    //     $token = $request->route()->parameter('token');
    //     $gid = $request->route()->parameter('gid');
    //     if ($token != setting('rpc_token')) {
    //         return 'unauthrized';
    //     }
    //     $aria2Url = 'http://' . setting('rpc_url') . ':' . setting('rpc_port') . '/jsonrpc';
    //     $aria2Token = 'token:' . setting('rpc_token');
    //     $aria2 = new Aria2($aria2Url, $aria2Token);

    //     //判断一下是不是种子文件 需要传到aria2
    //     $oldGid = Aria2::isBtTask($gid);
    //     //如果是链式传入 例如磁力或者metalink或者种子
    //     if ($oldGid != false) {
    //         $offlineDlfile = OfflineDlFile::where('gid', $oldGid)->first();
    //         $response = $aria2->tellStatus($gid, ['bittorrent']);
    //         $offlineDlfile->name = $response['result']['bittorrent']['info']['name'];
    //         //修改bt任务数据
    //         $offlineDlfile->status = 'downloading';
    //         $offlineDlfile->save();
    //         $gid = $oldGid;
    //     } else{
    //         $offlineDlfile = OfflineDlFile::where('gid', $gid)->first();
    //     }

    //     //圣诞节 写代码都白给

    //     // //此处传入种子文件
    //     // $offlineDlfile = OfflineDlFile::where('gid',$gid)->first();
    //     // if(strstr($preFile['name'],'.torrent')){
    //     //     return;
    //     //     $btFile = fopen($preFile['path'],'r');
    //     //     $btFileContent = base64_encode(fread($btFile,filesize($preFile['path'])));
    //     //     fclose($btFile);
    //     //     $response = $aria2->addTorrent($btFileContent);
    //     //     $offlineDlfile->gid = $response['result'];
    //     //     $response = $aria2->tellStatus($response['result'],['bittorrent']);
    //     //     $offlineDlfile->name = $response['result']['bittorrent']['info']['name'];
    //     //     //修改bt任务数据
    //     //     $offlineDlfile->status = 'downloading';
    //     //     $offlineDlfile->save();
    //     //     return 'bt task added';
    //     // }
    //     $response = $aria2->getFiles($gid);
    //     if (Arr::has($response, 'result')) {
    //         foreach ($response['result'] as $key => $item) {
    //             $payload = [
    //                 'local' => $item['path'],
    //                 'remote' => $offlineDlfile->upload_path,
    //                 'chuck' => 3276800,
    //                 'clientId' => $offlineDlfile->client_id,
    //                 'gid' => $gid
    //             ];
    //             ProcessUpload::dispatch($payload);
    //         }
    //         return 'all files uploaded';
    //     } else {
    //         return "gid not found";
    //     }
    // }

    // public function offlineDlFile(Request $request){
    //     $action = $request->action;
    //     $gid = $request->gid;
    //     $aria2Url = 'http://' . setting('rpc_url') . ':' . setting('rpc_port') . '/jsonrpc';
    //     $aria2Token = 'token:' . setting('rpc_token');
    //     $aria2 = new Aria2($aria2Url, $aria2Token);
    //     if($action == 'pause'){
    //         $aria2->forcePause($gid);
    //     } else if($action == 'unpause'){
    //         $aria2->unpause($gid);
    //     } else if($action == 'delete'){
    //         $aria2->forceRemove($gid);
    //         $oldGid = Aria2::isBtTask($gid);
    //         if($oldGid != false){ //BtTask
    //             OfflineDlFile::where('gid',$oldGid)->delete();
    //         } else{
    //             OfflineDlFile::where('gid',$gid)->delete();
    //         }
    //     }
    //     return redirect()->route('admin.offlinedl.download');
    // }
}
