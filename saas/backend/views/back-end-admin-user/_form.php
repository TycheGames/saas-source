<?php
use backend\components\widgets\ActiveForm;
use backend\models\AdminUserRole;
use Yii\helpers\Html;
use backend\models\Merchant;

?>
<style type="text/css">
.item{ float: left; width: 180px; line-height: 25px; margin-left: 5px; border-right: 1px #deeffb dotted; position: relative;}
/*.desc_show{display: none;position: absolute;top:20px;left:25px;background-color: #fff;}*/

</style>
<?php $this->showtips(Yii::T('common', 'Tips'), [Yii::T('common', 'For changes in administrators or roles, it is generally necessary for the corresponding administrators to re-login to take effect!')]); ?>

<?php $form = ActiveForm::begin(['id' => 'admin-form']); ?>
    <table class="tb tb2">
        <tr><td class="td27" colspan="2"><?php echo $this->activeLabel($model, 'username'); ?></td></tr>
        <tr class="noborder">
            <?php if ($this->context->action->id == 'add'): ?>
            <td class="vtop rowform"><?php echo $form->field($model, 'username')->textInput(['autocomplete' => 'off']); ?></td>
            <td class="vtop tips2"><?php echo Yii::T('common', 'It can only be letters, numbers or underscores. It can\'t be repeated. It can\'t be modified after adding') ?></td>
            <?php else: ?>
            <td colspan="2"><?php echo Html::encode($model->username); ?></td>
            <?php endif; ?>
        </tr>
        <tr><td class="td27" colspan="2"><?php echo $this->activeLabel($model, 'phone'); ?></td></tr>

        <tr class="noborder">
            <td class="vtop rowform"><?php echo $form->field($model, 'phone')->textInput(); ?></td>
        </tr>

        <?php if(!Yii::$app->user->identity->merchant_id): ?>
        <tr><td class="td27" colspan="2"><?php echo $this->activeLabel($model, 'merchant_id'); ?></td></tr>
        <tr>
            <td class="vtop rowform">
                <?php echo $form->field($model, 'merchant_id')->dropDownList(Merchant::getMerchantId()); ?>
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
                            <label class="txt"><input type="checkbox" class="checkbox" value="<?php echo Html::encode($k);?>" name="to_view_merchant_id[]" <?php if(in_array($k,explode(",", $model->to_view_merchant_id))){echo "checked";}?>>
                                <?php echo Html::encode($v); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </td>
        </tr>

        <tr><td class="td27" colspan="2"><?php echo $this->activeLabel($model, 'role'); ?></td></tr>
        <tr class="noborder">
            <td class="vtop rowform" colspan="2">
                <table class="tb tb2">
                <?php if ($is_super_admin || in_array("super_dev", $current_roles_arr)): //超级管理员或者超级开发?>
                    <?php foreach ($roles as $key => $role) {?>
                        <?php if ($key>0 ): ?>
                            <tr>
                                <th class="partition" colspan="15">
                                    <label><?php echo AdminUserRole::$status[$key];?></label>
                                </th>
                            </tr>
                            <tr>
                                <td class="vtop">
                                    <?php foreach ($role as $key => $val): ?>
                                        <div class="item">
                                            <label class="txt"><input type="checkbox" class="checkbox" value="<?php echo Html::encode($key);?>" name="roles[]" <?php if(in_array($key,explode(",", $model->role))){echo "checked";}?>>
                                                <?php echo Html::encode($val['title']); ?><br/><span style="color:#999999;"><?php echo Html::encode($val['desc']); ?></span>
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
                                    <label class="txt"><input type="checkbox" class="checkbox" value="<?php echo Html::encode($k);?>" name="roles[]" <?php if(in_array($k,explode(",", $model->role))){echo "checked";}?>>
                                        <?php echo Html::encode($v['title']); ?><br/><?php echo Html::encode($v['desc']); ?>
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
                                        <label><?php echo Html::encode(AdminUserRole::$status[$group]);?></label>
                                    </th>
                                </tr>

                                <tr>
                                    <td class="vtop">
                                        <?php foreach ($roles[$group] as $key => $val): ?>
                                            <div class="item">
                                                <label class="txt"><input type="checkbox" class="checkbox" value="<?php echo Html::encode($key);?>" name="roles[]" <?php if(in_array($key,explode(",", $model->role))){echo "checked";}?>>
                                                    <?php echo Html::encode($val['title']); ?><br/><span style="color:#999999;"><?php echo Html::encode($val['desc']); ?></span>
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
        <?php if ($this->context->action->id == 'add'): ?>
        <tr><td class="td27" colspan="2"><?php echo $this->activeLabel($model, 'password'); ?></td></tr>
        <tr class="noborder">
            <td class="vtop rowform"><input type="password" autocomplete="off" name="AdminUser[password]" class="txt" id="adminuser-password"></td>
            <td class="vtop tips2">Password 6-16 bits character or number</td>
        </tr>
        <?php endif; ?>
        <tr><td class="td27" colspan="2"><?php echo $this->activeLabel($model, 'mark'); ?></td></tr>
        <tr class="noborder">
            <td class="vtop rowform"><?php echo $form->field($model, 'mark')->textArea(); ?></td>
        </tr>
        <tr>
            <td colspan="15">
                <input type="submit" value="submit" name="submit_btn" class="btn">
            </td>
        </tr>
    </table>
<?php ActiveForm::end(); ?>
<script type="text/javascript">
    // $('.item').bind('mouseover',function(){
    //     $(this).children('.desc_show').show();
    // });
    // $('.item').bind('mouseout',function(){
    //     $(this).children('.desc_show').hide();
    // });
</script>