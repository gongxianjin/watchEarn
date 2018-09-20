<?php
class Des3
{
    /**
     *
     * ���ܺ���
     * �㷨��des
     * ����ģʽ��ecb
     * ���뷽����PKCS5
     *
     * @author 1336707969@qq.com
     */

    /**
    * ���ַ�������3DES����
    * @param string Ҫ���ܵ��ַ���
    * @return mixed ���ܳɹ����ؼ��ܺ���ַ��������򷵻�false
    */
    public static function encrypt($str,$key)
    {
        $m = MCRYPT_TRIPLEDES;
        $iv = mcrypt_create_iv(mcrypt_get_iv_size($m,MCRYPT_MODE_ECB), MCRYPT_RAND);
        $td = mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_ECB, '');
        mcrypt_generic_init($td, $key, $iv);
        $result = base64_encode(mcrypt_generic($td,self::pkcs5_padding($str,8)));
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        return $result;
    }

    /**
    * �Լ��ܵ��ַ�������3DES����
    * @param string Ҫ���ܵ��ַ���
    * @return mixed ���ܳɹ����ؼ��ܺ���ַ��������򷵻�false
    */
    public  static  function decrypt($str,$key)
    {
        $m = MCRYPT_TRIPLEDES;
        $iv = mcrypt_create_iv(mcrypt_get_iv_size($m,MCRYPT_MODE_ECB), MCRYPT_RAND);
        $td = mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_ECB, '');
        mcrypt_generic_init($td, $key, $iv);
        $result  = self::pkcs5_unpadding(mdecrypt_generic($td, base64_decode($str)));
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        return $result;
    }


    public static function pkcs5_padding($text, $blocksize)
    {
      $pad = $blocksize - (strlen($text) % $blocksize);
      return $text . str_repeat(chr($pad), $pad);
    }

    public static function pkcs5_unpadding($text)
    {
      $pad = ord($text{strlen($text)-1});
      if ($pad > strlen($text))
      {
        return false;
      }
      if( strspn($text, chr($pad), strlen($text) - $pad) != $pad)
      {
        return false;
      }
      return substr($text, 0, -1 * $pad);
    }
}