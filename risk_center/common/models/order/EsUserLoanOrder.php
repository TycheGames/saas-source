<?php


namespace common\models\order;


use yii\elasticsearch\ActiveRecord;
use Yii;

/**
 * Class EsUserLoanOrder
 * @package common\models\order
 *
 * 表属性
 * @property int $id
 * @property string $app_name
 * @property int $user_id
 * @property int $order_id
 * @property string $order_time
 * @property array $location
 *
 */
class EsUserLoanOrder extends ActiveRecord
{
    public static function getDb()
    {
        return Yii::$app->get('elasticsearch');
    }

    public static function index()
    {
        return 'risk_order';
    }

    public static function type()
    {
        return 'risk_order';
    }

    public function attributes()
    {
        return array_keys(self::mapping()[static::type()]['properties']);
    }

    public function attributeLabels()
    {
        return [
            'user_id'    => 'User ID',
            'order_id'   => 'Order ID',
            'order_time' => 'Order Time',
            'location'   => 'Location',
        ];
    }

    public static function mapping()
    {
        return [
            static::type() => [
                'properties' => [
                    'app_name'    => ['type' => 'string'],
                    'user_id'     => ['type' => 'long'],
                    'order_id'    => ['type' => 'long'],
                    'order_time'  => ['type' => 'date'],
                    'location'    => ['type' => 'geo_point'],
                ],
            ],
        ];
    }

    public static function createIndex()
    {
        $db = static::getDb();
        $command = $db->createCommand();
        $command->createIndex(static::index(), [
            'mappings' => static::mapping(),
        ]);
    }

    public static function updateMapping()
    {
        $db = static::getDb();
        $command = $db->createCommand();
        $command->setMapping(static::index(), static::type(), static::mapping());
    }

    public static function deleteIndex()
    {
        $db = static::getDb();
        $command = $db->createCommand();
        $command->deleteIndex(static::index());
    }

}