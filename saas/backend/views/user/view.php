<?php
use yii\helpers\Html;
?>
<style>

    .person {
        border:1px solid darkgray;
        background: #f5f5f5 none repeat scroll 0 0;
        font-weight: bold;
    }
    .table {
        max-width: 100%;
        width: 100%;
        border:1px solid #ddd;
    }
    .table th{
        border:1px solid darkgray;
        background: #f5f5f5 none repeat scroll 0 0;
        font-weight: bold;
        width:100px
    }
    .table td{
        border:1px solid darkgray;
    }
    .tb2 th{
        border:1px solid darkgray;
        background: #f5f5f5 none repeat scroll 0 0;
        font-weight: bold;
        width:100px
    }
    .tb2 td{
        border:1px solid darkgray;
    }
    .tb2 {
        border:1px solid darkgray;
    }
    .mark {
        font-weight: bold;
        /*background-color:indianred;*/
        color:red;
    }
</style>

<table class="tb tb2 fixpadding">
    <tr><th class="partition" colspan="10">user detail info</th></tr>
    <tr>
        <th width="110px;" class="person">user detail</th>
        <td style="padding: 2px;margin-bottom: 1px; border:1px solid darkgray;">
            <table style="margin-bottom: 0px" class="table">
                <tr>
                    <th ><?php echo Yii::T('common', 'userId') ?>：</th>
                    <td><?php echo Html::encode(\common\helpers\CommonHelper::idEncryption($information['loanPerson']['id'], 'user'));?></td>
                    <th ><?php echo Yii::T('common', 'username') ?>：</th>
                    <td><?php echo Html::encode($information['loanPerson']['name']);?></td>
                    <th><?php echo Yii::T('common', 'aadhaarNumber') ?>：</th>
                    <td><?php echo Html::encode($information['loanPerson']['aadhaar_number']);?></td>
                    <th><?php echo Yii::T('common', 'phone') ?>：</th>
                    <td><?php echo Html::encode($information['loanPerson']['phone']);?></td>
                </tr>
                <tr>
                    <th>pan_code：</th>
                    <td><?php echo Html::encode($information['loanPerson']['pan_code']);?></td>
                    <th><?php echo Yii::T('common', 'birthday') ?>：</th>
                    <td><?php echo Html::encode($information['loanPerson']['birthday'] ?? "--:--");?></td>
                    <th><?php echo Yii::T('common', 'gender') ?>：</th>
                    <td><?php echo Html::encode(\common\models\enum\Gender::$map[$information['loanPerson']['gender']] ?? '-');?></td>
                    <th><?php echo Yii::T('common', 'age') ?>：</th>
                    <td class="mark"><?php
                        $birthday = $information['loanPerson']['birthday'];
                        if(empty($birthday)){
                            echo "--";
                        }else{
                            $age = date('Y', time()) - date('Y', strtotime($birthday)) - 1;
                            if (date('m', time()) == date('m', strtotime($birthday))){

                                if (date('d', time()) > date('d', strtotime($birthday))){
                                    $age++;
                                }
                            }elseif (date('m', time()) > date('m', strtotime($birthday))){
                                $age++;
                            }
                            echo $age;
                        }
                        ?></td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<?= $this->render('/public/person-record-info', [
    'informationAll' => $information,
]); ?>




