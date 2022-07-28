<?php
namespace common\models\risk;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * 风控规则数据表
 * This is the model class for table "{{%risk_rules}}".
 * Class RiskRules
 * @package common\models
 * @property integer    $id 自增ID
 * @property string     $version 决策树版本
 * @property integer     $is_default 是否默认 0-否 1-是
 * @property integer     $is_gray 是否灰度 0-否 1-是
 * @property integer     $version_base_by 以哪个版本为基础修改
 * @property string     $remark 备注
 * @property integer     $created_at 创建时间
 * @property integer     $updated_at 修改时间
 * @property integer     $created_user_id 创建人ID
 */

class RuleVersion extends ActiveRecord {

    const DEFAULT_VERSION = '1.0';

    const GLOBAL_RULE_VERSION_LIST = 'global.rule.version.list'; // rule version list cache key

    const IS_DEFAULT = 1;
    const NO_DEFAULT = 0;

    public static $default_map = [
        self::IS_DEFAULT => 'yes',
        self::NO_DEFAULT => 'no'
    ];

    /**
     * @inheritdoc
     * @return string
     */
    public static function tableName(){
        return '{{%rule_version}}';
    }

    /**
     * @return null|object|\yii\db\Connection
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb(){
        return Yii::$app->get('db');
    }

    public function rules(){
        return [
            [['is_default', 'is_gray'], 'integer'],
            [['version', 'version_base_by', 'remark'], 'string'],
            [['remark'], 'safe']
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

    public static function existsVersion($version) {
        return !empty(self::findOne(['version' => $version]));
    }

    public static function getVersionList() {
        if ($version_list = YII::$app->cache->get(self::GLOBAL_RULE_VERSION_LIST)) {
            return json_decode($version_list, true);
        }

        $info = self::find()->asArray()->all();

        if (!empty($info)) {
            foreach ($info as $item) {
                $version_list[$item['version']] = $item['version'];
            }
        } else {
            $version_list = [self::DEFAULT_VERSION => self::DEFAULT_VERSION];
        }

        Yii::$app->cache->set(self::GLOBAL_RULE_VERSION_LIST, json_encode($version_list), 300);

        return $version_list;
    }

    public static function clearVersionListCache() {
        return Yii::$app->cache->delete(self::GLOBAL_RULE_VERSION_LIST);
    }

    public static function getDefaultVersion() {
        $info = self::findOne(['is_default' => 1]);
        return $info['version'] ?? self::DEFAULT_VERSION;
    }

    public static function getGrayVersion() {
        $info = self::findOne(['is_default' => 0, 'is_gray' => 1]);
        return $info['version'] ?? '';
    }

}