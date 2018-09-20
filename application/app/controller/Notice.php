<?php
namespace app\app\controller;


use app\model\Notice as NoticeModel;
use app\app\controller\BaseController;

class Notice extends BaseController
{

//    public function index()
//    {
//        $notice = new NoticeModel();
//        $data=$notice->todayInfo();
//
//        if($data!==null)
//        {
//            foreach ($data as &$item)
//            {
//                $item =  $item->toArray();
//                $item['create_time']!=0 && $item['create_time'] = date('Y-m-d',$item['create_time']);
//                $item['start_date']!=0 && $item['start_date'] = date('Y-m-d',$item['start_date']);
//                $item['end_date']!=0 && $item['end_date'] = date('Y-m-d',$item['end_date']);
//            }
//        }
//
//        return out($data);
//    }

    public function system()
    {
        $notice = new NoticeModel();
        $data=$notice->todayInfo($notice::SYSTEM_NOTICE);

        if($data!==null)
        {
            foreach ($data as &$item)
            {
                $item =  $item->toArray();
                $item['create_time']!=0 && $item['create_time'] = date('Y-m-d',$item['create_time']);
                $item['start_date']!=0 && $item['start_date'] = date('Y-m-d',$item['start_date']);
                $item['end_date']!=0 && $item['end_date'] = date('Y-m-d',$item['end_date']);
                $item['convert'] = config('ad_domain').$item['convert'];
            }
        }

        return out($data);
    }

    public function alert()
    {
        $notice = new NoticeModel();
        $data=$notice->todayInfo($notice::ALERT_NOTICE);

        if($data!==null)
        {
            foreach ($data as &$item)
            {
                $item =  $item->toArray();
                $item['create_time']!=0 && $item['create_time'] = date('Y-m-d',$item['create_time']);
                $item['start_date']!=0 && $item['start_date'] = date('Y-m-d',$item['start_date']);
                $item['end_date']!=0 && $item['end_date'] = date('Y-m-d',$item['end_date']);
                $item['convert'] = config('ad_domain').$item['convert'];
            }
        }

        return out($data);
    }




}
