<?php

use common\models\enum\Industry;
use common\models\enum\Education;
use common\models\enum\Relative;
use common\models\enum\Religion;
use common\models\enum\Student;
use common\models\enum\Marital;
use common\models\enum\Seniority;
use common\models\enum\Gender;
use common\services\FileStorageService;
use common\models\enum\creditech\ReportType;
use common\helpers\CommonHelper;
use yii\helpers\Html;

$fileStorageService = new FileStorageService(false);
?>
<script type="text/javascript" src="<?php echo $this->baseUrl; ?>/jquery-photo-gallery/jquery.js"></script>
<script type="text/javascript" src="<?php echo $this->baseUrl; ?>/jquery-photo-gallery/jquery.photo.gallery.js"></script>
<table class="tb tb2 fixpadding">
    <tr><th class="partition" colspan="10">Personal information details of the borrower</th></tr>
    <tr>
        <th width="110px;" class="person">Basic information</th>
        <td style="padding: 2px;margin-bottom: 1px;border:1px solid darkgray;">
            <table style="margin-bottom: 0px" class="table">
                <tr>
                    <th>User ID</th>
                    <td><?= Html::encode(CommonHelper::idEncryption($information['loanPerson']['id'], 'user'));?></td>
                    <th>Username</th>
                    <td><?= Html::encode($information['loanPerson']['name']);?></td>
                    <th>Gender</th>
                    <td><?= Html::encode(Gender::$map[$information['loanPerson']['gender']] ?? '-');?></td>
                    <th>Phone</th>
                    <td><?= Html::encode($information['loanPerson']['phone']);?></td>
                </tr>
                <tr>
                    <th>Aadhaar number</th>
                    <td><?//= $information['loanPerson']['aadhaar_number'];?>************</td>
                    <th>Birthday</th>
                    <td class="mark"><?= Html::encode($information['loanPerson']['birthday']);?></td>
                    <th>Pan code</th>
                    <td><?= Html::encode($information['loanPerson']['pan_code']);?></td>
                    <th>Father name</th>
                    <td class="mark"><?= Html::encode($information['loanPerson']['father_name']);?></td>
                </tr>
                <tr>
                    <th>Face photos of this order</th>
                    <td class="gallerys">
                        <img class="gallery-pic" height="100" src="<?= isset($information['userFrReport']->img_fr_path) ? $fileStorageService->getSignedUrl($information['userFrReport']->img_fr_path) : '';?>"/>
                    </td>
                    <th>Pan photos</th>
                    <td class="gallerys"  colspan="5">
                        <img class="gallery-pic" height="100" src="<?= isset($information['userPanReport']->img1) ? $fileStorageService->getSignedUrl($information['userPanReport']->img1) : '';?>"/>
                        <img class="gallery-pic" height="100" src="<?=
                        isset($information['userLoanOrder']->userCreditechOCRPan->img_front_mask_path) ?
                            $fileStorageService->getSignedUrl($information['userLoanOrder']->userCreditechOCRPan->img_front_mask_path) :
                            (isset($information['userLoanOrder']->userCreditechOCRPan->img_front_path) ? $fileStorageService->getSignedUrl($information['userLoanOrder']->userCreditechOCRPan->img_front_path) : '');?>"/>
                        <img class="gallery-pic" height="100" src="<?= isset($information['userLoanOrder']->userCreditechOCRPan->img_back_path) ? $fileStorageService->getSignedUrl($information['userLoanOrder']->userCreditechOCRPan->img_back_path) : '';?>"/>
                    </td>
                </tr>

                <tr>
                    <th>Religion:</th>
                    <td><?= Religion::$map[$information['userBasicInfo']['religion']] ?? '--'; ?></td>
                    <th>Marital status:</th>
                    <td><?= Marital::$map[$information['userBasicInfo']['marital_status']] ?? '--';?></td>
                    <th>Student or not:</th>
                    <td><?= Student::$map[$information['userBasicInfo']['student']] ?? '--';?></td>
                    <th>Email address:</th>
                    <td><?= Html::encode($information['userBasicInfo']['email_address'] ?? '--');?></td>
                </tr>
                <tr>
                    <th>Loan purpose</th>
                    <td colspan="7"><?= Html::encode($information['userBasicInfo']['loan_purpose'] ?? '--');?></td>
                </tr>

            </table>
        </td>
    </tr>
    <tr>
        <th width="110px;" class="person">Job information</th>
        <td style="padding: 2px;margin-bottom: 1px; border:1px solid darkgray;">
            <table style="margin-bottom: 0px" class="table">
                <tr>
                    <th>Company name</th>
                    <td><?= Html::encode($information['userWorkInfo']['company_name'] ?? '--');?></td>
                    <th>Industry</th>
                    <td><?= Industry::$map[$information['userWorkInfo']['industry']] ?? '--';?></td>
                    <th>Position</th>
                    <td><?= Html::encode($information['userWorkInfo']['work_position'] ?? '--'); ?></td>
                    <th>Working seniority</th>
                    <td><?= Seniority::$map[$information['userWorkInfo']['working_seniority']] ?? '--';?></td>
                </tr>
                <tr>
                    <th>Company phone</th>
                    <td><?= Html::encode($information['userWorkInfo']['company_phone'] ?? '--');?></td>
                    <th>Company address</th>
                    <td><?= Html::encode($information['userWorkInfo']['company_address'] ?? '--'.$information['userWorkInfo']['company_detail_address'] ?? '--');?></td>
                    <th>Eductaion Level</th>
                    <td><?= Education::$map[$information['userWorkInfo']['educated']] ?? '--'; ?></td>
                    <th>Educated School</th>
                    <td><?= Html::encode($information['userWorkInfo']['educated_school'] ?? '--');?></td>
                </tr>
                <tr>
                    <th>Monthly salary</th>
                    <td><?php
                        $monthlySalary = intval(\common\helpers\CommonHelper::CentsToUnit($information['userWorkInfo']['monthly_salary']));
                        echo $monthlySalary;
                        ?>
                    </td>
                    <th>Residential address</th>
                    <td colspan="5"><?= $information['userWorkInfo']['residential_address'] ?? '--'.$information['userWorkInfo']['residential_detail_address'] ?? '--';?></td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <th width="110px;" class="person">Contacts</th>
        <td style="padding: 2px;margin-bottom: 1px;border:1px solid darkgray;">
            <table style="margin-bottom: 0px" class="table">
                <tr>
                    <th>Contact relationship：</th>
                    <td><?= Relative::$map[$information['userContact']['relative_contact_person']] ?? '--' ;?></td>
                    <th>Contact name：</th>
                    <td><?= Html::encode($information['userContact']['name'] ?? '--');?></td>
                    <th>Contact phone：</th>
                    <td><?= Html::encode($information['userContact']['phone'] ?? '--');?></td>
                </tr>
                <tr>
                    <th>Other contact relationship：</th>
                    <td><?= Relative::$map[$information['userContact']['other_relative_contact_person']] ?? '--';?></td>
                    <th>Other Contact name：</th>
                    <td><?= Html::encode($information['userContact']['other_name'] ?? '--');?></td>
                    <th>Other Contact phone：</th>
                    <td><?= Html::encode($information['userContact']['other_phone'] ?? '--');?></td>
                </tr>
<!--                <tr>-->
<!--                    <th>All contacts</th>-->
<!--                    <td colspan="10"><a href="--><?php //echo Url::to(['mobile-contacts/mobile-contacts-list','user_id'=>$information['loanPerson']['id']]);?><!--">Click to view address book</a></td>-->
<!--                </tr>-->
            </table>
        </td>
    </tr>
</table>

<script>
    $('.gallery-pic').click(function(){
        $.openPhotoGallery(this);
    });
    $(window).resize(function(){
        $('#J_pg').height($(window).height());
        $('#J_pg').width($(window).width());
    });
</script>