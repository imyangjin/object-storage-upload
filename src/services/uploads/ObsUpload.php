<?php

namespace vhallComponent\common\services\uploads;

use Exception;
use Obs\ObsClient;

/**
 * ObsUpload 华为云上传
 *
 * @uses     yangjin
 * @date     2021-12-11
 * @author   imyangjin@vip.qq.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class ObsUpload implements UploadServiceInterface
{
    public $bucket;

    public $prefix;

    public $accessKey;

    public $secretKey;

    public $endPoint;

    /**
     * ObsUploadServices constructor.
     *
     * @param $prefix
     * @param $accessKey
     * @param $secretKey
     * @param $bucket
     * @param $endPoint
     */
    public function __construct($prefix, $accessKey, $secretKey, $bucket, $endPoint)
    {
        $this->bucket    = $bucket;
        $this->accessKey = $accessKey;
        $this->secretKey = $secretKey;
        $this->prefix    = $prefix;
        $this->endPoint  = $endPoint;
    }

    /**
     *
     * @param $filePath
     *
     * @return false|string
     */
    public function uploadToObs($filePath, $uploadFilePath)
    {
        try {
            $obsClient = new ObsClient([
                'key'      => $this->accessKey,
                'secret'   => $this->secretKey,
                'endpoint' => $this->endPoint,
            ]);

            $result = $obsClient->putObject([
                'Bucket'      => $this->bucket,
                'Key'         => $this->prefix . $uploadFilePath,
                'SourceFile'  => $filePath
            ]);
            $result['ObjectURL'];
            if ($result['ObjectURL']) {
                @unlink($filePath);
                return 'https://' . $result['Location'];
            }
            return false;
        } catch (Exception $e) {
            return "$e\n";
        }
    }

    public function upload($localFilePath, $uploadFilePath = '')
    {
        return $this->uploadToObs($localFilePath, $uploadFilePath);
    }
}
