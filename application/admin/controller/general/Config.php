<?php

namespace app\admin\controller\general;

use app\common\controller\Backend;
use app\common\library\Email;
use app\common\model\Config as ConfigModel;
use think\Exception;

/**
 * 系统配置
 *
 * @icon fa fa-circle-o
 */
class Config extends Backend
{

    protected $model = null;
    protected $noNeedRight = ['check'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Config');
    }

    public function index()
    {
        $siteList = [];
        $groupList = ConfigModel::getGroupList();
        foreach ($groupList as $k => $v)
        {
            $siteList[$k]['name'] = $k;
            $siteList[$k]['title'] = $v;
            $siteList[$k]['list'] = [];
        }

        foreach ($this->model->all() as $k => $v)
        {
            if (!isset($siteList[$v['group']]))
            {
                continue;
            }
            $value = $v->toArray();
            $value['title'] = __($value['title']);
            if (in_array($value['type'], ['select', 'selects', 'checkbox', 'radio']))
            {
                $value['value'] = explode(',', $value['value']);
            }
            if ($value['type'] == 'array')
            {
                $value['value'] = (array) json_decode($value['value'], TRUE);
            }
            $value['content'] = json_decode($value['content'], TRUE);
            $siteList[$v['group']]['list'][] = $value;
        }
        $index = 0;
        foreach ($siteList as $k => &$v)
        {
            $v['active'] = !$index ? true : false;
            $index++;
        }
        $this->view->assign('siteList', $siteList);
        $this->view->assign('typeList', ConfigModel::getTypeList());
        $this->view->assign('groupList', ConfigModel::getGroupList());
        return $this->view->fetch();
    }

    /**
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost())
        {
            $params = $this->request->post("row/a");
            if ($params)
            {
                foreach ($params as $k => &$v)
                {
                    $v = is_array($v) ? implode(',', $v) : $v;
                }
                try
                {
                    if (in_array($params['type'], ['select', 'selects', 'checkbox', 'radio', 'array']))
                    {
                        $params['content'] = ConfigModel::decode($params['content']);
                    }
                    else
                    {
                        $params['content'] = '';
                    }
                    $result = $this->model->create($params);
                    if ($result !== false)
                    {
                        try
                        {
                            $this->refreshFile();
                            $this->success();
                        }
                        catch (Exception $e)
                        {
                            $this->error($e->getMessage());
                        }
                    }
                    else
                    {
                        $this->error($this->model->getError());
                    }
                }
                catch (Exception $e)
                {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        return $this->view->fetch();
    }

    public function edit($ids = NULL)
    {
        if ($this->request->isPost())
        {
            $row = $this->request->post("row/a");
            if ($row)
            {
                $configList = [];
                foreach ($this->model->all() as $v)
                {
                    if (isset($row[$v['name']]))
                    {
                        $value = $row[$v['name']];
                        if (is_array($value) && isset($value['field']))
                        {
                            $value = json_encode(\app\common\model\Config::getArrayData($value), JSON_UNESCAPED_UNICODE);
                        }
                        else
                        {
                            $value = is_array($value) ? implode(',', $value) : $value;
                        }
                        $v['value'] = $value;
                        $configList[] = $v->toArray();
                    }
                }
                $this->model->allowField(true)->saveAll($configList);
                try
                {
                    $this->refreshFile();
                    $this->success();
                }
                catch (Exception $e)
                {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
    }

    protected function refreshFile()
    {
        $config = [];
        foreach ($this->model->all() as $k => $v)
        {

            $value = $v->toArray();
            if (in_array($value['type'], ['selects', 'checkbox', 'images', 'files']))
            {
                $value['value'] = explode(',', $value['value']);
            }
            if ($value['type'] == 'array')
            {
                $value['value'] = (array) json_decode($value['value'], TRUE);
            }
            $config[$value['name']] = $value['value'];
        }
        file_put_contents(APP_PATH . 'extra' . DS . 'site.php', '<?php' . "\n\nreturn " . var_export($config, true) . ";");
    }

    /**
     * @internal
     */
    public function check()
    {
        $params = $this->request->post("row/a");
        if ($params)
        {

            $config = $this->model->get($params);
            if (!$config)
            {
                return json(['ok' => '']);
            }
            else
            {
                return json(['error' => __('Name already exist')]);
            }
        }
        else
        {
            return json(['error' => __('Invalid parameters')]);
        }
    }

    /**
     * 发送测试邮件
     * @internal
     */
    public function emailtest()
    {
        $receiver = $this->request->request("receiver");
        $email = new Email;
        $result = $email
                ->to($receiver)
                ->subject(__("This is a test mail"))
                ->message('<div style="min-height:550px; padding: 100px 55px 200px;">' . __('This is a test mail content') . '</div>')
                ->send();
        if ($result)
        {
            $this->success();
        }
        else
        {
            $this->error($email->getError());
        }
    }

}
