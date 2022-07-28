<?php
use callcenter\components\widgets\ActiveForm;
use callcenter\models\AdminUserRole;
use yii\helpers\Html;

?>

    <style type="text/css">
        .item{ float: left; width: 180px; line-height: 25px; margin-left: 5px; border-right: 1px #deeffb dotted; }
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

<?php $this->showtips('技巧提示', ['对于管理员或角色的变更，一般需要对应的管理员重新登录才生效！']); ?>

<?php $form = ActiveForm::begin(['id' => 'role-form']); ?>
    <table class="tb tb2">
        <tr><td class="td27" colspan="2"><?php echo $this->activeLabel($model, 'name'); ?></td></tr>
        <tr class="noborder">
            <?php if ($this->context->action->id == 'role-add'): ?>
                <td class="vtop rowform"><?php echo $form->field($model, 'name')->textInput(); ?></td>
                <td class="vtop tips2">唯一标识，只能是字母、数字或下划线，添加后不能修改</td>
            <?php else: ?>
                <td colspan="2"><?php echo $model->name; ?></td>
            <?php endif; ?>
        </tr>
        <tr><td class="td27" colspan="2"><?php echo $this->activeLabel($model, 'title'); ?></td></tr>
        <tr class="noborder">
            <td class="vtop rowform"><?php echo $form->field($model, 'title')->textInput(); ?></td>
            <td class="vtop tips2"></td>
        </tr>
        <tr><td class="td27" colspan="2"><?php echo $this->activeLabel($model, 'groups'); ?></td></tr>
        <tr class="noborder">
            <td class="vtop rowform"><?php echo $form->field($model, 'groups')->dropDownList(AdminUserRole::$groups_map); ?></td>
            <td class="vtop tips2"></td>
        </tr>
        <tr><td class="td27" colspan="2"><?php echo $this->activeLabel($model, 'desc'); ?></td></tr>
        <tr class="noborder">
            <td class="vtop rowform"><?php echo $form->field($model, 'desc')->textarea(); ?></td>
            <td class="vtop tips2"></td>
        </tr>
        <tr><td class="td27" colspan="2"><?php echo $this->activeLabel($model, 'permissions'); ?></td></tr>

        <?php foreach ($permissions as $controller => $permission): ?>
            <table class="tb2" id="<?php echo $controller; ?>">
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
                                $str = $action->title;
                            ?>
                            <input type="checkbox" onclick="checkclk(this)" class="checkbox" value="<?php echo Html::encode($action->route); ?>" name="permissions[]"<?php echo in_array($action->route, $permissionChecks) ? ' checked' : ''; ?>><?php echo $str; ?>
                        </label>
                    </div>
                    <div class="item" style="color:#999;margin-left: 5px">
                        <p><?php echo Html::encode("({$route[1]})"); ?></p>
                    </div>
                </td>
                <?php if( intval($index++ % $line_cnt) == $line_cnt - 1):?>
                    </tr>
                <?php endif ?>
            <?php endforeach;  ?>
                </tbody>
            </table>
        <?php endforeach; ?>

        <tr>
            <td colspan="5">
                <input type="submit" value="提交" name="submit_btn" class="btn">
            <a href="javascript:history.go(-1)" class="btn back" style="cursor: pointer;border:none;">返回</a>
            </td>
        </tr>
    </table>
<?php ActiveForm::end(); ?>