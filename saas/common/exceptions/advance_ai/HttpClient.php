<?php
namespace common\exceptions\advance_ai;


class HttpClient extends AbstractClient
{
    public function request($api_name, $param_array, $file_array = null)
    {
        $this->prepare($api_name, $param_array, $file_array);

        $requestOpts = array(
            'http' => array(
                'method' => 'POST',
                'timeout' => $this->timeoutReadWrite,
                'header' => join("\r\n", $this->requestHeaders),
                'content' => $this->requestPostBody,
            )
        );

        if ($this->ignoreSslCheck) {
            $requestOpts['ssl'] = array(
                'verify_peer' => false,
                'verify_peer_name' => false,
            );
        }

        $context = stream_context_create($requestOpts);
        $resp = file_get_contents($this->requestUrl, null, $context);
        if($resp === false) {
            if(!empty($http_response_header)) {
                $this->requestError = array(
                    'error'=>'http error',
                    'http_response_header'=>$http_response_header,
                );
            } else {
                $this->requestError = array(
                    'error'=>'file_get_contents  error'
                );
            }
        }
        return $resp;
    }
}
