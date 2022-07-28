<?php

namespace backend\controllers;


use backend\models\search\NewRuleSearch;
use backend\models\search\RuleVersionSearch;
use common\models\order\UserLoanOrder;
use common\models\risk\RiskRules;
use common\models\risk\RuleVersion;
use common\services\order\OrderExtraService;
use common\services\risk\RiskDataDemoService;
use common\services\risk\RiskDataVerificationService;
use common\services\risk\RiskTreeService;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

class DecisionTreeController extends BaseController{

    public function beforeAction($action)
    {
        if(parent::beforeAction($action))
        {
            if(Yii::$app->user->identity->merchant_id){
                throw new ForbiddenHttpException(Yii::T('common', 'Insufficient permissions'));
            }
            return true;
        }else{
            return false;
        }
    }
    /**
     * @name  风控管理-版本决策树管理-版本配置
     * @return string
     */
    public function actionVersionList(){
        $ruleVersionModel = new RuleVersion();
        $searchModel = new RuleVersionSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $params = Yii::$app->request->post();

        if ($ruleVersionModel->load($params)) {
            if ($ruleVersionModel->validate() && !RuleVersion::existsVersion($params['RuleVersion']['version'])) {
                $ruleVersionModel->is_default = 0;

                if (!$ruleVersionModel->save() || !RiskRules::copyVersionRules($ruleVersionModel->version, $ruleVersionModel->version_base_by)) {
                    return $this->render('version-list',[
                        'searchModel' => $searchModel,
                        'dataProvider' => $dataProvider,
                        'ruleVersionModel' => $ruleVersionModel,
                        'copy_result' => '复制版本失败',
                    ]);
                }

                RuleVersion::clearVersionListCache();
            }
        }

        return $this->render('version-list',[
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'ruleVersionModel' => $ruleVersionModel,
        ]);
    }

    /**
     * @name 风控管理-版本决策树管理-设置默认版本
     * @param $version
     * @return bool
     */
    public function actionSetDefault($version){
        $rule_version = RuleVersion::findOne(['version' => $version]);
        if (!$rule_version) {
            return false;
        }

        $rule_version->is_default = 1;
        if (!$rule_version->save()) {
            return false;
        }

        $tmp_rule_version = RuleVersion::find()->where(['is_default' => 1])->andWhere(['!=', 'version', $version])->one();
        if ($tmp_rule_version) {
            $tmp_rule_version->is_default = 0;
            if (!$tmp_rule_version->save()) {
                $rule_version->is_default = 0;
                $rule_version->save();
                return false;
            }
        }

        return true;
    }

    /**
     * @name 风控管理-版本决策树管理-设置灰度版本
     * @param $version
     * @return bool
     */
    public function actionSetGray($version){
        $rule_version = RuleVersion::findOne(['version' => $version]);
        if (!$rule_version) {
            return false;
        }

        $rule_version->is_gray = 1;
        if (!$rule_version->save()) {
            return false;
        }

        $tmp_rule_version = RuleVersion::find()->where(['is_gray' => 1])->andWhere(['!=', 'version', $version])->one();
        if ($tmp_rule_version) {
            $tmp_rule_version->is_gray = 0;
            if (!$tmp_rule_version->save()) {
                $rule_version->is_gray = 0;
                $rule_version->save();
                return false;
            }
        }

        return true;
    }

    /**
     * @name 风控管理-版本决策树管理-特征配置
     * @return string
     */
    public function actionRuleList(){
        $searchModel = new NewRuleSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->get());
        return $this->render('rule-list',[
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ]);
    }

    /**
     * @name 决策树管理 -新建特征
     * @return string|\yii\web\Response
     */
    public function actionRuleAdd(){
        $ruleModel = new RiskRules();
        if(Yii::$app->request->isPost)
        {
            if($ruleModel->load(Yii::$app->request->post()) && $ruleModel->validate())
            {
                if($ruleModel->status == RiskRules::STATUS_OK){
                    $data = new RiskDataVerificationService();
                    $riskTree = new RiskTreeService($data);
                    $riskTree->setRuleVersion($ruleModel->version);
                    if(RiskRules::TYPE_BASE == $ruleModel->type)
                    {
                        $riskTree->riskData->calcBaseRuleValue($ruleModel);
                    }else{
                        $riskTree->calcExpValue($ruleModel->result);
                    }
                }
                if($ruleModel->save()){
                    if(RiskRules::TYPE_BASE == $ruleModel->type)
                    {
                        return $this->redirectMessage('success',self::MSG_SUCCESS,
                            Url::toRoute(['decision-tree/rule-list']));
                    }else{
                        return $this->redirectMessage('success',self::MSG_SUCCESS,
                            Url::toRoute(['decision-tree/rule-edit','code' =>$ruleModel->code ,'alias' => $ruleModel->alias, 'version' => $ruleModel->version, ]));
                    }
                }else{
                    return $this->redirectMessage($ruleModel->getFirstError(1) . 2,self::MSG_ERROR);

                }
            }else{
                return $this->redirectMessage($ruleModel->getFirstError(1) . 1,self::MSG_ERROR);
            }
        }

        return $this->render('rule-add', [
            'ruleModel' => $ruleModel,
        ]);
    }

    /**
     * @name 决策树管理-编辑规则
     * @param $code
     * @param $alias
     * @return string
     */
    public function actionRuleEdit($code, $alias, $version)
    {
        $ruleModel = new RiskRules();
        $ruleModel->code = $code;
        $ruleModel->alias = $alias;
        $ruleModel->version = $version;
        $query = RiskRules::find()->orderBy(['order'=> SORT_ASC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $query->andFilterWhere(['code' => $code, 'version' => $version]);
        if(Yii::$app->request->isPost)
        {

            if($ruleModel->load(Yii::$app->request->post()) && $ruleModel->validate())
            {
                if($ruleModel->status == RiskRules::STATUS_OK){
                    $data = new RiskDataVerificationService();
                    $riskTree = new RiskTreeService($data);
                    $riskTree->setRuleVersion($ruleModel->version);
                    if(RiskRules::TYPE_BASE == $ruleModel->type)
                    {
                        $riskTree->riskData->calcBaseRuleValue($ruleModel);
                    }else{
                        $riskTree->calcExpValue($ruleModel->result);
                    }
                }
                if($ruleModel->save()){
                    return $this->redirectMessage('success',self::MSG_SUCCESS);

                }else{
                    return $this->redirectMessage($ruleModel->getFirstError(1) . 2,self::MSG_ERROR);

                }
            }else{
                return $this->redirectMessage($ruleModel->getFirstError(1) . 1,self::MSG_ERROR);
            }
        }

        return $this->render('rule-edit', [
            'ruleModel' => $ruleModel,
            'dataProvider' => $dataProvider
        ]);
    }


    /**
     * @name 决策树管理-编辑特征
     * @param $id
     * @return string
     */
    public function actionRuleNodeEdit($id)
    {
        $ruleModel = RiskRules::findOne($id);
        if(Yii::$app->request->isPost)
        {
            if($ruleModel->load(Yii::$app->request->post()) && $ruleModel->validate())
            {

                if($ruleModel->status == RiskRules::STATUS_OK){
                    $data = new RiskDataVerificationService();
                    $riskTree = new RiskTreeService($data);
                    $riskTree->setRuleVersion($ruleModel->version);
                    if(RiskRules::TYPE_BASE == $ruleModel->type)
                    {
                        $riskTree->riskData->calcBaseRuleValue($ruleModel);
                    }else{
                        $riskTree->calcExpValue($ruleModel->result);
                    }
                }

                if($ruleModel->save()){
                    return $this->redirectMessage('success',self::MSG_SUCCESS,Url::toRoute('decision-tree/rule-list'));

                }else{
                    return $this->redirectMessage($ruleModel->getFirstError(1) . 2,self::MSG_ERROR);

                }
            }else{
                return $this->redirectMessage($ruleModel->getFirstError(1) . 1,self::MSG_ERROR);
            }
        }

        return $this->render('rule-node-edit', [
            'ruleModel' => $ruleModel,
        ]);
    }

    /**
     * @name 决策树管理 -依赖关系
     * @param $id
     * @param $version
     * @return string
     */
    public function actionViewDependence($id){
        $id = intval($id);
        $rule = RiskRules::findOne($id);
        $tree = [];
        $rule->generateTree($tree, $rule->code);
        $root = [
            'id' => $rule->code,
            "isroot" => true,
        ];
        if(RiskRules::TYPE_BASE == $rule->type)
        {
            $tmpTopic = "方法:{$rule->code}-{$rule->alias}<br/>";
            $tmpTopic .= "&nbsp&nbsp&nbsp&nbsp方法名:check{$rule->result}";
        }else{
            $tmpTopic = "特征:{$rule->code}-{$rule->alias}<br/>";
            $tmpTopic .= "&nbsp&nbsp&nbsp&nbsp表达式:{$rule->guard}";
            $tmpTopic .= "&nbsp结果:{$rule->result}";
        }
        $root['topic'] = $tmpTopic;

        $tree[] = $root;

        return $this->render('dependency-view', [
            'tree' => $tree,
        ]);
    }

    /**
     * @name 决策树管理 -依赖关系
     * @param $id
     * @param $version
     * @return string
     */
    public function actionViewDependenceTree($code, $version){
        $rules = RiskRules::find()->where(['code' => $code, 'version' => $version])->all();
        $tree = [];

        $root = [
            'id' => $code,
            "isroot" => true,
            'topic' => ''
        ];

        $topic = [];
        /**
         * @var RiskRules $rule
         */
        foreach($rules as $rule)
        {
            if(RiskRules::TYPE_BASE == $rule->type)
            {
                $tmpTopic = "方法:{$rule->code}-{$rule->alias}<br/>";
                $tmpTopic .= "&nbsp&nbsp&nbsp&nbsp方法名:check{$rule->result}";
            }else{
                $tmpTopic = "特征:{$rule->code}-{$rule->alias}<br/>";
                $tmpTopic .= "&nbsp&nbsp&nbsp&nbsp表达式:{$rule->guard}";
                $tmpTopic .= "&nbsp结果:{$rule->result}";
            }
            $topic[] = $tmpTopic;
            $rule->generateTree($tree, $rule->code);
        }

        $root['topic'] = implode('<br/>', $topic);


        $tree[] = $root;

        return $this->render('dependency-view', [
            'tree' => $tree,
        ]);
    }


    /**
     * @name 风控节点测试
     * @return string
     */
    public function actionRuleTest()
    {
        return $this->render('rule-test');
    }


    /**
     * @name 风控规则测试
     * @return array
     * @throws \yii\base\Exception
     */
    public function actionRuleTestResult()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $orderId = Yii::$app->request->post('order_id');
        $tree = Yii::$app->request->post('node');
        $version = Yii::$app->request->post('version');

        try{
            //订单维度
            $order = UserLoanOrder::findOne($orderId);
            $orderExtraService = new OrderExtraService($order);
            $orderData = [
                'order'                 => $order,
                'loanPerson'            => $order->loanPerson,
                'userWorkInfo'          => $orderExtraService->getUserWorkInfo(),
                'userBasicInfo'         => $orderExtraService->getUserBasicInfo(),
                'userBankAccount'       => $order->userBankAccount,
                'userContact'           => $orderExtraService->getUserContact(),
                'userAadhaarReport'     => $orderExtraService->getUserOcrAadhaarReport(),
                'userPanReport'         => $orderExtraService->getUserOcrPanReport(),
                'userPanVerifyReport'   => $orderExtraService->getUserVerifyPanReport(),
                'userFrReport'          => $orderExtraService->getUserFrReport(),
                'userFrFrReport'        => $orderExtraService->getUserFrFrReport(),
                'userFrPanReport'       => $orderExtraService->getUserFrPanReport(),
                'userFrCompareReport'   => $orderExtraService->getUserFrCompareReport(),
                'userQuestionReport'    => $orderExtraService->getUserQuestionReport(),

            ];

            $data = new RiskDataDemoService($orderData);
            $riskTree = new RiskTreeService($data);
            $riskTree->setRuleVersion($version);
            $result = $riskTree->exploreNodeValue($tree);
            return [
                'result' => $result,
                'basicNode' => $riskTree->basicNodeValues,
                'allNode' => $riskTree->nodeValueCaches
            ];
        } catch (\Exception $exception) {
            return [
                'code' => -1,
                'lastNode' => $riskTree->lastNode ?? [],
                'message' => $exception->getMessage().$exception->getTraceAsString()
            ];
        }


    }
}
