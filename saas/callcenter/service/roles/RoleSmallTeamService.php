<?php

namespace callcenter\service\roles;


use callcenter\models\AdminNxUser;
use callcenter\models\AdminUser;
use callcenter\models\AdminUserRole;
use callcenter\models\CompanyTeam;
use callcenter\models\loan_collection\UserCompany;
use callcenter\models\AdminManagerRelation;

class RoleSmallTeamService extends RoleBaseService
{
    public function getMyView(){
        $teamAliasName = AdminUser::$group_games[$this->adminUser->group_game];
        if($teamAlias = $this->getTeamAlias()){
            $teamAliasName .= ' '. $teamAlias;
        }
        $result = ['teamName' => $this->adminUser->username . ' ('.$teamAliasName.')','data' => [
            'username' => $this->adminUser->username,
            'name' => $this->adminUser->real_name,
            'phone' => $this->adminUser->phone,
            'type' => 'team leader',
        ]];
        $deputyUsers = $this->getDeputyUsersByUid($this->adminUser->id);
        if($deputyUsers){
            $result['data']['deputyName'] = $deputyUsers[$this->adminUser->id];
        }
        $res = $this->getMyCollectorTeam();
        foreach ($res as $key => $record) {
            $record['type'] = 'member';
            unset($record['id']);
            $result['list'][] = ['teamName' => $record['username'],'data' => $record];
        }

        $result = [$result];

        //我的leader数据
        $bigTeamList = $this->getLeaderBigTeam();
        $superTeamList = $this->getLeaderSuperTeam();

        //大组长
        if($bigTeamList){
            $bigTeamArr = [];
            $uid = [];
            $deputyUsers = [];
            foreach ($bigTeamList as $bigTeamData){
                $uid[] = $bigTeamData['id'];
            }
            if($uid){
                $deputyUsers = $this->getDeputyUsersByUid($uid);
            }
            foreach ($bigTeamList as $bigTeamData){
                $bigTeamData['type'] = 'big team leader';
                if(isset($deputyUsers[$bigTeamData['id']])){
                    $bigTeamData['deputyName'] = $deputyUsers[$bigTeamData['id']];
                }
                unset($bigTeamData['id']);
                $bigTeamArr[] = ['teamName' => $bigTeamData['username'],'data' => $bigTeamData,'list' => $result];
            }
            $result = $bigTeamArr;
        }

        //公司
        $userCompanyList = $this->getOutsideList();
        $userCompanyArr = [];
        foreach ($userCompanyList as $userCompanyName){
            $userCompanyArr[] = ['teamName' => $userCompanyName,'list' => $result];
        }
        $result = $userCompanyArr;

        //超级组长
        if($superTeamList){
            $superTeamArr = [];
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
                $superTeamArr[] = ['teamName' => $superTeamData['username'],'data' => $superTeamData,'list' => $result];
            }
            $result = $superTeamArr;
        }

        return $result;
    }

    /** 获取我的机构 */
    public function getOutsideList(){
        $userCompany = UserCompany::id($this->adminUser->outside);
        $userCompanyArr = [$userCompany->id => $userCompany->real_title];
        return $userCompanyArr;
    }

    //催收员
    protected function getMyCollectorTeam(){
        return AdminUser::find()
            ->alias('u')
            ->select([
                'u.id',
                'u.username',
                'u.real_name as name',
                'u.phone',
                'n.nx_name as NXUsername',
                'n.password as NXPassword'
            ])
            ->leftJoin(['r' => AdminUserRole::tableName()], 'u.role = r.name')
            ->leftJoin(['n' => AdminNxUser::tableName()], 'n.collector_id = u.id and n.type = 1 and n.status = 1')
            ->where([
                'u.group' => $this->adminUser->group,
                'u.group_game' => $this->adminUser->group_game,
                'u.outside'     => $this->adminUser->outside,
            ])
            ->andWhere([
                'u.merchant_id' => $this->adminUser->merchant_id,
                'u.open_status' => AdminUser::$usable_status,
                'r.groups'      => [
                    AdminUserRole::TYPE_COLLECTION,
                ]
            ])
            ->asArray()
            ->all();
    }

    public function getTeamCollectorList(){
        $collectors = $this->getMyCollectorTeam();
        return array_column($collectors,'username','id');
    }

    public function checkCollectorOnTeam($userId){
        $role = AdminUserRole::getRolesByGroup(AdminUserRole::TYPE_COLLECTION);
        return AdminUser::find()->select(['id','username','outside','group','group_game'])
            ->where([
                'open_status' => AdminUser::$usable_status,
                'role' => $role,
                'id' => $userId,
                'outside' => $this->adminUser->outside,
                'group' => $this->adminUser->group,
                'group_game' => $this->adminUser->group_game,
            ])
            ->one();
    }

    //我的大组长上级
    public function getLeaderBigTeam(){
        $res = AdminUser::find()
            ->alias('u')
            ->select([
                'u.id',
                'u.username',
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
        return $res;
    }

    //我的超级组长上级
    public function getLeaderSuperTeam(){
        $res = AdminUser::find()
            ->alias('u')
            ->select([
                'u.id',
                'u.username',
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
        return $res;
    }

    public function getTeamAlias(){
        $alias = '';
        $teamAlias = CompanyTeam::find()->select('alias')->where(['outside' => $this->adminUser->outside,'team' => $this->adminUser->group_game])->one();
        if($teamAlias){
            $alias = $teamAlias->alias;
        }
        return $alias;
    }
}