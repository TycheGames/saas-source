<?php
namespace common\exceptions\advance_ai;


class CurlClient extends AbstractClient
{
    public function request($api_name, $param_array, $file_array = null)
    {
        $this->prepare($api_name, $param_array, $file_array);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_URL, $this->requestUrl);

        if ($this->requestHeaders) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->requestHeaders);
        }

        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->requestPostBody);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeoutConnect);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeoutReadWrite);

        if ($this->ignoreSslCheck) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        }

        $resp = curl_exec($ch);

        if($resp === false) {
            $this->requestError = array(
                'errno'=>curl_errno($ch),
                'error'=>curl_error($ch),
            );
        }

        curl_close($ch);
        return $resp;
    }
}
