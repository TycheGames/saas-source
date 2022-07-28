<?php

namespace callcenter\service\roles;


use callcenter\models\AdminNxUser;
use callcenter\models\AdminUser;
use callcenter\models\AdminUserRole;
use callcenter\models\CompanyTeam;
use callcenter\models\loan_collection\UserCompany;
use callcenter\models\AdminManagerRelation;
use callcenter\models\loan_collection\LoanCollectionOrder;

class RoleSuperTeamService extends RoleBaseService
{
    public $adminManagerRelationArr = [];

    public function __construct(AdminUser $adminUser)
    {
        parent::__construct($adminUser);
        $this->setAdminManagerRelationArr();
    }

    ######我的team下发###########
    public function getMyView(){
        $result = ['teamName' => $this->adminUser->username ,'data' => [
            'username' => $this->adminUser->username,
            'name' => $this->adminUser->real_name,
            'phone' => $this->adminUser->phone,
            'type' => 'super team leader',
        ]];
        $deputyUsers = $this->getDeputyUsersByUid($this->adminUser->id);
        if($deputyUsers){
            $result['data']['deputyName'] = $deputyUsers[$this->adminUser->id];
        }
        $teamResult = [];

        //smallTeam
        $uid = [];
        $deputyUsers = [];
        $res = $this->getMyBigTeam();
        foreach ($res as $record){
            $uid[] = $record['id'];
        }
        if($uid){
            $deputyUsers = $this->getDeputyUsersByUid($uid);
        }
        foreach ($res as $key => $record) {
            $outside = $record['outside'];
            $group = $record['group'];
            $group_game = $record['group_game'];
            $record['type'] = 'big team leader';
            if(isset($deputyUsers[$record['id']])){
                $record['deputyName'] = $deputyUsers[$record['id']];
            }
            unset($record['id']);
            unset($record['outside']);
            unset($record['group']);
            unset($record['group_game']);
            $teamResult[$outside][$group][$group_game]['bigTeamList'][] = ['teamName' => $record['username'],'data' => $record];
        }

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
        $teamAlias = $this->getTeamAliasArr();
        foreach ($res as $key => $record) {
            $outside = $record['outside'];
            $group = $record['group'];
            $group_game = $record['group_game'];
            unset($record['outside']);
            unset($record['group']);
            unset($record['group_game']);
            if(isset($teamResult[$outside][$group][$group_game])){
                if ($record['groups'] == AdminUserRole::TYPE_COLLECTION) {
                    $record['type'] = 'member';
                    unset($record['id']);
                    $teamResult[$outside][$group][$group_game]['memberList'][] = ['teamName' => $record['username'],'data' => $record];
                }
                if ($record['groups'] == AdminUserRole::TYPE_SMALL_TEAM_MANAGER) {
                    $record['type'] = 'team leader';
                    $teamAliasName = AdminUser::$group_games[$group_game];
                    if(isset($teamAlias[$outside][$group_game])){
                        $teamAliasName .= ' '. $teamAlias[$outside][$group_game];
                    }
                    if(isset($deputyUsers[$record['id']])){
                        $record['deputyName'] = $deputyUsers[$record['id']];
                    }
                    unset($record['id']);
                    $teamResult[$outside][$group][$group_game]['smallTeamList'][] = ['teamName' => $record['username']  . '('.LoanCollectionOrder::$current_level[$group].' '.$teamAliasName.')','data' => $record];
                }
            }
        }

        $userCompanyArr = $this->getOutsideArr();
        foreach ($userCompanyArr as $outsideId => $userCompanyName){
            $userCompanyData = ['teamName' => $userCompanyName];
             //机构
            if(isset($teamResult[$outsideId])){
                foreach ($teamResult[$outsideId] as $group => $groupData){
                    //机构下的账龄组
                    foreach ($groupData as $groupGame => $groupGameData){

                        if(isset($groupGameData['bigTeamList'])){
                            foreach ($groupGameData['bigTeamList'] as $bigTeamData){
                                if(isset($groupGameData['smallTeamList'])){
                                    foreach ($groupGameData['smallTeamList'] as $smallTeamData){
                                        if(isset($groupGameData['memberList'])){
                                            $smallTeamData['list'] = $groupGameData['memberList'];
                                            $bigTeamData['list'][] = $smallTeamData;
                                        }
                                    }
                                }else{
                                    if(isset($groupGameData['memberList'])){
                                        $bigTeamData['list'][] = $groupGameData['memberList'];
                                    }
                                }
                                $userCompanyData['list'][] = $bigTeamData;
                            }
                        }else{
                            if(isset($groupGameData['smallTeamList'])){
                                foreach ($groupGameData['smallTeamList'] as $smallTeamData){
                                    if(isset($groupGameData['memberList'])){
                                        $smallTeamData['list'] = $groupGameData['memberList'];
                                    }
                                    $userCompanyData['list'][] = $smallTeamData;
                                }
                            }else{
                                if(isset($groupGameData['memberList'])){
                                    $userCompanyData['list'][] = $groupGameData['memberList'];
                                }
                            }
                        }
                    }
                }
                $result['list'][] = $userCompanyData;
            }
        }
        $result = [$result];
        return $result;
    }


    /** 设置自身可管理组的信息 */
    private function setAdminManagerRelationArr(){
        $uid = $this->adminUser->master_user_id ? $this->adminUser->master_user_id : $this->adminUser->id;
        $this-> adminManagerRelationArr= AdminManagerRelation::find()->select(['outside','group','group_game'])->where(['admin_id' => $uid])->asArray()->all();
    }

    /** 获取我的机构 */
    public function getOutsideArr(){
        $outsideIds = [];
        foreach ($this->adminManagerRelationArr as $item){
            $outsideIds[] = $item['outside'];
        }
        $userCompany = UserCompany::find()->select(['id','real_title'])->where(['id' => $outsideIds])->asArray()->all();
        $userCompanyArr = [];
        foreach ($userCompany as $item){
            $userCompanyArr[$item['id']] = $item['real_title'];
        }
        return $userCompanyArr;
    }

    /** 获取我的机构组里对应别名关系 */
    public function getTeamAliasArr(){
        $teamAliasArr = [];
        $teamWhere = ['OR'];
        foreach ($this->adminManagerRelationArr as $item){
            $teamWhere[] = ['outside' => $item['outside'],'team' => $item['group_game']];
        }
        /** @var CompanyTeam $teamAlias */
        $teamAlias = CompanyTeam::find()->select(['alias','team','outside'])
            ->where($teamWhere)
            ->asArray()
            ->all();

        foreach ($teamAlias as $item){
            $teamAliasArr[$item['outside']][$item['team']] = $item['alias'];
        }
        return $teamAliasArr;
    }

    //大组长
    protected function getMyBigTeam(){
        $bigTeamOrWhere = ['OR'];
        foreach ($this->adminManagerRelationArr as $item){
            $bigTeamOrWhere[] = ['u.outside' => $item['outside'],'mr.group' => $item['group'],'mr.group_game' => $item['group_game']];
        }
        return AdminUser::find()
            ->alias('u')
            ->select([
                'u.id',
                'u.username',
                'u.real_name as name',
                'u.phone',
                'u.outside',
                'mr.group',
                'mr.group_game',
            ])
            ->leftJoin(['mr' => AdminManagerRelation::tableName()], 'u.id = mr.admin_id')
            ->leftJoin(['r' => AdminUserRole::tableName()], 'u.role = r.name')
            ->where([
                'u.merchant_id' => $this->adminUser->merchant_id,
                'u.open_status' => AdminUser::$usable_status,
                'r.groups'      => AdminUserRole::TYPE_BIG_TEAM_MANAGER,
            ])
            ->andWhere($bigTeamOrWhere)
            ->asArray()
            ->all();
    }
    //小组长
    protected function getMySmallTeam(){
        $orWhere = ['OR'];
        foreach ($this->adminManagerRelationArr as $item){
            $orWhere[] = ['u.outside' => $item['outside'],'u.group' => $item['group'],'u.group_game' => $item['group_game']];
        }
        return AdminUser::find()
            ->alias('u')
            ->select([
                'u.id',
                'r.groups',
                'u.username',
                'u.real_name as name',
                'u.phone',
                'u.outside',
                'u.group',
                'u.group_game',
                'n.nx_name as NXUsername',
                'n.password as NXPassword'
            ])
            ->leftJoin(['r' => AdminUserRole::tableName()], 'u.role = r.name')
            ->leftJoin(['n' => AdminNxUser::tableName()], 'n.collector_id = u.id and n.type = 1 and n.status = 1')
            ->where([
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
            $orWhere[] = ['u.outside' => $item['outside'],'u.group' => $item['group'],'u.group_game' => $item['group_game']];
        }
        return AdminUser::find()
            ->alias('u')
            ->select([
                'u.id',
                'r.groups',
                'u.username',
                'u.real_name as name',
                'u.phone',
                'u.outside',
                'u.group',
                'u.group_game',
                'n.nx_name as NXUsername',
                'n.password as NXPassword'
            ])
            ->leftJoin(['r' => AdminUserRole::tableName()], 'u.role = r.name')
            ->leftJoin(['n' => AdminNxUser::tableName()], 'n.collector_id = u.id and n.type = 1 and n.status = 1')
            ->where([
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


    public function getTeamLeaderList($showAll = false){
        if($showAll){
            $result = [];
            $bigTeam = $this->getMyBigTeam();
            $smallTeam = $this->getMySmallTeam();
            $companyArr = $this->getOutsideArr();
            $teamAliasArr = $this->getTeamAliasArr();
            foreach ($bigTeam as $val){
                $result[$val['id']] = $val['username'] . '(big team leader)';
            }
            foreach ($smallTeam as $val){
                $aliasName = '';
                if(isset($teamAliasArr[$val['outside']]) && isset($teamAliasArr[$val['outside']][$val['outside']])){
                    $aliasName = $teamAliasArr[$val['outside']][$val['outside']];
                }
                $result[$val['id']] = $val['username'] .'('.
                    $companyArr[$val['outside']]. ' '.
                    LoanCollectionOrder::$current_level[$val['group']] ?? '' .' team '.$val['group_game'].$aliasName .')';
            }
            return $result;
        }else{
            $bigTeam = $this->getMyBigTeam();
            $smallTeam = $this->getMySmallTeam();
            $teamLeaders = array_merge($bigTeam,$smallTeam);
            return array_column($teamLeaders,'username','id');
        }
    }
}