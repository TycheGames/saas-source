<?php

namespace common\services;

use Carbon\Carbon;
use GuzzleHttp\Client;
use OSS\Core\OssException;
use OSS\OssClient;
use Yii;
use OSS\Http\ResponseCore;

class FileStorageService
{
    private $accessKeyId = '';
    private $accessKeySecret = '';

    private $endpoint = '';
    private $bucket = '';

    /**
     * FileStorageService constructor.
     * @param bool $isInternal
     * @param string $accessKeyId
     * @param string $accessKeySecret
     * @param string $bucket
     */
    public function __construct(bool $isInternal = true, string $bucket = '', string $accessKeyId = '', string $accessKeySecret = '')
    {
        $this->accessKeyId = empty($accessKeyId) ? Yii::$app->params['OSS']['OSS_ACCESS_ID'] : $accessKeyId;
        $this->accessKeySecret = empty($accessKeySecret) ? Yii::$app->params['OSS']['OSS_ACCESS_KEY'] : $accessKeySecret;
        $this->endpoint = $isInternal ? Yii::$app->params['OSS']['OSS_ENDPOINT_LAN'] : Yii::$app->params['OSS']['OSS_ENDPOINT_WAN'];
        $this->bucket = empty($bucket) ? Yii::$app->params['OSS']['OSS_BUCKET'] : $bucket;
    }

    /**
     * @param string $bucket
     */
    public function setBucket(string $bucket)
    {
        $this->bucket = $bucket;
    }

    /**
     * @param string $accessKeyId
     */
    public function setAccessKeyId(string $accessKeyId): void
    {
        $this->accessKeyId = $accessKeyId;
    }

    /**
     * @param string $accessKeySecret
     */
    public function setAccessKeySecret(string $accessKeySecret): void
    {
        $this->accessKeySecret = $accessKeySecret;
    }

    /**
     * @param string $endpoint
     */
    public function setEndpoint(string $endpoint): void
    {
        $this->endpoint = $endpoint;
    }

    /**
     * @return OssClient
     * @throws OssException
     */
    private function getClient()
    {
        return new OssClient($this->accessKeyId, $this->accessKeySecret, $this->endpoint);
    }

    /**
     * 上传文件-按日期分配路径
     * @param string $remoteDir
     * @param string $localPath
     * @param string $extension
     * @param bool $delete
     * @return string
     */
    public function uploadFile(string $remoteDir, string $localPath, string $extension = '', bool $delete = true):string
    {
        $imageType = empty($extension) ? pathinfo($localPath, PATHINFO_EXTENSION) : $extension;
        $fileName = uniqid() . '.' . $imageType;
        $filePath = '/tmp/' . $fileName;

        $ossClient = $this->getClient();
        $dateString = Carbon::now()->format('Ymd');
        $object = sprintf('%s/%s/%s', $remoteDir, $dateString, $fileName);
        //上传失败抛异常，否则成功，不需要对返回值做校验
        $ossRes = $ossClient->uploadFile($this->bucket, $object, $localPath);

        if ($delete) {
            unlink($localPath);
        }

        return $object;
    }

    /**
     * 上传文件-指定文件路径
     * @param string $filePath
     * @param string $localPath
     * @param bool $delete
     * @return string
     */
    public function uploadFileByPath(string $filePath, string $localPath, bool $delete = true):string
    {
        $ossClient = $this->getClient();
        //上传失败抛异常，否则成功，不需要对返回值做校验
        $ossRes = $ossClient->uploadFile($this->bucket, $filePath, $localPath);

        if ($delete) {
            unlink($localPath);
        }

        return $filePath;
    }

    /**
     * 下载文件
     * @param string $remotePath
     * @param string $localPath
     * @return string
     */
    public function downloadFile(string $remotePath, string $localPath = '/tmp/'): string
    {
        $remotePaths = explode('/', $remotePath);
        $filePath = $localPath . end($remotePaths);
        $ossClient = $this->getClient();
        $options = [
            OssClient::OSS_FILE_DOWNLOAD => $filePath,
        ];
        $ossClient->getObject($this->bucket, $remotePath, $options);

        return $filePath;
    }

    public function deleteFile(string $remotePath)
    {
        try {
            $ossClient = $this->getClient();
            //删除失败抛异常，否则成功，不需要对返回值做校验
            $ossRes = $ossClient->deleteObject($this->bucket, $remotePath);
        } catch (OssException $exception) {
            return false;
        }

        return true;
    }

    /**
     * @param string $url
     * @param int $timeout
     * @return string
     * @throws OssException
     */
    public function getSignedUrl(string $url, int $timeout = 3600): string
    {
        $ossClient = $this->getClient();
        $signedUrl = $ossClient->signUrl($this->bucket, $url, $timeout, "GET");

        return $signedUrl;
    }

    /**
     * @param string $remotePath OSS存储路径
     * @param string $url 下载文件URL
     * @return string
     */
    public function uploadFileByUrl(string $remotePath, string $url): string
    {
        $fileName = basename(parse_url($url, PHP_URL_PATH));
        $filePath = '/tmp/' . $fileName;

        $client = new Client();
        $response = $client->get($url, ['save_to' => $filePath]);

        $ossClient = $this->getClient();
        $dateString = Carbon::now()->format('Ymd');
        $object = sprintf('%s/%s/%s', $remotePath, $dateString, $fileName);
        //上传失败抛异常，否则成功，不需要对返回值做校验
        $ossRes = $ossClient->uploadFile($this->bucket, $object, $filePath);

        unlink($filePath);

        return $object;
    }

    /**
     * 上传Base64的图片数据
     * @param string $base64Str 文件base64字符串
     * @param string $remotePath OSS存储路径
     * @return string
     */
    public function uploadFileByPictureBase64(string $remotePath, string $base64Str): string
    {
        $imageParts = explode(";base64,", $base64Str);
        $imageTypeAux = explode("image/", $imageParts[0]);
        $imageType = $imageTypeAux[1];
        $imageBase64 = base64_decode($imageParts[1]);
        $fileName = uniqid() . '.' . $imageType;
        $filePath = '/tmp/' . $fileName;
        file_put_contents($filePath, $imageBase64);

        $ossClient = $this->getClient();
        $dateString = Carbon::now()->format('Ymd');
        $object = sprintf('%s/%s/%s', $remotePath, $dateString, $fileName);
        //上传失败抛异常，否则成功，不需要对返回值做校验
        $ossRes = $ossClient->uploadFile($this->bucket, $object, $filePath);

        unlink($filePath);

        return $object;
    }

    /**
     * 上传文件 aadhaar 掩码图片替换原图片
     * @param string $object    原地址
     * @param string $base64Str 掩码图片base64字符串
     * @return string
     */
    public function uploadFileBase64(string $object, string $base64Str):string
    {
        $localPath = explode('/', $object);
        $filePath = '/tmp/'.end($localPath);
        file_put_contents($filePath,base64_decode($base64Str));
        $ossClient = $this->getClient();
        //上传失败抛异常，否则成功，不需要对返回值做校验
        $ossRes = $ossClient->uploadFile($this->bucket, $object, $filePath);

        unlink($filePath);

        return $object;
    }
}