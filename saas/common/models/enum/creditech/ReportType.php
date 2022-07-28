<?php


namespace common\models\enum\creditech;


use MyCLabs\Enum\Enum;

/**
 * Class CreditechReportType
 * @package common\models\enum
 *
 * @method static ReportType OCR_AADHAAR()
 * @method static ReportType OCR_PAN()
 * @method static ReportType CONTRAST_AADHAAR_PAN()
 * @method static ReportType LIVE_DETECTION()
 * @method static ReportType MULTI_HEAD()
 * @method static ReportType EKYC()
 * @method static ReportType VERIFY_PAN()
 * @method static ReportType VERIFY_FR()
 */
class ReportType extends Enum
{
    private const OCR_AADHAAR = 1;
    private const OCR_PAN = 2;
    private const CONTRAST_AADHAAR_PAN = 3;
    private const LIVE_DETECTION = 4;
    private const MULTI_HEAD = 5;
    private const EKYC = 6;
    private const VERIFY_PAN = 7;
    private const VERIFY_FR = 8;
}