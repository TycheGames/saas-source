<?php

use yii\helpers\Url;

/**
 * @var backend\components\View $this
 */
?>


<title><?php echo Yii::T('common', 'Table structure cache cleaning') ?></title>
<table class="tb2 fixpadding">
    <thead>
    <tr class="header">
        <th><?php echo Yii::T('common', 'Features') ?></th>
        <th><?php echo Yii::T('common', 'operation') ?></th>
    </tr>
    </thead>
    <tbody>
        <tr class="hover">
            <td><?php echo Yii::T('common', 'Table structure cache cleaning') ?></td>
            <td><button id="clear"><?php echo Yii::T('common', 'Clean up') ?></button></td>
        </tr>
    </tbody>
</table>

<script>
    $('#clear').click(function () {
        if(!confirm("<?php echo Yii::T('common', 'Confirm cleanup') ?>")){
            return;
        }
        $.post('<?php echo Url::to(['development-tools/clear-schema-cache']) ;?>',
            {
                '_csrf' : '<?php echo Yii::$app->request->csrfToken;?>'
            },
            function (data, status) {
                alert(data.msg);
        })
    });
</script>