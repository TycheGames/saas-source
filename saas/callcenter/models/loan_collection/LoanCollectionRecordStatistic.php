<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2017/8/17
 * Time: 16:14
 */
namespace callcenter\models\loan_collection;

use Yii;
class LoanCollectionRecordStatistic extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%loan_collection_record_statistic}}';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_assist');
    }

    public static function getDb_rd()
    {
        return Yii::$app->get('db_assist');
    }

}