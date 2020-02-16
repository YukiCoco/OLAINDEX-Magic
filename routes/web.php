<?php

use Illuminate\Support\Str;
use App\Utils\Tool;

// 授权
Route::get('/oauth', 'OauthController@oauth')->name('oauth');

// 缩略图
Route::get('thumb/{clientId}/{id}/size/{size}', 'IndexController@thumb')->name('thumb');
Route::get('thumb/{clientId}/{id}/{width}/{height}', 'IndexController@thumbCrop')->name('thumb_crop');

// 搜索
Route::any('search', 'IndexController@search')
    ->name('search');
Route::any('search/file/{id}', 'IndexController@searchShow')
    ->name('search.show');


// 加密
Route::post('password', 'IndexController@handleEncrypt')->name('password');

//消息
Route::view('message', config('olaindex.theme') . 'message')->name('message');


// 图床
Route::get('image', 'ImageController@index')->name('image');
Route::post('image-upload', 'ImageController@upload')->name('image.upload');

//删除
Route::any('file/delete/{clientId?}/{sign?}', 'ManageController@deleteItem')->name('delete');

//后台设置管理
Route::get('login', 'LoginController@showLoginForm')->name('login');
Route::post('login', 'LoginController@login');
Route::post('logout', 'LoginController@logout')->name('logout');

// 操作
Route::prefix('admin')->group(static function () {
    // 安装
    Route::prefix('install')->group(static function () {
        Route::any('/', 'InstallController@install')->name('_1stInstall');
        Route::any('apply', 'InstallController@apply')->name('apply');
        Route::any('reset', 'InstallController@reset')->name('reset');
        Route::any('bind', 'InstallController@bind')->name('bind');
    });

    // 基础设置
    Route::any('/', 'AdminController@basic')->name('admin.basic');
    Route::any('bind', 'AdminController@bind')->name('admin.bind');
    Route::any('show', 'AdminController@show')->name('admin.show');
    Route::any('profile', 'AdminController@profile')->name('admin.profile');
    Route::any('clear', 'AdminController@clear')->name('admin.cache.clear');
    Route::any('refresh', 'AdminController@refresh')->name('admin.cache.refresh');
    Route::get('newbind', 'AdminController@newBind')->name('admin.bind.newbind');
    Route::post('newbind', 'AdminController@createBind')->name('admin.bind.createNewbind');
    Route::get('usage', 'AdminController@usage')->name('admin.usage');
    Route::any('offlinedl', 'AdminController@offlineDownload')->name('admin.offlinedl.download');
    Route::get('offlinedl/upload/{token}/{gid}','AdminController@offlineUpload')->name('admin.offlinedl.upload');
    Route::post('offlinedl/file', 'AdminController@offlineDlFile')->name('admin.offlinedl.file');
    // 文件夹操作
    Route::prefix('folder')->group(static function () {
        Route::post('lock', 'ManageController@lockFolder')->name('admin.lock');
        Route::post('create', 'ManageController@createFolder')->name('admin.folder.create');
    });
    // 文件操作
    Route::prefix('file')->group(static function () {
        Route::get('/', 'ManageController@uploadFile')->name('admin.file');
        Route::post('upload/{clientId?}', 'ManageController@uploadFile')
            ->name('admin.file.upload');
        Route::any('add/{clientId?}', 'ManageController@createFile')
            ->name('admin.file.create');
        Route::any('edit/{clientId?}/{id}', 'ManageController@updateFile')
            ->name('admin.file.update');
        Route::view('other', config('olaindex.theme') . 'admin.other')
            ->name('admin.other');
        Route::post('copy/{clientId?}', 'ManageController@copyItem')->name('admin.copy');
        Route::post('move/{clientId?}', 'ManageController@moveItem')->name('admin.move');
        Route::post('file/path2id/{clientId?}', 'ManageController@pathToItemId')
            ->name('admin.path2id');
        Route::post('share/{clientId?}', 'ManageController@createShareLink')
            ->name('admin.share');
        Route::post('share/delete/{clientId?}', 'ManageController@deleteShareLink')
            ->name('admin.share.delete');
    });
    // 离线上传
    // Route::post('url/upload', 'ManageController@uploadUrl')
    //     ->name('admin.url.upload');
});

if (Str::contains(config('app.url'), ['localhost', 'dev.ningkai.wang'])) {
    Route::get('about', static function () {
        $url = 'https://raw.githubusercontent.com/WangNingkai/OLAINDEX/master/README.md';
        $content = Tool::getFileContent($url, 'aboutMe');
        $markdown = Tool::markdown2Html($content);

        return response()->view(
            config('olaindex.theme') . 'about',
            compact('markdown')
        );
    });
}

// 首页索引
Route::get('/', 'IndexController@home');

$showOriginPath = setting('origin_path', 1);

if (!$showOriginPath) {

    //列表
    Route::prefix('home')->group(static function () {
        Route::get('{clientId?}/{query?}', 'IndexController@list')->where('query', '.*')->name('home');
    });

    //展示
    Route::get('show/{clientId?}/{query}', 'IndexController@show')
        ->where('query', '.*') //正则约束
        ->name('show');
    //展示
    // Route::get('{oneIndex}/show/{query}', 'IndexController@show')
    // ->where('query', '.*') //正则约束
    // ->name('show');
    // 下载
    Route::get('down/{clientId?}/{query}', 'IndexController@download')
        ->where('query', '.*')
        ->name('download');
    //看图
    Route::get('view/{clientId?}/{query}', 'IndexController@download')
        ->where('query', '.*')
        ->name('view');
} else {
    //展示
    Route::get('s/{clientId?}/{query}', 'IndexController@show')
        ->where('query', '.*')
        ->name('show');
    // 下载
    Route::get('d/{clientId?}/{query}', 'IndexController@download')
        ->where('query', '.*')
        ->name('download');
    //看图
    Route::get('v/{clientId?}/{query}', 'IndexController@download')
        ->where('query', '.*')
        ->name('view');

    Route::get('{clientId?}/{query?}', 'IndexController@list')->where('query', '.*')->name('home');
}
