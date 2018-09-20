<?php
namespace mail;

require '../vendor/phpmailer/phpmailer/src/PHPMailer.php';

use PHPMailer\PHPMailer\PHPMailer;
use think\Cache;
use think\Db;

class Mail
{

    const SEND_TYPE_REG = 1;
    const SEND_TYPE_FIND_PW = 2;

    /**
     * @var PHPMailer
     */
    public $mail ;

    public function __construct()
    {
        $mail = &$this->mail;
        $mail = new PHPMailer(true);

        $account = $this->getMailAccount();

        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $account['username'];
        $mail->Password = $account['password'];
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom($mail->Username, 'hkzy');

    }

    private function getMailAccount()
    {
        $cacheKey = 'emailConfig';
        $accounts = Cache::get($cacheKey);
        if(empty($accounts)){
            $accountsDb = Db::name('f_config')->where(['name' =>'emailConfig'])->find();
            $file_path = ROOT_PATH . 'public' . $accountsDb['value'];
            if(file_exists($file_path)){
                $accounts = file_get_contents($file_path);
                $accounts = json_decode($accounts,true);
                Cache::set($cacheKey,$accounts,300);
            }
        }

        if(empty($accounts)){
            $accounts = [
                [
                    'username'=>'hkzysdbuy32brubw@gmail.com',
                    'password'=>'tbq-KSW-bE2-DMN',
                ],
                [
                    'username'=>'hangkezhiyu@gmail.com',
                    'password'=>'CET-6EG-D9E-AB8',
                ],
                [
                    'username'=>'watchvideostoearn@gmail.com',
                    'password'=>'hangkezhiyu123',
                ],
                [
                    'username'=>'quwentech@gmail.com',
                    'password'=>'hangkezhiyu123',
                ]
            ];
        }

        $account = $accounts[ rand(0,count($accounts)-1) ];

        return $account;

    }

    /**
     * @param $toMail
     * @param $subject
     * @param $body
     * @param $altBody
     * @return bool
     * @throws \Exception
     */
    public function email($toMail,$body,$subject='',$altBody='')
    {
        try{
            $mail = &$this->mail;
            $mail->addAddress($toMail);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = $altBody;

            return $mail->send();
        }
        catch (\Exception $e)
        {
            throw $e;
        }

    }

}
