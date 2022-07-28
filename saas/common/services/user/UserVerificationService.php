<?php


namespace common\services\user;


use Carbon\Carbon;
use common\models\enum\PackageName;
use common\models\enum\VerificationItem;
use common\models\question\UserQuestionVerification;
use common\models\user\LoanPerson;
use common\models\user\UserBasicInfo;
use common\models\user\UserRegisterInfo;
use Yii;

class UserVerificationService
{
    /**
     * @var LoanPerson $user
     */
    private $userVerification;
    private $user;

    public function __construct(int $userID)
    {
        $this->user = LoanPerson::findById($userID);
        $this->userVerification = $this->user->userVerification;
    }

    /**
     * 获取用户所有人认证项是否完成
     * @param bool $isExternal
     * @return bool
     */
    public function getAllVerificationStatus($isExternal = false)
    {
        $status = $this->getVerificationStatus();
        $identityStatus = $status[VerificationItem::IDENTITY()->getValue()];
        $newBasicStatus = $status[VerificationItem::BASIC()->getValue()];
        $addressStatus = $status[VerificationItem::ADDRESS()->getValue()];
        $languageStatus = $status[VerificationItem::LANGUAGE()->getValue()];
        $contactStatus = $status[VerificationItem::CONTACT()->getValue()];
        $faceCompareStatus = $status[VerificationItem::FACE_COMPARE()->getValue()];
        $result = $identityStatus && $newBasicStatus && $contactStatus && $addressStatus && $languageStatus;
//        $result = $identityStatus && $newBasicStatus && $ekycStatus && $contactStatus && $addressStatus;
        if ($this->user->customer_type == 1 && $isExternal == false) {
            $result = $result && $faceCompareStatus;
        }

        return $result;
    }

    public function getVerificationStatus(string $appVersion = '1.3.7'): array
    {
        $frLivenessStatus = $this->userVerification->real_verify_status == 1;
//        if ($frLivenessStatus) {
            //有效期判断，目前永久
//        }
        $ocrAadStatus = $this->userVerification->ocr_aadhaar_status == 1;
//        if ($ocrAadStatus) {
            //有效期判断，目前永久
//        }
        $ocrPanStatus = $this->userVerification->ocr_pan_status == 1;
//        if ($ocrPanStatus) {
            //有效期判断，目前永久
//        }
        $panVerifyStatus = $this->userVerification->real_pan_status == 1;
//        if ($panVerifyStatus) {
            //有效期判断，目前永久
//        }
        $frPanStatus = $this->userVerification->real_fr_compare_pan_status == 1;
//        if ($frPanStatus) {
            //有效期判断，目前永久
//        }
        //兼容逻辑，修改注意
        $identityStatus = $frLivenessStatus && $ocrPanStatus && $panVerifyStatus && $frPanStatus;

        $workStatus = $this->userVerification->real_work_status == 1;
        if ($workStatus) {
            $updateTime = $this->user->userWorkInfo->updated_at ?? time();
            if ($this->checkDataExpired(VerificationItem::WORK(), Carbon::createFromTimestamp($updateTime))) {
                $workStatus = false;
            }
        }
        $basicStatus = $this->userVerification->real_basic_status == 1;
        if ($basicStatus) {
            $updateTime = $this->user->userBasicInfo->updated_at ?? time();
            if ($this->checkDataExpired(VerificationItem::BASIC(), Carbon::createFromTimestamp($updateTime))) {
                $basicStatus = false;
            }
        }
        if ($this->user->customer_type == 0) {
            $userBasicInfo = $this->user->userBasicInfo;
            if (empty($userBasicInfo->aadhaar_pin_code) ||
                empty($userBasicInfo->aadhaar_address1) ||
                empty($userBasicInfo->aadhaar_address2) ||
                empty($userBasicInfo->aadhaar_detail_address)
            ) {
                $basicStatus = false;
            }
        }
        $newBasicStatus = $basicStatus && $workStatus;

        $addressStatus = $ocrAadStatus;
//        if ($addressStatus) {
            //有效期判断，目前永久
//        }

        $contactStatus = $this->userVerification->real_contact_status == 1;
        if ($contactStatus) {
            $updateTime = $this->user->userContact->updated_at ?? time();
            if ($this->checkDataExpired(VerificationItem::CONTACT(), Carbon::createFromTimestamp($updateTime))) {
                $contactStatus = false;
            }
        }

        $languageStatus = true;

        if ($this->user->userFrFrReport) {
            $updateTime = $this->user->userFrFrReport->updated_at ?? time();
            if ($this->checkDataExpired(VerificationItem::FACE_COMPARE(), Carbon::createFromTimestamp($updateTime))) {
                $faceStatus = false;
            } else {
                $faceStatus = true;
            }
        } else {
            $faceStatus = false;
        }

        return [
            VerificationItem::IDENTITY()->getValue()      => $identityStatus,
            VerificationItem::BASIC()->getValue()         => $newBasicStatus, //basicInfo与workInfo合并
            VerificationItem::ADDRESS()->getValue()       => $addressStatus,
            VerificationItem::CONTACT()->getValue()       => $contactStatus,
            VerificationItem::LANGUAGE()->getValue()      => $languageStatus,
            VerificationItem::FACE_COMPARE()->getValue()  => $faceStatus, //始终过期
        ];
    }

    private function checkDataExpired(VerificationItem $item, Carbon $updateTime): bool
    {
        $serviceMap = [
            VerificationItem::IDENTITY()->getValue()      => UserKYCService::class,
            VerificationItem::BASIC()->getValue()         => UserBasicInfoService::class,
            VerificationItem::WORK()->getValue()          => UserWorkInfoService::class,
            VerificationItem::ADDRESS()->getValue()       => UserAddressService::class,
            VerificationItem::CONTACT()->getValue()       => UserContactService::class,
            VerificationItem::LANGUAGE()->getValue()      => UserQuestionService::class,
            VerificationItem::FACE_COMPARE()->getValue()  => UserFaceCompare::class,
//            VerificationItem::TAX_BILL()->getValue()      => UserTaxService::class,         //按照规则
        ];

        /**
         * @var IThirdDataService $service
         */
        $service = new $serviceMap[$item->getValue()];

        return $service->checkDataExpired($updateTime);
    }

    private function getVerificationProcess(): array
    {
        //以数字1开始，简化判断
        return [
            1 => VerificationItem::BASIC(),
            2 => VerificationItem::IDENTITY(),
            3 => VerificationItem::ADDRESS(), //有特殊逻辑兼容旧版本，修改需要需要注意
            4 => VerificationItem::CONTACT(),
            5 => VerificationItem::LANGUAGE(), //有特殊逻辑新老用户，准入城市，修改需要需要注意，下线时需要注释getAllVerificationStatus中的判断
            9 => VerificationItem::FACE_COMPARE()
        ];
    }

    private function getVerificationPath(array $extraParams, string $host = ''): array
    {
        //客户端H5,type必须h5
        //H5跳客户端,type必须client
        //H5跳H5,type h5、client均可
        $path = $host . '/h5/#';
        return [
            VerificationItem::IDENTITY()->getValue()      => [
                'type' => 'client',
                'jump' => json_encode(array_merge($extraParams, [
                    'path'         => '/auth/ocr_auth_center',
                    'isCheck'      => false,
                    'isFinishPage' => false,
                ]), JSON_UNESCAPED_UNICODE),
                'link' => '',
            ],
            VerificationItem::BASIC()->getValue()         => [
                'type' => 'h5',
                'jump' => json_encode(array_merge($extraParams, [
                    'path'         => '/auth/h5/webview',
                    'url'          => $path . '/basicInfo/1',
                    'isFinishPage' => false,
                ]), JSON_UNESCAPED_UNICODE),
                'link' => $path . '/basicInfo/1',
            ],
            VerificationItem::WORK()->getValue()          => [
                'type' => 'h5',
                'jump' => json_encode(array_merge($extraParams, [
                    'path'         => '/auth/h5/webview',
                    'url'          => $path . '/workInfo/1',
                    'isFinishPage' => false,
                ]), JSON_UNESCAPED_UNICODE),
                'link' => $path . '/workInfo/1',
            ],
            VerificationItem::ADDRESS()->getValue()       => [
                'type' => 'client',
                'jump' => json_encode(array_merge($extraParams, [
                    'path'         => '/auth/address_proof',
                    'isCheck'      => false,
                    'isFinishPage' => false,
                ]), JSON_UNESCAPED_UNICODE),
                'link' => '',
            ],
            VerificationItem::CONTACT()->getValue()       => [
                'type' => 'client',
                'jump' => json_encode(array_merge($extraParams, [
                    'path'         => '/auth/contact',
                    'isCheck'      => false,
                    'isFinishPage' => false,
                ]), JSON_UNESCAPED_UNICODE),
                'link' => '',
            ],
            VerificationItem::LANGUAGE()->getValue() => [
                'type' => 'h5',
                'jump' => json_encode(array_merge($extraParams, [
                    'path'         => '/h5/webview',
                    'url'          => $path . '/problemAuth',
                    'isFinishPage' => false,
                ]), JSON_UNESCAPED_UNICODE),
                'link' => $path . '/problemAuth',
            ],
            VerificationItem::FACE_COMPARE()->getValue()       => [
                'type' => 'client',
                'jump' => json_encode(array_merge($extraParams, [
                    'path'         => '/auth/liveness',
                    'isCheck'      => false,
                    'isFinishPage' => false,
                ]), JSON_UNESCAPED_UNICODE),
                'link' => '',
            ],
        ];
    }

    /**
     * 获取下一项认证
     * @param VerificationItem $currentItem
     * @param string $appVersion
     * @return VerificationItem
     */
    public function getNextVerificationItem(VerificationItem $currentItem, string $appVersion): VerificationItem
    {
        $verificationProcess = $this->getVerificationProcess();

        $currentStep = array_search($currentItem, $verificationProcess);

        if (!isset($verificationProcess[$currentStep + 1])) {
            //老用户复借
            if ($this->user->customer_type == 1) {
                $faceCompareStatus = $this->getVerificationStatus($appVersion)[VerificationItem::FACE_COMPARE()->getValue()];
                if ($faceCompareStatus) {
                    return VerificationItem::END_ITEM();
                } else {
                    return VerificationItem::FACE_COMPARE();
                }
            } else {
                return VerificationItem::END_ITEM();
            }
        }

        /**
         * @var VerificationItem $nextItem
         */
        $nextItem = $nextItem ?? $verificationProcess[$currentStep + 1];
        $nextItemStatus = $this->getVerificationStatus($appVersion)[$nextItem->getValue()];

        if (!$nextItemStatus) {
            return $nextItem;
        } else {
            return $this->getNextVerificationItem($nextItem, $appVersion);
        }
    }

    public function getNextVerificationItemPath(VerificationItem $currentItem, string $host = '', array $clientInfo = []): array
    {
        $appVersion = $clientInfo['appVersion'] ?? '';
        $packageName = $clientInfo['packageName'] ?? '';
        $appMarket = $clientInfo['appMarket'] ?? '';

        $nextItem = $this->getNextVerificationItem($currentItem, $appVersion);
        $verificationProcess = $this->getVerificationProcess();
        if ($nextItem->equals(VerificationItem::END_ITEM())) {
            //认证流程结束，进入下单页
            $url = $host . '/h5/#/applyLoan';
            return [
                'type' => 'h5',
                'jump' => json_encode([
                    'path'         => '/auth/h5/webview',
                    'url'          => $url,
                    'isFinishPage' => false,
                ], JSON_UNESCAPED_UNICODE),
                'link' => $url,
            ];
        }

        $extraParams['totalNum'] = count($verificationProcess);
        $extraParams['currentPosition'] = array_search($nextItem, $verificationProcess);
        $path = $this->getVerificationPath($extraParams, $host)[$nextItem->getValue()];

        return $path;
    }

    public function checkBeforeVerificationItem(VerificationItem $currentItem, array $clientInfo = []): bool
    {
        $verificationProcess = $this->getVerificationProcess();
        //第一个认证项
        if ($currentItem->equals(VerificationItem::START_ITEM())) {
            return true;
        }
        $currentStep = array_search($currentItem, $verificationProcess);
        //特殊逻辑，兼容旧版本跳过地址认证
        $appVersion = $clientInfo['appVersion'] ?? '1.0.0';
        //第一个认证项
        if (!isset($verificationProcess[$currentStep - 1])) {
            return true;
        }
        /**
         * @var VerificationItem $beforeItem
         */
        $beforeItem = $verificationProcess[$currentStep - 1];
        $beforeItemStatus = $this->getVerificationStatus($appVersion)[$beforeItem->getValue()];

        return $beforeItemStatus;
    }
}