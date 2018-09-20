<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/15
 * Time: 11:46
 */
require_once 'aliyun-sdk/aliyun-php-sdk-core/Config.php';   // 假定您的源码文件和aliyun-php-sdk处于同一目录
use Sts\Request\V20150401 as Sts;

class AliyunSts
{
    public $regionId = 'cn-shanghai';
    public $endPint = "sts.cn-shanghai.aliyuncs.com";
    public $client;

    function __construct()
    {
        $accessKeyId = \think\Env::get('aliyun.accessKeyId');
        $accessKeySecret = \think\Env::get('aliyun.accessKeySecret');

        // 只允许子用户使用角色
        DefaultProfile::addEndpoint($this->regionId, $this->regionId, "Sts", $this->endPint);
        $iClientProfile = DefaultProfile::getProfile($this->regionId, $accessKeyId,$accessKeySecret);
        $this->client = new DefaultAcsClient($iClientProfile);
    }


    function getTokenInfo($user_id){
        if(empty($user_id)){
            return false;
        }
        // 角色资源描述符，在RAM的控制台的资源详情页上可以获取
        // 在扮演角色(AssumeRole)时，可以附加一个授权策略，进一步限制角色的权限；
        // 详情请参考《RAM使用指南》
        // 此授权策略表示读取所有OSS的只读权限
        $roleArn = \think\Env::get('aliyun.roleArn');
        $policy=<<<POLICY
{
  "Version": "1",
  "Statement": [
    {
      "Action": [
        "vod:CreateUploadVideo",
        "vod:RefreshUploadVideo",
        "vod:CreateUploadImage"
      ],
      "Resource": "*",
      "Effect": "Allow"
    }
  ]
}
POLICY;
        $request = new Sts\AssumeRoleRequest();
        // RoleSessionName即临时身份的会话名称，用于区分不同的临时身份
        // 您可以使用您的客户的ID作为会话名称
        $request->setRoleSessionName($user_id);
        $request->setRoleArn($roleArn);
        $request->setPolicy($policy);
        $request->setDurationSeconds(3600);
        return $this->client->getAcsResponse($request);
    }
}