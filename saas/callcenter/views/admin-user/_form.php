<?php
use callcenter\components\widgets\ActiveForm;
use callcenter\models\AdminUserRole;
use callcenter\models\loan_collection\LoanCollectionOrder;
use callcenter\models\AdminUser;

?>
<style type="text/css">
.item{ float: left; width: 180px; line-height: 25px; margin-left: 5px; border-right: 1px #deeffb dotted; position: relative;}
/*.desc_show{display: none;position: absolute;top:20px;left:25px;background-color: #fff;}*/

</style>
<?php $this->showtips('技巧提示', ['对于管理员或角色的变更，一般需要对应的管理员重新登录才生效！!']); ?>

<?php $form = ActiveForm::begin(['id' => 'admin-form']); ?>
	<table class="tb tb2">
		<tr><td class="td27" colspan="2"><?php echo $this->activeLabel($model, 'username'); ?></td></tr>
		<tr class="noborder">
			<?php if ($this->context->action->id == 'add'): ?>
			<td class="vtop rowform"><?php echo $form->field($model, 'username')->textInput(['autocomplete' => 'off']); ?></td>
			<td class="vtop tips2">只能是字母、数字或下划线，不能重复，添加后不能修改</td>
			<?php else: ?>
			<td colspan="2"><?php echo $model->username; ?></td>
			<?php endif; ?>
		</tr>
		<tr><td class="td27" colspan="2"><?php echo $this->activeLabel($model, 'phone'); ?></td></tr>
		<tr class="noborder">
			<td class="vtop rowform"><?php echo $form->field($model, 'phone')->textInput(); ?></td>
		</tr>
		<?php if ($this->context->action->id == 'add'): ?>
		<tr><td class="td27" colspan="2"><?php echo $this->activeLabel($model, 'password'); ?></td></tr>
		<tr class="noborder">
            <td class="vtop rowform"><?php echo $form->field($model, 'password')->passwordInput(['class' => 'txt']); ?></td>
			<td class="vtop tips2">密码为6-16位字符或数字</td>
		</tr>
		<?php endif; ?>
        <?php if($strategyOperating):?>
            <tr><td class="td27" colspan="2"><?php echo $this->activeLabel($model, 'open_search_label'); ?></td></tr>
            <tr class="noborder">
                <td class="vtop rowform"><?php echo $form->field($model, 'open_search_label')->dropDownList(AdminUser::$can_search_label_map); ?></td>
            </tr>
            <tr><td class="td27" colspan="2"><?php echo $this->activeLabel($model, 'login_app'); ?></td></tr>
            <tr class="noborder">
                <td class="vtop rowform"><?php echo $form->field($model, 'login_app')->dropDownList(AdminUser::$can_login_app_map); ?></td>
            </tr>
            <tr><td class="td27" colspan="2"><?php echo $this->activeLabel($model, 'nx_phone'); ?></td></tr>
            <tr class="noborder">
                <td class="vtop rowform"><?php echo $form->field($model, 'nx_phone')->dropDownList(AdminUser::$can_use_nx_phone_map); ?></td>
            </tr>
            <tr><td class="td27" colspan="2"><?php echo $this->activeLabel($model, 'job_number'); ?></td></tr>
            <tr class="noborder">
                <td class="vtop rowform"><?php echo $form->field($model, 'job_number')->textInput(); ?></td>
            </tr>
        <?php endif; ?>
        <tr><td class="td27" colspan="2"><?php echo $this->activeLabel($model, 'role'); ?></td></tr>
        <tr class="noborder">
            <td class="vtop rowform" colspan="2">
                <table class="tb tb2">
                    <?php if ($is_super_admin || in_array(AdminUserRole::TYPE_SUPER_MANAGER, $current_user_groups_arr)): //超级管理员或者超级开发?>
                        <?php foreach ($roles as $key => $role) {?>
                            <?php if ($key>0 ): ?>
                                <tr>
                                    <th class="partition" colspan="15">
                                        <label><?php echo AdminUserRole::$groups_map[$key];?></label>
                                    </th>
                                </tr>
                                <tr>
                                    <td class="vtop">
                                        <?php foreach ($role as $key => $val): ?>
                                            <div class="item">
                                                <label class="txt"><input type="radio" class="radio" value="<?php echo $key;?>" name="roles" <?php if(in_array($key,explode(",", $model->role))){echo "checked";}?>>
                                                    <?php echo $val['title']; ?><br/><span style="color:#999999;"><?php echo $val['desc']; ?></span>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php }?>
                        <tr>
                            <th class="partition" colspan="15">
                                <label><?php echo 'Ungrouped';?></label>
                            </th>
                        </tr>
                        <tr>
                            <td class="vtop">
                                <?php if (!empty($roles[0])) : ?>
                                    <?php foreach ($roles[0] as $k => $v): ?>
                                        <div class="item">
                                            <label class="txt"><input type="radio" class="radio" value="<?php echo $k;?>" name="roles[]" <?php if(in_array($k,explode(",", $model->role))){echo "checked";}?>>
                                                <?php echo $v['title']; ?><br/><?php echo $v['desc']; ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php else://其他角色 ?>
                        <?php if(!empty($current_user_groups_arr)):?>
                            <?php foreach ($current_user_groups_arr as $g_key => $group): ?>
                                <?php if (!empty($roles[$group])): ?>
                                    <tr>
                                        <th class="partition" colspan="15">
                                            <label><?php echo AdminUserRole::$groups_map[$group];?></label>
                                        </th>
                                    </tr>

                                    <tr>
                                        <td class="vtop">
                                            <?php foreach ($roles[$group] as $key => $val): ?>
                                                <div class="item">
                                                    <label class="txt"><input type="radio" class="radio" value="<?php echo $key;?>" name="roles[]" <?php if(in_array($key,explode(",", $model->role))){echo "checked";}?>>
                                                        <?php echo $val['title']; ?><br/><span style="color:#999999;"><?php echo $val['desc']; ?></span>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </table>
            </td>
        </tr>
		<tr><td class="td27" colspan="2"><?php echo $this->activeLabel($model, 'mark'); ?></td></tr>
		<tr class="noborder">
			<td class="vtop rowform"><?php echo $form->field($model, 'mark')->textArea(); ?></td>
		</tr>
        <?php if($isNotMerchantAdmin): ?>
            <tr><td class="td27" colspan="2"><?php echo $this->activeLabel($model, 'merchant_id'); ?></td></tr>
            <tr>
                <td class="vtop rowform">
                    <?php echo $form->field($model, 'merchant_id')->dropDownList($arrMerchantIds,[
                        'onchange' => 'getOutsideList($(this).val())'
                    ]); ?>
                </td>
            </tr>
        <?php endif; ?>
        <tr>
            <th class="partition" colspan="15">
                <label>To view merchant</label>
            </th>
        </tr>
        <tr>
            <td class="vtop">
                <?php if (!empty($arrMerchantIds)) : ?>
                    <?php foreach ($arrMerchantIds as $k => $v): ?>
                        <div class="item">
                            <label class="txt"><input type="checkbox" class="checkbox" value="<?php echo $k;?>" name="to_view_merchant_id[]" <?php if(in_array($k,explode(",", $model->to_view_merchant_id))){echo "checked";}?>>
                                <?php echo $v; ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </td>
        </tr>
        <tr><td class="td27" colspan="2"><?php echo $this->activeLabel($model, 'outside'); ?></td></tr>
        <tr>
            <td class="vtop rowform">
                <?php echo $form->field($model, 'outside')->dropDownList($defaultCompanys,['prompt' => 'none']); ?>
            </td>
        </tr>
        <tr><td class="td27" colspan="2"><?php echo $this->activeLabel($model, 'group'); ?></td></tr>
        <tr>
            <td class="vtop rowform">
                <?php echo $form->field($model, 'group')->dropDownList(LoanCollectionOrder::$current_level,['prompt' => 'none']); ?>
            </td>
        </tr>
        <tr><td class="td27" colspan="2"><?php echo $this->activeLabel($model, 'group_game'); ?></td></tr>
        <tr>
            <td class="vtop rowform">
                <?php echo $form->field($model, 'group_game')->dropDownList(AdminUser::$group_games,['prompt' => 'none']); ?>
            </td>
        </tr>
		<tr>
			<td colspan="15">
				<input type="submit" value="提交" name="submit_btn" class="btn">
            <a href="javascript:history.go(-1)" class="btn back" style="cursor: pointer;border:none;">返回</a>
			</td>
		</tr>
	</table>
<?php ActiveForm::end(); ?>
<script>
    var outsides = <?php echo json_encode($companys);?>;
    //方式切换
    function getOutsideList(merchant_id)
    {
        var trElement = '<option value>none</option>';
        if(outsides[merchant_id]){
            $.each(outsides[merchant_id], function(key,val){
                trElement += '<option value="'+ key +'">'+ val +'</option>';
            });
            if(trElement == ''){
                trElement = '<option value="">--</option>'
            }
        }
        $('#adminuser-outside').html(trElement);
    }
</script>