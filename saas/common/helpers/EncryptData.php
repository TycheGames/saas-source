<?php


namespace common\helpers;

use yii\base\Exception;

class EncryptData
{
    const METHOD = 'aes-256-cbc'; //加密解密方法均基于该加密方式，不兼容其他加密方法
    const PUBLIC_KEY = '3KQEZh4EcYdlYKqtRiCOL6PrFOPz4WzY'; //随机生成的字符串

    /**
     * 加密数据
     * @param string $message 原文
     * @param string $key 秘钥
     * @return string 密文
     * @throws Exception
     */
    public static function encrypt(string $message, string $key = self::PUBLIC_KEY)
    {
        if (mb_strlen($key, '8bit') !== 32) {
            throw new Exception("Needs a 256-bit key!");
        }

        $ivSize = openssl_cipher_iv_length(self::METHOD);
        $iv = openssl_random_pseudo_bytes($ivSize);

        $cipherText = openssl_encrypt(
            $message,
            self::METHOD,
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );

        return base64_encode($iv . $cipherText);
    }

    /**
     * 解密数据
     * @param string $message 密文
     * @param string $key 秘钥
     * @return false|string 原文
     * @throws Exception
     */
    public static function decrypt(string $message, string $key = self::PUBLIC_KEY)
    {
        if (mb_strlen($key, '8bit') !== 32) {
            throw new Exception("Needs a 256-bit key!");
        }

        $ivSize = openssl_cipher_iv_length(self::METHOD);
        $message = base64_decode($message);
        $iv = mb_substr($message, 0, $ivSize, '8bit');
        $cipherText = mb_substr($message, $ivSize, null, '8bit');

        return openssl_decrypt(
            $cipherText,
            self::METHOD,
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );
    }

    /**
     * 加密文件
     * @param string $filePath
     * @param string $encryptedFilePath
     * @param string $key
     * @param bool $delete
     * @return int|false
     * @throws Exception
     */
    public static function encryptFile(string $filePath, string $encryptedFilePath, string $key = self::PUBLIC_KEY, bool $delete = false)
    {
        if (!is_file($filePath)) {
            throw new Exception("Invalid file path!");
        }

        $dirPath = pathinfo($encryptedFilePath, PATHINFO_DIRNAME);
        if (!is_dir($dirPath)) {
            throw new Exception("Invalid dir path!");
        }

        $result = file_put_contents($encryptedFilePath, self::encrypt(file_get_contents($filePath), $key));
        if ($result !== false && $delete) {
            @unlink($filePath);
        }

        return $result;
    }

    /**
     * 解密文件
     * @param string $filePath
     * @param string $decryptedFilePath
     * @param string $key
     * @param bool $delete
     * @return int|false
     * @throws Exception
     */
    public static function decryptFile(string $filePath, string $decryptedFilePath, string $key = self::PUBLIC_KEY, bool $delete = false)
    {
        if (!is_file($filePath)) {
            throw new Exception("Invalid file path!");
        }

        $dirPath = pathinfo($decryptedFilePath, PATHINFO_DIRNAME);
        if (!is_dir($dirPath)) {
            throw new Exception("Invalid dir path!");
        }

        $result = file_put_contents($decryptedFilePath, self::decrypt(file_get_contents($filePath), $key));
        if ($result !== false && $delete) {
            @unlink($filePath);
        }

        return $result;
    }
}