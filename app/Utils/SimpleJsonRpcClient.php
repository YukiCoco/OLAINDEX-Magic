<?php
namespace App\Utils;
use Curl\Curl;

class SimpleJsonRpcClient
{
    protected $url = '';

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function __call(string $name, array $args)
    {
        return $this->call($name, ...$args);
    }

    public function call(string $name, ...$args)
    {
        function GUID()
        {
            $hash      = strtoupper(md5(uniqid(mt_rand(), true)));
            $seguments = [
                substr($hash, 0, 8),
                substr($hash, 8, 4),
                substr($hash, 12, 4),
                substr($hash, 16, 4),
                substr($hash, 20, 12),
            ];
            return implode('-', $seguments);
        }
        $json = json_encode([
            'jsonrpc' => '2.0',
            'id'      => GUID(),
            'method'  => $name,
            'params'  => $args,
        ]);
        $curl = new Curl();
        $curl->setHeader('Content-Type','application/json; charset=utf-8');
        $curl->post($this->url,$json);
        return $curl->response->json;
    }
}
