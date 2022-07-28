<?php

namespace common\services\export;

use backend\models\AdminUser;
use backend\models\ExportTimeTask;
use callcenter\models\CollectionCheckinLog;
use callcenter\models\CollectorBackMoney;
use callcenter\models\loan_collection\LoanCollectionOrder;
use callcenter\models\loan_collection\LoanCollectionRecord;
use callcenter\models\loan_collection\UserCompany;
use common\helpers\Util;
use common\models\enum\PackageName;
use common\models\financial\FinancialPaymentOrder;
use common\models\fund\LoanFund;
use common\models\kudos\LoanKudosOrder;
use common\models\order\FinancialLoanRecord;
use common\models\order\UserLoanOrder;
use common\models\order\UserLoanOrderExternal;
use common\models\order\UserLoanOrderExtraRelation;
use common\models\order\UserLoanOrderRepayment;
use common\models\order\UserRepaymentLog;
use common\models\user\LoanPerson;
use common\models\user\UserCaptcha;
use common\models\user\UserVerification;
use common\models\user\UserVerificationLog;
use common\models\user\UserWorkInfo;
use common\services\FileStorageService;
use Yii;
use yii\base\BaseObject;
use yii\base\Exception;
use yii\helpers\ArrayHelper;

/**
 * 后台导出服务
 */
class BackendReportService extends BaseObject
{
    public $ossAccessKeyId = 'xxxx';
    public $ossAccessKeySecret = 'xxxx';
    public $ossBucket = 'ytwl-test';
    public $ossEndpoint = 'oss-cn-shanghai.aliyuncs.com';


    public function collectorSalaryCalcBackend($beginDate, $endDate)
    {
        $startDate = $beginDate;

        $thresholdAmount = 1200;
        $thresholdCount = 50;
        $list = [];
        $datas = CollectorBackMoney::find()->select(['date', 'admin_user_id', 'back_money'])
            ->where(['>=' , 'date', $startDate])->andWhere(['<', 'date', $endDate])->orderBy(['date' => SORT_ASC])->asArray()->all();


        $teamMap = ArrayHelper::map(UserCompany::find()->select(['id', 'real_title'])->asArray()->all(), 'id' , 'real_title');


        foreach ($datas as $data)
        {
            $uid = $data['admin_user_id'];
            $date = $data['date'];
            $canGetSalary = 'y'; //是否可以获取薪资 y/n
            $isDiscount = 'n'; //是否打9折 y/n
            $backAmount = $data['back_money'] / 100; //摧回金额
            $checkinTime = '-'; //上班打卡时间
            $checkoutTime = '-'; //下班打卡时间

            $adminUser = \callcenter\models\AdminUser::find()->select(['username', 'real_name', 'outside', 'group'])->where(['id' => $uid])->one();
            $username = $adminUser['username'];
            $realname = $adminUser['real_name'];
            $level = LoanCollectionOrder::$level[$adminUser['group']];
            $team = $teamMap[$adminUser['outside']];
            $recordCount = 0; //催记条数

            //如果催回金额小于阈值，则无获取薪资资格
            if($backAmount < $thresholdAmount)
            {
                $canGetSalary = 'n';
            }

            $checkin = CollectionCheckinLog::find()->select(['created_at'])
                ->where(['user_id' => $uid, 'date' => $date, 'type' => CollectionCheckinLog::TYPE_START_WORK])
                ->orderBy(['id' => SORT_ASC])->asArray()->one();
            //如果上班打卡不存在，则无获取薪资资格
            if(empty($checkin))
            {
                $canGetSalary = 'n';
            }else{
                $checkinTime = date('Y-m-d H:i:s', $checkin['created_at']);

                $checkout = CollectionCheckinLog::find()->select(['created_at'])
                    ->where(['user_id' => $uid, 'date' => $date, 'type' => CollectionCheckinLog::TYPE_OFF_WORK])
                    ->orderBy(['id' => SORT_DESC])->asArray()->one();
                if(!empty($checkout))
                {
                    $checkoutTime = date('Y-m-d H:i:s', $checkout['created_at']);
                }
            }

            $recordCount = LoanCollectionRecord::find()
                ->where(['operator' => $uid])
                ->andWhere(['>=', 'created_at', strtotime($date)])
                ->andWhere(['<', 'created_at', strtotime($date) + 86400])
                ->count();

            //催记小于50条，则薪资打9折
            if($recordCount < $thresholdCount)
            {
                $isDiscount = 'y';
            }


            $list[] = [
                '日期' => $date,
                '机构' => $team,
                '账龄' => $level,
                '用户名' => $username,
                '真实姓名' => $realname,
                '催回金额' => $backAmount,
                '催记条数' => $recordCount,
                '上班打卡时间' => $checkinTime,
                '下班打卡时间' => $checkoutTime,
                '是否获得薪资' => $canGetSalary,
                '是否打9折' => $isDiscount
            ];

        }



        $file = strtoupper(md5(uniqid(mt_rand(), true))) . '.csv';
        $path = '/tmp/';
        $csv_file = $path . $file;
        $ossPath = 'india/collection/saas-salary'.$file;
        $this->_exportSearchData($list, $csv_file);
        $service = new FileStorageService(true,$this->ossBucket,$this->ossAccessKeyId,$this->ossAccessKeySecret,$this->ossEndpoint, 'oss');
        $service->uploadFileByPath(
            $ossPath,
            $csv_file
        );

        var_dump($ossPath);
    }


    /**
     * 导出方法
     * @param $datas
     * @param int $i
     */
    private function _exportSearchData($datas, $file) {
        return $this->_array2csv($datas, $file);
    }

    protected function _array2csv(array &$array, $file)
    {
        if (count($array) == 0) {
            return null;
        }
        // set_time_limit(0);//响应时间改为60秒
        // ini_set('memory_limit', '512M');
        ob_start();
        // $df = fopen("php://output", 'w');
        $df = fopen($file, 'w');
        fwrite($df,chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($df, array_keys(reset($array)));
        foreach ($array as $row) {
            fputcsv($df, $row);
        }
        fclose($df);
        return ob_get_clean();
    }



}
