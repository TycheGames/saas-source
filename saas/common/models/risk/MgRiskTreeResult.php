<?php

namespace common\models\risk;

use Yii;
use MongoDB\BSON\ObjectId;
use yii\behaviors\TimestampBehavior;
use yii\mongodb\Connection;
use yii\mongodb\ActiveRecord;

/**
 * This is the model class for collection "risk_tree_result".
 *
 * @property ObjectID|string $_id
 * @property mixed $order_id
 * @property mixed $user_id
 * @property mixed $base_node
 * @property mixed $guard_node
 * @property mixed $manual_node
 * @property mixed $result
 * @property mixed $created_at
 * @property mixed $updated_at
 */
class MgRiskTreeResult extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function collectionName()
    {
        return 'risk_tree_result';
    }

    /**
     * @return Connection the MongoDB connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('mongodb_risk');
    }

    /**
     * {@inheritdoc}
     */
    public function attributes()
    {
        return [
            '_id',
            'order_id',
            'user_id',
            'base_node',
            'guard_node',
            'manual_node',
            'result',
            'created_at',
            'updated_at',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'user_id', 'base_node', 'guard_node', 'manual_node', 'result', 'created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            '_id'        => 'ID',
            'order_id'   => 'Order ID',
            'user_id'    => 'User ID',
            'base_node'  => 'Base Node',
            'guard_node' => 'Guard Node',
            'manual_node' => 'Manual Node',
            'result'     => 'Result',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @inheritdoc
     * @return array
     */
    public function behaviors(){
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * @param int $orderId
     * @param array $resultData
     * @return bool
     */
    public static function insertRiskResultSnapshot(int $orderId,array $resultData):bool
    {
        $model = new MgRiskTreeResult();
        $model->order_id = $orderId;
        $model->base_node = $resultData['base_node'];
        $model->guard_node = $resultData['guard_node'];
        $model->result = $resultData['result'];

        return $model->save();
    }


    /**
     * 风控被拒原因统计
     * @param $node string T101是前置风控 T102是后置风控
     * @param $isPreposition bool true 是前置风控  false 不是前置风控
     * @param $beginTime int 开始时间
     * @param $endTime int 结束时间
     * @return array|\MongoDB\Driver\Cursor
     * @throws \yii\mongodb\Exception
     */
    public static function rejectReasonStatistics($node, $isPreposition, $beginTime, $endTime, $useCache = true ,$expire = 300 )
    {
        $cacheKey = 'query:cache:'. self::class . ':'. md5(implode(':', func_get_args()));
        if($useCache && yii::$app->cache->get($cacheKey))
        {
            return json_decode(yii::$app->cache->get($cacheKey), true);
        }
        $match = [
            'result.'.$node.'.result' => 'reject',
            'created_at' => [
                '$gte' => $beginTime,
                '$lt' => $endTime
            ]
        ];
        if($isPreposition)
        {
            $match['order_id'] = 0;
        }
        $r = self::getCollection()->aggregate([
            [
                '$match' => $match
            ],
            [
                '$group' => [
                    '_id'   =>  '$result.'.$node.'.txt',
                    'num_tutorial'  =>  [
                        '$sum'  => 1
                    ]
                ]
            ]
        ]);

        yii::$app->cache->set($cacheKey, json_encode($r, JSON_UNESCAPED_UNICODE), $expire);
        return $r;
    }
}
