<?php

use common\models\enum\Industry;
use common\models\enum\Education;
use common\models\enum\Relative;
use common\models\enum\Religion;
use common\models\enum\Student;
use common\models\enum\Marital;
use common\models\enum\Seniority;
use yii\helpers\Html;

?>


<table class="tb tb2 fixpadding">
    <tr><th class="partition" colspan="10">Expand historical information</th></tr>
    <tr>
        <th width="110px;" class="person">Basic Information（history）</th>
        <td style="padding: 2px;margin-bottom: 1px; border:1px solid darkgray;">
            <table style="margin-bottom: 0px" class="table">
                <tr>
                    <th>Religion:</th>
                    <th>Is student:</th>
                    <th>Marital:</th>
                    <th>Email:</th>
                    <th>Purpose</th>
                </tr>
                <?php foreach ($informationAll['userBasicInfos'] ?? [] as $v): ?>
                    <tr>
                        <td><?= Religion::$map[$v['religion']] ?? ''; ?></td>
                        <td><?= Student::$map[$v['student']] ?? '';?></td>
                        <td><?= Marital::$map[$v['marital_status']] ?? '';?></td>
                        <td><?= Html::encode($v['email_address']);?></td>
                        <td><?= Html::encode($v['loan_purpose']);?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($informationAll['userBasicInfos'])): ?>
                    <tr>
                        <td colspan="5">No historical record</td>
                    </tr>
                <?php endif; ?>
            </table>
        </td>
    </tr>
    <tr>
        <th width="110px;" class="person">Work Information（history）</th>
        <td style="padding: 2px;margin-bottom: 1px; border:1px solid darkgray;">
            <table style="margin-bottom: 0px" class="table">
                <tr>
                    <th>Education</th>
                    <th>Educated school</th>
                    <th>Industry</th>
                    <th>Residential Address</th>
                    <th>Company</th>
                    <th>Company phone</th>
                    <th>Working Seniority</th>
                    <th>Company Address</th>
                </tr>
                <?php foreach ($informationAll['userWorkInfos'] ?? [] as $v): ?>
                    <tr>
                        <td><?= Education::$map[$v['educated']] ?? null;?></td>
                        <td><?= Html::encode($v['educated_school']);?></td>
                        <td><?= Industry::$map[$v['industry']] ?? ''?></td>
                        <td><?= Html::encode($v['residential_address1'].$v['residential_address2'].$v['residential_detail_address']);?></td>
                        <td><?= Html::encode($v['company_name']);?></td>
                        <td><?= Html::encode($v['company_phone']);?></td>
                        <td><?= Html::encode(Seniority::$map[$v['working_seniority']] ?? '');?></td>
                        <td><?= Html::encode($v['company_address1'].$v['company_address2'].$v['company_detail_address']);?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($informationAll['userWorkInfos'])): ?>
                    <tr>
                        <td colspan="8">No record</td>
                    </tr>
                <?php endif; ?>
            </table>
        </td>
    </tr>
    <tr>
        <th width="110px;" class="person">Bank card information (history)</th>
        <td style="padding: 2px;margin-bottom: 1px;border:1px solid darkgray;">
            <table style="margin-bottom: 0px" class="table">
                <tr>
                    <th >Ifsc code</th>
                    <th>Account</th>
                    <th>Name</th>
                    <th>Certification status</th>
                </tr>
                <?php foreach ($informationAll['userBankAccounts'] ?? [] as $v): ?>
                    <tr>
                        <td><?= Html::encode($v['ifsc']);?></td>
                        <td><?= Html::encode($v['account']);?></td>
                        <td><?= Html::encode($v['name']);?></td>
                        <td><?= Html::encode($v['status']);?></td>
                    </tr>
                <?php endforeach;?>
                <?php if (empty($informationAll['userBankAccounts'])): ?>
                    <tr>
                        <td colspan="4">No record</td>
                    </tr>
                <?php endif; ?>
            </table>
        </td>
    </tr>
    <tr>
        <th width="110px;" class="person">Contact person (history)</th>
        <td style="padding: 2px;margin-bottom: 1px;border:1px solid darkgray;">
            <table style="margin-bottom: 0px" class="table">
                <tr>
                    <th>Contact relation：</th>
                    <th>Name：</th>
                    <th>Phone：</th>
                    <th>Contact relation (other)：</th>
                    <th>Name (other)：</th>
                    <th>Phone (other)：</th>
                    <th>Add time：</th>
                </tr>
                <?php foreach ($informationAll['userContacts'] ?? [] as $v): ?>
                    <tr>
                        <td><?= Relative::$map[$v['relative_contact_person']] ?? '' ;?></td>
                        <td><?= Html::encode($v['name']);?></td>
                        <td><?= Html::encode($v['phone']);?></td>
                        <td><?= Html::encode(Relative::$map[$v['other_relative_contact_person']] ?? '');?></td>
                        <td><?= Html::encode($v['other_name']);?></td>
                        <td><?= Html::encode($v['other_phone']);?></td>
                        <td><?= Html::encode(date("Y-m-d",$v['created_at']));?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($informationAll['userContacts'])): ?>
                    <tr>
                        <td colspan="7">No record</td>
                    </tr>
                <?php endif; ?>
            </table>
        </td>
    </tr>
</table>
