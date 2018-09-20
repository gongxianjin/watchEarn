<?php
/**
 * Created by PhpStorm.
 * User: zilongs
 * Date: 17-11-30
 * Time: 上午12:44
 */

namespace app\common;

use think\Controller;
use think\exception\ValidateException;
use think\Loader;

class MyController extends Controller
{
    
   
    
    /**
     * 验证数据
     * @access protected
     * @param array        $data     数据
     * @param string|array $validate 验证器名或者验证规则数组
     * @param array        $message  提示信息
     * @param bool         $batch    是否批量验证
     * @param mixed        $callback 回调方法（闭包）
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate($data, $validate, $message = [], $batch = false, $callback = null)
    {
        if (is_array($validate)) {
            $v = Loader::validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                list($validate, $scene) = explode('.', $validate);
            }
            $v = Loader::validate($validate);
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }
        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        if (is_array($message)) {
            $v->message($message);
        }

        if ($callback && is_callable($callback)) {
            call_user_func_array($callback, [$v, &$data]);
        }

        if (!$v->check($data)) {
            if ($this->failException) {
                throw new ValidateException($v->getError());
            } else {
                pjson(array('code' => 10001, 'msg' => $v->getError(), 'data' => ''));
                exit;
            }
        } else {
            return true;
        }
    }
    /**
     * 用户密码
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public  function getMd5Pass($pass){
        return md5(md5(crypt($pass,"4715fdf457"))."!#03287418");
    }
    /**
     * 生成auth_token
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public  function makeToken($str){
        return   md5(md5(time().config("token_str").$str));
    }
}