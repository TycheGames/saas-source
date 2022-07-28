<?php

namespace callcenter\service\roles;


use callcenter\models\AdminUser;
use callcenter\models\AdminUserRole;

class AdminRoleService
{

    public $roleGroup;

    public $roleService;

    public $adminUser;

    public $organization;

    public function __construct(AdminUser $adminUser)
    {
        $this->adminUser = $adminUser;
        if($adminUser->role == AdminUser::SUPER_ROLE){
            $this->roleGroup = 0;
        }else{
            $this->roleGroup = AdminUserRole::getGroupByRoles($adminUser->role);
        }
        if(isset(AdminUserRole::$groups_map[$this->roleGroup])){
            switch ($this->roleGroup){
//                case AdminUserRole::TYPE_DEFAULT:
//                case AdminUserRole::TYPE_SUPER_MANAGER:
//                    $this->roleService = new RoleManagerService();
//                    break;
                case AdminUserRole::TYPE_SMALL_TEAM_MANAGER:
                    $this->roleService = new RoleSmallTeamService($adminUser);
                    break;
                case AdminUserRole::TYPE_BIG_TEAM_MANAGER:
                    $this->roleService = new RoleBigTeamService($adminUser);
                    break;
                case AdminUserRole::TYPE_SUPER_TEAM:
                    $this->roleService = new RoleSuperTeamService($adminUser);
                    break;
//                case AdminUserRole::TYPE_COMPANY_MANAGER:
//                    $this->roleService = new RoleCompanyService();
//                    break;
                case AdminUserRole::TYPE_COLLECTION:
                    $this->roleService = new RoleCollectorService($adminUser);
                    break;
            }
        }
    }

    public function setOrganization()
    {
        if($this->roleService){
            return [$this->roleService->getMyView()];
        }else{
            return [];
        }
    }
}