<?php


namespace console\controllers;


use Carbon\Carbon;
use common\helpers\RedisQueue;
use common\models\user\UserCreditReportFrLiveness;
use common\models\user\UserCreditReportFrVerify;
use common\models\user\UserCreditReportOcrAad;
use common\models\user\UserCreditReportOcrPan;
use common\services\FileStorageService;
use yii\console\ExitCode;

class ThirdDataController extends BaseController
{
    public function actionMigrateFile($type)
    {
        if (!$this->lock()) {
            $this->printMessage('脚本MigrateFile 已经运行中,关闭脚本,参数：' . $type);
            return ExitCode::UNSPECIFIED_ERROR;
        }
        $this->printMessage("脚本MigrateFile，开始执行：" . $type);

        $runTime = time();
        $service = new FileStorageService();
        switch ($type) {
            case 'liveness':
                $query = UserCreditReportFrLiveness::find()
                    ->orderBy(['id' => SORT_ASC])
                    ->limit(1000);
                $cloneQuery = clone $query;
                $maxId = RedisQueue::get(['key' => 'fix_script:liveness']);
                $maxId = intval($maxId);
                $records = $cloneQuery->andWhere(['>', 'id', $maxId])->all();
                while ($records) {
                    if (time() - $runTime > 300) {
                        $this->printMessage('运行满5分钟，关闭当前脚本');
                        exit;
                    }
                    foreach ($records as $record) {
                        /**
                         * @var UserCreditReportFrLiveness $record
                         */
                        try {
                            if (!empty($record->img_fr_path) && !$this->isS3Path($record->img_fr_path)) {
                                $record->img_fr_path = $service->s3Migrate($record->img_fr_path, $service->getSignedUrl($record->img_fr_path));
                            }
                            if (!empty($record->data_fr_path) && !$this->isS3Path($record->data_fr_path)) {
                                $record->data_fr_path = $service->s3Migrate($record->data_fr_path, $service->getSignedUrl($record->data_fr_path));
                            }
                            $record->detachBehaviors();
                            if (!$record->save()) {
                                $this->printMessage("类型：UserCreditReportFrLiveness，不能修复的ID：{$maxId}");
                            }
                        } catch (\Exception $exception) {
                            $this->printMessage("类型：UserCreditReportFrLiveness，不能修复的ID：{$maxId}");
                        }
                        $maxId = $record->id;
                        RedisQueue::set(['expire'=>2073600,'key'=>'fix_script:liveness','value'=> $maxId]);
                    }
                    $cloneQuery = clone $query;
                    $records = $cloneQuery->andWhere(['>', 'id', $maxId])->all();
                }
                break;
            case 'verify':
                $query = UserCreditReportFrVerify::find()
                    ->orderBy(['id' => SORT_ASC])
                    ->limit(1000);
                $cloneQuery = clone $query;
                $maxId = RedisQueue::get(['key' => 'fix_script:verify']);
                $maxId = intval($maxId);
                $records = $cloneQuery->andWhere(['>', 'id', $maxId])->all();
                while ($records) {
                    if (time() - $runTime > 300) {
                        $this->printMessage('运行满5分钟，关闭当前脚本');
                        exit;
                    }
                    foreach ($records as $record) {
                        /**
                         * @var UserCreditReportFrVerify $record
                         */
                        try {
                            if (!empty($record->img1_path) && !$this->isS3Path($record->img1_path)) {
                                $record->img1_path = $service->s3Migrate($record->img1_path, $service->getSignedUrl($record->img1_path));
                            }
                            if (!empty($record->img2_path) && !$this->isS3Path($record->img2_path)) {
                                $record->img2_path = $service->s3Migrate($record->img2_path, $service->getSignedUrl($record->img2_path));
                            }
                            $record->detachBehaviors();
                            if (!$record->save()) {
                                $this->printMessage("类型：UserCreditReportFrVerify，不能修复的ID：{$maxId}");
                            }
                        } catch (\Exception $exception) {
                            $this->printMessage("类型：UserCreditReportFrVerify，不能修复的ID：{$maxId}");
                        }
                        $maxId = $record->id;
                        RedisQueue::set(['expire'=>2073600,'key'=>'fix_script:verify','value'=> $maxId]);
                    }
                    $cloneQuery = clone $query;
                    $records = $cloneQuery->andWhere(['>', 'id', $maxId])->all();
                }
                break;
            case 'pan':
                $query = UserCreditReportOcrPan::find()
                    ->orderBy(['id' => SORT_ASC])
                    ->limit(1000);
                $cloneQuery = clone $query;
                $maxId = RedisQueue::get(['key' => 'fix_script:pan']);
                $maxId = intval($maxId);
                $records = $cloneQuery->andWhere(['>', 'id', $maxId])->all();
                while ($records) {
                    if (time() - $runTime > 300) {
                        $this->printMessage('运行满5分钟，关闭当前脚本');
                        exit;
                    }
                    foreach ($records as $record) {
                        /**
                         * @var UserCreditReportOcrPan $record
                         */
                        try {
                            if (!empty($record->img_front_path) && !$this->isS3Path($record->img_front_path)) {
                                $record->img_front_path = $service->s3Migrate($record->img_front_path, $service->getSignedUrl($record->img_front_path));
                            }
                            if (!empty($record->img_back_path) && !$this->isS3Path($record->img_back_path)) {
                                $record->img_back_path = $service->s3Migrate($record->img_back_path, $service->getSignedUrl($record->img_back_path));
                            }
                            $record->detachBehaviors();
                            if (!$record->save()) {
                                $this->printMessage("类型：UserCreditReportOcrPan，不能修复的ID：{$maxId}");
                            }
                        } catch (\Exception $exception) {
                            $this->printMessage("类型：UserCreditReportOcrPan，不能修复的ID：{$maxId}");
                        }
                        $maxId = $record->id;
                        RedisQueue::set(['expire'=>2073600,'key'=>'fix_script:pan','value'=> $maxId]);
                    }
                    $cloneQuery = clone $query;
                    $records = $cloneQuery->andWhere(['>', 'id', $maxId])->all();
                }
                break;
            case 'aad':
                $query = UserCreditReportOcrAad::find()
                    ->orderBy(['id' => SORT_ASC])
                    ->limit(1000);
                $cloneQuery = clone $query;
                $maxId = RedisQueue::get(['key' => 'fix_script:aad']);
                $maxId = intval($maxId);
                $records = $cloneQuery->andWhere(['>', 'id', $maxId])->all();
                while ($records) {
                    if (time() - $runTime > 300) {
                        $this->printMessage('运行满5分钟，关闭当前脚本');
                        exit;
                    }
                    foreach ($records as $record) {
                        /**
                         * @var UserCreditReportOcrAad $record
                         */
                        try {
                            if (!empty($record->img_front_path) && !$this->isS3Path($record->img_front_path)) {
                                $record->img_front_path = $service->s3Migrate($record->img_front_path, $service->getSignedUrl($record->img_front_path));
                            }
                            if (!empty($record->img_back_path) && !$this->isS3Path($record->img_back_path)) {
                                $record->img_back_path = $service->s3Migrate($record->img_back_path, $service->getSignedUrl($record->img_back_path));
                            }
                            if (!empty($record->check_data_f_path) && !$this->isS3Path($record->check_data_f_path)) {
                                $record->check_data_f_path = $service->s3Migrate($record->check_data_f_path, $service->getSignedUrl($record->check_data_f_path));
                            }
                            if (!empty($record->check_data_z_path) && !$this->isS3Path($record->check_data_z_path)) {
                                $record->check_data_z_path = $service->s3Migrate($record->check_data_z_path, $service->getSignedUrl($record->check_data_z_path));
                            }
                            if (!empty($record->img_front_mask_path) && !$this->isS3Path($record->img_front_mask_path)) {
                                $record->img_front_mask_path = $service->s3Migrate($record->img_front_mask_path, $service->getSignedUrl($record->img_front_mask_path));
                            }
                            if (!empty($record->img_back_mask_path) && !$this->isS3Path($record->img_back_mask_path)) {
                                $record->img_back_mask_path = $service->s3Migrate($record->img_back_mask_path, $service->getSignedUrl($record->img_back_mask_path));
                            }

                            $record->detachBehaviors();
                            if (!$record->save()) {
                                $this->printMessage("类型：UserCreditReportOcrAad，不能修复的ID：{$maxId}");
                            }
                        } catch (\Exception $exception) {
                            $this->printMessage("类型：UserCreditReportOcrAad，不能修复的ID：{$maxId}");
                        }
                        $maxId = $record->id;
                        RedisQueue::set(['expire'=>2073600,'key'=>'fix_script:aad','value'=> $maxId]);
                    }
                    $cloneQuery = clone $query;
                    $records = $cloneQuery->andWhere(['>', 'id', $maxId])->all();
                }
                break;
            default:
                $this->printMessage("脚本MigrateFile，参数错误");
                return false;
        }
        return ExitCode::OK;
    }

    private function isS3Path(string $path)
    {
        return strpos($path,'aws-s3') !== false || strpos($path,'oss-migrate') !== false;
    }
}