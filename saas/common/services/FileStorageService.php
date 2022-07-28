<?php

namespace common\services;

use Carbon\Carbon;
use GuzzleHttp\Client;
use OSS\Core\OssException;
use OSS\OssClient;
use frostealth\yii2\aws\s3\Service as s3Service;
use frostealth\yii2\aws\s3\commands\UploadCommand as s3UploadCommand;
use frostealth\yii2\aws\s3\commands\GetCommand as s3GetCommand;
use frostealth\yii2\aws\s3\commands\GetPresignedUrlCommand as s3GetPresignedUrlCommand;
use frostealth\yii2\aws\s3\commands\DeleteCommand as s3DeleteCommand;
use Yii;

class FileStorageService
{
    private $accessKeyId = '';
    private $accessKeySecret = '';

    private $endpoint = '';
    private $bucket = '';

    private $serviceType = 's3';

    /**
     * FileStorageService constructor.
     * @param bool $isInternal
     * @param string $accessKeyId
     * @param string $accessKeySecret
     * @param string $bucket
     * @param string $endpoint
     * @param string $serviceType 可选值 "s3","oss"
     */
    public function __construct(bool $isInternal = true, string $bucket = '', string $accessKeyId = '', string $accessKeySecret = '',string $endpoint ='', string $serviceType = 's3')
    {
        if ($serviceType === 'oss') {
            $this->serviceType = $serviceType;
            $this->accessKeyId = empty($accessKeyId) ? Yii::$app->params['OSS']['OSS_ACCESS_ID'] : $accessKeyId;
            $this->accessKeySecret = empty($accessKeySecret) ? Yii::$app->params['OSS']['OSS_ACCESS_KEY'] : $accessKeySecret;
            if (empty($endpoint)) {
                $this->endpoint = $isInternal ? Yii::$app->params['OSS']['OSS_ENDPOINT_LAN'] : Yii::$app->params['OSS']['OSS_ENDPOINT_WAN'];
            } else {
                $this->endpoint = $endpoint;
            }
            $this->bucket = empty($bucket) ? Yii::$app->params['OSS']['OSS_BUCKET'] : $bucket;
        } else {
            $this->accessKeyId = $accessKeyId;
            $this->accessKeySecret = $accessKeySecret;
            $this->endpoint = $endpoint;
            $this->bucket = $bucket;
        }
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
     * @param string $serviceType
     */
    public function setServiceType(string $serviceType): void
    {
        $this->serviceType = $serviceType;
    }

    /**
     * @return OssClient|s3Service
     * @param string $serviceType
     * @throws
     */
    private function getClient(string $serviceType)
    {
        if ($serviceType === 'oss') {
            $this->accessKeyId = empty($this->accessKeyId) ? Yii::$app->params['OSS']['OSS_ACCESS_ID'] : $this->accessKeyId;
            $this->accessKeySecret = empty($this->accessKeySecret) ? Yii::$app->params['OSS']['OSS_ACCESS_KEY'] : $this->accessKeySecret;
            $this->endpoint = empty($this->endpoint) ? Yii::$app->params['OSS']['OSS_ENDPOINT_WAN'] : $this->endpoint;
            $this->bucket = empty($this->bucket) ? Yii::$app->params['OSS']['OSS_BUCKET'] : $this->bucket;
            $client = new OssClient($this->accessKeyId, $this->accessKeySecret, $this->endpoint);
        } else {
            /**
             * @var s3Service $client
             */
            $client = Yii::$app->get($serviceType);
//            if (!empty($this->accessKeyId) && !empty($this->accessKeySecret)) {
//                $client->setCredentials([
//                    'key'    => $this->accessKeyId,
//                    'secret' => $this->accessKeySecret,
//                ]);
//            }
//            if(!empty($this->endpoint)) {
//                $client->setRegion($this->endpoint);
//            }
        }

        return $client;
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

        $dateString = Carbon::now()->format('Ymd');
        $object = sprintf('%s/%s/%s', $remoteDir, $dateString, $fileName);
        //上传失败抛异常，否则成功，不需要对返回值做校验
        if ($this->serviceType == 'oss') {
            $ossClient = $this->getClient('oss');
            $ossRes = $ossClient->uploadFile($this->bucket, $object, $localPath);
        } else {
            $ossClient = $this->getClient('s3');
            $object = $this->formatS3Path($object);
            /**
             * @var s3UploadCommand $common
             */
            $common = $ossClient->create(s3UploadCommand::class);
//            if (!empty($this->bucket)) {
//                $common->inBucket($this->bucket);
//            }
            $common->withAcl('private')
                ->withFilename($object)
                ->withSource($localPath)
                ->execute();
        }

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
        if ($this->serviceType == 'oss') {
            $ossClient = $this->getClient('oss');
            //上传失败抛异常，否则成功，不需要对返回值做校验
            $ossRes = $ossClient->uploadFile($this->bucket, $filePath, $localPath);
        } else {
            $ossClient = $this->getClient('s3');
            $filePath = $this->formatS3Path($filePath);
            /**
             * @var s3UploadCommand $common
             */
            $common = $ossClient->create(s3UploadCommand::class);
//            if (!empty($this->bucket)) {
//                $common->inBucket($this->bucket);
//            }
            $common->withAcl('private')
                ->withFilename($filePath)
                ->withSource($localPath)
                ->execute();
        }

        if ($delete) {
            @unlink($localPath);
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

        if ($this->serviceType == 'oss' || !$this->isS3Path($remotePath)) {
            $ossClient = $this->getClient('oss');
            $options = [
                OssClient::OSS_FILE_DOWNLOAD => $filePath,
            ];
            $ossClient->getObject($this->bucket, $remotePath, $options);
        } else {
            $ossClient = $this->getClient('s3');
            $remotePath = $this->formatS3Path($remotePath);
            /**
             * @var s3GetCommand $common
             */
            $common = $ossClient->create(s3GetCommand::class);
//            if (!empty($this->bucket)) {
//                $common->inBucket($this->bucket);
//            }
            $common->byFilename($remotePath)
                ->saveAs($filePath)
                ->execute();
        }

        return $filePath;
    }

    public function deleteFile(string $remotePath)
    {
        try {
            //删除失败抛异常，否则成功，不需要对返回值做校验
            if ($this->serviceType == 'oss' || !$this->isS3Path($remotePath)) {
                $ossClient = $this->getClient('oss');
                $ossRes = $ossClient->deleteObject($this->bucket, $remotePath);
            } else {
                $ossClient = $this->getClient('s3');
                $remotePath = $this->formatS3Path($remotePath);
                /**
                 * @var s3DeleteCommand $common
                 */
                $common = $ossClient->create(s3DeleteCommand::class);
//                if (!empty($this->bucket)) {
//                    $common->inBucket($this->bucket);
//                }
                $common->byFilename($remotePath)->execute();
            }
        } catch (\Exception $exception) {
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
    public function getSignedUrl(string $url, int $timeout = 3600, $type = 's3'): string
    {
        if ($this->serviceType == 'oss'|| !$this->isS3Path($url)) {
            $ossClient = $this->getClient('oss');
            $signedUrl = $ossClient->signUrl($this->bucket, $url, $timeout, "GET");
        } else {
            $ossClient = $this->getClient($type);
            $url = $this->formatS3Path($url);
            /**
             * @var s3GetPresignedUrlCommand $common
             */
            $common = $ossClient->create(s3GetPresignedUrlCommand::class);
//            if (!empty($this->bucket)) {
//                $common->inBucket($this->bucket);
//            }
            $signedUrl = $common
                ->byFilename($url)
                ->withExpiration($timeout + time())
                ->execute();
        }

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

        $dateString = Carbon::now()->format('Ymd');
        $object = sprintf('%s/%s/%s', $remotePath, $dateString, $fileName);
        //上传失败抛异常，否则成功，不需要对返回值做校验
        if ($this->serviceType == 'oss') {
            $ossClient = $this->getClient('oss');
            $ossRes = $ossClient->uploadFile($this->bucket, $object, $filePath);
        } else {
            $ossClient = $this->getClient('s3');
            $object = $this->formatS3Path($object);
            /**
             * @var s3UploadCommand $common
             */
            $common = $ossClient->create(s3UploadCommand::class);
//            if (!empty($this->bucket)) {
//                $common->inBucket($this->bucket);
//            }
            $common->withAcl('private')
                ->withFilename($object)
                ->withSource($filePath)
                ->execute();
        }

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

        $dateString = Carbon::now()->format('Ymd');
        $object = sprintf('%s/%s/%s', $remotePath, $dateString, $fileName);
        //上传失败抛异常，否则成功，不需要对返回值做校验
        if ($this->serviceType == 'oss') {
            $ossClient = $this->getClient('oss');
            $ossRes = $ossClient->uploadFile($this->bucket, $object, $filePath);
        } else {
            $ossClient = $this->getClient('s3');
            $object = $this->formatS3Path($object);
            /**
             * @var s3UploadCommand $common
             */
            $common = $ossClient->create(s3UploadCommand::class);
//            if (!empty($this->bucket)) {
//                $common->inBucket($this->bucket);
//            }
            $common->withAcl('private')
                ->withFilename($object)
                ->withSource($filePath)
                ->execute();
        }

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

        //上传失败抛异常，否则成功，不需要对返回值做校验
        if ($this->serviceType == 'oss' || !$this->isS3Path($object)) {
            $ossClient = $this->getClient('oss');
            $ossRes = $ossClient->uploadFile($this->bucket, $object, $filePath);
        } else {
            $ossClient = $this->getClient('s3');
            $object = $this->formatS3Path($object);
            /**
             * @var s3UploadCommand $common
             */
            $common = $ossClient->create(s3UploadCommand::class);
//            if (!empty($this->bucket)) {
//                $common->inBucket($this->bucket);
//            }
            $common->withAcl('private')
                ->withFilename($object)
                ->withSource($filePath)
                ->execute();
        }

        unlink($filePath);

        return $object;
    }

    /**
     * @param string $remotePath OSS存储路径
     * @param string $url 下载文件URL
     * @return string
     */
    public function s3Migrate(string $remotePath, string $url): string
    {
        $fileName = basename(parse_url($url, PHP_URL_PATH));
        $filePath = '/tmp/' . $fileName;

        $client = new Client();
        $response = $client->get($url, ['save_to' => $filePath]);

        $dateString = Carbon::now()->format('Ymd');
        $ossClient = $this->getClient('s3');
        $object = 'oss-migrate/' . $remotePath;
        /**
         * @var s3UploadCommand $common
         */
        $common = $ossClient->create(s3UploadCommand::class);

        $common->withAcl('private')
            ->withFilename($object)
            ->withSource($filePath)
            ->execute();

        unlink($filePath);

        return $object;
    }

    private function isS3Path(string $path)
    {
        return strpos($path,'aws-s3') !== false || strpos($path,'oss-migrate') !== false;
    }

    private function formatS3Path(string $path)
    {
        return $this->isS3Path($path) ? $path : 'aws-s3/' . $path;
    }

    /**
     * 上传文件-按日期分配路径
     * @param string $remoteDir
     * @param string $localPath
     * @param string $extension
     * @param bool $delete
     * @return string
     */
    public function uploadFilePhoto(string $remoteDir, string $json, bool $delete = true):string
    {
        $fileName = uniqid() . '.txt';
        $filePath = '/tmp/' . $fileName;
        file_put_contents($filePath, $json);

        $dateString = Carbon::now()->format('Ymd');
        $object = sprintf('%s/%s/%s', $remoteDir, $dateString, $fileName);
        //上传失败抛异常，否则成功，不需要对返回值做校验

        $ossClient = $this->getClient('s3');
        $object = $this->formatS3Path($object);
        /**
         * @var s3UploadCommand $common
         */
        $common = $ossClient->create(s3UploadCommand::class);
        $common->withAcl('private')
            ->withFilename($object)
            ->withSource($filePath)
            ->execute();

        if ($delete) {
            unlink($filePath);
        }

        return $object;
    }
}