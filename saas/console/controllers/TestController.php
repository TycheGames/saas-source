<?php

namespace console\controllers;

use Carbon\Carbon;
use common\models\order\EsUserLoanOrder;
use common\services\test\TestService;

class TestController extends BaseController
{

    public function actionTestEs()
    {
        EsUserLoanOrder::createIndex();

        $user_id = mt_rand(1,100);

        $model = new EsUserLoanOrder();
        $model->user_id = $user_id;
        $model->order_id = $user_id;
        $model->merchant_id = 1;
        $model->order_time = Carbon::now()->toIso8601ZuluString();
        $model->location = [
            'lat' =>  17.197777,
            'lon' => 78.484739,
        ];
        $primaryKey = $model->user_id . '_' . $model->order_id;
        $model->setPrimaryKey($primaryKey);
        $model->save();

        sleep(8);

        $test = EsUserLoanOrder::findOne(['user_id' => $user_id])->toArray();
        var_dump($test);
    }

    /**
     * @name TestController 生成用户测试数据 [test/generate-user-test-data]
     */
    public function actionGenerateUserTestData()
    {
        // 需要生成的用户数量
        $nCount = 72;
        $oTest  = new TestService([
            'package_name' => 'icredit',
        ]);

        try {
            while ($nCount > 0) {
                $result = $oTest->generateUser();
                if (!empty($result)) {
                    echo '用户ID为：' . $result . "-生成成功\r\n";
                    $nCount --;
                } else {
                    echo "error \r\n";
                }
            }
        } catch (\Exception $exception) {
            var_dump($exception->getMessage());
            var_dump($exception->getTraceAsString());
        }

    }// END actionGenerateUserTestData






}// END CLASS