<?php

namespace common\services\client;

use common\services\BaseService;


class ClientService extends BaseService
{
    private $pirvateKey = '-----BEGIN PRIVATE KEY-----
MIIEvwIBADANBgkqhkiG9w0BAQEFAASCBKkwggSlAgEAAoIBAQDc/vsyIDrlfj/W
s+gjtfAd7wUjY4cOYpyhiTW5KtCWACR/iOSHnXnPJ62bmu2o+0eMdyB0SxWvoroL
bD4pfwawhx3h/poB6r5qjYgmgCuqIrEpPlTOTuvyi9KpozIA/Kb3427zbr2iNkbT
SYxIne082l2VdJWJBeurtPB3GmvPrljIVjTb47ssalrL4XbTDW0OR43n4ARStawR
6aSJFfB1kfKe6ouIaDqGZwHUZpm99Yxt6KkfJdyAZcjPl9Ro1E1pWGX+yxojGxb4
4oQGnmxGwJWbkizyMGulGTgXRzhpF/TeM5mqUxNYS2fU3WeeLfAeH1VGcRYYXbUZ
KgrjcQJ/AgMBAAECggEBANjIx2DEfQHidn0GvhFJx+GVNlUgrLtPwJY1Ip4hgbuj
KCpy/rqJn5O2OHcL41aAKA+CTDPH8932osnRxKiwEr/Xy1iFiE2ZGHEtpQEfebXL
kj4DBu3aPVoQDvaZP9P4A1HnqE7jsuIMnC1nWFVjlfS7wFF7q7ReOnCVbc1n/Zoa
eg5RK+BdfG5loSfbZDFCglcFm9gFv7sF8QCEH/WY5NKlILUWoG94teV/sjVSHRNp
06H9CtSsZReVJTnL7DaXQckI0C/xzjotuMw2mGcQW4zMl22/k1J242xo7/PQgyCB
pKWOun54rbZ2cM4BCfstldhPKZgUdczhtDLCVpXtYwECgYEA8KL7COpsdNpLSKuW
CcE0clnQ+mE88rZSArs5fylmE8wITeJ096jtewnSOsXuGCBdwsKddivCXgB8+HAc
765//8ldUjwREZUFMz7+n+5rJ2DA5q3PtBonqSF+RhKxV7f9nLCdqf2YQxwxYmpX
McvHRwnEfIsk0p2iqtj4jSmTtWsCgYEA6xr9aKfnS6AZJbI2Oaqa+NG9Wa8GXe+0
Ja5mnnQ7KVO9bdadwjthn6Ma/aOK6O/BbZ69UntjQ5zbs6R6Gt/HZlgyiOT/GxyQ
hpn45eNIifmXDJwdc0rurXm+gwrT19TkCkqwcNUsz8rNiUjwpLK0k+c51eY/l9SZ
pmTuC5UmWD0CgYEAzFscpW2vvWzFEmnleVNCnvn3pyp9AAIlk3w8T+lwbs+PHdV7
T/d99kdY6eC9Wm/iMEvmPTzcS8hOWQ2dBU9EFI2FWxj3xd6wE90jj96B0WcxarWg
9mpf1BpFimzFfqEaF79Fpd9fN17x8YotknRkP8fjvYDNPK/yPPUV34u3jRECgYEA
07c9ZmZ1QkQNAUZH0IxS/CicNEwKFsOKXbh8zspqkF/JoUT4UfX5hrFx1+DTccqe
TGH7qXBVxohVzKMcFmFYhlB8SYy2Mir590xmuFaBd0adAI0BdFaRMwUES6uPc7hS
FvaFh0ZhEUpW5v8ZPad0OBCso3Ox5r9cElynhSSuIB0CgYASJodZCIld2W345d1d
EYfapdCtqj6Q3v8sD352/6Unhb4v4s3JXSv5koSeetj3ynbsR6ntsOpragimPlu5
FD1YQGoXhXXLEMOdY18udAFsJUZeZIVVuaGLwOUP9uFQ7c0qUhI+9fyW7vExsTnz
ligd3ZqCjI/Z/AeDqLmpzJ2EBw==
-----END PRIVATE KEY-----
';

    private $publucKey = '-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA3P77MiA65X4/1rPoI7Xw
He8FI2OHDmKcoYk1uSrQlgAkf4jkh515zyetm5rtqPtHjHcgdEsVr6K6C2w+KX8G
sIcd4f6aAeq+ao2IJoArqiKxKT5Uzk7r8ovSqaMyAPym9+Nu8269ojZG00mMSJ3t
PNpdlXSViQXrq7Twdxprz65YyFY02+O7LGpay+F20w1tDkeN5+AEUrWsEemkiRXw
dZHynuqLiGg6hmcB1GaZvfWMbeipHyXcgGXIz5fUaNRNaVhl/ssaIxsW+OKEBp5s
RsCVm5Is8jBrpRk4F0c4aRf03jOZqlMTWEtn1N1nni3wHh9VRnEWGF21GSoK43EC
fwIDAQAB
-----END PUBLIC KEY-----
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
