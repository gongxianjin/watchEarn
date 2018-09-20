<?php

/**
 * Created by PhpStorm.
 * User: zilongs
 * Date: 18-2-1
 * Time: 下午3:42
 */

namespace app\common\library;

use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\LabelAlignment;
use Endroid\QrCode\QrCode;

class Util
{
    public static function generateQrCode($url, $filename)
    {
        $url_array = parse_url($url);
        $domain = config("share_img_host");

        $filename = md5($filename).'.png';
        $filepath = ROOT_PATH . 'public/qr/qrSource/'.$filename;
        //如果文件存在则直接返回
        if (file_exists($filepath)){
            return $domain.'/qr/qrSource/'.$filename;
        }

        self::saveQrCode($url, $filepath);

        return $domain.'/qr/qrSource/'.$filename;
    }

    public static function generateWatermarkQrCode($url, $filename)
    {
        $url_array = parse_url($url);
        $domain = config("share_img_host");
        $riqi = date('Ymd');
        $QRfilename =md5($filename).'.png';
        $dst_path = ROOT_PATH . 'public/qr/shareTemplate1.jpg';
        $src_path = ROOT_PATH . 'public/qr/qrSource/'.$riqi."/".$QRfilename;
        
        if (!is_dir("./qr/qrSource/".$riqi)){
            @mkdir("./qr/qrSource/".$riqi,0777,true);
        }
        if (!is_dir("./qr/watermark/".$riqi)){
            @mkdir("./qr/watermark/".$riqi,0777,true);
        }
        //如果文件存在则直接返回
        if (!file_exists($src_path)){
            self::saveQrCode($url, $src_path);
        }

        $watermarkFilename =md5($filename.'_share');
        $path = ROOT_PATH . 'public/qr/watermark/'.$riqi."/";
        $watermark_src_path = $path.$watermarkFilename.'.jpg';
        //如果文件存在则直接返回
        if (file_exists($watermark_src_path)){
            return $domain.'/qr/watermark/'.$riqi."/".$watermarkFilename.'.jpg';
        }

        //创建图片的实例
        $dst = imagecreatefromstring(file_get_contents($dst_path));
        $src = imagecreatefromstring(file_get_contents($src_path));

        //获取水印图片的宽高
        list($src_w, $src_h) = getimagesize($src_path);

        //将水印图片复制到目标图片上，最后个参数50是设置透明度，这里实现半透明效果
        imagecopymerge($dst, $src, 210, 676, 0, 0, $src_w, $src_h, 100);

        //输出图片
        list($dst_w, $dst_h, $dst_type) = getimagesize($dst_path);
        switch ($dst_type) {
            case 1://GIF
                $watermarkFilename = $watermarkFilename.'.gif';
                $path = $path.$watermarkFilename;
                imagegif($dst, $path);
                break;
            case 2://JPG
                $watermarkFilename = $watermarkFilename.'.jpg';
                $path = $path.$watermarkFilename;
                imagejpeg($dst, $path);
                break;
            case 3://PNG
                $watermarkFilename = $watermarkFilename.'.png';
                $path = $path.$watermarkFilename;
                imagepng($dst, $path);
                break;
            default:
                break;
        }

        imagedestroy($dst);
        imagedestroy($src);

        return $domain.'/qr/watermark/'.$riqi."/".$watermarkFilename;
    }

    private static function saveQrCode($url, $filepath)
    {
        $qrCode = new QrCode($url);
        $qrCode->setSize(280);
        $qrCode->setWriterByName('png');
        $qrCode->setMargin(10);
        $qrCode->setEncoding('UTF-8');
        $qrCode->setErrorCorrectionLevel(ErrorCorrectionLevel::LOW);
        $qrCode->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0]);
        $qrCode->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0]);
        /*$qrCode->setLabel('my Qr');
        $qrCode->setLogoPath(__DIR__.'/../assets/images/symfony.png');
        $qrCode->setLogoWidth(150);*/
        //$qrCode->setRoundBlockSize(true);
        $qrCode->setValidateResult(false);
        $qrCode->writeFile($filepath);
    }
}