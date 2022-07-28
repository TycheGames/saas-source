<?php

namespace common\models\user;

use Yii;
use MongoDB\BSON\ObjectId;
use yii\behaviors\TimestampBehavior;
use yii\mongodb\ActiveRecord;

/**
 * This is the model class for collection "user_mobile_photos".
 *
 * @property ObjectId|string $_id
 * @property mixed $user_id
 * @property mixed $created_at
 * @property mixed $updated_at
 */
class MgUserMobilePhotos extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function collectionName()
    {
        return 'user_mobile_photos';
    }

    public static function getDb()
    {
        return Yii::$app->get('mongodb');
    }

    /**
     * {@inheritdoc}
     */
    public function attributes()
    {
        return [
            '_id',
            'user_id',
            'merchant_id',
            'date',
            'AlbumFileCrawlTime',
            'AlbumFileLastModifiedTime',
            'DateTime',
            'DateTimeDigitized',
            'DateTimeOriginal',
            'GPSAltitude',
            'GPSAltitudeRef',
            'GPSDateStamp',
            'GPSLatitude',
            'GPSLatitudeRef',
            'GPSLongitude',
            'GPSLongitudeRef',
            'GPSProcessingMethod',
            'GPSTimeStamp',
            'GPSVersionID',
            'ISOSpeedRatings',
            'ImageLength',
            'ImageWidth',
            'Make',
            'Model',
            'Orientation',
            'PixelXDimension',
            'PixelYDimension',
            'created_at',
            'updated_at',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'merchant_id', 'date','AlbumFileCrawlTime', 'AlbumFileLastModifiedTime', 'DateTime', 'DateTimeDigitized', 'DateTimeOriginal', 'GPSAltitude', 'GPSAltitudeRef',
              'GPSDateStamp', 'GPSLatitude', 'GPSLatitudeRef', 'GPSLongitude', 'GPSLongitudeRef', 'GPSProcessingMethod', 'GPSTimeStamp', 'GPSVersionID', 'ISOSpeedRatings',
              'ImageLength', 'ImageWidth', 'Make', 'Model', 'Orientation', 'PixelXDimension', 'PixelYDimension', 'created_at', 'updated_at'], 'safe']
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }
}
