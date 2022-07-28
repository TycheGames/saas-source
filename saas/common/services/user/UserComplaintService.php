<?php


namespace common\services\user;

use common\models\user\LoanPerson;
use common\models\workOrder\UserApplyComplaint;
use common\services\BaseService;
use common\services\FileStorageService;
use frontend\models\user\ApplyComplaintForm;


class UserComplaintService extends BaseService
{

    public static $problem_map = [
        '1' => 'Review process is too long',
        '2' => 'Credit amount related',
        '3' => 'Repayment related',
        '4' => 'Collection related (Complaint Collector)',
        '5' => 'Others'
    ];

    public function saveUserComplaintInfo(ApplyComplaintForm $form)
    {
        //检查是否有进行中的工单
//        if(UserApplyComplaint::isAcceptProgressByUserId($form->userId)){
//            $this->setError('Complaint work order in progress');
//            return false;
//        }
        $imageList = [];
        if(!empty($form->fileList) && is_array($form->fileList)){
            $service = new FileStorageService();
            foreach ($form->fileList as $value){
                $url = $service->uploadFileByPictureBase64('india/complaint',$value);
                $imageList[] = $url;
            }
        }
        $imageListStr = json_encode($imageList);
        $loanPerson = LoanPerson::findOne($form->userId);
        $res = UserApplyComplaint::createComplaintWorkOrder($loanPerson->merchant_id,$form->userId,$form->problemId,$form->description,$form->contact,$imageListStr);
        if(!$res){
            $this->setError('Complaint work order add fail');
            return false;
        }
        return true;
    }

    public function getUserComplaintRecord($userId)
    {
        //检查是否有进行中的工单
        $list = UserApplyComplaint::find()
            ->select(['id','problem_id','description','created_at'])
            ->where(['user_id' => $userId])
            ->orderBy(['id' => SORT_DESC])->asArray()->all();
        $result = [];
        foreach ($list as $val){
            $data = [];
            $data['date'] = date('Y-m-d',$val['created_at']);
            $data['reason'] = self::$problem_map[$val['problem_id']] ?? '--';
            $data['description'] = $val['description'];
            $result[] = $data;
        }
        return $result;
    }

    public function getProblems()
    {
        $data = [];
        //检查是否有进行中的工单
        foreach (self::$problem_map as $k => $v){
            $data[] = ['id' => $k,'text' => $v];
        }
        return $data;
    }
}