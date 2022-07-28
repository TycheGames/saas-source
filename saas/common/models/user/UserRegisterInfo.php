<?php

namespace common\models\user;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;


/**
 * Class UserRegisterInfo
 * @package common\models\user
 *
 * @property int user_id
 * @property string clientType
 * @property string osVersion
 * @property string appVersion
 * @property string deviceName
 * @property string appMarket
 * @property string deviceId
 * @property string did
 * @property string date
 * @property string headers
 * @property string media_source
 * @property string apps_flyer_uid appsflyer的uid
 * @property string af_status 是自然量还是非自然量 Organic, Non-organic
 */
class UserRegisterInfo extends ActiveRecord
{

    const PLATFORM_IOS = 'ios';
    const PLATFORM_ANDROID = 'android';

    const AF_STATUS_O = 'Organic'; //自然量
    const AF_STATUS_NO = 'Non-organic'; //非自然量


    public static function tableName()
    {
        return '{{%user_register_info}}';
    }

    public static function getDb()
    {
        return Yii::$app->get('db');
    }
    

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * 获取appMarket列表
     * @return array
     */
    public static function getChannelList()
    {
        $appMarkets = UserRegisterInfo::find()->select(['appMarket'])
            ->distinct(['appMarket'])->asArray()->all();
        if(!empty($appMarkets))
        {
            $appMarkets = ArrayHelper::getColumn($appMarkets,'appMarket');
        }else{
            $appMarkets = [];
        }

        return $appMarkets;

    }


    /**
     * appMarket筛选列表
     * @return array
     */
    public static function getChannelSearchList()
    {
        $cacheKey = 'channel_search_list';
        if(Yii::$app->cache->get($cacheKey))
        {
            return json_decode(Yii::$app->cache->get($cacheKey), true);
        }else{
            $channelList = [];
            $appMarkets = self::getChannelList();
            foreach($appMarkets as $v)
            {
                $channelList[$v] = $v;
            }
            Yii::$app->cache->set($cacheKey, json_encode($channelList, JSON_UNESCAPED_UNICODE), 300);
            return $channelList;
        }
    }


    /**
     * 获取media_source列表
     * @return array
     */
    public static function getMediaSourceList()
    {
        $mediaSources = UserRegisterInfo::find()->select(['media_source'])
            ->distinct(['media_source'])->asArray()->all();
        if(!empty($mediaSources))
        {
            $mediaSources = ArrayHelper::getColumn($mediaSources,'media_source');
        }else{
            $mediaSources = [];
        }

        return $mediaSources;

    }

    /**
     * 渠道media_source筛选列表
     * @return array
     */
    public static function getMediaSourceSearchList()
    {
        $cacheKey = 'media_source_search_list';
        if(Yii::$app->cache->get($cacheKey))
        {
            return json_decode(Yii::$app->cache->get($cacheKey), true);
        }else{
            $mediaSourceList = [];
            $mediaSources = self::getMediaSourceList();
            foreach($mediaSources as $v)
            {
                $mediaSourceList[$v] = $v;
            }
            Yii::$app->cache->set($cacheKey, json_encode($mediaSourceList, JSON_UNESCAPED_UNICODE), 3000);
            return $mediaSourceList;
        }
    }
}