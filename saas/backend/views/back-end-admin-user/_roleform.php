<?php
use backend\components\widgets\ActiveForm;
use backend\models\AdminUserRole;
use yii\helpers\Html;

?>

    <style type="text/css">
        .item{ float: left; width: 300px; line-height: 25px; margin-left: 5px; border-right: 1px #deeffb dotted; }
    </style>
    <script type="text/JavaScript">
        function permcheckall(obj, perms, t) {
            var t = !t ? 0 : t;
            var checkboxs = $id(perms).getElementsByTagName('INPUT');
            for(var i = 0; i < checkboxs.length; i++) {
                var e = checkboxs[i];
                if(e.type == 'checkbox') {
                    if(!t) {
                        if(!e.disabled) {
                            e.checked = obj.checked;
                        }
                    } else {
                        if(obj != e) {
                            e.style.visibility = obj.checked ? 'hidden' : 'visible';
                        }
                    }
                    e.parentNode.parentNode.className = e.checked ? 'item checked' : 'item';
                }
            }
        }
        function checkclk(obj) {
            var obj = obj.parentNode.parentNode;
            obj.className = obj.className == 'item' ? 'item checked' : 'item';
        }
    </script>

<?php $this->showtips(Yii::T('common', 'Tips'), [Yii::T('common', 'For changes in administrators or roles, it is generally necessary for the corresponding administrators to re-login to take effect!')]); ?>

<?php $form = ActiveForm::begin(['id' => 'role-form']); ?>
    <table class="tb tb2">
        <tr><td class="td27" colspan="2"><?php echo $this->activeLabel($model, 'name'); ?></td></tr>
        <tr class="noborder">
            <?php if ($this->context->action->id == 'role-add'): ?>
                <td class="vtop rowform"><?php echo $form->field($model, 'name')->textInput(); ?></td>
                <td class="vtop tips2"><?php echo Yii::T('common', 'Only letters, numbers or underscores can be used for the unique identification, which can not be modified after being added') ?></td>
            <?php else: ?>
                <td colspan="2"><?php echo Html::encode($model->name); ?></td>
            <?php endif; ?>
        </tr>
        <tr><td class="td27" colspan="2"><?php echo $this->activeLabel($model, 'title'); ?></td></tr>
        <tr class="noborder">
            <td class="vtop rowform"><?php echo $form->field($model, 'title')->textInput(); ?></td>
            <td class="vtop tips2"></td>
        </tr>
        <tr><td class="td27" colspan="2"><?php echo $this->activeLabel($model, 'groups'); ?></td></tr>
        <tr class="noborder">
            <td class="vtop rowform"><?php echo $form->field($model, 'groups')->dropDownList(\common\helpers\CommonHelper::getListT(AdminUserRole::$status), ['prompt' => Yii::T('common', 'Select Group Name')]); ?></td>
            <td class="vtop tips2"></td>
        </tr>
        <tr><td class="td27" colspan="2"><?php echo $this->activeLabel($model, 'desc'); ?></td></tr>
        <tr class="noborder">
            <td class="vtop rowform"><?php echo $form->field($model, 'desc')->textarea(); ?></td>
            <td class="vtop tips2"></td>
        </tr>
        <tr><td class="td27" colspan="2"><?php echo $this->activeLabel($model, 'permissions'); ?></td></tr>

        <?php foreach ($permissions as $controller => $permission): ?>
        <?php if( $controller != "AdminUserController"): //AdminUserController 会和其他子系统共用，为了区分加了个BackEndAdminUserController替代之前的AdminUserController?>
        <table class="tb2" id="<?php echo Html::encode($controller); ?>">
                <tbody>
            <tr>
                <th class="partition" colspan="5">
                    <label> <?php echo Html::encode($permission['label']); ?> - <?php echo Html::encode($controller); ?></label>
                </th>
            </tr>

            <?php
            $index = 0;
            $line_cnt = 5;
            foreach ($permission['actions'] as $action):?>
                <?php if( intval($index % $line_cnt) == 0):?>
                    <tr>
                <?php endif ?>

                <td width="200px" >
                    <div class="item<?php echo in_array($action->route, $permissionChecks) ? ' checked' : ''; ?>">
                        <label class="txt">
                            <?php
                                $route = explode("/",$action->route);
                                $str = Html::encode($action->title);
                            ?>
                            <input type="checkbox" onclick="checkclk(this)" class="checkbox" value="<?php echo Html::encode($action->route); ?>" name="permissions[]"<?php echo in_array($action->route, $permissionChecks) ? ' checked' : ''; ?>><?php echo Yii::T('common', $str); ?>
                        </label>
                    </div>
                    <div style="color:#999;margin-left: 5px">
                        <?php echo Html::encode("({$route[1]})"); ?>
                    </div>
                </td>
                <?php if( intval($index++ % $line_cnt) == $line_cnt - 1):?>
                    </tr>
                <?php endif ?>
            <?php endforeach;  ?>
                </tbody>
            </table>
        <?php endif ?>
        <?php endforeach; ?>

        <tr>
            <td colspan="5">
                <input type="submit" value="submit" name="submit_btn" class="btn">
            </td>
        </tr>
    </table>
<?php ActiveForm::end(); ?>