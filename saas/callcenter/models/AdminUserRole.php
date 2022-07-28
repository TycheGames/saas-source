<?php
namespace callcenter\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * AdminUserRole model
 * @property string $name
 * @property string $title
 * @property string $desc
 * @property string $permissions
 * @property string $created_user
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $groups
 * @property integer $open_status
 */
class AdminUserRole extends ActiveRecord
{
     // 角色分组类型
    const TYPE_COLLECTION = 1;//催收组
    const TYPE_SMALL_TEAM_MANAGER = 2;//催收小组管理员组
    const TYPE_BIG_TEAM_MANAGER = 3;///催收大组管理员组
    const TYPE_COMPANY_MANAGER = 4;//催收机构管理员组
    const TYPE_SUPER_MANAGER = 5;//催收超级管理员组
    const TYPE_SUPER_TEAM = 6;//催收超级组管理员组

    public static $groups_map = [
        self::TYPE_COLLECTION => '催收组',
        self::TYPE_SMALL_TEAM_MANAGER => '小组管理组',
        self::TYPE_BIG_TEAM_MANAGER => '大组管理组(经理)',
        self::TYPE_COMPANY_MANAGER => '机构管理组',
        self::TYPE_SUPER_TEAM    => '超级组长组',
        self::TYPE_SUPER_MANAGER => '超级管理组',
    ];

    public static $groups_default_role_map = [
        self::TYPE_COLLECTION => 'collection',
        self::TYPE_SMALL_TEAM_MANAGER => 'collection_team',
        self::TYPE_BIG_TEAM_MANAGER => 'collection_big_team',
        self::TYPE_COMPANY_MANAGER => 'collection_monitor',
        self::TYPE_SUPER_TEAM => 'collection_super_team',
        self::TYPE_SUPER_MANAGER => 'collection_manager',
    ];

    public static $groups_level_list = [
        self::TYPE_COLLECTION => ['outside','group','group_game'],
        self::TYPE_SMALL_TEAM_MANAGER => ['outside','group','group_game'],
        self::TYPE_BIG_TEAM_MANAGER => ['outside'],
        self::TYPE_COMPANY_MANAGER => ['outside'],
        self::TYPE_SUPER_TEAM => [],
        self::TYPE_SUPER_MANAGER => [],
    ];

    public static $team_leader_groups = [
        self::TYPE_SMALL_TEAM_MANAGER,
        self::TYPE_BIG_TEAM_MANAGER,
        self::TYPE_SUPER_TEAM
    ];

    const OPEN_STATUS_ON = 1;   //启用
    const OPEN_STATUS_OFF = 0; //已删除


    /**
     *根据角色标识，返回角色所属分组
     */
    public static function groups_of_roles($roles = ''){
        if(is_string($roles)) {
            $roles = explode(',', $roles);
        }
        $res = self::find()
            ->select('groups')
            ->where(['name' => implode('","', $roles)])
            ->all();
        $result = array();
        if(!empty($res)){
            foreach ($res as $key => $item) {
                $result[] = $item['groups'];
            }
        }
        return array_unique($result);
    }

    public static function getGroupByRoles($role){
        $res = self::find()->select('groups')->where(['name' => $role])->one();
        if($res){
            return $res['groups'];
        }
        return false;
    }

    /**
     * @param AdminUser $adminUser
     * @param $tb
     * @return array
     */
    public static function getCondition($adminUser,$tb){
        $condition = [];
        $group = AdminUserRole::getGroupByRoles($adminUser->role);
        switch ($group){
            case self::TYPE_BIG_TEAM_MANAGER:
                $conditionArr = [];
                $arr = AdminManagerRelation::find()
                    ->select(['group','group_game'])
                    ->where(['admin_id' => $adminUser->id])
                    ->asArray()
                    ->all();
                foreach ($arr as $val){
                    $conditionArr[] = ['and', ["{$tb}.group" => $val['group']], ["{$tb}.group_game" => $val['group_game']]];
                }
                if($conditionArr){
                    $condition[] = array_merge(['or'], $conditionArr);
                }

                $condition[] = ["{$tb}.outside" => $adminUser->outside];
                break;
            case self::TYPE_SUPER_TEAM:
                $conditionArr = ['or'];
                $arr = AdminManagerRelation::find()->select(['outside','group','group_game'])
                    ->where(['admin_id' => $adminUser->id])
                    ->asArray()->all();
                foreach ($arr as $val){
                    $conditionArr[] = ["{$tb}.outside" => $val['outside'], "{$tb}.group" => $val['group'], "{$tb}.group_game" => $val['group_game']];
                }
                $condition[] = $conditionArr;
                break;
            case self::TYPE_COMPANY_MANAGER:
                $condition[] = ["{$tb}.outside" => $adminUser->outside];
                break;
            case self::TYPE_SMALL_TEAM_MANAGER:
                $condition[] = ["{$tb}.outside" => $adminUser->outside];
                $condition[] = ["{$tb}.group" => $adminUser->group];
                $condition[] = ["{$tb}.group_game" => $adminUser->group_game];
                break;
        }

        return $condition;
    }


    public static function getConditionNew($adminUser, $tb){
        $condition = [];
        $group = AdminUserRole::getGroupByRoles($adminUser->role);
        switch ($group){
            case self::TYPE_BIG_TEAM_MANAGER:
                $arr = AdminManagerRelation::find()->select(['group','group_game'])->where(['admin_id' => $adminUser->id])->asArray()->all();
                $conditionArr = ['or'];
                foreach ($arr as $val){
                    $conditionArr[] = ["{$tb}.group" => $val['group'], "{$tb}.group_game" => $val['group_game']];
                }
                $condition[] = $conditionArr;
                $condition[] = ["{$tb}.outside" => $adminUser->outside];
                break;
            case self::TYPE_SUPER_TEAM:
                $conditionArr = ['or'];
                $arr = AdminManagerRelation::find()->select(['outside','group','group_game'])
                    ->where(['admin_id' => $adminUser->id])
                    ->asArray()->all();
                foreach ($arr as $val){
                    $conditionArr[] = ["{$tb}.outside" => $val['outside'], "{$tb}.group" => $val['group'], "{$tb}.group_game" => $val['group_game']];
                }
                $condition[] = $conditionArr;
                break;
            case self::TYPE_COMPANY_MANAGER:
                $condition[] = ["{$tb}.outside" => $adminUser->outside];
                break;
            case self::TYPE_SMALL_TEAM_MANAGER:
                $condition[] = [
                    "{$tb}.outside" => $adminUser->outside,
                    "{$tb}.group" => $adminUser->group,
                    "{$tb}.group_game" => $adminUser->group_game
                ];
                break;
        }

        return $condition;
    }

    public static function getRolesByGroup($roleGroup){
        if(!is_array($roleGroup)){
            $roleGroup = [$roleGroup];
        }
        foreach ($roleGroup as $item){
            if(!isset(self::$groups_map[$item])){
                return [];
            }
        }
        return array_column(
            self::find()
            ->select(['name'])
            ->where(['groups' => $roleGroup,'open_status' => AdminUserRole::OPEN_STATUS_ON])
            ->asArray()
            ->all(),
            'name');
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
        return Yii::$app->get('db_assist');
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
    	return [
    		[['name', 'title'], 'required'],
    		['name', 'match', 'pattern' => '/^[0-9A-Za-z_]{1,30}$/i', 'message' => '标识只能是1-30位字母、数字或下划线'],
    		['name', 'unique'],
    		[['desc', 'permissions' ,'groups'], 'safe'],
    	];
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
    	return [
    		'name' => '标识',
    		'title' => '名称',
    		'desc' => '角色描述',
    		'permissions' => '权限',
            'groups' => '分组',
    	];
    }
    
    public static function findAllSelected()
    {
        $roles = self::find()->asArray()->all();
    	$rolesItems = array();
    	foreach ($roles as $v) {
            $rolesItems[$v['groups']][$v['name']]['title'] = $v['title'];
    		$rolesItems[$v['groups']][$v['name']]['desc'] = $v['desc'];
    	}
    	return $rolesItems;
    }

    public static function findManagerSelected()
    {
        $roles = self::find()->asArray()->all();
        $rolesItems = array();
        foreach ($roles as $v) {
            $rolesItems[$v['groups']][$v['name']]['title'] = $v['title'];
            $rolesItems[$v['groups']][$v['name']]['desc'] = $v['desc'];
        }
        return $rolesItems;
    }


    public static function getCheck()
    {
        $permissions = Yii::$app->getSession()->get('permissions');
        $role = Yii::$app->user->identity->role;
        if($role){
            $roleModel = self::find()->andWhere("name in('".implode("','",explode(',',$role))."')")->all();
            if($roleModel){
                $arr = array();
                foreach ($roleModel as $val) {
                    if($val->permissions)
                        $arr = array_unique(array_merge($arr,json_decode($val->permissions)));
                }
                Yii::$app->getSession()->set('permissions', json_encode($arr));
                $permissions = json_decode($permissions, true)?json_decode($permissions, true):array();
                if (!in_array('user-company/company-real-title', $permissions)) {
                    return false;
                }
                else
                {
                    return true;
                }
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
}