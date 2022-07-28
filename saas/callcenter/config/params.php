<?php
return [

    'adminEmail' => '',
    // 权限配置Controller,只能是后台callcenter命名空间下的
    'permissionControllers' => [
        'AdminUserController'=>'用户管理',
        //'CollectionIpController'=>'催收IP白名单',
        'UserCompanyController'=>'催收机构管理',
        'UserCollectionController'=>'催收人员管理',
        'WorkDeskController'=>'催收工作台',
        'CollectionController'=>'催收管理',
        'CollectionStatisticsController'=>'催收统计',
        'CollectionStatisticsNewController'=>'催收统计(新)',
        'RepayOrderController'=>'订单还款',
        'ContentSettingController'=>'内容设置',
        'DispatchScriptController'=> '脚本分派',
    ],

     //权限下的二级方法

];
