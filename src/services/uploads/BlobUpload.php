<?php

namespace imyangjin\objectStorageUpload\services\uploads;

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;

/**
 * BlobUpload
 *
 * @uses     yangjin
 * @date     2021-11-29
 * @author   imyangjin@vip.qq.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class BlobUpload implements UploadServiceInterface
{
    public $container;              //容器

    public $accountName;

    public $accountKey;

    public $suffix;                 //端点后缀

    public $protocol = 'http';      //端点协议

    public $cacertUrl;              //证书绝对路径    https时需要

    /**
     * CosUploadServices constructor.
     *
     * @param $secretId
     * @param $bucket
     * @param $secretKey
     */
    public function __construct($accountName, $accountKey, $container, $suffix, $protocol = 'http', $cacertUrl = '')
    {
        $this->accountName = $accountName;
        $this->accountKey  = $accountKey;
        $this->container   = $container;
        $this->suffix      = $suffix;
        $this->protocol    = $protocol;
        $this->cacertUrl   = $cacertUrl;
    }

    public function upload($localFilePath, $uploadFilePath)
    {
        return $this->uploadToBlob($localFilePath, $uploadFilePath) ?: '';
    }

    /**
     *
     * @param        $file
     * @param        $filePath
     * @param string $newFile
     * @param string $dir
     *
     * @return bool|string
     */
    public function uploadToBlob($file, $filePath)
    {
        try {
            //连接参数
            $connectionString = $this->getConnectionString();

            $options = [];
            $this->protocol == 'https' && $options['http'] = ['verify' => $this->cacertUrl];
            //创建服务
            $blobRestProxy = BlobRestProxy::createBlobService($connectionString, $options);
            //上传文件
            $content = @fopen($file, 'r');
            $blobRestProxy->createBlockBlob($this->container, $filePath, $content);
            //获取文件路径
            $imageUrl = $blobRestProxy->getBlobUrl($this->container, $filePath);
            if ($imageUrl) {
                @unlink($file->getPathname());
                return $imageUrl;
            }
            return false;
        } catch (ServiceException $e) {
            $code          = $e->getCode();
            $error_message = $e->getMessage();
            return $code . ': ' . $error_message . "\n";
        }
    }

    /**
     * 连接参数
     * @return string
     */
    private function getConnectionString(): string
    {
        $connectionString = 'DefaultEndpointsProtocol=' . $this->protocol
            . ';AccountName=' . $this->accountName . ';AccountKey=' . $this->accountKey;
        //端点后缀
        $this->suffix && $connectionString = $connectionString . ';EndpointSuffix=' . $this->suffix;
        return $connectionString;
    }
}
