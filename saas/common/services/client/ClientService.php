<?php

namespace common\services\client;

use common\services\BaseService;


class ClientService extends BaseService
{
    private $pirvateKey = '-----BEGIN PRIVATE KEY-----
MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQCetPFxVsF5m7s6
w0DsZA9FoMib3/ep+/ytLSrn9TtlV+RWppgCh2Z0lT53go2f/0VE8rwrG/RTDBwG
C/AeQ9urdTeku4UyBxuASpCTUdBAGDG6401w99uusaPPQ7VlB2Cyf3Nawki567MO
PnJLzfRi5o4uIne6asxP4/fDiSCcJMQ1eo5BwtGbi+30hUhD47SjZEZBTpNCgwrX
PTetT1OgCHYyGEGyK0ryVi84t94PmOW2VsuaD5QsF85gSCmJAuVqLrSI9ZEPh6Vv
Xkqj4+G8jvlkYFTFqFY5okqcEofzjmYmMDghrDz8exhl1XSgacGHHQWZm6qwrV15
ASGh0V9zAgMBAAECggEBAIn0r4vd5gBXpsDFyUGzGLNlt/wHC7nvGQ1QHePT//Vw
GURZO/m2yRC+SraH/aP+ua/dcEPo4NDvzcxTxD4KroC0O9IuTvHVy8oRBuuISoXs
gg36V+7DBWstbz9Tk/JmH2AJ+bQC/kTe5Z6UGbZbKPxvfVgNDhY1j7RCbbTDFl2j
GYIj0O3IG6fcqY7LuqNrLLvbW3QF7Jm/ShzkKRFE28cGycvWNGcvQdh6FlfvaJpX
vh3Q2Nhq8eZrQub988amwSC9x+DpxjDtdZkrBSlT8krb3rOgJpF0Pzaw4D8rFtIu
FzteSs5Y58aBKPzREnKmx55R716bq4hqgcn3681XAEECgYEAzqXFiAUSeZSrl6ZC
lqn+8v3LU/a1pyeDBWCM/VNVkB35birsmtWxaLhuU9BWjKfXF5zE1UpsaXwf+BSR
SSoKuzwYqMkM6MSsbNDluKYtkIdzRZgMWKw4tXFxfInexpczxmE6/6kHigqDnnU2
t40E7BLZnj9QL9F4OOiLo4NZj50CgYEAxJwfNPcDWJD1eCeyx4jTwFtlbuWbizi/
zBrFoJrWuci5kxIioJTrTSSIoXuyBflsx//VqXrwqh+/22P6DfiJVPZ58rv7m2kA
ggj88hoSXnFCxeIecgIXgvdbTXpMY933uCMM2RM2MnZm/tsvQssFYFeRdiljoPXm
njwmBGeT5k8CgYBTLt215DzMnjD4iZ+yYFnVXJ5VfTEgSUJvjq8SZlJCAh7e3v6B
UVXpzpvytyGv2vw1cBC3vw6m46VGypi1N+w79HQEtXGx3UF/SBPA4Xvj+vJG6G7H
3wOw3iqNtD3t+P2JdKg4M8VqJtNWnN5awrRhhzD8h4XyycI4Pju/pWcO7QKBgHF7
u/eSbHf+k/Fa2LKbo73wA0hTd8iQZQSmn1Poi/CIj7T4TsxpqLfKUqMDBuqz4bo/
TVCaCbfow5Ea0AaoYnyMGC+nmb9GiGiGT5peuiORNH2L2w7rbi7GIAlEgjtSr921
PYE+uZW/Cgwo78qZ7OyDHw8ZLMyHoynKhhccwh25AoGBAIJf+aqfKR0C+ZmiHbE5
4SAlw2zQi+GSDNpfb3kNS3AP8mEVT2kAbB1aFASm4Z4vT/2CA0YcvbN6H3R+4i9M
AlD1dq8T7KyWZrlUVko4nGEt19ZlaTOI7EflZE3MK6kSjXGFHd8c00BkT/vFOepr
IaY12kxiZuJSyPkjfW5xka+6
-----END PRIVATE KEY-----
';

    /**
     * 解密客户端密码
     * @param $pass
     * @return bool
     */
    public function decryptClientPass($pass)
    {
        $privateKey = openssl_get_privatekey($this->pirvateKey);
        $result = openssl_private_decrypt($pass, $decrypted, $privateKey);
        if($result)
        {
            return $decrypted;
        }else{
            return false;
        }
    }


    /**
     * 解密客户端header头
     * @param $data
     * @param $key
     * @param $iv
     * @return string
     */
    public function decryptClientData($data, $key, $iv)
    {
        $decrypted = openssl_decrypt($data, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
        return $decrypted;
    }


    /**
     * @param $data
     * @param $key
     * @param $iv
     * @return string
     */
    public function getClientInfo($data, $key, $iv)
    {
        $service = new ClientService();
        $key = $service->decryptClientPass(base64_decode(urldecode($key)));
        $iv = $service->decryptClientPass(base64_decode(urldecode($iv)));
        $data = base64_decode(urldecode($data));
        return $this->decryptClientData($data, $key, $iv);
    }
}
