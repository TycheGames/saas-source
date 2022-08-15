<?php

namespace common\exceptions\advance_ai;

class ApiClientFile
{
    public $name;
    public $data;

    public function __construct($name, $data)
    {
        $this->name = $name;
        $this->data = $data;
    }
}

abstract class AbstractClient
{
    protected $apiHost;
    protected $accessKey;
    protected $secretKey;

    protected $requestUrl;
    protected $requestPostBody;
    protected $requestHeaders;

    protected $ignoreSslCheck = false;

    protected $requestError;

    protected $timeoutConnect = 5;
    protected $timeoutReadWrite = 20;

    public function __construct($api_host, $access_key, $secret_key)
    {
        $this->apiHost = $api_host;
        $this->accessKey = $access_key;
        $this->secretKey = $secret_key;
    }

    public function setIgnoreSslCheck($v)
    {
        $this->ignoreSslCheck = $v;
    }

    public function setTimeout($connect, $readWrite)
    {
        $this->timeoutConnect = $connect;
        $this->timeoutReadWrite = $readWrite;
    }

    public function getRequestError()
    {
        return $this->requestError;
    }

    protected function prepare($api_name, $param_array, $file_array = null)
    {
        if (substr($api_name, 0, 1) != '/') {
            $api_name = '/' . $api_name;
        }

        $this->requestError = null;
        $this->requestUrl = substr($this->apiHost, -1) == '/' ? substr($this->apiHost, 0, -1) : $this->apiHost;
        $this->requestUrl .= $api_name;

        if ($file_array) {
            //use multipart
            $this->requestPostBody = '';

            $boundary = uniqid('----AD1238MJL7' . time() . 'I', true);
            $contentType = "multipart/form-data; boundary=$boundary";

            if ($param_array) {
                foreach ($param_array as $k => $v) {
                    if (!is_scalar($v)) {
                        throw new \RuntimeException("only scalar key/value params support when uploading files");
                    }

                    $this->requestPostBody .= "--{$boundary}\r\n";
                    $this->requestPostBody .= "Content-Disposition: form-data; name=\"$k\"\r\n";
                    $this->requestPostBody .= "\r\n{$v}\r\n";
                }
            }

            foreach ($file_array as $k => $f) {
                if($f instanceof ApiClientFile) {
                    $baseName = $f->name;
                    $fileContent = $f->data;
                } else {
                    $fn = $f;
                    $baseName = basename($fn);
                    $fileContent = file_get_contents($fn);
                    if ($fileContent === false) {
                        throw new \RuntimeException("$fn not exists");
                    }
                }
                $mimeType = 'application/octet-stream';
                $this->requestPostBody .= "--{$boundary}\r\n";
                $this->requestPostBody .= "Content-Disposition: form-data; name=\"$k\"; filename=\"$baseName\"\r\n";
                $this->requestPostBody .= "Content-Type: $mimeType\r\n";
                $this->requestPostBody .= "\r\n{$fileContent}\r\n";
            }
            $this->requestPostBody .= "--{$boundary}--";
        } else {
            //use json
            $contentType = 'application/json';
            $this->requestPostBody = json_encode($param_array);
        }

        $now = gmdate('D, d M Y H:i:s', time()) . ' GMT';
        $this->requestHeaders = array(
            'Content-Type: ' . $contentType,
            'Date: ' . $now,
        );

        $separator = '$';
        $sign_str = 'POST' . $separator;
        $sign_str .= $api_name . $separator;
        $sign_str .= $contentType . $separator;
        $sign_str .= $now . $separator;
        $authorization = sprintf('%s:%s', $this->accessKey, base64_encode(hash_hmac('sha256', $sign_str, $this->secretKey, true)));

        $this->requestHeaders[] = 'Authorization:' . $authorization;
    }

    abstract public function request($api_name, $param_array);
}
