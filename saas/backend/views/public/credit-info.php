<?php

use common\models\enum\Industry;
use common\models\enum\Education;
use common\models\enum\Relative;
use common\models\enum\Religion;
use common\models\enum\Student;
use common\models\enum\Marital;
use common\models\enum\Seniority;
use common\models\enum\Gender;
use yii\helpers\Url;
use yii\helpers\Html;

?>
<table class="tb tb2 fixpadding">
    <tr><th class="partition" colspan="10">Tax forms</th></tr>
    <tr>
<!--        <th width="110px;" class="person">Credit and tax forms</th>-->
        <td style="padding: 2px;margin-bottom: 1px;border:1px solid darkgray;">
            <table style="margin-bottom: 0px" class="table">
                <tr>
                    <th>Tax company name</th>
                    <td>
                        <?php
                        if(isset($information['userTaxReport'])){
                            foreach ($information['userTaxReport'] as $key => $userTaxReport){
                                if(isset($userTaxReport->report_data)){
                                    $data = json_decode($userTaxReport->report_data, true);
                                    if(isset($data['company_name'])){
                                        echo Html::encode('('.($key+1).')、'.$data['company_name']);
                                    }
                                }
                            }
                        }else{
                            echo '--';
                        }
                        ;?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>