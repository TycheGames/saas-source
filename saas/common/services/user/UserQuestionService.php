<?php


namespace common\services\user;


use Carbon\Carbon;
use common\models\ClientInfoLog;
use common\models\question\QuestionList;
use common\models\question\UserQuestionVerification;
use common\models\user\LoanPerson;
use common\models\user\UserVerification;
use common\models\user\UserVerificationLog;
use common\services\BaseService;
use common\services\FileStorageService;
use frontend\models\user\UserQuestionForm;
use Yii;

class UserQuestionService extends BaseService implements IThirdDataService
{
    private $assignmentNum = 3;

    /**
     * 检查数据是否过期，true:过期 false:未过期
     * @param Carbon $updateTime
     * @return bool
     */
    public function checkDataExpired(Carbon $updateTime): bool
    {
//        return $updateTime->floatDiffInMonths(Carbon::now()) > 3;
        return false;
    }

    public function getRandomQuestion(int $userID, array $params): bool
    {
        $user = LoanPerson::findById($userID);
        if (empty($user)) {
            $this->setError('System has error, please try again later');
            return false;
        }

        $questions = QuestionList::find()
            ->where(['is_used' => 1])
            ->all();
        if (count($questions) < $this->assignmentNum) {
            $this->setError('System has error, please try again later');
            return false;
        }

        $fileStorageService = new FileStorageService(false);
        $randKeys = array_rand($questions, $this->assignmentNum);
        $assignmentQuestions = [];
        $answerStr = '';
        $questionIdStr = '';
        foreach ($randKeys as $key) {
            /**
             * @var QuestionList $question
             */
            $question = $questions[$key];
            $option = json_decode($question->question_option, true);
            $imgPath = empty($question->question_img) ? '' : $fileStorageService->getSignedUrl($question->question_img);

            $answerStr .= trim($question->answer) . ',';
            $questionIdStr .= trim($question->id) . ',';

            array_push($assignmentQuestions, [
                'questionContent' => $question->question_content,
                'answerList'      => $option,
                'img'             => $imgPath,
                'id'              => $question->id,
            ]);
        }

        $userQuestionModel = new UserQuestionVerification();
        $userQuestionModel->user_id = $userID;
        $userQuestionModel->merchant_id = $user->merchant_id;
        $userQuestionModel->questions = $questionIdStr;
        $userQuestionModel->answers = $answerStr;
        $userQuestionModel->question_num = $this->assignmentNum;
        $userQuestionModel->data_status = UserQuestionVerification::STATUS_INIT;
        if (!$userQuestionModel->save()) {
            $this->setError('System has error, please try again later');
            return false;
        }

        $result = [
            'inPageTime' => time(),
            'list'       => $assignmentQuestions,
            'paperId'    => $userQuestionModel->id,
        ];

        $this->setResult($result);

        ClientInfoLog::addLog($userID, ClientInfoLog::EVENT_LANGUAGE, $userQuestionModel->id, $params);

        return true;
    }

    public function submitExaminationPaper(UserQuestionForm $form, int $userID): bool
    {
        $user = LoanPerson::findById($userID);
        if (empty($user)) {
            $this->setError('System has error, please try again later');
            return false;
        }

        /**
         * @var UserQuestionVerification $userQuestion
         */
        $userQuestion = UserQuestionVerification::find()
            ->where(['id' => $form->paperId])
            ->andWhere(['user_id' => $userID])
            ->andWhere(['data_status' => UserQuestionVerification::STATUS_INIT])
            ->one();

        if (!$userQuestion) {
            $this->setError('Error!!!');
            return false;
        }

        $questionIds = substr($userQuestion->questions, 0, strlen($userQuestion->questions) - 1);
        $questionAns = substr($userQuestion->answers, 0, strlen($userQuestion->answers) - 1);
        $standardAnswers = array_combine(explode(',', $questionIds), explode(',', $questionAns));

        $correctNum = 0;
        foreach ($form->list as $item) {
            if ($standardAnswers[$item['id']] == $item['label']) {
                $correctNum++;
                Yii::info(['user_id' => $userID, 'questionID' => $item['id'], 'standardAnswer' => $standardAnswers[$item['id']], 'userAnswer' => $item['label'], 'status' => 'success'], 'question_info');
            } else {
                Yii::info(['user_id' => $userID, 'questionID' => $item['id'], 'standardAnswer' => $standardAnswers[$item['id']], 'userAnswer' => $item['label'], 'status' => 'error'], 'question_info');
            }
        }
        $userQuestion->user_answers = json_encode($form->list, JSON_UNESCAPED_UNICODE);
        $userQuestion->correct_num = $correctNum;
        $userQuestion->data_status = UserQuestionVerification::STATUS_SUBMIT;
        $userQuestion->enter_time = $form->inPageTime;
        $userQuestion->submit_time = $form->outPageTime;

        if ($userQuestion->save()) {
            $verification = $user->userVerification;
            $verification->verificationUpdate(UserVerification::TYPE_LANGUAGE, UserVerificationLog::STATUS_VERIFY_SUCCESS);
            return true;
        }

        $this->setError('System has error, please try again later');
        return false;
    }
}