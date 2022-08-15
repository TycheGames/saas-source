<?php
namespace common\models\risk;
use common\models\InfoDevice;
use common\models\InfoOrder;
use common\models\InfoPictureMetadata;
use common\models\InfoUser;
use common\models\RiskOrder;
use yii\base\Model;


/**
 * 风控数据容器
 * Class RiskDataContainer
 * @package common\models\risk
 * @property RiskOrder $order 风控订单类
 * @property InfoUser $infoUser 用户类
 * @property InfoOrder $infoOrder 订单信息类
 * @property InfoDevice $infoDevice 设备本信息类
 * @property InfoPictureMetadata $infoPictureMeta 照片信息类
 */
class RiskDataContainer extends Model {

    public $order,$infoUser,$infoOrder,$infoDevice,$infoPictureMeta;

    /**
     * 字段验证规则
     * @return array
     */
    public function rules(){
        return [
            ['order','required', 'when' => function($model){
                if(!$model->order instanceof RiskOrder){
                    $this->addError('order', '非RiskOrder实例');
                }
            }],
            ['infoUser','required', 'when' => function($model){
                if(!$model->infoUser instanceof InfoUser){
                    $this->addError('infoUser', '非InfoUser实例');
                }
            }],
            ['infoOrder','required', 'when' => function($model){
                if(!$model->infoOrder instanceof InfoOrder){
                    $this->addError('infoOrder', '非InfoOrder实例');
                }
            }],
//            ['infoDevice','required', 'when' => function($model){
//                if(!$model->infoDevice instanceof InfoDevice){
//                    $this->addError('infoDevice', '非InfoDevice实例');
//                }
//            }],
            [
                [
                    'infoPictureMeta', 'infoDevice'
                ],
                'safe'
            ]
        ];
    }

}