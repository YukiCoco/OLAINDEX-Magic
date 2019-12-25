<?php
Namespace App\Utils;

use Illuminate\Support\Arr;

class Aria2
{
    protected $ch;
    protected $token;
    protected $batch = false;
    protected $batch_cmds = [];
    public $error;

    function __construct($server='http://127.0.0.1:6800/jsonrpc', $token=null)
    {
        $this->ch = curl_init($server);
        $this->token = $token;
        curl_setopt_array($this->ch, [
            CURLOPT_POST=>true,
            CURLOPT_RETURNTRANSFER=>true,
            CURLOPT_HEADER=>false
        ]);
    }

    function __destruct()
    {
        curl_close($this->ch);
    }

    protected function req($data)
    {
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
        return curl_exec($this->ch);
    }

    function batch($func=null)
    {
        $this->batch = true;
        if(is_callable($func)) {
            $func($this);
        }
        return $this;
    }

    function inBatch()
    {
        return $this->batch;
    }

    function commit()
    {
        $this->batch = false;
        $cmds = json_encode($this->batch_cmds);
        $result = $this->req($cmds);
        $this->batch_cmds = [];
        return $result;
    }

    function __call($name, $arg)
    {
        if(!is_null($this->token)) {
            array_unshift($arg, $this->token);
        }

        //Support system methods
        if(strpos($name, '_')===false) {
            $name = 'aria2.'.$name;
        } else {
            $name = str_replace('_', '.', $name);
        }

        $data = [
            'jsonrpc'=>'2.0',
            'id'=>'1',
            'method'=>$name,
            'params'=>$arg
        ];
        //Support batch requests
        if($this->batch) {
            $this->batch_cmds[] = $data;
            return $this;
        }
        $data = json_encode($data);
        $response = $this->req($data);
        if($response===false) {
            trigger_error(curl_error($this->ch));
        }
        $response = json_decode($response, 1);
        if(Arr::has($response,'error')){
            $this->error['error'] = true;
            $this->error['code'] = $response['error']['code'];
            $this->error['msg'] = $response['error']['message'];
        }
        return $response;
    }

    public static function isBtTask($gid){
        $aria2Url = 'http://' . setting('rpc_url') . ':' . setting('rpc_port') . '/jsonrpc';
        $aria2Token = 'token:' . setting('rpc_token');
        $aria2 = new Aria2($aria2Url, $aria2Token);
        //判断一下是不是种子文件 需要传到aria2
        $response = $aria2->tellStatus($gid,['following']);
        //如果是链式传入 例如磁力或者metalink或者种子
        if (Arr::has($response['result'], 'following')) {
            return $response['result']['following'];
        } else{
            return false;
        }
    }
}
