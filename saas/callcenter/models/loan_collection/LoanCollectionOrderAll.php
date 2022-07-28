<?php

namespace callcenter\models\loan_collection;

use Yii;
use yii\db\ActiveRecord;

/**
 * Class LoanCollectionOrderAll
 * @package callcenter\models\loan_collection
 * @property int $id
 * @property int $user_id
 * @property int $loan_collection_order_id
 * @property int $user_loan_order_repayment_id
 * @property int $dispatch_time
 * @property int $current_collection_admin_user_id
 * @property int $current_overdue_level
 * @property int $current_overdue_group
 * @property int $outside_id
 * @property int $last_collection_time
 * @property int $overdue_status
 * @property int $customer_type
 * @property int $status
 * @property int $that_day_status
 * @property int $created_at
 * @property int $updated_at
 */
class LoanCollectionOrderAll extends ActiveRecord
{
    const REDEPLOY_YES =1;  //已过期
    const REDEPLOY_NO =0;  //正常

    const THAT_DAY_STATUS_RETURN = 1;  //当天已重新分派
    const THAT_DAY_STATUS_IN_HANDS = 0;  //当天分派当天在手中-默认

    public static function tableName()
    {
        return '{{%loan_collection_order_all}}';
    }

    /**
     * @return object|\yii\db\Connection|null
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb()
    {
        return Yii::$app->get('db_assist');
    }

    /**
     * @return object|\yii\db\Connection|null
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb_rd()
    {
        return Yii::$app->get('db_assist_read');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'loan_collection_order_id', 'user_loan_order_repayment_id', 'dispatch_time', 'current_collection_admin_user_id', 'current_overdue_level', 'last_collection_time', 'created_at', 'updated_at'], 'integer'],
        ];
    }

    public static function ids($ids = array()){
        $result = array();
        $res = self::find()->select("*")->where("`loan_collection_order_id` IN(".implode(',', $ids).")")->andWhere(['status'=>0])->all();
        if(!empty($res)){
            foreach ($res as $key => $item) {
                $result[$item['loan_collection_order_id']] = $item;
            }
        }
        return $result;
    }
}