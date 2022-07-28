<?php

namespace callcenter\service\roles;



use callcenter\models\AdminUser;
use callcenter\models\AdminUserMasterSlaverRelation;

class RoleBaseService
{
    public $adminUser;

    public function __construct(AdminUser $adminUser)
    {
        $this->adminUser = $adminUser;
    }

    public function getMyView(){

    }


    public function getDeputyUsersByUid($uid){
        $deputyUsers = [];
        $adminUserMasterSlaverRelation = AdminUserMasterSlaverRelation::find()
            ->alias('r')
            ->select(['r.admin_id','u.username'])
            ->leftJoin(AdminUser::tableName().' u','r.slave_admin_id = u.id')
            ->where(['r.admin_id' => $uid])
            ->andWhere(['>','r.slave_admin_id',0])
            ->asArray()
            ->all();
        foreach ($adminUserMasterSlaverRelation as $item){
            $deputyUsers[$item['admin_id']] = $item['username'];
        }
        return $deputyUsers;
    }
}