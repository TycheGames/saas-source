<?php


namespace common\models\enum;


use MyCLabs\Enum\Enum;

/**
 * Class Industry
 * @package common\models\enum
 *
 * @method static Industry IT()
 * @method static Industry DRIVER()
 * @method static Industry WAITER()
 * @method static Industry FARMER()
 * @method static Industry WORKER()
 *
 * @method static Industry CUSTOMER_SERVICE()
 * @method static Industry ACCOUNTING()
 * @method static Industry TEACHER()
 * @method static Industry SHIPPING_COURIER()
 * @method static Industry SELF_EMPLOYED()
 *
 * @method static Industry FINANCIAL_STAFF()
 * @method static Industry ADVERTISEMENT()
 * @method static Industry REPORTER()
 * @method static Industry AIR_HOSTESS()
 * @method static Industry LAWYER()
 *
 * @method static Industry SOLDIER()
 * @method static Industry POLICEMAN()
 * @method static Industry SAILOR()
 * @method static Industry PART_TIME()
 * @method static Industry ENTREPRENEUR()
 *
 * @method static Industry OTHERS()
 * @method static Industry EDITOR()
 */
class Industry extends Enum
{
    use TCommon;

    private const IT = 1;
    private const DRIVER = 2;
    private const WAITER = 3;
    private const FARMER = 4;
    private const WORKER = 5;
    private const CUSTOMER_SERVICE = 6;
    private const ACCOUNTING = 7;
    private const TEACHER = 8;
    private const SHIPPING_COURIER = 9;
    private const SELF_EMPLOYED = 10;
    private const FINANCIAL_STAFF = 11;
    private const ADVERTISEMENT = 12;
    private const REPORTER = 13;
    private const EDITOR = 14;
    private const AIR_HOSTESS = 15;
    private const LAWYER = 16;
    private const SOLDIER = 17;
    private const POLICEMAN = 18;
    private const SAILOR = 19;
    private const PART_TIME = 20;
    private const ENTREPRENEUR = 21;
    private const OTHERS = 22;

    public static $map = [
        self::IT               => 'IT Staff',
        self::DRIVER           => 'Driver',
        self::WAITER           => 'Waiter/Waitress',
        self::FARMER           => 'Farmer',
        self::WORKER           => 'Worker',
        self::CUSTOMER_SERVICE => 'Customer Service',
        self::ACCOUNTING       => 'Accounting',
        self::TEACHER          => 'Teacher',
        self::SHIPPING_COURIER => 'Shipping courier',
        self::SELF_EMPLOYED    => 'Self-employed Businessman',
        self::FINANCIAL_STAFF  => 'Financial Staff',
        self::ADVERTISEMENT    => 'Advertisement',
        self::REPORTER         => 'Reporter',
        self::EDITOR           => 'Editor',
        self::AIR_HOSTESS      => 'Air hostess',
        self::LAWYER           => 'Lawyer',
        self::SOLDIER          => 'Soldier',
        self::POLICEMAN        => 'Policeman',
        self::SAILOR           => 'Sailor',
        self::PART_TIME        => 'Part-time Job',
        self::ENTREPRENEUR     => 'Entrepreneur',
        self::OTHERS           => 'Others',
    ];
}