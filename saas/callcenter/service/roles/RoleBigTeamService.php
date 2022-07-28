<?php

namespace callcenter\service\roles;


use callcenter\models\AdminNxUser;
use callcenter\models\AdminUser;
use callcenter\models\AdminUserRole;
use callcenter\models\CompanyTeam;
use callcenter\models\loan_collection\UserCompany;
use callcenter\models\AdminManagerRelation;
use callcenter\models\loan_collection\LoanCollectionOrder;

class RoleBigTeamService extends RoleBaseService
{
    public $adminManagerRelationArr = [];

    public function __construct(AdminUser $adminUser)
    {
        parent::__construct($adminUser);
        $this->setAdminManagerRelationArr();
    }

    public function getMyView(){
        $result = ['teamName' => $this->adminUser->username ,'data' => [
            'username' => $this->adminUser->username,
            'name' => $this->adminUser->real_name,
            'phone' => $this->adminUser->phone,
            'type' => 'big team leader',
        ]];
        $deputyUsers = $this->getDeputyUsersByUid($this->adminUser->id);
        if($deputyUsers){
            $result['data']['deputyName'] = $deputyUsers[$this->adminUser->id];
        }
        $teamResult = [];

        //smallTeam
        $uid = [];
        $deputyUsers = [];
        $smallTeamRes = $this->getMySmallTeam();
        foreach ($smallTeamRes as $smallTeamData){
            $uid[] = $smallTeamData['id'];
        }
        if($uid){
            $deputyUsers = $this->getDeputyUsersByUid($uid);
        }

        $res = array_merge($smallTeamRes,$this->getMyCollectorTeam());
        $teamAliasArr = $this->getTeamAliasArr();

        foreach ($res as $key => $record) {
            $group = $record['group'];
            $group_game = $record['group_game'];
            $roleGroup = $record['groups'];
            unset($record['group']);
            unset($record['group_game']);
            unset($record['groups']);

            if ($roleGroup == AdminUserRole::TYPE_COLLECTION) {
                $record['type'] = 'member';
                unset($record['id']);
                $teamResult[$group][$group_game]['memberList'][] = ['teamName' => $record['username'],'data' => $record];
            }
            if ($roleGroup == AdminUserRole::TYPE_SMALL_TEAM_MANAGER) {
                $record['type'] = 'leader';
                $teamAliasName = AdminUser::$group_games[$group_game];
                if(isset($teamAliasArr[$group_game])){
                    $teamAliasName .= ' '. $teamAliasArr[$group_game];
                }
                if(isset($deputyUsers[$record['id']])){
                    $record['deputyName'] = $deputyUsers[$record['id']];
                }
                unset($record['id']);
                $teamResult[$group][$group_game]['smallTeamList'][] = ['teamName' => $record['username']  . '('.LoanCollectionOrder::$current_level[$group].' '.$teamAliasName.')','data' => $record];
            }
        }

        foreach ($teamResult as $group => $groupData){
            foreach ($groupData as $groupGame => $groupGameData){
                if(isset($groupGameData['smallTeamList'])){
                    foreach ($groupGameData['smallTeamList'] as $smallTeamData){
                        if(isset($groupGameData['memberList'])){
                            $smallTeamData['list'] = $groupGameData['memberList'];
                        }
                        $result['list'][] = $smallTeamData;
                    }
                }else{
                    if(isset($groupGameData['memberList'])){
                        $result['list'][] = $groupGameData['memberList'];
                    }
                }
            }
        }

        $result = [$result];

        //上级
        //机构
        $userCompanyList = $this->getOutsideList();
        $userCompanyArr = [];
        foreach ($userCompanyList as $userCompanyName){
            $userCompanyArr[] = ['teamName' => $userCompanyName, 'list' => $result];
        }
        $result = $userCompanyArr;

        //超级组长
        $superTeamList = $this->getLeaderSuperTeam();
        if($superTeamList){
            $superTeamArr = [];
            //superTeam
            $uid = [];
            $deputyUsers = [];
            foreach ($superTeamList as $superTeamData){
                $uid[] = $superTeamData['id'];
            }
            if($uid){
                $deputyUsers = $this->getDeputyUsersByUid($uid);
            }
            foreach ($superTeamList as $superTeamData){
                $superTeamData['type'] = 'super team leader';
                if(isset($deputyUsers[$superTeamData['id']])){
                    $superTeamData['deputyName'] = $deputyUsers[$superTeamData['id']];
                }
                unset($superTeamData['id']);
                $superTeamArr[] = ['teamName' => $superTeamData['username'],'data' => $superTeamData, 'list' => $result];
            }
            $result = $superTeamArr;
        }
        return $result;
    }

    /** 设置自身可管理组的信息 */
    private function setAdminManagerRelationArr(){
        $uid = $this->adminUser->master_user_id ? $this->adminUser->master_user_id : $this->adminUser->id;
        $this->adminManagerRelationArr = AdminManagerRelation::find()->select(['group','group_game'])->where(['admin_id' => $uid])->asArray()->all();
    }


    /** 获取我的机构 */
    public function getOutsideList(){
        $userCompany = UserCompany::id($this->adminUser->outside);
        $userCompanyArr = [$userCompany->id => $userCompany->real_title];
        return $userCompanyArr;
    }

    /** 获取我的机构组里对应别名关系 */
    public function getTeamAliasArr(){
        $teamIds = [];
        foreach ($this->adminManagerRelationArr as $item){
            $teamIds[] = $item['group_game'];
        }
        /** @var CompanyTeam $teamAlias */
        $teamAliasArr = CompanyTeam::find()->select(['alias','team'])
            ->where(['outside' => $this->adminUser->outside])
            ->andWhere(['team' => $teamIds])
            ->indexBy('team')
            ->asArray()
            ->all();
        return $teamAliasArr;
    }

    protected function getLeaderSuperTeam(){
        $orWhere = ['OR'];
        foreach ($this->adminManagerRelationArr as $item){
            $orWhere[] = ['mr.group' => $item['group'],'mr.group_game' => $item['group_game']];
        }
        //我的超级组长上级
        $res = AdminUser::find()
            ->alias('u')
            ->select([
                'u.id',
                'u.username as username',
                'u.real_name as name',
                'u.phone',
                'n.nx_name as NXUsername',
                'n.password as NXPassword'
            ])
            ->leftJoin(['mr' => AdminManagerRelation::tableName()], 'u.id = mr.admin_id')
            ->leftJoin(['r' => AdminUserRole::tableName()], 'u.role = r.name')
            ->leftJoin(['n' => AdminNxUser::tableName()], 'n.collector_id = u.id and n.type = 1 and n.status = 1')
            ->where([
                'mr.outside' => $this->adminUser->outside,
                'u.merchant_id' => $this->adminUser->merchant_id,
                'u.open_status' => AdminUser::$usable_status,
                'r.groups'      => AdminUserRole::TYPE_SUPER_TEAM,
            ])
            ->andWhere($orWhere)
            ->asArray()
            ->all();
        return $res;
    }

    //小组长
    protected function getMySmallTeam(){
        $orWhere = ['OR'];
        foreach ($this->adminManagerRelationArr as $item){
            $orWhere[] = ['u.group' => $item['group'],'u.group_game' => $item['group_game']];
        }
        return AdminUser::find()
            ->alias('u')
            ->select([
                'u.id',
                'r.groups',
                'u.username ',
                'u.real_name as name',
                'u.phone',
                'u.group',
                'u.group_game',
                'n.nx_name as NXUsername',
                'n.password as NXPassword'
            ])
            ->leftJoin(['r' => AdminUserRole::tableName()], 'u.role = r.name')
            ->leftJoin(['n' => AdminNxUser::tableName()], 'n.collector_id = u.id and n.type = 1 and n.status = 1')
            ->where([
                'u.outside' => $this->adminUser->outside,
                'u.merchant_id' => $this->adminUser->merchant_id,
                'u.open_status' => AdminUser::$usable_status,
                'r.groups'      => [
                    AdminUserRole::TYPE_SMALL_TEAM_MANAGER,
                ]
            ])
            ->andWhere($orWhere)
            ->asArray()
            ->all();
    }

    //催收员
    protected function getMyCollectorTeam(){
        $orWhere = ['OR'];
        foreach ($this->adminManagerRelationArr as $item){
            $orWhere[] = ['u.group' => $item['group'],'u.group_game' => $item['group_game']];
        }
        return AdminUser::find()
            ->alias('u')
            ->select([
                'u.id',
                'r.groups',
                'u.username',
                'u.real_name as name',
                'u.phone',
                'u.group',
                'u.group_game',
                'n.nx_name as NXUsername',
                'n.password as NXPassword'
            ])
            ->leftJoin(['r' => AdminUserRole::tableName()], 'u.role = r.name')
            ->leftJoin(['n' => AdminNxUser::tableName()], 'n.collector_id = u.id and n.type = 1 and n.status = 1')
            ->where([
                'u.outside' => $this->adminUser->outside,
                'u.merchant_id' => $this->adminUser->merchant_id,
                'u.open_status' => AdminUser::$usable_status,
                'r.groups'      => [
                    AdminUserRole::TYPE_COLLECTION,
                ]
            ])
            ->andWhere($orWhere)
            ->asArray()
            ->all();
    }


    public function getTeamCollectorList(){
        $collectors = $this->getMyCollectorTeam();
        return array_column($collectors,'username','id');
    }

    public function checkCollectorOnTeam($userId){
        $andWhere = ['OR'];
        foreach ($this->adminManagerRelationArr as $item){
            $andWhere[] =  ['group' => $item['group'],'group_game' => $item['group_game']];;
        }
        $role = AdminUserRole::getRolesByGroup(AdminUserRole::TYPE_COLLECTION);
        return AdminUser::find()->select(['id','username','outside','group','group_game'])
            ->where(['open_status' => AdminUser::$usable_status, 'role' => $role, 'id' => $userId])
            ->andWhere($andWhere)
            ->one();
    }
}