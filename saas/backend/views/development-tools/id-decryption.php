<?php

use yii\helpers\Url;

?>


<title><?php echo Yii::T('common', 'Set id display status') ?></title>
<table class="tb2 fixpadding">
    <thead>
    <tr class="header">
        <th>输入加密ID</th>
        <th></th>
        <th>明文ID</th>
    </tr>
    </thead>
    <tbody>
        <tr class="hover">
            <td><input type="text" id="encryption_id"></td>
            <td><button id="button">提交</button></td>
            <td id="decryption_id"></td>
        </tr>
    </tbody>
</table>

<script>
    $('#button').click(function () {
        var y = document.getElementById('encryption_id').value;
        $.post('<?php echo Url::to(['development-tools/id-decryption']) ;?>',
        {
            '_csrf' : '<?php echo Yii::$app->request->csrfToken;?>',
            'id' : y
        },
        function (data, status) {
            if (data > 0) {
                $('#decryption_id').html(data);
            } else {
                $('#decryption_id').html('解析失败');
            }

        })
    });


</script>