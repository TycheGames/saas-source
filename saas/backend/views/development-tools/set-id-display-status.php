<?php

use yii\helpers\Url;

?>


<title><?php echo Yii::T('common', 'Set id display status') ?></title>
<table class="tb2 fixpadding">
    <thead>
    <tr class="header">
        <th><?php echo Yii::T('common', 'Features') ?></th>
        <th>当前状态</th>
        <th><?php echo Yii::T('common', 'operation') ?></th>
    </tr>
    </thead>
    <tbody>
        <tr class="hover">
            <td><?php echo Yii::T('common', 'Set id display status') ?></td>
            <td><?php echo $oSetting->value == 'clear' ? '明文' : '加密'; ?></td>
            <td>
                <a href="<?= Url::to(['development-tools/set-id-display-status', 'status'=>'clear']); ?>"><button>明文显示</button></a>
                <a href="<?= Url::to(['development-tools/set-id-display-status', 'status'=>'encryption']); ?>"><button>加密显示</button></a>
            </td>
        </tr>
    </tbody>
</table>

<script>
    $('#clear').click(function () {
        $.post('<?php echo Url::to(['development-tools/set-id-display-status']) ;?>',
            {
                '_csrf' : '<?php echo Yii::$app->request->csrfToken;?>',
                'status' : 'clear'
            },
            function (data, status) {
                alert(data.msg);
        })
    });

    $('#encryption').click(function () {
        $.post('<?php echo Url::to(['development-tools/set-id-display-status']) ;?>',
            {
                '_csrf' : '<?php echo Yii::$app->request->csrfToken;?>',
                'status' : 'encryption'
            },
            function (data, status) {
                alert(data.msg);
            })
    });
</script>