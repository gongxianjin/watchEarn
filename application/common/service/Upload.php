<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/4
 * Time: 9:30
 */
namespace app\common\service;

use think\Exception;
use think\Image;

class Upload
{
    private $httpUrl = "";    //网站图片访问地址
    private $uploadPath = "uploads/headImage/";
    private $webIndexPath = "";
    private $tempPath = "uploads/tempHead/";
    private $markStr = "#%1doekdDH";

    public function __construct()
    {
        $this->httpUrl = "http://www.hesheng138.com";
        $this->webIndexPath = ROOT_PATH . "public/";
    }

    //生成缓存文件
    function savePath($meid,$imageFile)
    {
        //生成标志码 和 文件名
        $markId = md5($meid . $this->markStr);
        $image = Image::open($imageFile);
        $imageName = uuid() . "." . $image->type();
        $tempPath = $this->webIndexPath . $this->tempPath . $markId;
        //储存权限
        if (!$this->checkDir($tempPath)) {
            trace("error msg :" . $tempPath . ' insufficient privileges', 'error');
            return false;
        }

        //图片放缓存目录
        $image->save($tempPath . '/' . $imageName);
        return [
            'markId' => $markId,  //标志吗
            'fileName' => $imageName,  //文件名
            "httpUrl" => $this->httpUrl . $this->uploadPath . $imageName  //确认后访问地址
        ];
    }

    //生成缓存文件
    function saveDataPath($meid,$imageFile)
    {
        //生成标志码 和 文件名
        $markId = md5($meid . $this->markStr);
//        $image = Image::open($imageFile);
        $imageName = uuid() . "." . "png";
        $tempPath = $this->webIndexPath . $this->tempPath . $markId;
        //储存权限
        if (!$this->checkDir($tempPath)) {
            trace("error msg :" . $tempPath . ' insufficient privileges', 'error');
            return false;
        }
        file_put_contents($tempPath . '/' . $imageName,$imageFile,true);

        //图片放缓存目录
//        $image->save($tempPath . '/' . $imageName);
        return [
            'markId' => $markId,  //标志吗
            'fileName' => $imageName,  //文件名
            "httpUrl" => $this->httpUrl .'/'. $this->uploadPath . $imageName  //确认后访问地址
        ];
    }

    //base64 文件编码生成文件
    function saveBase64Path($meid,$imageFile)
    {
        //生成标志码 和 文件名
        $markId = md5($meid . $this->markStr);
        $imageName = uuid() . "." . "png";
        $tempPath = $this->webIndexPath . $this->tempPath . $markId;
        //储存权限
        if (!$this->checkDir($tempPath)) {
            trace("error msg :" . $tempPath . ' insufficient privileges', 'error');
            return false;
        }
        

        file_put_contents($tempPath . '/' . $imageName,$imageFile,true);

        //图片放缓存目录
//        $image->save($tempPath . '/' . $imageName);
        return [
            'markId' => $markId,  //标志吗
            'fileName' => $imageName,  //文件名
            "httpUrl" => $this->httpUrl .'/'. $this->uploadPath . $imageName  //确认后访问地址
        ];
    }

    //转移缓存文件
    function saveTempImage($imageName, $markId="")
    {
        //标志码根据session 生成  如果无法保持session 统一 传入标志码
        if (empty($markId)) {
            $markId = md5(session_id() . $this->markStr);
        }
        $tempPath = $this->webIndexPath . $this->tempPath . $markId . "/";
        if (!file_exists($tempPath . $imageName)) {
            //删除缓存文件夹
            $this->deldir($tempPath);
            trace("error msg :" . $tempPath . $imageName . ' temp file is null', 'error');
            return false;
        }

        //储存权限
        $savePath = $this->webIndexPath . $this->uploadPath;
        if (!$this->checkDir($savePath)) {
            //删除缓存文件夹
            $this->deldir($tempPath);
            trace("error msg :" . $this->tempPath . ' insufficient privileges', 'error');
            return false;
        }
        //转移图片
        $res = copy($tempPath . $imageName, $savePath . $imageName);
        if (!$res) {
            //删除缓存文件夹
            $this->deldir($tempPath);
            trace("error msg :" . $imageName . ' copy file error', 'error');
            return false;
        }

        //删除缓存文件夹
        $this->deldir($tempPath);

        return $this->httpUrl .'/'. $this->uploadPath . $imageName;
    }

    //生成地址目录
    function checkDir($path)
    {
        //分析目录
        $pathArray = explode('/', $path);
        $pathTemp = '';
        $canCreate = false;
        foreach ($pathArray as $val) {
            //从网站根目录开始 没有的目录创建目录
            if (!empty($val)) {
                if($pathTemp == ROOT_PATH || $pathTemp . "/" == ROOT_PATH){
                    $canCreate = true;
                }
                $pathTemp .= "/" . $val;
                if($canCreate){
                    if (is_dir($pathTemp)) {
                        continue;
                    }

                    try {
                        mkdir($pathTemp);
                    } catch (Exception $e) {
                        trace("error msg : mkdir --- " . $pathTemp , 'error');
                        return false;
                    }
                }
            }
        }

        //判断创建完成目录是否符合 最初设置要求
        if ($pathTemp == $path || $pathTemp . "/" == $path) {
            return true;
        }
        return false;
    }

    //删除文件
    function deldir($dir)
    {
        //先删除目录下的文件：
        $dh = opendir($dir);
        while ($file = readdir($dh)) {
            if ($file != "." && $file != "..") {
                $fullpath = $dir . "/" . $file;
                if (!is_dir($fullpath)) {
                    unlink($fullpath);
                } else {
                    $this->deldir($fullpath);
                }
            }
        }
        closedir($dh);

        //删除当前文件夹：
        if (rmdir($dir)) {
            return true;
        } else {
            return false;
        }
    }
}