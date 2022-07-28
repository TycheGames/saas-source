<?php

use yii\helpers\Url;

?>


<table class="tb2 fixpadding">
    <thead>
    <tr class="header">
        <th>输入明文ID</th>
        <th></th>
        <th>加密ID</th>
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
        $.post('<?php echo Url::to(['development-tools/id-encryption']) ;?>',
        {
            '_csrf' : '<?php echo Yii::$app->request->csrfToken;?>',
            'id' : y
        },
        function (data, status) {
            $('#decryption_id').html(data);
        })
    });


</script>