<?php

namespace console\controllers;

use common\helpers\CommonHelper;
use common\models\RiskOrder;
use common\services\order\PushOrderRiskService;
use yii\db\Exception;
use yii;
use yii\console\ExitCode;

class MockController extends BaseController {

    /**
     * 主决策审核
     * @param $id
     * @param $type
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function actionAutoCheck($id,$type){
        if(!in_array($type, [1,2,3])){
            echo '参数错误';
            exit;
        }

        /**
         * @var RiskOrder $riskOrder
         */
        $riskOrder = RiskOrder::findOne($id);
        if (empty($riskOrder)) {
            $this->printMessage("订单不存在");
            return;
        }

        if (RiskOrder::STATUS_WAIT_CHECK != $riskOrder->status) {
            $this->printMessage('订单状态错误');
            return;
        }

        switch ($type)
        {
            case 1://通过
                $riskOrder->changeStatus(RiskOrder::STATUS_CHECK_SUCCESS);
                $params['risk'] = [
                    "result"    => "approve",
                    "interval"  => 0,
                    "head_code" => "",
                    "back_code" => "",
                    "txt"       => "approve"
                ];
                $params['amount'] = ['result' => 2700];
                break;
            case 2://拒绝
                $riskOrder->changeStatus(RiskOrder::STATUS_CHECK_REJECT);
                $params['risk'] = [
                    "result"    => "reject",
                    "interval"  => 7,
                    "head_code" => "B",
                    "back_code" => "051",
                    "txt"       => "InconsistentDOB"
                ];
                break;
            case 3://转人工
                $riskOrder->changeStatus(RiskOrder::STATUS_CHECK_MANUAL);
                $params['risk'] = [
                    "result"       => "manual",
                    "interval"     => 0,
                    "head_code"    => "MNew1",
                    "back_code"    => "10",
                    "txt"          => "ResidentialAddressHitWhitelistDataAcquisitionException",
                    "ManualModule" => "Module1"
                ];
                $params['amount'] = ['result' => 1700];
                break;
            default:
        }

        $pushService = new PushOrderRiskService($riskOrder->infoOrder->product_source);
        $res = $pushService->pushOrderRisk($riskOrder->order_id, $params);
        if(isset($res['code']) && $res['code'] == 0){
            $riskOrder->is_push = RiskOrder::IS_PUSH_YES;
            $riskOrder->save();
        }else{
            $this->printMessage('风控订单回调失败，需手动处理');
        }

        $this->printMessage('脚本结束');
    }
}

