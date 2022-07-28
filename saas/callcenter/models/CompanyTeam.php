<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/12/26
 * Time: 21:25
 */


namespace callcenter\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use Yii;

/**
 * Class CompanyTeam
 * @package callcenter\models
 * @property int $id
 * @property int $outside
 * @property int $team
 * @property int $alias
 * @property int $created_at
 * @property int $updated_at
 */
class CompanyTeam extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%company_team}}';
    }

    /**
     * @return object|\yii\db\Connection|null
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb()
    {
        return Yii::$app->get('db_assist');
    }

    public static function getDb_rd()
    {
        return Yii::$app->get('db_assist_read');
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }


    public static function getTeamsByOutside($outside = 1){
        $all = self::find()->where(['outside' => $outside])->all();
        $arr = [];
        foreach ($all as $item){
            $arr[$item->team] = $item->alias;
        }
        $res = [];
        foreach (AdminUser::$group_games as $key => $group_game){
            $res[$key] = isset($arr[$key]) ? ($group_game.':'.$arr[$key]) : $group_game;
        }
        return $res;
    }
}