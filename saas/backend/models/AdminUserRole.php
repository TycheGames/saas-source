<?php
namespace backend\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * AdminUserRole model
 * @property integer $id
 * @property integer $merchant_id
 * @property string $permissions
 * @property int $groups
 * @property string $name
 * @property string $created_user
 * @property integer $open_status
 * @property integer $created_at
 * @property integer $updated_at
 */
class AdminUserRole extends \yii\db\ActiveRecord
{
     // 角色分组类型
    const TYPE_DEFAULT = 0;//超级管理员
    const TYPE_SERVICE = 1;//客服
    const TYPE_OPERATE = 2;//运营
    const TYPE_PRODUCT = 3;//产品
    const TYPE_FINANCE = 4;//财务
    const TYPE_DEVELOP = 5;//开发
    const TYPE_TEST = 6;//测试
    const TYPE_OPERATION = 7;//职能
    const TYPE_PROPERTY = 8;//风控
    const TYPE_COLLECTION = 9;//催收
    const TYPE_CREDIT_AUDIT = 10;//信审

    public static $status = [
        self::TYPE_DEFAULT      => 'Super administrator',
        self::TYPE_SERVICE      => 'Customer Service Groups',
        self::TYPE_OPERATE      => 'Operations Groups',
        self::TYPE_PRODUCT      => 'Product Group',
        self::TYPE_FINANCE      => 'Finance Groups',
        self::TYPE_DEVELOP      => 'Development Groups',
        self::TYPE_TEST         => 'Test Groups',
        self::TYPE_PROPERTY     => 'Risk Control Groups',
        self::TYPE_COLLECTION   => 'Collection Groups',
        self::TYPE_CREDIT_AUDIT => 'Credit Groups',

    ];

    /**
     *根据角色分组返回角色信息
     *结果数组以角色标识为下标
     */
    public static function groups($groupId){
        $res = self::find()->where(['groups'=>$groupId])->all(Yii::$app->get('db'));
        $result = array();
        if(!empty($res)){
            foreach ($res as $key => $item) {
                $result[$item['name']] = $item;
            }
        }
        return $result;
    }

     public static function groups_array($groupId){
        $res = self::find()->asArray()->where(['groups'=>$groupId])->all(Yii::$app->get('db'));
        $result = array();
        if(!empty($res)){
            foreach ($res as $key => $item) {
                $result[$item['name']] = $item['title'];
            }
        }
        return $result;
    }

    /**
     *根据角色标识，返回角色所属分组
     */
    public static function groups_of_roles($roles = ''){
        if(is_string($roles)) {
            $roles = explode(',', $roles);
        }
        //$res = self::find()->select('groups')->where('`name` IN ("'.implode('","', $roles).'")')->all(Yii::$app->get('db'));
        $res = self::find()->select('groups')->where(['in', 'name', $roles])->all(Yii::$app->get('db'));
        $result = array();
        if(!empty($res)){
            foreach ($res as $key => $item) {
                $result[] = $item['groups'];
            }
        }
        return array_unique($result);
    }


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%admin_user_role}}';
    }

    public static function getDb()
    {
        return Yii::$app->get('db');
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
    	return [
    		[['name', 'title'], 'required'],
    		['name', 'match', 'pattern' => '/^[0-9A-Za-z_]{1,30}$/i', 'message' => 'Identity can only be 1-30-bit letters, numbers or underscores'],
//    		['name', 'unique'],
    		[['desc', 'permissions' ,'groups', 'merchant_id'], 'safe'],
    	];
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
    	return [
            'name' => Yii::T('common', '标识'),
            'title' => Yii::T('common', 'title'),
            'desc' => Yii::T('common', 'Role description'),
            'permissions' => Yii::T('common', 'Authority'),
            'groups' => Yii::T('common', 'groups'),
    	];
    }
    
    public static function findAllSelected()
    {
    	$roles = self::find()->where(['open_status'=>1])->asArray()->all(Yii::$app->get('db'));
    	$rolesItems = array();
    	foreach ($roles as $v) {
            $rolesItems[$v['groups']][$v['name']]['title'] = $v['title'];
    		$rolesItems[$v['groups']][$v['name']]['desc'] = $v['desc'];
    	}
    	return $rolesItems;
    }
}