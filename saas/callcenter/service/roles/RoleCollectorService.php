<?php

namespace callcenter\service\roles;


use callcenter\models\AdminNxUser;
use callcenter\models\AdminUser;
use callcenter\models\AdminUserRole;
use callcenter\models\CompanyTeam;
use callcenter\models\loan_collection\UserCompany;
use callcenter\models\AdminManagerRelation;

class RoleCollectorService extends RoleBaseService
{
    public function getMyView(){
        //上级
        $smallTeamList = $this->getSmallTeamLeaderData();
        $bigTeamList = $this->getBigTeamLeaderData();
        $superTeamList = $this->getSuperTeamLeaderData();

        //组别名
        $teamAlias = $this->getTeamAliasData();
        //公司名
        /** @var UserCompany $userCompany */
        $userCompany = UserCompany::id($this->adminUser->outside);
        $userCompanyArr = [['teamName' => $userCompany->real_title]];

        $myTeamList = $this->getMyTeamData();
        $result = $this->setLevelData($myTeamList,$superTeamList,$bigTeamList,$smallTeamList,$userCompanyArr,$teamAlias);
        return $result;
    }

    public function getSmallTeamLeaderData(){
        $list = [];
        //我的小组长上级
        $res = AdminUser::find()
            ->alias('u')
            ->select([
                'u.id',
                'u.username',
                'u.real_name as name',
                'u.phone',
            ])
            ->leftJoin(['r' => AdminUserRole::tableName()], 'u.role = r.name')
            ->where([
                'u.merchant_id' => $this->adminUser->merchant_id,
                'u.outside' => $this->adminUser->outside,
                'u.group' => $this->adminUser->group,
                'u.group_game' => $this->adminUser->group_game,
                'u.open_status' => AdminUser::$usable_status,
                'r.groups'      => AdminUserRole::TYPE_SMALL_TEAM_MANAGER,
            ])
            ->asArray()
            ->all();

        $uid = [];
        $deputyUsers = [];
        foreach ($res as $key => $record) {
            $uid[] = $record['id'];
        }
        if($uid){
            $deputyUsers = $this->getDeputyUsersByUid($uid);
        }

        foreach ($res as $key => $record) {
            $record['type'] = 'team leader';
            if(isset($deputyUsers[$record['id']])){
                $record['deputyName'] = $deputyUsers[$record['id']];
            }
            unset($record['id']);
            $list[] = ['teamName' => $record['username'],'data' => $record];
        }
        return $list;
    }

    public function getBigTeamLeaderData(){
        $list = [];
        //我的大组长上级
        $res = AdminUser::find()
            ->alias('u')
            ->select([
                'u.id',
                'u.username as username',
                'u.real_name as name',
                'u.phone',
            ])
            ->leftJoin(['mr' => AdminManagerRelation::tableName()], 'u.id = mr.admin_id')
            ->leftJoin(['r' => AdminUserRole::tableName()], 'u.role = r.name')
            ->where([
                'u.merchant_id' => $this->adminUser->merchant_id,
                'u.outside' => $this->adminUser->outside,
                'u.open_status' => AdminUser::$usable_status,
                'r.groups'      => AdminUserRole::TYPE_BIG_TEAM_MANAGER,
            ])
            ->andWhere(['mr.group' => $this->adminUser->group,'mr.group_game' => $this->adminUser->group_game])
            ->asArray()
            ->all();

        $uid = [];
        $deputyUsers = [];
        foreach ($res as $key => $record) {
            $uid[] = $record['id'];
        }
        if($uid){
            $deputyUsers = $this->getDeputyUsersByUid($uid);
        }

        foreach ($res as $key => $record) {
            $record['type'] = 'big team leader';
            if(isset($deputyUsers[$record['id']])){
                $record['deputyName'] = $deputyUsers[$record['id']];
            }
            unset($record['id']);
            $list[] = ['teamName' => $record['username'],'data' => $record];
        }
        return $list;
    }

    public function getSuperTeamLeaderData(){
        $list = [];
        //我的超级组长上级
        $res = AdminUser::find()
            ->alias('u')
            ->select([
                'u.id',
                'u.username as username',
                'u.real_name as name',
                'u.phone',
            ])
            ->leftJoin(['mr' => AdminManagerRelation::tableName()], 'u.id = mr.admin_id')
            ->leftJoin(['r' => AdminUserRole::tableName()], 'u.role = r.name')
            ->where([
                'u.merchant_id' => $this->adminUser->merchant_id,
                'u.open_status' => AdminUser::$usable_status,
                'r.groups'      => AdminUserRole::TYPE_SUPER_TEAM,
            ])
            ->andWhere(['mr.outside' => $this->adminUser->outside,'mr.group' => $this->adminUser->group,'mr.group_game' => $this->adminUser->group_game])
            ->asArray()
            ->all();

        $uid = [];
        $deputyUsers = [];
        foreach ($res as $key => $record) {
            $uid[] = $record['id'];
        }
        if($uid){
            $deputyUsers = $this->getDeputyUsersByUid($uid);
        }

        foreach ($res as $key => $record) {
            $record['type'] = 'super team leader';
            if(isset($deputyUsers[$record['id']])){
                $record['deputyName'] = $deputyUsers[$record['id']];
            }
            unset($record['id']);
            $list[] = ['teamName' => $record['username'],'data' => $record];
        }
        return $list;
    }

    public function getMyTeamData(){
        /** @var AdminNxUser $adminNxUser */
        $adminNxUser = AdminNxUser::find()->where(['collector_id' => $this->adminUser->id,'type' => 1,'status' => 1])->one();

        $record = [
            'username' => $this->adminUser->username,
            'name' => $this->adminUser->real_name,
            'phone' => $this->adminUser->phone,
            'NXUsername' => $adminNxUser->nx_name ?? '',
            'NXPassword' => $adminNxUser->password ?? '',
            'type' => 'member'
        ];
        return ['teamName' => $this->adminUser->username,'data' => $record];
    }

    public function getTeamAliasData(){
        $alias = '';
        /** @var CompanyTeam $teamAlias */
        $teamAlias = CompanyTeam::find()->select('alias')->where(['outside' => $this->adminUser->outside,'team' => $this->adminUser->group_game])->one();
        if($teamAlias){
            $alias = $teamAlias->alias;
        }
        return $alias;
    }


    public function setLevelData($result,$superTeamList,$bigTeamList,$smallTeamList,$userCompanyArr,$teamAlias){
        //我的team数据
        $teamAliasName = AdminUser::$group_games[$this->adminUser->group_game];
        if($teamAlias){
            $teamAliasName .= ' '. $teamAlias;
        }

        //我的leader数据
        if($smallTeamList){
            foreach ($smallTeamList as &$smallTeamData){
                $smallTeamData['teamName'] = ($smallTeamData['teamName'] .' '. $teamAliasName);
                $smallTeamData['list'] = [$result];
            }
            $result = $smallTeamList;
        }

        if($bigTeamList){
            foreach ($bigTeamList as &$bigTeamData){
                $bigTeamData['list'] = $smallTeamList ? $result : [$result];
            }

            $result = $bigTeamList;
        }


        foreach ($userCompanyArr as &$userCompanyData){
            $userCompanyData['list'] = $bigTeamList ? $result : [$result];
        }
        $result = $userCompanyArr;


        if($superTeamList){
            foreach ($superTeamList as &$superTeamData){
                $superTeamData['list'] = $result;
            }
            $result = $superTeamList;
        }

        return $result;
    }

}