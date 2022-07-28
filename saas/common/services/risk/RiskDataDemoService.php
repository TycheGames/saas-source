<?php
namespace common\services\risk;

use callcenter\models\loan_collection\LoanCollectionSuggestionChangeLog;
use Carbon\Carbon;
use common\helpers\CommonHelper;
use common\helpers\Util;
use common\models\ClientInfoLog;
use common\models\enum\CreditReportStatus;
use common\models\order\EsUserLoanOrder;
use common\models\order\UserLoanOrder;
use common\models\order\UserLoanOrderExternal;
use common\models\order\UserLoanOrderRepayment;
use common\models\question\UserQuestionVerification;
use common\models\third_data\ThirdDataShumeng;
use common\models\user\MgUserCallReports;
use common\models\user\MgUserMobilePhotos;
use common\models\user\MgUserMobileSms;
use common\models\user\UserCreditReportCibil;
use common\models\user\UserCreditReportExperian;
use common\models\user\UserCreditReportFrLiveness;
use common\models\user\UserCreditReportFrVerify;
use common\models\user\UserCreditReportOcrAad;
use common\models\user\UserPanCheckLog;
use common\models\user\UserPictureMetadataLog;
use common\models\user\UserRegisterInfo;
use common\models\whiteList\PhoneBrand;
use common\models\risk\RiskDataContainer;
use common\models\user\LoanPerson;
use common\models\user\MgUserInstalledApps;
use common\models\user\MgUserMobileContacts;
use common\models\user\UserContact;
use common\models\user\UserLoginLog;
use common\models\user\UserOverdueContact;
use common\models\whiteList\Pin;
use common\services\third_data\ShuMengService;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * RiskData Demo类
 * Class RiskDataDemoService
 * @package common\services
 * @property  RiskDataContainer $data
 */
class RiskDataDemoService extends RiskDataService
{

    const RL = 2;   # 低
    const RM = 1;   # 中
    const RH = 0;   # 高

    //是否获取第三方数据
    public $isGetData = true;

    # attrs
    protected $data;
    //手机号对应的用户ID
    protected $phoneUserIds = [];
    //手机号对应的用户ID 不区分商户
    protected $phoneAllUserIds = [];
    //手机号对应的用户ID loan
    protected $phoneLoanUserIds = [];
    //aadhaar对应的用户ID
    protected $aadhaarUserIds = [];
    //aadhaar对应的用户ID   loan
    protected $aadhaarOtherUserIds = [];
    //pan对应的用户ID
    protected $panUserIds = [];
    //pan对应的用户ID 不区分商户
    protected $panAllUserIds = [];
    //pan对应的用户ID loan
    protected $panLoanUserIds = [];
    //pan对应的用户紧急联系人
    protected $panUserContacts = [];
    //本平台用户紧急联系人
    protected $userContactsSelf = [];
    //该ip近天数时间内的申请数
    protected $ipInDayOrderApplyCount = [];
    //该ip近天数时间内的申请数 loan
    protected $ipInDayLoanOrderApplyCount = [];
    //该ip近天数时间内的申请数 本平台
    protected $ipInDayOrderApplyCountSelf = [];

    //用户短信数据
    protected $userSms = [];
    //用户短信数据  全平台
    protected $userAllSms = [];
    protected $mobilePhoto = [];

    protected $cibilReport;
    protected $experianReport;
    protected $experian_updated_at;
    protected $shumengReport;

    //外部用户id
    protected $userOtherId;

    //用户手机通讯录
    protected $userContacts = [];


    //提醒还款关键词
    private $repaymentList = [
        'repay',
        'repayment',
        'due',
    ];

    private $lawPoliceList = [
        'law',
        'suit',
        'plaintiff',
        'accuser',
        'prosecutor',
        'indicter',
        'defendant',
        'appellee',
        'indictee',
        'accused',
        'respondent',
        'court',
        'stand trial',
        'confession to justice',
        'police',
        'arrest',
        'list as wanted',
    ];

    private $addressWhiteList = [
        'BHUBANESWAR',
        'INDORE',
        'KOCHI',
        'MANGALURU',
        'MYSORE',
        'THIRUVANANTHAPURAM',
        'VIJAYAWADA',
        'VISAKHAPATNAM',
        'ALLAHABAD',
        'NOIDA',
        'HUBLI-DHARWAD',
        'KANCHEEPURAM',
        'THIRUVALLUR',
        'BHILAI NAGAR',
        'AGARTALA',
        'SHILLONG',
        'SHIMLA',
        'VARANASI',
        'BHIWANDI',
        'TIRUCHIRAPPALLI',
        'NELLORE',
        'TIRUPATI',
        'RAJAHMUNDRY',
        'CUTTACK',
        'ONGOLE',
        'KADAPA',
        'VILUPPURAM',
        'THANJAVUR',
        'VIZIANAGARAM',
        'KOLAR',
        'ICHALKARANJI',
        'TENALI',
        'TUMKUR',
        'BALLARI',
        'BALESHWAR TOWN',
        'BANKURA',
        'KAKINADA',
        'PURI',
        'NAGERCOIL',
        'RAMAGUNDAM',
        'NANDED-WAGHALA',
        'SHIVAMOGGA',
        'KALYAN-DOMBIVALI',
        'TAMLUK',
        'MACHILIPATNAM',
        'ELURU',
        'AMALAPURAM',
        'VASAI-VIRAR',
        'NARASARAOPET',
        'SIDDIPET',
        'MAHBUBNAGAR',
        'MAHESANA',
        'PANVEL',
        'BOKARO STEEL CITY',
        'THENI ALLINAGARAM',
        'OSMANABAD',
        'MOHALI',
        'DAVANAGERE',
        'BATHINDA',
        'VAPI',
        'ROORKEE',
        'WARDHA',
        'PALANPUR',
        'NADIAD',
        'UDUPI',
        'VIRUDHACHALAM',
        'DARJILING',
        'KHARAGPUR',
        'BHUJ',
        'RAAYACHURU',
        'KENDUJHAR',
        'KARWAR',
        'TIRUCHENGODE',
        'AMRELI',
        'HINDUPUR',
        'CHIKKAMAGALURU',
        'GUDUR',
        'BHIMAVARAM',
        'PRODDATUR',
        'PITHAMPUR',
        'RUPNAGAR',
        'SATNA',
        'RAURKELA',
        'PACHORA',
        'ADONI',
        'ITARSI',
        'TADEPALLIGUDEM',
        'BAHARAMPUR',
        'SHIVPURI',
        'POLLACHI',
        'ANJAR',
        'CHIRALA',
        'KAVALI',
        'VINUKONDA',
        'ODDANCHATRAM',
        'NARSINGHGARH',
        'CHILAKALURIPET',
        'NEEMUCH',
        'UDHAGAMANDALAM',
        'MODASA',
        'RAJGARH',
        'LUNAWADA',
        'ARARIA',
        'THIRUVARUR',
        'MEDININAGAR (DALTONGANJ)',
        'SIBSAGAR',
        'KOVVUR',
        'JALANDHAR CANTT.',
        'MARGAO',
        'ASHOK NAGAR',
        'SIVAKASI',
        'NAWABGANJ',
        'MORVI',
        'NEYYATTINKARA',
        'NEYVELI (TS)',
        'GUNTAKAL',
        'SEONI',
        'MURWARA (KATNI)',
        'BYASANAGAR',
        'SRIVILLIPUTHUR',
        'PILANI',
        'RAYACHOTI',
        'SAHJANWA',
        'PADRAUNA',
        'SANTIPUR',
        'MATHABHANGA',
        'PANAJI',
        'SANAWAD',
        'SINGRAULI',
        'DHARMAVARAM',
        'SAUSAR',
        'MANDI',
        'VANIYAMBADI',
        'MADIKERI',
        'BALAGHAT',
        'TANUKU',
        'VADALUR',
        'RAJNANDGAON',
        'SATTENAPALLE',
        'RAMPURHAT',
        'MAHASAMUND',
        'MANDAPETA',
        'RAMACHANDRAPURAM',
        'TIRUVURU',
        'DEESA',
        'SHAHBAD',
        'MADANAPALLE',
        'YAWAL',
        'TIRUR',
        'BAPATLA',
        'JAGGAIAHPET',
        'SIDHI',
        'RAJAPALAYAM',
        'PUTTUR',
        'WADHWAN',
        'WAGHALA',
        'KALYAN',
        'DOMBIVALI',
        'DALTONGANJ',
        'JALANDHAR CANTT',
        'BHILAI',
        'NAGAR',
        'HUBLI',
        'ALLINAGARAM',
        'MURWARA',
        'KATNI',
        'VASAI',
        'SILCHAR',
        'SEDAM',
        'KANDUKUR',
        'MANDLA',
        'REPALLE',
        'NARASAPURAM',
        'SAMALKOT',
        'LALSOT',
        'YELLANDU',
        'NATHAM',
        'KARJAT',
        'MUVATTUPUZHA',
        'PARADIP',
        'NABARANGAPUR',
        'KESHOD',
        'MIRA-BHAYANDAR',
        'MAPUSA',
        'MEHKAR',
        'NABHA',
        'WANAPARTHY',
        'ARSIKERE',
        'PALACOLE',
        'VERAVAL',
        'VYARA',
        'MALKANGIRI',
        'ANKLESHWAR',
        'BARPETA',
        'ARAMBAGH',
        'NAILA JANJGIR',
        'MANDIDEEP',
        'RAJPURA',
        'PANCHLA',
        'WARORA',
        'NANJANGUD',
        'JATANI',
        'CHERTHALA',
        'SUNABEDA',
        'NIDADAVOLE',
        'PUSAD',
        'NAGDA',
        'TADPATRI',
        'MALKAPUR',
        'PEHOWA',
        'RAYAGADA',
        'AFZALPUR',
        'PERUMBAVOOR',
        'KARAIKAL',
        'NAGARKURNOOL',
        'OTTAPPALAM',
        'RISHIKESH',
        'WADI',
        'PANRUTI',
        'RAVER',
        'SENDHWA',
        'TUNI',
        'SHEGAON',
        'SRIKALAHASTI',
        'PORBANDAR',
        'PARLI',
        'MANDVI',
        'MAVELIKKARA',
        'SUPAUL',
        'NILAMBUR',
        'MAHEMDABAD',
        'UCHGAON',
        'MACHERLA',
        'AMBEJOGAI',
        'PONNUR',
        'PEDDAPURAM',
        'MAHAD',
        'PINJORE'
    ];

    private $addressWhiteList2 = [
        'Aurangabad',
        'Bengaluru',
        'Bangalore',
        'Bhopal',
        'Bhubaneswar',
        'Chennai',
        'Coimbatore',
        'Delhi',
        'New Delhi',
        'Hyderabad',
        'Indore',
        'Kochi',
        'Kollam',
        'Kottayam',
        'Mangaluru',
        'Mumbai',
        'Mysore',
        'Nagpur',
        'Nashik',
        'Pondicherry',
        'Pune',
        'Surat',
        'Thiruvananthapuram',
        'Vadodara',
        'Vijayawada',
        'Visakhapatnam',
        'Agra',
        'Allahabad',
        'Faridabad',
        'Gurgaon',
        'Jaipur',
        'Noida',
        'Amravati',
        'Guntur',
        'Hubli-Dharwad',
        'Karimnagar',
        'Kancheepuram',
        'Khammam',
        'Kozhikode',
        'Sangareddy',
        'Salem',
        'Thiruvallur',
        'Warangal',
        'Bhilai Nagar',
        'Gwalior',
        'Jabalpur',
        'Raipur',
        'Thane',
        'Agartala',
        'Ahmedabad',
        'Amritsar',
        'Chandigarh',
        'Dehradun',
        'Hisar',
        'Jodhpur',
        'Kanpur',
        'Kolkata',
        'Lucknow',
        'Ludhiana',
        'Meerut',
        'Rajkot',
        'Shillong',
        'Shimla',
        'Srinagar',
        'Udaipur',
        'Varanasi',
        'Vellore',
        'Madurai',
        'Mathura',
        'Erode',
        'Bhiwandi',
        'Jhansi',
        'Ujjain',
        'Tiruchirappalli',
        'Nellore',
        'Ranchi',
        'Thrissur',
        'Malappuram',
        'Tirupati',
        'Rajahmundry',
        'Aligarh',
        'Tiruppur',
        'Cuttack',
        'Chittoor',
        'Ongole',
        'Kadapa',
        'Anantapur',
        'Ahmednagar',
        'Solapur',
        'Viluppuram',
        'Satara',
        'Bhavnagar',
        'Kurnool',
        'Thanjavur',
        'Belagavi',
        'Vizianagaram',
        'Akola',
        'Kolar',
        'Latur',
        'Ichalkaranji',
        'Tiruvannamalai',
        'Namakkal',
        'Tenali',
        'Sangli',
        'Tumkur',
        'Ballari',
        'Baleshwar Town',
        'Bankura',
        'Kakinada',
        'Puri',
        'Suryapet',
        'Nagercoil',
        'Medak',
        'Ramagundam',
        'Srikakulam',
        'Nanded-Waghala',
        'Navsari',
        'Shivamogga',
        'Jamnagar',
        'Ratlam',
        'Kalyan-Dombivali',
        'Tamluk',
        'Parbhani',
        'Machilipatnam',
        'Eluru',
        'Amalapuram',
        'Vasai-Virar',
        'Narasaraopet',
        'Karur',
        'Anand',
        'Siddipet',
        'Sagar',
        'Mahbubnagar',
        'Mahesana',
        'Panvel',
        'Bharuch',
        'Bokaro Steel City',
        'Nizamabad',
        'Theni Allinagaram',
        'Osmanabad',
        'Mohali',
        'Davanagere',
        'Valsad',
        'Bathinda',
        'Gandhinagar',
        'Mandya',
        'Vapi',
        'Vijayapura',
        'Nagapattinam',
        'Roorkee',
        'Dhule',
        'Bhiwani',
        'Wardha',
        'Kendrapara',
        'Palanpur',
        'Panchkula',
        'Nadiad',
        'Udupi',
        'Virudhachalam',
        'Darjiling',
        'Ramanathapuram',
        'Kharagpur',
        'Bhuj',
        'Raayachuru',
        'Mandsaur',
        'Kendujhar',
        'Karwar',
        'Tiruchengode',
        'Amreli',
        'Hindupur',
        'Chikkamagaluru',
        'Gudur',
        'Bhimavaram',
        'Proddatur',
        'Sawai Madhopur',
        'Solan',
        'Pithampur',
        'Rupnagar',
        'Satna',
        'Perambalur',
        'Raurkela',
        'Begusarai',
        'Malda',
        'Pachora',
        'Adoni',
        'Itarsi',
        'Tadepalligudem',
        'Baharampur',
        'Sivaganga',
        'Muktsar',
        'Shivpuri',
        'Pollachi',
        'Anjar',
        'Chirala',
        'Faridkot',
        'Shahdol',
        'Kavali',
        'Palghar',
        'Vinukonda',
        'Oddanchatram',
        'Gurdaspur',
        'Narsinghgarh',
        'Chilakaluripet',
        'Neemuch',
        'Udhagamandalam',
        'Sangrur',
        'Modasa',
        'Rajgarh',
        'Tirupathur',
        'Mansa',
        'Lunawada',
        'Araria',
        'Thiruvarur',
        'Medininagar (Daltonganj)',
        'Patan',
        'Sibsagar',
        'Kovvur',
        'Jalandhar Cantt.',
        'Margao',
        'Ranipet',
        'Ashok Nagar',
        'Sivakasi',
        'Nawabganj',
        'Morvi',
        'Neyyattinkara',
        'Raisen',
        'Neyveli (TS)',
        'Guntakal',
        'Seoni',
        'Murwara (Katni)',
        'Byasanagar',
        'Srivilliputhur',
        'Pilani',
        'Kamareddy',
        'Rayachoti',
        'Sahjanwa',
        'Padrauna',
        'Nainital',
        'Vikarabad',
        'Santipur',
        'Mathabhanga',
        'Andhra Pradesh',
        'Panaji',
        'Sanawad',
        'Singrauli',
        'Dharmavaram',
        'Sausar',
        'Mandi',
        'Vaniyambadi',
        'Madikeri',
        'Maharashtra',
        'Shajapur',
        'Balaghat',
        'Tanuku',
        'Vadalur',
        'Rajnandgaon',
        'Sattenapalle',
        'Rampurhat',
        'Mahasamund',
        'Mandapeta',
        'Pratapgarh',
        'Ramachandrapuram',
        'Tiruvuru',
        'Deesa',
        'Shahbad',
        'Madanapalle',
        'Yawal',
        'Tirur',
        'Bapatla',
        'Jaggaiahpet',
        'Sidhi',
        'Rajapalayam',
        'Puttur',
        'Wadhwan',
        'Nanded',
        'Waghala',
        'Kalyan',
        'Dombivali',
        'Baleshwar',
        'Daltonganj',
        'Jalandhar',
        'Jalandhar Cantt',
        'Bhilai',
        'Nagar',
        'Hubli',
        'Dharwad',
        'Theni',
        'Allinagaram',
        'Murwara',
        'Katni',
        'Vasai',
        'Silchar',
        'Sedam',
        'Tamil Nadu',
        'Kandukur',
        'Mandla',
        'Repalle',
        'Kasaragod',
        'Narasapuram',
        'Samalkot',
        'Lalsot',
        'Yellandu',
        'Natham',
        'Karjat',
        'Muvattupuzha',
        'Paradip',
        'Nabarangapur',
        'Keshod',
        'Mira-Bhayandar',
        'Mapusa',
        'Mehkar',
        'Nabha',
        'Wanaparthy',
        'Arsikere',
        'Palacole',
        'Veraval',
        'Jammu',
        'Vyara',
        'Malkangiri',
        'Ankleshwar',
        'Barpeta',
        'Arambagh',
        'Naila Janjgir',
        'Mandideep',
        'Kapurthala',
        'Rajpura',
        'Panchla',
        'Warora',
        'Nanjangud',
        'Jatani',
        'Cherthala',
        'Sunabeda',
        'Nidadavole',
        'Pusad',
        'Nagda',
        'Tadpatri',
        'Malkapur',
        'Karnataka',
        'Pehowa',
        'Rayagada',
        'Afzalpur',
        'Deoghar',
        'Perumbavoor',
        'Karaikal',
        'Nagarkurnool',
        'Ottappalam',
        'Rishikesh',
        'Wadi',
        'Nirmal',
        'Panruti',
        'Raver',
        'Sendhwa',
        'Tuni',
        'Shegaon',
        'Srikalahasti',
        'Porbandar',
        'Parli',
        'Mandvi',
        'Tarn Taran',
        'Mavelikkara',
        'Supaul',
        'Nilambur',
        'Mahemdabad',
        'Uchgaon',
        'Macherla',
        'Ambejogai',
        'Ponnur',
        'Peddapuram',
        'Mahad',
        'Pinjore'
    ];

    //传销敏感词
    private $pyramidWords = [
        'pyramid scheme',
        'pyramid selling',
        'pyramid sale'
    ];
    //毒品敏感词
    private $drugsWords = [
        'drugs',
        'Marijuana',
        'Exotic pets and animals',
        'Narcotics',
        'scag',
        'Philopon',
        'morphine',
        'morphia',
        'Speical K',
        'ecstacy',
        'cocaine',
        'heroin',
        'opium',
        'meth',
        'hemp'
    ];
    //赌博敏感词
    private $gamblingWords = [
        'gambling',
        'casino',
        'sands',
    ];
    //黑敏感词
    private $blackWords = [
        'black market',
        'black money',
        'money laundering',
        'gang',
        'tax haven',
        'shell companies',
        'offshore shell companies',
        'anti fraud',
        'Swiss bank',
        'parallel economy',
        'corruption',
        'defaulters',
        'Guns',
        'Hacking',
        'Fraud',
        'Data breaching',
        'Scams',
        'Human body parts',
        'Weapon',
        'Bombs',
        'Pirated media',
        'Pirated software',
        'Human trafficking',
        'Racketeering'
    ];

    //用户app列表
    protected $userAppList = [];
    private $loanAppList = [
        '1 MINUTE ME AADHAR LOAN',
        '10MINUTELOAN',
        '360 LOAN',
        '5NANCE',
        'AADHAR PE LOAN',
        'ABCASH',
        'ABHIPAISA',
        'AF LOANS',
        'AFINOZ',
        'AGRI LOAN APPRAISER',
        'AIRLOAN',
        'ANYTIMELOAN',
        'APNAPAISA',
        'ASAP LOAN FINDER',
        'ATD MONEY',
        'ATOME CREDIT',
        'AVAIL',
        'AVAIL FINANCE',
        'B ROBOCASH',
        'BAJAJ FINSERV WALLET',
        'BAJAJFINSERV',
        'BALIKBAYAD OFW AND SEAMAN LOANS',
        'BANK LOAN PROVIDER',
        'BETTR CREDIT',
        'BILLIONCASH',
        'BIZCAPITAL.IN',
        'BRANCH',
        'CAPITAL FIRST LIMITED',
        'CAPZEST',
        'CASH CREDIT',
        'CASH POCKET',
        'CASH+',
        'CASHBEAN',
        'CASHBOWL',
        'CASHBULL',
        'CASHBUS',
        'CASHCLUB',
        'CASHE',
        'CASHIN',
        'CASHINYOU',
        'CASHIYA',
        'CASHKUMAR',
        'CASHMAMA',
        'CASHOUSE',
        'CASHPAPA',
        'CASHPELI',
        'CASHPIE',
        'CASHTAP',
        'CASHTM',
        'CASHTM CASH THRU MOBILE',
        'CASHUP',
        'CHECKPOINT',
        'CRAZYRUPEE',
        'CREDENC',
        'CREDICXO',
        'CREDIFIABLE',
        'CREDIME',
        'CREDITMANTRI',
        'CREDITMATE',
        'CREDITME',
        'CREDITT',
        'CREDY',
        'CUTE MONEY',
        'DEALSOFLOAN',
        'DHANADHAN',
        'DHANI',
        'DIGILEND',
        'EARLYSALARY',
        'EARNWEALTH',
        'EASY LOANS',
        'EASY MONEY',
        'EASYLOAN',
        'ERUPEE',
        'FAIRCENT',
        'FAST CASH',
        'FAST CASH LOAN',
        'FAST RUPEE',
        'FINACARI',
        'FINGERDEV',
        'FINNABLE',
        'FINSERV MARKETS',
        'FINWEGO',
        'FINZY',
        'FLASHCASH',
        'FLASHPAISA',
        'FLEXI MONEY',
        'FLEXILOANS',
        'FLEXSALARY',
        'FREELOAN',
        'FULLERTON',
        'FULLERTON INDIA',
        'FULLERTON INDIA INSTALOAN',
        'FULLERTON INDIA MCONNECT',
        'FULLERTONINDIA',
        'GOLDEN LIGHTNING',
        'GORUPEE',
        'GOTOCASH',
        'HAPPYMONIES',
        'HDB FINANCIAL SERVICES ONTHEGO',
        'HDFC HOME LOANS',
        'HERO FINCORP',
        'HICASH',
        'HOME CREDIT',
        'HOME CREDIT LOAN',
        'HOMECREDITLOAN',
        'HOMEFIRST CUSTOMER PORTAL',
        'I2IFUNDING',
        'ICREDIT',
        'IDFC FIRST LOANS',
        'IEASYLOAN',
        'IGNITIVE APPS',
        'IIFL LOANS',
        'I-LEND',
        'INCRED',
        'INDIABULLS',
        'INDIABULLS DHANI BIZ',
        'INDIABULLS HOME LOANS',
        'INDIALENDS',
        'INDIAMONEYMART',
        'INSTACASH7',
        'INSTAMONEY',
        'INSTANT MUDRA',
        'INSTANT PERSONAL LOAN',
        'INSTANTPERSONALLOAN',
        'INSTAPAISA',
        'IRUPEE',
        'IZWA LOANS',
        'KISSHT',
        'K-KCASH',
        'KRAZYBEE',
        'KREDITBEE',
        'KREDITONE',
        'KREDITZY',
        'KYEPOT',
        'LAZYPAY',
        'LECRED',
        'LEEWAY LOAN',
        'LENDENCLUB',
        'LENDINGADDA',
        'LENDINGKART',
        'LENDINGKART INSTANT BUSINESS LOANS',
        'LENDKARO',
        'LENDTEK',
        'LEYLAND ORCHARD',
        'LIGHTNINGLOAN',
        'LOAN AGENT',
        'LOAN ASSIST',
        'LOAN DOST',
        'LOAN FRAME',
        'LOAN FRAME PARTNER',
        'LOAN GLOBALLY',
        'LOAN NOW',
        'LOAN RAJA',
        'LOAN SHARK',
        'LOAN TIGER',
        'LOANADDA',
        'LOANBABA',
        'LOANBRO',
        'LOANFIT',
        'LOANFLIX',
        'LOANFRONT',
        'LOANHUB',
        'LOANIT',
        'LOANONMIND',
        'LOANQUICK.IN',
        'LOANS CHAP CHAP',
        'LOANS.COM.AU SMART MONEY',
        'LOANSIMPLE',
        'LOANSINGH',
        'LOANSUMO',
        'LOANTAP',
        'LOANU',
        'LOANWIX',
        'LOANX',
        'L-PESA',
        'MAHINDRA FINANCE',
        'MANAPPURAM PERSONAL LOAN',
        'MAX LOAN',
        'MI CREDIT',
        'MOLP2P',
        'MOMO',
        'MONCASH',
        'MONEED',
        'MONEXO',
        'MONEY IN MINUTES',
        'MONEY VIEW',
        'MONEY VIEW LOANS',
        'MONEYENJOY',
        'MONEYINMINUTES',
        'MONEYMORE',
        'MONEYTAP',
        'MONEYTAP CREDIT',
        'MONEYVIEW',
        'MONEYWOW',
        'MORERUPEE',
        'MPOKKET',
        'MUDRAKIWK',
        'MUDRAKWIK',
        'MUTHOOT BLUE',
        'MY LOAN CARE',
        'MYLOANMITRA',
        'MYSTRO',
        'NAMASTE BIZ',
        'NAMASTE CREDIT',
        'NAMASTE CREDIT LOAN HUB',
        'NAMASTE CREDIT NEW LOAN',
        'NAMASTECREDIT',
        'NAMASTECREDITNEWLOAN',
        'NANOCRED',
        'NIRA',
        'OCASH',
        'OFFERMELOAN',
        'OKCASH',
        'OKOA CASH LOANS',
        'ONLINE LOAN INFORMATION',
        'OPTACREDIT',
        'OYE LOANS',
        'PAISABAZAAR',
        'PAISADUKAAN',
        'PAYME',
        'PAYME INDIA',
        'PAYMEINDIA',
        'PAYSENSE',
        'PAYSENSE PARTNER',
        'PEERLEND',
        'PERKFINANCE',
        'PHOCKET',
        'PHONEPARLOAN',
        'POCKET LOAN',
        'QBERA',
        'QUICK LOAN',
        'QUICKCREDIT',
        'QUIKKLOAN',
        'RAPIDRUPEE',
        'REDCARPET',
        'REVFIN',
        'ROBOCASH',
        'RSLOAN',
        'RUBIQUE',
        'RUPEE MAX',
        'RUPEEBOX',
        'RUPEEBUS',
        'RUPEECASH ',
        'RUPEECIRCLE',
        'RUPEECLUB',
        'RUPEEHUB',
        'RUPEEK',
        'RUPEELEND',
        'RUPEELOAN',
        'RUPEEMAX',
        'RUPEEPLUS',
        'RUPEEREDEE',
        'RUPEESTAR',
        'SAHUKAR',
        'SALARY DOST',
        'SALARY NOW',
        'SALARYDOST',
        'SALT',
        'SALT APP',
        'SANKALP INDIA FINANCE',
        'SBI HOME LOANS',
        'SBI LOANS',
        'SHUBH',
        'SHUBH LOANS',
        'SHUBHLOANS',
        'SIMPLYCASH',
        'SLICEPAY',
        'SMART MONEY',
        'SMARTCOIN',
        'SNAPMINT',
        'SPEEDCASH',
        'STASHFIN',
        'STASHFIN ELEV8',
        'STUCRED',
        'SUPERCASH',
        'TACHYLOANS',
        'TATA CAPITAL MOBILE APP',
        'THIRD FEDERAL SAVINGS & LOAN',
        'TRUE BALANCE',
        'TVS CREDIT SAATHI',
        'UA',
        'UCASH',
        'UPWARDS',
        'V SERVE LOANS',
        'WALNUT',
        'WAY2MONEY',
        'WECASH',
        'WERUPEE',
        'WIFICASH',
        'YAARII',
        'YARRII',
        'YELO',
        'Z2P',
        'ZESTMONEY',
        'ZIPLOAN',
        'ZOOMCASH',
    ];
    /**
     * RiskDataDemoService constructor.
     * @param array $opt
     * @throws Exception
     */
    public function __construct(array $opt = [])
    {
        $this->data = new RiskDataContainer();
        if (!$this->data->load($opt, '') || !$this->data->validate()) {
            throw new Exception(implode(',', $this->data->getErrorSummary(true)));
        }
    }


    /**
     * 是否为新用户
     * @return int 1 新客  0 老客
     */
    public function checkIsNewUser()
    {
        if (UserLoanOrder::FIRST_LOAN_IS == $this->data->order->is_first) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * 用户年龄
     * @return int
     */
    public function checkUserAge()
    {
        $birthday = $this->data->userBasicInfo->birthday;
        $date = Carbon::rawCreateFromFormat('Y-m-d', $birthday);
        return $date->diffInYears(Carbon::now());
    }

    /**
     * 教育程度
     * @return int  1 大学及以上   0 大学以下
     */
    public function checkHighEducationLevel()
    {
        $userWorkInfo = $this->data->userWorkInfo;
        return $userWorkInfo->educated;
    }

    /**
     * 行业
     * @return int
     */
    public function checkIndustry()
    {
        $userWorkInfo = $this->data->userWorkInfo;
        return $userWorkInfo->industry;
    }

    /**
     * 居住地址是否命中本平台白名单地区
     * @return int   1 命中   0 未命中
     */
    public function checkResidentialAddressHitWhiteList()
    {
        $userWorkInfo = $this->data->userWorkInfo;

        foreach ($this->addressWhiteList as $value) {
            if (strtolower($userWorkInfo->residential_address2) == strtolower($value)) {
                return 1;
            }
        }

        foreach ($this->addressWhiteList2 as $value) {
            if (strtolower($userWorkInfo->residential_address2) == strtolower($value)) {
                return 2;
            }
        }
        return 0;
    }

    /**
     * 身份证号是否命中自有黑名单
     * @return int   1 命中   0 未命中
     */
    public function checkIDCardHitBlackList()
    {
        if(!isset($this->data->loanPerson->aadhaar_md5)){
            return 0;
        }
       $aadhaarIds = [
            $this->data->loanPerson->aadhaar_md5
        ];
        $service = new RiskBlackListService();
        if($service->checkHitByAadhaar($aadhaarIds, $this->data->order->merchant_id))
        {
            return 1;
        }else{
            return 0;
        }
    }

    /**
     * 手机号是否命中自有黑名单
     * @return int   1 命中   0 未命中
     */
    public function checkMobileHitBlackList()
    {
        $phones = [
            $this->data->loanPerson->phone
        ];
        $service = new RiskBlackListService();
        if($service->checkHitByPhones($phones, $this->data->order->merchant_id))
        {
            return 1;
        }else{
            return 0;
        }
    }

    /**
     * 设备号是否命中自有黑名单
     * @return int   1 命中   0 未命中
     */
    public function checkDevicenoHitBlackList()
    {
        if(!isset($this->data->order->device_id)){
            return 0;
        }
        $deviceIds = [
            $this->data->order->device_id
        ];
        $service = new RiskBlackListService();
        if ($service->checkHitByDeviceIds($deviceIds, $this->data->order->merchant_id)) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * 数盟设备ID是否命中自有黑名单
     * @return int   1 命中   0 未命中
     */
    public function checkIsSMDeviceIDHitBlackList()
    {
        if(empty($this->data->order->did)){
            return -1;
        }
        $deviceIds = [
            $this->data->order->did
        ];
        $service = new RiskBlackListService();
        if ($service->checkHitBySMDeviceIds($deviceIds, $this->data->order->merchant_id)) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * 手机号命中逾期用户的紧急联系人的手机号个数
     * @return int
     */
    public function checkMobileSameAsOverdueContactMobileCnt()
    {
        $count = UserOverdueContact::find()->where(['phone' => $this->data->loanPerson->phone])->count();
        $count_loan = UserOverdueContact::find()->where(['phone' => $this->data->loanPerson->phone])->count('*', Yii::$app->db_loan);
        return $count + $count_loan;
    }

    /**
     * 近1个月内该手机号申请被拒次数
     * @return int|string
     */
    public function checkRejectCntLast1MonthByMobile()
    {
        $phone = $this->data->loanPerson->phone;
        $userIds = $this->getPhoneUserIds($phone);
        $lastTime = strtotime('last month');
        $count = UserLoanOrder::find()
            ->where(['status' => UserLoanOrder::STATUS_CHECK_REJECT, 'user_id' => $userIds])
            ->andWhere(['>=', 'order_time', $lastTime])->count();
        return $count;
    }

    /**
     * 手机号近1个月内申请次数
     * @return int|string
     */
    public function checkApplyCntLast1MonthByMobile()
    {
        $phone = $this->data->loanPerson->phone;
        $userIds = $this->getPhoneUserIds($phone);
        $lastTime = strtotime('last month');
        $count = UserLoanOrder::find()
            ->where(['user_id' => $userIds])
            ->andWhere(['>=', 'order_time', $lastTime])->count();
        return $count;
    }

    /**
     * 紧急联系人的手机号命中自有黑名单
     * @return int   0 未命中   1 命中  -1 获取异常
     */
    public function checkContactMobileHitBlackList()
    {
        $phones = $this->getPanUserContacts($this->data->loanPerson->pan_code);
        $service = new RiskBlackListService();
        if ($service->checkHitByContactPhones($phones, $this->data->order->merchant_id)) {
            return 1;
        } else {
            return 0;
        }

    }

    /**
     * 申请手机号是近1个月内申请用户紧急联系人手机号码的数量
     * @return int|string
     */
    public function checkMobileSameAsContactMobileCntLast1Month()
    {
        $phone = $this->data->loanPerson->phone;
        $userIds = array_unique(ArrayHelper::getColumn(UserContact::find()->select(['user_id'])
            ->where(['phone' => $phone])
            ->orWhere(['other_phone' => $phone])
            ->asArray()->all(),
            'user_id'));
        $lastTime = strtotime('last month');
        $count = 0;
        if (!empty($userIds)) {
            $count = UserLoanOrder::find()->where(['user_id' => $userIds])
                ->andWhere(['>=', 'order_time', $lastTime])
                ->asArray()->groupBy(['user_id'])->count();
        }

        $userIds_loan = array_unique(ArrayHelper::getColumn(UserContact::find()->select(['user_id'])
            ->where(['phone' => $phone])
            ->orWhere(['other_phone' => $phone])
            ->asArray()->all(Yii::$app->db_loan),
            'user_id'));
        $count_loan = 0;
        if (!empty($userIds_loan)) {
            $count_loan = UserLoanOrder::find()->where(['user_id' => $userIds_loan])
                ->andWhere(['>=', 'order_time', $lastTime])
                ->asArray()->groupBy(['user_id'])->count('*', Yii::$app->db_loan);
        }
        return $count + $count_loan;
    }


    /**
     * 紧急联系人的手机号码近1个月内在本平台出现的次数
     * @return mixed
     */
    public function checkSameContactCntLast1Month()
    {
        $phones = $this->getPanUserContacts($this->data->loanPerson->pan_code);
        $lastTime = strtotime('last month');
        $count1 = UserContact::find()->select(['user_id'])
            ->where(['phone' => $phones])
            ->andWhere(['!=', 'user_id', $this->data->loanPerson->id])
            ->andWhere(['>=', 'created_at', $lastTime])
            ->groupBy(['user_id'])->count();
        $count1_loan = UserContact::find()->select(['user_id'])
            ->where(['phone' => $phones])
            ->andWhere(['>=', 'created_at', $lastTime])
            ->groupBy(['user_id'])->count('*', Yii::$app->db_loan);
        $count2 = UserContact::find()->select(['user_id'])
            ->where(['other_phone' => $phones])
            ->andWhere(['!=', 'user_id', $this->data->loanPerson->id])
            ->andWhere(['>=', 'created_at', $lastTime])
            ->groupBy(['user_id'])->count();
        $count2_loan = UserContact::find()->select(['user_id'])
            ->where(['other_phone' => $phones])
            ->andWhere(['>=', 'created_at', $lastTime])
            ->groupBy(['user_id'])->count('*', Yii::$app->db_loan);
        return max($count1 + $count1_loan, $count2 + $count2_loan);

    }

    /**
     * 紧急联系人的手机号是否为有效手机号
     * @return int  1 两个号码均有效   0 至少有一个号码无效
     */
    public function checkContactMobileIsValid()
    {
        $phones = $this->getPanUserContacts($this->data->loanPerson->pan_code);
        $i = 0;
        foreach ($phones as $phone) {
            $phone_arr = explode(':',$phone);
            foreach ($phone_arr as $v){
                if (preg_match(Util::getPhoneMatch(),$v)) {
                    $i++;
                    break;
                }
            }
        }

        if($i == count($phones)){
            return 1;
        }
        return 0;
    }


    /**
     * 紧急联系人的手机号是否为申请手机号
     * @return int  0 不是   1 是
     */
    public function checkContactMobileSameAsApplyMobile()
    {
        $phones = $this->getPanUserContacts($this->data->loanPerson->pan_code);
        if (in_array($this->data->loanPerson->phone, $phones)
        ) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * 紧急联系人的手机号命中逾期用户手机号的数量
     * @return int
     */
    public function checkContactNameMobileHitOverdueUserMobileCnt()
    {
        $phones = $this->getPanUserContacts($this->data->loanPerson->pan_code);
        $userIds = ArrayHelper::getColumn(
            LoanPerson::find()->select(['id'])->where(['phone' => $phones])->asArray()->all(),
            'id');
        $count = 0;
        if (!empty($userIds)) {
            $count = UserLoanOrderRepayment::find()
                ->where([
                    'user_id' => $userIds,
                    'is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_YES
                ])
                ->andWhere(['!=', 'user_id', $this->data->loanPerson->id])
                ->count('distinct user_id');
        }

        $userIds_loan = ArrayHelper::getColumn(
            LoanPerson::find()->select(['id'])->where(['phone' => $phones])->asArray()->all(Yii::$app->db_loan),
            'id');
        $count_loan = 0;
        if (!empty($userIds_loan)) {
            $count_loan = UserLoanOrderRepayment::find()
                ->where([
                    'user_id' => $userIds_loan,
                    'is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_YES
                ])
                ->count('distinct user_id', Yii::$app->db_loan);
        }

        return $count + $count_loan;

    }

    /**
     * 紧急联系人的手机号命中逾期用户的紧急联系人手机号数量
     * @return int|string
     */
    public function checkContactNameMobileHitOverdueUserContactMobileCnt()
    {
        $phones = $this->getPanUserContacts($this->data->loanPerson->pan_code);
        $userIds1 = ArrayHelper::getColumn(
            UserContact::find()->select(['user_id'])
            ->where(['phone' => $phones])
            ->groupBy(['user_id'])->asArray()->all(),
            'user_id');

        $userIds2 = ArrayHelper::getColumn(
            UserContact::find()->select(['user_id'])
            ->where(['other_phone' => $phones])
            ->groupBy(['user_id'])->asArray()->all(),
            'user_id');

        $userIds = array_merge($userIds1, $userIds2);
        $count = 0;
        if (!empty($userIds)) {
            $count = UserLoanOrderRepayment::find()
                ->where([
                    'user_id' => $userIds,
                    'is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_YES
                ])
                ->andWhere(['!=', 'user_id', $this->data->loanPerson->id])
                ->count('distinct user_id');
        }

        $userIds1_loan = ArrayHelper::getColumn(
            UserContact::find()->select(['user_id'])
            ->where(['phone' => $phones])
            ->groupBy(['user_id'])->asArray()->all(Yii::$app->db_loan),
            'user_id');

        $userIds2_loan = ArrayHelper::getColumn(
            UserContact::find()->select(['user_id'])
            ->where(['other_phone' => $phones])
            ->groupBy(['user_id'])->asArray()->all(Yii::$app->db_loan),
            'user_id');

        $userIds_loan = array_merge($userIds1_loan, $userIds2_loan);
        $count_loan = 0;
        if (!empty($userIds_loan)) {
            $count_loan = UserLoanOrderRepayment::find()
                ->where([
                    'user_id' => $userIds_loan,
                    'is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_YES
                ])
                ->count('distinct user_id', Yii::$app->db_loan);
        }
        return $count + $count_loan;
    }


    /**
     * 紧急联系人的手机号命中逾期30+用户手机号的数量
     * @return int|string
     */
    public function checkContactNameMobileHitOver30OverdueUserMobileCnt()
    {
        $phones = $this->getPanUserContacts($this->data->loanPerson->pan_code);
        $userIds = ArrayHelper::getColumn(
            LoanPerson::find()->select(['id'])->where(['phone' => $phones])->asArray()->all(),
            'id');
        $count = 0;
        if (!empty($userIds)) {
            $count = UserLoanOrderRepayment::find()
                ->where([
                    'user_id' => $userIds,
                    'is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_YES,
                ])
                ->andWhere(['>=', 'overdue_day', 30])
                ->andWhere(['!=', 'user_id', $this->data->loanPerson->id])
                ->count('distinct user_id');
        }

        $userIds_loan = ArrayHelper::getColumn(
            LoanPerson::find()->select(['id'])->where(['phone' => $phones])->asArray()->all(Yii::$app->db_loan),
            'id');
        $count_loan = 0;
        if (!empty($userIds_loan)) {
            $count_loan = UserLoanOrderRepayment::find()
                ->where([
                    'user_id' => $userIds_loan,
                    'is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_YES,
                ])
                ->andWhere(['>=', 'overdue_day', 30])
                ->count('distinct user_id', Yii::$app->db_loan);
        }
        return $count + $count_loan;
    }


    /**
     * 紧急联系人的手机号命中逾期30+用户的紧急联系人手机号数量
     * @return int|string
     */
    public function checkContactNameMobileHitOver30OverdueUserContactMobileCnt()
    {
        $phones = $this->getPanUserContacts($this->data->loanPerson->pan_code);
        $userIds1 = ArrayHelper::getColumn(
            UserContact::find()->select(['user_id'])
                ->where(['phone' => $phones])
                ->groupBy(['user_id'])->asArray()->all(),
            'user_id');

        $userIds2 = ArrayHelper::getColumn(
            UserContact::find()->select(['user_id'])
            ->where(['other_phone' => $phones])
            ->groupBy(['user_id'])->asArray()->all(),
            'user_id');

        $userIds = array_merge($userIds1, $userIds2);
        $count = 0;
        if (!empty($userIds)) {
            $count = UserLoanOrderRepayment::find()
                ->where([
                    'user_id' => $userIds,
                    'is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_YES
                ])
                ->andWhere(['>=', 'overdue_day', 30])
                ->andWhere(['!=', 'user_id', $this->data->loanPerson->id])
                ->count('distinct user_id');
        }

        $userIds1_loan = ArrayHelper::getColumn(
            UserContact::find()->select(['user_id'])
                ->where(['phone' => $phones])
                ->groupBy(['user_id'])->asArray()->all(Yii::$app->db_loan),
            'user_id');

        $userIds2_loan = ArrayHelper::getColumn(
            UserContact::find()->select(['user_id'])
                ->where(['other_phone' => $phones])
                ->groupBy(['user_id'])->asArray()->all(Yii::$app->db_loan),
            'user_id');

        $userIds_loan = array_merge($userIds1_loan, $userIds2_loan);
        $count_loan = 0;
        if (!empty($userIds_loan)) {
            $count_loan = UserLoanOrderRepayment::find()
                ->where([
                    'user_id' => $userIds_loan,
                    'is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_YES
                ])
                ->andWhere(['>=', 'overdue_day', 30])
                ->count('distinct user_id', Yii::$app->db_loan);
        }
        return $count + $count_loan;
    }

    /**
     * 获取用户外部id
     * @return int|mixed
     */
    private function getUserOtherId(){
        if(isset($this->userOtherId)){
            return $this->userOtherId;
        }else{
            if($this->data->order->is_export == UserLoanOrder::IS_EXPORT_YES){
                $order = UserLoanOrderExternal::userExternalOrder($this->data->order->order_uuid);
                if(!empty($order)){
                    return $this->userOtherId = intval($order->user_id);
                }
            }

            return $this->userOtherId = 0;
        }
    }

    /**
     * 通讯录中联系人数量
     * @return int
     * @throws \yii\mongodb\Exception
     */
    public function checkAddressBookContactCnt()
    {
        $data = $this->getContactByUserId($this->data->loanPerson->id);
        return count($data);
    }


    /**
     * 有效号码占比
     * @return int （%）
     */
    public function checkValidMobileRatio()
    {
        $validCount = 0;
        $totalCount = 0;
        $mobiles = $this->getContactByUserId($this->data->loanPerson->id);
        foreach ($mobiles as $mobile) {
            $totalCount++;
            preg_match(Util::getPhoneMatch(),$mobile['mobile']) && $validCount++;
        }
        if ($totalCount == 0) {
            return 0;
        }
        return intval(round($validCount / $totalCount * 100));
    }

    /**
     * 通讯录信息
     * @param $userId
     * @return array
     */
    protected function getContactByUserId($userId)
    {
        if (isset($this->userContacts[$userId])) {
            return $this->userContacts[$userId];
        } else {
            if($this->data->order->is_export == UserLoanOrder::IS_EXPORT_YES){
                $user_id = $this->getUserOtherId();
                $mobiles = MgUserMobileContacts::find()
                    ->where(['user_id' => $user_id])
                    ->asArray()
                    ->all(Yii::$app->mongodb_loan);
            }else{
                $mobiles = MgUserMobileContacts::find()
                    ->where(['user_id' => intval($userId)])
                    ->asArray()
                    ->all();
            }

            return $this->userContacts[$userId] = $mobiles;
        }
    }

    /**
     * 用户申请时间段
     * @return int
     */
    public function checkApplyTimeHour()
    {
        return intval(date('H', $this->data->order->order_time));
    }

    /**
     * 根据指定IP在多少天之内的申请数
     * @param $ip
     * @param int $day 至少为1
     * @param int $orderTime
     * @return int
     */
    protected function getApplyCntByBeforeDayIP($ip, $day ,$orderTime)
    {
        $key = $day;
        if(isset($this->ipInDayOrderApplyCount[$key])){
            return $this->ipInDayOrderApplyCount[$key];
        }else{
            $before = $orderTime - 86400 * $day;
            $count = UserLoanOrder::find()->select(['user_id'])
                ->where(['ip' => $ip])
                ->andWhere(['>=', 'order_time', $before])
                ->andWhere(['<=', 'order_time', $orderTime])
                ->count();
            return $this->ipInDayOrderApplyCount[$key] = $count;
        }
    }

    /**
     * 根据指定IP在多少天之内的申请数 loan
     * @param $ip
     * @param int $day 至少为1
     * @param int $orderTime
     * @return int
     */
    protected function getLoanApplyCntByBeforeDayIP($ip, $day ,$orderTime)
    {
        $key = $day;
        if(isset($this->ipInDayLoanOrderApplyCount[$key])){
            return $this->ipInDayLoanOrderApplyCount[$key];
        }else{
            $before = $orderTime - 86400 * $day;
            $count = UserLoanOrder::find()->select(['user_id'])
                ->where(['ip' => $ip])
                ->andWhere(['>=', 'order_time', $before])
                ->andWhere(['<=', 'order_time', $orderTime])
                ->count('*', Yii::$app->db_loan);
            return $this->ipInDayLoanOrderApplyCount[$key] = $count;
        }
    }

    /**
     * 同一个IP下近7天内的申请数 (7天00:00至前1天24:00的时间)
     * @return int
     */
    public function checkApplyCntLast7daysByIP()
    {
        $ip = $this->data->order->ip;
        $count = $this->getApplyCntByBeforeDayIP($ip, 7, $this->data->order->order_time);
        $loan_count = $this->getLoanApplyCntByBeforeDayIP($ip, 7, $this->data->order->order_time);
        return $count + $loan_count;
    }

    /**
     * 同一个IP下历史在总平台的申请数
     * @return int
     */
    public function checkHisApplyCntByIPTotPlatform()
    {
        $ip = $this->data->order->ip;
        $count = UserLoanOrder::find()->select(['user_id'])
            ->where(['ip' => $ip])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->count();

        $loan_count = UserLoanOrder::find()->select(['user_id'])
            ->where(['ip' => $ip])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->count('*', Yii::$app->db_loan);
        return $count + $loan_count;
    }

    /**
     * 同一个IP下历史在总平台申请的拒绝数
     * @return int
     */
    public function checkHisApplyRejectCntByIPTotPlatform()
    {
        $ip = $this->data->order->ip;
        $count = UserLoanOrder::find()->select(['user_id'])
            ->where(['status' => UserLoanOrder::STATUS_CHECK_REJECT, 'ip' => $ip])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->count();

        $loan_count = UserLoanOrder::find()->select(['user_id'])
            ->where(['status' => UserLoanOrder::STATUS_CHECK_REJECT, 'ip' => $ip])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->count('*', Yii::$app->db_loan);
        return $count + $loan_count;
    }

    /**
     * 同一个IP下近7天内在总平台申请的拒绝数
     * @return int
     */
    public function checkLast7dApplyRejectCntByIPTotPlatform()
    {
        $ip = $this->data->order->ip;
        $before = $this->data->order->order_time - 86400 * 7;
        $count = UserLoanOrder::find()->select(['user_id'])
            ->where(['status' => UserLoanOrder::STATUS_CHECK_REJECT, 'ip' => $ip])
            ->andWhere(['>=', 'order_time', $before])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->count();

        $loan_count = UserLoanOrder::find()->select(['user_id'])
            ->where(['status' => UserLoanOrder::STATUS_CHECK_REJECT, 'ip' => $ip])
            ->andWhere(['>=', 'order_time', $before])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->count('*', Yii::$app->db_loan);
        return $count + $loan_count;
    }

    /**
     * 同一个IP下近1天内的申请数 (比如今日申请时间为17：50，则统计从昨日的17：50至今日17：50的申请数)
     * @return int
     */
    public function checkApplyCntLast1dayByIP()
    {
        $ip = $this->data->order->ip;
        $count = $this->getApplyCntByBeforeDayIP($ip, 1, $this->data->order->order_time);
        $loan_count = $this->getLoanApplyCntByBeforeDayIP($ip, 1, $this->data->order->order_time);
        return $count + $loan_count;

    }

    /**
     * 同一IP下1天内在总平台申请的拒绝数
     * @return int
     */
    public function checkLast1dApplyRejectCntByIPTotPlatform()
    {
        $ip = $this->data->order->ip;
        $before = $this->data->order->order_time - 86400;
        $count = UserLoanOrder::find()->select(['user_id'])
            ->where(['ip' => $ip, 'status' => UserLoanOrder::STATUS_CHECK_REJECT])
            ->andWhere(['>=', 'order_time', $before])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->count();

        $loan_count = UserLoanOrder::find()->select(['user_id'])
            ->where(['ip' => $ip, 'status' => UserLoanOrder::STATUS_CHECK_REJECT])
            ->andWhere(['>=', 'order_time', $before])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->count('*', Yii::$app->db_loan);
        return $count + $loan_count;
    }

    /**
     * 同一IP下近1小时内申请数
     * @return int
     */
    public function checkApplyCntLast1hourByIP()
    {
        $ip = $this->data->order->ip;
        $before = $this->data->order->order_time - 3600;
        $count = UserLoanOrder::find()->select(['user_id'])
            ->where(['ip' => $ip])
            ->andWhere(['>=', 'order_time', $before])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->count();

        $loan_count = UserLoanOrder::find()->select(['user_id'])
            ->where(['ip' => $ip])
            ->andWhere(['>=', 'order_time', $before])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->count('*', Yii::$app->db_loan);
        return $count + $loan_count;
    }

    /**
     * 同一IP下1小时内在总平台申请的拒绝数
     * @return int
     */
    public function checkLast1hApplyRejectCntByIPTotPlatform()
    {
        $ip = $this->data->order->ip;
        $before = $this->data->order->order_time - 3600;
        $count = UserLoanOrder::find()->select(['user_id'])
            ->where(['ip' => $ip, 'status' => UserLoanOrder::STATUS_CHECK_REJECT])
            ->andWhere(['>=', 'order_time', $before])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->count();

        $loan_count = UserLoanOrder::find()->select(['user_id'])
            ->where(['ip' => $ip, 'status' => UserLoanOrder::STATUS_CHECK_REJECT])
            ->andWhere(['>=', 'order_time', $before])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->count('*', Yii::$app->db_loan);
        return $count + $loan_count;
    }

    /**
     * 同一个IP下历史在总平台的已到期订单数
     * @return int
     */
    public function checkHisExpireCntByIPTotPlatform()
    {
        $ip = $this->data->order->ip;
        $after = strtotime(date('Y-m-d', $this->data->order->order_time));
        $count = UserLoanOrderRepayment::find()
            ->from(UserLoanOrderRepayment::tableName() . ' as r')
            ->leftJoin(UserLoanOrder::tableName(). ' as o', 'r.order_id=o.id')
            ->where(['o.ip' => $ip])
            ->andWhere(['<=', 'r.plan_repayment_time', $after])
            ->count();

        $loan_count = UserLoanOrderRepayment::find()
            ->from(UserLoanOrderRepayment::tableName() . ' as r')
            ->leftJoin(UserLoanOrder::tableName(). ' as o', 'r.order_id=o.id')
            ->where(['o.ip' => $ip])
            ->andWhere(['<=', 'r.plan_repayment_time', $after])
            ->count('*', Yii::$app->db_loan);
        return $count + $loan_count;
    }

    /**
     * 同一个IP下历史在总平台的已到期订单中的逾期订单数
     * @return int
     */
    public function checkHisExpireDueCntByIPTotPlatform()
    {
        $ip = $this->data->order->ip;
        $after = strtotime(date('Y-m-d', $this->data->order->order_time));
        $count = UserLoanOrderRepayment::find()
            ->from(UserLoanOrderRepayment::tableName() . ' as r')
            ->leftJoin(UserLoanOrder::tableName(). ' as o', 'r.order_id=o.id')
            ->where(['o.ip' => $ip, 'r.is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_YES])
            ->andWhere(['<=', 'r.plan_repayment_time', $after])
            ->count();

        $loan_count = UserLoanOrderRepayment::find()
            ->from(UserLoanOrderRepayment::tableName() . ' as r')
            ->leftJoin(UserLoanOrder::tableName(). ' as o', 'r.order_id=o.id')
            ->where(['o.ip' => $ip, 'r.is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_YES])
            ->andWhere(['<=', 'r.plan_repayment_time', $after])
            ->count('*', Yii::$app->db_loan);
        return $count + $loan_count;
    }

    /**
     * 获取用户安装app列表
     * @param int $userId
     * @return array
     */
    public function getUserAppList($day=0){
        $key = "{$day}";
        if(isset($this->userAppList[$key])){
            return $this->userAppList[$key];
        }else{
            $appList = [];
            $appList3 = [];
            $appList7 = [];
            $appList30 = [];
            if($this->data->order->is_export == UserLoanOrder::IS_EXPORT_YES){
                $user_id = $this->getUserOtherId();
                $appLineInfo = MgUserInstalledApps::find()
                    ->where(['user_id' => $user_id])
                    ->asArray()
                    ->all(Yii::$app->mongodb_loan);
            }else{
                $appLineInfo = MgUserInstalledApps::find()
                    ->where(['user_id' => $this->data->order->user_id])
                    ->asArray()
                    ->all();
            }

            $start_time = strtotime(date('Y-m-d', $this->data->order->order_time));
            $start_3 = $start_time - 3 * 86400;
            $start_7 = $start_time - 7 * 86400;
            $start_30 = $start_time - 30 * 86400;

            foreach ($appLineInfo as $item){
                foreach ($item['addeds'] as $v){
                    $appList[] = $v['appName'];

                    $installTime = intval($v['firstInstallTime'] / 1000);

                    if($installTime >= $start_3){
                        $appList3[] = $v['appName'];
                    }
                    if($installTime >= $start_7){
                        $appList7[] = $v['appName'];
                    }
                    if($installTime >= $start_30){
                        $appList30[] = $v['appName'];
                    }
                }
            }

            $this->userAppList = [
                0 => array_unique($appList),
                3 => array_unique($appList3),
                7 => array_unique($appList7),
                30 => array_unique($appList30),
            ];

            return $this->userAppList[$key];
        }
    }

    /**
     * 借贷类app个数(申请人手机上安装借贷类app命中借贷类app列表的的个数)
     * @return int
     */
    public function checkLoanAppCnt(){
        $appList = $this->getUserAppList();

        $count = 0;
        foreach ($appList as $v){
            if(in_array(strtoupper($v), $this->loanAppList)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 借贷类app百分比(%)
     * @return int
     */
    public function checkLoanAppRatio(){
        $appList = $this->getUserAppList();
        if(empty($appList)){
            return 0;
        }

        $count = 0;
        foreach ($appList as $v){
            if(in_array(strtoupper($v), $this->loanAppList)){
                $count++;
            }
        }

        return round($count / count($appList) * 100);
    }

    /**
     * 近3天安装的借贷类APP数量
     * @return int
     */
    public function checkLast3dLoanAppCnt(){
        $appList = $this->getUserAppList(3);
        $count = 0;
        foreach ($appList as $v){
            if(in_array(strtoupper($v), $this->loanAppList)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 近3天安装的借贷类APP数量占比
     * @return int
     */
    public function checkLast3dLoanAppRate(){
        $appList = $this->getUserAppList(3);
        if(empty($appList)){
            return 0;
        }

        $count = 0;
        foreach ($appList as $v){
            if(in_array(strtoupper($v), $this->loanAppList)){
                $count++;
            }
        }

        return round($count / count($appList) * 100);
    }

    /**
     * 近7天安装的借贷类APP数量
     * @return int
     */
    public function checkLast7dLoanAppCnt(){
        $appList = $this->getUserAppList(7);
        $count = 0;
        foreach ($appList as $v){
            if(in_array(strtoupper($v), $this->loanAppList)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 近7天安装的借贷类APP数量占比
     * @return int
     */
    public function checkLast7dLoanAppRate(){
        $appList = $this->getUserAppList(7);
        if(empty($appList)){
            return 0;
        }

        $count = 0;
        foreach ($appList as $v){
            if(in_array(strtoupper($v), $this->loanAppList)){
                $count++;
            }
        }

        return round($count / count($appList) * 100);
    }

    /**
     * 近30天安装的借贷类APP数量
     * @return int
     */
    public function checkLast30dLoanAppCnt(){
        $appList = $this->getUserAppList(30);
        $count = 0;
        foreach ($appList as $v){
            if(in_array(strtoupper($v), $this->loanAppList)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 近30天安装的借贷类APP数量占比
     * @return int
     */
    public function checkLast30dLoanAppRate(){
        $appList = $this->getUserAppList(30);
        if(empty($appList)){
            return 0;
        }

        $count = 0;
        foreach ($appList as $v){
            if(in_array(strtoupper($v), $this->loanAppList)){
                $count++;
            }
        }

        return round($count / count($appList) * 100);
    }

    /**
     * 近3天安装的APP数量
     * @return int
     */
    public function checkLast3dAppCnt(){
        $appList = $this->getUserAppList(3);

        return count($appList);
    }

    /**
     * 近7天安装的APP数量
     * @return int
     */
    public function checkLast7dAppCnt(){
        $appList = $this->getUserAppList(7);

        return count($appList);
    }

    /**
     * 近30天安装的APP数量
     * @return int
     */
    public function checkLast30dAppCnt(){
        $appList = $this->getUserAppList(30);

        return count($appList);
    }

    /**
     * 获取该手机号下所有用户ID
     * @param $phone
     * @return array|mixed
     */
    protected function getPhoneUserIds($phone)
    {
        $key = "{$phone}";
        if (isset($this->phoneUserIds[$key])) {
            return $this->phoneUserIds[$key];
        } else {
            $userIds = ArrayHelper::getColumn(
                LoanPerson::find()->select(['id'])->where(['phone' => $phone, 'merchant_id' => $this->data->order->merchant_id])->asArray()->all(),
                'id');
            return $this->phoneUserIds[$key] = $userIds;
        }

    }

    /**
     * 获取该手机号下所有用户ID 不区分商户
     * @param $phone
     * @return array|mixed
     */
    protected function getPhoneAllUserIds($phone)
    {
        $key = "{$phone}";
        if (isset($this->phoneAllUserIds[$key])) {
            return $this->phoneAllUserIds[$key];
        } else {
            $userIds = ArrayHelper::getColumn(
                LoanPerson::find()->select(['id'])->where(['phone' => $phone])->asArray()->all(),
                'id');
            return $this->phoneAllUserIds[$key] = $userIds;
        }

    }

    /**
     * 获取该pan下所有用户ID
     * @param $pan
     * @return array|mixed
     */
    protected function getPanUserIds($pan)
    {
        $key = "{$pan}";
        if (isset($this->panUserIds[$key])) {
            return $this->panUserIds[$key];
        } else {
            $userIds = ArrayHelper::getColumn(
                LoanPerson::find()->select(['id'])->where(['pan_code' => $pan, 'merchant_id' => $this->data->order->merchant_id])->asArray()->all(),
                'id');
            return $this->panUserIds[$key] = $userIds;
        }

    }

    /**
     * 获取该pan下所有用户ID  不区分商户
     * @param $pan
     * @return array|mixed
     */
    protected function getPanAllUserIds($pan)
    {
        $key = "{$pan}";
        if (isset($this->panAllUserIds[$key])) {
            return $this->panAllUserIds[$key];
        } else {
            $userIds = ArrayHelper::getColumn(
                LoanPerson::find()->select(['id'])->where(['pan_code' => $pan])->asArray()->all(),
                'id');
            return $this->panAllUserIds[$key] = $userIds;
        }

    }

    /**
     * 获取该pan下所有用户紧急联系人
     * @param $phone
     * @return array|mixed
     */
    protected function getPanUserContacts($pan)
    {
        $key = "{$pan}";
        if (isset($this->panUserContacts[$key])) {
            return $this->panUserContacts[$key];
        } else {
            $userIds = LoanPerson::find()->select(['id'])->where(['pan_code' => $pan])->asArray()->column();
            $userContact = UserContact::find()->select(['phone', 'other_phone'])->where(['user_id' => $userIds])->asArray()->all();

            $userIds_loan = LoanPerson::find()->select(['id'])->where(['pan_code' => $pan])->asArray()->column(Yii::$app->db_loan);
            $userContact_loan = UserContact::find()->select(['phone', 'other_phone'])->where(['user_id' => $userIds_loan])->asArray()->all(Yii::$app->db_loan);
            $phones = array_unique(array_merge(ArrayHelper::getColumn($userContact, 'phone'),
                ArrayHelper::getColumn($userContact, 'other_phone'),
                ArrayHelper::getColumn($userContact_loan, 'phone'),
                ArrayHelper::getColumn($userContact_loan, 'other_phone')));
            return $this->panUserContacts[$key] = $phones;
        }
    }

    /**
     * 同一定位地址的500米半径内、近7天内申请贷款笔数
     *
     * @return int
     * @throws
     */
    public function checkApplyCnt500mAwayFromGPSlocLast7Days(): int
    {
        //elasticsearch的使用说明
        //1.使用count时禁止使用source
        //2.distance单位km,m,cm,mm,nmi
        //3.date 时间戳或Zulu ISO8601
        $clientInfo = json_decode($this->data->order->client_info, true);
        if(empty($clientInfo['latitude']) || empty($clientInfo['longitude'])){
            return -1;
        }
        $time = $this->data->order->order_time;
        $orderNum = EsUserLoanOrder::find()
            ->query([
                'bool' => [
                    'must' => [
//                        [
//                            'term' => [
//                                'merchant_id' => $this->data->order->merchant_id,
//                            ]
//                        ],
//                        [
                            'range' => [
                                'order_time' => [
                                    'gte' => Carbon::createFromTimestamp($time)->subDays(7)->toIso8601ZuluString(),
                                    'lte' => Carbon::createFromTimestamp($time)->toIso8601ZuluString()
                                ]
                            ]
//                        ]
                    ],
                    'filter' => [
                        'geo_distance' => [
                            'distance' => '500m',
                            'location' => [
                                'lat' => $clientInfo['latitude'],
                                'lon' => $clientInfo['longitude'],
                            ]
                        ]
                    ]
                ]
            ])
            ->count();

        $orderNum_loan = EsUserLoanOrder::find()
            ->query([
                'bool' => [
                    'must' => [
                        'range' => [
                            'order_time' => [
                                'gte' => Carbon::createFromTimestamp($time)->subDays(7)->toIso8601ZuluString(),
                                'lte' => Carbon::createFromTimestamp($time)->toIso8601ZuluString()
                            ]
                        ]
                    ],
                    'filter' => [
                        'geo_distance' => [
                            'distance' => '500m',
                            'location' => [
                                'lat' => $clientInfo['latitude'],
                                'lon' => $clientInfo['longitude'],
                            ]
                        ]
                    ]
                ]
            ])
            ->count('*', Yii::$app->elasticsearch_loan);

        return $orderNum + $orderNum_loan;
    }

    /**
     * 同一定位地址的500米半径内、历史在总平台的申请贷款笔数
     *
     * @return int
     * @throws
     */
    public function checkHisApplyCnt500mAwayFromGPSLoc(): int
    {
        //elasticsearch的使用说明
        //1.使用count时禁止使用source
        //2.distance单位km,m,cm,mm,nmi
        //3.date 时间戳或Zulu ISO8601
        $clientInfo = json_decode($this->data->order->client_info, true);
        if(empty($clientInfo['latitude']) || empty($clientInfo['longitude'])){
            return -1;
        }
        $time = $this->data->order->order_time;
        $orderNum = EsUserLoanOrder::find()
            ->query([
                'bool' => [
                    'must' => [
                        'range' => [
                            'order_time' => [
                                'lte' => Carbon::createFromTimestamp($time)->toIso8601ZuluString()
                            ]
                        ]
                    ],
                    'filter' => [
                        'geo_distance' => [
                            'distance' => '500m',
                            'location' => [
                                'lat' => $clientInfo['latitude'],
                                'lon' => $clientInfo['longitude'],
                            ]
                        ]
                    ]
                ]
            ])
            ->count();

        $orderNum_loan = EsUserLoanOrder::find()
            ->query([
                'bool' => [
                    'must' => [
                        'range' => [
                            'order_time' => [
                                'lte' => Carbon::createFromTimestamp($time)->toIso8601ZuluString()
                            ]
                        ]
                    ],
                    'filter' => [
                        'geo_distance' => [
                            'distance' => '500m',
                            'location' => [
                                'lat' => $clientInfo['latitude'],
                                'lon' => $clientInfo['longitude'],
                            ]
                        ]
                    ]
                ]
            ])
            ->count('*', Yii::$app->elasticsearch_loan);

        return $orderNum + $orderNum_loan;
    }

    /**
     * 同一定位地址的500米半径内、近1小时内申请贷款数
     *
     * @return int
     * @throws
     */
    public function checkApplyCnt500mAwayFromGPSlocLast1Hour(): int
    {
        //elasticsearch的使用说明
        //1.使用count时禁止使用source
        //2.distance单位km,m,cm,mm,nmi
        //3.date 时间戳或Zulu ISO8601
        $clientInfo = json_decode($this->data->order->client_info, true);
        if(empty($clientInfo['latitude']) || empty($clientInfo['longitude'])){
            return -1;
        }
        $orderNum = EsUserLoanOrder::find()
            ->query([
                'bool' => [
                    'must' => [
//                        [
//                            'term' => [
//                                'merchant_id' => $this->data->order->merchant_id,
//                            ],
//                        ],
//                        [
                            'range' => [
                                'order_time' => [
                                    'gte' => Carbon::createFromTimestamp($this->data->order->order_time)->subHours(1)->toIso8601ZuluString(),
                                    'lte' => Carbon::createFromTimestamp($this->data->order->order_time)->toIso8601ZuluString()
                                ]
                            ],
//                        ]
                    ],
                    'filter' => [
                        'geo_distance' => [
                            'distance' => '500m',
                            'location' => [
                                'lat' => $clientInfo['latitude'],
                                'lon' => $clientInfo['longitude'],
                            ]
                        ]
                    ]
                ]
            ])
            ->count();

        $orderNum_loan = EsUserLoanOrder::find()
            ->query([
                'bool' => [
                    'must' => [
                        'range' => [
                            'order_time' => [
                                'gte' => Carbon::createFromTimestamp($this->data->order->order_time)->subHours(1)->toIso8601ZuluString(),
                                'lte' => Carbon::createFromTimestamp($this->data->order->order_time)->toIso8601ZuluString()
                            ]
                        ],
                    ],
                    'filter' => [
                        'geo_distance' => [
                            'distance' => '500m',
                            'location' => [
                                'lat' => $clientInfo['latitude'],
                                'lon' => $clientInfo['longitude'],
                            ]
                        ]
                    ]
                ]
            ])
            ->count('*', Yii::$app->elasticsearch_loan);

        return $orderNum + $orderNum_loan;
    }

    /**
     * 同一定位地址的500米半径内、近1天内申请贷款数
     *
     * @return int
     * @throws
     */
    public function checkApplyCnt500mAwayFromGPSlocLast1Day(): int
    {
        $clientInfo = json_decode($this->data->order->client_info, true);
        if(empty($clientInfo['latitude']) || empty($clientInfo['longitude'])){
            return -1;
        }
        $orderNum = EsUserLoanOrder::find()
            ->query([
                'bool' => [
                    'must' => [
//                        [
//                            'term' => [
//                                'merchant_id' => $this->data->order->merchant_id,
//                            ],
//                        ],
//                        [
                            'range' => [
                                'order_time' => [
                                    'gte' => Carbon::createFromTimestamp($this->data->order->order_time)->subDays(1)->toIso8601ZuluString(),
                                    'lte' => Carbon::createFromTimestamp($this->data->order->order_time)->toIso8601ZuluString()
                                ]
                            ],
//                        ]
                    ],
                    'filter' => [
                        'geo_distance' => [
                            'distance' => '500m',
                            'location' => [
                                'lat' => $clientInfo['latitude'],
                                'lon' => $clientInfo['longitude'],
                            ]
                        ]
                    ]
                ]
            ])
            ->count();

        $orderNum_loan = EsUserLoanOrder::find()
            ->query([
                'bool' => [
                    'must' => [
                        'range' => [
                            'order_time' => [
                                'gte' => Carbon::createFromTimestamp($this->data->order->order_time)->subDays(1)->toIso8601ZuluString(),
                                'lte' => Carbon::createFromTimestamp($this->data->order->order_time)->toIso8601ZuluString()
                            ]
                        ],
                    ],
                    'filter' => [
                        'geo_distance' => [
                            'distance' => '500m',
                            'location' => [
                                'lat' => $clientInfo['latitude'],
                                'lon' => $clientInfo['longitude'],
                            ]
                        ]
                    ]
                ]
            ])
            ->count('*', Yii::$app->elasticsearch_loan);

        return $orderNum + $orderNum_loan;
    }

    /**
     * 申请信息填写的税前工资
     * @return int
     */
    public function checkAppFormSalaryBeforeTax()
    {
        return intval(CommonHelper::CentsToUnit($this->data->userWorkInfo->monthly_salary));
    }

    /**
     * 根据pan卡号查询的历史最大逾期天数
     * @return int
     */
    public function checkHistMaxOverdueDaysByIDCard()
    {
        $userIds = $this->getPanUserIds($this->data->loanPerson->pan_code);
        return UserLoanOrderRepayment::getMaxOverdueDay(['user_id' => $userIds]);
    }

    /**
     * 是否命中准入手机品牌白名单
     * @return int   1 命中  0 未命中
     */
    public function checkMobileBrandHitWhiteList()
    {
        $clientInfo = json_decode($this->data->order->client_info, true);
        if(empty($clientInfo['brandName'])){
            return -1;
        }
        $brandName = strtoupper($clientInfo['brandName']);
        if(in_array($brandName, PhoneBrand::$whiteList)){
            return 1;
        }else{
            return 0;
        }
    }

    /**
     * 调用cibil报告
     * @return array
     * @throws \Exception
     */
    protected function getCibilRequest(){
        if($this->isGetData){
            $params = [
                'loanPerson' => $this->data->loanPerson,
                'order' => $this->data->order,
                'retryLimit' => 3,
            ];
            $service = new CreditReportCibilService($params);
            $service->getData();
        }

        return $this->getCibilReport();
    }

    /**
     * 获取cibil征信报告
     * @return array
     * @throws \Exception
     */
    protected function getCibilReport()
    {
        if (is_null($this->cibilReport)) {
            $this->data->order = UserLoanOrder::findOne($this->data->order->id);
            $xml_result = $this->data->order->userCreditReportCibil->data ?? [];
            if(empty($xml_result)){
                return $this->cibilReport = [];
            }
            try {
                $xpath = simplexml_load_string($xml_result);
                $xpath->registerXPathNamespace('soap', 'http://schemas.xmlsoap.org/soap/envelope/');
                $result = $xpath->xpath("soap:Body");
                $result = json_decode(json_encode($result),true);
                $xml = htmlspecialchars_decode($result[0]['ExecuteXMLStringResponse']['ExecuteXMLStringResult']);
                $data = json_decode(json_encode(simplexml_load_string($xml)),true);
                if(isset($data['ContextData']['Field'][0]['Applicants'])){
                    $data = $data['ContextData']['Field'][0]['Applicants']['Applicant']['DsCibilBureau']['Response']['CibilBureauResponse'];
                }
                if(isset($data['ContextData']['Field'][0]['Applicant'])){
                    $data = $data['ContextData']['Field'][0]['Applicant']['DsCibilBureau']['Response']['CibilBureauResponse'];
                }
                $IsSucess = $data['IsSucess'] ?? '';
                if($IsSucess == 'True'){
                    $this->cibilReport['CreditReport'] = json_decode(json_encode(simplexml_load_string($data['BureauResponseXml'])),true);
                    $this->cibilReport['updated_at'] = $this->data->order->userCreditReportCibil->query_time;
                }else{
                    $this->cibilReport = [];
                }
            } catch (\Exception $e){
                \Yii::error(['order_id'=>$this->data->order->id,'err_msg'=>$e->getMessage(),'trace'=>$e->getTraceAsString()], 'RiskAutoCheck');
                $this->cibilReport = [];
            }
            return $this->cibilReport;
        } else {
            return $this->cibilReport;
        }
    }

    /**
     * Cibil历史被查询征信的次数
     * @return int
     * @throws \Exception
     */
    public function checkCibilHisEnquiryCnt(){
        $report = $this->getCibilReport();
        if(!empty($report['CreditReport']['Enquiry'])){
            return count($report['CreditReport']['Enquiry']);
        }
        return 0;
    }

    /**
     * Cibil近6个月被查询征信的次数
     * @return int
     * @throws \Exception
     */
    public function checkCibilLast6mEnquiryCnt(){
        $report = $this->getCibilReport();
        $count = 0;
        if(empty($report['CreditReport']['Enquiry'])){
            return $count;
        }
        $time = strtotime("-6 month", $report['updated_at']);
        foreach ($report['CreditReport']['Enquiry'] as $v){
            if(!empty($v['DateOfEnquiryFields'])
                && $time < strtotime(Carbon::rawCreateFromFormat('dmY', $v['DateOfEnquiryFields'])->toDateString())){
                $count++;
            }
        }
        return $count;
    }

    /**
     * Cibil近1个月被查询征信的次数
     * @return int
     * @throws \Exception
     */
    public function checkCibilLast1mEnquiryCnt(){
        $report = $this->getCibilReport();
        $count = 0;
        if(empty($report['CreditReport']['Enquiry'])){
            return $count;
        }
        $time = strtotime("-1 month", $report['updated_at']);
        foreach ($report['CreditReport']['Enquiry'] as $v){
            if(!empty($v['DateOfEnquiryFields'])
                && $time < strtotime(Carbon::rawCreateFromFormat('dmY', $v['DateOfEnquiryFields'])->toDateString())){
                $count++;
            }
        }
        return $count;
    }

    /**
     * Cibil历史最大授信金额
     * @return int
     * @throws \Exception
     */
    public function checkCibilHisMaxCreditAmt(){
        $report = $this->getCibilReport();
        $amount = 0;
        if(empty($report['CreditReport']['Account'])){
            return $amount;
        }
        foreach ($report['CreditReport']['Account'] as $v){
            if(!empty($v['Account_NonSummary_Segment_Fields']['HighCreditOrSanctionedAmount'])
                && $amount < $v['Account_NonSummary_Segment_Fields']['HighCreditOrSanctionedAmount']){
                $amount = $v['Account_NonSummary_Segment_Fields']['HighCreditOrSanctionedAmount'];
            }
        }
        return intval($amount);
    }

    /**
     * Cibil最近6个月的最大授信金额
     * @return int
     * @throws \Exception
     */
    public function checkCibilLast6mMaxCreditAmt(){
        $report = $this->getCibilReport();
        $amount = 0;
        if(empty($report['CreditReport']['Account'])){
            return $amount;
        }
        $time = strtotime("-6 month", $report['updated_at']);
        foreach ($report['CreditReport']['Account'] as $v){
            if(!empty($v['Account_NonSummary_Segment_Fields']['HighCreditOrSanctionedAmount'])
                && !empty($v['Account_NonSummary_Segment_Fields']['DateOpenedOrDisbursed'])
                && $amount < $v['Account_NonSummary_Segment_Fields']['HighCreditOrSanctionedAmount']
                && $time < strtotime(Carbon::rawCreateFromFormat('dmY', $v['Account_NonSummary_Segment_Fields']['DateOpenedOrDisbursed'])->toDateString())){
                $amount = $v['Account_NonSummary_Segment_Fields']['HighCreditOrSanctionedAmount'];
            }
        }
        return $amount;
    }

    /**
     * Cibil最近1个月的最大授信金额
     * @return int
     * @throws \Exception
     */
    public function checkCibilLast1mMaxCreditAmt(){
        $report = $this->getCibilReport();
        $amount = 0;
        if(empty($report['CreditReport']['Account'])){
            return $amount;
        }
        $time = strtotime("-1 month", $report['updated_at']);
        foreach ($report['CreditReport']['Account'] as $v){
            if(!empty($v['Account_NonSummary_Segment_Fields']['HighCreditOrSanctionedAmount'])
                && !empty($v['Account_NonSummary_Segment_Fields']['DateOpenedOrDisbursed'])
                && $amount < $v['Account_NonSummary_Segment_Fields']['HighCreditOrSanctionedAmount']
                && $time < strtotime(Carbon::rawCreateFromFormat('dmY', $v['Account_NonSummary_Segment_Fields']['DateOpenedOrDisbursed'])->toDateString())){
                $amount = $v['Account_NonSummary_Segment_Fields']['HighCreditOrSanctionedAmount'];
            }
        }
        return $amount;
    }

    /**
     * Cibil历史平均授信金额
     * @return int
     * @throws \Exception
     */
    public function checkCibilHisAvgCreditAmt(){
        $report = $this->getCibilReport();
        $amount = 0;
        $count = 0;
        if(empty($report['CreditReport']['Account'])){
            return 0;
        }
        foreach ($report['CreditReport']['Account'] as $v){
            $amount += $v['Account_NonSummary_Segment_Fields']['HighCreditOrSanctionedAmount'] ?? 0;
            $count ++;
        }
        if($count == 0){
            return 0;
        }
        return round($amount/$count);
    }

    /**
     * Cibil最近6个月的平均授信金额
     * @return int
     * @throws \Exception
     */
    public function checkCibilLast6mAvgCreditAmt(){
        $report = $this->getCibilReport();
        $amount = 0;
        $count = 0;
        if(empty($report['CreditReport']['Account'])){
            return 0;
        }
        $time = strtotime("-6 month", $report['updated_at']);
        foreach ($report['CreditReport']['Account'] as $v){
            if(!empty($v['Account_NonSummary_Segment_Fields']['DateOpenedOrDisbursed'])
                && $time < strtotime(Carbon::rawCreateFromFormat('dmY', $v['Account_NonSummary_Segment_Fields']['DateOpenedOrDisbursed'])->toDateString())
                && !empty($v['Account_NonSummary_Segment_Fields']['HighCreditOrSanctionedAmount'])){
                $amount += $v['Account_NonSummary_Segment_Fields']['HighCreditOrSanctionedAmount'];
                $count ++;
            }
        }
        if($count == 0){
            return 0;
        }
        return round($amount/$count);
    }

    /**
     * Cibil最近1个月的平均授信金额
     * @return int
     * @throws \Exception
     */
    public function checkCibilLast1mAvgCreditAmt(){
        $report = $this->getCibilReport();
        $amount = 0;
        $count = 0;
        if(empty($report['CreditReport']['Account'])){
            return 0;
        }
        $time = strtotime("-1 month", $report['updated_at']);
        foreach ($report['CreditReport']['Account'] as $v){
            if(!empty($v['Account_NonSummary_Segment_Fields']['DateOpenedOrDisbursed'])
                && $time < strtotime(Carbon::rawCreateFromFormat('dmY', $v['Account_NonSummary_Segment_Fields']['DateOpenedOrDisbursed'])->toDateString())
                && !empty($v['Account_NonSummary_Segment_Fields']['HighCreditOrSanctionedAmount'])){
                $amount += $v['Account_NonSummary_Segment_Fields']['HighCreditOrSanctionedAmount'];
                $count ++;
            }
        }
        if($count == 0){
            return 0;
        }
        return round($amount/$count);
    }

    /**
     * Cibil最近一次授信距今的时间
     * @return int
     * @throws \Exception
     */
    public function checkCibilTimeOfLastCreditTimeToNow(){
        $report = $this->getCibilReport();
        $last_time = 0;
        if(empty($report['CreditReport']['Account'])){
            return -9999;
        }
        foreach ($report['CreditReport']['Account'] as $v){
            if(!empty($v['Account_NonSummary_Segment_Fields']['DateOpenedOrDisbursed'])) {
                $time = strtotime(Carbon::rawCreateFromFormat('dmY', $v['Account_NonSummary_Segment_Fields']['DateOpenedOrDisbursed'])->toDateString());
                if($time > $last_time){
                    $last_time = $time;
                }
            }
        }

        if(empty($last_time)){
            return -9999;
        }

        $diff = (strtotime(date('Y-m-d', $report['updated_at'])) - $last_time)/86400;
        return intval($diff);
    }

    /**
     * Cibil历史逾期总金额
     * @return int
     * @throws \Exception
     */
    public function checkCibilHisDueTotAmt(){
        $report = $this->getCibilReport();
        $amount = 0;
        if(empty($report['CreditReport']['Account'])){
            return $amount;
        }
        foreach ($report['CreditReport']['Account'] as $v){
            $amount += $v['Account_NonSummary_Segment_Fields']['AmountOverdue'] ?? 0;
        }
        return $amount;
    }

    /**
     * Cibil最近6个月的逾期总金额
     * @return int
     * @throws \Exception
     */
    public function checkCibilLast6mDueTotAmt(){
        $report = $this->getCibilReport();
        $amount = 0;
        if(empty($report['CreditReport']['Account'])){
            return $amount;
        }
        $time = strtotime("-6 month", $report['updated_at']);
        foreach ($report['CreditReport']['Account'] as $v){
            if(!empty($v['Account_NonSummary_Segment_Fields']['DateOpenedOrDisbursed'])
                && $time < strtotime(Carbon::rawCreateFromFormat('dmY', $v['Account_NonSummary_Segment_Fields']['DateOpenedOrDisbursed'])->toDateString())){
                $amount += $v['Account_NonSummary_Segment_Fields']['AmountOverdue'] ?? 0;
            }
        }
        return $amount;
    }

    /**
     * Cibil最近1个月的逾期总金额
     * @return int
     * @throws \Exception
     */
    public function checkCibilLast1mDueTotAmt(){
        $report = $this->getCibilReport();
        $amount = 0;
        if(empty($report['CreditReport']['Account'])){
            return $amount;
        }
        $time = strtotime("-1 month", $report['updated_at']);
        foreach ($report['CreditReport']['Account'] as $v){
            if(!empty($v['Account_NonSummary_Segment_Fields']['DateOpenedOrDisbursed'])
                && $time < strtotime(Carbon::rawCreateFromFormat('dmY', $v['Account_NonSummary_Segment_Fields']['DateOpenedOrDisbursed'])->toDateString())){
                $amount += $v['Account_NonSummary_Segment_Fields']['AmountOverdue'] ?? 0;
            }
        }
        return $amount;
    }

    /**
     * Cibil历史逾期总次数
     * @return int
     * @throws \Exception
     */
    public function checkCibilHisDueTotCnt(){
        $report = $this->getCibilReport();
        $count = 0;
        if(empty($report['CreditReport']['Account'])){
            return $count;
        }
        foreach ($report['CreditReport']['Account'] as $v){
            if(!empty($v['Account_NonSummary_Segment_Fields']['AmountOverdue'])){
                $count ++;
            }
        }
        return $count;
    }

    /**
     * Cibil最近6个月的逾期次数
     * @return int
     * @throws \Exception
     */
    public function checkCibilLast6mDueTotCnt(){
        $report = $this->getCibilReport();
        $count = 0;
        if(empty($report['CreditReport']['Account'])){
            return $count;
        }
        $time = strtotime("-6 month", $report['updated_at']);
        foreach ($report['CreditReport']['Account'] as $v){
            if(!empty($v['Account_NonSummary_Segment_Fields']['DateOpenedOrDisbursed'])
                && $time < strtotime(Carbon::rawCreateFromFormat('dmY', $v['Account_NonSummary_Segment_Fields']['DateOpenedOrDisbursed'])->toDateString())
                && !empty($v['Account_NonSummary_Segment_Fields']['AmountOverdue'])){
                $count ++;
            }
        }
        return $count;
    }

    /**
     * Cibil最近1个月的逾期次数
     * @return int
     * @throws \Exception
     */
    public function checkCibilLast1mDueTotCnt(){
        $report = $this->getCibilReport();
        $count = 0;
        if(empty($report['CreditReport']['Account'])){
            return $count;
        }
        $time = strtotime("-1 month", $report['updated_at']);
        foreach ($report['CreditReport']['Account'] as $v){
            if(!empty($v['Account_NonSummary_Segment_Fields']['DateOpenedOrDisbursed'])
                && $time < strtotime(Carbon::rawCreateFromFormat('dmY', $v['Account_NonSummary_Segment_Fields']['DateOpenedOrDisbursed'])->toDateString())
                && !empty($v['Account_NonSummary_Segment_Fields']['AmountOverdue'])){
                $count ++;
            }
        }
        return $count;
    }

    /**
     * Cibil历史最大逾期天数
     * @return int
     * @throws \Exception
     */
    public function checkCibilHisMaxDueDays(){
        $report = $this->getCibilReport();
        $arr = [];
        if(empty($report['CreditReport']['Account'])){
            return 0;
        }
        foreach ($report['CreditReport']['Account'] as $v){
            if(!empty($v['Account_NonSummary_Segment_Fields']['AmountOverdue'])){
                if(!empty($v['Account_NonSummary_Segment_Fields']['PaymentHistory1'])){
                    for ($i = 0; $i < strlen($v['Account_NonSummary_Segment_Fields']['PaymentHistory1'])/3; $i++){
                        $str = substr($v['Account_NonSummary_Segment_Fields']['PaymentHistory1'], 3*$i, 3);
                        if(is_numeric($str)){
                            $arr[] = $str;
                        }
                    }
                }
                if(!empty($v['Account_NonSummary_Segment_Fields']['PaymentHistory2'])){
                    for ($i = 0; $i < strlen($v['Account_NonSummary_Segment_Fields']['PaymentHistory2'])/3; $i++){
                        $str = substr($v['Account_NonSummary_Segment_Fields']['PaymentHistory2'], 3*$i, 3);
                        if(is_numeric($str)){
                            $arr[] = $str;
                        }
                    }
                }
            }
        }
        return !empty($arr) ? intval(max($arr)) : 0;
    }

    /**
     * Cibil最近一次还款距今的时间
     * @return int
     * @throws \Exception
     */
    public function checkCibilTimeOfLastPayMent(){
        $report = $this->getCibilReport();
        $last_time = 0;
        if(empty($report['CreditReport']['Account'])){
            return -9999;
        }
        foreach ($report['CreditReport']['Account'] as $v){
            if(empty($v['Account_NonSummary_Segment_Fields']['DateOfLastPayment'])){
                continue;
            }
            $time = strtotime(Carbon::rawCreateFromFormat('dmY', $v['Account_NonSummary_Segment_Fields']['DateOfLastPayment'])->toDateString());
            if($time > $last_time){
                $last_time = $time;
            }
        }

        if($last_time == 0){
            return -9999;
        }

        $diff = (strtotime(date('Y-m-d', $report['updated_at'])) - $last_time)/86400;
        return intval($diff);
    }

    /**
     * Cibil征信分
     * @return int
     * @throws \Exception
     */
    public function checkCibilCreditScore(){
        $report = $this->getCibilReport();
        $score = $report['CreditReport']['ScoreSegment']['Score'] ?? 0;
        if($score == '000-1'){
            return -1;
        }
        return intval($score);
    }

    /**
     * Cibil最近6个月的月供最大值
     * @return int
     * @throws \Exception
     */
    public function checkCibilLast6mMaxEMI(){
        $report = $this->getCibilReport();
        $amount = 0;
        if(empty($report['CreditReport']['Account'])){
            return $amount;
        }
        $time = strtotime("-6 month", $report['updated_at']);
        foreach ($report['CreditReport']['Account'] as $v){
            if(!empty($v['Account_NonSummary_Segment_Fields']['EmiAmount'])
                && !empty($v['Account_NonSummary_Segment_Fields']['DateOpenedOrDisbursed'])
                && $amount < $v['Account_NonSummary_Segment_Fields']['EmiAmount']
                && $time < strtotime(Carbon::rawCreateFromFormat('dmY', $v['Account_NonSummary_Segment_Fields']['DateOpenedOrDisbursed'])->toDateString())){
                $amount = $v['Account_NonSummary_Segment_Fields']['EmiAmount'];
            }
        }
        return $amount;
    }

    /**
     * Cibil最近1个月的月供最大值
     * @return int
     * @throws \Exception
     */
    public function checkCibilLast1mMaxEMI(){
        $report = $this->getCibilReport();
        $amount = 0;
        if(empty($report['CreditReport']['Account'])){
            return $amount;
        }
        $time = strtotime("-1 month", $report['updated_at']);
        foreach ($report['CreditReport']['Account'] as $v){
            if(!empty($v['Account_NonSummary_Segment_Fields']['EmiAmount'])
                && !empty($v['Account_NonSummary_Segment_Fields']['DateOpenedOrDisbursed'])
                && $amount < $v['Account_NonSummary_Segment_Fields']['EmiAmount']
                && $time < strtotime(Carbon::rawCreateFromFormat('dmY', $v['Account_NonSummary_Segment_Fields']['DateOpenedOrDisbursed'])->toDateString())){
                $amount = $v['Account_NonSummary_Segment_Fields']['EmiAmount'];
            }
        }
        return $amount;
    }

    /**
     * Cibil最近6个月的月供总和
     * @return int
     * @throws \Exception
     */
    public function checkCibilLast6mSumEMI(){
        $report = $this->getCibilReport();
        $amount = 0;
        if(empty($report['CreditReport']['Account'])){
            return $amount;
        }
        $time = strtotime("-6 month", $report['updated_at']);
        foreach ($report['CreditReport']['Account'] as $v){
            if(!empty($v['Account_NonSummary_Segment_Fields']['DateOpenedOrDisbursed'])
                && $time < strtotime(Carbon::rawCreateFromFormat('dmY', $v['Account_NonSummary_Segment_Fields']['DateOpenedOrDisbursed'])->toDateString())){
                $amount += $v['Account_NonSummary_Segment_Fields']['EmiAmount'] ?? 0;
            }
        }
        return $amount;
    }

    /**
     * Cibil最近1个月的月供总和
     * @return int
     * @throws \Exception
     */
    public function checkCibilLast1mSumEMI(){
        $report = $this->getCibilReport();
        $amount = 0;
        if(empty($report['CreditReport']['Account'])){
            return $amount;
        }
        $time = strtotime("-1 month", $report['updated_at']);
        foreach ($report['CreditReport']['Account'] as $v){
            if(!empty($v['Account_NonSummary_Segment_Fields']['DateOpenedOrDisbursed'])
                && $time < strtotime(Carbon::rawCreateFromFormat('dmY', $v['Account_NonSummary_Segment_Fields']['DateOpenedOrDisbursed'])->toDateString())){
                $amount += $v['Account_NonSummary_Segment_Fields']['EmiAmount'] ?? 0;
            }
        }
        return $amount;
    }

    /**
     * Cibil最近6个月的月供均值
     * @return int
     * @throws \Exception
     */
    public function checkCibilLast6mAvgEMI(){
        $report = $this->getCibilReport();
        $amount = 0;
        $count = 0;
        if(empty($report['CreditReport']['Account'])){
            return $amount;
        }
        $time = strtotime("-6 month", $report['updated_at']);
        foreach ($report['CreditReport']['Account'] as $v){
            if(!empty($v['Account_NonSummary_Segment_Fields']['DateOpenedOrDisbursed'])
                && $time < strtotime(Carbon::rawCreateFromFormat('dmY', $v['Account_NonSummary_Segment_Fields']['DateOpenedOrDisbursed'])->toDateString())
                && !empty($v['Account_NonSummary_Segment_Fields']['EmiAmount'])){
                $amount += $v['Account_NonSummary_Segment_Fields']['EmiAmount'];
                $count ++;
            }
        }
        if($count == 0){
            return 0;
        }
        return round($amount/$count);
    }

    /**
     * Cibil最近1个月的月供均值
     * @return int
     * @throws \Exception
     */
    public function checkCibilLast1mAvgEMI(){
        $report = $this->getCibilReport();
        $amount = 0;
        $count = 0;
        if(empty($report['CreditReport']['Account'])){
            return $amount;
        }
        $time = strtotime("-1 month", $report['updated_at']);
        foreach ($report['CreditReport']['Account'] as $v){
            if(!empty($v['Account_NonSummary_Segment_Fields']['DateOpenedOrDisbursed'])
                && $time < strtotime(Carbon::rawCreateFromFormat('dmY', $v['Account_NonSummary_Segment_Fields']['DateOpenedOrDisbursed'])->toDateString())
                && !empty($v['Account_NonSummary_Segment_Fields']['EmiAmount'])){
                $amount += $v['Account_NonSummary_Segment_Fields']['EmiAmount'];
                $count ++;
            }
        }
        if($count == 0){
            return 0;
        }
        return round($amount/$count);
    }

    /**
     * CIBIL征信是否异常
     * @return int
     * @throws \Exception
     */
    public function checkIsCibilNormal(){
        $report = $this->getCibilRequest();
        if(empty($report)){
            return -1;
        }
        if(!empty($report['CreditReport']['Header']['SubjectReturnCode']) && $report['CreditReport']['Header']['SubjectReturnCode'] == 1){
            return 1;
        }
        return 0;
    }

    /**
     * 用户真实年龄
     * @return int
     */
    public function checkRealAge()
    {
        $birthday = $this->data->loanPerson->birthday;
        $date = Carbon::rawCreateFromFormat('Y-m-d', $birthday);
        return $date->diffInYears(Carbon::now());
    }

    /**
     * Accuauth-Pan卡OCR返回的状态
     * @return int
     */
    public function checkStatusOfPanOCR()
    {
        if($this->data->userPanReport && $this->data->userPanReport->data_status == 'OK'){
            return 1;
        }
        return 0;
    }

    /**
     * Accuauth-Pan验真返回的状态
     * @return int
     */
    public function checkStatusOfPanVertify()
    {
        if($this->data->userPanVerifyReport && $this->data->userPanVerifyReport->report_status == 1){
            return 1;
        }
        return 0;
    }

    /**
     * Accuauth-Aadhaar卡OCR返回的状态
     * @return int
     */
    public function checkStatusOfAadhaarOCR()
    {
        if($this->data->userAadhaarReport && $this->data->userAadhaarReport->data_front_status == 'OK' && $this->data->userAadhaarReport->data_back_status == 'OK'){
            return 1;
        }
        return 0;
    }

    /**
     * 定位地址是否在印度
     * @return int
     */
    public function checkIsIndiaGPSRough(){
        $clientInfo = json_decode($this->data->order->client_info,true);
        $i = 0;
        if(!empty($clientInfo['longitude']) && !empty($clientInfo['latitude'])){
            $i++;
            if($clientInfo['longitude'] < 68.51 || $clientInfo['longitude'] > 89.46 || $clientInfo['latitude'] < 7.58 || $clientInfo['latitude'] > 36.85){
                return 0;
            }
        }

        $user = UserRegisterInfo::findOne(['user_id' => $this->data->loanPerson->id]);
        if(!empty($user['headers'])){
            $clientInfo = json_decode($user['headers'],true);
            if(!empty($clientInfo['longitude']) && !empty($clientInfo['latitude'])){
                $i++;
                if($clientInfo['longitude'] < 68.51 || $clientInfo['longitude'] > 89.46 || $clientInfo['latitude'] < 7.58 || $clientInfo['latitude'] > 36.85){
                    return 0;
                }
            }
        }

        $login = UserLoginLog::find()->where(['user_id' => $this->data->loanPerson->id])
            ->andWhere(['<', 'created_at', $this->data->order->order_time])
            ->orderBy(['id' => SORT_DESC])
            ->limit(1)
            ->one();
        if(!empty($login)){
            $clientInfo = unserialize($login->source);
            if(!empty($clientInfo['longitude']) && !empty($clientInfo['latitude'])) {
                $i++;
                if ($clientInfo['longitude'] < 68.51 || $clientInfo['longitude'] > 89.46 || $clientInfo['latitude'] < 7.58 || $clientInfo['latitude'] > 36.85) {
                    return 0;
                }
            }
        }

        if(!empty($this->data->userBasicInfo->client_info)){
            $clientInfo = json_decode($this->data->userBasicInfo->client_info, true);
            if(!empty($clientInfo['longitude']) && !empty($clientInfo['latitude'])) {
                $i++;
                if ($clientInfo['longitude'] < 68.51 || $clientInfo['longitude'] > 89.46 || $clientInfo['latitude'] < 7.58 || $clientInfo['latitude'] > 36.85) {
                    return 0;
                }
            }
        }

        $clientInfo = $this->data->userPanReport->clientInfo;
        if(!empty($clientInfo['longitude']) && !empty($clientInfo['latitude'])) {
            $i++;
            if ($clientInfo['longitude'] < 68.51 || $clientInfo['longitude'] > 89.46 || $clientInfo['latitude'] < 7.58 || $clientInfo['latitude'] > 36.85) {
                return 0;
            }
        }

        if($i == 0){
            return -1;
        }
        return 1;
    }

    /**
     * 获取shumeng报告
     * @return array|ThirdDataShumeng|null
     * @throws \Exception
     */
    protected function getShumengReport()
    {
        if (is_null($this->shumengReport)) {
            if($this->isGetData){
                $params = [
                    'loanPerson' => $this->data->loanPerson,
                    'order' => $this->data->order,
                    'retryLimit' => 3,
                ];
                $service = new ShuMengService($params);
                if(!$service->getData()){
                    throw new Exception('shumeng数据拉取失败，等待重试', 1001);
                }
            }

            $data = ThirdDataShumeng::findOne(['order_id' => $this->data->order->id]);
            return $this->shumengReport = $data;
        } else {
            return $this->shumengReport;
        }
    }

    /**
     * 数盟设备ID调用API返回是否正常
     * @return int
     * @throws \Exception
     */
    public function checkIsSMDeviceIDApiReturnedNormal(){
        $data = $this->getShumengReport();
        if(empty($data) || $data['status'] != 1){
            return 0;
        }

        return 1;
    }

    /**
     * 数盟设备ID调用API返回的结果中的错误码
     * @return int
     * @throws \Exception
     */
    public function checkErrorCodeOfSMDeviceIDApiReturned(){
        $data = $this->getShumengReport();
        if(empty($data)){
            return -1;
        }

        $data = json_decode($data['report'], true);

        return $data['err'] ?? -1;
    }

    /**
     * 数盟设备ID调用API返回的结果中的设备状态
     * @return int
     * @throws \Exception
     */
    public function checkDeviceTypeCodeOfSMDeviceIDApiReturned(){
        $data = $this->getShumengReport();
        if(empty($data)){
            return -1;
        }

        $data = json_decode($data['report'], true);

        return $data['device_type'] ?? -1;
    }


    /**
     * 数盟设备ID关联的Pan数量
     * @return int
     */
    public function checkSMDeviceIDMatchPanCnt(){
        if(!$this->data->order->did){
            return -1;
        }
        $data = UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['p.pan_code'])
            ->where(['o.did' => $this->data->order->did, 'o.merchant_id' => $this->data->order->merchant_id])
            ->groupBy(['p.pan_code'])
            ->asArray()
            ->all();

        return count($data);
    }

    /**
     * 近30天内该数盟设备ID在全平台关联的不同Pan卡号数量
     * @return int
     */
    public function checkLast30SMDeviceIDMatchPanCntAllPlat(){
        if(!$this->data->order->did){
            return -1;
        }

        $begin_time = $this->data->order->order_time - 30 * 86400;
        $data = UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['p.pan_code'])
            ->where(['o.did' => $this->data->order->did, 'o.merchant_id' => $this->data->order->merchant_id])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->groupBy(['p.pan_code'])
            ->asArray()
            ->all();

        return count($data);
    }

    /**
     * 近60天内该数盟设备ID在全平台关联的不同Pan卡号数量
     * @return int
     */
    public function checkLast60SMDeviceIDMatchPanCntAllPlat(){
        if(!$this->data->order->did){
            return -1;
        }

        $begin_time = $this->data->order->order_time - 60 * 86400;
        $data = UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['p.pan_code'])
            ->where(['o.did' => $this->data->order->did, 'o.merchant_id' => $this->data->order->merchant_id])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->groupBy(['p.pan_code'])
            ->asArray()
            ->all();

        return count($data);
    }

    /**
     * 近90天数盟设备ID关联的Pan数量
     * @return int
     */
    public function checkLast90SMDeviceIDMatchPanCnt(){
        if(!$this->data->order->did){
            return -1;
        }

        $begin_time = $this->data->order->order_time - 90 * 86400;
        $data = UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['p.pan_code'])
            ->where(['o.did' => $this->data->order->did, 'o.merchant_id' => $this->data->order->merchant_id])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->groupBy(['p.pan_code'])
            ->asArray()
            ->all();

        return count($data);
    }


    /**
     * 下单环节Pan关联的数盟设备ID数量
     * @return int
     */
    public function checkPanMatchSMDeviceIDCnt(){
        if(!$this->data->loanPerson->pan_code){
            return -1;
        }

        $data = UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['o.did'])
            ->where(['p.pan_code' => $this->data->loanPerson->pan_code, 'p.merchant_id' => $this->data->order->merchant_id])
            ->groupBy(['o.did'])
            ->asArray()
            ->all();

        return count($data);
    }

    /**
     * 近30天内该Pan卡号在全平台关联的不同数盟设备ID数量
     * @return int
     */
    public function checkLast30PanMatchSMDeviceIDCntAllPlat(){
        if(!$this->data->loanPerson->pan_code){
            return -1;
        }

        $begin_time = $this->data->order->order_time - 30 * 86400;
        $data = UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['o.did'])
            ->where(['p.pan_code' => $this->data->loanPerson->pan_code, 'p.merchant_id' => $this->data->order->merchant_id])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->groupBy(['o.did'])
            ->asArray()
            ->all();

        return count($data);
    }

    /**
     * 近60天内该Pan卡号在全平台关联的不同数盟设备ID数量
     * @return int
     */
    public function checkLast60PanMatchSMDeviceIDCntAllPlat(){
        if(!$this->data->loanPerson->pan_code){
            return -1;
        }

        $begin_time = $this->data->order->order_time - 60 * 86400;
        $data = UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['o.did'])
            ->where(['p.pan_code' => $this->data->loanPerson->pan_code, 'p.merchant_id' => $this->data->order->merchant_id])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->groupBy(['o.did'])
            ->asArray()
            ->all();

        return count($data);
    }

    /**
     * 近90天Pan关联的数盟设备ID数量
     * @return int
     */
    public function checkLast90PanMatchSMDeviceIDCnt(){
        if(!$this->data->loanPerson->pan_code){
            return -1;
        }

        $begin_time = $this->data->order->order_time - 90 * 86400;
        $data = UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['o.did'])
            ->where(['p.pan_code' => $this->data->loanPerson->pan_code, 'p.merchant_id' => $this->data->order->merchant_id])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->groupBy(['o.did'])
            ->asArray()
            ->all();

        return count($data);
    }

    /**
     * 该Aadhaar卡号在全平台关联的不同数盟设备ID数量
     * @return int
     */
    public function checkAadhaarMatchSMDeviceIDCnt(){
        if(!$this->data->loanPerson->aadhaar_md5){
            return -1;
        }

        $data = UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['o.did'])
            ->where(['p.aadhaar_md5' => $this->data->loanPerson->aadhaar_md5, 'p.merchant_id' => $this->data->order->merchant_id])
            ->groupBy(['o.did'])
            ->asArray()
            ->all();

        return count($data);
    }

    /**
     * 近30天内该Aadhaar卡号在全平台关联的不同数盟设备ID数量
     * @return int
     */
    public function checkLast30AadhaarMatchSMDeviceIDCntAllPlat(){
        if(!$this->data->loanPerson->aadhaar_md5){
            return -1;
        }

        $begin_time = $this->data->order->order_time - 30 * 86400;
        $data = UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['o.did'])
            ->where(['p.aadhaar_md5' => $this->data->loanPerson->aadhaar_md5, 'p.merchant_id' => $this->data->order->merchant_id])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->groupBy(['o.did'])
            ->asArray()
            ->all();

        return count($data);
    }

    /**
     * 近60天内该Aadhaar卡号在全平台关联的不同数盟设备ID数量
     * @return int
     */
    public function checkLast60AadhaarMatchSMDeviceIDCntAllPlat(){
        if(!$this->data->loanPerson->aadhaar_md5){
            return -1;
        }

        $begin_time = $this->data->order->order_time - 60 * 86400;
        $data = UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['o.did'])
            ->where(['p.aadhaar_md5' => $this->data->loanPerson->aadhaar_md5, 'p.merchant_id' => $this->data->order->merchant_id])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->groupBy(['o.did'])
            ->asArray()
            ->all();

        return count($data);
    }

    /**
     * 近90天内该Aadhaar卡号在全平台关联的不同数盟设备ID数量
     * @return int
     */
    public function checkLast90AadhaarMatchSMDeviceIDCnt(){
        if(!$this->data->loanPerson->aadhaar_md5){
            return -1;
        }

        $begin_time = $this->data->order->order_time - 90 * 86400;
        $data = UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['o.did'])
            ->where(['p.aadhaar_md5' => $this->data->loanPerson->aadhaar_md5, 'p.merchant_id' => $this->data->order->merchant_id])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->groupBy(['o.did'])
            ->asArray()
            ->all();

        return count($data);
    }

    /**
     * 该手机号在全平台关联的不同数盟设备ID数量
     * @return int
     */
    public function checkPhoneMatchSMDeviceIDCnt(){
        if(!$this->data->loanPerson->phone){
            return -1;
        }

        $data = UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['o.did'])
            ->where(['p.phone' => $this->data->loanPerson->phone, 'p.merchant_id' => $this->data->order->merchant_id])
            ->groupBy(['o.did'])
            ->asArray()
            ->all();

        return count($data);
    }

    /**
     * 近30天内该手机号在全平台关联的不同数盟设备ID数量
     * @return int
     */
    public function checkLast30PhoneMatchSMDeviceIDCntAllPlat(){
        if(!$this->data->loanPerson->phone){
            return -1;
        }

        $begin_time = $this->data->order->order_time - 30 * 86400;
        $data = UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['o.did'])
            ->where(['p.phone' => $this->data->loanPerson->phone, 'p.merchant_id' => $this->data->order->merchant_id])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->groupBy(['o.did'])
            ->asArray()
            ->all();

        return count($data);
    }

    /**
     * 近60天内该手机号在全平台关联的不同数盟设备ID数量
     * @return int
     */
    public function checkLast60PhoneMatchSMDeviceIDCntAllPlat(){
        if(!$this->data->loanPerson->phone){
            return -1;
        }

        $begin_time = $this->data->order->order_time - 60 * 86400;
        $data = UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['o.did'])
            ->where(['p.phone' => $this->data->loanPerson->phone, 'p.merchant_id' => $this->data->order->merchant_id])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->groupBy(['o.did'])
            ->asArray()
            ->all();

        return count($data);
    }

    /**
     * 近90天内该手机号在全平台关联的不同数盟设备ID数量
     * @return int
     */
    public function checkLast90PhoneMatchSMDeviceIDCnt(){
        if(!$this->data->loanPerson->phone){
            return -1;
        }

        $begin_time = $this->data->order->order_time - 90 * 86400;
        $data = UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['o.did'])
            ->where(['p.phone' => $this->data->loanPerson->phone, 'p.merchant_id' => $this->data->order->merchant_id])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->groupBy(['o.did'])
            ->asArray()
            ->all();

        return count($data);
    }

    /**
     * 屏幕分辨率是否大于等于1080*720的标准
     * @return int
     */
    public function checkIsScreenResolutionOver1080And720(){
        $data = json_decode($this->data->order->client_info, true);
        if(!isset($data['screenHeight']) || !isset($data['screenWidth'])){
            return -1;
        }
        $arr = [$data['screenHeight'],$data['screenWidth']];
        if(max($arr) >= 1080 && min($arr) >= 720){
            return 1;
        }
        return 0;
    }

    /**
     * Accuauth-AadhaarOCR返回的城市地址是否命中准入地区白名单
     * @return int
     */
    public function checkAadhaarOCRAddressHitWhiteList(){
        if (empty($this->data->userAadhaarReport->address)) {
            return -1;
        }

        $address = $this->data->userAadhaarReport->address;
        foreach ($this->addressWhiteList as $value) {
            if(stripos($address,$value) !== false){
                return 1;
            }
        }

        foreach ($this->addressWhiteList2 as $value) {
            if(stripos($address,$value) !== false){
                return 2;
            }
        }
        return 0;
    }

    /**
     * 填写的姓名跟PanOCR返回的姓名模糊匹配的结果
     * @return int
     */
    public function checkNameMatchResultOfFillAndPanOCR(){
        $full_name = $this->data->userBasicInfo->full_name;
        if(empty($this->data->userPanReport->full_name) || !$full_name){
            return -1;
        }

        $ocr_name = $this->data->userPanReport->full_name;

        return $this->nameDiff($full_name,$ocr_name);
    }

    /**
     * 填写的姓名跟Pan验真返回的姓名模糊匹配的结果
     * @return int
     */
    public function checkNameMatchResultOfFillAndPanVertify(){
        $full_name = $this->data->userBasicInfo->full_name;
        if(!$this->data->userPanVerifyReport || !$full_name){
            return -1;
        }

        $pan_name = $this->data->userPanVerifyReport->full_name;

        return $this->nameDiff($full_name,$pan_name);
    }

    /**
     * 填写的姓名跟AadhaarOCR返回的姓名模糊匹配的结果
     * @return int
     */
    public function checkNameMatchResultOfFillAndAadhaarOCR(){
        $full_name = $this->data->userBasicInfo->full_name;
        if(empty($this->data->userAadhaarReport->full_name) || !$full_name){
            return -1;
        }

        $ocr_name = $this->data->userAadhaarReport->full_name;

        return $this->nameDiff($full_name,$ocr_name);
    }


    public function nameDiff($name1,$name2){
        preg_match_all('/\S+/', $name1, $name1_new);
        preg_match_all('/\S+/', $name2, $name2_new);

        $count = 0;
        foreach ($name1_new[0] as $v){
            foreach ($name2_new[0] as $val){
                if(strtoupper($v) == strtoupper($val)){
                    $count++;
                    break;
                }
            }
        }

        return $count;
    }


    /**
     * PanOCR出来的卡号与填写的Pan卡号一致的位数
     * @return int
     */
    public function checkSameCntOfPanOCRAndFill(){
        $pan_log = UserPanCheckLog::find()->where(['user_id' => $this->data->loanPerson->id])->orderBy(['id' => SORT_DESC])->one();
        if(empty($pan_log)){
            return -1;
        }
        if(strlen($pan_log['pan_input']) == 10 && strlen($pan_log['pan_ocr']) == 10){
            $count = 0;
            for ($i = 0; $i < 10; $i++){
                if($pan_log['pan_input'][$i] == $pan_log['pan_ocr'][$i]){
                    $count++;
                }
            }
            return $count;
        }

        return -2;
    }

    /**
     * 全平台人脸比对分
     * @return int
     */
    public function checkAllPlatformFaceComparisonScore(){
        if(!$this->data->userFrCompareReport || $this->data->userFrCompareReport->report_status == 0){
            return -1;
        }
        if($this->data->userFrCompareReport->data_status == 'OK'){
            return $this->data->userFrCompareReport->score;
        }

        return -2;
    }

    /**
     * 全平台人脸比对报告类型
     * @return int
     */
    public function checkAllPlatformFaceComparisonType(){
        if(!$this->data->userFrCompareReport){
            return -1;
        }

        if($this->data->userFrCompareReport->report_type == 1){
            return 2;
        }else{
            return 1;
        }
    }

    /**
     * 人脸对比的服务来源
     * @return int
     */
    public function checkSourceOfFaceCompare(){
        if(!$this->data->userFrCompareReport){
            return -1;
        }

        if($this->data->userFrCompareReport->type == UserCreditReportFrVerify::SOURCE_ACCUAUTH){
            return 1;
        }

        if($this->data->userFrCompareReport->type == UserCreditReportFrVerify::SOURCE_ADVANCE){
            return 2;
        }

        return -1;
    }

    /**
     * 活体检测的分数
     * @return int
     */
    public function checkScoreOFLivenessDetect(){
        if(!$this->data->userFrReport || $this->data->userFrReport->report_status == 0){
            return -1;
        }

        if($this->data->userFrReport->data_status == 'OK'){
            return $this->data->userFrReport->score;
        }

        return -2;
    }

    /**
     * 多个生日的年份核对是否一致
     * @return int
     */
    public function checkIsYOBSame(){
        if($this->data->userPanReport && $this->data->userBasicInfo->birthday){
            $pan_date = str_replace(['/', ' '], '-', $this->data->userPanReport->date_info);
            $pan_year = date('Y', strtotime($pan_date));
            if($pan_year == date('Y', strtotime($this->data->userBasicInfo->birthday))){
                return 1;
            }

            return 0;
        }
        return -1;
    }

    /**
     * 数盟设备ID历史申请次数
     * @return int
     */
    public function checkHisSMDeviceIDApplyCnt(){
        if(empty($this->data->order->did)){
            return -1;
        }
        $arr = UserLoanOrder::find()
            ->where(['did' => $this->data->order->did, 'merchant_id' => $this->data->order->merchant_id])->all();
        return count($arr);
    }

    /**
     * 近90天数盟设备ID申请次数
     * @return int
     */
    public function checkLast90ApplyCntBySMDeviceID(){
        if(empty($this->data->order->did)){
            return -1;
        }
        $begin_time = $this->data->order->order_time - 90 * 86400;
        $arr = UserLoanOrder::find()
            ->where(['did' => $this->data->order->did, 'merchant_id' => $this->data->order->merchant_id])
            ->andWhere(['>=', 'order_time', $begin_time])->all();
        return count($arr);
    }

    /**
     * 数盟设备ID历史申请被拒次数
     * @return int
     */
    public function checkHisSMDeviceIDApplyRejectCnt(){
        if(empty($this->data->order->did)){
            return -1;
        }
        $arr = UserLoanOrder::find()
            ->where(['did' => $this->data->order->did, 'merchant_id' => $this->data->order->merchant_id])
            ->andWhere(['<', 'status', 0])->all();
        return count($arr);
    }

    /**
     * 近90天数盟设备ID申请被拒次数
     * @return int
     */
    public function checkLast90RejectCntBySMDeviceID(){
        if(empty($this->data->order->did)){
            return -1;
        }
        $begin_time = $this->data->order->order_time - 90 * 86400;
        $arr = UserLoanOrder::find()
            ->where(['did' => $this->data->order->did, 'merchant_id' => $this->data->order->merchant_id])
            ->andWhere(['>=', 'order_time', $begin_time])
            ->andWhere(['<', 'status', 0])->all();
        return count($arr);
    }


    /**
     * 数盟设备ID关联的手机号数量
     * @return int
     */
    public function checkSMDeviceIDMatchPhoneCnt(){
        if(!$this->data->order->did){
            return -1;
        }
        $data = UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['p.phone'])
            ->where(['o.did' => $this->data->order->did, 'o.merchant_id' => $this->data->order->merchant_id])
            ->groupBy(['p.phone'])
            ->asArray()
            ->all();

        return count($data);
    }

    /**
     * 近30天内该数盟设备ID在全平台关联的不同手机号数量
     * @return int
     */
    public function checkLast30SMDeviceIDMatchPhoneCntAllPlat(){
        if(!$this->data->order->did){
            return -1;
        }

        $begin_time = $this->data->order->order_time - 30 * 86400;
        $data = UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['p.phone'])
            ->where(['o.did' => $this->data->order->did, 'o.merchant_id' => $this->data->order->merchant_id])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->groupBy(['p.phone'])
            ->asArray()
            ->all();

        return count($data);
    }

    /**
     * 近60天内该数盟设备ID在全平台关联的不同手机号数量
     * @return int
     */
    public function checkLast60SMDeviceIDMatchPhoneCntAllPlat(){
        if(!$this->data->order->did){
            return -1;
        }

        $begin_time = $this->data->order->order_time - 60 * 86400;
        $data = UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['p.phone'])
            ->where(['o.did' => $this->data->order->did, 'o.merchant_id' => $this->data->order->merchant_id])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->groupBy(['p.phone'])
            ->asArray()
            ->all();

        return count($data);
    }

    /**
     * 近90天数盟设备ID关联的手机号数量
     * @return int
     */
    public function checkLast90SMDeviceIDMatchPhoneCnt(){
        if(!$this->data->order->did){
            return -1;
        }

        $begin_time = $this->data->order->order_time - 90 * 86400;
        $data = UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['p.phone'])
            ->where(['o.did' => $this->data->order->did, 'o.merchant_id' => $this->data->order->merchant_id])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->groupBy(['p.phone'])
            ->asArray()
            ->all();

        return count($data);
    }

    /**
     * 数盟设备ID关联的Aadhaar数量
     * @return int
     */
    public function checkSMDeviceIDMatchAahdaarCnt(){
        if(!$this->data->order->did){
            return -1;
        }
        $data = UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['p.aadhaar_md5'])
            ->where(['o.did' => $this->data->order->did, 'o.merchant_id' => $this->data->order->merchant_id])
            ->groupBy(['p.aadhaar_md5'])
            ->asArray()
            ->all();

        return count($data);
    }

    /**
     * 近30天内该数盟设备ID在全平台关联的不同Aadhaar卡号数量
     * @return int
     */
    public function checkLast30SMDeviceIDMatchAahdaarCntAllPlat(){
        if(!$this->data->order->did){
            return -1;
        }

        $begin_time = $this->data->order->order_time - 30 * 86400;
        $data = UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['p.aadhaar_md5'])
            ->where(['o.did' => $this->data->order->did, 'o.merchant_id' => $this->data->order->merchant_id])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->groupBy(['p.aadhaar_md5'])
            ->asArray()
            ->all();

        return count($data);
    }

    /**
     * 近60天内该数盟设备ID在全平台关联的不同Aadhaar卡号数量
     * @return int
     */
    public function checkLast60SMDeviceIDMatchAahdaarCntAllPlat(){
        if(!$this->data->order->did){
            return -1;
        }

        $begin_time = $this->data->order->order_time - 60 * 86400;
        $data = UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['p.aadhaar_md5'])
            ->where(['o.did' => $this->data->order->did, 'o.merchant_id' => $this->data->order->merchant_id])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->groupBy(['p.aadhaar_md5'])
            ->asArray()
            ->all();

        return count($data);
    }

    /**
     * 近90天数盟设备ID关联的Aadhaar数量
     * @return int
     */
    public function checkLast90SMDeviceIDMatchAahdaarCnt(){
        if(!$this->data->order->did){
            return -1;
        }

        $begin_time = $this->data->order->order_time - 90 * 86400;
        $data = UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['p.aadhaar_md5'])
            ->where(['o.did' => $this->data->order->did, 'o.merchant_id' => $this->data->order->merchant_id])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->groupBy(['p.aadhaar_md5'])
            ->asArray()
            ->all();

        return count($data);
    }


    /**
     * 数盟设备ID关联的设备号IMEI的数量
     * @return int
     */
    public function checkSMDeviceIDMatchDeviceIMEICnt(){
        if(!$this->data->order->did){
            return -1;
        }

        $data = UserLoanOrder::find()
            ->where(['did' => $this->data->order->did, 'merchant_id' => $this->data->order->merchant_id])
            ->groupBy(['device_id'])->all();

        return count($data);
    }

    /**
     * 总平台历史该数盟设备ID关联的手机序列号IMEI的数量
     * @return int
     */
    public function checkHisSMDeviceIDMatchImeiCntTotPlatform(){
        if(!$this->data->order->did){
            return -1;
        }

        $data = ArrayHelper::getColumn(UserLoanOrder::find()
            ->where(['did' => $this->data->order->did])
            ->andWhere(['!=', 'device_id', ''])
            ->andWhere(['is not', 'device_id', null])
            ->groupBy(['device_id'])->all(),
            'device_id');

        $data_loan = ArrayHelper::getColumn(UserLoanOrder::find()
            ->where(['did' => $this->data->order->did])
            ->andWhere(['!=', 'device_id', ''])
            ->andWhere(['is not', 'device_id', null])
            ->groupBy(['device_id'])->all(Yii::$app->db_loan),
            'device_id');

        return count(array_unique(array_merge($data, $data_loan)));
    }

    /**
     * 用户在每次下单时的不同数盟设备ID的数量
     * @return int
     */
    public function checkSMDeviceIDOfOrderCnt(){
        $userIds = $this->getPanUserIds($this->data->loanPerson->pan_code);
        $data = ClientInfoLog::find()
            ->where(['user_id' => $userIds,'event' => ClientInfoLog::EVENT_APPLY_ORDER])
            ->groupBy(['szlm_query_id'])
            ->all(\Yii::$app->db_read_1);
        return count($data);
    }

    /**
     * 近90天用户在每次下单时的不同数盟设备ID的数量
     * @return int
     */
    public function checkLast90MSMDeviceIDOfOrderCnt(){
        $userIds = $this->getPanUserIds($this->data->loanPerson->pan_code);
        $begin_time = $this->data->order->order_time - 90 * 86400;
        $data = ClientInfoLog::find()
            ->where(['user_id' => $userIds,'event' => ClientInfoLog::EVENT_APPLY_ORDER])
            ->andWhere(['>=', 'created_at', $begin_time])
            ->groupBy(['szlm_query_id'])
            ->all(\Yii::$app->db_read_1);
        return count($data);
    }

    /**
     * 随机数1
     * @return int
     */
    public function checkRandomNumber1(){
        return rand(1, 100);
    }

    /**
     * Accuauth-AadhaarOCR返回的邮编是否命中准入地区邮编
     * @return int
     */
    public function checkAadhaarOCRPostHitWhiteAddressPost(){
        if (!$this->data->userAadhaarReport || empty($this->data->userAadhaarReport->pin)) {
            return -1;
        }

        $pin = $this->data->userAadhaarReport->pin;
        if(in_array($pin, Pin::$whiteList)){
            return 1;
        }

        if(in_array($pin, Pin::$whiteList2)){
            return 2;
        }
        return 0;
    }

    /**
     * 近30天内接收提醒还款的短信数量
     * @return int
     */
    public function checkRepayRemindReceivedSMSCntLast30Days(){
        $begin_time = $this->data->order->order_time - 30 * 86400;
        if($this->data->order->is_export == UserLoanOrder::IS_EXPORT_YES) {
            $userId = $this->getUserOtherId();
            $sms = MgUserMobileSms::find()
                ->select(['messageContent'])
                ->where(['user_id' => $userId, 'type' => 1])
                ->andWhere(['>=', 'messageDate',$begin_time])
                ->asArray()
                ->all(Yii::$app->mongodb_loan);
        }else{
            $sms = MgUserMobileSms::find()
                ->select(['messageContent'])
                ->where(['user_id' => intval($this->data->order->user_id), 'type' => 1])
                ->andWhere(['>=', 'messageDate',$begin_time])
                ->asArray()
                ->all();
        }

        $count = 0;
        foreach ($sms as $v){
            foreach ($this->repaymentList as $value){
                if(stripos($v['messageContent'], $value) !== false){
                    $count++;
                    break;
                }
            }
        }

        return $count;
    }

    /**
     * 发出短信信息文本命中毒、赌、黑或传销等不良产业的敏感词的短信数量
     * @return int
     */
    public function checkSensitiveWordSentSMSCnt(){
        if($this->data->order->is_export == UserLoanOrder::IS_EXPORT_YES) {
            $userId = $this->getUserOtherId();
            $sms = MgUserMobileSms::find()->where(['user_id' => $userId, 'type' => 2])
                ->select(['messageContent'])
                ->asArray()
                ->all(Yii::$app->mongodb_loan);
        }else{
            $sms = MgUserMobileSms::find()->where(['user_id' => intval($this->data->order->user_id), 'type' => 2])
                ->select(['messageContent'])
                ->asArray()
                ->all();
        }

        $count = 0;
        $arr = array_merge($this->pyramidWords, $this->drugsWords, $this->gamblingWords, $this->blackWords);
        foreach ($sms as $v){
            foreach ($arr as $value){
                $vList = explode(' ', $value);
                $flag = true;
                foreach ($vList as $val){
                    if(stripos($v['messageContent'], $val) === false){
                        $flag = false;
                        break;
                    }
                }
                if($flag){
                    $count++;
                    break;
                }
            }
        }

        return $count;
    }

    /**
     * 近6个月内接收关于法院诉讼或公安案件的短信数量
     * @return int
     */
    public function checkLawPoliceReceivedSMSCnt(){
        $begin_time = strtotime('-6 month', $this->data->order->order_time);
        if($this->data->order->is_export == UserLoanOrder::IS_EXPORT_YES){
            $user_id = $this->getUserOtherId();
            $sms = MgUserMobileSms::find()
                ->select(['messageContent'])
                ->where(['user_id' => $user_id, 'type' => 1])
                ->andWhere(['>=', 'messageDate',$begin_time])
                ->asArray()
                ->all(Yii::$app->mongodb_loan);
        }else{
            $sms = MgUserMobileSms::find()
                ->select(['messageContent'])
                ->where(['user_id' => intval($this->data->order->user_id), 'type' => 1])
                ->andWhere(['>=', 'messageDate',$begin_time])
                ->asArray()
                ->all();
        }

        $count = 0;
        foreach ($sms as $v){
            foreach ($this->lawPoliceList as $value){
                $vList = explode(' ', $value);
                $flag = true;
                foreach ($vList as $val){
                    if(stripos($v['messageContent'], $val) === false){
                        $flag = false;
                        break;
                    }
                }
                if($flag){
                    $count++;
                    break;
                }
            }
        }

        return $count;
    }

    /**
     * 近30天内紧急联系人为互通联系人人数
     * @return int
     */
    public function checkMutualCallContactCntLast30Days(){
        $begin_time = strtotime('-30 day', $this->data->order->order_time);

        if(empty($this->data->userContact->phone) || empty($this->data->userContact->other_phone)){
            return -1;
        }

        $phone_arr = array_merge(explode(':', $this->data->userContact->phone), explode(':', $this->data->userContact->other_phone));
        $phone = [];
        foreach ($phone_arr as $value){
            $phone[] = substr($value, -10);
        }

        $phone = array_unique($phone);

        if($this->data->order->is_export == UserLoanOrder::IS_EXPORT_YES){
            $user_id = $this->getUserOtherId();
            $data = MgUserCallReports::find()
                ->select(['callNumber'])
                ->where(['user_id' => $user_id, 'callType' => [1, 2]])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->asArray()
                ->column(Yii::$app->mongodb_loan);
        }else{
            $data = MgUserCallReports::find()
                ->select(['callNumber'])
                ->where(['user_id' => intval($this->data->order->user_id), 'callType' => [1, 2]])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->asArray()
                ->column();
        }

        if(empty($data)){
            return 0;
        }
        $count = 0;
        foreach ($phone as $v){
            foreach ($data as $val){
                if(substr($val, -10) == $v){
                    $count++;
                    break;
                }
            }
        }

        return $count;
    }

    /**
     * 近30天内的总通话次数（不区分主被叫）
     * @return int
     */
    public function checkTotalDialCalledCntLast30Days(){
        $begin_time = strtotime('-30 day', $this->data->order->order_time);

        if($this->data->order->is_export == UserLoanOrder::IS_EXPORT_YES){
            $userId = $this->getUserOtherId();
            $data = MgUserCallReports::find()
                ->select(['callNumber'])
                ->where(['user_id' => $userId, 'callType' => [1, 2]])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->column(Yii::$app->mongodb_loan);
        }else{
            $data = MgUserCallReports::find()
                ->select(['callNumber'])
                ->where(['user_id' => intval($this->data->order->user_id), 'callType' => [1, 2]])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->column();
        }

        return count($data);
    }

    /**
     * 近30天内的总通话时长(s)
     * @return int
     */
    public function checkTotalDialCalledDurationLast30Days(){
        $begin_time = strtotime('-30 day', $this->data->order->order_time);

        if($this->data->order->is_export == UserLoanOrder::IS_EXPORT_YES){
            $userId = $this->getUserOtherId();
            $data = MgUserCallReports::find()
                ->select(['callDuration'])
                ->where(['user_id' => $userId, 'callType' => [1, 2]])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->column(Yii::$app->mongodb_loan);
        }else{
            $data = MgUserCallReports::find()
                ->select(['callDuration'])
                ->where(['user_id' => intval($this->data->order->user_id), 'callType' => [1, 2]])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->column();
        }

        return array_sum($data);
    }

    /**
     * 近30天内1-5点的通话次数
     * @return int
     */
    public function checkTotal1amTo5amDialCalledCntLast30Days(){
        $begin_time = strtotime('-30 day', $this->data->order->order_time);

        if($this->data->order->is_export == UserLoanOrder::IS_EXPORT_YES) {
            $userId = $this->getUserOtherId();
            $data = MgUserCallReports::find()
                ->select(['callDateTime'])
                ->where(['user_id' => $userId, 'callType' => [1, 2]])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->column(Yii::$app->mongodb_loan);
        }else{
            $data = MgUserCallReports::find()
                ->select(['callDateTime'])
                ->where(['user_id' => intval($this->data->order->user_id), 'callType' => [1, 2]])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->column();
        }
        $count = 0;
        foreach ($data as $v){
            $hour = date('H',$v);
            if($hour >= 1 && $hour < 5){
                $count++;
            }
        }
        return $count;
    }

    /**
     * 近30天内1-5点的通话次数占比
     * @return int
     */
    public function checkTotal1amTo5amDialCalledCntRatioLast30Days(){
        $count = $this->checkTotalDialCalledCntLast30Days();

        if($count == 0){
            return 0;
        }

        $count_1 = $this->checkTotal1amTo5amDialCalledCntLast30Days();

        return round($count_1 / $count * 100);

    }

    /**
     * 近30天内通话的去重号码数
     * @return int
     */
    public function checkTotalDialCalledMobileCntLast30Days(){
        $begin_time = strtotime('-30 day', $this->data->order->order_time);

        if($this->data->order->is_export == UserLoanOrder::IS_EXPORT_YES){
            $userId = $this->getUserOtherId();
            $data = MgUserCallReports::find()
                ->select(['callNumber'])
                ->where(['user_id' => $userId, 'callType' => [1, 2]])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->column(Yii::$app->mongodb_loan);
        }else{
            $data = MgUserCallReports::find()
                ->select(['callNumber'])
                ->where(['user_id' => intval($this->data->order->user_id), 'callType' => [1, 2]])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->column();
        }

        $phone_arr = [];
        foreach ($data as $v){
            $phone_arr[] = substr($v, -10);
        }

        return count(array_unique($phone_arr));
    }

    /**
     * 近30天内1-5点通话的去重号码数
     * @return int
     */
    public function checkTotal1amTo5amDialCalledMobileCntLast30Days(){
        $begin_time = strtotime('-30 day', $this->data->order->order_time);

        if($this->data->order->is_export == UserLoanOrder::IS_EXPORT_YES) {
            $userId = $this->getUserOtherId();
            $data = MgUserCallReports::find()
                ->select(['callDateTime','callNumber'])
                ->where(['user_id' => $userId, 'callType' => [1, 2]])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->all(Yii::$app->mongodb_loan);
        }else{
            $data = MgUserCallReports::find()
                ->select(['callDateTime','callNumber'])
                ->where(['user_id' => intval($this->data->order->user_id), 'callType' => [1, 2]])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->all();
        }

        $phone_arr = [];
        foreach ($data as $v){
            $hour = date('H',$v['callDateTime']);
            if($hour >= 1 && $hour < 5){
                $phone_arr[] = substr($v['callNumber'], -10);
            }
        }

        return count(array_unique($phone_arr));
    }

    /**
     * 近30天内1-5点通话的去重号码数占比=158/157
     * @return int
     */
    public function checkTotal1amTo5amDialCalledMobileCntRatioLast30Days(){
        $count = $this->checkTotalDialCalledMobileCntLast30Days();

        if($count == 0){
            return 0;
        }

        $count_1 = $this->checkTotal1amTo5amDialCalledMobileCntLast30Days();

        return round($count_1 / $count * 100);

    }

    /**
     * 近30天内1-5点的呼出次数
     * @return int
     */
    public function checkTotal1amTo5amDialCntLast30Days(){
        $begin_time = strtotime('-30 day', $this->data->order->order_time);

        if($this->data->order->is_export == UserLoanOrder::IS_EXPORT_YES) {
            $userId = $this->getUserOtherId();
            $data = MgUserCallReports::find()
                ->select(['callDateTime'])
                ->where(['user_id' => $userId, 'callType' => 2])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->column(Yii::$app->mongodb_loan);

        }else{
            $data = MgUserCallReports::find()
                ->select(['callDateTime'])
                ->where(['user_id' => intval($this->data->order->user_id), 'callType' => 2])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->column();
        }

        $count = 0;
        foreach ($data as $v){
            $hour = date('H',$v);
            if($hour >= 1 && $hour < 5){
                $count++;
            }
        }
        return $count;
    }

    /**
     * 近30天内1-5点呼出的去重号码数
     * @return int
     */
    public function checkTotal1amTo5amDialMobileCntLast30Days(){
        $begin_time = strtotime('-30 day', $this->data->order->order_time);

        if($this->data->order->is_export == UserLoanOrder::IS_EXPORT_YES) {
            $userId = $this->getUserOtherId();
            $data = MgUserCallReports::find()
                ->select(['callDateTime','callNumber'])
                ->where(['user_id' => $userId, 'callType' => 2])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->all(Yii::$app->mongodb_loan);

        }else{
            $data = MgUserCallReports::find()
                ->select(['callDateTime','callNumber'])
                ->where(['user_id' => intval($this->data->order->user_id), 'callType' => 2])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->all();
        }

        $phone_arr = [];
        foreach ($data as $v){
            $hour = date('H',$v['callDateTime']);
            if($hour >= 1 && $hour < 5){
                $phone_arr[] = substr($v['callNumber'], -10);
            }
        }

        return count(array_unique($phone_arr));
    }

    /**
     * 近30天内通话时长为(0s,5s]的通话次数
     * @return int
     */
    public function checkTotalLessThan5sDialCalledCntLast30Days(){
        $begin_time = strtotime('-30 day', $this->data->order->order_time);

        if($this->data->order->is_export == UserLoanOrder::IS_EXPORT_YES) {
            $userId = $this->getUserOtherId();
            $data = MgUserCallReports::find()
                ->select(['callDateTime'])
                ->where(['user_id' => $userId, 'callType' => [1, 2]])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->andWhere(['<=', 'callDuration', 5])
                ->asArray()
                ->column(Yii::$app->mongodb_loan);
        }else{
            $data = MgUserCallReports::find()
                ->select(['callDateTime'])
                ->where(['user_id' => intval($this->data->order->user_id), 'callType' => [1, 2]])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->andWhere(['<=', 'callDuration', 5])
                ->asArray()
                ->column();
        }

        return count($data);
    }

    /**
     * 近30天内通话时长为(0s,5s]的通话次数占比=162/153
     * @return int
     */
    public function checkTotalLessThan5sDialCalledCntRatioLast30Days(){
        $count = $this->checkTotalDialCalledCntLast30Days();

        if($count == 0){
            return 0;
        }

        $count_1 = $this->checkTotalLessThan5sDialCalledCntLast30Days();

        return round($count_1 / $count * 100);

    }

    /**
     * 近30天内最大通话时长为(0s,5s]的去重号码数
     * @return int
     */
    public function checkTotalMaxDurLessThan5sDialCalledMobileCntLast30Days(){
        $begin_time = strtotime('-30 day', $this->data->order->order_time);

        if($this->data->order->is_export == UserLoanOrder::IS_EXPORT_YES) {
            $userId = $this->getUserOtherId();
            $data = MgUserCallReports::find()
                ->select(['callNumber'])
                ->where(['user_id' => $userId, 'callType' => [1, 2]])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->andWhere(['<=', 'callDuration', 5])
                ->asArray()
                ->column(Yii::$app->mongodb_loan);
        }else{
            $data = MgUserCallReports::find()
                ->select(['callNumber'])
                ->where(['user_id' => intval($this->data->order->user_id), 'callType' => [1, 2]])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->andWhere(['<=', 'callDuration', 5])
                ->asArray()
                ->column();
        }

        $phone_arr = [];
        foreach ($data as $v){
            $phone_arr[] = substr($v, -10);
        }

        return count(array_unique($phone_arr));
    }


    /**
     * 近30天内最大通话时长为(0s,5s]的去重号码数占比=164/157
     * @return int
     */
    public function checkTotalMaxDurLessThan5sDialCalledMobileCntRatioLast30Days(){
        $count = $this->checkTotalDialCalledMobileCntLast30Days();

        if($count == 0){
            return 0;
        }

        $count_1 = $this->checkTotalMaxDurLessThan5sDialCalledMobileCntLast30Days();

        return round($count_1 / $count * 100);

    }

    /**
     * 近30天内呼出时长为(0s,5s]的通话次数
     * @return int
     */
    public function checkTotalLessThan5sDialCntLast30Days(){
        $begin_time = strtotime('-30 day', $this->data->order->order_time);

        if($this->data->order->is_export == UserLoanOrder::IS_EXPORT_YES) {
            $userId = $this->getUserOtherId();
            $data = MgUserCallReports::find()
                ->select(['callDateTime'])
                ->where(['user_id' => $userId, 'callType' => 2])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->andWhere(['<=', 'callDuration', 5])
                ->asArray()
                ->column(Yii::$app->mongodb_loan);
        }else{
            $data = MgUserCallReports::find()
                ->select(['callDateTime'])
                ->where(['user_id' => intval($this->data->order->user_id), 'callType' => 2])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->andWhere(['<=', 'callDuration', 5])
                ->asArray()
                ->column();
        }

        return count($data);
    }

    /**
     * 近30天内呼出时长为(0s,5s]的去重号码数
     * @return int
     */
    public function checkTotalLessThan5sDialMobileCntLast30Days(){
        $begin_time = strtotime('-30 day', $this->data->order->order_time);

        if($this->data->order->is_export == UserLoanOrder::IS_EXPORT_YES) {
            $userId = $this->getUserOtherId();
            $data = MgUserCallReports::find()
                ->select(['callNumber'])
                ->where(['user_id' => $userId, 'callType' => 2])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->andWhere(['<=', 'callDuration', 5])
                ->asArray()
                ->column(Yii::$app->mongodb_loan);
        }else{
            $data = MgUserCallReports::find()
                ->select(['callNumber'])
                ->where(['user_id' => intval($this->data->order->user_id), 'callType' => 2])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->andWhere(['<=', 'callDuration', 5])
                ->asArray()
                ->column();
        }

        $phone_arr = [];
        foreach ($data as $v){
            $phone_arr[] = substr($v, -10);
        }

        return count(array_unique($phone_arr));
    }

    /**
     * 近3个月内的月均呼出的去重号码数
     * @return int
     */
    public function checkAvgByMonthDialMobileCntLast3Months(){
        $begin_time = strtotime('-3 month', $this->data->order->order_time);

        if($this->data->order->is_export == UserLoanOrder::IS_EXPORT_YES) {
            $userId = $this->getUserOtherId();
            $data = MgUserCallReports::find()
                ->select(['callNumber','callDateTime'])
                ->where(['user_id' => $userId, 'callType' => 2])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->all(Yii::$app->mongodb_loan);
        }else{
            $data = MgUserCallReports::find()
                ->select(['callNumber','callDateTime'])
                ->where(['user_id' => intval($this->data->order->user_id), 'callType' => 2])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->all();
        }

        $phone_arr = [];
        $month = [];
        foreach ($data as $v){
            $phone_arr[] = substr($v['callNumber'], -10);
            $month[] = date('Y-m', $v['callDateTime']);
        }

        if(empty($month)){
            return 0;
        }

        return ceil(count(array_unique($phone_arr)) / min(count(array_unique($month)),3));
    }

    /**
     * 近6个月内的去重互通号码数量
     * 近6个月内互通（该号码与本机号码既有主叫、也有被叫记录）号码去重后的数量(不包括未接通的电话，即不包含通话时常为0秒的数据)
     * @return int
     */
    public function checkMutualCallMobileCntLast6Months(){
        $begin_time = strtotime('-6 month', $this->data->order->order_time);

        if($this->data->order->is_export == UserLoanOrder::IS_EXPORT_YES) {
            $userId = $this->getUserOtherId();
            $data = MgUserCallReports::find()
                ->select(['callNumber'])
                ->where(['user_id' => $userId, 'callType' => 2])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->column(Yii::$app->mongodb_loan);

            $data1 = MgUserCallReports::find()
                ->select(['callNumber'])
                ->where(['user_id' => $userId, 'callType' => 1])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->column(Yii::$app->mongodb_loan);

        }else{
            $data = MgUserCallReports::find()
                ->select(['callNumber'])
                ->where(['user_id' => intval($this->data->order->user_id), 'callType' => 2])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->column();

            $data1 = MgUserCallReports::find()
                ->select(['callNumber'])
                ->where(['user_id' => intval($this->data->order->user_id), 'callType' => 1])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->column();
        }

        $zj_phone_arr = [];
        foreach ($data as $v){
            $zj_phone_arr[] = substr($v, -10);
        }

        $bj_phone_arr = [];
        foreach ($data1 as $v){
            $bj_phone_arr[] = substr($v, -10);
        }

        return count(array_intersect(array_unique($zj_phone_arr), array_unique($bj_phone_arr)));
    }

    /**
     * 近6个月内通话记录主叫次数
     * @return int
     */
    public function checkDialRatioLast6Months(){
        $begin_time = strtotime('-6 month', $this->data->order->order_time);

        if($this->data->order->is_export == UserLoanOrder::IS_EXPORT_YES) {
            $userId = $this->getUserOtherId();
            $data = MgUserCallReports::find()
                ->select(['callNumber'])
                ->where(['user_id' => $userId, 'callType' => 2])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->column(Yii::$app->mongodb_loan);
            $data_num = MgUserCallReports::find()
                ->select(['callNumber'])
                ->where(['user_id' => $userId, 'callType' => [1, 2]])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->column(Yii::$app->mongodb_loan);
        }else{
            $data = MgUserCallReports::find()
                ->select(['callNumber'])
                ->where(['user_id' => intval($this->data->order->user_id), 'callType' => 2])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->column();
            $data_num = MgUserCallReports::find()
                ->select(['callNumber'])
                ->where(['user_id' => intval($this->data->order->user_id), 'callType' => [1, 2]])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->column();
        }

        if(empty($data_num)){
            return 0;
        }

        return round(count($data) / count($data_num) * 100);
    }

    /**
     * 近6个月内通话记录被叫次数
     * @return int
     */
    public function checkCalledRatioLast6Months(){
        $begin_time = strtotime('-6 month', $this->data->order->order_time);

        if($this->data->order->is_export == UserLoanOrder::IS_EXPORT_YES) {
            $userId = $this->getUserOtherId();
            $data = MgUserCallReports::find()
                ->select(['callNumber'])
                ->where(['user_id' => $userId, 'callType' => 1])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->column(Yii::$app->mongodb_loan);

            $data_num = MgUserCallReports::find()
                ->select(['callNumber'])
                ->where(['user_id' => $userId, 'callType' => [1, 2]])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->column(Yii::$app->mongodb_loan);
        }else{
            $data = MgUserCallReports::find()
                ->select(['callNumber'])
                ->where(['user_id' => intval($this->data->order->user_id), 'callType' => 1])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->column();

            $data_num = MgUserCallReports::find()
                ->select(['callNumber'])
                ->where(['user_id' => intval($this->data->order->user_id), 'callType' => [1, 2]])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->column();
        }

        if(empty($data_num)){
            return 0;
        }

        return round(count($data) / count($data_num) * 100);
    }

    /**
     * 通话记录爬取是否正常
     * @return int
     */
    public function checkIsCallRecordGrabNormal(){
        if($this->data->order->is_export == UserLoanOrder::IS_EXPORT_YES) {
            $userId = $this->getUserOtherId();
            $data = MgUserCallReports::find()
                ->where(['user_id' => $userId])
                ->orderBy(['created_at' => SORT_DESC])
                ->asArray()
                ->one(Yii::$app->mongodb_loan);
        }else{
            $data = MgUserCallReports::find()
                ->where(['user_id' => intval($this->data->order->user_id)])
                ->orderBy(['created_at' => SORT_DESC])
                ->asArray()
                ->one();
        }

        if(!empty($data)){
            $time = strtotime(date('Y-m-d', $this->data->order->order_time)) - 7 * 86400;
            if($data['created_at'] >= $time){
                return 1;
            }else{
                return 2;
            }
        }

        return 0;
    }

    /**
     * 短信记录爬取是否正常
     * @return int
     */
    public function checkIsSMSRecordGrabNormal(){
        if($this->data->order->is_export == UserLoanOrder::IS_EXPORT_YES) {
            $userId = $this->getUserOtherId();
            $data = MgUserMobileSms::find()
                ->where(['user_id' => $userId])
                ->asArray()
                ->one(Yii::$app->mongodb_loan);
        }else{
            $data = MgUserMobileSms::find()
                ->where(['user_id' => intval($this->data->order->user_id)])
                ->asArray()
                ->one();
        }

        if(!empty($data)){
            return 1;
        }

        return 0;
    }

    /**
     * 调用experian报告
     * @return array
     * @throws \Exception
     */
    protected function getExperianRequest(){
        if($this->isGetData){
            $params = [
                'loanPerson' => $this->data->loanPerson,
                'order' => $this->data->order,
                'retryLimit' => 3,
            ];
            $service = new CreditReportExperianService($params);
            $service->getData();
        }

        return $this->getExperianReport();
    }

    /**
     * 获取experian征信报告
     * @return array
     * @throws \Exception
     */
    protected function getExperianReport()
    {
        if (is_null($this->experianReport)) {
            $this->data->order = UserLoanOrder::findOne($this->data->order->id);
            $xml_result = $this->data->order->userCreditReportExperian->data ?? [];
            if(empty($xml_result)){
                return $this->experianReport = [];
            }
            try {
                $result = simplexml_load_string($xml_result);
                $result = $result->children('http://schemas.xmlsoap.org/soap/envelope/')->Body;
                $result = $result->children('urn:cbv2');
                $result = json_decode(json_encode($result), true);
                $result = json_decode(json_encode(simplexml_load_string($result['processResponse']['out'])), true);

                $this->experianReport = $result;
                $this->experian_updated_at = $this->data->order->userCreditReportExperian->query_time;
            } catch (\Exception $e){
                \Yii::error(['order_id'=>$this->data->order->id,'err_msg'=>$e->getMessage(),'trace'=>$e->getTraceAsString()], 'RiskAutoCheck');
                $this->experianReport = [];
            }
            return $this->experianReport;
        } else {
            return $this->experianReport;
        }
    }

    /**
     * Experian征信报告返回是否正常
     * @return int
     * @throws \Exception
     */
    public function checkIsExperianCreditReportReturnedNormal(){
        $report = $this->getExperianRequest();

        if(isset($report['Header']['SystemCode']) && $report['Header']['SystemCode'] == 0){
            if(!empty($report['UserMessage']['UserMessageText']) && $report['UserMessage']['UserMessageText'] == 'Normal Response'){
                return 1;
            }
            return 0;
        }

        return -1;
    }

    /**
     * Experian中的账户是否有命中负面信息的状态
     * @return int
     * @throws \Exception
     */
    public function checkExperianAccountStatusCntOfClosed(){
        $report = $this->getExperianReport();

        if(empty($report) || empty($report['CAIS_Account']['CAIS_Account_DETAILS'])){
            return -1;
        }

        foreach ($report['CAIS_Account']['CAIS_Account_DETAILS'] as $v){
            if(!empty($v['Account_Status']) && in_array($v['Account_Status'], [93,89,97,53,54,55,56,57,58,59,60,61,62,63,64,65,66,67,68,69,70,72,73,74,75,76,77,79,81,85,86,87,88,94,90,91])){
                return 1;
            }
        }

        return 0;
    }

    /**
     * Experian信贷账户总数
     * @return int
     * @throws \Exception
     */
    public function checkExperianCreditAccountTotal(){
        $report = $this->getExperianReport();

        if(empty($report) || empty($report['CAIS_Account']['CAIS_Summary']['Credit_Account']['CreditAccountTotal'])){
            return -1;
        }

        return intval($report['CAIS_Account']['CAIS_Summary']['Credit_Account']['CreditAccountTotal']);
    }

    /**
     * Experian信贷账户Active数
     * @return int
     * @throws \Exception
     */
    public function checkExperianCreditAccountActive(){
        $report = $this->getExperianReport();

        if(empty($report) || empty($report['CAIS_Account']['CAIS_Summary']['Credit_Account']['CreditAccountActive'])){
            return -1;
        }

        return intval($report['CAIS_Account']['CAIS_Summary']['Credit_Account']['CreditAccountActive']);
    }

    /**
     * Experian信贷账户Closed数
     * @return int
     * @throws \Exception
     */
    public function checkExperianCreditAccountClosed(){
        $report = $this->getExperianReport();

        if(empty($report) || empty($report['CAIS_Account']['CAIS_Summary']['Credit_Account']['CreditAccountClosed'])){
            return -1;
        }

        return intval($report['CAIS_Account']['CAIS_Summary']['Credit_Account']['CreditAccountClosed']);
    }

    /**
     * Experian抵押贷的待还余额
     * @return int
     * @throws \Exception
     */
    public function checkExperianOutstandingBalanceSecured(){
        $report = $this->getExperianReport();

        if(empty($report) || empty($report['CAIS_Account']['CAIS_Summary']['Total_Outstanding_Balance']['Outstanding_Balance_Secured'])){
            return -1;
        }

        return intval($report['CAIS_Account']['CAIS_Summary']['Total_Outstanding_Balance']['Outstanding_Balance_Secured']);
    }

    /**
     * Experian抵押贷的待还余额的占比
     * @return int
     * @throws \Exception
     */
    public function checkExperianOutstandingBalanceSecuredPercentage(){
        $report = $this->getExperianReport();

        if(empty($report) || empty($report['CAIS_Account']['CAIS_Summary']['Total_Outstanding_Balance']['Outstanding_Balance_Secured_Percentage'])){
            return -1;
        }

        return intval($report['CAIS_Account']['CAIS_Summary']['Total_Outstanding_Balance']['Outstanding_Balance_Secured_Percentage']);
    }

    /**
     * Experian非抵押贷的待还余额
     * @return int
     * @throws \Exception
     */
    public function checkExperianOutstandingBalanceUnSecured(){
        $report = $this->getExperianReport();

        if(empty($report) || empty($report['CAIS_Account']['CAIS_Summary']['Total_Outstanding_Balance']['Outstanding_Balance_UnSecured'])){
            return -1;
        }

        return intval($report['CAIS_Account']['CAIS_Summary']['Total_Outstanding_Balance']['Outstanding_Balance_UnSecured']);
    }

    /**
     * Experian非抵押贷的待还余额的占比
     * @return int
     * @throws \Exception
     */
    public function checkExperianOutstandingBalanceUnSecuredPercentage(){
        $report = $this->getExperianReport();

        if(empty($report) || empty($report['CAIS_Account']['CAIS_Summary']['Total_Outstanding_Balance']['Outstanding_Balance_UnSecured_Percentage'])){
            return -1;
        }

        return intval($report['CAIS_Account']['CAIS_Summary']['Total_Outstanding_Balance']['Outstanding_Balance_UnSecured_Percentage']);
    }

    /**
     * Experian总待还余额
     * @return int
     * @throws \Exception
     */
    public function checkExperianOutstandingBalanceAll(){
        $report = $this->getExperianReport();

        if(empty($report) || empty($report['CAIS_Account']['CAIS_Summary']['Total_Outstanding_Balance']['Outstanding_Balance_All'])){
            return -1;
        }

        return intval($report['CAIS_Account']['CAIS_Summary']['Total_Outstanding_Balance']['Outstanding_Balance_All']);
    }

    /**
     * Experian近180天被查询征信的次数
     * @return int
     * @throws \Exception
     */
    public function checkExperianLast180dEnquiryCnt(){
        $report = $this->getExperianReport();

        if(empty($report) || empty($report['CAPS']['CAPS_Summary']['CAPSLast180Days'])){
            return -1;
        }

        return intval($report['CAPS']['CAPS_Summary']['CAPSLast180Days']);
    }

    /**
     * Experian近90天被查询征信的次数
     * @return int
     * @throws \Exception
     */
    public function checkExperianLast90dEnquiryCnt(){
        $report = $this->getExperianReport();

        if(empty($report) || empty($report['CAPS']['CAPS_Summary']['CAPSLast90Days'])){
            return -1;
        }

        return intval($report['CAPS']['CAPS_Summary']['CAPSLast90Days']);
    }

    /**
     * Experian近30天被查询征信的次数
     * @return int
     * @throws \Exception
     */
    public function checkExperianLast30dEnquiryCnt(){
        $report = $this->getExperianReport();

        if(empty($report) || empty($report['CAPS']['CAPS_Summary']['CAPSLast30Days'])){
            return -1;
        }

        return intval($report['CAPS']['CAPS_Summary']['CAPSLast30Days']);
    }

    /**
     * Experian近7天被查询征信的次数
     * @return int
     * @throws \Exception
     */
    public function checkExperianLast7dEnquiryCnt(){
        $report = $this->getExperianReport();

        if(empty($report) || empty($report['CAPS']['CAPS_Summary']['CAPSLast7Days'])){
            return -1;
        }

        return intval($report['CAPS']['CAPS_Summary']['CAPSLast7Days']);
    }

    /**
     * ExperianNonCredit近180天被查询征信的次数
     * @return int
     * @throws \Exception
     */
    public function checkExperianNonCreditLast180dEnquiryCnt(){
        $report = $this->getExperianReport();

        if(empty($report) || empty($report['NonCreditCAPS']['NonCreditCAPS_Summary']['NonCreditCAPSLast180Days'])){
            return -1;
        }

        return intval($report['NonCreditCAPS']['NonCreditCAPS_Summary']['NonCreditCAPSLast180Days']);
    }

    /**
     * ExperianNonCredit近90天被查询征信的次数
     * @return int
     * @throws \Exception
     */
    public function checkExperianNonCreditLast90dEnquiryCnt(){
        $report = $this->getExperianReport();

        if(empty($report) || empty($report['NonCreditCAPS']['NonCreditCAPS_Summary']['NonCreditCAPSLast90Days'])){
            return -1;
        }

        return intval($report['NonCreditCAPS']['NonCreditCAPS_Summary']['NonCreditCAPSLast90Days']);
    }

    /**
     * ExperianNonCredit近30天被查询征信的次数
     * @return int
     * @throws \Exception
     */
    public function checkExperianNonCreditLast30dEnquiryCnt(){
        $report = $this->getExperianReport();

        if(empty($report) || empty($report['NonCreditCAPS']['NonCreditCAPS_Summary']['NonCreditCAPSLast30Days'])){
            return -1;
        }

        return intval($report['NonCreditCAPS']['NonCreditCAPS_Summary']['NonCreditCAPSLast30Days']);
    }

    /**
     * ExperianNonCredit近7天被查询征信的次数
     * @return int
     * @throws \Exception
     */
    public function checkExperianNonCreditLast7dEnquiryCnt(){
        $report = $this->getExperianReport();

        if(empty($report) || empty($report['NonCreditCAPS']['NonCreditCAPS_Summary']['NonCreditCAPSLast7Days'])){
            return -1;
        }

        return intval($report['NonCreditCAPS']['NonCreditCAPS_Summary']['NonCreditCAPSLast7Days']);
    }

    /**
     * Experian历史最大授信金额
     * @return int
     * @throws \Exception
     */
    public function checkExperianHisMaxCreditAmt(){
        $report = $this->getExperianReport();

        if(empty($report) || empty($report['CAIS_Account']['CAIS_Account_DETAILS'])){
            return -1;
        }

        $arr = [];
        foreach ($report['CAIS_Account']['CAIS_Account_DETAILS'] as $v){
            if(!empty($v['Highest_Credit_or_Original_Loan_Amount'])){
                $arr[] = $v['Highest_Credit_or_Original_Loan_Amount'];
            }
        }

        if(empty($arr)){
            return -1;
        }

        return max($arr);
    }

    /**
     * Experian最近6个月的最大授信金额
     * @return int
     * @throws \Exception
     */
    public function checkExperianLast6mMaxCreditAmt(){
        $report = $this->getExperianReport();

        if(empty($report) || empty($report['CAIS_Account']['CAIS_Account_DETAILS'])){
            return -1;
        }

        $time = strtotime("-6 month", $this->experian_updated_at);

        $arr = [];
        foreach ($report['CAIS_Account']['CAIS_Account_DETAILS'] as $v){
            if(!empty($v['Open_Date']) && !empty($v['Highest_Credit_or_Original_Loan_Amount'])
                && $time < strtotime($v['Open_Date'])){
                $arr[] = $v['Highest_Credit_or_Original_Loan_Amount'];
            }
        }

        if(empty($arr)){
            return -1;
        }

        return max($arr);
    }

    /**
     * Experian最近1个月的最大授信金额
     * @return int
     * @throws \Exception
     */
    public function checkExperianLast1mMaxCreditAmt(){
        $report = $this->getExperianReport();

        if(empty($report) || empty($report['CAIS_Account']['CAIS_Account_DETAILS'])){
            return -1;
        }

        $time = strtotime("-1 month", $this->experian_updated_at);

        $arr = [];
        foreach ($report['CAIS_Account']['CAIS_Account_DETAILS'] as $v){
            if(!empty($v['Open_Date']) && !empty($v['Highest_Credit_or_Original_Loan_Amount'])
                && $time < strtotime($v['Open_Date'])){
                $arr[] = $v['Highest_Credit_or_Original_Loan_Amount'];
            }
        }

        if(empty($arr)){
            return -1;
        }

        return max($arr);
    }

    /**
     * Experian历史平均授信金额
     * @return int
     * @throws \Exception
     */
    public function checkExperianHisAvgCreditAmt(){
        $report = $this->getExperianReport();

        if(empty($report) || empty($report['CAIS_Account']['CAIS_Account_DETAILS'])){
            return -1;
        }

        $arr = [];
        foreach ($report['CAIS_Account']['CAIS_Account_DETAILS'] as $v){
            if(!empty($v['Highest_Credit_or_Original_Loan_Amount'])){
                $arr[] = $v['Highest_Credit_or_Original_Loan_Amount'];
            }
        }

        if(empty($arr)){
            return -1;
        }

        return intval(round(array_sum($arr) / count($arr)));
    }

    /**
     * Experian最近6个月的平均授信金额
     * @return int
     * @throws \Exception
     */
    public function checkExperianLast6mAvgCreditAmt(){
        $report = $this->getExperianReport();

        if(empty($report) || empty($report['CAIS_Account']['CAIS_Account_DETAILS'])){
            return -1;
        }

        $time = strtotime("-6 month", $this->experian_updated_at);

        $arr = [];
        foreach ($report['CAIS_Account']['CAIS_Account_DETAILS'] as $v){
            if(!empty($v['Open_Date']) && !empty($v['Highest_Credit_or_Original_Loan_Amount'])
                && $time < strtotime($v['Open_Date'])){
                $arr[] = $v['Highest_Credit_or_Original_Loan_Amount'];
            }
        }

        if(empty($arr)){
            return -1;
        }

        return intval(round(array_sum($arr) / count($arr)));
    }

    /**
     * Experian最近1个月的平均授信金额
     * @return int
     * @throws \Exception
     */
    public function checkExperianLast1mAvgCreditAmt(){
        $report = $this->getExperianReport();

        if(empty($report) || empty($report['CAIS_Account']['CAIS_Account_DETAILS'])){
            return -1;
        }

        $time = strtotime("-1 month", $this->experian_updated_at);

        $arr = [];
        foreach ($report['CAIS_Account']['CAIS_Account_DETAILS'] as $v){
            if(!empty($v['Open_Date']) && !empty($v['Highest_Credit_or_Original_Loan_Amount'])
                && $time < strtotime($v['Open_Date'])){
                $arr[] = $v['Highest_Credit_or_Original_Loan_Amount'];
            }
        }

        if(empty($arr)){
            return -1;
        }

        return intval(round(array_sum($arr) / count($arr)));
    }

    /**
     * Experian最近一次授信距今的时间
     * @return int
     * @throws \Exception
     */
    public function checkExperianTimeOfLastCreditTimeToNow(){
        $report = $this->getExperianReport();

        if(empty($report) || empty($report['CAIS_Account']['CAIS_Account_DETAILS'])){
            return -1;
        }

        $arr = [];
        foreach ($report['CAIS_Account']['CAIS_Account_DETAILS'] as $v){
            if(!empty($v['Open_Date'])){
                $arr[] = strtotime($v['Open_Date']);
            }
        }

        if(empty($arr)){
            return -1;
        }

        return intval((strtotime(date('Y-m-d', $this->experian_updated_at)) - max($arr)) / 86400);
    }

    /**
     * Experian首次授信距今的时间
     * @return int
     * @throws \Exception
     */
    public function checkExperianTimeOfFirstCreditTimeToNow(){
        $report = $this->getExperianReport();

        if(empty($report) || empty($report['CAIS_Account']['CAIS_Account_DETAILS'])){
            return -1;
        }

        $arr = [];
        foreach ($report['CAIS_Account']['CAIS_Account_DETAILS'] as $v){
            if(!empty($v['Open_Date'])){
                $arr[] = strtotime($v['Open_Date']);
            }
        }

        if(empty($arr)){
            return -1;
        }

        return intval((strtotime(date('Y-m-d', $this->experian_updated_at)) - min($arr)) / 86400);
    }

    /**
     * Experian历史逾期总金额
     * @return int
     * @throws \Exception
     */
    public function checkExperianHisDueTotAmt(){
        $report = $this->getExperianReport();

        if(empty($report) || empty($report['CAIS_Account']['CAIS_Account_DETAILS'])){
            return -1;
        }

        $arr = [];
        foreach ($report['CAIS_Account']['CAIS_Account_DETAILS'] as $v){
            if(!empty($v['Amount_Past_Due'])){
                $arr[] = $v['Amount_Past_Due'];
            }
        }

        if(empty($arr)){
            return -1;
        }

        return array_sum($arr);
    }

    /**
     * Experian最近6个月的逾期总金额
     * @return int
     * @throws \Exception
     */
    public function checkExperianLast6mDueTotAmt(){
        $report = $this->getExperianReport();

        if(empty($report) || empty($report['CAIS_Account']['CAIS_Account_DETAILS'])){
            return -1;
        }

        $time = strtotime("-6 month", $this->experian_updated_at);

        $arr = [];
        foreach ($report['CAIS_Account']['CAIS_Account_DETAILS'] as $v){
            if(!empty($v['Open_Date']) && !empty($v['Amount_Past_Due'])
                && $time < strtotime($v['Open_Date'])){
                $arr[] = $v['Amount_Past_Due'];
            }
        }

        if(empty($arr)){
            return -1;
        }

        return array_sum($arr);
    }

    /**
     * Experian最近1个月的逾期总金额
     * @return int
     * @throws \Exception
     */
    public function checkExperianLast1mDueTotAmt(){
        $report = $this->getExperianReport();

        if(empty($report) || empty($report['CAIS_Account']['CAIS_Account_DETAILS'])){
            return -1;
        }

        $time = strtotime("-1 month", $this->experian_updated_at);

        $arr = [];
        foreach ($report['CAIS_Account']['CAIS_Account_DETAILS'] as $v){
            if(!empty($v['Open_Date']) && !empty($v['Amount_Past_Due'])
                && $time < strtotime($v['Open_Date'])){
                $arr[] = $v['Amount_Past_Due'];
            }
        }

        if(empty($arr)){
            return -1;
        }

        return array_sum($arr);
    }

    /**
     * Experian历史逾期总次数
     * @return int
     * @throws \Exception
     */
    public function checkExperianHisDueTotCnt(){
        $report = $this->getExperianReport();

        if(empty($report) || empty($report['CAIS_Account']['CAIS_Account_DETAILS'])){
            return -1;
        }

        $count = 0;
        foreach ($report['CAIS_Account']['CAIS_Account_DETAILS'] as $v){
            if(!empty($v['Amount_Past_Due']) && $v['Amount_Past_Due'] > 0){
                $count++;
            }
        }

        if($count == 0){
            return -1;
        }

        return $count;
    }

    /**
     * Experian最近6个月的逾期次数
     * @return int
     * @throws \Exception
     */
    public function checkExperianLast6mDueTotCnt(){
        $report = $this->getExperianReport();

        if(empty($report) || empty($report['CAIS_Account']['CAIS_Account_DETAILS'])){
            return -1;
        }

        $time = strtotime("-6 month", $this->experian_updated_at);

        $count = 0;
        foreach ($report['CAIS_Account']['CAIS_Account_DETAILS'] as $v){
            if(!empty($v['Open_Date']) && !empty($v['Amount_Past_Due'])
                && $time < strtotime($v['Open_Date']) && $v['Amount_Past_Due'] > 0){
                $count++;
            }
        }

        if($count == 0){
            return -1;
        }

        return $count;
    }

    /**
     * Experian最近1个月的逾期次数
     * @return int
     * @throws \Exception
     */
    public function checkExperianLast1mDueTotCnt(){
        $report = $this->getExperianReport();

        if(empty($report) || empty($report['CAIS_Account']['CAIS_Account_DETAILS'])){
            return -1;
        }

        $time = strtotime("-1 month", $this->experian_updated_at);

        $count = 0;
        foreach ($report['CAIS_Account']['CAIS_Account_DETAILS'] as $v){
            if(!empty($v['Open_Date']) && !empty($v['Amount_Past_Due'])
                && $time < strtotime($v['Open_Date']) && $v['Amount_Past_Due'] > 0){
                $count++;
            }
        }

        if($count == 0){
            return -1;
        }

        return $count;
    }

    /**
     * Experian历史最大逾期天数段
     * @return int
     * @throws \Exception
     */
    public function checkExperianHisMaxDueDaysLevel(){
        $report = $this->getExperianReport();

        if(empty($report) || empty($report['CAIS_Account']['CAIS_Account_DETAILS'])){
            return -1;
        }

        $arr = [];
        foreach ($report['CAIS_Account']['CAIS_Account_DETAILS'] as $v){
            if(!empty($v['Payment_History_Profile'])){
                for ($i = 0; $i < strlen($v['Payment_History_Profile']); $i++){
                    if(is_numeric($v['Payment_History_Profile'][$i])){
                        $arr[] = $v['Payment_History_Profile'][$i];
                    }
                }
            }
        }

        if(empty($arr)){
            return -1;
        }

        return max($arr);
    }

    /**
     * Experian历史最大逾期天数
     * @return int
     * @throws \Exception
     */
    public function checkExperianHisMaxDueDays(){
        $report = $this->getExperianReport();

        if(empty($report) || empty($report['CAIS_Account']['CAIS_Account_DETAILS'])){
            return -1;
        }

        $arr = [];
        foreach ($report['CAIS_Account']['CAIS_Account_DETAILS'] as $v){
            if(!empty($v['CAIS_Account_History'])){
                foreach ($v['CAIS_Account_History'] as $val){
                    if(!empty($val['Days_Past_Due'])){
                        $arr[] = $val['Days_Past_Due'];
                    }
                }
            }
        }

        if(empty($arr)){
            return -1;
        }

        return max($arr);
    }

    /**
     * Experian最近一次还款距今的时间
     * @return int
     * @throws \Exception
     */
    public function checkExperianTimeOfLastPayMent(){
        $report = $this->getExperianReport();

        if(empty($report) || empty($report['CAIS_Account']['CAIS_Account_DETAILS'])){
            return -1;
        }

        $arr = [];
        foreach ($report['CAIS_Account']['CAIS_Account_DETAILS'] as $v){
            if(!empty($v['Date_of_Last_Payment'])){
                $arr[] = strtotime($v['Date_of_Last_Payment']);
            }
        }

        if(empty($arr)){
            return -1;
        }

        return intval((strtotime(date('Y-m-d', $this->experian_updated_at)) - max($arr)) / 86400);
    }

    /**
     * Experian征信分
     * @return int
     * @throws \Exception
     */
    public function checkExperianCreditScore(){
        $report = $this->getExperianReport();

        if(empty($report) || empty($report['SCORE']['BureauScore'])){
            return -9;
        }

        return intval($report['SCORE']['BureauScore']);
    }

    /**
     * 紧急联系人A与用户通话的总时长(包括主被叫)
     * @return int
     */
    public function checkTotalCallTimeOfContactAWithUser(){
        if(empty($this->data->userContact->phone)){
            return -1;
        }

        $phone_arr = explode(':', $this->data->userContact->phone);
        $phone = [];
        foreach ($phone_arr as $value){
            $phone[] = substr($value, -10);
        }

        $phone = array_unique($phone);

        if($this->data->order->is_export == UserLoanOrder::IS_EXPORT_YES){
            $userId = $this->getUserOtherId();
            $data = MgUserCallReports::find()
                ->select(['callNumber','callDuration'])
                ->where(['user_id' => $userId, 'callType' => [1, 2]])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->all(Yii::$app->mongodb_loan);
        }else{
            $data = MgUserCallReports::find()
                ->select(['callNumber','callDuration'])
                ->where(['user_id' => intval($this->data->order->user_id), 'callType' => [1, 2]])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->all();
        }

        if(empty($data)){
            return 0;
        }

        $callDuration = 0;
        foreach ($phone as $v){
            foreach ($data as $val){
                if(substr($val['callNumber'], -10) == $v){
                    $callDuration += $val['callDuration'];
                }
            }
        }

        return $callDuration;
    }

    /**
     * 紧急联系人A与用户通话的总次数(包括主被叫)
     * @return int
     */
    public function checkTotalCallCntOfContactAWithUser(){
        if(empty($this->data->userContact->phone)){
            return -1;
        }

        $phone_arr = explode(':', $this->data->userContact->phone);
        $phone = [];
        foreach ($phone_arr as $value){
            $phone[] = substr($value, -10);
        }

        $phone = array_unique($phone);

        if($this->data->order->is_export == UserLoanOrder::IS_EXPORT_YES){
            $userId = $this->getUserOtherId();
            $data = MgUserCallReports::find()
                ->select(['callNumber','callDuration'])
                ->where(['user_id' => $userId, 'callType' => [1, 2]])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->all(Yii::$app->mongodb_loan);
        }else{
            $data = MgUserCallReports::find()
                ->select(['callNumber','callDuration'])
                ->where(['user_id' => intval($this->data->order->user_id), 'callType' => [1, 2]])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->all();
        }

        if(empty($data)){
            return 0;
        }

        $count = 0;
        foreach ($phone as $v){
            foreach ($data as $val){
                if(substr($val['callNumber'], -10) == $v){
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * 近30天紧急联系人A与用户通话的总时长(包括主被叫)
     * @return int
     */
    public function checkLast30dTotalCallTimeOfContactAWithUser(){
        $begin_time = strtotime('-30 day', $this->data->order->order_time);

        if(empty($this->data->userContact->phone)){
            return -1;
        }

        $phone_arr = explode(':', $this->data->userContact->phone);
        $phone = [];
        foreach ($phone_arr as $value){
            $phone[] = substr($value, -10);
        }

        $phone = array_unique($phone);

        if($this->data->order->is_export == UserLoanOrder::IS_EXPORT_YES){
            $userId = $this->getUserOtherId();
            $data = MgUserCallReports::find()
                ->select(['callNumber','callDuration'])
                ->where(['user_id' => $userId, 'callType' => [1, 2]])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->all(Yii::$app->mongodb_loan);
        }else{
            $data = MgUserCallReports::find()
                ->select(['callNumber','callDuration'])
                ->where(['user_id' => intval($this->data->order->user_id), 'callType' => [1, 2]])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->all();
        }

        if(empty($data)){
            return 0;
        }
        $callDuration = 0;
        foreach ($phone as $v){
            foreach ($data as $val){
                if(substr($val['callNumber'], -10) == $v){
                    $callDuration += $val['callDuration'];
                }
            }
        }

        return $callDuration;
    }

    /**
     * 近30天紧急联系人A与用户通话的总次数(包括主被叫)
     * @return int
     */
    public function checkLast30dTotalCallCntOfContactAWithUser(){
        $begin_time = strtotime('-30 day', $this->data->order->order_time);

        if(empty($this->data->userContact->phone)){
            return -1;
        }

        $phone_arr = explode(':', $this->data->userContact->phone);
        $phone = [];
        foreach ($phone_arr as $value){
            $phone[] = substr($value, -10);
        }

        $phone = array_unique($phone);

        if($this->data->order->is_export == UserLoanOrder::IS_EXPORT_YES){
            $userId = $this->getUserOtherId();
            $data = MgUserCallReports::find()
                ->select(['callNumber','callDuration'])
                ->where(['user_id' => $userId, 'callType' => [1, 2]])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->all(Yii::$app->mongodb_loan);
        }else{
            $data = MgUserCallReports::find()
                ->select(['callNumber','callDuration'])
                ->where(['user_id' => intval($this->data->order->user_id), 'callType' => [1, 2]])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->all();
        }

        if(empty($data)){
            return 0;
        }

        $count = 0;
        foreach ($phone as $v){
            foreach ($data as $val){
                if(substr($val['callNumber'], -10) == $v){
                    $count++;
                }
            }
        }

        return $count;
    }


    /**
     * 紧急联系人A与用户最近一次通话距今时间(包括主被叫)
     * @return int
     */
    public function checkLastCallTimeDiffOfContactAWithUser(){
        if(empty($this->data->userContact->phone)){
            return -1;
        }

        $phone_arr = explode(':', $this->data->userContact->phone);
        $phone = [];
        foreach ($phone_arr as $value){
            $phone[] = substr($value, -10);
        }

        $phone = array_unique($phone);

        if($this->data->order->is_export == UserLoanOrder::IS_EXPORT_YES){
            $userId = $this->getUserOtherId();
            $data = MgUserCallReports::find()
                ->select(['callNumber','callDuration', 'callDateTime'])
                ->where(['user_id' => $userId, 'callType' => [1, 2]])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->all(Yii::$app->mongodb_loan);
        }else{
            $data = MgUserCallReports::find()
                ->select(['callNumber','callDuration', 'callDateTime'])
                ->where(['user_id' => intval($this->data->order->user_id), 'callType' => [1, 2]])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->all();
        }

        if(empty($data)){
            return -1;
        }

        $callDuration = [];
        foreach ($phone as $v){
            foreach ($data as $val){
                if(substr($val['callNumber'], -10) == $v){
                    $callDuration[] = $val['callDateTime'];
                }
            }
        }

        if(empty($callDuration)){
            return -1;
        }

        return ceil((strtotime('today') - strtotime(max($callDuration))) / 86400);
    }

    /**
     * 近30天紧急联系人A与用户通话的最大时长(包括主被叫)
     * @return int
     */
    public function checkLast30MaxCallTimeOfContactAWithUser(){
        $begin_time = strtotime('-30 day', $this->data->order->order_time);

        if(empty($this->data->userContact->phone)){
            return -1;
        }

        $phone_arr = explode(':', $this->data->userContact->phone);
        $phone = [];
        foreach ($phone_arr as $value){
            $phone[] = substr($value, -10);
        }

        $phone = array_unique($phone);

        if($this->data->order->is_export == UserLoanOrder::IS_EXPORT_YES){
            $userId = $this->getUserOtherId();
            $data = MgUserCallReports::find()
                ->select(['callNumber','callDuration'])
                ->where(['user_id' => $userId, 'callType' => [1, 2]])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->all(Yii::$app->mongodb_loan);
        }else{
            $data = MgUserCallReports::find()
                ->select(['callNumber','callDuration'])
                ->where(['user_id' => intval($this->data->order->user_id), 'callType' => [1, 2]])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->all();
        }

        if(empty($data)){
            return 0;
        }

        $callDuration = [];
        foreach ($phone as $v){
            foreach ($data as $val){
                if(substr($val['callNumber'], -10) == $v){
                    $callDuration[] = $val['callDuration'];
                }
            }
        }

        if(empty($callDuration)){
            return 0;
        }

        return max($callDuration);
    }

    /**
     * 近30天紧急联系人A与用户通话的平均时长(包括主被叫)
     * @return int
     */
    public function checkLast30AvgCallTimeOfContactAWithUser(){
        $begin_time = strtotime('-30 day', $this->data->order->order_time);

        if(empty($this->data->userContact->phone)){
            return -1;
        }

        $phone_arr = explode(':', $this->data->userContact->phone);
        $phone = [];
        foreach ($phone_arr as $value){
            $phone[] = substr($value, -10);
        }

        $phone = array_unique($phone);

        if($this->data->order->is_export == UserLoanOrder::IS_EXPORT_YES){
            $userId = $this->getUserOtherId();
            $data = MgUserCallReports::find()
                ->select(['callNumber','callDuration'])
                ->where(['user_id' => $userId, 'callType' => [1, 2]])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->all(Yii::$app->mongodb_loan);
        }else{
            $data = MgUserCallReports::find()
                ->select(['callNumber','callDuration'])
                ->where(['user_id' => intval($this->data->order->user_id), 'callType' => [1, 2]])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->all();
        }

        if(empty($data)){
            return 0;
        }

        $count = 0;
        $callDuration = 0;
        foreach ($phone as $v){
            foreach ($data as $val){
                if(substr($val['callNumber'], -10) == $v){
                    $count++;
                    $callDuration += $val['callDuration'];
                }
            }
        }

        if($count == 0){
            return 0;
        }

        return round($callDuration / $count);

    }

    /**
     * 近30天紧急联系人A与用户通话的最小时长(包括主被叫)
     * @return int
     */
    public function checkLast30MinCallTimeOfContactAWithUser(){
        $begin_time = strtotime('-30 day', $this->data->order->order_time);

        if(empty($this->data->userContact->phone)){
            return -1;
        }

        $phone_arr = explode(':', $this->data->userContact->phone);
        $phone = [];
        foreach ($phone_arr as $value){
            $phone[] = substr($value, -10);
        }

        $phone = array_unique($phone);

        if($this->data->order->is_export == UserLoanOrder::IS_EXPORT_YES){
            $userId = $this->getUserOtherId();
            $data = MgUserCallReports::find()
                ->select(['callNumber','callDuration'])
                ->where(['user_id' => $userId, 'callType' => [1, 2]])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->all(Yii::$app->mongodb_loan);
        }else{
            $data = MgUserCallReports::find()
                ->select(['callNumber','callDuration'])
                ->where(['user_id' => intval($this->data->order->user_id), 'callType' => [1, 2]])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->all();
        }

        if(empty($data)){
            return 0;
        }

        $callDuration = [];
        foreach ($phone as $v){
            foreach ($data as $val){
                if(substr($val['callNumber'], -10) == $v){
                    $callDuration[] = $val['callDuration'];
                }
            }
        }

        if(empty($callDuration)){
            return 0;
        }

        return min($callDuration);
    }

    /**
     * 紧急联系人B与用户通话的总时长(包括主被叫)
     * @return int
     */
    public function checkTotalCallTimeOfContactBWithUser(){
        if(empty($this->data->userContact->other_phone)){
            return -1;
        }

        $phone_arr = explode(':', $this->data->userContact->other_phone);
        $phone = [];
        foreach ($phone_arr as $value){
            $phone[] = substr($value, -10);
        }

        $phone = array_unique($phone);

        if($this->data->order->is_export == UserLoanOrder::IS_EXPORT_YES){
            $userId = $this->getUserOtherId();
            $data = MgUserCallReports::find()
                ->select(['callNumber','callDuration'])
                ->where(['user_id' => $userId, 'callType' => [1, 2]])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->all(Yii::$app->mongodb_loan);
        }else{
            $data = MgUserCallReports::find()
                ->select(['callNumber','callDuration'])
                ->where(['user_id' => intval($this->data->order->user_id), 'callType' => [1, 2]])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->all();
        }

        if(empty($data)){
            return 0;
        }

        $callDuration = 0;
        foreach ($phone as $v){
            foreach ($data as $val){
                if(substr($val['callNumber'], -10) == $v){
                    $callDuration += $val['callDuration'];
                }
            }
        }

        return $callDuration;
    }

    /**
     * 紧急联系人B与用户通话的总次数(包括主被叫)
     * @return int
     */
    public function checkTotalCallCntOfContactBWithUser(){
        if(empty($this->data->userContact->other_phone)){
            return -1;
        }

        $phone_arr = explode(':', $this->data->userContact->other_phone);
        $phone = [];
        foreach ($phone_arr as $value){
            $phone[] = substr($value, -10);
        }

        $phone = array_unique($phone);

        if($this->data->order->is_export == UserLoanOrder::IS_EXPORT_YES){
            $userId = $this->getUserOtherId();
            $data = MgUserCallReports::find()
                ->select(['callNumber','callDuration'])
                ->where(['user_id' => $userId, 'callType' => [1, 2]])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->all(Yii::$app->mongodb_loan);
        }else{
            $data = MgUserCallReports::find()
                ->select(['callNumber','callDuration'])
                ->where(['user_id' => intval($this->data->order->user_id), 'callType' => [1, 2]])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->all();
        }

        if(empty($data)){
            return 0;
        }

        $count = 0;
        foreach ($phone as $v){
            foreach ($data as $val){
                if(substr($val['callNumber'], -10) == $v){
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * 近30天紧急联系人B与用户通话的总时长(包括主被叫)
     * @return int
     */
    public function checkLast30dTotalCallTimeOfContactBWithUser(){
        $begin_time = strtotime('-30 day', $this->data->order->order_time);

        if(empty($this->data->userContact->other_phone)){
            return -1;
        }

        $phone_arr = explode(':', $this->data->userContact->other_phone);
        $phone = [];
        foreach ($phone_arr as $value){
            $phone[] = substr($value, -10);
        }

        $phone = array_unique($phone);

        if($this->data->order->is_export == UserLoanOrder::IS_EXPORT_YES){
            $userId = $this->getUserOtherId();
            $data = MgUserCallReports::find()
                ->select(['callNumber','callDuration'])
                ->where(['user_id' => $userId, 'callType' => [1, 2]])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->all(Yii::$app->mongodb_loan);
        }else{
            $data = MgUserCallReports::find()
                ->select(['callNumber','callDuration'])
                ->where(['user_id' => intval($this->data->order->user_id), 'callType' => [1, 2]])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->all();
        }

        if(empty($data)){
            return 0;
        }

        $callDuration = 0;
        foreach ($phone as $v){
            foreach ($data as $val){
                if(substr($val['callNumber'], -10) == $v){
                    $callDuration += $val['callDuration'];
                }
            }
        }

        return $callDuration;
    }

    /**
     * 近30天紧急联系人B与用户通话的总次数(包括主被叫)
     * @return int
     */
    public function checkLast30dTotalCallCntOfContactBWithUser(){
        $begin_time = strtotime('-30 day', $this->data->order->order_time);

        if(empty($this->data->userContact->other_phone)){
            return -1;
        }

        $phone_arr = explode(':', $this->data->userContact->other_phone);
        $phone = [];
        foreach ($phone_arr as $value){
            $phone[] = substr($value, -10);
        }

        $phone = array_unique($phone);

        if($this->data->order->is_export == UserLoanOrder::IS_EXPORT_YES){
            $userId = $this->getUserOtherId();
            $data = MgUserCallReports::find()
                ->select(['callNumber','callDuration'])
                ->where(['user_id' => $userId, 'callType' => [1, 2]])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->all(Yii::$app->mongodb_loan);
        }else{
            $data = MgUserCallReports::find()
                ->select(['callNumber','callDuration'])
                ->where(['user_id' => intval($this->data->order->user_id), 'callType' => [1, 2]])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->all();
        }

        if(empty($data)){
            return 0;
        }

        $count = 0;
        foreach ($phone as $v){
            foreach ($data as $val){
                if(substr($val['callNumber'], -10) == $v){
                    $count++;
                }
            }
        }

        return $count;
    }


    /**
     * 紧急联系人B与用户最近一次通话距今时间(包括主被叫)
     * @return int
     */
    public function checkLastCallTimeDiffOfContactBWithUser(){
        if(empty($this->data->userContact->other_phone)){
            return -1;
        }

        $phone_arr = explode(':', $this->data->userContact->other_phone);
        $phone = [];
        foreach ($phone_arr as $value){
            $phone[] = substr($value, -10);
        }

        $phone = array_unique($phone);

        if($this->data->order->is_export == UserLoanOrder::IS_EXPORT_YES){
            $userId = $this->getUserOtherId();
            $data = MgUserCallReports::find()
                ->select(['callNumber','callDuration', 'callDateTime'])
                ->where(['user_id' => $userId, 'callType' => [1, 2]])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->all(Yii::$app->mongodb_loan);
        }else{
            $data = MgUserCallReports::find()
                ->select(['callNumber','callDuration', 'callDateTime'])
                ->where(['user_id' => intval($this->data->order->user_id), 'callType' => [1, 2]])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->all();
        }

        if(empty($data)){
            return -1;
        }

        $callDuration = [];
        foreach ($phone as $v){
            foreach ($data as $val){
                if(substr($val['callNumber'], -10) == $v){
                    $callDuration[] = $val['callDateTime'];
                }
            }
        }

        if(empty($callDuration)){
            return -1;
        }

        return ceil((strtotime('today') - strtotime(max($callDuration))) / 86400);
    }

    /**
     * 近30天紧急联系人B与用户通话的最大时长(包括主被叫)
     * @return int
     */
    public function checkLast30MaxCallTimeOfContactBWithUser(){
        $begin_time = strtotime('-30 day', $this->data->order->order_time);

        if(empty($this->data->userContact->other_phone)){
            return -1;
        }

        $phone_arr = explode(':', $this->data->userContact->other_phone);
        $phone = [];
        foreach ($phone_arr as $value){
            $phone[] = substr($value, -10);
        }

        $phone = array_unique($phone);

        if($this->data->order->is_export == UserLoanOrder::IS_EXPORT_YES){
            $userId = $this->getUserOtherId();
            $data = MgUserCallReports::find()
                ->select(['callNumber','callDuration'])
                ->where(['user_id' => $userId, 'callType' => [1, 2]])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->all(Yii::$app->mongodb_loan);
        }else{
            $data = MgUserCallReports::find()
                ->select(['callNumber','callDuration'])
                ->where(['user_id' => intval($this->data->order->user_id), 'callType' => [1, 2]])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->all();
        }

        if(empty($data)){
            return 0;
        }

        $callDuration = [];
        foreach ($phone as $v){
            foreach ($data as $val){
                if(substr($val['callNumber'], -10) == $v){
                    $callDuration[] = $val['callDuration'];
                }
            }
        }

        if(empty($callDuration)){
            return 0;
        }

        return max($callDuration);
    }

    /**
     * 近30天紧急联系人B与用户通话的平均时长(包括主被叫)
     * @return int
     */
    public function checkLast30AvgCallTimeOfContactBWithUser(){
        $begin_time = strtotime('-30 day', $this->data->order->order_time);

        if(empty($this->data->userContact->other_phone)){
            return -1;
        }

        $phone_arr = explode(':', $this->data->userContact->other_phone);
        $phone = [];
        foreach ($phone_arr as $value){
            $phone[] = substr($value, -10);
        }

        $phone = array_unique($phone);

        if($this->data->order->is_export == UserLoanOrder::IS_EXPORT_YES){
            $userId = $this->getUserOtherId();
            $data = MgUserCallReports::find()
                ->select(['callNumber','callDuration'])
                ->where(['user_id' => $userId, 'callType' => [1, 2]])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->all(Yii::$app->mongodb_loan);
        }else{
            $data = MgUserCallReports::find()
                ->select(['callNumber','callDuration'])
                ->where(['user_id' => intval($this->data->order->user_id), 'callType' => [1, 2]])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->all();
        }

        if(empty($data)){
            return 0;
        }

        $count = 0;
        $callDuration = 0;
        foreach ($phone as $v){
            foreach ($data as $val){
                if(substr($val['callNumber'], -10) == $v){
                    $count++;
                    $callDuration += $val['callDuration'];
                }
            }
        }

        if($count == 0){
            return 0;
        }

        return round($callDuration / $count);
    }

    /**
     * 近30天紧急联系人B与用户通话的最小时长(包括主被叫)
     * @return int
     */
    public function checkLast30MinCallTimeOfContactBWithUser(){
        $begin_time = strtotime('-30 day', $this->data->order->order_time);

        if(empty($this->data->userContact->other_phone)){
            return -1;
        }

        $phone_arr = explode(':', $this->data->userContact->other_phone);
        $phone = [];
        foreach ($phone_arr as $value){
            $phone[] = substr($value, -10);
        }

        $phone = array_unique($phone);

        if($this->data->order->is_export == UserLoanOrder::IS_EXPORT_YES){
            $userId = $this->getUserOtherId();
            $data = MgUserCallReports::find()
                ->select(['callNumber','callDuration'])
                ->where(['user_id' => $userId, 'callType' => [1, 2]])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->all(Yii::$app->mongodb_loan);
        }else{
            $data = MgUserCallReports::find()
                ->select(['callNumber','callDuration'])
                ->where(['user_id' => intval($this->data->order->user_id), 'callType' => [1, 2]])
                ->andWhere(['>=', 'callDateTime', $begin_time])
                ->andWhere(['>', 'callDuration', 0])
                ->asArray()
                ->all();
        }

        if(empty($data)){
            return 0;
        }

        $callDuration = [];
        foreach ($phone as $v){
            foreach ($data as $val){
                if(substr($val['callNumber'], -10) == $v){
                    $callDuration[] = $val['callDuration'];
                }
            }
        }

        if(empty($callDuration)){
            return 0;
        }

        return min($callDuration);
    }

    /**
     * 催收建议是否拒绝
     * @return int
     */
    public function checkIsCollectionAdviceReject(){
        $ids = LoanPerson::find()->select(['id'])->where(['pan_code' => $this->data->loanPerson->pan_code])
            ->orWhere(['phone' => $this->data->loanPerson->phone])
            ->andWhere(['merchant_id' => $this->data->order->merchant_id])
            ->asArray()->column();

        $query = UserLoanOrder::find()->select(['id'])->where(['user_id' => $ids]);

        if(!empty($this->data->order->did)){
            $query->orWhere(['did' => $this->data->order->did]);
        }

        $order_ids = $query->andWhere(['merchant_id' => $this->data->order->merchant_id])->asArray()->column();

        $data = LoanCollectionSuggestionChangeLog::findOne(['order_id' => $order_ids, 'suggestion' => -1]);

        if(empty($data)){
            return 0;
        }

        return 1;
    }

    /**
     * 总平台催收建议是否拒绝
     * @return int
     */
    public function checkIsCollectionAdviceRejectTotPlatform(){
        $ids = LoanPerson::find()->select(['id'])->where(['pan_code' => $this->data->loanPerson->pan_code])
            ->orWhere(['phone' => $this->data->loanPerson->phone])
            ->asArray()->column();

        $ids_loan = LoanPerson::find()->select(['id'])->where(['pan_code' => $this->data->loanPerson->pan_code])
            ->orWhere(['phone' => $this->data->loanPerson->phone])
            ->asArray()->column(Yii::$app->db_loan);

        $query = UserLoanOrder::find()->select(['id'])->where(['user_id' => $ids]);
        $query_loan = UserLoanOrder::find()->select(['id'])->where(['user_id' => $ids_loan]);

        if(!empty($this->data->order->did)){
            $query->orWhere(['did' => $this->data->order->did]);
            $query_loan->orWhere(['did' => $this->data->order->did]);
        }

        $order_ids = $query->asArray()->column();
        $order_ids_loan = $query_loan->asArray()->column(Yii::$app->db_loan);

        $data = LoanCollectionSuggestionChangeLog::findOne(['order_id' => $order_ids, 'suggestion' => -1]);
        $data_loan = LoanCollectionSuggestionChangeLog::find()->where(['order_id' => $order_ids_loan, 'suggestion' => -1])->one(Yii::$app->db_assist_loan);

        if(empty($data) && empty($data_loan)){
            return 0;
        }

        return 1;
    }

    /**
     * 本次订单Pan卡号在全平台的当前处于待还款状态的订单数
     * @return int
     */
    public function checkPendingRepaymentCntOfPanInAllPlatform(){
        $data = UserLoanOrderRepayment::find()
            ->select(['r.order_id'])
            ->from(UserLoanOrderRepayment::tableName(). ' as r')
            ->leftJoin(LoanPerson::tableName(). ' as p', 'p.id = r.user_id')
            ->where(['p.pan_code' => $this->data->loanPerson->pan_code,
                     'r.status' => UserLoanOrderRepayment::STATUS_NORAML,
                     'p.merchant_id' => $this->data->order->merchant_id])
            ->asArray()
            ->all();

        return count($data);
    }

    /**
     * 本次订单Pan卡号在全平台当前待还款订单的最大逾期天数
     * @return int
     */
    public function checkMaxDueDaysOfPendingRepaymentOrderOfPanInAllPaltform(){
        $data = UserLoanOrderRepayment::find()
            ->select(['r.overdue_day'])
            ->from(UserLoanOrderRepayment::tableName(). ' as r')
            ->leftJoin(LoanPerson::tableName(). ' as p', 'p.id = r.user_id')
            ->where(['p.pan_code' => $this->data->loanPerson->pan_code,
                     'r.status' => UserLoanOrderRepayment::STATUS_NORAML,
                     'p.merchant_id' => $this->data->order->merchant_id])
            ->orderBy(['r.overdue_day' => SORT_DESC])
            ->asArray()
            ->one();

        return $data['overdue_day'] ?? 0;
    }

    /**
     * Pan卡号是否命中自有黑名单
     * @return int
     */
    public function checkPanCardHitBlackList(){
        if(!isset($this->data->loanPerson->pan_code)){
            return -1;
        }
        $pan = [
            $this->data->loanPerson->pan_code
        ];
        $service = new RiskBlackListService();
        if($service->checkHitByPan($pan, $this->data->order->merchant_id))
        {
            return 1;
        }else{
            return 0;
        }
    }

    /**
     * 近30天数盟设备ID在下单环节的申请次数
     * @return int
     */
    public function checkLast30ApplyCntBySMDeviceID(){
        if(empty($this->data->order->did)){
            return -1;
        }
        $begin_time = $this->data->order->order_time - 30 * 86400;
        $arr = UserLoanOrder::find()->where(['did' => $this->data->order->did, 'merchant_id' => $this->data->order->merchant_id])
            ->andWhere(['>=', 'order_time', $begin_time])->all();
        return count($arr);
    }

    /**
     * 近30天数盟设备ID在下单环节申请被拒次数
     * @return int
     */
    public function checkLast30RejectCntBySMDeviceID(){
        if(empty($this->data->order->did)){
            return -1;
        }
        $begin_time = $this->data->order->order_time - 30 * 86400;
        $arr = UserLoanOrder::find()->where(['did' => $this->data->order->did, 'merchant_id' => $this->data->order->merchant_id])
            ->andWhere(['>=', 'order_time', $begin_time])->andWhere(['<', 'status', 0])->all();
        return count($arr);
    }

    /**
     * 本平台最近1笔订单的提前还款天数
     * @return int
     */
    public function checkPrerepaymentDaysLastOrderThisApp(){
        $order = UserLoanOrderRepayment::find()->where(['user_id' => $this->data->loanPerson->id])
            ->andWhere(['<', 'order_id', $this->data->order->id])
            ->orderBy(['order_id' => SORT_DESC])
            ->one();
        if(empty($order)){
            return -2;
        }

        if($order->is_overdue == 1){
            return -1;
        }

        return (strtotime(date('Y-m-d', $order->plan_repayment_time)) - strtotime(date('Y-m-d', $order->closing_time))) / 86400;
    }

    /**
     * 该手机号在全平台关联的不同Aadhaar卡号数量
     * @return int
     */
    public function checkPhoneMatchAadhaarCnt()
    {
        $phone = $this->data->loanPerson->phone;
        $aadhaars = ArrayHelper::getColumn(
            LoanPerson::find()
                ->select(['aadhaar_md5'])
                ->where(['phone' => $phone, 'merchant_id' => $this->data->order->merchant_id])
                ->andWhere(['is not','aadhaar_md5',null])
                ->asArray()->all(),
            'aadhaar_md5');
        return count(array_unique($aadhaars));
    }

    /**
     * 该手机号在全平台关联的不同Pan卡号数量
     * @return int
     */
    public function checkPhoneMatchPanCnt()
    {
        $phone = $this->data->loanPerson->phone;
        $pan = ArrayHelper::getColumn(
            LoanPerson::find()
                ->select(['pan_code'])
                ->where(['phone' => $phone, 'merchant_id' => $this->data->order->merchant_id])
                ->andWhere(['is not','pan_code',null])
                ->asArray()->all(),
            'pan_code');
        return count(array_unique($pan));
    }

    /**
     * 该Aadhaar卡号在全平台关联的不同手机号数量
     * @return int
     */
    public function checkAadhaarMatchPhoneCnt()
    {
        if(empty($this->data->loanPerson->aadhaar_md5)){
            return -1;
        }
        $aadhaar_number = $this->data->loanPerson->aadhaar_md5;
        $phone = ArrayHelper::getColumn(
            LoanPerson::find()
                ->select(['phone'])
                ->where(['aadhaar_md5' => $aadhaar_number, 'merchant_id' => $this->data->order->merchant_id])
                ->asArray()->all(),
            'phone');
        return count(array_unique($phone));
    }

    /**
     * 该Pan卡号在全平台关联的不同Aadhaar卡号数量
     * @return int
     */
    public function checkPanMatchAadhaarCnt()
    {
        $pan_code = $this->data->loanPerson->pan_code;
        $aadhaars = ArrayHelper::getColumn(
            LoanPerson::find()
                ->select(['aadhaar_md5'])
                ->where(['pan_code' => $pan_code, 'merchant_id' => $this->data->order->merchant_id])
                ->andWhere(['is not','aadhaar_md5',null])
                ->asArray()->all(),
            'aadhaar_md5');
        return count(array_unique($aadhaars));
    }

    /**
     * 该Aadhaar卡号在全平台关联的不同Pan卡号数量
     * @return int
     */
    public function checkAadhaarMatchPanCnt()
    {
        if(empty($this->data->loanPerson->aadhaar_md5)){
            return -1;
        }
        $aadhaar_number = $this->data->loanPerson->aadhaar_md5;
        $pans = ArrayHelper::getColumn(
            LoanPerson::find()
                ->select(['pan_code'])
                ->where(['aadhaar_md5' => $aadhaar_number, 'merchant_id' => $this->data->order->merchant_id])
                ->andWhere(['is not','pan_code',null])
                ->asArray()->all(),
            'pan_code');
        return count(array_unique($pans));
    }

    /**
     * 近90天该Pan卡号在全平台关联的不同手机号数量
     * @return int
     */
    public function checkLast90dPanMatchPhoneCnt()
    {
        $pan_code = $this->data->loanPerson->pan_code;
        $lastTime = strtotime('-90 day');
        $phone = ArrayHelper::getColumn(
            LoanPerson::find()
                ->select(['phone'])
                ->where(['pan_code' => $pan_code, 'merchant_id' => $this->data->order->merchant_id])
                ->andWhere(['>=', 'created_at', $lastTime])
                ->asArray()->all(),
            'phone');
        return count(array_unique($phone));
    }

    /**
     * 近90天该手机号在全平台关联的不同Pan卡号数量
     * @return int
     */
    public function checkLast90dPhoneMatchPanCnt()
    {
        $phone = $this->data->loanPerson->phone;
        $lastTime = strtotime('-90 day');
        $pan = ArrayHelper::getColumn(
            LoanPerson::find()
                ->select(['pan_code'])
                ->where(['phone' => $phone, 'merchant_id' => $this->data->order->merchant_id])
                ->andWhere(['>=', 'created_at', $lastTime])
                ->andWhere(['is not','pan_code',null])
                ->asArray()->all(),
            'pan_code');
        return count(array_unique($pan));
    }

    /**
     * 最近30天最近一次成功调用的征信报告
     * @return int
     */
    public function checkIsLast30dCreditReportReturned()
    {
        $time = $this->data->order->order_time - 30 * 86400;
        $cibilReport = UserCreditReportCibil::find()
            ->select(['id','query_time'])
            ->where(['pan_code' => $this->data->loanPerson->pan_code,
                     'status' => UserCreditReportCibil::STATUS_SUCCESS])
            ->andWhere(['>=', 'query_time', $time])
            ->orderBy(['query_time' => SORT_DESC])
            ->one();

        $experianReport = UserCreditReportExperian::find()
            ->select(['id','query_time'])
            ->where(['pan_code' => $this->data->loanPerson->pan_code,
                     'status' => UserCreditReportExperian::STATUS_SUCCESS])
            ->andWhere(['>=', 'query_time', $time])
            ->orderBy(['query_time' => SORT_DESC])
            ->one();

        if(empty($cibilReport) && empty($experianReport)){
            $cibilReportLoan = UserCreditReportCibil::find()
                ->select(['id','query_time'])
                ->where(['pan_code' => $this->data->loanPerson->pan_code,
                         'status' => UserCreditReportCibil::STATUS_SUCCESS])
                ->andWhere(['>=', 'query_time', $time])
                ->orderBy(['query_time' => SORT_DESC])
                ->one(Yii::$app->db_loan);

            $experianReportLoan = UserCreditReportExperian::find()
                ->select(['id','query_time'])
                ->where(['pan_code' => $this->data->loanPerson->pan_code,
                         'status' => UserCreditReportExperian::STATUS_SUCCESS])
                ->andWhere(['>=', 'query_time', $time])
                ->orderBy(['query_time' => SORT_DESC])
                ->one(Yii::$app->db_loan);

            if(empty($cibilReportLoan) && empty($experianReportLoan)){
                return -1;
            }

            $cibil_query_time_loan = $cibilReportLoan['query_time'] ?? 0;
            $experian_query_time_loan = $experianReportLoan['query_time'] ?? 0;

            if($cibil_query_time_loan > $experian_query_time_loan){
                return 1;
            }else{
                return 0;
            }
        }

        $cibil_query_time = $cibilReport['query_time'] ?? 0;
        $experian_query_time = $experianReport['query_time'] ?? 0;

        if($cibil_query_time > $experian_query_time){
            return 1;
        }else{
            return 0;
        }
    }

    /*
     * 语言校验项回答正确的问题数量
     * @return int
     */
    public function checkCorrectQuesNumOfLanguageValidation(){
        $userQuestionReport = $this->data->userQuestionReport;
        if(empty($userQuestionReport)){
            return -1;
        }

        return $userQuestionReport->correct_num;
    }

    /**
     * 语言校验项问题回答的用时时间
     * @return int
     */
    public function checkTimeUsedInLanguageValidation(){
        $userQuestionReport = $this->data->userQuestionReport;
        if(empty($userQuestionReport)){
            return -1;
        }

        return $userQuestionReport->submit_time - $userQuestionReport->enter_time;
    }

    /**
     * 居住地址城市是否为11月及以后新增的城市
     * @return int
     */
    public function checkResidentialCityHitNewlyAddedCityList(){
        $userWorkInfo = $this->data->userWorkInfo;

        if(empty($userWorkInfo)){
            return -1;
        }

        if(UserQuestionVerification::checkCity($userWorkInfo->residential_address2)){
            return 1;
        }

        return 0;

    }

    /**
     * 该手机号历史曾试图关联过的不同Pan卡号数量
     * @return int
     */
    public function checkPhoneHisTryMatchPanCnt(){
        $data = ArrayHelper::getColumn(
            UserPanCheckLog::find()
                ->select(['pan_input'])
                ->where(['user_id' => $this->data->loanPerson->id])
                ->groupBy(['pan_input'])
                ->asArray()->all(),
            'pan_input');
        return count($data);
    }

    /**
     * 总平台该手机号历史曾试图关联过的不同Pan卡号数量
     * @return int
     */
    public function checkPhoneHisTryMatchPanCntTotPlatform(){
        $userIds = $this->getPhoneAllUserIds($this->data->loanPerson->phone);
        $data = ArrayHelper::getColumn(
            UserPanCheckLog::find()
                ->select(['pan_input'])
                ->where(['user_id' => $userIds])
                ->groupBy(['pan_input'])
                ->asArray()->all(),
            'pan_input');

        $userIds_loan = $this->getLoanPhoneUserIds($this->data->loanPerson->phone);
        $data_loan = ArrayHelper::getColumn(
            UserPanCheckLog::find()
                ->select(['pan_input'])
                ->where(['user_id' => $userIds_loan])
                ->groupBy(['pan_input'])
                ->asArray()->all(Yii::$app->db_loan),
            'pan_input');
        return count(array_unique(array_merge($data, $data_loan)));
    }

    /**
     * 该手机号历史曾试图关联过的不同Aadhaar卡号数量
     * @return int
     */
    public function checkPhoneHisTryMatchAadhaarCnt(){
        $data = ArrayHelper::getColumn(
            UserCreditReportOcrAad::find()
                ->select(['card_no_md5'])
                ->where(['user_id' => $this->data->loanPerson->id])
                ->andWhere(['report_status' => CreditReportStatus::PASS()->getValue()])
                ->groupBy(['card_no_md5'])
                ->asArray()->all(),
            'card_no_md5');
        return count($data);
    }

    /**
     * 总平台该手机号历史曾试图关联过的不同Aadhaar卡号数量
     * @return int
     */
    public function checkPhoneHisTryMatchAadhaarCntTotPlatform(){
        $userIds = $this->getPhoneAllUserIds($this->data->loanPerson->phone);
        $data = ArrayHelper::getColumn(
            UserCreditReportOcrAad::find()
                ->select(['card_no_md5'])
                ->where(['user_id' => $userIds])
                ->andWhere(['report_status' => CreditReportStatus::PASS()->getValue()])
                ->groupBy(['card_no_md5'])
                ->asArray()->all(),
            'card_no_md5');

        $userIds_loan = $this->getLoanPhoneUserIds($this->data->loanPerson->phone);
        $data_loan = ArrayHelper::getColumn(
            UserCreditReportOcrAad::find()
                ->select(['card_no_md5'])
                ->where(['user_id' => $userIds_loan])
                ->andWhere(['report_status' => CreditReportStatus::PASS()->getValue()])
                ->groupBy(['card_no_md5'])
                ->asArray()->all(Yii::$app->db_loan),
            'card_no_md5');
        return count(array_unique(array_merge($data, $data_loan)));
    }

    /**
     * 该手机号历史曾试图关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkPhoneHIsTryMatchSMDeviceIDCnt(){
        $data = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $this->data->loanPerson->id])
                ->groupBy(['szlm_query_id'])
                ->asArray()->all(),
            'szlm_query_id');
        return count($data);
    }

    /**
     * 总平台该手机号历史曾试图关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkPhoneHIsTryMatchSMDeviceIDCntTotPlatform(){
        $userIds = $this->getPhoneAllUserIds($this->data->loanPerson->phone);
        $data = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $userIds])
                ->groupBy(['szlm_query_id'])
                ->asArray()->all(),
            'szlm_query_id');

        $userIds_loan = $this->getLoanPhoneUserIds($this->data->loanPerson->phone);
        $data_loan = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $userIds_loan])
                ->groupBy(['szlm_query_id'])
                ->asArray()->all(Yii::$app->db_loan),
            'szlm_query_id');
        return count(array_unique(array_merge($data, $data_loan)));
    }

    /**
     * 近30天内该手机号曾试图关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkPhoneLast30dTryMatchSMdeviceIDCnt(){
        $begin_time = $this->data->order->order_time - 30 * 86400;
        $data = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $this->data->loanPerson->id])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['szlm_query_id'])
                ->asArray()->all(),
            'szlm_query_id');
        return count($data);
    }

    /**
     * 总平台近30天内该手机号曾试图关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkPhoneLast30dTryMatchSMdeviceIDCntTotPlatform(){
        $begin_time = $this->data->order->order_time - 30 * 86400;
        $userIds = $this->getPhoneAllUserIds($this->data->loanPerson->phone);
        $data = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $userIds])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['szlm_query_id'])
                ->asArray()->all(),
            'szlm_query_id');

        $userIds_loan = $this->getLoanPhoneUserIds($this->data->loanPerson->phone);
        $data_loan = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $userIds_loan])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['szlm_query_id'])
                ->asArray()->all(Yii::$app->db_loan),
            'szlm_query_id');
        return count(array_unique(array_merge($data, $data_loan)));
    }

    /**
     * 近60天内该手机号曾试图关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkPhoneLast60dTryMatchSMdeviceIDCnt(){
        $begin_time = $this->data->order->order_time - 60 * 86400;
        $data = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $this->data->loanPerson->id])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['szlm_query_id'])
                ->asArray()->all(),
            'szlm_query_id');
        return count($data);
    }

    /**
     * 总平台近60天内该手机号曾试图关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkPhoneLast60dTryMatchSMdeviceIDCntTotPlatform(){
        $begin_time = $this->data->order->order_time - 60 * 86400;
        $userIds = $this->getPhoneAllUserIds($this->data->loanPerson->phone);
        $data = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $userIds])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['szlm_query_id'])
                ->asArray()->all(),
            'szlm_query_id');

        $userIds_loan = $this->getLoanPhoneUserIds($this->data->loanPerson->phone);
        $data_loan = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $userIds_loan])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['szlm_query_id'])
                ->asArray()->all(Yii::$app->db_loan),
            'szlm_query_id');
        return count(array_unique(array_merge($data, $data_loan)));
    }

    /**
     * 近90天内该手机号曾试图关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkPhoneLast90dTryMatchSMdeviceIDCnt(){
        $begin_time = $this->data->order->order_time - 90 * 86400;
        $data = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $this->data->loanPerson->id])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['szlm_query_id'])
                ->asArray()->all(),
            'szlm_query_id');
        return count($data);
    }

    /**
     * 总平台近90天内该手机号曾试图关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkPhoneLast90dTryMatchSMdeviceIDCntTotPlatform(){
        $begin_time = $this->data->order->order_time - 90 * 86400;
        $userIds = $this->getPhoneAllUserIds($this->data->loanPerson->phone);
        $data = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $userIds])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['szlm_query_id'])
                ->asArray()->all(),
            'szlm_query_id');

        $userIds_loan = $this->getLoanPhoneUserIds($this->data->loanPerson->phone);
        $data_loan = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $userIds_loan])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['szlm_query_id'])
                ->asArray()->all(Yii::$app->db_loan),
            'szlm_query_id');
        return count(array_unique(array_merge($data, $data_loan)));
    }

    /**
     * 该Pan卡号历史曾试图关联过的不同手机号数量
     * @return int
     */
    public function checkPanHisTryMatchPhoneCnt(){
        $data = ArrayHelper::getColumn(
            UserPanCheckLog::find()
                ->from(UserPanCheckLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.id'])
                ->where(['l.pan_input' => $this->data->loanPerson->pan_code, 'p.source_id' => $this->data->loanPerson->source_id])
                ->groupBy(['p.id'])
                ->asArray()->all(),
            'id');
        return count($data);
    }

    /**
     * 总平台该Pan卡号历史曾试图关联过的不同手机号数量
     * @return int
     */
    public function checkPanHisTryMatchPhoneCntTotPlatform(){
        $data = ArrayHelper::getColumn(
            UserPanCheckLog::find()
                ->from(UserPanCheckLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.phone'])
                ->where(['l.pan_input' => $this->data->loanPerson->pan_code])
                ->groupBy(['p.phone'])
                ->asArray()->all(),
            'phone');

        $data_loan = ArrayHelper::getColumn(
            UserPanCheckLog::find()
                ->from(UserPanCheckLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.phone'])
                ->where(['l.pan_input' => $this->data->loanPerson->pan_code])
                ->groupBy(['p.phone'])
                ->asArray()->all(Yii::$app->db_loan),
            'phone');
        return count(array_unique(array_merge($data, $data_loan)));
    }

    /**
     * 该Pan卡号历史曾试图关联过的不同Aadhaar卡号数量
     * @return int
     */
    public function checkPanHisTryMatchAadhaarCnt(){
        $user_ids = ArrayHelper::getColumn(
            UserPanCheckLog::find()
                ->from(UserPanCheckLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.id'])
                ->where(['l.pan_input' => $this->data->loanPerson->pan_code, 'p.source_id' => $this->data->loanPerson->source_id])
                ->groupBy(['p.id'])
                ->asArray()->all(),
            'id');

        $data = ArrayHelper::getColumn(
            UserCreditReportOcrAad::find()
                ->select(['card_no_md5'])
                ->where(['user_id' => $user_ids])
                ->andWhere(['report_status' => CreditReportStatus::PASS()->getValue()])
                ->groupBy(['card_no_md5'])
                ->asArray()->all(),
            'card_no_md5');
        return count($data);
    }

    /**
     * 总平台该Pan卡号历史曾试图关联过的不同Aadhaar卡号数量
     * @return int
     */
    public function checkPanHisTryMatchAadhaarCntTotPlatform(){
        $user_ids = ArrayHelper::getColumn(
            UserPanCheckLog::find()
                ->from(UserPanCheckLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.id'])
                ->where(['l.pan_input' => $this->data->loanPerson->pan_code])
                ->groupBy(['p.id'])
                ->asArray()->all(),
            'id');

        $data = ArrayHelper::getColumn(
            UserCreditReportOcrAad::find()
                ->select(['card_no_md5'])
                ->where(['user_id' => $user_ids])
                ->andWhere(['report_status' => CreditReportStatus::PASS()->getValue()])
                ->groupBy(['card_no_md5'])
                ->asArray()->all(),
            'card_no_md5');

        $user_ids_loan = ArrayHelper::getColumn(
            UserPanCheckLog::find()
                ->from(UserPanCheckLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.id'])
                ->where(['l.pan_input' => $this->data->loanPerson->pan_code])
                ->groupBy(['p.id'])
                ->asArray()->all(Yii::$app->db_loan),
            'id');

        $data_loan = ArrayHelper::getColumn(
            UserCreditReportOcrAad::find()
                ->select(['card_no_md5'])
                ->where(['user_id' => $user_ids_loan])
                ->andWhere(['report_status' => CreditReportStatus::PASS()->getValue()])
                ->groupBy(['card_no_md5'])
                ->asArray()->all(Yii::$app->db_loan),
            'card_no_md5');

        return count(array_unique(array_merge($data, $data_loan)));
    }

    /**
     * 该Pan卡号历史曾试图关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkPanHisTryMatchSMDeviceIDCnt(){
        $user_ids = ArrayHelper::getColumn(
            UserPanCheckLog::find()
                ->from(UserPanCheckLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.id'])
                ->where(['l.pan_input' => $this->data->loanPerson->pan_code, 'p.source_id' => $this->data->loanPerson->source_id])
                ->groupBy(['p.id'])
                ->asArray()->all(),
            'id');

        $data = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $user_ids])
                ->groupBy(['szlm_query_id'])
                ->asArray()->all(),
            'szlm_query_id');
        return count($data);
    }

    /**
     * 总平台该Pan卡号历史曾试图关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkPanHisTryMatchSMDeviceIDCntTotPlatform(){
        $user_ids = ArrayHelper::getColumn(
            UserPanCheckLog::find()
                ->from(UserPanCheckLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.id'])
                ->where(['l.pan_input' => $this->data->loanPerson->pan_code])
                ->groupBy(['p.id'])
                ->asArray()->all(),
            'id');

        $data = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $user_ids])
                ->groupBy(['szlm_query_id'])
                ->asArray()->all(),
            'szlm_query_id');

        $user_ids_loan = ArrayHelper::getColumn(
            UserPanCheckLog::find()
                ->from(UserPanCheckLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.id'])
                ->where(['l.pan_input' => $this->data->loanPerson->pan_code])
                ->groupBy(['p.id'])
                ->asArray()->all(Yii::$app->db_loan),
            'id');

        $data_loan = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $user_ids_loan])
                ->groupBy(['szlm_query_id'])
                ->asArray()->all(Yii::$app->db_loan),
            'szlm_query_id');

        return count(array_unique(array_merge($data, $data_loan)));
    }

    /**
     * 近30天内该Pan卡号曾试图关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkPanLast30dTryMatchSMdeviceIDCnt(){
        $begin_time = $this->data->order->order_time - 30 * 86400;
        $user_ids = ArrayHelper::getColumn(
            UserPanCheckLog::find()
                ->from(UserPanCheckLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.id'])
                ->where(['l.pan_input' => $this->data->loanPerson->pan_code, 'p.source_id' => $this->data->loanPerson->source_id])
                ->andWhere(['>=', 'l.created_at', $begin_time])
                ->groupBy(['p.id'])
                ->asArray()->all(),
            'id');

        $data = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $user_ids])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['szlm_query_id'])
                ->asArray()->all(),
            'szlm_query_id');
        return count($data);
    }

    /**
     * 总平台近30天内该Pan卡号曾试图关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkPanLast30dTryMatchSMdeviceIDCntTotPlatform(){
        $begin_time = $this->data->order->order_time - 30 * 86400;
        $user_ids = ArrayHelper::getColumn(
            UserPanCheckLog::find()
                ->from(UserPanCheckLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.id'])
                ->where(['l.pan_input' => $this->data->loanPerson->pan_code])
                ->andWhere(['>=', 'l.created_at', $begin_time])
                ->groupBy(['p.id'])
                ->asArray()->all(),
            'id');

        $data = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $user_ids])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['szlm_query_id'])
                ->asArray()->all(),
            'szlm_query_id');

        $user_ids_loan = ArrayHelper::getColumn(
            UserPanCheckLog::find()
                ->from(UserPanCheckLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.id'])
                ->where(['l.pan_input' => $this->data->loanPerson->pan_code])
                ->andWhere(['>=', 'l.created_at', $begin_time])
                ->groupBy(['p.id'])
                ->asArray()->all(Yii::$app->db_loan),
            'id');

        $data_loan = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $user_ids_loan])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['szlm_query_id'])
                ->asArray()->all(Yii::$app->db_loan),
            'szlm_query_id');
        return count(array_unique(array_merge($data, $data_loan)));
    }

    /**
     * 近60天内该Pan卡号曾试图关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkPanLast60dTryMatchSMdeviceIDCnt(){
        $begin_time = $this->data->order->order_time - 60 * 86400;
        $user_ids = ArrayHelper::getColumn(
            UserPanCheckLog::find()
                ->from(UserPanCheckLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.id'])
                ->where(['l.pan_input' => $this->data->loanPerson->pan_code, 'p.source_id' => $this->data->loanPerson->source_id])
                ->andWhere(['>=', 'l.created_at', $begin_time])
                ->groupBy(['p.id'])
                ->asArray()->all(),
            'id');

        $data = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $user_ids])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['szlm_query_id'])
                ->asArray()->all(),
            'szlm_query_id');
        return count($data);
    }

    /**
     * 总平台近60天内该Pan卡号曾试图关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkPanLast60dTryMatchSMdeviceIDCntTotPlatform(){
        $begin_time = $this->data->order->order_time - 60 * 86400;
        $user_ids = ArrayHelper::getColumn(
            UserPanCheckLog::find()
                ->from(UserPanCheckLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.id'])
                ->where(['l.pan_input' => $this->data->loanPerson->pan_code])
                ->andWhere(['>=', 'l.created_at', $begin_time])
                ->groupBy(['p.id'])
                ->asArray()->all(),
            'id');

        $data = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $user_ids])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['szlm_query_id'])
                ->asArray()->all(),
            'szlm_query_id');

        $user_ids_loan = ArrayHelper::getColumn(
            UserPanCheckLog::find()
                ->from(UserPanCheckLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.id'])
                ->where(['l.pan_input' => $this->data->loanPerson->pan_code])
                ->andWhere(['>=', 'l.created_at', $begin_time])
                ->groupBy(['p.id'])
                ->asArray()->all(Yii::$app->db_loan),
            'id');

        $data_loan = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $user_ids_loan])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['szlm_query_id'])
                ->asArray()->all(Yii::$app->db_loan),
            'szlm_query_id');
        return count(array_unique(array_merge($data, $data_loan)));
    }

    /**
     * 近90天内该Pan卡号曾试图关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkPanLast90dTryMatchSMdeviceIDCnt(){
        $begin_time = $this->data->order->order_time - 90 * 86400;
        $user_ids = ArrayHelper::getColumn(
            UserPanCheckLog::find()
                ->from(UserPanCheckLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.id'])
                ->where(['l.pan_input' => $this->data->loanPerson->pan_code, 'p.source_id' => $this->data->loanPerson->source_id])
                ->andWhere(['>=', 'l.created_at', $begin_time])
                ->groupBy(['p.id'])
                ->asArray()->all(),
            'id');

        $data = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $user_ids])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['szlm_query_id'])
                ->asArray()->all(),
            'szlm_query_id');
        return count($data);
    }

    /**
     * 总平台近90天内该Pan卡号曾试图关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkPanLast90dTryMatchSMdeviceIDCntTotPlatform(){
        $begin_time = $this->data->order->order_time - 90 * 86400;
        $user_ids = ArrayHelper::getColumn(
            UserPanCheckLog::find()
                ->from(UserPanCheckLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.id'])
                ->where(['l.pan_input' => $this->data->loanPerson->pan_code])
                ->andWhere(['>=', 'l.created_at', $begin_time])
                ->groupBy(['p.id'])
                ->asArray()->all(),
            'id');

        $data = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $user_ids])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['szlm_query_id'])
                ->asArray()->all(),
            'szlm_query_id');

        $user_ids_loan = ArrayHelper::getColumn(
            UserPanCheckLog::find()
                ->from(UserPanCheckLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.id'])
                ->where(['l.pan_input' => $this->data->loanPerson->pan_code])
                ->andWhere(['>=', 'l.created_at', $begin_time])
                ->groupBy(['p.id'])
                ->asArray()->all(Yii::$app->db_loan),
            'id');

        $data_loan = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $user_ids_loan])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['szlm_query_id'])
                ->asArray()->all(Yii::$app->db_loan),
            'szlm_query_id');
        return count(array_unique(array_merge($data, $data_loan)));
    }

    /**
     * 该Aadhaar卡号历史曾试图关联过的不同Pan卡号数量
     * @return int
     */
    public function checkAadhaarHisTryMatchPanCnt(){
        $user_ids = ArrayHelper::getColumn(
            UserCreditReportOcrAad::find()
                ->from(UserCreditReportOcrAad::tableName(). ' as a')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=a.user_id')
                ->select(['p.id'])
                ->where(['a.card_no_md5' => $this->data->loanPerson->aadhaar_md5,
                         'p.source_id' => $this->data->loanPerson->source_id,
                         'a.report_status' => CreditReportStatus::PASS()->getValue()])
                ->groupBy(['p.id'])
                ->asArray()->all(),
            'id');

        $data = ArrayHelper::getColumn(
            UserPanCheckLog::find()
                ->select(['pan_input'])
                ->where(['user_id' => $user_ids])
                ->groupBy(['pan_input'])
                ->asArray()->all(),
            'pan_input');

        return count($data);
    }

    /**
     * 总平台该Aadhaar卡号历史曾试图关联过的不同Pan卡号数量
     * @return int
     */
    public function checkAadhaarHisTryMatchPanCntTotPlatform(){
        $user_ids = ArrayHelper::getColumn(
            UserCreditReportOcrAad::find()
                ->from(UserCreditReportOcrAad::tableName(). ' as a')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=a.user_id')
                ->select(['p.id'])
                ->where(['a.card_no_md5' => $this->data->loanPerson->aadhaar_md5,
                         'a.report_status' => CreditReportStatus::PASS()->getValue()])
                ->groupBy(['p.id'])
                ->asArray()->all(),
            'id');

        $data = ArrayHelper::getColumn(
            UserPanCheckLog::find()
                ->select(['pan_input'])
                ->where(['user_id' => $user_ids])
                ->groupBy(['pan_input'])
                ->asArray()->all(),
            'pan_input');

        $user_ids_loan = ArrayHelper::getColumn(
            UserCreditReportOcrAad::find()
                ->from(UserCreditReportOcrAad::tableName(). ' as a')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=a.user_id')
                ->select(['p.id'])
                ->where(['a.card_no_md5' => $this->data->loanPerson->aadhaar_md5,
                         'a.report_status' => CreditReportStatus::PASS()->getValue()])
                ->groupBy(['p.id'])
                ->asArray()->all(Yii::$app->db_loan),
            'id');

        $data_loan = ArrayHelper::getColumn(
            UserPanCheckLog::find()
                ->select(['pan_input'])
                ->where(['user_id' => $user_ids_loan])
                ->groupBy(['pan_input'])
                ->asArray()->all(Yii::$app->db_loan),
            'pan_input');

        return count(array_unique(array_merge($data, $data_loan)));
    }

    /**
     * 该Aadhaar卡号历史曾试图关联过的不同手机号数量
     * @return int
     */
    public function checkAadhaarHisTryMatchPhoneCnt(){
        $data = ArrayHelper::getColumn(
            UserCreditReportOcrAad::find()
                ->from(UserCreditReportOcrAad::tableName(). ' as a')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=a.user_id')
                ->select(['p.id'])
                ->where(['a.card_no_md5' => $this->data->loanPerson->aadhaar_md5,
                         'p.source_id' => $this->data->loanPerson->source_id,
                         'a.report_status' => CreditReportStatus::PASS()->getValue()])
                ->groupBy(['p.id'])
                ->asArray()->all(),
            'id');
        return count($data);
    }

    /**
     * 总平台该Aadhaar卡号历史曾试图关联过的不同手机号数量
     * @return int
     */
    public function checkAadhaarHisTryMatchPhoneCntTotPlatform(){
        $data = ArrayHelper::getColumn(
            UserCreditReportOcrAad::find()
                ->from(UserCreditReportOcrAad::tableName(). ' as a')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=a.user_id')
                ->select(['p.phone'])
                ->where(['a.card_no_md5' => $this->data->loanPerson->aadhaar_md5,
                         'a.report_status' => CreditReportStatus::PASS()->getValue()])
                ->groupBy(['p.phone'])
                ->asArray()->all(),
            'phone');

        $data_loan = ArrayHelper::getColumn(
            UserCreditReportOcrAad::find()
                ->from(UserCreditReportOcrAad::tableName(). ' as a')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=a.user_id')
                ->select(['p.phone'])
                ->where(['a.card_no_md5' => $this->data->loanPerson->aadhaar_md5,
                         'a.report_status' => CreditReportStatus::PASS()->getValue()])
                ->groupBy(['p.phone'])
                ->asArray()->all(Yii::$app->db_loan),
            'phone');

        return count(array_unique(array_merge($data, $data_loan)));
    }

    /**
     * 该Aadhaar卡号历史曾试图关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkAadhaarHisTryMatchSMDeviceIDCnt(){
        $user_ids = ArrayHelper::getColumn(
            UserCreditReportOcrAad::find()
                ->from(UserCreditReportOcrAad::tableName(). ' as a')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=a.user_id')
                ->select(['p.id'])
                ->where(['a.card_no_md5' => $this->data->loanPerson->aadhaar_md5,
                         'p.source_id' => $this->data->loanPerson->source_id,
                         'a.report_status' => CreditReportStatus::PASS()->getValue()])
                ->groupBy(['p.id'])
                ->asArray()->all(),
            'id');

        $data = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $user_ids])
                ->groupBy(['szlm_query_id'])
                ->asArray()->all(),
            'szlm_query_id');
        return count($data);
    }

    /**
     * 总平台该Aadhaar卡号历史曾试图关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkAadhaarHisTryMatchSMDeviceIDCntTotPlatform(){
        $user_ids = ArrayHelper::getColumn(
            UserCreditReportOcrAad::find()
                ->from(UserCreditReportOcrAad::tableName(). ' as a')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=a.user_id')
                ->select(['p.id'])
                ->where(['a.card_no_md5' => $this->data->loanPerson->aadhaar_md5,
                         'a.report_status' => CreditReportStatus::PASS()->getValue()])
                ->groupBy(['p.id'])
                ->asArray()->all(),
            'id');

        $data = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $user_ids])
                ->groupBy(['szlm_query_id'])
                ->asArray()->all(),
            'szlm_query_id');

        $user_ids_loan = ArrayHelper::getColumn(
            UserCreditReportOcrAad::find()
                ->from(UserCreditReportOcrAad::tableName(). ' as a')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=a.user_id')
                ->select(['p.id'])
                ->where(['a.card_no_md5' => $this->data->loanPerson->aadhaar_md5,
                         'a.report_status' => CreditReportStatus::PASS()->getValue()])
                ->groupBy(['p.id'])
                ->asArray()->all(Yii::$app->db_loan),
            'id');

        $data_loan = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $user_ids_loan])
                ->groupBy(['szlm_query_id'])
                ->asArray()->all(Yii::$app->db_loan),
            'szlm_query_id');

        return count(array_unique(array_merge($data, $data_loan)));
    }

    /**
     * 近30天内该Aadhaar卡号曾试图关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkAadhaarLast30dTryMatchSMDeviceIDCnt(){
        $begin_time = $this->data->order->order_time - 30 * 86400;

        $user_ids = ArrayHelper::getColumn(
            UserCreditReportOcrAad::find()
                ->from(UserCreditReportOcrAad::tableName(). ' as a')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=a.user_id')
                ->select(['p.id'])
                ->where(['a.card_no_md5' => $this->data->loanPerson->aadhaar_md5,
                         'p.source_id' => $this->data->loanPerson->source_id,
                         'a.report_status' => CreditReportStatus::PASS()->getValue()])
                ->andWhere(['>=', 'a.created_at', $begin_time])
                ->groupBy(['p.id'])
                ->asArray()->all(),
            'id');

        $data = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $user_ids])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['szlm_query_id'])
                ->asArray()->all(),
            'szlm_query_id');
        return count($data);
    }

    /**
     * 总平台近30天内该Aadhaar卡号曾试图关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkAadhaarLast30dTryMatchSMDeviceIDCntTotPlatform(){
        $begin_time = $this->data->order->order_time - 30 * 86400;

        $user_ids = ArrayHelper::getColumn(
            UserCreditReportOcrAad::find()
                ->from(UserCreditReportOcrAad::tableName(). ' as a')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=a.user_id')
                ->select(['p.id'])
                ->where(['a.card_no_md5' => $this->data->loanPerson->aadhaar_md5,
                         'a.report_status' => CreditReportStatus::PASS()->getValue()])
                ->andWhere(['>=', 'a.created_at', $begin_time])
                ->groupBy(['p.id'])
                ->asArray()->all(),
            'id');

        $data = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $user_ids])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['szlm_query_id'])
                ->asArray()->all(),
            'szlm_query_id');

        $user_ids_loan = ArrayHelper::getColumn(
            UserCreditReportOcrAad::find()
                ->from(UserCreditReportOcrAad::tableName(). ' as a')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=a.user_id')
                ->select(['p.id'])
                ->where(['a.card_no_md5' => $this->data->loanPerson->aadhaar_md5,
                         'a.report_status' => CreditReportStatus::PASS()->getValue()])
                ->andWhere(['>=', 'a.created_at', $begin_time])
                ->groupBy(['p.id'])
                ->asArray()->all(Yii::$app->db_loan),
            'id');

        $data_loan = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $user_ids_loan])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['szlm_query_id'])
                ->asArray()->all(Yii::$app->db_loan),
            'szlm_query_id');

        return count(array_unique(array_merge($data, $data_loan)));
    }

    /**
     * 近60天内该Aadhaar卡号曾试图关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkAadhaarLast60dTryMatchSMDeviceIDCnt(){
        $begin_time = $this->data->order->order_time - 60 * 86400;

        $user_ids = ArrayHelper::getColumn(
            UserCreditReportOcrAad::find()
                ->from(UserCreditReportOcrAad::tableName(). ' as a')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=a.user_id')
                ->select(['p.id'])
                ->where(['a.card_no_md5' => $this->data->loanPerson->aadhaar_md5,
                         'p.source_id' => $this->data->loanPerson->source_id,
                         'a.report_status' => CreditReportStatus::PASS()->getValue()])
                ->andWhere(['>=', 'a.created_at', $begin_time])
                ->groupBy(['p.id'])
                ->asArray()->all(),
            'id');

        $data = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $user_ids])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['szlm_query_id'])
                ->asArray()->all(),
            'szlm_query_id');
        return count($data);
    }

    /**
     * 总平台近60天内该Aadhaar卡号曾试图关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkAadhaarLast60dTryMatchSMDeviceIDCntTotPlatform(){
        $begin_time = $this->data->order->order_time - 60 * 86400;

        $user_ids = ArrayHelper::getColumn(
            UserCreditReportOcrAad::find()
                ->from(UserCreditReportOcrAad::tableName(). ' as a')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=a.user_id')
                ->select(['p.id'])
                ->where(['a.card_no_md5' => $this->data->loanPerson->aadhaar_md5,
                         'a.report_status' => CreditReportStatus::PASS()->getValue()])
                ->andWhere(['>=', 'a.created_at', $begin_time])
                ->groupBy(['p.id'])
                ->asArray()->all(),
            'id');

        $data = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $user_ids])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['szlm_query_id'])
                ->asArray()->all(),
            'szlm_query_id');

        $user_ids_loan = ArrayHelper::getColumn(
            UserCreditReportOcrAad::find()
                ->from(UserCreditReportOcrAad::tableName(). ' as a')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=a.user_id')
                ->select(['p.id'])
                ->where(['a.card_no_md5' => $this->data->loanPerson->aadhaar_md5,
                         'a.report_status' => CreditReportStatus::PASS()->getValue()])
                ->andWhere(['>=', 'a.created_at', $begin_time])
                ->groupBy(['p.id'])
                ->asArray()->all(Yii::$app->db_loan),
            'id');

        $data_loan = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $user_ids_loan])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['szlm_query_id'])
                ->asArray()->all(Yii::$app->db_loan),
            'szlm_query_id');

        return count(array_unique(array_merge($data, $data_loan)));
    }

    /**
     * 近90天内该Aadhaar卡号曾试图关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkAadhaarLast90dTryMatchSMDeviceIDCnt(){
        $begin_time = $this->data->order->order_time - 90 * 86400;

        $user_ids = ArrayHelper::getColumn(
            UserCreditReportOcrAad::find()
                ->from(UserCreditReportOcrAad::tableName(). ' as a')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=a.user_id')
                ->select(['p.id'])
                ->where(['a.card_no_md5' => $this->data->loanPerson->aadhaar_md5,
                         'p.source_id' => $this->data->loanPerson->source_id,
                         'a.report_status' => CreditReportStatus::PASS()->getValue()])
                ->andWhere(['>=', 'a.created_at', $begin_time])
                ->groupBy(['p.id'])
                ->asArray()->all(),
            'id');

        $data = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $user_ids])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['szlm_query_id'])
                ->asArray()->all(),
            'szlm_query_id');
        return count($data);
    }

    /**
     * 总平台近90天内该Aadhaar卡号曾试图关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkAadhaarLast90dTryMatchSMDeviceIDCntTotPlatform(){
        $begin_time = $this->data->order->order_time - 90 * 86400;

        $user_ids = ArrayHelper::getColumn(
            UserCreditReportOcrAad::find()
                ->from(UserCreditReportOcrAad::tableName(). ' as a')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=a.user_id')
                ->select(['p.id'])
                ->where(['a.card_no_md5' => $this->data->loanPerson->aadhaar_md5,
                         'a.report_status' => CreditReportStatus::PASS()->getValue()])
                ->andWhere(['>=', 'a.created_at', $begin_time])
                ->groupBy(['p.id'])
                ->asArray()->all(),
            'id');

        $data = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $user_ids])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['szlm_query_id'])
                ->asArray()->all(),
            'szlm_query_id');

        $user_ids_loan = ArrayHelper::getColumn(
            UserCreditReportOcrAad::find()
                ->from(UserCreditReportOcrAad::tableName(). ' as a')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=a.user_id')
                ->select(['p.id'])
                ->where(['a.card_no_md5' => $this->data->loanPerson->aadhaar_md5,
                         'a.report_status' => CreditReportStatus::PASS()->getValue()])
                ->andWhere(['>=', 'a.created_at', $begin_time])
                ->groupBy(['p.id'])
                ->asArray()->all(Yii::$app->db_loan),
            'id');

        $data_loan = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $user_ids_loan])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['szlm_query_id'])
                ->asArray()->all(Yii::$app->db_loan),
            'szlm_query_id');

        return count(array_unique(array_merge($data, $data_loan)));
    }

    /**
     * 该数盟设备ID历史曾试图关联过的不同手机号数量
     * @return int
     */
    public function checkSMDeviceIDHisTryMatchPhoneCnt(){
        $did = $this->data->order->did;
        if(empty($did)){
            $client = ClientInfoLog::find()->where(['user_id' => $this->data->loanPerson->id])
                ->andWhere(['is not', 'szlm_query_id', null])
                ->andWhere(['!=', 'szlm_query_id', ''])
                ->orderBy(['id' => SORT_DESC])->one();
            if(empty($client)){
                return -1;
            }

            $did = $client['szlm_query_id'];
        }

        $data = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->from(ClientInfoLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.id'])
                ->where(['l.szlm_query_id' => $did,
                        'p.source_id' => $this->data->loanPerson->source_id])
                ->groupBy(['p.id'])
                ->asArray()->all(),
            'id');
        return count($data);
    }

    /**
     * 总平台该数盟设备ID历史曾试图关联过的不同手机号数量
     * @return int
     */
    public function checkSMDeviceIDHisTryMatchPhoneCntTotPlatform(){
        $did = $this->data->order->did;
        if(empty($did)){
            $client = ClientInfoLog::find()->where(['user_id' => $this->data->loanPerson->id])
                ->andWhere(['is not', 'szlm_query_id', null])
                ->andWhere(['!=', 'szlm_query_id', ''])
                ->orderBy(['id' => SORT_DESC])->one();
            if(empty($client)){
                return -1;
            }

            $did = $client['szlm_query_id'];
        }

        $data = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->from(ClientInfoLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.phone'])
                ->where(['l.szlm_query_id' => $did])
                ->groupBy(['p.phone'])
                ->asArray()->all(),
            'phone');

        $data_loan = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->from(ClientInfoLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.phone'])
                ->where(['l.szlm_query_id' => $did])
                ->groupBy(['p.phone'])
                ->asArray()->all(Yii::$app->db_loan),
            'phone');

        return count(array_unique(array_merge($data, $data_loan)));
    }

    /**
     * 该数盟设备ID历史曾试图关联过的不同Pan卡号数量
     * @return int
     */
    public function checkSMDeviceIDHisTryMatchPanCnt(){
        $did = $this->data->order->did;
        if(empty($did)){
            $client = ClientInfoLog::find()->where(['user_id' => $this->data->loanPerson->id])
                ->andWhere(['is not', 'szlm_query_id', null])
                ->andWhere(['!=', 'szlm_query_id', ''])
                ->orderBy(['id' => SORT_DESC])->one();
            if(empty($client)){
                return -1;
            }

            $did = $client['szlm_query_id'];
        }

        $user_ids = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->from(ClientInfoLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.id'])
                ->where(['l.szlm_query_id' => $did,
                         'p.source_id' => $this->data->loanPerson->source_id])
                ->groupBy(['p.id'])
                ->asArray()->all(),
            'id');

        $data = ArrayHelper::getColumn(
            UserPanCheckLog::find()
                ->select(['pan_input'])
                ->where(['user_id' => $user_ids])
                ->groupBy(['pan_input'])
                ->asArray()->all(),
            'pan_input');
        return count($data);
    }

    /**
     * 总平台该数盟设备ID历史曾试图关联过的不同Pan卡号数量
     * @return int
     */
    public function checkSMDeviceIDHisTryMatchPanCntTotPlatform(){
        $did = $this->data->order->did;
        if(empty($did)){
            $client = ClientInfoLog::find()->where(['user_id' => $this->data->loanPerson->id])
                ->andWhere(['is not', 'szlm_query_id', null])
                ->andWhere(['!=', 'szlm_query_id', ''])
                ->orderBy(['id' => SORT_DESC])->one();
            if(empty($client)){
                return -1;
            }

            $did = $client['szlm_query_id'];
        }

        $user_ids = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->from(ClientInfoLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.id'])
                ->where(['l.szlm_query_id' => $did])
                ->groupBy(['p.id'])
                ->asArray()->all(),
            'id');

        $data = ArrayHelper::getColumn(
            UserPanCheckLog::find()
                ->select(['pan_input'])
                ->where(['user_id' => $user_ids])
                ->groupBy(['pan_input'])
                ->asArray()->all(),
            'pan_input');

        $user_ids_loan = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->from(ClientInfoLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.id'])
                ->where(['l.szlm_query_id' => $did])
                ->groupBy(['p.id'])
                ->asArray()->all(Yii::$app->db_loan),
            'id');

        $data_loan = ArrayHelper::getColumn(
            UserPanCheckLog::find()
                ->select(['pan_input'])
                ->where(['user_id' => $user_ids_loan])
                ->groupBy(['pan_input'])
                ->asArray()->all(Yii::$app->db_loan),
            'pan_input');

        return count(array_unique(array_merge($data, $data_loan)));
    }

    /**
     * 该数盟设备ID历史曾试图关联过的不同Aadhaar卡号数量
     * @return int
     */
    public function checkSMDeviceIDHisTryMatchAadhaarCnt(){
        $did = $this->data->order->did;
        if(empty($did)){
            $client = ClientInfoLog::find()->where(['user_id' => $this->data->loanPerson->id])
                ->andWhere(['is not', 'szlm_query_id', null])
                ->andWhere(['!=', 'szlm_query_id', ''])
                ->orderBy(['id' => SORT_DESC])->one();
            if(empty($client)){
                return -1;
            }

            $did = $client['szlm_query_id'];
        }

        $user_ids = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->from(ClientInfoLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.id'])
                ->where(['l.szlm_query_id' => $did,
                         'p.source_id' => $this->data->loanPerson->source_id])
                ->groupBy(['p.id'])
                ->asArray()->all(),
            'id');

        $data = ArrayHelper::getColumn(
            UserCreditReportOcrAad::find()
                ->select(['card_no_md5'])
                ->where(['user_id' => $user_ids])
                ->andWhere(['report_status' => CreditReportStatus::PASS()->getValue()])
                ->groupBy(['card_no_md5'])
                ->asArray()->all(),
            'card_no_md5');
        return count($data);
    }

    /**
     * 总平台该数盟设备ID历史曾试图关联过的不同Aadhaar卡号数量
     * @return int
     */
    public function checkSMDeviceIDHisTryMatchAadhaarCntTotPlatform(){
        $did = $this->data->order->did;
        if(empty($did)){
            $client = ClientInfoLog::find()->where(['user_id' => $this->data->loanPerson->id])
                ->andWhere(['is not', 'szlm_query_id', null])
                ->andWhere(['!=', 'szlm_query_id', ''])
                ->orderBy(['id' => SORT_DESC])->one();
            if(empty($client)){
                return -1;
            }

            $did = $client['szlm_query_id'];
        }

        $user_ids = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->from(ClientInfoLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.id'])
                ->where(['l.szlm_query_id' => $did])
                ->groupBy(['p.id'])
                ->asArray()->all(),
            'id');

        $data = ArrayHelper::getColumn(
            UserCreditReportOcrAad::find()
                ->select(['card_no_md5'])
                ->where(['user_id' => $user_ids])
                ->andWhere(['report_status' => CreditReportStatus::PASS()->getValue()])
                ->groupBy(['card_no_md5'])
                ->asArray()->all(),
            'card_no_md5');

        $user_ids_loan = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->from(ClientInfoLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.id'])
                ->where(['l.szlm_query_id' => $did])
                ->groupBy(['p.id'])
                ->asArray()->all(Yii::$app->db_loan),
            'id');

        $data_loan = ArrayHelper::getColumn(
            UserCreditReportOcrAad::find()
                ->select(['card_no_md5'])
                ->where(['user_id' => $user_ids_loan])
                ->andWhere(['report_status' => CreditReportStatus::PASS()->getValue()])
                ->groupBy(['card_no_md5'])
                ->asArray()->all(Yii::$app->db_loan),
            'card_no_md5');

        return count(array_unique(array_merge($data, $data_loan)));
    }

    /**
     * 近30天内该数盟设备ID曾试图关联过的不同手机号数量
     * @return int
     */
    public function checkSMDeviceIDLast30dTryMatchPhoneCnt(){
        $did = $this->data->order->did;
        if(empty($did)){
            $client = ClientInfoLog::find()->where(['user_id' => $this->data->loanPerson->id])
                ->andWhere(['is not', 'szlm_query_id', null])
                ->andWhere(['!=', 'szlm_query_id', ''])
                ->orderBy(['id' => SORT_DESC])->one();
            if(empty($client)){
                return -1;
            }

            $did = $client['szlm_query_id'];
        }

        $begin_time = $this->data->order->order_time - 30 * 86400;
        $data = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->from(ClientInfoLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.id'])
                ->where(['l.szlm_query_id' => $did,
                         'p.source_id' => $this->data->loanPerson->source_id])
                ->andWhere(['>=', 'l.created_at', $begin_time])
                ->groupBy(['p.id'])
                ->asArray()->all(),
            'id');
        return count($data);
    }

    /**
     * 总平台近30天内该数盟设备ID曾试图关联过的不同手机号数量
     * @return int
     */
    public function checkSMDeviceIDLast30dTryMatchPhoneCntTotPlatform(){
        $did = $this->data->order->did;
        if(empty($did)){
            $client = ClientInfoLog::find()->where(['user_id' => $this->data->loanPerson->id])
                ->andWhere(['is not', 'szlm_query_id', null])
                ->andWhere(['!=', 'szlm_query_id', ''])
                ->orderBy(['id' => SORT_DESC])->one();
            if(empty($client)){
                return -1;
            }

            $did = $client['szlm_query_id'];
        }

        $begin_time = $this->data->order->order_time - 30 * 86400;
        $data = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->from(ClientInfoLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.phone'])
                ->where(['l.szlm_query_id' => $did])
                ->andWhere(['>=', 'l.created_at', $begin_time])
                ->groupBy(['p.phone'])
                ->asArray()->all(),
            'phone');

        $data_loan = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->from(ClientInfoLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.phone'])
                ->where(['l.szlm_query_id' => $did])
                ->andWhere(['>=', 'l.created_at', $begin_time])
                ->groupBy(['p.phone'])
                ->asArray()->all(Yii::$app->db_loan),
            'phone');

        return count(array_unique(array_merge($data, $data_loan)));
    }

    /**
     * 近30天内该数盟设备ID曾试图关联过的不同Pan卡号数量
     * @return int
     */
    public function checkSMDeviceIDLast30dTryMatchPanCnt(){
        $did = $this->data->order->did;
        if(empty($did)){
            $client = ClientInfoLog::find()->where(['user_id' => $this->data->loanPerson->id])
                ->andWhere(['is not', 'szlm_query_id', null])
                ->andWhere(['!=', 'szlm_query_id', ''])
                ->orderBy(['id' => SORT_DESC])->one();
            if(empty($client)){
                return -1;
            }

            $did = $client['szlm_query_id'];
        }

        $begin_time = $this->data->order->order_time - 30 * 86400;
        $user_ids = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->from(ClientInfoLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.id'])
                ->where(['l.szlm_query_id' => $did,
                         'p.source_id' => $this->data->loanPerson->source_id])
                ->andWhere(['>=', 'l.created_at', $begin_time])
                ->groupBy(['p.id'])
                ->asArray()->all(),
            'id');

        $data = ArrayHelper::getColumn(
            UserPanCheckLog::find()
                ->select(['pan_input'])
                ->where(['user_id' => $user_ids])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['pan_input'])
                ->asArray()->all(),
            'pan_input');
        return count($data);
    }

    /**
     * 总平台近30天内该数盟设备ID曾试图关联过的不同Pan卡号数量
     * @return int
     */
    public function checkSMDeviceIDLast30dTryMatchPanCntTotPlatform(){
        $did = $this->data->order->did;
        if(empty($did)){
            $client = ClientInfoLog::find()->where(['user_id' => $this->data->loanPerson->id])
                ->andWhere(['is not', 'szlm_query_id', null])
                ->andWhere(['!=', 'szlm_query_id', ''])
                ->orderBy(['id' => SORT_DESC])->one();
            if(empty($client)){
                return -1;
            }

            $did = $client['szlm_query_id'];
        }

        $begin_time = $this->data->order->order_time - 30 * 86400;
        $user_ids = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->from(ClientInfoLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.id'])
                ->where(['l.szlm_query_id' => $did])
                ->andWhere(['>=', 'l.created_at', $begin_time])
                ->groupBy(['p.id'])
                ->asArray()->all(),
            'id');

        $data = ArrayHelper::getColumn(
            UserPanCheckLog::find()
                ->select(['pan_input'])
                ->where(['user_id' => $user_ids])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['pan_input'])
                ->asArray()->all(),
            'pan_input');

        $user_ids_loan = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->from(ClientInfoLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.id'])
                ->where(['l.szlm_query_id' => $did])
                ->andWhere(['>=', 'l.created_at', $begin_time])
                ->groupBy(['p.id'])
                ->asArray()->all(Yii::$app->db_loan),
            'id');

        $data_loan = ArrayHelper::getColumn(
            UserPanCheckLog::find()
                ->select(['pan_input'])
                ->where(['user_id' => $user_ids_loan])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['pan_input'])
                ->asArray()->all(Yii::$app->db_loan),
            'pan_input');

        return count(array_unique(array_merge($data, $data_loan)));
    }

    /**
     * 总平台近60天内该数盟设备ID曾试图关联过的不同Pan卡号数量
     * @return int
     */
    public function checkSMDeviceIDLast60dTryMatchPanCntTotPlatform(){
        $did = $this->data->order->did;
        if(empty($did)){
            $client = ClientInfoLog::find()->where(['user_id' => $this->data->loanPerson->id])
                ->andWhere(['is not', 'szlm_query_id', null])
                ->andWhere(['!=', 'szlm_query_id', ''])
                ->orderBy(['id' => SORT_DESC])->one();
            if(empty($client)){
                return -1;
            }

            $did = $client['szlm_query_id'];
        }

        $begin_time = $this->data->order->order_time - 60 * 86400;
        $user_ids = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->from(ClientInfoLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.id'])
                ->where(['l.szlm_query_id' => $did])
                ->andWhere(['>=', 'l.created_at', $begin_time])
                ->groupBy(['p.id'])
                ->asArray()->all(),
            'id');

        $data = ArrayHelper::getColumn(
            UserPanCheckLog::find()
                ->select(['pan_input'])
                ->where(['user_id' => $user_ids])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['pan_input'])
                ->asArray()->all(),
            'pan_input');

        $user_ids_loan = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->from(ClientInfoLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.id'])
                ->where(['l.szlm_query_id' => $did])
                ->andWhere(['>=', 'l.created_at', $begin_time])
                ->groupBy(['p.id'])
                ->asArray()->all(Yii::$app->db_loan),
            'id');

        $data_loan = ArrayHelper::getColumn(
            UserPanCheckLog::find()
                ->select(['pan_input'])
                ->where(['user_id' => $user_ids_loan])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['pan_input'])
                ->asArray()->all(Yii::$app->db_loan),
            'pan_input');

        return count(array_unique(array_merge($data, $data_loan)));
    }

    /**
     * 总平台近90天内该数盟设备ID曾试图关联过的不同Pan卡号数量
     * @return int
     */
    public function checkSMDeviceIDLast90dTryMatchPanCntTotPlatform(){
        $did = $this->data->order->did;
        if(empty($did)){
            $client = ClientInfoLog::find()->where(['user_id' => $this->data->loanPerson->id])
                ->andWhere(['is not', 'szlm_query_id', null])
                ->andWhere(['!=', 'szlm_query_id', ''])
                ->orderBy(['id' => SORT_DESC])->one();
            if(empty($client)){
                return -1;
            }

            $did = $client['szlm_query_id'];
        }

        $begin_time = $this->data->order->order_time - 90 * 86400;
        $user_ids = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->from(ClientInfoLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.id'])
                ->where(['l.szlm_query_id' => $did])
                ->andWhere(['>=', 'l.created_at', $begin_time])
                ->groupBy(['p.id'])
                ->asArray()->all(),
            'id');

        $data = ArrayHelper::getColumn(
            UserPanCheckLog::find()
                ->select(['pan_input'])
                ->where(['user_id' => $user_ids])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['pan_input'])
                ->asArray()->all(),
            'pan_input');

        $user_ids_loan = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->from(ClientInfoLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.id'])
                ->where(['l.szlm_query_id' => $did])
                ->andWhere(['>=', 'l.created_at', $begin_time])
                ->groupBy(['p.id'])
                ->asArray()->all(Yii::$app->db_loan),
            'id');

        $data_loan = ArrayHelper::getColumn(
            UserPanCheckLog::find()
                ->select(['pan_input'])
                ->where(['user_id' => $user_ids_loan])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['pan_input'])
                ->asArray()->all(Yii::$app->db_loan),
            'pan_input');

        return count(array_unique(array_merge($data, $data_loan)));
    }

    /**
     * 近30天内该数盟设备ID曾试图关联过的不同Aadhaar卡号数量
     * @return int
     */
    public function checkSMDeviceIDLast30dTryMatchAadhaarCnt(){
        $did = $this->data->order->did;
        if(empty($did)){
            $client = ClientInfoLog::find()->where(['user_id' => $this->data->loanPerson->id])
                ->andWhere(['is not', 'szlm_query_id', null])
                ->andWhere(['!=', 'szlm_query_id', ''])
                ->orderBy(['id' => SORT_DESC])->one();
            if(empty($client)){
                return -1;
            }

            $did = $client['szlm_query_id'];
        }

        $begin_time = $this->data->order->order_time - 30 * 86400;
        $user_ids = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->from(ClientInfoLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.id'])
                ->where(['l.szlm_query_id' => $did,
                         'p.source_id' => $this->data->loanPerson->source_id])
                ->andWhere(['>=', 'l.created_at', $begin_time])
                ->groupBy(['p.id'])
                ->asArray()->all(),
            'id');

        $data = ArrayHelper::getColumn(
            UserCreditReportOcrAad::find()
                ->select(['card_no_md5'])
                ->where(['user_id' => $user_ids])
                ->andWhere(['report_status' => CreditReportStatus::PASS()->getValue()])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['card_no_md5'])
                ->asArray()->all(),
            'card_no_md5');
        return count($data);
    }

    /**
     * 总平台近30天内该数盟设备ID曾试图关联过的不同Aadhaar卡号数量
     * @return int
     */
    public function checkSMDeviceIDLast30dTryMatchAadhaarCntTotPlatform(){
        $did = $this->data->order->did;
        if(empty($did)){
            $client = ClientInfoLog::find()->where(['user_id' => $this->data->loanPerson->id])
                ->andWhere(['is not', 'szlm_query_id', null])
                ->andWhere(['!=', 'szlm_query_id', ''])
                ->orderBy(['id' => SORT_DESC])->one();
            if(empty($client)){
                return -1;
            }

            $did = $client['szlm_query_id'];
        }

        $begin_time = $this->data->order->order_time - 30 * 86400;
        $user_ids = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->from(ClientInfoLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.id'])
                ->where(['l.szlm_query_id' => $did])
                ->andWhere(['>=', 'l.created_at', $begin_time])
                ->groupBy(['p.id'])
                ->asArray()->all(),
            'id');

        $data = ArrayHelper::getColumn(
            UserCreditReportOcrAad::find()
                ->select(['card_no_md5'])
                ->where(['user_id' => $user_ids])
                ->andWhere(['report_status' => CreditReportStatus::PASS()->getValue()])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['card_no_md5'])
                ->asArray()->all(),
            'card_no_md5');

        $user_ids_loan = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->from(ClientInfoLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.id'])
                ->where(['l.szlm_query_id' => $did])
                ->andWhere(['>=', 'l.created_at', $begin_time])
                ->groupBy(['p.id'])
                ->asArray()->all(Yii::$app->db_loan),
            'id');

        $data_loan = ArrayHelper::getColumn(
            UserCreditReportOcrAad::find()
                ->select(['card_no_md5'])
                ->where(['user_id' => $user_ids_loan])
                ->andWhere(['report_status' => CreditReportStatus::PASS()->getValue()])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['card_no_md5'])
                ->asArray()->all(Yii::$app->db_loan),
            'card_no_md5');

        return count(array_unique(array_merge($data, $data_loan)));
    }

    /**
     * 总平台近60天内该数盟设备ID曾试图关联过的不同Aadhaar卡号数量
     * @return int
     */
    public function checkSMDeviceIDLast60dTryMatchAadhaarCntTotPlatform(){
        $did = $this->data->order->did;
        if(empty($did)){
            $client = ClientInfoLog::find()->where(['user_id' => $this->data->loanPerson->id])
                ->andWhere(['is not', 'szlm_query_id', null])
                ->andWhere(['!=', 'szlm_query_id', ''])
                ->orderBy(['id' => SORT_DESC])->one();
            if(empty($client)){
                return -1;
            }

            $did = $client['szlm_query_id'];
        }

        $begin_time = $this->data->order->order_time - 60 * 86400;
        $user_ids = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->from(ClientInfoLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.id'])
                ->where(['l.szlm_query_id' => $did])
                ->andWhere(['>=', 'l.created_at', $begin_time])
                ->groupBy(['p.id'])
                ->asArray()->all(),
            'id');

        $data = ArrayHelper::getColumn(
            UserCreditReportOcrAad::find()
                ->select(['card_no_md5'])
                ->where(['user_id' => $user_ids])
                ->andWhere(['report_status' => CreditReportStatus::PASS()->getValue()])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['card_no_md5'])
                ->asArray()->all(),
            'card_no_md5');

        $user_ids_loan = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->from(ClientInfoLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.id'])
                ->where(['l.szlm_query_id' => $did])
                ->andWhere(['>=', 'l.created_at', $begin_time])
                ->groupBy(['p.id'])
                ->asArray()->all(Yii::$app->db_loan),
            'id');

        $data_loan = ArrayHelper::getColumn(
            UserCreditReportOcrAad::find()
                ->select(['card_no_md5'])
                ->where(['user_id' => $user_ids_loan])
                ->andWhere(['report_status' => CreditReportStatus::PASS()->getValue()])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['card_no_md5'])
                ->asArray()->all(Yii::$app->db_loan),
            'card_no_md5');

        return count(array_unique(array_merge($data, $data_loan)));
    }

    /**
     * 总平台近90天内该数盟设备ID曾试图关联过的不同Aadhaar卡号数量
     * @return int
     */
    public function checkSMDeviceIDLast90dTryMatchAadhaarCntTotPlatform(){
        $did = $this->data->order->did;
        if(empty($did)){
            $client = ClientInfoLog::find()->where(['user_id' => $this->data->loanPerson->id])
                ->andWhere(['is not', 'szlm_query_id', null])
                ->andWhere(['!=', 'szlm_query_id', ''])
                ->orderBy(['id' => SORT_DESC])->one();
            if(empty($client)){
                return -1;
            }

            $did = $client['szlm_query_id'];
        }

        $begin_time = $this->data->order->order_time - 90 * 86400;
        $user_ids = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->from(ClientInfoLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.id'])
                ->where(['l.szlm_query_id' => $did])
                ->andWhere(['>=', 'l.created_at', $begin_time])
                ->groupBy(['p.id'])
                ->asArray()->all(),
            'id');

        $data = ArrayHelper::getColumn(
            UserCreditReportOcrAad::find()
                ->select(['card_no_md5'])
                ->where(['user_id' => $user_ids])
                ->andWhere(['report_status' => CreditReportStatus::PASS()->getValue()])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['card_no_md5'])
                ->asArray()->all(),
            'card_no_md5');

        $user_ids_loan = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->from(ClientInfoLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.id'])
                ->where(['l.szlm_query_id' => $did])
                ->andWhere(['>=', 'l.created_at', $begin_time])
                ->groupBy(['p.id'])
                ->asArray()->all(Yii::$app->db_loan),
            'id');

        $data_loan = ArrayHelper::getColumn(
            UserCreditReportOcrAad::find()
                ->select(['card_no_md5'])
                ->where(['user_id' => $user_ids_loan])
                ->andWhere(['report_status' => CreditReportStatus::PASS()->getValue()])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['card_no_md5'])
                ->asArray()->all(Yii::$app->db_loan),
            'card_no_md5');

        return count(array_unique(array_merge($data, $data_loan)));
    }

    /**
     * 近60天内该数盟设备ID曾试图关联过的不同手机号数量
     * @return int
     */
    public function checkSMDeviceIDLast60dTryMatchPhoneCnt(){
        $did = $this->data->order->did;
        if(empty($did)){
            $client = ClientInfoLog::find()->where(['user_id' => $this->data->loanPerson->id])
                ->andWhere(['is not', 'szlm_query_id', null])
                ->andWhere(['!=', 'szlm_query_id', ''])
                ->orderBy(['id' => SORT_DESC])->one();
            if(empty($client)){
                return -1;
            }

            $did = $client['szlm_query_id'];
        }

        $begin_time = $this->data->order->order_time - 60 * 86400;
        $data = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->from(ClientInfoLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.id'])
                ->where(['l.szlm_query_id' => $did,
                         'p.source_id' => $this->data->loanPerson->source_id])
                ->andWhere(['>=', 'l.created_at', $begin_time])
                ->groupBy(['p.id'])
                ->asArray()->all(),
            'id');
        return count($data);
    }

    /**
     * 总平台近60天内该数盟设备ID曾试图关联过的不同手机号数量
     * @return int
     */
    public function checkSMDeviceIDLast60dTryMatchPhoneCntTotPlatform(){
        $did = $this->data->order->did;
        if(empty($did)){
            $client = ClientInfoLog::find()->where(['user_id' => $this->data->loanPerson->id])
                ->andWhere(['is not', 'szlm_query_id', null])
                ->andWhere(['!=', 'szlm_query_id', ''])
                ->orderBy(['id' => SORT_DESC])->one();
            if(empty($client)){
                return -1;
            }

            $did = $client['szlm_query_id'];
        }

        $begin_time = $this->data->order->order_time - 60 * 86400;
        $data = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->from(ClientInfoLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.phone'])
                ->where(['l.szlm_query_id' => $did])
                ->andWhere(['>=', 'l.created_at', $begin_time])
                ->groupBy(['p.phone'])
                ->asArray()->all(),
            'phone');

        $data_loan = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->from(ClientInfoLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.phone'])
                ->where(['l.szlm_query_id' => $did])
                ->andWhere(['>=', 'l.created_at', $begin_time])
                ->groupBy(['p.phone'])
                ->asArray()->all(Yii::$app->db_loan),
            'phone');

        return count(array_unique(array_merge($data, $data_loan)));
    }

    /**
     * 总平台近90天内该数盟设备ID曾试图关联过的不同手机号数量
     * @return int
     */
    public function checkSMDeviceIDLast90dTryMatchPhoneCntTotPlatform(){
        $did = $this->data->order->did;
        if(empty($did)){
            $client = ClientInfoLog::find()->where(['user_id' => $this->data->loanPerson->id])
                ->andWhere(['is not', 'szlm_query_id', null])
                ->andWhere(['!=', 'szlm_query_id', ''])
                ->orderBy(['id' => SORT_DESC])->one();
            if(empty($client)){
                return -1;
            }

            $did = $client['szlm_query_id'];
        }

        $begin_time = $this->data->order->order_time - 90 * 86400;
        $data = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->from(ClientInfoLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.phone'])
                ->where(['l.szlm_query_id' => $did])
                ->andWhere(['>=', 'l.created_at', $begin_time])
                ->groupBy(['p.phone'])
                ->asArray()->all(),
            'phone');

        $data_loan = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->from(ClientInfoLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.phone'])
                ->where(['l.szlm_query_id' => $did])
                ->andWhere(['>=', 'l.created_at', $begin_time])
                ->groupBy(['p.phone'])
                ->asArray()->all(Yii::$app->db_loan),
            'phone');

        return count(array_unique(array_merge($data, $data_loan)));
    }

    /**
     * 近60天内该数盟设备ID曾试图关联过的不同Pan卡号数量
     * @return int
     */
    public function checkSMDeviceIDLast60dTryMatchPanCnt(){
        $did = $this->data->order->did;
        if(empty($did)){
            $client = ClientInfoLog::find()->where(['user_id' => $this->data->loanPerson->id])
                ->andWhere(['is not', 'szlm_query_id', null])
                ->andWhere(['!=', 'szlm_query_id', ''])
                ->orderBy(['id' => SORT_DESC])->one();
            if(empty($client)){
                return -1;
            }

            $did = $client['szlm_query_id'];
        }

        $begin_time = $this->data->order->order_time - 60 * 86400;
        $user_ids = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->from(ClientInfoLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.id'])
                ->where(['l.szlm_query_id' => $did,
                         'p.source_id' => $this->data->loanPerson->source_id])
                ->andWhere(['>=', 'l.created_at', $begin_time])
                ->groupBy(['p.id'])
                ->asArray()->all(),
            'id');

        $data = ArrayHelper::getColumn(
            UserPanCheckLog::find()
                ->select(['pan_input'])
                ->where(['user_id' => $user_ids])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['pan_input'])
                ->asArray()->all(),
            'pan_input');
        return count($data);
    }

    /**
     * 近60天内该数盟设备ID曾试图关联过的不同Aadhaar卡号数量
     * @return int
     */
    public function checkSMDeviceIDLast60dTryMatchAadhaarCnt(){
        $did = $this->data->order->did;
        if(empty($did)){
            $client = ClientInfoLog::find()->where(['user_id' => $this->data->loanPerson->id])
                ->andWhere(['is not', 'szlm_query_id', null])
                ->andWhere(['!=', 'szlm_query_id', ''])
                ->orderBy(['id' => SORT_DESC])->one();
            if(empty($client)){
                return -1;
            }

            $did = $client['szlm_query_id'];
        }

        $begin_time = $this->data->order->order_time - 60 * 86400;
        $user_ids = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->from(ClientInfoLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.id'])
                ->where(['l.szlm_query_id' => $did,
                         'p.source_id' => $this->data->loanPerson->source_id])
                ->andWhere(['>=', 'l.created_at', $begin_time])
                ->groupBy(['p.id'])
                ->asArray()->all(),
            'id');

        $data = ArrayHelper::getColumn(
            UserCreditReportOcrAad::find()
                ->select(['card_no_md5'])
                ->where(['user_id' => $user_ids])
                ->andWhere(['report_status' => CreditReportStatus::PASS()->getValue()])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['card_no_md5'])
                ->asArray()->all(),
            'card_no_md5');
        return count($data);
    }

    /**
     * 近90天内该数盟设备ID曾试图关联过的不同手机号数量
     * @return int
     */
    public function checkSMDeviceIDLast90dTryMatchPhoneCnt(){
        $did = $this->data->order->did;
        if(empty($did)){
            $client = ClientInfoLog::find()->where(['user_id' => $this->data->loanPerson->id])
                ->andWhere(['is not', 'szlm_query_id', null])
                ->andWhere(['!=', 'szlm_query_id', ''])
                ->orderBy(['id' => SORT_DESC])->one();
            if(empty($client)){
                return -1;
            }

            $did = $client['szlm_query_id'];
        }

        $begin_time = $this->data->order->order_time - 90 * 86400;
        $data = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->from(ClientInfoLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.id'])
                ->where(['l.szlm_query_id' => $did,
                         'p.source_id' => $this->data->loanPerson->source_id])
                ->andWhere(['>=', 'l.created_at', $begin_time])
                ->groupBy(['p.id'])
                ->asArray()->all(),
            'id');
        return count($data);
    }

    /**
     * 近90天内该数盟设备ID曾试图关联过的不同Pan卡号数量
     * @return int
     */
    public function checkSMDeviceIDLast90dTryMatchPanCnt(){
        $did = $this->data->order->did;
        if(empty($did)){
            $client = ClientInfoLog::find()->where(['user_id' => $this->data->loanPerson->id])
                ->andWhere(['is not', 'szlm_query_id', null])
                ->andWhere(['!=', 'szlm_query_id', ''])
                ->orderBy(['id' => SORT_DESC])->one();
            if(empty($client)){
                return -1;
            }

            $did = $client['szlm_query_id'];
        }

        $begin_time = $this->data->order->order_time - 90 * 86400;
        $user_ids = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->from(ClientInfoLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.id'])
                ->where(['l.szlm_query_id' => $did,
                         'p.source_id' => $this->data->loanPerson->source_id])
                ->andWhere(['>=', 'l.created_at', $begin_time])
                ->groupBy(['p.id'])
                ->asArray()->all(),
            'id');

        $data = ArrayHelper::getColumn(
            UserPanCheckLog::find()
                ->select(['pan_input'])
                ->where(['user_id' => $user_ids])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['pan_input'])
                ->asArray()->all(),
            'pan_input');
        return count($data);
    }

    /**
     * 近90天内该数盟设备ID曾试图关联过的不同Aadhaar卡号数量
     * @return int
     */
    public function checkSMDeviceIDLast90dTryMatchAadhaarCnt(){
        $did = $this->data->order->did;
        if(empty($did)){
            $client = ClientInfoLog::find()->where(['user_id' => $this->data->loanPerson->id])
                ->andWhere(['is not', 'szlm_query_id', null])
                ->andWhere(['!=', 'szlm_query_id', ''])
                ->orderBy(['id' => SORT_DESC])->one();
            if(empty($client)){
                return -1;
            }

            $did = $client['szlm_query_id'];
        }

        $begin_time = $this->data->order->order_time - 90 * 86400;
        $user_ids = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->from(ClientInfoLog::tableName(). ' as l')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=l.user_id')
                ->select(['p.id'])
                ->where(['l.szlm_query_id' => $did,
                         'p.source_id' => $this->data->loanPerson->source_id])
                ->andWhere(['>=', 'l.created_at', $begin_time])
                ->groupBy(['p.id'])
                ->asArray()->all(),
            'id');

        $data = ArrayHelper::getColumn(
            UserCreditReportOcrAad::find()
                ->select(['card_no_md5'])
                ->where(['user_id' => $user_ids])
                ->andWhere(['report_status' => CreditReportStatus::PASS()->getValue()])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['card_no_md5'])
                ->asArray()->all(),
            'card_no_md5');
        return count($data);
    }

    /**
     * 本次Pan绑定的Aadhaar卡号是否与Pan在全平台所有绑定的Aadhaar卡号是否一致
     * @return int
     */
    public function checkArePanBindedAadhaarsAllTheSame(){
        $aadhaar_number = $this->data->loanPerson->aadhaar_md5;

        $data = LoanPerson::find()->select(['aadhaar_md5'])
            ->where(['pan_code' => $this->data->loanPerson->pan_code, 'merchant_id' => $this->data->order->merchant_id])
            ->groupBy(['aadhaar_md5'])
            ->asArray()->all();

        foreach ($data as $v){
            if(empty($v['aadhaar_md5'])){
                continue;
            }

            if($v['aadhaar_md5'] != $aadhaar_number){
                return 0;
            }
        }

        return 1;
    }

    /**
     * 实际还款与应还款天数差的总和的得分
     * @return int
     */
    public function checkOldUserSumDuedayScoreModelV1(){
        $data = UserLoanOrderRepayment::find()
            ->select(['closing_time', 'plan_repayment_time'])
            ->where(['user_id' => $this->data->loanPerson->id, 'status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE])
            ->asArray()
            ->all();

        if(empty($data)){
            return 62;
        }

        $sum = 0;
        foreach ($data as $v){
            $sum += (strtotime(date('Y-m-d', $v['closing_time'])) - strtotime(date('Y-m-d', $v['plan_repayment_time']))) / 86400;
        }

        if($sum < -10){
            return 80;
        }elseif($sum < 1){
            return 89;
        }else{
            return 62;
        }
    }

    /**
     * 月收入的得分
     * @return int
     */
    public function checkOldUserSalaryScoreModelV1(){
        $salary = intval(CommonHelper::CentsToUnit($this->data->userWorkInfo->monthly_salary));

        if($salary < 20000){
            return 80;
        }elseif($salary < 25000){
            return 76;
        }elseif($salary < 45000){
            return 91;
        }else{
            return 104;
        }
    }

    /**
     * 首单放款成功的订单的下单时间距今的时间差的得分
     * @return int
     */
    public function checkOldUserMaxDateOfRepayToTodayScoreModelV1(){
        $data = UserLoanOrder::find()
            ->select(['order_time'])
            ->where(['user_id' => $this->data->loanPerson->id])
            ->andWhere(['>=', 'loan_time', 0])
            ->orderBy(['loan_time' => SORT_ASC])
            ->one();

        if(empty($data)){
            return 97;
        }

        $day = (strtotime('today') - strtotime(date('Y-m-d', $data['order_time']))) / 86400;
        if($day < 1){
            return 97;
        }elseif($day < 7){
            return 90;
        }else{
            return 75;
        }
    }

    /**
     * 未逾期的订单的还款天数的均值的得分
     * @return int
     */
    public function checkOldUserAvgTiQianRepayDayScoreModelV1(){
        $data = UserLoanOrderRepayment::find()
            ->select(['closing_time', 'plan_repayment_time'])
            ->where(['user_id' => $this->data->loanPerson->id,
                     'status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE,
                     'is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_NO])
            ->asArray()
            ->all();

        if(empty($data)){
            return 80;
        }

        $sum = 0;
        $count = 0;
        foreach ($data as $v){
            $count++;
            $sum += (strtotime(date('Y-m-d', $v['closing_time'])) - strtotime(date('Y-m-d', $v['plan_repayment_time']))) / 86400;
        }

        $avg = round($sum / $count);

        if($avg < -4){
            return 87;
        }elseif($avg < -3){
            return 85;
        }elseif($avg < -1){
            return 86;
        }else{
            return 84;
        }
    }

    /**
     * 最近一笔放款成功的订单的下单时间距今的时间差的得分
     * @return int
     */
    public function checkOldUserMinDateOfOrderToTodayScoreModelV1(){
        $data = UserLoanOrder::find()
            ->select(['order_time'])
            ->where(['user_id' => $this->data->loanPerson->id])
            ->andWhere(['>=', 'loan_time', 0])
            ->orderBy(['loan_time' => SORT_DESC])
            ->one();

        if(empty($data)){
            return 86;
        }

        $day = (strtotime('today') - strtotime(date('Y-m-d', $data['order_time']))) / 86400;
        if($day < 4){
            return 86;
        }elseif($day < 7){
            return 91;
        }elseif($day < 10){
            return 81;
        }else{
            return 82;
        }
    }

    /**
     * 历史提前3天还款的金额的均值占历史还款金额均值的比例的得分
     * @return int
     */
    public function checkOldUserHisTiQian3RepayamtAvgHisAvgRateScoreModelV1(){
        $data = UserLoanOrderRepayment::find()
            ->select(['closing_time', 'plan_repayment_time', 'principal', 'cost_fee'])
            ->where(['user_id' => $this->data->loanPerson->id,
                     'status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE,
                     'is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_NO])
            ->asArray()
            ->all();

        if(empty($data)){
            return 84;
        }

        $money = 0;
        $count = 0;
        foreach ($data as $v){
            $day = (strtotime(date('Y-m-d', $v['plan_repayment_time'])) - strtotime(date('Y-m-d', $v['closing_time']))) / 86400;
            if($day >= 3){
                $money += $v['principal'] - $v['cost_fee'];
                $count++;
            }
        }

        if($count == 0){
            return 84;
        }
        $avg = round($money / $count);

        $data1 = UserLoanOrderRepayment::find()
            ->select(['principal', 'cost_fee'])
            ->where(['user_id' => $this->data->loanPerson->id,
                     'status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE])
            ->asArray()
            ->all();

        $money = 0;
        $count = 0;
        foreach ($data1 as $val){
            $money += $val['principal'] - $val['cost_fee'];
            $count++;
        }

        $avg1 = round($money / $count);

        $rate = round($avg / $avg1 * 100);

        if($rate < 86){
            return 77;
        }elseif($rate < 102){
            return 98;
        }else{
            return 81;
        }
    }

    /**
     * 近30天放款金额的最小值的得分
     * @return int
     */
    public function checkOldUserLast30dWithdrawAmtMinScoreModelV1(){
        $begin_time = strtotime('today') - 30 * 86400;
        $data = UserLoanOrder::find()
            ->select(['amount', 'cost_fee'])
            ->where(['user_id' => $this->data->loanPerson->id])
            ->andWhere(['>=', 'loan_time', $begin_time])
            ->all();

        if(empty($data)){
            return 61;
        }

        $arr = [];
        foreach ($data as $v){
            $arr[] = $v['amount'] - $v['cost_fee'];
        }

        $min = min($arr);

        if($min < 160000){
            return 78;
        }else{
            return 87;
        }
    }

    /**
     * 老用户最近一笔的到账金额
     * @return int
     */
    public function checkOldUserLastLoanOrderAmount(){
        $data = UserLoanOrderRepayment::find()
            ->select(['principal', 'cost_fee'])
            ->where(['user_id' => $this->data->loanPerson->id])
            ->orderBy(['id' => SORT_DESC])
            ->one();
        if(empty($data)){
            return 1700;
        }

        return intval(CommonHelper::CentsToUnit($data['principal'] - $data['cost_fee']));
    }


    /**
     * Aadhaar手填地址的城市是否命中准入地区白名单
     * @return int
     */
    public function checkWrittenAadhaarAddressHitWhiteList(){
        if(empty($this->data->userBasicInfo->aadhaar_address2)){
            return -1;
        }

        foreach ($this->addressWhiteList as $value){
            if (strtolower($this->data->userBasicInfo->aadhaar_address2) == strtolower($value)) {
                return 1;
            }
        }

        foreach ($this->addressWhiteList2 as $value){
            if (strtolower($this->data->userBasicInfo->aadhaar_address2) == strtolower($value)) {
                return 2;
            }
        }

        return 0;
    }

    /**
     * Aadhaar手填地址的邮编是否命中准入地区白名单
     * @return int
     */
    public function checkWrittenAadhaarAddressPincodeHitWhiteList(){
        if(empty($this->data->userBasicInfo->aadhaar_pin_code)){
            return -1;
        }

        if(in_array($this->data->userBasicInfo->aadhaar_pin_code, Pin::$whiteList)){
            return 1;
        }

        if(in_array($this->data->userBasicInfo->aadhaar_pin_code, Pin::$whiteList2)){
            return 2;
        }

        return 0;
    }

    /**
     * 获取用户相册信息
     * @param $phone
     * @return array|mixed
     */
    protected function getMobilePhoto($order_uuid)
    {
        $key = "{$order_uuid}";
        if (isset($this->mobilePhoto[$key])) {
            return $this->mobilePhoto[$key];
        } else {
            $data = UserPictureMetadataLog::find()
                ->where(['order_uuid' => $order_uuid])
                ->orderBy(['id' => SORT_DESC])->one(Yii::$app->db_loan);

            return $this->mobilePhoto[$key] = $data;
        }
    }

    /**
     * 历史手机照片的数量
     * @return int
     * @throws \yii\mongodb\Exception
     */
    public function checkHisMobilePhotoAmount(){
        $date = date('Y-m-d', $this->data->order->order_time);
        if($this->data->order->is_export == UserLoanOrder::IS_EXPORT_YES){
            $data = $this->getMobilePhoto($this->data->order->order_uuid);
            if(!empty($data)){
                return $data['number_all'];
            }

            $userId = $this->getUserOtherId();
            return MgUserMobilePhotos::find()->where(['user_id' => $userId, 'date' => $date])->count('*', Yii::$app->mongodb_loan);
        }else{
            return MgUserMobilePhotos::find()->where(['user_id' => $this->data->loanPerson->id, 'date' => $date])->count();
        }
    }

    /**
     * 最近30天手机照片的数量
     * @return int
     * @throws \yii\mongodb\Exception
     */
    public function checkLast30MobilePhotoAmount(){
        $date = date('Y-m-d', $this->data->order->order_time);
        $begin_time = $this->data->order->order_time - 30 * 86400;
        if($this->data->order->is_export == UserLoanOrder::IS_EXPORT_YES){
            $data = $this->getMobilePhoto($this->data->order->order_uuid);
            if(!empty($data)){
                return $data['number30'];
            }

            $userId = $this->getUserOtherId();
            return MgUserMobilePhotos::find()->where(['user_id' => $userId, 'date' => $date])
                ->andWhere(['>=', 'AlbumFileLastModifiedTime', $begin_time])
                ->count('*', Yii::$app->mongodb_loan);
        }else{
            return MgUserMobilePhotos::find()->where(['user_id' => $this->data->loanPerson->id, 'date' => $date])
                ->andWhere(['>=', 'AlbumFileLastModifiedTime', $begin_time])
                ->count();
        }
    }

    /**
     * 最近90天手机照片的数量
     * @return int
     * @throws \yii\mongodb\Exception
     */
    public function checkLast90MobilePhotoAmount(){
        $date = date('Y-m-d', $this->data->order->order_time);
        $begin_time = $this->data->order->order_time - 90 * 86400;
        if($this->data->order->is_export == UserLoanOrder::IS_EXPORT_YES){
            $data = $this->getMobilePhoto($this->data->order->order_uuid);
            if(!empty($data)){
                return $data['number90'];
            }

            $userId = $this->getUserOtherId();
            return MgUserMobilePhotos::find()->where(['user_id' => $userId, 'date' => $date])
                ->andWhere(['>=', 'AlbumFileLastModifiedTime', $begin_time])
                ->count('*', Yii::$app->mongodb_loan);
        }else{
            return MgUserMobilePhotos::find()->where(['user_id' => $this->data->loanPerson->id, 'date' => $date])
                ->andWhere(['>=', 'AlbumFileLastModifiedTime', $begin_time])
                ->count();
        }
    }

    /**
     * 手机最早的照片时间距今的时间
     * @return int
     */
    public function checkFirstPhotoTimeToNow(){
        $date = date('Y-m-d', $this->data->order->order_time);
        if($this->data->order->is_export == UserLoanOrder::IS_EXPORT_YES){
            $data = $this->getMobilePhoto($this->data->order->order_uuid);
            if(!empty($data)){
                $info = json_decode($data['metadata_earliest'], true);
                if(!empty($info['AlbumFileLastModifiedTime'])){
                    return intval((strtotime("today") - strtotime(date('Y-m-d', intval($info['AlbumFileLastModifiedTime']/1000)))) / 86400);
                }

                return -1;
            }

            $userId = $this->getUserOtherId();
            $data = MgUserMobilePhotos::find()->where(['user_id' => $userId, 'date' => $date])
                ->orderBy(['AlbumFileLastModifiedTime' => SORT_ASC])
                ->one(Yii::$app->mongodb_loan);
        }else{
            $data = MgUserMobilePhotos::find()->where(['user_id' => $this->data->loanPerson->id, 'date' => $date])
                ->orderBy(['AlbumFileLastModifiedTime' => SORT_ASC])
                ->one();
        }

        if(empty($data)){
            return -1;
        }

        return intval((strtotime("today") - strtotime(date('Y-m-d', $data['AlbumFileLastModifiedTime']))) / 86400);
    }

    /**
     * 手机最晚的照片时间距今的时间
     * @return int
     */
    public function checkLastPhotoTimeToNow(){
        $date = date('Y-m-d', $this->data->order->order_time);
        if($this->data->order->is_export == UserLoanOrder::IS_EXPORT_YES){
            $data = $this->getMobilePhoto($this->data->order->order_uuid);
            if(!empty($data)){
                $info = json_decode($data['metadata_latest'], true);
                if(!empty($info['AlbumFileLastModifiedTime'])){
                    return intval((strtotime("today") - strtotime(date('Y-m-d', intval($info['AlbumFileLastModifiedTime']/1000)))) / 86400);
                }

                return -1;
            }

            $userId = $this->getUserOtherId();
            $data = MgUserMobilePhotos::find()->where(['user_id' => $userId, 'date' => $date])
                ->orderBy(['AlbumFileLastModifiedTime' => SORT_DESC])
                ->one(Yii::$app->mongodb_loan);
        }else{
            $data = MgUserMobilePhotos::find()->where(['user_id' => $this->data->loanPerson->id, 'date' => $date])
                ->orderBy(['AlbumFileLastModifiedTime' => SORT_DESC])
                ->one();
        }

        if(empty($data)){
            return -1;
        }

        return intval((strtotime("today") - strtotime(date('Y-m-d', $data['AlbumFileLastModifiedTime']))) / 86400);
    }

    /**
     * 最早一张手机照片定位地址与手机当前定位地址的距离
     * @return int
     */
    public function checkDistanceOfFirstPhotoAndMobileGPS(){
        $date = date('Y-m-d', $this->data->order->order_time);
        $clientInfo = json_decode($this->data->order->client_info,true);
        if(empty($clientInfo['longitude']) || empty($clientInfo['latitude'])) {
            return -1;
        }

        if($this->data->order->is_export == UserLoanOrder::IS_EXPORT_YES){
            $data = $this->getMobilePhoto($this->data->order->order_uuid);
            if(!empty($data)){
                $info = json_decode($data['metadata_earliest_positioned'], true);
                if(!empty($info)){
                    $long = CommonHelper::GetDecimalFromDms($info['GPSLongitude'], $info['GPSLongitudeRef']);
                    $lat = CommonHelper::GetDecimalFromDms($info['GPSLatitude'], $info['GPSLatitudeRef']);

                    if(empty($lat) || empty($long)){
                        return -2;
                    }

                    return intval(CommonHelper::GetDistance($clientInfo['longitude'], $clientInfo['latitude'], $long, $lat));
                }

                return -2;
            }

            $userId = $this->getUserOtherId();
            $data = MgUserMobilePhotos::find()->where([
                'user_id' => $userId,
                'date' => $date,
                'GPSLongitude' => ['$exists'=>1, '$ne'=> ''],
                'GPSLongitudeRef' => ['W', 'E'],
                'GPSLatitude' => ['$exists'=>1, '$ne'=> ''],
                'GPSLatitudeRef' => ['S', 'N']])
                ->orderBy(['AlbumFileLastModifiedTime' => SORT_ASC])
                ->one(Yii::$app->mongodb_loan);
        }else{
            $data = MgUserMobilePhotos::find()->where([
                'user_id' => $this->data->loanPerson->id,
                'date' => $date,
                'GPSLongitude' => ['$exists'=>1, '$ne'=> ''],
                'GPSLongitudeRef' => ['W', 'E'],
                'GPSLatitude' => ['$exists'=>1, '$ne'=> ''],
                'GPSLatitudeRef' => ['S', 'N']])
                ->orderBy(['AlbumFileLastModifiedTime' => SORT_ASC])
                ->one();
        }

        if(empty($data)){
            return -2;
        }

        $long = CommonHelper::GetDecimalFromDms($data['GPSLongitude'], $data['GPSLongitudeRef']);
        $lat = CommonHelper::GetDecimalFromDms($data['GPSLatitude'], $data['GPSLatitudeRef']);

        return intval(CommonHelper::GetDistance($clientInfo['longitude'], $clientInfo['latitude'], $long, $lat));
    }

    /**
     * 最近一张手机照片定位地址与手机当前定位地址的距离
     * @return int
     */
    public function checkDistanceOfLastPhotoAndMobileGPS(){
        $date = date('Y-m-d', $this->data->order->order_time);
        $clientInfo = json_decode($this->data->order->client_info,true);
        if(empty($clientInfo['longitude']) || empty($clientInfo['latitude'])) {
            return -1;
        }

        if($this->data->order->is_export == UserLoanOrder::IS_EXPORT_YES){
            $data = $this->getMobilePhoto($this->data->order->order_uuid);
            if(!empty($data)){
                $info = json_decode($data['metadata_latest_positioned'], true);
                if(!empty($info)){
                    $long = CommonHelper::GetDecimalFromDms($info['GPSLongitude'], $info['GPSLongitudeRef']);
                    $lat = CommonHelper::GetDecimalFromDms($info['GPSLatitude'], $info['GPSLatitudeRef']);

                    if(empty($lat) || empty($long)){
                        return -2;
                    }

                    return intval(CommonHelper::GetDistance($clientInfo['longitude'], $clientInfo['latitude'], $long, $lat));
                }

                return -2;
            }

            $userId = $this->getUserOtherId();
            $data = MgUserMobilePhotos::find()->where([
                'user_id' => $userId,
                'date' => $date,
                'GPSLongitude' => ['$exists'=>1, '$ne'=> ''],
                'GPSLongitudeRef' => ['W', 'E'],
                'GPSLatitude' => ['$exists'=>1, '$ne'=> ''],
                'GPSLatitudeRef' => ['S', 'N']])
                ->orderBy(['AlbumFileLastModifiedTime' => SORT_DESC])
                ->one(Yii::$app->mongodb_loan);
        }else{
            $data = MgUserMobilePhotos::find()->where([
                'user_id' => $this->data->loanPerson->id,
                'date' => $date,
                'GPSLongitude' => ['$exists'=>1, '$ne'=> ''],
                'GPSLongitudeRef' => ['W', 'E'],
                'GPSLatitude' => ['$exists'=>1, '$ne'=> ''],
                'GPSLatitudeRef' => ['S', 'N']])
                ->orderBy(['AlbumFileLastModifiedTime' => SORT_DESC])
                ->one();
        }


        if(empty($data)){
            return -2;
        }

        $long = CommonHelper::GetDecimalFromDms($data['GPSLongitude'], $data['GPSLongitudeRef']);
        $lat = CommonHelper::GetDecimalFromDms($data['GPSLatitude'], $data['GPSLatitudeRef']);

        return intval(CommonHelper::GetDistance($clientInfo['longitude'], $clientInfo['latitude'], $long, $lat));
    }

    /**
     * 近1个月内该手机号在本平台申请被拒次数
     * @return int|string
     */
    public function checkRejectCntLast1MonthByMobileInThisPlat()
    {
        $lastTime = strtotime('last month');
        $count = UserLoanOrder::find()
            ->where(['status' => UserLoanOrder::STATUS_CHECK_REJECT, 'user_id' => $this->data->loanPerson->id])
            ->andWhere(['>=', 'order_time', $lastTime])->count();
        return $count;
    }

    /**
     * 近1个月内手机号在本平台的申请次数
     * @return int|string
     */
    public function checkApplyCntLast1MonthByMobileInThisPlat()
    {
        $lastTime = strtotime('last month');
        $count = UserLoanOrder::find()
            ->where(['user_id' => $this->data->loanPerson->id])
            ->andWhere(['>=', 'order_time', $lastTime])->count();
        return $count;
    }

    /**
     * 数盟设备ID在本平台的历史申请次数
     * @return int
     */
    public function checkHisSMDeviceIDApplyCntInThisPlat(){
        if(empty($this->data->order->did)){
            return -1;
        }

        $count = UserLoanOrder::find()
            ->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id=o.user_id')
            ->where(['o.did' => $this->data->order->did, 'p.source_id' => $this->data->loanPerson->source_id])
            ->count();
        return $count;
    }

    /**
     * 近90天数盟设备ID在本平台的申请次数
     * @return int
     */
    public function checkLast90ApplyCntBySMDeviceIDInThisPlat(){
        if(empty($this->data->order->did)){
            return -1;
        }
        $begin_time = $this->data->order->order_time - 90 * 86400;

        $count = UserLoanOrder::find()
            ->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id=o.user_id')
            ->where(['o.did' => $this->data->order->did, 'p.source_id' => $this->data->loanPerson->source_id])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->count();
        return $count;
    }

    /**
     * 近30天数盟设备ID在本平台的申请次数
     * @return int
     */
    public function checkLast30ApplyCntBySMDeviceIDInThisPlat(){
        if(empty($this->data->order->did)){
            return -1;
        }
        $begin_time = $this->data->order->order_time - 30 * 86400;

        $count = UserLoanOrder::find()
            ->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id=o.user_id')
            ->where(['o.did' => $this->data->order->did, 'p.source_id' => $this->data->loanPerson->source_id])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->count();
        return $count;
    }

    /**
     * 数盟设备ID历史在本平台的申请被拒次数
     * @return int
     */
    public function checkHisSMDeviceIDApplyRejectCntInThisPlat(){
        if(empty($this->data->order->did)){
            return -1;
        }

        $count = UserLoanOrder::find()
            ->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id=o.user_id')
            ->where(['o.did' => $this->data->order->did,
                     'p.source_id' => $this->data->loanPerson->source_id,
                     'o.status' => UserLoanOrder::STATUS_CHECK_REJECT])
            ->count();
        return $count;
    }

    /**
     * 近90天数盟设备ID在本平台的申请被拒次数
     * @return int
     */
    public function checkLast90RejectCntBySMDeviceIDInThisPlat(){
        if(empty($this->data->order->did)){
            return -1;
        }
        $begin_time = $this->data->order->order_time - 90 * 86400;

        $count = UserLoanOrder::find()
            ->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id=o.user_id')
            ->where(['o.did' => $this->data->order->did,
                     'p.source_id' => $this->data->loanPerson->source_id,
                     'o.status' => UserLoanOrder::STATUS_CHECK_REJECT])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->count();
        return $count;
    }

    /**
     * 近30天数盟设备ID在本平台的申请被拒次数
     * @return int
     */
    public function checkLast30RejectCntBySMDeviceIDInThisPlat(){
        if(empty($this->data->order->did)){
            return -1;
        }
        $begin_time = $this->data->order->order_time - 30 * 86400;

        $count = UserLoanOrder::find()
            ->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id=o.user_id')
            ->where(['o.did' => $this->data->order->did,
                     'p.source_id' => $this->data->loanPerson->source_id,
                     'o.status' => UserLoanOrder::STATUS_CHECK_REJECT])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->count();
        return $count;
    }

    /**
     * 老用户复杂规则V1-历史提前还款订单数量
     * @return int
     */
    public function checkOldUserComplexRuleV1HisTiqianOrderCnt(){
        $count = UserLoanOrderRepayment::find()
            ->where(['user_id' => $this->data->loanPerson->id,
                     'status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE,
                     'is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_NO])
            ->count();
        return $count;
    }

    /**
     * 老用户复杂规则V1-总平台历史提前还款订单数量
     * @return int
     */
    public function checkOldUserComplexRuleV1HisTiqianOrderCntTotPlatform(){
        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $count = UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds,
                     'status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE,
                     'is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_NO])
            ->count();

        $userIds_loan = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);
        $count_loan = UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds_loan,
                     'status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE,
                     'is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_NO])
            ->count('*', Yii::$app->db_loan);
        return $count + $count_loan;
    }

    /**
     * 老用户复杂规则V1-历史逾期订单数量
     * @return int
     */
    public function checkOldUserComplexRuleV1HisDueOrderCnt(){
        $count = UserLoanOrderRepayment::find()
            ->where(['user_id' => $this->data->loanPerson->id,
                     'is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_YES])
            ->count();
        return $count;
    }

    /**
     * 老用户复杂规则V1-总平台历史逾期订单数量
     * @return int
     */
    public function checkOldUserComplexRuleV1HisDueOrderCntTotPlatform(){
        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $count = UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds,
                     'is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_YES])
            ->count();

        $userIds_loan = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);
        $count_loan = UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds_loan,
                     'is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_YES])
            ->count('*', Yii::$app->db_loan);
        return $count + $count_loan;
    }

    /**
     * 老用户复杂规则V1-历史还款日期与应还款日期之差的和
     * @return int
     */
    public function checkOldUserComplexRuleV1HisCpDaySum(){
        $data = UserLoanOrderRepayment::find()
            ->where(['user_id' => $this->data->loanPerson->id,
                     'status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE])
            ->all();
        $count = 0;
        foreach ($data as $v){
            $count += (strtotime(date('Y-m-d', $v['closing_time'])) - strtotime(date('Y-m-d', $v['plan_repayment_time'])))/86400;
        }
        return $count;
    }

    /**
     * 老用户复杂规则V1-总平台历史还款日期与应还款日期之差的和
     * @return int
     */
    public function checkOldUserComplexRuleV1HisCpDaySumTotPlatform(){
        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $data = UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds,
                     'status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE])
            ->all();
        $count = 0;
        foreach ($data as $v){
            $count += (strtotime(date('Y-m-d', $v['closing_time'])) - strtotime(date('Y-m-d', $v['plan_repayment_time'])))/86400;
        }

        $userIds_loan = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);
        $data_loan = UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds_loan,
                     'status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE])
            ->all(Yii::$app->db_loan);
        foreach ($data_loan as $v){
            $count += (strtotime(date('Y-m-d', $v['closing_time'])) - strtotime(date('Y-m-d', $v['plan_repayment_time'])))/86400;
        }
        return $count;
    }

    /**
     * 老用户复杂规则V1-历史逾期订单的还款日期与应还款日期之差的和
     * @return int
     */
    public function checkOldUserComplexRuleV1HisDueCpDaySum(){
        $data = UserLoanOrderRepayment::find()
            ->where(['user_id' => $this->data->loanPerson->id,
                     'status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE,
                     'is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_YES])
            ->all();
        $count = 0;
        foreach ($data as $v){
            $count += (strtotime(date('Y-m-d', $v['closing_time'])) - strtotime(date('Y-m-d', $v['plan_repayment_time'])))/86400;
        }
        return $count;
    }

    /**
     * 老用户复杂规则V1-总平台历史逾期订单的还款日期与应还款日期之差的和
     * @return int
     */
    public function checkOldUserComplexRuleV1HisDueCpDaySumTotPlatform(){
        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $data = UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds,
                     'status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE,
                     'is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_YES])
            ->all();
        $count = 0;
        foreach ($data as $v){
            $count += (strtotime(date('Y-m-d', $v['closing_time'])) - strtotime(date('Y-m-d', $v['plan_repayment_time'])))/86400;
        }

        $userIds_loan = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);
        $data_loan = UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds_loan,
                     'status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE,
                     'is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_YES])
            ->all(Yii::$app->db_loan);
        foreach ($data_loan as $v){
            $count += (strtotime(date('Y-m-d', $v['closing_time'])) - strtotime(date('Y-m-d', $v['plan_repayment_time'])))/86400;
        }
        return $count;
    }

    /**
     * 老用户复杂规则V1-历史逾期订单的数量占历史总放款订单数量的比例
     * @return int
     */
    public function checkOldUserComplexRuleV1HisDueOrderCntHisOrderCntRate(){
        $count = UserLoanOrderRepayment::find()
            ->where(['user_id' => $this->data->loanPerson->id,
                     'status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE,
                     'is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_YES])
            ->count();

        if(empty($count)){
            return 0;
        }

        $data = UserLoanOrderRepayment::find()
            ->where(['user_id' => $this->data->loanPerson->id])
            ->count();

        return round($count / $data * 100 ,2);
    }

    /**
     * 老用户复杂规则V1-总平台历史逾期订单的数量占历史总放款订单数量的比例
     * @return int
     */
    public function checkOldUserComplexRuleV1HisDueOrderCntHisOrderCntRateTotPlatform(){
        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $count = UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds,
                     'is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_YES])
            ->count();

        $userIds_loan = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);
        $count += UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds_loan,
                     'is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_YES])
            ->count('*', Yii::$app->db_loan);

        if(empty($count)){
            return 0;
        }

        $data = UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds])
            ->count();

        $data += UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds_loan])
            ->count('*', Yii::$app->db_loan);

        return round($count / $data * 100 ,2);
    }

    /**
     * 老用户复杂规则V1-历史逾期订单的还款日期与应还款日期之差的最大值
     * @return int
     */
    public function checkOldUserComplexRuleV1HisDueCpDayMax(){
        $data = UserLoanOrderRepayment::find()
            ->where(['user_id' => $this->data->loanPerson->id,
                     'status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE,
                     'is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_YES])
            ->all();
        $arr = [0];
        foreach ($data as $v){
            $arr[] = (strtotime(date('Y-m-d', $v['closing_time'])) - strtotime(date('Y-m-d', $v['plan_repayment_time'])))/86400;
        }
        return max($arr);
    }

    /**
     * 老用户复杂规则V1-总平台历史逾期订单的还款日期与应还款日期之差的最大值
     * @return int
     */
    public function checkOldUserComplexRuleV1HisDueCpDayMaxTotPlatform(){
        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $data = UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds,
                     'status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE,
                     'is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_YES])
            ->asArray()
            ->all();

        $userIds_loan = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);
        $data_loan = UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds_loan,
                     'status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE,
                     'is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_YES])
            ->asArray()
            ->all(Yii::$app->db_loan);
        $arr = [0];
        $data = array_merge($data, $data_loan);
        foreach ($data as $v){
            $arr[] = (strtotime(date('Y-m-d', $v['closing_time'])) - strtotime(date('Y-m-d', $v['plan_repayment_time'])))/86400;
        }
        return max($arr);
    }

    /**
     * 用户上一笔订单贷款的实际还款日期与应还款日期的天数差
     * @return int
     */
    public function checkLastLoanOrderCpDay(){
        $data = UserLoanOrderRepayment::find()
            ->where(['user_id' => $this->data->loanPerson->id,
                     'status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE])
            ->orderBy(['order_id' => SORT_DESC])
            ->one();
        if(empty($data)){
            return 0;
        }

        $count = (strtotime(date('Y-m-d', $data['closing_time'])) - strtotime(date('Y-m-d', $data['plan_repayment_time'])))/86400;
        return $count;
    }

    /**
     * 该用户在总平台上一笔订单贷款的实际还款日期与应还款日期的天数差
     * @return int
     */
    public function checkLastLoanOrderCpDayTotPlatform(){
        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $data = UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds,
                     'status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE])
            ->orderBy(['loan_time' => SORT_DESC])
            ->one();

        $userIds_loan = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);
        $data_loan = UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds_loan,
                     'status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE])
            ->orderBy(['loan_time' => SORT_DESC])
            ->one(Yii::$app->db_loan);
        if(empty($data) && empty($data_loan)){
            return 0;
        }

        $time = $data['loan_time'] ?? 0;
        $time_loan = $data_loan['loan_time'] ?? 0;
        if($time > $time_loan){
            $closing_time = $data['closing_time'];
            $plan_repayment_time = $data['plan_repayment_time'];
        }else{
            $closing_time = $data_loan['closing_time'];
            $plan_repayment_time = $data_loan['plan_repayment_time'];
        }

        $count = (strtotime(date('Y-m-d', $closing_time)) - strtotime(date('Y-m-d', $plan_repayment_time)))/86400;
        return $count;
    }

    /**
     * 近30天内贷款的实际还款日期与应还款日期的天数差的最大值
     * @return int
     */
    public function checkLast30dCpDayMax(){
        $begin_time = strtotime(date('Y-m-d', $this->data->order->order_time)) - 30 * 86400;
        $data = UserLoanOrderRepayment::find()
            ->where(['user_id' => $this->data->loanPerson->id,
                     'status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE])
            ->andWhere(['>=', 'closing_time', $begin_time])
            ->all();
        if(empty($data)){
            return -9999;
        }

        $arr = [];
        foreach ($data as $v){
            $arr[] = (strtotime(date('Y-m-d', $v['closing_time'])) - strtotime(date('Y-m-d', $v['plan_repayment_time'])))/86400;
        }

        return max($arr);
    }

    /**
     * 该用户在近30天内在总平台内贷款的实际还款日期与应还款日期的天数差的最大值
     * @return int
     */
    public function checkLast30dCpDayMaxTotPlatform(){
        $begin_time = strtotime(date('Y-m-d', $this->data->order->order_time)) - 30 * 86400;
        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $data = UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds,
                     'status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE])
            ->andWhere(['>=', 'closing_time', $begin_time])
            ->asArray()
            ->all();

        $userIds_loan = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);
        $data_loan = UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds_loan,
                     'status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE])
            ->andWhere(['>=', 'closing_time', $begin_time])
            ->asArray()
            ->all(Yii::$app->db_loan);
        $data = array_merge($data, $data_loan);
        if(empty($data)){
            return -9999;
        }

        $arr = [];
        foreach ($data as $v){
            $arr[] = (strtotime(date('Y-m-d', $v['closing_time'])) - strtotime(date('Y-m-d', $v['plan_repayment_time'])))/86400;
        }

        return max($arr);
    }

    /**
     * 跑此笔订单风控时用户曾成功还款的订单数加1(用来代表此笔订单是第几笔订单)
     * @return int
     */
    public function checkHisSuccessClosingOrderCntPlusOne(){
        $count = UserLoanOrderRepayment::find()
            ->where(['user_id' => $this->data->loanPerson->id,
                     'status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE])
            ->count();

        return $count + 1;
    }

    /**
     * 该用户跑此笔订单风控时用户曾成功在总平台还款的订单数加1(用来代表此笔订单是第几笔订单)
     * @return int
     */
    public function checkHisSuccessClosingOrderCntPlusOneTotPlatform(){
        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $count = UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds,
                     'status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE])
            ->count();

        $userIds_loan = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);
        $count += UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds_loan,
                     'status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE])
            ->count('*', Yii::$app->db_loan);

        return $count + 1;
    }

    /**
     * Cibil首次授信距今的时间
     * @return int
     * @throws \Exception
     */
    public function checkCibilTimeOfFirstCreditTimeToNow(){
        $report = $this->getCibilReport();
        $first_time = time();
        $flag = true;
        if(empty($report['CreditReport']['Account'])){
            return -9999;
        }
        foreach ($report['CreditReport']['Account'] as $v){
            if(!empty($v['Account_NonSummary_Segment_Fields']['DateOpenedOrDisbursed'])){
                $time = strtotime(Carbon::rawCreateFromFormat('dmY', $v['Account_NonSummary_Segment_Fields']['DateOpenedOrDisbursed'])->toDateString());
                if($time < $first_time){
                    $flag = false;
                    $first_time = $time;
                }
            }
        }

        if($flag){
            return -9999;
        }

        $diff = intval((strtotime(date('Y-m-d', $report['updated_at'])) - $first_time)/86400);
        return $diff;
    }

    /**
     * 新用户Cibil模型分V3
     * @return int
     * @throws \Exception
     */
    public function checkNewUserCibilModelScoreV3(){
        $this->isGetData = false;
        $v262 = $this->checkIsCibilNormal();
        if($v262 != 1){
            return $v262;
        }

        $score = 0;
        $v244 = $this->checkCibilTimeOfFirstCreditTimeToNow();
        switch (true){
            case $v244 < 0:
                $score += 62;
                break;
            case $v244 < 1100:
                $score += 67;
                break;
            case $v244 >= 1100:
                $score += 68;
                break;
        }

        $v252 = $this->checkCibilTimeOfLastPayMent();
        switch (true){
            case $v252 < 5:
                $score += 32;
                break;
            case $v252 < 45:
                $score += 99;
                break;
            case $v252 < 55:
                $score += 86;
                break;
            case $v252 < 85:
                $score += 65;
                break;
            case $v252 >= 85:
                $score += 37;
                break;
        }

        $v236 = $this->checkCibilLast1mEnquiryCnt();
        switch (true){
            case $v236 < 2:
                $score += 53;
                break;
            case $v236 < 5:
                $score += 68;
                break;
            case $v236 < 7:
                $score += 76;
                break;
            case $v236 >= 7:
                $score += 88;
                break;
        }

        $v248 = $this->checkCibilHisDueTotCnt();
        switch (true){
            case $v248 < 1:
                $score += 78;
                break;
            case $v248 >= 1:
                $score += 31;
                break;
        }

        $v206 = $this->checkAppFormSalaryBeforeTax();
        switch (true){
            case $v206 < 21000:
                $score += 65;
                break;
            case $v206 < 30000:
                $score += 66;
                break;
            case $v206 < 36000:
                $score += 69;
                break;
            case $v206 >= 36000:
                $score += 72;
                break;
        }

        $v256 = $this->checkCibilLast6mSumEMI();
        switch (true){
            case $v256 < 2000:
                $score += 65;
                break;
            case $v256 < 8000:
                $score += 72;
                break;
            case $v256 >= 8000:
                $score += 93;
                break;
        }

        $v253 = $this->checkCibilCreditScore();
        switch (true){
            case $v253 < 630:
                $score += 47;
                break;
            case $v253 < 680:
                $score += 64;
                break;
            case $v253 >= 680:
                $score += 82;
                break;
        }

        $v202 = $this->checkLoanAppCnt();
        switch (true){
            case $v202 < 1:
                $score += 45;
                break;
            case $v202 < 2:
                $score += 65;
                break;
            case $v202 < 5:
                $score += 84;
                break;
            case $v202 >= 5:
                $score += 91;
                break;
        }

        $v234 = $this->checkCibilHisEnquiryCnt();
        switch (true){
            case $v234 < 8:
                $score += 36;
                break;
            case $v234 < 18:
                $score += 58;
                break;
            case $v234 < 22:
                $score += 67;
                break;
            case $v234 < 76:
                $score += 82;
                break;
            case $v234 >= 76:
                $score += 122;
                break;
        }

        return $score;
    }

    /**
     * 新用户Cibil模型分V4
     * @return int
     * @throws \Exception
     */
    public function checkNewUserCibilModelScoreV4(){
        $this->isGetData = false;
        $v262 = $this->checkIsCibilNormal();
        if($v262 != 1){
            return $v262;
        }

        $score = 0;
        $v101 = $this->checkUserAge();
        switch (true){
            case $v101 < 29:
                $score += 38;
                break;
            case $v101 < 34:
                $score += 41;
                break;
            case $v101 < 41:
                $score += 45;
                break;
            case $v101 >= 41:
                $score += 38;
                break;
        }

        $v593 = $this->checkSMSCntOfLoanApplicationTrialLast60Days();
        switch (true){
            case $v593 < 30:
                $score += 39;
                break;
            case $v593 < 40:
                $score += 47;
                break;
            case $v593 < 56:
                $score += 42;
                break;
            case $v593 >= 56:
                $score += 39;
                break;
        }

        $v244 = $this->checkCibilTimeOfFirstCreditTimeToNow();
        switch (true){
            case $v244 < 100:
                $score += 31;
                break;
            case $v244 < 700:
                $score += 40;
                break;
            case $v244 < 2300:
                $score += 42;
                break;
            case $v244 >= 1100:
                $score += 46;
                break;
        }

        $v611 = $this->checkSMSCntOfLoanDisbursalLast7Days();
        switch (true){
            case $v611 < 1:
                $score += 35;
                break;
            case $v611 < 2:
                $score += 54;
                break;
            case $v611 >= 2:
                $score += 62;
                break;
        }

        $v182 = $this->checkApplyTimeHour();
        switch (true){
            case $v182 < 15:
                $score += 39;
                break;
            case $v182 < 17:
                $score += 41;
                break;
            case $v182 < 19:
                $score += 44;
                break;
            case $v182 >= 19:
                $score += 39;
                break;
        }

        $v206 = $this->checkAppFormSalaryBeforeTax();
        switch (true){
            case $v206 < 17000:
                $score += 41;
                break;
            case $v206 < 29000:
                $score += 39;
                break;
            case $v206 < 46000:
                $score += 40;
                break;
            case $v206 >= 46000:
                $score += 43;
                break;
        }

        $v143 = $this->checkValidMobileRatio();
        switch (true){
            case $v143 < 93:
                $score += 40;
                break;
            case $v143 < 94:
                $score += 44;
                break;
            case $v143 < 95:
                $score += 41;
                break;
            case $v143 >= 95:
                $score += 39;
                break;
        }

        $v243 = $this->checkCibilTimeOfLastCreditTimeToNow();
        switch (true){
            case $v243 < 0:
                $score += 23;
                break;
            case $v243 < 90:
                $score += 50;
                break;
            case $v243 < 175:
                $score += 35;
                break;
            case $v243 >= 175:
                $score += 29;
                break;
        }

        $v142 = $this->checkAddressBookContactCnt();
        switch (true){
            case $v142 < 250:
                $score += 38;
                break;
            case $v142 < 550:
                $score += 40;
                break;
            case $v142 < 1000:
                $score += 41;
                break;
            case $v142 >= 1000:
                $score += 44;
                break;
        }

        $v592 = $this->checkSMSCntOfLoanApplicationTrialLast30Days();
        switch (true){
            case $v592 < 16:
                $score += 40;
                break;
            case $v592 < 29:
                $score += 41;
                break;
            case $v592 < 38:
                $score += 46;
                break;
            case $v592 >= 38:
                $score += 39;
                break;
        }

        $v234 = $this->checkCibilHisEnquiryCnt();
        switch (true){
            case $v234 < 10:
                $score += 32;
                break;
            case $v234 < 28:
                $score += 39;
                break;
            case $v234 < 68:
                $score += 49;
                break;
            case $v234 >= 68:
                $score += 58;
                break;
        }

        $v600 = $this->checkHistSMSCntOfLoanRejection();
        switch (true){
            case $v600 < 5:
                $score += 40;
                break;
            case $v600 < 8:
                $score += 42;
                break;
            case $v600 < 12:
                $score += 39;
                break;
            case $v600 >= 12:
                $score += 38;
                break;
        }

        $v323 = $this->checkHisMobilePhotoAmount();
        switch (true){
            case $v323 < 1600:
                $score += 38;
                break;
            case $v323 < 8000:
                $score += 43;
                break;
            case $v323 >= 8000:
                $score += 48;
                break;
        }

        $v629 = $this->checkSMSCntOfOverdueRemindLast90Days();
        switch (true){
            case $v629 < 2:
                $score += 43;
                break;
            case $v629 < 3:
                $score += 39;
                break;
            case $v629 >= 3:
                $score += 32;
                break;
        }

        return $score;
    }

    /**
     * 新用户Experian模型分V2
     * @return int
     * @throws \Exception
     */
    public function checkNewUserExperianModelScoreV2(){
        $this->isGetData = false;
        $v357 = $this->checkIsExperianCreditReportReturnedNormal();
        if($v357 != 1){
            return $v357;
        }

        $score = 0;
        $v361 = $this->checkExperianCreditAccountClosed();
        switch (true){
            case $v361 < 4:
                $score += 83;
                break;
            case $v361 < 16:
                $score += 136;
                break;
            case $v361 >= 16:
                $score += 192;
                break;
        }

        $v369 = $this->checkExperianLast30dEnquiryCnt();
        switch (true){
            case $v369 < 1:
                $score += 59;
                break;
            case $v369 < 2:
                $score += 92;
                break;
            case $v369 < 4:
                $score += 105;
                break;
            case $v369 >= 4:
                $score += 116;
                break;
        }

        $v392 = $this->checkExperianCreditScore();
        switch (true){
            case $v392 < 725:
                $score += 78;
                break;
            case $v392 < 835:
                $score += 115;
                break;
            case $v392 >= 835:
                $score += 145;
                break;
        }

        $v368 = $this->checkExperianLast90dEnquiryCnt();
        switch (true){
            case $v368 < 2:
                $score += 57;
                break;
            case $v368 < 6:
                $score += 84;
                break;
            case $v368 < 11:
                $score += 113;
                break;
            case $v368 < 13:
                $score += 127;
                break;
            case $v368 >= 13:
                $score += 156;
                break;
        }

        $v202 = $this->checkLoanAppCnt();
        switch (true){
            case $v202 < 1:
                $score += 92;
                break;
            case $v202 < 2:
                $score += 101;
                break;
            case $v202 >= 2:
                $score += 106;
                break;
        }

        $v206 = $this->checkAppFormSalaryBeforeTax();
        switch (true){
            case $v206 < 24000:
                $score += 92;
                break;
            case $v206 < 30000:
                $score += 101;
                break;
            case $v206 >= 30000:
                $score += 118;
                break;
        }

        return $score;
    }

    /**
     * 新用户Experian模型分V3
     * @return int
     * @throws \Exception
     */
    public function checkNewUserExperianModelScoreV3(){
        $this->isGetData = false;
        $v357 = $this->checkIsExperianCreditReportReturnedNormal();
        if($v357 != 1){
            return $v357;
        }

        $score = 0;
        $v392 = $this->checkExperianCreditScore();
        switch (true){
            case $v392 < 730:
                $score += 42;
                break;
            case $v392 < 780:
                $score += 51;
                break;
            case $v392 < 815:
                $score += 49;
                break;
            case $v392 >= 815:
                $score += 46;
                break;
        }

        $v606 = $this->checkSMSCntOfLoanApprovalLast7Days();
        switch (true){
            case $v606 < 1:
                $score += 44;
                break;
            case $v606 < 3:
                $score += 46;
                break;
            case $v606 < 4:
                $score += 47;
                break;
            case $v606 < 8:
                $score += 48;
                break;
            case $v606 >= 8:
                $score += 51;
                break;
        }

        $v612 = $this->checkSMSCntOfLoanDisbursalLast30Days();
        switch (true){
            case $v612 < 2:
                $score += 38;
                break;
            case $v612 < 3:
                $score += 58;
                break;
            case $v612 < 4:
                $score += 76;
                break;
            case $v612 >= 4:
                $score += 94;
                break;
        }

        $v101 = $this->checkUserAge();
        switch (true){
            case $v101 < 25:
                $score += 42;
                break;
            case $v101 < 31:
                $score += 46;
                break;
            case $v101 >= 31:
                $score += 49;
                break;
        }

        $v368 = $this->checkExperianLast90dEnquiryCnt();
        switch (true){
            case $v368 < 3:
                $score += 34;
                break;
            case $v368 < 4:
                $score += 41;
                break;
            case $v368 < 8:
                $score += 47;
                break;
            case $v368 < 13:
                $score += 53;
                break;
            case $v368 >= 13:
                $score += 43;
                break;
        }

        $v369 = $this->checkExperianLast30dEnquiryCnt();
        switch (true){
            case $v369 < 2:
                $score += 44;
                break;
            case $v369 < 4:
                $score += 46;
                break;
            case $v369 < 5:
                $score += 49;
                break;
            case $v369 < 6:
                $score += 48;
                break;
            case $v369 >= 6:
                $score += 46;
                break;
        }

        $v323 = $this->checkHisMobilePhotoAmount();
        switch (true) {
            case $v323 < 1400:
                $score += 43;
                break;
            case $v323 < 2200:
                $score += 47;
                break;
            case $v323 >= 2200:
                $score += 51;
                break;
        }

        $v391 = $this->checkExperianTimeOfLastPayMent();
        switch (true) {
            case $v391 < 30:
                $score += 39;
                break;
            case $v391 < 65:
                $score += 56;
                break;
            case $v391 < 95:
                $score += 48;
                break;
            case $v391 < 195:
                $score += 36;
                break;
            case $v391 >= 195:
                $score += 32;
                break;
        }

        $v627 = $this->checkSMSCntOfOverdueRemindLast30Days();
        switch (true) {
            case $v627 < 1:
                $score += 56;
                break;
            case $v627 < 3:
                $score += 40;
                break;
            case $v627 >= 3:
                $score += 14;
                break;
        }

        $v603 = $this->checkSMSCntOfLoanRejectionLast60Days();
        switch (true) {
            case $v603 < 2:
                $score += 48;
                break;
            case $v603 < 7:
                $score += 45;
                break;
            case $v603 >= 7:
                $score += 39;
                break;
        }

        $v386 = $this->checkExperianHisDueTotCnt();
        switch (true) {
            case $v386 < 0:
                $score += 47;
                break;
            case $v386 < 2:
                $score += 45;
                break;
            case $v386 >= 2:
                $score += 39;
                break;
        }

        $v364 = $this->checkExperianOutstandingBalanceUnSecured();
        switch (true) {
            case $v364 < 5000:
                $score += 36;
                break;
            case $v364 < 40000:
                $score += 45;
                break;
            case $v364 < 85000:
                $score += 53;
                break;
            case $v364 >= 85000:
                $score += 65;
                break;
        }

        return $score;
    }

    /**
     * 获取该pan下所有用户ID
     * @param $aadhaar
     * @return array|mixed
     */
    protected function getPanOtherUserIds($pan)
    {
        $key = "{$pan}";
        if (isset($this->panLoanUserIds[$key])) {
            return $this->panLoanUserIds[$key];
        } else {
            $userIds = ArrayHelper::getColumn(
                LoanPerson::find()->select(['id'])
                    ->where(['pan_code' => $pan])
                    ->asArray()->all(Yii::$app->db_loan),
                'id');
            return $this->panLoanUserIds[$key] = $userIds;
        }
    }

    /**
     * 是否为总平台新用户
     * @return int
     */
    public function checkIsNewUserInTotPlatform(){
        if($this->data->order->is_all_first == UserLoanOrder::FIRST_LOAN_IS){
            return 1;
        }
        return 0;
    }

    /**
     * 是否为总平台新用户
     * @return int
     */
    public function checkPackageName(){
        if(empty($this->data->order->clientInfoLog->package_name)){
            return -1;
        }
        return $this->data->order->clientInfoLog->package_name;
    }


    /**
     * 总平台(内外)-根据Pan号查询的历史最大逾期天数
     * @return int
     */
    public function checkHistMaxOverdueDaysByPanTotPlatform(){
        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $data = UserLoanOrderRepayment::find()->select(['overdue_day'])
            ->where(['user_id' => $userIds])
            ->orderBy(['overdue_day' => SORT_DESC])
            ->asArray()
            ->one();

        $loanUserIds = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);
        $data_loan = UserLoanOrderRepayment::find()->select(['overdue_day'])
            ->where(['user_id' => $loanUserIds])
            ->orderBy(['overdue_day' => SORT_DESC])
            ->asArray()
            ->one(Yii::$app->db_loan);

        return max($data['overdue_day'] ?? 0, $data_loan['overdue_day'] ?? 0);
    }


    /**
     * loan获取该手机号下所有用户ID
     * @param $phone
     * @return array|mixed
     */
    protected function getLoanPhoneUserIds($phone)
    {
        $key = "{$phone}";
        if (isset($this->phoneLoanUserIds[$key])) {
            return $this->phoneLoanUserIds[$key];
        } else {
            $userIds = ArrayHelper::getColumn(
                LoanPerson::find()->select(['id'])->where(['phone' => $phone])->asArray()->all(Yii::$app->db_loan),
                'id');
            return $this->phoneLoanUserIds[$key] = $userIds;
        }
    }

    /**
     * 近1个月内该手机号在总平台(内外)申请被拒次数
     * @return int|string
     */
    public function checkRejectCntLast1MonthByMobileInTotPlatporm()
    {
        $phone = $this->data->loanPerson->phone;
        $userIds = $this->getPhoneAllUserIds($phone);
        $lastTime = strtotime('last month');
        $count = UserLoanOrder::find()
            ->where(['status' => UserLoanOrder::STATUS_CHECK_REJECT, 'user_id' => $userIds])
            ->andWhere(['>=', 'order_time', $lastTime])->count();

        $loanUserIds = $this->getLoanPhoneUserIds($phone);
        $loan_count = UserLoanOrder::find()
            ->where(['status' => UserLoanOrder::STATUS_CHECK_REJECT, 'user_id' => $loanUserIds])
            ->andWhere(['>=', 'order_time', $lastTime])->count('*', Yii::$app->db_loan);
        return $count + $loan_count;
    }

    /**
     * 历史该手机号在总平台申请被拒次数
     * @return int|string
     */
    public function checkHisRejectCntByPhoneTotPlatform()
    {
        $phone = $this->data->loanPerson->phone;
        $userIds = $this->getPhoneAllUserIds($phone);
        $count = UserLoanOrder::find()
            ->where(['status' => UserLoanOrder::STATUS_CHECK_REJECT, 'user_id' => $userIds])
            ->count();

        $loanUserIds = $this->getLoanPhoneUserIds($phone);
        $loan_count = UserLoanOrder::find()
            ->where(['status' => UserLoanOrder::STATUS_CHECK_REJECT, 'user_id' => $loanUserIds])
            ->count('*', Yii::$app->db_loan);
        return $count + $loan_count;
    }

    /**
     * 近90天该手机号在总平台申请被拒次数
     * @return int|string
     */
    public function checkLast90dRejectCntByPhoneTotPlatform()
    {
        $lastTime = $this->data->order->order_time - 90 * 86400;

        $phone = $this->data->loanPerson->phone;
        $userIds = $this->getPhoneAllUserIds($phone);
        $count = UserLoanOrder::find()
            ->where(['status' => UserLoanOrder::STATUS_CHECK_REJECT, 'user_id' => $userIds])
            ->andWhere(['>=', 'order_time', $lastTime])->count();

        $loanUserIds = $this->getLoanPhoneUserIds($phone);
        $loan_count = UserLoanOrder::find()
            ->where(['status' => UserLoanOrder::STATUS_CHECK_REJECT, 'user_id' => $loanUserIds])
            ->andWhere(['>=', 'order_time', $lastTime])->count('*', Yii::$app->db_loan);
        return $count + $loan_count;
    }

    /**
     * 近60天该手机号在总平台申请被拒次数
     * @return int|string
     */
    public function checkLast60dRejectCntByPhoneTotPlatform()
    {
        $lastTime = $this->data->order->order_time - 60 * 86400;

        $phone = $this->data->loanPerson->phone;
        $userIds = $this->getPhoneAllUserIds($phone);
        $count = UserLoanOrder::find()
            ->where(['status' => UserLoanOrder::STATUS_CHECK_REJECT, 'user_id' => $userIds])
            ->andWhere(['>=', 'order_time', $lastTime])->count();

        $loanUserIds = $this->getLoanPhoneUserIds($phone);
        $loan_count = UserLoanOrder::find()
            ->where(['status' => UserLoanOrder::STATUS_CHECK_REJECT, 'user_id' => $loanUserIds])
            ->andWhere(['>=', 'order_time', $lastTime])->count('*', Yii::$app->db_loan);
        return $count + $loan_count;
    }

    /**
     * 近7天该手机号在总平台申请被拒次数
     * @return int|string
     */
    public function checkLast7dRejectCntByPhoneTotPlatform()
    {
        $lastTime = $this->data->order->order_time - 7 * 86400;

        $phone = $this->data->loanPerson->phone;
        $userIds = $this->getPhoneAllUserIds($phone);
        $count = UserLoanOrder::find()
            ->where(['status' => UserLoanOrder::STATUS_CHECK_REJECT, 'user_id' => $userIds])
            ->andWhere(['>=', 'order_time', $lastTime])->count();

        $loanUserIds = $this->getLoanPhoneUserIds($phone);
        $loan_count = UserLoanOrder::find()
            ->where(['status' => UserLoanOrder::STATUS_CHECK_REJECT, 'user_id' => $loanUserIds])
            ->andWhere(['>=', 'order_time', $lastTime])->count('*', Yii::$app->db_loan);
        return $count + $loan_count;
    }

    /**
     * 历史该Pan卡号在总平台申请被拒次数
     * @return int|string
     */
    public function checkHisRejectCntByPanInTotPlatform()
    {
        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $count = UserLoanOrder::find()
            ->where(['status' => UserLoanOrder::STATUS_CHECK_REJECT, 'user_id' => $userIds])
            ->count();

        $loanUserIds = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);
        $loan_count = UserLoanOrder::find()
            ->where(['status' => UserLoanOrder::STATUS_CHECK_REJECT, 'user_id' => $loanUserIds])
            ->count('*', Yii::$app->db_loan);
        return $count + $loan_count;
    }

    /**
     * 近90天该Pan卡号在总平台申请被拒次数
     * @return int|string
     */
    public function checkLast90dRejectCntByPanInTotPlatform()
    {
        $lastTime = $this->data->order->order_time - 90 * 86400;

        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $count = UserLoanOrder::find()
            ->where(['status' => UserLoanOrder::STATUS_CHECK_REJECT, 'user_id' => $userIds])
            ->andWhere(['>=', 'order_time', $lastTime])
            ->count();

        $loanUserIds = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);
        $loan_count = UserLoanOrder::find()
            ->where(['status' => UserLoanOrder::STATUS_CHECK_REJECT, 'user_id' => $loanUserIds])
            ->andWhere(['>=', 'order_time', $lastTime])
            ->count('*', Yii::$app->db_loan);
        return $count + $loan_count;
    }

    /**
     * 近60天该Pan卡号在总平台申请被拒次数
     * @return int|string
     */
    public function checkLast60dRejectCntByPanInTotPlatform()
    {
        $lastTime = $this->data->order->order_time - 60 * 86400;

        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $count = UserLoanOrder::find()
            ->where(['status' => UserLoanOrder::STATUS_CHECK_REJECT, 'user_id' => $userIds])
            ->andWhere(['>=', 'order_time', $lastTime])
            ->count();

        $loanUserIds = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);
        $loan_count = UserLoanOrder::find()
            ->where(['status' => UserLoanOrder::STATUS_CHECK_REJECT, 'user_id' => $loanUserIds])
            ->andWhere(['>=', 'order_time', $lastTime])
            ->count('*', Yii::$app->db_loan);
        return $count + $loan_count;
    }

    /**
     * 近30天该Pan卡号在总平台申请被拒次数
     * @return int|string
     */
    public function checkLast30dRejectCntByPanInTotPlatform()
    {
        $lastTime = $this->data->order->order_time - 30 * 86400;

        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $count = UserLoanOrder::find()
            ->where(['status' => UserLoanOrder::STATUS_CHECK_REJECT, 'user_id' => $userIds])
            ->andWhere(['>=', 'order_time', $lastTime])
            ->count();

        $loanUserIds = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);
        $loan_count = UserLoanOrder::find()
            ->where(['status' => UserLoanOrder::STATUS_CHECK_REJECT, 'user_id' => $loanUserIds])
            ->andWhere(['>=', 'order_time', $lastTime])
            ->count('*', Yii::$app->db_loan);
        return $count + $loan_count;
    }

    /**
     * 近7天该Pan卡号在总平台申请被拒次数
     * @return int|string
     */
    public function checkLast7dRejectCntByPanInTotPlatform()
    {
        $lastTime = $this->data->order->order_time - 7 * 86400;

        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $count = UserLoanOrder::find()
            ->where(['status' => UserLoanOrder::STATUS_CHECK_REJECT, 'user_id' => $userIds])
            ->andWhere(['>=', 'order_time', $lastTime])
            ->count();

        $loanUserIds = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);
        $loan_count = UserLoanOrder::find()
            ->where(['status' => UserLoanOrder::STATUS_CHECK_REJECT, 'user_id' => $loanUserIds])
            ->andWhere(['>=', 'order_time', $lastTime])
            ->count('*', Yii::$app->db_loan);
        return $count + $loan_count;
    }

    /**
     * 历史该Pan卡号在总平台的申请次数
     * @return int|string
     */
    public function checkHisApplyCntByPanInTotPlatform()
    {
        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $count = UserLoanOrder::find()
            ->where(['user_id' => $userIds])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->count();

        $loanUserIds = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);
        $loan_count = UserLoanOrder::find()
            ->where(['user_id' => $loanUserIds])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->count('*', Yii::$app->db_loan);
        return $count + $loan_count;
    }

    /**
     * 近90天该Pan卡号在总平台的申请次数
     * @return int|string
     */
    public function checkLast90dApplyCntByPanInTotPlatform()
    {
        $lastTime = $this->data->order->order_time - 90 * 86400;

        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $count = UserLoanOrder::find()
            ->where(['user_id' => $userIds])
            ->andWhere(['>=', 'order_time', $lastTime])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->count();

        $loanUserIds = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);
        $loan_count = UserLoanOrder::find()
            ->where(['user_id' => $loanUserIds])
            ->andWhere(['>=', 'order_time', $lastTime])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->count('*', Yii::$app->db_loan);
        return $count + $loan_count;
    }

    /**
     * 近60天该Pan卡号在总平台的申请次数
     * @return int|string
     */
    public function checkLast60dApplyCntByPanInTotPlatform()
    {
        $lastTime = $this->data->order->order_time - 60 * 86400;

        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $count = UserLoanOrder::find()
            ->where(['user_id' => $userIds])
            ->andWhere(['>=', 'order_time', $lastTime])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->count();

        $loanUserIds = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);
        $loan_count = UserLoanOrder::find()
            ->where(['user_id' => $loanUserIds])
            ->andWhere(['>=', 'order_time', $lastTime])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->count('*', Yii::$app->db_loan);
        return $count + $loan_count;
    }

    /**
     * 近30天该Pan卡号在总平台的申请次数
     * @return int|string
     */
    public function checkLast30dApplyCntByPanInTotPlatform()
    {
        $lastTime = $this->data->order->order_time - 30 * 86400;

        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $count = UserLoanOrder::find()
            ->where(['user_id' => $userIds])
            ->andWhere(['>=', 'order_time', $lastTime])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->count();

        $loanUserIds = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);
        $loan_count = UserLoanOrder::find()
            ->where(['user_id' => $loanUserIds])
            ->andWhere(['>=', 'order_time', $lastTime])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->count('*', Yii::$app->db_loan);
        return $count + $loan_count;
    }

    /**
     * 近7天该Pan卡号在总平台的申请次数
     * @return int|string
     */
    public function checkLast7dApplyCntByPanInTotPlatform()
    {
        $lastTime = $this->data->order->order_time - 7 * 86400;

        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $count = UserLoanOrder::find()
            ->where(['user_id' => $userIds])
            ->andWhere(['>=', 'order_time', $lastTime])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->count();

        $loanUserIds = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);
        $loan_count = UserLoanOrder::find()
            ->where(['user_id' => $loanUserIds])
            ->andWhere(['>=', 'order_time', $lastTime])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->count('*', Yii::$app->db_loan);
        return $count + $loan_count;
    }

    /**
     * 近1个月内手机号在总平台(内外)的申请次数
     * @return int|string
     */
    public function checkApplyCntLast1MonthByMobileInTotPlatporm()
    {
        $phone = $this->data->loanPerson->phone;
        $userIds = $this->getPhoneAllUserIds($phone);
        $lastTime = strtotime('last month');
        $count = UserLoanOrder::find()
            ->where(['user_id' => $userIds])
            ->andWhere(['>=', 'order_time', $lastTime])->count();

        $loanUserIds = $this->getLoanPhoneUserIds($phone);
        $loan_count = UserLoanOrder::find()
            ->where(['user_id' => $loanUserIds])
            ->andWhere(['>=', 'order_time', $lastTime])->count('*', Yii::$app->db_loan);

        return $count + $loan_count;
    }

    /**
     * 历史该手机号在总平台申请次数
     * @return int|string
     */
    public function checkHisApplyCntByPhoneTotPlatform()
    {
        $phone = $this->data->loanPerson->phone;
        $userIds = $this->getPhoneAllUserIds($phone);
        $count = UserLoanOrder::find()
            ->where(['user_id' => $userIds])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->count();

        $loanUserIds = $this->getLoanPhoneUserIds($phone);
        $loan_count = UserLoanOrder::find()
            ->where(['user_id' => $loanUserIds])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->count('*', Yii::$app->db_loan);

        return $count + $loan_count;
    }

    /**
     * 近90天该手机号在总平台申请次数
     * @return int|string
     */
    public function checkLast90dApplyCntByPhoneTotPlatform()
    {
        $lastTime = $this->data->order->order_time - 90 * 86400;

        $phone = $this->data->loanPerson->phone;
        $userIds = $this->getPhoneAllUserIds($phone);
        $count = UserLoanOrder::find()
            ->where(['user_id' => $userIds])
            ->andWhere(['>=', 'order_time', $lastTime])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->count();

        $loanUserIds = $this->getLoanPhoneUserIds($phone);
        $loan_count = UserLoanOrder::find()
            ->where(['user_id' => $loanUserIds])
            ->andWhere(['>=', 'order_time', $lastTime])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->count('*', Yii::$app->db_loan);

        return $count + $loan_count;
    }

    /**
     * 近60天该手机号在总平台申请次数
     * @return int|string
     */
    public function checkLast60dApplyCntByPhoneTotPlatform()
    {
        $lastTime = $this->data->order->order_time - 60 * 86400;

        $phone = $this->data->loanPerson->phone;
        $userIds = $this->getPhoneAllUserIds($phone);
        $count = UserLoanOrder::find()
            ->where(['user_id' => $userIds])
            ->andWhere(['>=', 'order_time', $lastTime])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->count();

        $loanUserIds = $this->getLoanPhoneUserIds($phone);
        $loan_count = UserLoanOrder::find()
            ->where(['user_id' => $loanUserIds])
            ->andWhere(['>=', 'order_time', $lastTime])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->count('*', Yii::$app->db_loan);

        return $count + $loan_count;
    }

    /**
     * 近7天该手机号在总平台申请次数
     * @return int|string
     */
    public function checkLast7dApplyCntByPhoneTotPlatform()
    {
        $lastTime = $this->data->order->order_time - 7 * 86400;

        $phone = $this->data->loanPerson->phone;
        $userIds = $this->getPhoneAllUserIds($phone);
        $count = UserLoanOrder::find()
            ->where(['user_id' => $userIds])
            ->andWhere(['>=', 'order_time', $lastTime])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->count();

        $loanUserIds = $this->getLoanPhoneUserIds($phone);
        $loan_count = UserLoanOrder::find()
            ->where(['user_id' => $loanUserIds])
            ->andWhere(['>=', 'order_time', $lastTime])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->count('*', Yii::$app->db_loan);

        return $count + $loan_count;
    }

    /**
     * 数盟设备ID在总平台(内外)的历史申请次数
     * @return int
     */
    public function checkHisSMDeviceIDApplyCntInTotPlatporm(){
        if(empty($this->data->order->did)){
            return -1;
        }
        $count = UserLoanOrder::find()->where(['did' => $this->data->order->did])->count();
        $loan_count = UserLoanOrder::find()->where(['did' => $this->data->order->did])->count('*', Yii::$app->db_loan);

        return $count + $loan_count;
    }

    /**
     * 近90天数盟设备ID在总平台(内外)的申请次数
     * @return int
     */
    public function checkLast90ApplyCntBySMDeviceIDInTotPlatporm(){
        if(empty($this->data->order->did)){
            return -1;
        }
        $begin_time = $this->data->order->order_time - 90 * 86400;
        $count = UserLoanOrder::find()->where(['did' => $this->data->order->did])->andWhere(['>=', 'order_time', $begin_time])->count();
        $loan_count = UserLoanOrder::find()->where(['did' => $this->data->order->did])->andWhere(['>=', 'order_time', $begin_time])->count('*', Yii::$app->db_loan);

        return $count + $loan_count;
    }

    /**
     * 近60天数盟设备ID在总平台(内外)的申请次数
     * @return int
     */
    public function checkLast60dApplyCntBySMDeviceIDTotPlatform(){
        if(empty($this->data->order->did)){
            return -1;
        }
        $begin_time = $this->data->order->order_time - 60 * 86400;
        $count = UserLoanOrder::find()
            ->where(['did' => $this->data->order->did])
            ->andWhere(['>=', 'order_time', $begin_time])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->count();
        $loan_count = UserLoanOrder::find()
            ->where(['did' => $this->data->order->did])
            ->andWhere(['>=', 'order_time', $begin_time])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->count('*', Yii::$app->db_loan);

        return $count + $loan_count;
    }

    /**
     * 近30天数盟设备ID在总平台(内外)的申请次数
     * @return int
     */
    public function checkLast30ApplyCntBySMDeviceIDInTotPlatporm(){
        if(empty($this->data->order->did)){
            return -1;
        }
        $begin_time = $this->data->order->order_time - 30 * 86400;
        $count = UserLoanOrder::find()->where(['did' => $this->data->order->did])->andWhere(['>=', 'order_time', $begin_time])->count();
        $loan_count = UserLoanOrder::find()->where(['did' => $this->data->order->did])->andWhere(['>=', 'order_time', $begin_time])->count('*', Yii::$app->db_loan);

        return $count + $loan_count;
    }

    /**
     * 近7天数盟设备ID在总平台(内外)的申请次数
     * @return int
     */
    public function checkLast7dApplyCntBySMDeviceIDTotPlatform(){
        if(empty($this->data->order->did)){
            return -1;
        }
        $begin_time = $this->data->order->order_time - 7 * 86400;
        $count = UserLoanOrder::find()
            ->where(['did' => $this->data->order->did])
            ->andWhere(['>=', 'order_time', $begin_time])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->count();

        $loan_count = UserLoanOrder::find()
            ->where(['did' => $this->data->order->did])
            ->andWhere(['>=', 'order_time', $begin_time])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->count('*', Yii::$app->db_loan);

        return $count + $loan_count;
    }

    /**
     * 数盟设备ID历史在总平台(内外)的申请被拒次数
     * @return int
     */
    public function checkHisSMDeviceIDApplyRejectCntInTotPlatporm(){
        if(empty($this->data->order->did)){
            return -1;
        }
        $count = UserLoanOrder::find()
            ->where(['did' => $this->data->order->did,
                     'status' => UserLoanOrder::STATUS_CHECK_REJECT])
            ->count();

        $loan_count = UserLoanOrder::find()
            ->where(['did' => $this->data->order->did,
                     'status' => UserLoanOrder::STATUS_CHECK_REJECT])
            ->count('*', Yii::$app->db_loan);

        return $count + $loan_count;
    }

    /**
     * 近90天数盟设备ID在总平台(内外)的申请被拒次数
     * @return int
     */
    public function checkLast90RejectCntBySMDeviceIDInTotPlatporm(){
        if(empty($this->data->order->did)){
            return -1;
        }
        $begin_time = $this->data->order->order_time - 90 * 86400;
        $count = UserLoanOrder::find()
            ->where(['did' => $this->data->order->did,
                     'status' => UserLoanOrder::STATUS_CHECK_REJECT])
            ->andWhere(['>=', 'order_time', $begin_time])
            ->count();

        $loan_count = UserLoanOrder::find()
            ->where(['did' => $this->data->order->did,
                     'status' => UserLoanOrder::STATUS_CHECK_REJECT])
            ->andWhere(['>=', 'order_time', $begin_time])
            ->count('*', Yii::$app->db_loan);

        return $count + $loan_count;
    }

    /**
     * 近60天数盟设备ID在总平台(内外)的申请被拒次数
     * @return int
     */
    public function checkLast60dRejectCntBySMDeviceIDTotPlatform(){
        if(empty($this->data->order->did)){
            return -1;
        }
        $begin_time = $this->data->order->order_time - 60 * 86400;
        $count = UserLoanOrder::find()
            ->where(['did' => $this->data->order->did,
                     'status' => UserLoanOrder::STATUS_CHECK_REJECT])
            ->andWhere(['>=', 'order_time', $begin_time])
            ->count();

        $loan_count = UserLoanOrder::find()
            ->where(['did' => $this->data->order->did,
                     'status' => UserLoanOrder::STATUS_CHECK_REJECT])
            ->andWhere(['>=', 'order_time', $begin_time])
            ->count('*', Yii::$app->db_loan);

        return $count + $loan_count;
    }

    /**
     * 近30天数盟设备ID在总平台(内外)的申请被拒次数
     * @return int
     */
    public function checkLast30RejectCntBySMDeviceIDInTotPlatporm(){
        if(empty($this->data->order->did)){
            return -1;
        }
        $begin_time = $this->data->order->order_time - 30 * 86400;
        $count = UserLoanOrder::find()
            ->where(['did' => $this->data->order->did,
                     'status' => UserLoanOrder::STATUS_CHECK_REJECT])
            ->andWhere(['>=', 'order_time', $begin_time])
            ->count();

        $loan_count = UserLoanOrder::find()
            ->where(['did' => $this->data->order->did,
                     'status' => UserLoanOrder::STATUS_CHECK_REJECT])
            ->andWhere(['>=', 'order_time', $begin_time])
            ->count('*', Yii::$app->db_loan);

        return $count + $loan_count;
    }

    /**
     * 近7天数盟设备ID在总平台(内外)的申请被拒次数
     * @return int
     */
    public function checkLast7dRejectCntBySMDeviceIDTotPlatform(){
        if(empty($this->data->order->did)){
            return -1;
        }
        $begin_time = $this->data->order->order_time - 7 * 86400;
        $count = UserLoanOrder::find()
            ->where(['did' => $this->data->order->did,
                     'status' => UserLoanOrder::STATUS_CHECK_REJECT])
            ->andWhere(['>=', 'order_time', $begin_time])
            ->count();

        $loan_count = UserLoanOrder::find()
            ->where(['did' => $this->data->order->did,
                     'status' => UserLoanOrder::STATUS_CHECK_REJECT])
            ->andWhere(['>=', 'order_time', $begin_time])
            ->count('*', Yii::$app->db_loan);

        return $count + $loan_count;
    }

    /**
     * 本次订单Pan卡号在总平台(内外)的当前处于待还款状态的订单数
     * @return int
     */
    public function checkPendingRepaymentCntOfPanInTotPlatporm(){
        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $count = UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds, 'status' => UserLoanOrderRepayment::STATUS_NORAML])
            ->count();

        $loanUserIds = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);
        $loan_count = UserLoanOrderRepayment::find()
            ->where(['user_id' => $loanUserIds, 'status' => UserLoanOrderRepayment::STATUS_NORAML])
            ->count('*', Yii::$app->db_loan);

        return $count + $loan_count;
    }

    /**
     * 本次订单Pan卡号在总平台(内外)的当前处于待还款状态的订单总金额
     * @return int
     */
    public function checkPendingRepaymentTotAmtOfPanTotPlatform(){
        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $data = UserLoanOrderRepayment::find()
            ->select(['principal', 'cost_fee'])
            ->where(['user_id' => $userIds, 'status' => UserLoanOrderRepayment::STATUS_NORAML])
            ->all();

        $money = 0;
        foreach ($data as $v){
            $money += $v['principal'] - $v['cost_fee'];
        }

        $loanUserIds = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);
        $data_loan = UserLoanOrderRepayment::find()
            ->select(['principal', 'cost_fee'])
            ->where(['user_id' => $loanUserIds, 'status' => UserLoanOrderRepayment::STATUS_NORAML])
            ->all(Yii::$app->db_loan);

        foreach ($data_loan as $v){
            $money += $v['principal'] - $v['cost_fee'];
        }

        return round($money / 100);
    }

    /**
     * 本次订单Pan卡号在总平台(内外)当前待还款订单的最大逾期天数
     * @return int
     */
    public function checkMaxDueDaysOfPendingRepaymentOrderOfPanInTotPlatporm(){
        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $data = UserLoanOrderRepayment::find()->select(['overdue_day'])
            ->where(['user_id' => $userIds, 'status' => UserLoanOrderRepayment::STATUS_NORAML])
            ->orderBy(['overdue_day' => SORT_DESC])
            ->asArray()
            ->one();

        $loanUserIds = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);
        $data_loan = UserLoanOrderRepayment::find()->select(['overdue_day'])
            ->where(['user_id' => $loanUserIds, 'status' => UserLoanOrderRepayment::STATUS_NORAML])
            ->orderBy(['overdue_day' => SORT_DESC])
            ->asArray()
            ->one(Yii::$app->db_loan);

        return max($data['overdue_day'] ?? 0, $data_loan['overdue_day'] ?? 0);
    }

    /**
     * 总平台该手机号注册的账户体系个数
     * @return int
     */
    public function checkAccountCntOfPhoneTotPlatform(){
        $userIds = $this->getPhoneAllUserIds($this->data->loanPerson->phone);
        $userIds_loan = $this->getLoanPhoneUserIds($this->data->loanPerson->phone);

        return count($userIds) + count($userIds_loan);
    }

    /**
     * 总平台该手机号最早注册时间距今的时间
     * @return int
     */
    public function checkMaxDateDiffOfRegisterAndOrderByPhoneTotPlatform(){
        $data = LoanPerson::find()
            ->where(['phone' => $this->data->loanPerson->phone])
            ->orderBy(['created_at' => SORT_ASC])
            ->asArray()->one();

        $time = $data['created_at'];

        $data_loan = LoanPerson::find()
            ->where(['phone' => $this->data->loanPerson->phone])
            ->orderBy(['created_at' => SORT_ASC])
            ->asArray()->one(Yii::$app->db_loan);

        if(!empty($data_loan) && $data_loan['created_at'] < $time){
            $time = $data_loan['created_at'];
        }

        return (strtotime(date('Y-m-d', $this->data->order->order_time)) - strtotime(date('Y-m-d', $time))) / 86400;
    }

    /**
     * 总平台该手机号最晚注册时间距今的时间
     * @return int
     */
    public function checkMinDateDiffOfRegisterAndOrderByPhoneTotPlatform(){
        $data = LoanPerson::find()
            ->where(['phone' => $this->data->loanPerson->phone])
            ->orderBy(['created_at' => SORT_DESC])
            ->asArray()->one();

        $time = $data['created_at'];

        $data_loan = LoanPerson::find()
            ->where(['phone' => $this->data->loanPerson->phone])
            ->orderBy(['created_at' => SORT_DESC])
            ->asArray()->one(Yii::$app->db_loan);

        if(!empty($data_loan) && $data_loan['created_at'] > $time){
            $time = $data_loan['created_at'];
        }

        return (strtotime(date('Y-m-d', $this->data->order->order_time)) - strtotime(date('Y-m-d', $time))) / 86400;
    }

    /**
     * 历史该Pan卡号在总平台放款的次数
     * @return int
     */
    public function checkHisLoanCntByPanTotPlatform(){
        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $count = UserLoanOrderRepayment::find()->where(['user_id' => $userIds])->count();

        $userIds_loan = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);
        $count_loan = UserLoanOrderRepayment::find()->where(['user_id' => $userIds_loan])->count('*', Yii::$app->db_loan);
        return $count + $count_loan;
    }

    /**
     * 近30天该Pan卡号在总平台放款的次数
     * @return int
     */
    public function checkLast30dLoanCntByPanTotPlatform(){
        $begin_time = $this->data->order->order_time - 30 * 86400;
        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $count = UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds])
            ->andWhere(['>=', 'loan_time', $begin_time])
            ->count();

        $userIds_loan = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);
        $count_loan = UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds_loan])
            ->andWhere(['>=', 'loan_time', $begin_time])
            ->count('*', Yii::$app->db_loan);
        return $count + $count_loan;
    }

    /**
     * 历史该Pan卡号在总平台放款的不同账户体系数
     * @return int
     */
    public function checkHisLoanAccountCntByPanTotPlatform(){
        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $data = UserLoanOrderRepayment::find()->where(['user_id' => $userIds])->groupBy(['user_id'])->all();

        $userIds_loan = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);
        $data_loan = UserLoanOrderRepayment::find()->where(['user_id' => $userIds_loan])->groupBy(['user_id'])->all(Yii::$app->db_loan);
        return count($data) + count($data_loan);
    }

    /**
     * 近30天该Pan卡号在总平台放款的不同账户体系数
     * @return int
     */
    public function checkLast30dLoanAccountCntByPanTotPlatform(){
        $begin_time = $this->data->order->order_time - 30 * 86400;
        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $data = UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds])
            ->andWhere(['>=', 'loan_time', $begin_time])
            ->groupBy(['user_id'])
            ->all();

        $userIds_loan = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);
        $data_loan = UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds_loan])
            ->andWhere(['>=', 'loan_time', $begin_time])
            ->groupBy(['user_id'])
            ->all(Yii::$app->db_loan);
        return count($data) + count($data_loan);
    }

    /**
     * 总平台该Pan卡号本次申请订单距离上次申请订单时间的时间差
     * @return int
     */
    public function checkDateDiffOfOrderAndLastOrderApplyByPanTotPlatform(){
        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $data = UserLoanOrder::find()
            ->where(['user_id' => $userIds])
            ->andWhere(['!=', 'id', $this->data->order->id])
            ->orderBy(['order_time' => SORT_DESC])
            ->one();

        $userIds_loan = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);
        $data_loan = UserLoanOrder::find()
            ->where(['user_id' => $userIds_loan])
            ->orderBy(['order_time' => SORT_DESC])
            ->one(Yii::$app->db_loan);

        if(empty($data) && empty($data_loan)){
            return -1;
        }

        $orderTime = max($data['order_time'] ?? 0, $data_loan['order_time'] ?? 0);


        return (strtotime(date('Y-m-d', $this->data->order->order_time)) - strtotime(date('Y-m-d', $orderTime))) / 86400;
    }

    /**
     * 总平台该Pan卡号本次申请订单距离上次放款订单时间的时间差
     * @return int
     */
    public function checkDateDiffOfOrderAndLastOrderLoanByPanTotPlatform(){
        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $data = UserLoanOrder::find()
            ->where(['user_id' => $userIds])
            ->andWhere(['>', 'loan_time', 0])
            ->orderBy(['loan_time' => SORT_DESC])
            ->one();

        $userIds_loan = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);
        $data_loan = UserLoanOrder::find()
            ->where(['user_id' => $userIds_loan])
            ->andWhere(['>', 'loan_time', 0])
            ->orderBy(['loan_time' => SORT_DESC])
            ->one(Yii::$app->db_loan);

        if(empty($data) && empty($data_loan)){
            return -1;
        }

        $orderTime = max($data['loan_time'] ?? 0, $data_loan['loan_time'] ?? 0);


        return (strtotime(date('Y-m-d', $this->data->order->order_time)) - strtotime(date('Y-m-d', $orderTime))) / 86400;
    }

    /**
     * 总平台该Pan卡号已到期订单数
     * @return int
     */
    public function checkHisExpireCntByPanTotPlatform(){
        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $after = strtotime(date('Y-m-d', $this->data->order->order_time));

        $count = UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds])
            ->andWhere(['<=', 'plan_repayment_time', $after])
            ->count();

        $userIds_loan = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);
        $loan_count = UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds_loan])
            ->andWhere(['<=', 'plan_repayment_time', $after])
            ->count('*', Yii::$app->db_loan);
        return $count + $loan_count;
    }

    /**
     * 近30天该Pan卡号在总平台已到期订单数
     * @return int
     */
    public function checkLast30dExpireCntByPanTotPlatform(){
        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $after = strtotime(date('Y-m-d', $this->data->order->order_time));
        $before = $after - 30 * 86400;

        $count = UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds])
            ->andWhere(['>=', 'plan_repayment_time', $before])
            ->andWhere(['<=', 'plan_repayment_time', $after])
            ->count();

        $userIds_loan = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);
        $loan_count = UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds_loan])
            ->andWhere(['>=', 'plan_repayment_time', $before])
            ->andWhere(['<=', 'plan_repayment_time', $after])
            ->count('*', Yii::$app->db_loan);
        return $count + $loan_count;
    }

    /**
     * 历史该Pan卡号在总平台的还款次数
     * @return int
     */
    public function checkHisRepayCntByPanTotPlatform(){
        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $count = UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds,
                     'status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE])
            ->count();

        $userIds_loan = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);
        $count_loan = UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds_loan,
                     'status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE])
            ->count('*', Yii::$app->db_loan);

        return $count + $count_loan;
    }

    /**
     * 近30天该Pan卡号在总平台的还款次数
     * @return int
     */
    public function checkLast30dRepayCntByPanTotPlatform(){
        $before = $this->data->order->order_time - 30 * 86400;
        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $count = UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds,
                     'status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE])
            ->andWhere(['>=', 'closing_time', $before])
            ->count();

        $userIds_loan = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);
        $count_loan = UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds_loan,
                     'status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE])
            ->andWhere(['>=', 'closing_time', $before])
            ->count('*', Yii::$app->db_loan);

        return $count + $count_loan;
    }

    /**
     * 历史该Pan卡号在总平台的逾期次数
     * @return int
     */
    public function checkHisDueCntByPanTotPlatform(){
        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $count = UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds,
                     'is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_YES])
            ->count();

        $userIds_loan = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);
        $count_loan = UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds_loan,
                     'is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_YES])
            ->count('*', Yii::$app->db_loan);

        return $count + $count_loan;
    }

    /**
     * 近30天该Pan卡号在总平台的逾期次数
     * @return int
     */
    public function checkLast30dDueCntByPanTotPlatform(){
        $before = $this->data->order->order_time - 30 * 86400;

        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $count = UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds,
                     'is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_YES])
            ->andWhere(['>=', 'plan_repayment_time', $before])
            ->count();

        $userIds_loan = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);
        $count_loan = UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds_loan,
                     'is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_YES])
            ->andWhere(['>=', 'plan_repayment_time', $before])
            ->count('*', Yii::$app->db_loan);

        return $count + $count_loan;
    }

    /**
     * 总平台-根据Pan号查询的历史逾期天数的总和
     * @return int
     */
    public function checkHisDueSumDayByPanTotPlatform(){
        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $data = ArrayHelper::getColumn(UserLoanOrderRepayment::find()->select(['overdue_day'])
            ->where(['user_id' => $userIds])
            ->asArray()
            ->all(),
            'overdue_day');

        $loanUserIds = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);
        $data_loan = ArrayHelper::getColumn(UserLoanOrderRepayment::find()->select(['overdue_day'])
            ->where(['user_id' => $loanUserIds])
            ->asArray()
            ->all(Yii::$app->db_loan),
            'overdue_day');

        return array_sum($data) + array_sum($data_loan);
    }

    /**
     * 总平台-根据Pan号查询的历史逾期天数的平均值
     * @return int
     */
    public function checkHisDueAvgDayByPanTotPlatform(){
        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $data = ArrayHelper::getColumn(UserLoanOrderRepayment::find()->select(['overdue_day'])
            ->where(['user_id' => $userIds, 'is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_YES])
            ->asArray()
            ->all(),
            'overdue_day');

        $loanUserIds = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);
        $data_loan = ArrayHelper::getColumn(UserLoanOrderRepayment::find()->select(['overdue_day'])
            ->where(['user_id' => $loanUserIds, 'is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_YES])
            ->asArray()
            ->all(Yii::$app->db_loan),
            'overdue_day');

        if(empty($data) && empty($data_loan)){
            return 0;
        }

        return round((array_sum($data) + array_sum($data_loan)) / (count($data) + count($data_loan)));
    }

    /**
     * 总平台该Pan卡号历史关联过的不同Aadhaar卡号数量
     * @return int
     */
    public function checkPanHisMatchAadhaarCntTotPlatform(){
        $pan_code = $this->data->loanPerson->pan_code;
        $data = ArrayHelper::getColumn(
            LoanPerson::find()
                ->select(['aadhaar_md5'])
                ->where(['pan_code' => $pan_code])
                ->andWhere(['is not','aadhaar_md5',null])
                ->asArray()->all(),
            'aadhaar_md5');

        $data_loan = ArrayHelper::getColumn(
            LoanPerson::find()
                ->select(['aadhaar_md5'])
                ->where(['pan_code' => $pan_code])
                ->andWhere(['is not','aadhaar_md5',null])
                ->asArray()->all(Yii::$app->db_loan),
            'aadhaar_md5');

        return count(array_unique(array_merge($data, $data_loan)));
    }

    /**
     * 总平台该Aadhaar卡号历史关联过的不同Pan卡号数量
     * @return int
     */
    public function checkAadhaarHisMatchPanCntTotPlatform()
    {
        if(empty($this->data->loanPerson->aadhaar_md5)){
            return -1;
        }
        $aadhaar_number = $this->data->loanPerson->aadhaar_md5;
        $data = ArrayHelper::getColumn(
            LoanPerson::find()
                ->select(['pan_code'])
                ->where(['aadhaar_md5' => $aadhaar_number])
                ->andWhere(['is not','pan_code',null])
                ->asArray()->all(),
            'pan_code');

        $data_loan = ArrayHelper::getColumn(
            LoanPerson::find()
                ->select(['pan_code'])
                ->where(['aadhaar_md5' => $aadhaar_number])
                ->andWhere(['is not','pan_code',null])
                ->asArray()->all(Yii::$app->db_loan),
            'pan_code');

        return count(array_unique(array_merge($data, $data_loan)));
    }

    /**
     * icredit_google包的版本号是否在1.5.0以下（不包含1.5.0）
     * @return int
     */
    public function checkIsIcreditGoogleVersionBanned(){
        if(empty($this->data->order->clientInfoLog->app_market) || empty($this->data->order->clientInfoLog->app_version)){
            return -1;
        }


        if($this->data->order->clientInfoLog->app_market != 'external_icredit_google'){
            return -2;
        }

        if(version_compare($this->data->order->clientInfoLog->app_version, '1.6.0', '<')){
            return 1;
        }
        return 0;
    }


    /**
     * 本次成功调用哪一种征信报告
     * @return int
     * @throws \Exception
     */
    public function checkWhichSuccessCallReport(){
        $report = $this->getCibilReport();
        if(!empty($report)){
            if(!empty($report['CreditReport']['Header']['SubjectReturnCode']) && $report['CreditReport']['Header']['SubjectReturnCode'] == 1){
                return 1;
            }
            return 0;
        }

        $report = $this->getExperianReport();
        if(isset($report['Header']['SystemCode']) && $report['Header']['SystemCode'] == 0){
            if(!empty($report['UserMessage']['UserMessageText']) && $report['UserMessage']['UserMessageText'] == 'Normal Response'){
                return 2;
            }
        }

        return -1;
    }

    /**
     * 本次订单征信报告均调用失败
     * @throws \Exception
     */
    public function checkActionOfReportCall(){
        if(!$this->isGetData){
            return -1;
        }
        $report_cibil = $this->getCibilReport();
        if(!empty($report_cibil)){
            return -1;
        }

        $report = $this->getExperianReport();
        $cibil_retry_num = $this->data->order->userCreditReportCibil->retry_num ?? 0;
        $experian_retry_num = $this->data->order->userCreditReportExperian->retry_num ?? 0;

        if(isset($report['Header']['SystemCode']) && $report['Header']['SystemCode'] == 0){
            if(!empty($report['UserMessage']['UserMessageText']) && $report['UserMessage']['UserMessageText'] == 'Normal Response'){
                return -1;
            }

            if($cibil_retry_num < 3){
                throw new Exception('征信报告调用失败，等待重试', 1001);
            }
        }else{
            if($cibil_retry_num < 3 || $experian_retry_num < 3){
                throw new Exception('征信报告调用失败，等待重试', 1001);
            }
        }

        return -1;
    }

    /**
     * 获取用户短信
     * @param int $day
     * @param int $orderTime
     * @return array|mixed
     */
    protected function getUserSmsByDay($day = 0, $orderTime = 0)
    {
        $key = $day;
        if(isset($this->userSms[$key])){
            return $this->userSms[$key];
        }else{
            if($this->data->order->is_export == UserLoanOrder::IS_EXPORT_YES) {
                $userId = $this->getUserOtherId();
                if($day == 0){
                    $sms = MgUserMobileSms::find()
                        ->select(['messageContent','messageDate'])
                        ->where(['user_id' => $userId, 'type' => 1])
                        ->asArray()
                        ->all(Yii::$app->mongodb_loan);
                }else{
                    $begin_time = $orderTime - $day * 86400;
                    $sms = MgUserMobileSms::find()
                        ->select(['messageContent','messageDate'])
                        ->where(['user_id' => $userId, 'type' => 1])
                        ->andWhere(['>=', 'messageDate',$begin_time])
                        ->asArray()
                        ->all(Yii::$app->mongodb_loan);
                }
            }else{
                if($day == 0){
                    $sms = MgUserMobileSms::find()
                        ->select(['messageContent','messageDate'])
                        ->where(['user_id' => intval($this->data->order->user_id), 'type' => 1])
                        ->asArray()
                        ->all();
                }else{
                    $begin_time = $orderTime - $day * 86400;
                    $sms = MgUserMobileSms::find()
                        ->select(['messageContent','messageDate'])
                        ->where(['user_id' => intval($this->data->order->user_id), 'type' => 1])
                        ->andWhere(['>=', 'messageDate',$begin_time])
                        ->asArray()
                        ->all();
                }
            }

            $data = [];
            foreach ($sms as $values){
                $date = date('Y-m-d', $values['messageDate']);
                $data[$date][] = $values['messageContent'];
            }

            $arr = [];
            foreach ($data as $value){
                $arr = array_merge($arr,array_unique($value));
            }
            return $this->userSms[$key] = $arr;
        }
    }

    /**
     * @param $sms
     * @return bool
     */
    private function loanApplicationTrial($sms){
        if(stripos($sms, 'loan') === false && stripos($sms, 'application') === false){
            return false;
        }

        if(stripos($sms, 'OTP') !== false
            || stripos($sms, 'pending') !== false
            || stripos($sms, 'incomplete') !== false
            || stripos($sms, 'complete') !== false
            || stripos($sms, 'continue') !== false
            || stripos($sms, 'in process') !== false
            || stripos($sms, 'cancelled') !== false
            || stripos($sms, 'has been forwarded') !== false
        ){
            return true;
        }
        return false;
    }

    /**
     * 历史尝试申请贷款的短信数量
     * @return int
     */
    public function checkHistSMSCntOfLoanApplicationTrial(){
        $data = $this->getUserSmsByDay();
        $count = 0;
        foreach ($data as $v){
            if($this->loanApplicationTrial($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 近7天内尝试申请贷款的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanApplicationTrialLast7Days(){
        $data = $this->getUserSmsByDay(7, $this->data->order->order_time);
        $count = 0;
        foreach ($data as $v){
            if($this->loanApplicationTrial($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 近30天内尝试申请贷款的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanApplicationTrialLast30Days(){
        $data = $this->getUserSmsByDay(30, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanApplicationTrial($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 近60天内尝试申请贷款的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanApplicationTrialLast60Days(){
        $data = $this->getUserSmsByDay(60, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanApplicationTrial($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 近90天内尝试申请贷款的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanApplicationTrialLast90Days(){
        $data = $this->getUserSmsByDay(90, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanApplicationTrial($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * @param $sms
     * @return bool
     */
    private function loanApplicationSubmission($sms){
        if(stripos($sms, 'loan') === false && stripos($sms, 'application') === false){
            return false;
        }

        if(stripos($sms, 'submitted successfully') !== false
            || stripos($sms, 'successfully submitted') !== false
            || stripos($sms, 'has been submitted') !== false
            || stripos($sms, 'successfully completed') !== false
            || stripos($sms, 'has been updated') !== false
            || stripos($sms, 'received') !== false
            || stripos($sms, 'successfully applied') !== false
        ){
            return true;
        }
        return false;
    }

    /**
     * 历史申请贷款提交成功的短信数量
     * @return int
     */
    public function checkHistSMSCntOfLoanApplicationSubmission(){
        $data = $this->getUserSmsByDay();

        $count = 0;
        foreach ($data as $v){
            if($this->loanApplicationSubmission($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 近7天内申请贷款提交成功的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanApplicationSubmissionLast7Days(){
        $data = $this->getUserSmsByDay(7, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanApplicationSubmission($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 近30天内申请贷款提交成功的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanApplicationSubmissionLast30Days(){
        $data = $this->getUserSmsByDay(30, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanApplicationSubmission($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 近60天内申请贷款提交成功的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanApplicationSubmissionLast60Days(){
        $data = $this->getUserSmsByDay(60, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanApplicationSubmission($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 近90天内申请贷款提交成功的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanApplicationSubmissionLast90Days(){
        $data = $this->getUserSmsByDay(90, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanApplicationSubmission($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * @param $sms
     * @return bool
     */
    private function loanRejection($sms){
        if(stripos($sms, 'loan') === false && stripos($sms, 'application') === false){
            return false;
        }

        if(stripos($sms, 'not pass') !== false
            || stripos($sms, 'has not passed') !== false
            || stripos($sms, 'rejected') !== false
            || stripos($sms, 'not approved') !== false
            || stripos($sms, 'disapproved') !== false
        ){
            return true;
        }
        return false;
    }

    /**
     * 历史贷款审批拒绝的短信数量
     * @return int
     */
    public function checkHistSMSCntOfLoanRejection(){
        $data = $this->getUserSmsByDay();

        $count = 0;
        foreach ($data as $v){
            if($this->loanRejection($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 近7天内贷款审批拒绝的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanRejectionLast7Days(){
        $data = $this->getUserSmsByDay(7, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanRejection($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 近30天内贷款审批拒绝的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanRejectionLast30Days(){
        $data = $this->getUserSmsByDay(30, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanRejection($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 近60天内贷款审批拒绝的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanRejectionLast60Days(){
        $data = $this->getUserSmsByDay(60, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanRejection($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 近90天内贷款审批拒绝的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanRejectionLast90Days(){
        $data = $this->getUserSmsByDay(90, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanRejection($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * @param $sms
     * @return bool
     */
    private function loanApproval($sms){
        if(stripos($sms, 'loan') === false
            && stripos($sms, 'application') === false
        ){
            return false;
        }

        if(stripos($sms, 'not approved') !== false
        ){
            return false;
        }

        if(stripos($sms, 'approved') !== false){
            return true;
        }
        return false;
    }

    /**
     * 历史贷款审批通过的短信数量
     * @return int
     */
    public function checkHistSMSCntOfLoanApproval(){
        $data = $this->getUserSmsByDay();

        $count = 0;
        foreach ($data as $v){
            if($this->loanApproval($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 近7天内贷款审批通过的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanApprovalLast7Days(){
        $data = $this->getUserSmsByDay(7, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanApproval($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 近30天内贷款审批通过的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanApprovalLast30Days(){
        $data = $this->getUserSmsByDay(30, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanApproval($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 近60天内贷款审批通过的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanApprovalLast60Days(){
        $data = $this->getUserSmsByDay(60, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanApproval($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 近90天内贷款审批通过的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanApprovalLast90Days(){
        $data = $this->getUserSmsByDay(90, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanApproval($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * @param $sms
     * @return bool
     */
    private function loanDisbursal($sms){
        if(stripos($sms, 'loan') === false && stripos($sms, 'application') === false){
            return false;
        }

        if(stripos($sms, 'has been released') !== false
            || stripos($sms, 'has been disbursed') !== false
            || stripos($sms, 'confirm disbursement') !== false
            || stripos($sms, 'disbursement has been credited') !== false
            || stripos($sms, 'has been approved and disbursed') !== false
            || (stripos($sms, 'disbursement') !== false && (stripos($sms, 'initiated to') !== false || stripos($sms, 'success') !== false))
        ){
            return true;
        }
        return false;
    }

    /**
     * 历史放款成功的短信数量
     * @return int
     */
    public function checkHistSMSCntOfLoanDisbursal(){
        $data = $this->getUserSmsByDay();

        $count = 0;
        foreach ($data as $v){
            if($this->loanDisbursal($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 近7天内放款成功的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanDisbursalLast7Days(){
        $data = $this->getUserSmsByDay(7, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanDisbursal($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 近30天内放款成功的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanDisbursalLast30Days(){
        $data = $this->getUserSmsByDay(30, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanDisbursal($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 近60天内放款成功的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanDisbursalLast60Days(){
        $data = $this->getUserSmsByDay(60, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanDisbursal($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 近90天内放款成功的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanDisbursalLast90Days(){
        $data = $this->getUserSmsByDay(90, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanDisbursal($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * @param $sms
     * @return bool
     */
    private function loanDueRemind($sms){
        if(stripos($sms, 'loan') === false){
            return false;
        }

        if(stripos($sms, 'disburse') !== false
            || stripos($sms, 'received') !== false){
            return false;
        }

        if(stripos($sms, 'due') !== false
            || stripos($sms, 'payment') !== false
            || stripos($sms, "haven\'t paid off") !== false
        ){
            return true;
        }
        return false;
    }

    /**
     * 历史到期前提醒还款的短信数量
     * @return int
     */
    public function checkHistSMSCntOfLoanDueRemind(){
        $data = $this->getUserSmsByDay();

        $count = 0;
        foreach ($data as $v){
            if($this->loanDueRemind($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 近7天内到期前提醒还款的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanDueRemindLast7Days(){
        $data = $this->getUserSmsByDay(7, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanDueRemind($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 近30天内到期前提醒还款的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanDueRemindLast30Days(){
        $data = $this->getUserSmsByDay(30, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanDueRemind($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 近60天内到期前提醒还款的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanDueRemindLast60Days(){
        $data = $this->getUserSmsByDay(60, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanDueRemind($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 近90天内到期前提醒还款的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanDueRemindLast90Days(){
        $data = $this->getUserSmsByDay(90, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanDueRemind($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * @param $sms
     * @return bool
     */
    private function loanPayOff($sms){
        if(stripos($sms, 'loan') === false && stripos($sms, 'bill') === false){
            return false;
        }

        if(stripos($sms, 'has been paid off') !== false
            || stripos($sms, 'is paid off') !== false
        ){
            return true;
        }
        return false;
    }

    /**
     * 历史所有还款成功的短信数量
     * @return int
     */
    public function checkHistSMSCntOfLoanPayOff(){
        $data = $this->getUserSmsByDay();

        $count = 0;
        foreach ($data as $v){
            if($this->loanPayOff($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 近7天内所有还款成功的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanPayOffLast7Days(){
        $data = $this->getUserSmsByDay(7, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanPayOff($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 近30天内所有还款成功的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanPayOffLast30Days(){
        $data = $this->getUserSmsByDay(30, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanPayOff($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 近60天内所有还款成功的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanPayOffLast60Days(){
        $data = $this->getUserSmsByDay(60, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanPayOff($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 近90天内所有还款成功的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanPayOffLast90Days(){
        $data = $this->getUserSmsByDay(90, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanPayOff($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * @param $sms
     * @return bool
     */
    private function overdueRemind($sms){
        if(stripos($sms, 'is overdue') !== false
            || stripos($sms, 'has been overdue') !== false
            || stripos($sms, 'was due') !== false
            || stripos($sms, 'overdue days') !== false
            || stripos($sms, 'payment overdue') !== false
            || stripos($sms, 'is still due') !== false
        ){
            return true;
        }
        return false;
    }

    /**
     * 历史所有逾期提醒还款的短信数量
     * @return int
     */
    public function checkHistSMSCntOfOverdueRemind(){
        $data = $this->getUserSmsByDay();

        $count = 0;
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 近7天内逾期提醒还款的短信数量
     * @return int
     */
    public function checkSMSCntOfOverdueRemindLast7Days(){
        $data = $this->getUserSmsByDay(7, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 近30天内逾期提醒还款的短信数量
     * @return int
     */
    public function checkSMSCntOfOverdueRemindLast30Days(){
        $data = $this->getUserSmsByDay(30, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 近60天内逾期提醒还款的短信数量
     * @return int
     */
    public function checkSMSCntOfOverdueRemindLast60Days(){
        $data = $this->getUserSmsByDay(60, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 近90天内逾期提醒还款的短信数量
     * @return int
     */
    public function checkSMSCntOfOverdueRemindLast90Days(){
        $data = $this->getUserSmsByDay(90, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $count++;
            }
        }

        return $count;
    }

    private function amountPreg($sms){
        $data = [];
        $sms = strtoupper(str_replace([' ', ',', '(', ')'], '', $sms));
        if(preg_match_all('/(RS|RS\.|INR|INR\.|RP|AMOUNT|UPTO|RUPEE|RUPEES)(\d+)/', $sms, $matches)){
            foreach ($matches[2] as $money){
                if(strlen($money) < 3){
                    continue;
                }

                if(strlen($money) > 6){
                    $money = substr($money, 0, 6);
                }

                $data[] = intval($money);
            }
        }

        if(preg_match_all('/(\d+|\d+\.\d{2})(RUPEE|RUPEES)/', $sms, $matches)){
            foreach ($matches[1] as $money){
                $money = intval($money);
                if(strlen($money) < 3){
                    continue;
                }

                if(strlen($money) > 6){
                    $money = substr($money, 0, 6);
                }

                $data[] = $money;
            }
        }

        return $data;
    }

    /**
     * 历史短信中贷款授信额度合计
     * @return int
     */
    public function checkHistSumOfSMSLoanCreditAmt(){
        $data = $this->getUserSmsByDay();

        $money = 0;
        foreach ($data as $v){
            if($this->loanApproval($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 历史短信中贷款授信额度最大值
     * @return int
     */
    public function checkHistMaxOfSMSLoanCreditAmt(){
        $data = $this->getUserSmsByDay();

        $money = [];
        foreach ($data as $v){
            if($this->loanApproval($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 历史短信中贷款授信额度最小值
     * @return int
     */
    public function checkHistMinOfSMSLoanCreditAmt(){
        $data = $this->getUserSmsByDay();

        $money = [];
        foreach ($data as $v){
            if($this->loanApproval($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return min($money);
    }

    /**
     * 历史短信中贷款授信额度平均值
     * @return int
     */
    public function checkHistAvgOfSMSLoanCreditAmt(){
        $data = $this->getUserSmsByDay();

        $money = [];
        foreach ($data as $v){
            if($this->loanApproval($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 近7天内短信中贷款授信额度合计
     * @return int
     */
    public function checkSumOfSMSLoanCreditAmtLast7Days(){
        $data = $this->getUserSmsByDay(7, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->loanApproval($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 近7天内短信中贷款授信额度最大值
     * @return int
     */
    public function checkMaxOfSMSLoanCreditAmtLast7Days(){
        $data = $this->getUserSmsByDay(7, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanApproval($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 近7天内短信中贷款授信额度最小值
     * @return int
     */
    public function checkMinOfSMSLoanCreditAmtLast7Days(){
        $data = $this->getUserSmsByDay(7, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanApproval($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return min($money);
    }

    /**
     * 近7天内短信中贷款授信额平均值
     * @return int
     */
    public function checkAvgOfSMSLoanCreditAmtLast7Days(){
        $data = $this->getUserSmsByDay(7, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanApproval($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 近30天内短信中贷款授信额度合计
     * @return int
     */
    public function checkSumOfSMSLoanCreditAmtLast30Days(){
        $data = $this->getUserSmsByDay(30, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->loanApproval($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 近30天内短信中贷款授信额度最大值
     * @return int
     */
    public function checkMaxOfSMSLoanCreditAmtLast30Days(){
        $data = $this->getUserSmsByDay(30, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanApproval($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 近30天内短信中贷款授信额度最小值
     * @return int
     */
    public function checkMinOfSMSLoanCreditAmtLast30Days(){
        $data = $this->getUserSmsByDay(30, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanApproval($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return min($money);
    }

    /**
     * 近30天内短信中贷款授信额平均值
     * @return int
     */
    public function checkAvgOfSMSLoanCreditAmtLast30Days(){
        $data = $this->getUserSmsByDay(30, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanApproval($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 近60天内短信中贷款授信额度合计
     * @return int
     */
    public function checkSumOfSMSLoanCreditAmtLast60Days(){
        $data = $this->getUserSmsByDay(60, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->loanApproval($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 近60天内短信中贷款授信额度最大值
     * @return int
     */
    public function checkMaxOfSMSLoanCreditAmtLast60Days(){
        $data = $this->getUserSmsByDay(60, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanApproval($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 近60天内短信中贷款授信额度最小值
     * @return int
     */
    public function checkMinOfSMSLoanCreditAmtLast60Days(){
        $data = $this->getUserSmsByDay(60, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanApproval($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return min($money);
    }

    /**
     * 近60天内短信中贷款授信额平均值
     * @return int
     */
    public function checkAvgOfSMSLoanCreditAmtLast60Days(){
        $data = $this->getUserSmsByDay(60, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanApproval($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 近90天内短信中贷款授信额度合计
     * @return int
     */
    public function checkSumOfSMSLoanCreditAmtLast90Days(){
        $data = $this->getUserSmsByDay(90, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->loanApproval($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 近90天内短信中贷款授信额度最大值
     * @return int
     */
    public function checkMaxOfSMSLoanCreditAmtLast90Days(){
        $data = $this->getUserSmsByDay(90, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanApproval($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 近90天内短信中贷款授信额度最小值
     * @return int
     */
    public function checkMinOfSMSLoanCreditAmtLast90Days(){
        $data = $this->getUserSmsByDay(90, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanApproval($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return min($money);
    }

    /**
     * 近90天内短信中贷款授信额平均值
     * @return int
     */
    public function checkAvgOfSMSLoanCreditAmtLast90Days(){
        $data = $this->getUserSmsByDay(90, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanApproval($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 历史短信中贷款放款金额合计
     * @return int
     */
    public function checkHistSumOfSMSLoanDisburseAmt(){
        $data = $this->getUserSmsByDay();

        $money = 0;
        foreach ($data as $v){
            if($this->loanDisbursal($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 历史短信中贷款放款金额最大值
     * @return int
     */
    public function checkHistMaxOfSMSLoanDisburseAmt(){
        $data = $this->getUserSmsByDay();

        $money = [];
        foreach ($data as $v){
            if($this->loanDisbursal($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 历史短信中贷款放款金额最小值
     * @return int
     */
    public function checkHistMinOfSMSLoanDisburseAmt(){
        $data = $this->getUserSmsByDay();

        $money = [];
        foreach ($data as $v){
            if($this->loanDisbursal($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return min($money);
    }

    /**
     * 历史短信中贷款放款金额平均值
     * @return int
     */
    public function checkHistAvgOfSMSLoanDisburseAmt(){
        $data = $this->getUserSmsByDay();

        $money = [];
        foreach ($data as $v){
            if($this->loanDisbursal($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 近7天内短信中贷款放款金额合计
     * @return int
     */
    public function checkSumOfSMSLoanDisburseAmtLast7Days(){
        $data = $this->getUserSmsByDay(7, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->loanDisbursal($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 近7天内短信中贷款放款金额最大值
     * @return int
     */
    public function checkMaxOfSMSLoanDisburseAmtLast7Days(){
        $data = $this->getUserSmsByDay(7, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanDisbursal($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 近7天内短信中贷款放款金额最小值
     * @return int
     */
    public function checkMinOfSMSLoanDisburseAmtLast7Days(){
        $data = $this->getUserSmsByDay(7, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanDisbursal($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return min($money);
    }

    /**
     * 近7天内短信中贷款放款金额平均值
     * @return int
     */
    public function checkAvgOfSMSLoanDisburseAmtLast7Days(){
        $data = $this->getUserSmsByDay(7, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanDisbursal($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 近30天内短信中贷款放款金额合计
     * @return int
     */
    public function checkSumOfSMSLoanDisburseAmtLast30Days(){
        $data = $this->getUserSmsByDay(30, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->loanDisbursal($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 近30天内短信中贷款放款金额最大值
     * @return int
     */
    public function checkMaxOfSMSLoanDisburseAmtLast30Days(){
        $data = $this->getUserSmsByDay(30, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanDisbursal($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 近30天内短信中贷款放款金额最小值
     * @return int
     */
    public function checkMinOfSMSLoanDisburseAmtLast30Days(){
        $data = $this->getUserSmsByDay(30, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanDisbursal($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return min($money);
    }

    /**
     * 近30天内短信中贷款放款金额平均值
     * @return int
     */
    public function checkAvgOfSMSLoanDisburseAmtLast30Days(){
        $data = $this->getUserSmsByDay(30, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanDisbursal($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 近60天内短信中贷款放款金额合计
     * @return int
     */
    public function checkSumOfSMSLoanDisburseAmtLast60Days(){
        $data = $this->getUserSmsByDay(60, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->loanDisbursal($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 近60天内短信中贷款放款金额最大值
     * @return int
     */
    public function checkMaxOfSMSLoanDisburseAmtLast60Days(){
        $data = $this->getUserSmsByDay(60, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanDisbursal($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 近60天内短信中贷款放款金额最小值
     * @return int
     */
    public function checkMinOfSMSLoanDisburseAmtLast60Days(){
        $data = $this->getUserSmsByDay(60, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanDisbursal($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return min($money);
    }

    /**
     * 近60天内短信中贷款放款金额平均值
     * @return int
     */
    public function checkAvgOfSMSLoanDisburseAmtLast60Days(){
        $data = $this->getUserSmsByDay(60, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanDisbursal($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 近90天内短信中贷款放款金额合计
     * @return int
     */
    public function checkSumOfSMSLoanDisburseAmtLast90Days(){
        $data = $this->getUserSmsByDay(90, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->loanDisbursal($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 近90天内短信中贷款放款金额最大值
     * @return int
     */
    public function checkMaxOfSMSLoanDisburseAmtLast90Days(){
        $data = $this->getUserSmsByDay(90, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanDisbursal($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 近90天内短信中贷款放款金额最小值
     * @return int
     */
    public function checkMinOfSMSLoanDisburseAmtLast90Days(){
        $data = $this->getUserSmsByDay(90, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanDisbursal($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return min($money);
    }

    /**
     * 近90天内短信中贷款放款金额平均值
     * @return int
     */
    public function checkAvgOfSMSLoanDisburseAmtLast90Days(){
        $data = $this->getUserSmsByDay(90, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanDisbursal($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * @param $sms
     * @return bool
     */
    private function smsEmi($sms){
        if(stripos($sms, 'loan') === false
            && stripos($sms, 'application') === false
        ){
            return false;
        }

        if(stripos($sms, 'apply') !== false
        ){
            return false;
        }

        if(stripos($sms, 'emi') !== false){
            return true;
        }
        return false;
    }

    /**
     * 历史短信每月还款金额之和
     * @return int
     */
    public function checkSumOfHistSMSEMIAmt(){
        $data = $this->getUserSmsByDay();

        $money = 0;
        foreach ($data as $v){
            if($this->smsEmi($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money += min($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 历史短信每月还款金额的最大值
     * @return int
     */
    public function checkMaxOfHistSMSEMIAmt(){
        $data = $this->getUserSmsByDay();

        $money = [];
        foreach ($data as $v){
            if($this->smsEmi($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = min($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 历史短信每月还款金额的最小值
     * @return int
     */
    public function checkMinOfHistSMSEMIAmt(){
        $data = $this->getUserSmsByDay();

        $money = [];
        foreach ($data as $v){
            if($this->smsEmi($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = min($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return min($money);
    }

    /**
     * 历史短信每月还款金额的平均值
     * @return int
     */
    public function checkAvgOfHistSMSEMIAmt(){
        $data = $this->getUserSmsByDay();

        $money = [];
        foreach ($data as $v){
            if($this->smsEmi($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = min($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 近7天内短信每月还款金额之和
     * @return int
     */
    public function checkSumOfSMSEMIAmtLast7Days(){
        $data = $this->getUserSmsByDay(7, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->smsEmi($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money += min($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 近7天内短信每月还款金额的最大值
     * @return int
     */
    public function checkMaxOfSMSEMIAmtLast7Days(){
        $data = $this->getUserSmsByDay(7, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->smsEmi($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = min($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 近7天内短信每月还款金额的最小值
     * @return int
     */
    public function checkMinOfSMSEMIAmtLast7Days(){
        $data = $this->getUserSmsByDay(7, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->smsEmi($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = min($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return min($money);
    }

    /**
     * 近7天内短信每月还款金额的平均值
     * @return int
     */
    public function checkAvgOfSMSEMIAmtLast7Days(){
        $data = $this->getUserSmsByDay(7, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->smsEmi($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = min($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 近30天内短信每月还款金额之和
     * @return int
     */
    public function checkSumOfSMSEMIAmtLast30Days(){
        $data = $this->getUserSmsByDay(30, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->smsEmi($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money += min($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 近30天内短信每月还款金额的最大值
     * @return int
     */
    public function checkMaxOfSMSEMIAmtLast30Days(){
        $data = $this->getUserSmsByDay(30, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->smsEmi($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = min($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 近30天内短信每月还款金额的最小值
     * @return int
     */
    public function checkMinOfSMSEMIAmtLast30Days(){
        $data = $this->getUserSmsByDay(30, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->smsEmi($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = min($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return min($money);
    }

    /**
     * 近30天内短信每月还款金额的平均值
     * @return int
     */
    public function checkAvgOfSMSEMIAmtLast30Days(){
        $data = $this->getUserSmsByDay(30, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->smsEmi($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = min($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 近60天内短信每月还款金额之和
     * @return int
     */
    public function checkSumOfSMSEMIAmtLast60Days(){
        $data = $this->getUserSmsByDay(60, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->smsEmi($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money += min($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 近60天内短信每月还款金额的最大值
     * @return int
     */
    public function checkMaxOfSMSEMIAmtLast60Days(){
        $data = $this->getUserSmsByDay(60, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->smsEmi($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = min($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 近60天内短信每月还款金额的最小值
     * @return int
     */
    public function checkMinOfSMSEMIAmtLast60Days(){
        $data = $this->getUserSmsByDay(60, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->smsEmi($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = min($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return min($money);
    }

    /**
     * 近60天内短信每月还款金额的平均值
     * @return int
     */
    public function checkAvgOfSMSEMIAmtLast60Days(){
        $data = $this->getUserSmsByDay(60, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->smsEmi($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = min($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 近90天内短信每月还款金额之和
     * @return int
     */
    public function checkSumOfSMSEMIAmtLast90Days(){
        $data = $this->getUserSmsByDay(90, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->smsEmi($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money += min($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 近90天内短信每月还款金额的最大值
     * @return int
     */
    public function checkMaxOfSMSEMIAmtLast90Days(){
        $data = $this->getUserSmsByDay(90, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->smsEmi($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = min($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 近90天内短信每月还款金额的最小值
     * @return int
     */
    public function checkMinOfSMSEMIAmtLast90Days(){
        $data = $this->getUserSmsByDay(90, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->smsEmi($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = min($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return min($money);
    }

    /**
     * 近90天内短信每月还款金额的平均值
     * @return int
     */
    public function checkAvgOfSMSEMIAmtLast90Days(){
        $data = $this->getUserSmsByDay(90, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->smsEmi($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = min($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 历史到期前提醒还款的贷款金额之和
     * @return int
     */
    public function checkSumOfHistSMSDueRemindLoanAmt(){
        $data = $this->getUserSmsByDay();

        $money = 0;
        foreach ($data as $v){
            if($this->loanDueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 历史到期前提醒还款的贷款金额的最大值
     * @return int
     */
    public function checkMaxOfHistSMSDueRemindLoanAmt(){
        $data = $this->getUserSmsByDay();

        $money = [];
        foreach ($data as $v){
            if($this->loanDueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 历史到期前提醒还款的贷款金额的最小值
     * @return int
     */
    public function checkMinOfHistSMSDueRemindLoanAmt(){
        $data = $this->getUserSmsByDay();

        $money = [];
        foreach ($data as $v){
            if($this->loanDueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return min($money);
    }

    /**
     * 历史到期前提醒还款的贷款金额的平均值
     * @return int
     */
    public function checkAvgOfHistSMSDueRemindLoanAmt(){
        $data = $this->getUserSmsByDay();

        $money = [];
        foreach ($data as $v){
            if($this->loanDueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 近7天内到期前提醒还款的贷款金额之和
     * @return int
     */
    public function checkSumOfSMSDueRemindLoanAmtLast7Days(){
        $data = $this->getUserSmsByDay(7, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->loanDueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 近7天内到期前提醒还款的贷款金额的最大值
     * @return int
     */
    public function checkMaxOfSMSDueRemindLoanAmtLast7Days(){
        $data = $this->getUserSmsByDay(7, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanDueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 近7天内到期前提醒还款的贷款金额的最小值
     * @return int
     */
    public function checkMinOfSMSDueRemindLoanAmtLast7Days(){
        $data = $this->getUserSmsByDay(7, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanDueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return min($money);
    }

    /**
     * 近7天内到期前提醒还款的贷款金额的平均值
     * @return int
     */
    public function checkAvgOfSMSDueRemindLoanAmtLast7Days(){
        $data = $this->getUserSmsByDay(7, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanDueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 近30天内到期前提醒还款的贷款金额之和
     * @return int
     */
    public function checkSumOfSMSDueRemindLoanAmtLast30Days(){
        $data = $this->getUserSmsByDay(30, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->loanDueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 近30天内到期前提醒还款的贷款金额的最大值
     * @return int
     */
    public function checkMaxOfSMSDueRemindLoanAmtLast30Days(){
        $data = $this->getUserSmsByDay(30, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanDueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 近30天内到期前提醒还款的贷款金额的最小值
     * @return int
     */
    public function checkMinOfSMSDueRemindLoanAmtLast30Days(){
        $data = $this->getUserSmsByDay(30, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanDueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return min($money);
    }

    /**
     * 近30天内到期前提醒还款的贷款金额的平均值
     * @return int
     */
    public function checkAvgOfSMSDueRemindLoanAmtLast30Days(){
        $data = $this->getUserSmsByDay(30, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanDueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 近60天内到期前提醒还款的贷款金额之和
     * @return int
     */
    public function checkSumOfSMSDueRemindLoanAmtLast60Days(){
        $data = $this->getUserSmsByDay(60, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->loanDueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 近60天内到期前提醒还款的贷款金额的最大值
     * @return int
     */
    public function checkMaxOfSMSDueRemindLoanAmtLast60Days(){
        $data = $this->getUserSmsByDay(60, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanDueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 近60天内到期前提醒还款的贷款金额的最小值
     * @return int
     */
    public function checkMinOfSMSDueRemindLoanAmtLast60Days(){
        $data = $this->getUserSmsByDay(60, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanDueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return min($money);
    }

    /**
     * 近60天内到期前提醒还款的贷款金额的平均值
     * @return int
     */
    public function checkAvgOfSMSDueRemindLoanAmtLast60Days(){
        $data = $this->getUserSmsByDay(60, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanDueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 近90天内到期前提醒还款的贷款金额之和
     * @return int
     */
    public function checkSumOfSMSDueRemindLoanAmtLast90Days(){
        $data = $this->getUserSmsByDay(90, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->loanDueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 近90天内到期前提醒还款的贷款金额的最大值
     * @return int
     */
    public function checkMaxOfSMSDueRemindLoanAmtLast90Days(){
        $data = $this->getUserSmsByDay(90, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanDueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 近90天内到期前提醒还款的贷款金额的最小值
     * @return int
     */
    public function checkMinOfSMSDueRemindLoanAmtLast90Days(){
        $data = $this->getUserSmsByDay(90, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanDueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return min($money);
    }

    /**
     * 近90天内到期前提醒还款的贷款金额的平均值
     * @return int
     */
    public function checkAvgOfSMSDueRemindLoanAmtLast90Days(){
        $data = $this->getUserSmsByDay(90, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanDueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    private function overdueDayPreg($sms){
        $data = [];
        $sms = strtoupper(str_replace([' ',], '', $sms));

        if(preg_match_all('/(\-\d+|\d+)DAY/', $sms, $matches)){
            foreach ($matches[1] as $day){
                if($day <= 0){
                    continue;
                }

                $data[] = intval($day);
            }
        }

        return $data;
    }

    /**
     * 历史短信中的逾期天数之和
     * @return int
     */
    public function checkHistSumOfSMSLoanOverdueDays(){
        $data = $this->getUserSmsByDay();

        $overdueDay = 0;
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $days = $this->overdueDayPreg($v);
                if(!empty($days)){
                    $overdueDay += max($days);
                }
            }
        }

        if(empty($overdueDay)){
            return -1;
        }

        return $overdueDay;
    }

    /**
     * 历史短信中的逾期天数最大值
     * @return int
     */
    public function checkHistMaxOfSMSLoanOverdueDays(){
        $data = $this->getUserSmsByDay();

        $overdueDay = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $days = $this->overdueDayPreg($v);
                if(!empty($days)){
                    $overdueDay[] = max($days);
                }
            }
        }

        if(empty($overdueDay)){
            return -1;
        }

        return max($overdueDay);
    }

    /**
     * 历史短信中的逾期天数最小值
     * @return int
     */
    public function checkHistMinOfSMSLoanOverdueDays(){
        $data = $this->getUserSmsByDay();

        $overdueDay = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $days = $this->overdueDayPreg($v);
                if(!empty($days)){
                    $overdueDay[] = max($days);
                }
            }
        }

        if(empty($overdueDay)){
            return -1;
        }

        return min($overdueDay);
    }

    /**
     * 历史短信中的逾期天数平均值
     * @return int
     */
    public function checkHistAvgOfSMSLoanOverdueDays(){
        $data = $this->getUserSmsByDay();

        $overdueDay = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $days = $this->overdueDayPreg($v);
                if(!empty($days)){
                    $overdueDay[] = max($days);
                }
            }
        }

        if(empty($overdueDay)){
            return -1;
        }

        return round(array_sum($overdueDay) / count($overdueDay));
    }

    /**
     * 近7天内短信中的逾期天数之和
     * @return int
     */
    public function checkSumOfSMSLoanOverdueDaysLast7Days(){
        $data = $this->getUserSmsByDay(7, $this->data->order->order_time);

        $overdueDay = 0;
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $days = $this->overdueDayPreg($v);
                if(!empty($days)){
                    $overdueDay += max($days);
                }
            }
        }

        if(empty($overdueDay)){
            return -1;
        }

        return $overdueDay;
    }

    /**
     * 近7天内短信中的逾期天数最大值
     * @return int
     */
    public function checkMaxOfSMSLoanOverdueDaysLast7Days(){
        $data = $this->getUserSmsByDay(7, $this->data->order->order_time);

        $overdueDay = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $days = $this->overdueDayPreg($v);
                if(!empty($days)){
                    $overdueDay[] = max($days);
                }
            }
        }

        if(empty($overdueDay)){
            return -1;
        }

        return max($overdueDay);
    }

    /**
     * 近7天内短信中的逾期天数最小值
     * @return int
     */
    public function checkMinOfSMSLoanOverdueDaysLast7Days(){
        $data = $this->getUserSmsByDay(7, $this->data->order->order_time);

        $overdueDay = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $days = $this->overdueDayPreg($v);
                if(!empty($days)){
                    $overdueDay[] = max($days);
                }
            }
        }

        if(empty($overdueDay)){
            return -1;
        }

        return min($overdueDay);
    }

    /**
     * 近7天内短信中的逾期天数平均值
     * @return int
     */
    public function checkAvgOfSMSLoanOverdueDaysLast7Days(){
        $data = $this->getUserSmsByDay(7, $this->data->order->order_time);

        $overdueDay = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $days = $this->overdueDayPreg($v);
                if(!empty($days)){
                    $overdueDay[] = max($days);
                }
            }
        }

        if(empty($overdueDay)){
            return -1;
        }

        return round(array_sum($overdueDay) / count($overdueDay));
    }

    /**
     * 近30天内短信中的逾期天数之和
     * @return int
     */
    public function checkSumOfSMSLoanOverdueDaysLast30Days(){
        $data = $this->getUserSmsByDay(30, $this->data->order->order_time);

        $overdueDay = 0;
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $days = $this->overdueDayPreg($v);
                if(!empty($days)){
                    $overdueDay += max($days);
                }
            }
        }

        if(empty($overdueDay)){
            return -1;
        }

        return $overdueDay;
    }

    /**
     * 近30天内短信中的逾期天数最大值
     * @return int
     */
    public function checkMaxOfSMSLoanOverdueDaysLast30Days(){
        $data = $this->getUserSmsByDay(30, $this->data->order->order_time);

        $overdueDay = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $days = $this->overdueDayPreg($v);
                if(!empty($days)){
                    $overdueDay[] = max($days);
                }
            }
        }

        if(empty($overdueDay)){
            return -1;
        }

        return max($overdueDay);
    }

    /**
     * 近30天内短信中的逾期天数最小值
     * @return int
     */
    public function checkMinOfSMSLoanOverdueDaysLast30Days(){
        $data = $this->getUserSmsByDay(30, $this->data->order->order_time);

        $overdueDay = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $days = $this->overdueDayPreg($v);
                if(!empty($days)){
                    $overdueDay[] = max($days);
                }
            }
        }

        if(empty($overdueDay)){
            return -1;
        }

        return min($overdueDay);
    }

    /**
     * 近30天内短信中的逾期天数平均值
     * @return int
     */
    public function checkAvgOfSMSLoanOverdueDaysLast30Days(){
        $data = $this->getUserSmsByDay(30, $this->data->order->order_time);

        $overdueDay = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $days = $this->overdueDayPreg($v);
                if(!empty($days)){
                    $overdueDay[] = max($days);
                }
            }
        }

        if(empty($overdueDay)){
            return -1;
        }

        return round(array_sum($overdueDay) / count($overdueDay));
    }

    /**
     * 近60天内短信中的逾期天数之和
     * @return int
     */
    public function checkSumOfSMSLoanOverdueDaysLast60Days(){
        $data = $this->getUserSmsByDay(60, $this->data->order->order_time);

        $overdueDay = 0;
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $days = $this->overdueDayPreg($v);
                if(!empty($days)){
                    $overdueDay += max($days);
                }
            }
        }

        if(empty($overdueDay)){
            return -1;
        }

        return $overdueDay;
    }

    /**
     * 近60天内短信中的逾期天数最大值
     * @return int
     */
    public function checkMaxOfSMSLoanOverdueDaysLast60Days(){
        $data = $this->getUserSmsByDay(60, $this->data->order->order_time);

        $overdueDay = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $days = $this->overdueDayPreg($v);
                if(!empty($days)){
                    $overdueDay[] = max($days);
                }
            }
        }

        if(empty($overdueDay)){
            return -1;
        }

        return max($overdueDay);
    }

    /**
     * 近60天内短信中的逾期天数最小值
     * @return int
     */
    public function checkMinOfSMSLoanOverdueDaysLast60Days(){
        $data = $this->getUserSmsByDay(60, $this->data->order->order_time);

        $overdueDay = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $days = $this->overdueDayPreg($v);
                if(!empty($days)){
                    $overdueDay[] = max($days);
                }
            }
        }

        if(empty($overdueDay)){
            return -1;
        }

        return min($overdueDay);
    }

    /**
     * 近60天内短信中的逾期天数平均值
     * @return int
     */
    public function checkAvgOfSMSLoanOverdueDaysLast60Days(){
        $data = $this->getUserSmsByDay(60, $this->data->order->order_time);

        $overdueDay = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $days = $this->overdueDayPreg($v);
                if(!empty($days)){
                    $overdueDay[] = max($days);
                }
            }
        }

        if(empty($overdueDay)){
            return -1;
        }

        return round(array_sum($overdueDay) / count($overdueDay));
    }

    /**
     * 近90天内短信中的逾期天数之和
     * @return int
     */
    public function checkSumOfSMSLoanOverdueDaysLast90Days(){
        $data = $this->getUserSmsByDay(90, $this->data->order->order_time);

        $overdueDay = 0;
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $days = $this->overdueDayPreg($v);
                if(!empty($days)){
                    $overdueDay += max($days);
                }
            }
        }

        if(empty($overdueDay)){
            return -1;
        }

        return $overdueDay;
    }

    /**
     * 近90天内短信中的逾期天数最大值
     * @return int
     */
    public function checkMaxOfSMSLoanOverdueDaysLast90Days(){
        $data = $this->getUserSmsByDay(90, $this->data->order->order_time);

        $overdueDay = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $days = $this->overdueDayPreg($v);
                if(!empty($days)){
                    $overdueDay[] = max($days);
                }
            }
        }

        if(empty($overdueDay)){
            return -1;
        }

        return max($overdueDay);
    }

    /**
     * 近90天内短信中的逾期天数最小值
     * @return int
     */
    public function checkMinOfSMSLoanOverdueDaysLast90Days(){
        $data = $this->getUserSmsByDay(90, $this->data->order->order_time);

        $overdueDay = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $days = $this->overdueDayPreg($v);
                if(!empty($days)){
                    $overdueDay[] = max($days);
                }
            }
        }

        if(empty($overdueDay)){
            return -1;
        }

        return min($overdueDay);
    }

    /**
     * 近90天内短信中的逾期天数平均值
     * @return int
     */
    public function checkAvgOfSMSLoanOverdueDaysLast90Days(){
        $data = $this->getUserSmsByDay(90, $this->data->order->order_time);

        $overdueDay = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $days = $this->overdueDayPreg($v);
                if(!empty($days)){
                    $overdueDay[] = max($days);
                }
            }
        }

        if(empty($overdueDay)){
            return -1;
        }

        return round(array_sum($overdueDay) / count($overdueDay));
    }

    /**
     * 历史短信中的逾期金额之和
     * @return int
     */
    public function checkHistSumOfSMSLoanOverdueAmt(){
        $data = $this->getUserSmsByDay();

        $money = 0;
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 历史短信中的逾期金额最大值
     * @return int
     */
    public function checkHistMaxOfSMSLoanOverdueAmt(){
        $data = $this->getUserSmsByDay();

        $money = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 历史短信中的逾期金额最小值
     * @return int
     */
    public function checkHistMinOfSMSLoanOverdueAmt(){
        $data = $this->getUserSmsByDay();

        $money = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return min($money);
    }

    /**
     * 历史短信中的逾期金额平均值
     * @return int
     */
    public function checkHistAvgOfSMSLoanOverdueAmt(){
        $data = $this->getUserSmsByDay();

        $money = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 近7天内短信中的逾期金额之和
     * @return int
     */
    public function checkSumOfSMSLoanOverdueAmtLast7Days(){
        $data = $this->getUserSmsByDay(7, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 近7天内短信中的逾期金额最大值
     * @return int
     */
    public function checkMaxOfSMSLoanOverdueAmtLast7Days(){
        $data = $this->getUserSmsByDay(7, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 近7天内短信中的逾期金额最小值
     * @return int
     */
    public function checkMinOfSMSLoanOverdueAmtLast7Days(){
        $data = $this->getUserSmsByDay(7, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return min($money);
    }

    /**
     * 近7天内短信中的逾期金额平均值
     * @return int
     */
    public function checkAvgOfSMSLoanOverdueAmtLast7Days(){
        $data = $this->getUserSmsByDay(7, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 近30天内短信中的逾期金额之和
     * @return int
     */
    public function checkSumOfSMSLoanOverdueAmtLast30Days(){
        $data = $this->getUserSmsByDay(30, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 近30天内短信中的逾期金额最大值
     * @return int
     */
    public function checkMaxOfSMSLoanOverdueAmtLast30Days(){
        $data = $this->getUserSmsByDay(30, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 近30天内短信中的逾期金额最小值
     * @return int
     */
    public function checkMinOfSMSLoanOverdueAmtLast30Days(){
        $data = $this->getUserSmsByDay(30, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return min($money);
    }

    /**
     * 近30天内短信中的逾期金额平均值
     * @return int
     */
    public function checkAvgOfSMSLoanOverdueAmtLast30Days(){
        $data = $this->getUserSmsByDay(30, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 近60天内短信中的逾期金额之和
     * @return int
     */
    public function checkSumOfSMSLoanOverdueAmtLast60Days(){
        $data = $this->getUserSmsByDay(60, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 近60天内短信中的逾期金额最大值
     * @return int
     */
    public function checkMaxOfSMSLoanOverdueAmtLast60Days(){
        $data = $this->getUserSmsByDay(60, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 近60天内短信中的逾期金额最小值
     * @return int
     */
    public function checkMinOfSMSLoanOverdueAmtLast60Days(){
        $data = $this->getUserSmsByDay(60, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return min($money);
    }

    /**
     * 近60天内短信中的逾期金额平均值
     * @return int
     */
    public function checkAvgOfSMSLoanOverdueAmtLast60Days(){
        $data = $this->getUserSmsByDay(60, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 近90天内短信中的逾期金额之和
     * @return int
     */
    public function checkSumOfSMSLoanOverdueAmtLast90Days(){
        $data = $this->getUserSmsByDay(90, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 近90天内短信中的逾期金额最大值
     * @return int
     */
    public function checkMaxOfSMSLoanOverdueAmtLast90Days(){
        $data = $this->getUserSmsByDay(90, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 近90天内短信中的逾期金额最小值
     * @return int
     */
    public function checkMinOfSMSLoanOverdueAmtLast90Days(){
        $data = $this->getUserSmsByDay(90, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return min($money);
    }

    /**
     * 近90天内短信中的逾期金额平均值
     * @return int
     */
    public function checkAvgOfSMSLoanOverdueAmtLast90Days(){
        $data = $this->getUserSmsByDay(90, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * @param $sms
     * @return bool
     */
    private function smsSalary($sms){
        if(stripos($sms, 'salary') === false
            || stripos($sms, 'credited to') === false
            || stripos($sms, 'a/c') === false
        ){
            return false;
        }

        return true;
    }

    /**
     * 历史累计收到的发薪短信数量
     * @return int
     */
    public function checkHistSMSCntOfSalary(){
        $data = $this->getUserSmsByDay();

        $count = 0;
        foreach ($data as $v){
            if($this->smsSalary($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 近30天内收到的发薪短信数量
     * @return int
     */
    public function checkSMSCntOfSalaryLast30Days(){
        $data = $this->getUserSmsByDay(30, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->smsSalary($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 近90天内收到的发薪短信数量
     * @return int
     */
    public function checkSMSCntOfSalaryLast90Days(){
        $data = $this->getUserSmsByDay(90, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->smsSalary($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 近180天内收到的发薪短信数量
     * @return int
     */
    public function checkSMSCntOfSalaryLast180Days(){
        $data = $this->getUserSmsByDay(180, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->smsSalary($v)){
                $count++;
            }
        }

        return $count;
    }

    private function salaryPreg($sms){
        $data = [];
        $sms = strtoupper(str_replace([' ', ','], '', $sms));
        if(preg_match_all('/SALARYOFINR(\d+)/', $sms, $matches)){
            foreach ($matches[1] as $money){
                $data[] = intval($money);
            }
        }

        if(preg_match_all('/INR(\d+|\d+\.\d{2})ISCREDITEDTO/', $sms, $matches)){
            foreach ($matches[1] as $money){
                $data[] = intval($money);
            }
        }

        return $data;
    }

    /**
     * 历史累计收到发薪短信中的工资之和
     * @return int
     */
    public function checkSumOfHistSMSSalary(){
        $data = $this->getUserSmsByDay();

        $money = 0;
        foreach ($data as $v){
            if($this->smsSalary($v)){
                $moneyArr = $this->salaryPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 历史累计收到发薪短信中的工资最大值
     * @return int
     */
    public function checkMaxOfHistSMSSalary(){
        $data = $this->getUserSmsByDay();

        $money = [];
        foreach ($data as $v){
            if($this->smsSalary($v)){
                $moneyArr = $this->salaryPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 历史累计收到发薪短信中的工资最小值
     * @return int
     */
    public function checkMinOfHistSMSSalary()
    {
        $data = $this->getUserSmsByDay();

        $money = [];
        foreach ($data as $v) {
            if ($this->smsSalary($v)) {
                $moneyArr = $this->salaryPreg($v);
                if (!empty($moneyArr)) {
                    $money[] = max($moneyArr);
                }
            }
        }

        if (empty($money)) {
            return -1;
        }

        return min($money);
    }

    /**
     * 历史累计收到发薪短信中的工资平均值
     * @return int
     */
    public function checkAvgOfHistSMSSalary()
    {
        $data = $this->getUserSmsByDay();

        $money = [];
        foreach ($data as $v) {
            if ($this->smsSalary($v)) {
                $moneyArr = $this->salaryPreg($v);
                if (!empty($moneyArr)) {
                    $money[] = max($moneyArr);
                }
            }
        }

        if (empty($money)) {
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 近30天内收到发薪短信中的工资之和
     * @return int
     */
    public function checkSumOfSMSSalaryLast30Days(){
        $data = $this->getUserSmsByDay(30, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->smsSalary($v)){
                $moneyArr = $this->salaryPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 近30天内收到发薪短信中的工资最大值
     * @return int
     */
    public function checkMaxOfSMSSalaryLast30Days(){
        $data = $this->getUserSmsByDay(30, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->smsSalary($v)){
                $moneyArr = $this->salaryPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 近30天内收到发薪短信中的工资最小值
     * @return int
     */
    public function checkMinOfSMSSalaryLast30Days()
    {
        $data = $this->getUserSmsByDay(30, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v) {
            if ($this->smsSalary($v)) {
                $moneyArr = $this->salaryPreg($v);
                if (!empty($moneyArr)) {
                    $money[] = max($moneyArr);
                }
            }
        }

        if (empty($money)) {
            return -1;
        }

        return min($money);
    }

    /**
     * 近30天内收到发薪短信中的工资平均值
     * @return int
     */
    public function checkAvgOfSMSSalaryLast30Days()
    {
        $data = $this->getUserSmsByDay(30, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v) {
            if ($this->smsSalary($v)) {
                $moneyArr = $this->salaryPreg($v);
                if (!empty($moneyArr)) {
                    $money[] = max($moneyArr);
                }
            }
        }

        if (empty($money)) {
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 近90天内收到发薪短信中的工资之和
     * @return int
     */
    public function checkSumOfSMSSalaryLast90Days(){
        $data = $this->getUserSmsByDay(90, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->smsSalary($v)){
                $moneyArr = $this->salaryPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 近90天内收到发薪短信中的工资最大值
     * @return int
     */
    public function checkMaxOfSMSSalaryLast90Days(){
        $data = $this->getUserSmsByDay(90, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->smsSalary($v)){
                $moneyArr = $this->salaryPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 近90天内收到发薪短信中的工资最小值
     * @return int
     */
    public function checkMinOfSMSSalaryLast90Days()
    {
        $data = $this->getUserSmsByDay(90, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v) {
            if ($this->smsSalary($v)) {
                $moneyArr = $this->salaryPreg($v);
                if (!empty($moneyArr)) {
                    $money[] = max($moneyArr);
                }
            }
        }

        if (empty($money)) {
            return -1;
        }

        return min($money);
    }

    /**
     * 近90天内收到发薪短信中的工资平均值
     * @return int
     */
    public function checkAvgOfSMSSalaryLast90Days()
    {
        $data = $this->getUserSmsByDay(90, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v) {
            if ($this->smsSalary($v)) {
                $moneyArr = $this->salaryPreg($v);
                if (!empty($moneyArr)) {
                    $money[] = max($moneyArr);
                }
            }
        }

        if (empty($money)) {
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 近180天内收到发薪短信中的工资之和
     * @return int
     */
    public function checkSumOfSMSSalaryLast180Days(){
        $data = $this->getUserSmsByDay(180, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->smsSalary($v)){
                $moneyArr = $this->salaryPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 近180天内收到发薪短信中的工资最大值
     * @return int
     */
    public function checkMaxOfSMSSalaryLast180Days(){
        $data = $this->getUserSmsByDay(180, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->smsSalary($v)){
                $moneyArr = $this->salaryPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 近180天内收到发薪短信中的工资最小值
     * @return int
     */
    public function checkMinOfSMSSalaryLast180Days()
    {
        $data = $this->getUserSmsByDay(180, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v) {
            if ($this->smsSalary($v)) {
                $moneyArr = $this->salaryPreg($v);
                if (!empty($moneyArr)) {
                    $money[] = max($moneyArr);
                }
            }
        }

        if (empty($money)) {
            return -1;
        }

        return min($money);
    }

    /**
     * 近180天内收到发薪短信中的工资平均值
     * @return int
     */
    public function checkAvgOfSMSSalaryLast180Days()
    {
        $data = $this->getUserSmsByDay(180, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v) {
            if ($this->smsSalary($v)) {
                $moneyArr = $this->salaryPreg($v);
                if (!empty($moneyArr)) {
                    $money[] = max($moneyArr);
                }
            }
        }

        if (empty($money)) {
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * @param $sms
     * @return bool
     */
    private function smsSalaryAvlBal($sms){
        if(stripos($sms, 'salary') === false
            || stripos($sms, 'credited to') === false
            || stripos($sms, 'a/c') === false
        ){
            return false;
        }

        if(stripos($sms, 'avl bal') !== false
            || stripos($sms, 'avbl bal') !== false
        ){
            return true;
        }

        return false;
    }

    private function salaryAvlBalPreg($sms){
        $data = [];
        $sms = strtoupper(str_replace([' ', ','], '', $sms));
        if(preg_match_all('/(AVLBAL:INR|AVBLBAL\-)(\d+)/', $sms, $matches)){
            foreach ($matches[2] as $money){
                $data[] = intval($money);
            }
        }

        return $data;
    }

    /**
     * 历史累计收到的发薪短信中的账户余额之和
     * @return int
     */
    public function checkSumOfHistSMSSalaryAvlBal(){
        $data = $this->getUserSmsByDay();

        $money = 0;
        foreach ($data as $v){
            if($this->smsSalaryAvlBal($v)){
                $moneyArr = $this->salaryAvlBalPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 历史累计收到的发薪短信中的账户余额最大值
     * @return int
     */
    public function checkMaxOfHistSMSSalaryAvlBal(){
        $data = $this->getUserSmsByDay();

        $money = [];
        foreach ($data as $v){
            if($this->smsSalaryAvlBal($v)){
                $moneyArr = $this->salaryAvlBalPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 历史累计收到的发薪短信中的账户余额最小值
     * @return int
     */
    public function checkMinOfHistSMSSalaryAvlBal()
    {
        $data = $this->getUserSmsByDay();

        $money = [];
        foreach ($data as $v) {
            if ($this->smsSalaryAvlBal($v)) {
                $moneyArr = $this->salaryAvlBalPreg($v);
                if (!empty($moneyArr)) {
                    $money[] = max($moneyArr);
                }
            }
        }

        if (empty($money)) {
            return -1;
        }

        return min($money);
    }

    /**
     * 历史累计收到的发薪短信中的账户余额平均值
     * @return int
     */
    public function checkAvgOfHistSMSSalaryAvlBal()
    {
        $data = $this->getUserSmsByDay();

        $money = [];
        foreach ($data as $v) {
            if ($this->smsSalaryAvlBal($v)) {
                $moneyArr = $this->salaryAvlBalPreg($v);
                if (!empty($moneyArr)) {
                    $money[] = max($moneyArr);
                }
            }
        }

        if (empty($money)) {
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 近30天内收到的发薪短信中的账户余额之和
     * @return int
     */
    public function checkSumOfSMSSalaryAvlBalLast30Days(){
        $data = $this->getUserSmsByDay(30, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->smsSalaryAvlBal($v)){
                $moneyArr = $this->salaryAvlBalPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 近30天内收到的发薪短信中的账户余额最大值
     * @return int
     */
    public function checkMaxOfSMSSalaryAvlBalLast30Days(){
        $data = $this->getUserSmsByDay(30, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->smsSalaryAvlBal($v)){
                $moneyArr = $this->salaryAvlBalPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 近30天内收到的发薪短信中的账户余额最小值
     * @return int
     */
    public function checkMinOfSMSSalaryAvlBalLast30Days()
    {
        $data = $this->getUserSmsByDay(30, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v) {
            if ($this->smsSalaryAvlBal($v)) {
                $moneyArr = $this->salaryAvlBalPreg($v);
                if (!empty($moneyArr)) {
                    $money[] = max($moneyArr);
                }
            }
        }

        if (empty($money)) {
            return -1;
        }

        return min($money);
    }

    /**
     * 近30天内收到的发薪短信中的账户余额平均值
     * @return int
     */
    public function checkAvgOfSMSSalaryAvlBalLast30Days()
    {
        $data = $this->getUserSmsByDay(30, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v) {
            if ($this->smsSalaryAvlBal($v)) {
                $moneyArr = $this->salaryAvlBalPreg($v);
                if (!empty($moneyArr)) {
                    $money[] = max($moneyArr);
                }
            }
        }

        if (empty($money)) {
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 近90天内收到的发薪短信中的账户余额之和
     * @return int
     */
    public function checkSumOfSMSSalaryAvlBalLast90Days(){
        $data = $this->getUserSmsByDay(90, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->smsSalaryAvlBal($v)){
                $moneyArr = $this->salaryAvlBalPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 近90天内收到的发薪短信中的账户余额最大值
     * @return int
     */
    public function checkMaxOfSMSSalaryAvlBalLast90Days(){
        $data = $this->getUserSmsByDay(90, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->smsSalaryAvlBal($v)){
                $moneyArr = $this->salaryAvlBalPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 近90天内收到的发薪短信中的账户余额最小值
     * @return int
     */
    public function checkMinOfSMSSalaryAvlBalLast90Days()
    {
        $data = $this->getUserSmsByDay(90, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v) {
            if ($this->smsSalaryAvlBal($v)) {
                $moneyArr = $this->salaryAvlBalPreg($v);
                if (!empty($moneyArr)) {
                    $money[] = max($moneyArr);
                }
            }
        }

        if (empty($money)) {
            return -1;
        }

        return min($money);
    }

    /**
     * 近90天内收到的发薪短信中的账户余额平均值
     * @return int
     */
    public function checkAvgOfSMSSalaryAvlBalLast90Days()
    {
        $data = $this->getUserSmsByDay(90, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v) {
            if ($this->smsSalaryAvlBal($v)) {
                $moneyArr = $this->salaryAvlBalPreg($v);
                if (!empty($moneyArr)) {
                    $money[] = max($moneyArr);
                }
            }
        }

        if (empty($money)) {
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 近180天内收到的发薪短信中的账户余额之和
     * @return int
     */
    public function checkSumOfSMSSalaryAvlBalLast180Days(){
        $data = $this->getUserSmsByDay(180, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->smsSalaryAvlBal($v)){
                $moneyArr = $this->salaryAvlBalPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 近180天内收到的发薪短信中的账户余额最大值
     * @return int
     */
    public function checkMaxOfSMSSalaryAvlBalLast180Days(){
        $data = $this->getUserSmsByDay(180, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->smsSalaryAvlBal($v)){
                $moneyArr = $this->salaryAvlBalPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 近180天内收到的发薪短信中的账户余额最小值
     * @return int
     */
    public function checkMinOfSMSSalaryAvlBalLast180Days()
    {
        $data = $this->getUserSmsByDay(180, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v) {
            if ($this->smsSalaryAvlBal($v)) {
                $moneyArr = $this->salaryAvlBalPreg($v);
                if (!empty($moneyArr)) {
                    $money[] = max($moneyArr);
                }
            }
        }

        if (empty($money)) {
            return -1;
        }

        return min($money);
    }

    /**
     * 近180天内收到的发薪短信中的账户余额平均值
     * @return int
     */
    public function checkAvgOfSMSSalaryAvlBalLast180Days()
    {
        $data = $this->getUserSmsByDay(180, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v) {
            if ($this->smsSalaryAvlBal($v)) {
                $moneyArr = $this->salaryAvlBalPreg($v);
                if (!empty($moneyArr)) {
                    $money[] = max($moneyArr);
                }
            }
        }

        if (empty($money)) {
            return -1;
        }

        return round(array_sum($money) / count($money));
    }


    /**
     * 根据指定IP在多少天之内的申请数
     * @param $ip
     * @param int $day 至少为1
     * @param int $orderTime
     * @return int
     */
    protected function getApplyCntByBeforeDayIPSelf($ip, $day ,$orderTime)
    {
        $key = $day;
        if(isset($this->ipInDayOrderApplyCountSelf[$key])){
            return $this->ipInDayOrderApplyCountSelf[$key];
        }else{
            $before = $orderTime - 86400 * $day;
            $count = UserLoanOrder::find()
                ->from(UserLoanOrder::tableName() . ' as o')
                ->leftJoin(LoanPerson::tableName() . ' as p', 'o.user_id=p.id')
                ->where(['o.ip' => $ip, 'p.source_id' => $this->data->loanPerson->source_id])
                ->andWhere(['>=', 'o.order_time', $before])
                ->andWhere(['<=', 'o.order_time', $orderTime])
                ->count();
            return $this->ipInDayOrderApplyCountSelf[$key] = $count;
        }
    }

    /**
     * 同一个IP下近7天内在本平台的申请数
     * @return int
     */
    public function checkApplyCntLast7daysByIPSelf()
    {
        $ip = $this->data->order->ip;
        $count = $this->getApplyCntByBeforeDayIPSelf($ip, 7, $this->data->order->order_time);
        return $count;
    }

    /**
     * 同一IP下1天内在本平台的申请数
     * @return int
     */
    public function checkApplyCntLast1dayByIPSelf()
    {
        $ip = $this->data->order->ip;
        $count = $this->getApplyCntByBeforeDayIPSelf($ip, 1, $this->data->order->order_time);
        return $count;
    }

    /**
     * 同一IP下1小时内在本平台的申请数
     * @return int
     */
    public function checkApplyCntLast1hourByIPSelf()
    {
        $ip = $this->data->order->ip;
        $before = $this->data->order->order_time - 3600;
        $count = UserLoanOrder::find()
            ->from(UserLoanOrder::tableName() . ' as o')
            ->leftJoin(LoanPerson::tableName() . ' as p', 'o.user_id=p.id')
            ->where(['o.ip' => $ip, 'p.source_id' => $this->data->loanPerson->source_id])
            ->andWhere(['>=', 'o.order_time', $before])
            ->andWhere(['<=', 'o.order_time', $this->data->order->order_time])
            ->count();

        return $count;
    }

    /**
     * 同一个IP下历史在本平台的申请数
     * @return int
     */
    public function checkHisApplyCntByIPSelf()
    {
        $ip = $this->data->order->ip;
        $count = UserLoanOrder::find()
            ->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'o.user_id=p.id')
            ->where(['o.ip' => $ip, 'p.source_id' => $this->data->loanPerson->source_id])
            ->andWhere(['<=', 'o.order_time', $this->data->order->order_time])
            ->count();

        return $count;
    }

    /**
     * 同一个IP下历史在本平台申请的拒绝数
     * @return int
     */
    public function checkHisApplyRejectCntByIPSelf()
    {
        $ip = $this->data->order->ip;
        $count = UserLoanOrder::find()
            ->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'o.user_id=p.id')
            ->where(['o.status' => UserLoanOrder::STATUS_CHECK_REJECT,
                     'o.ip' => $ip,
                     'p.source_id' => $this->data->loanPerson->source_id])
            ->andWhere(['<=', 'o.order_time', $this->data->order->order_time])
            ->count();

        return $count;
    }

    /**
     * 同一个IP下近7天内在本平台申请的拒绝数
     * @return int
     */
    public function checkLast7dApplyRejectCntByIPSelf()
    {
        $ip = $this->data->order->ip;
        $before = $this->data->order->order_time - 86400 * 7;
        $count = UserLoanOrder::find()
            ->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'o.user_id=p.id')
            ->where(['o.status' => UserLoanOrder::STATUS_CHECK_REJECT,
                     'o.ip' => $ip,
                     'p.source_id' => $this->data->loanPerson->source_id])
            ->andWhere(['>=', 'o.order_time', $before])
            ->andWhere(['<=', 'o.order_time', $this->data->order->order_time])
            ->count();

        return $count;
    }

    /**
     * 同一IP下1天内在本平台申请的拒绝数
     * @return int
     */
    public function checkLast1dApplyRejectCntByIPSelf()
    {
        $ip = $this->data->order->ip;
        $before = $this->data->order->order_time - 86400;
        $count = UserLoanOrder::find()
            ->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'o.user_id=p.id')
            ->where(['o.status' => UserLoanOrder::STATUS_CHECK_REJECT,
                     'o.ip' => $ip,
                     'p.source_id' => $this->data->loanPerson->source_id])
            ->andWhere(['>=', 'o.order_time', $before])
            ->andWhere(['<=', 'o.order_time', $this->data->order->order_time])
            ->count();

        return $count;
    }

    /**
     * 同一IP下1小时内在本平台申请的拒绝数
     * @return int
     */
    public function checkLast1hApplyRejectCntByIPSelf()
    {
        $ip = $this->data->order->ip;
        $before = $this->data->order->order_time - 3600;
        $count = UserLoanOrder::find()
            ->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'o.user_id=p.id')
            ->where(['o.status' => UserLoanOrder::STATUS_CHECK_REJECT,
                     'o.ip' => $ip,
                     'p.source_id' => $this->data->loanPerson->source_id])
            ->andWhere(['>=', 'o.order_time', $before])
            ->andWhere(['<=', 'o.order_time', $this->data->order->order_time])
            ->count();

        return $count;
    }

    /**
     * 同一个IP下历史在本平台的已到期订单数
     * @return int
     */
    public function checkHisExpireCntByIPSelf()
    {
        $ip = $this->data->order->ip;
        $after = strtotime(date('Y-m-d', $this->data->order->order_time));
        $count = UserLoanOrderRepayment::find()
            ->from(UserLoanOrderRepayment::tableName() . ' as r')
            ->leftJoin(UserLoanOrder::tableName(). ' as o', 'r.order_id=o.id')
            ->leftJoin(LoanPerson::tableName().' as p', 'o.user_id=p.id')
            ->where(['o.ip' => $ip, 'p.source_id' => $this->data->loanPerson->source_id])
            ->andWhere(['<=', 'r.plan_repayment_time', $after])
            ->count();

        return $count;
    }

    /**
     * 同一个IP下历史在本平台的已到期订单中的逾期订单数
     * @return int
     */
    public function checkHisExpireDueCntByIPSelf()
    {
        $ip = $this->data->order->ip;
        $after = strtotime(date('Y-m-d', $this->data->order->order_time));
        $count = UserLoanOrderRepayment::find()
            ->from(UserLoanOrderRepayment::tableName() . ' as r')
            ->leftJoin(UserLoanOrder::tableName(). ' as o', 'r.order_id=o.id')
            ->leftJoin(LoanPerson::tableName().' as p', 'o.user_id=p.id')
            ->where(['o.ip' => $ip,
                     'r.is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_YES,
                     'p.source_id' => $this->data->loanPerson->source_id])
            ->andWhere(['<=', 'r.plan_repayment_time', $after])
            ->count();

        return $count;
    }

    /**
     * 同一定位地址的500米半径内、近7天内在全平台的申请贷款笔数
     *
     * @return int
     * @throws
     */
    public function checkApplyCnt500mAwayFromGPSlocLast7DaysAllPlatform(): int
    {
        //elasticsearch的使用说明
        //1.使用count时禁止使用source
        //2.distance单位km,m,cm,mm,nmi
        //3.date 时间戳或Zulu ISO8601
        $clientInfo = json_decode($this->data->order->client_info, true);
        if(empty($clientInfo['latitude']) || empty($clientInfo['longitude'])){
            return -1;
        }
        $time = $this->data->order->order_time;
        $orderNum = EsUserLoanOrder::find()
            ->query([
                'bool' => [
                    'must' => [
                        'range' => [
                            'order_time' => [
                                'gte' => Carbon::createFromTimestamp($time)->subDays(7)->toIso8601ZuluString(),
                                'lte' => Carbon::createFromTimestamp($time)->toIso8601ZuluString()
                            ]
                        ],
                    ],
                    'filter' => [
                        'geo_distance' => [
                            'distance' => '500m',
                            'location' => [
                                'lat' => $clientInfo['latitude'],
                                'lon' => $clientInfo['longitude'],
                            ]
                        ]
                    ]
                ]
            ])
            ->count();

        return $orderNum;
    }

    /**
     * 同一定位地址的500米半径内、近1天内在全平台的申请贷款笔数
     *
     * @return int
     * @throws
     */
    public function checkApplyCnt500mAwayFromGPSlocLast1DayAllPlatform(): int
    {
        $clientInfo = json_decode($this->data->order->client_info, true);
        if(empty($clientInfo['latitude']) || empty($clientInfo['longitude'])){
            return -1;
        }
        $orderNum = EsUserLoanOrder::find()
            ->query([
                'bool' => [
                    'must' => [
                        'range' => [
                            'order_time' => [
                                'gte' => Carbon::createFromTimestamp($this->data->order->order_time)->subDays(1)->toIso8601ZuluString(),
                                'lte' => Carbon::createFromTimestamp($this->data->order->order_time)->toIso8601ZuluString()
                            ]
                        ],
                    ],
                    'filter' => [
                        'geo_distance' => [
                            'distance' => '500m',
                            'location' => [
                                'lat' => $clientInfo['latitude'],
                                'lon' => $clientInfo['longitude'],
                            ]
                        ]
                    ]
                ]
            ])
            ->count();

        return $orderNum;
    }

    /**
     * 同一定位地址的500米半径内、近1小时内在全平台的申请贷款笔数
     *
     * @return int
     * @throws
     */
    public function checkApplyCnt500mAwayFromGPSlocLast1HourAllPlatform(): int
    {
        //elasticsearch的使用说明
        //1.使用count时禁止使用source
        //2.distance单位km,m,cm,mm,nmi
        //3.date 时间戳或Zulu ISO8601
        $clientInfo = json_decode($this->data->order->client_info, true);
        if(empty($clientInfo['latitude']) || empty($clientInfo['longitude'])){
            return -1;
        }
        $orderNum = EsUserLoanOrder::find()
            ->query([
                'bool' => [
                    'must' => [
                        'range' => [
                            'order_time' => [
                                'gte' => Carbon::createFromTimestamp($this->data->order->order_time)->subHours(1)->toIso8601ZuluString(),
                                'lte' => Carbon::createFromTimestamp($this->data->order->order_time)->toIso8601ZuluString()
                            ]
                        ],
                    ],
                    'filter' => [
                        'geo_distance' => [
                            'distance' => '500m',
                            'location' => [
                                'lat' => $clientInfo['latitude'],
                                'lon' => $clientInfo['longitude'],
                            ]
                        ]
                    ]
                ]
            ])
            ->count();

        return $orderNum;
    }

    /**
     * 同一定位地址的500米半径内、历史在全平台的申请贷款笔数
     *
     * @return int
     * @throws
     */
    public function checkHisApplyCnt500mAwayFromGPSLocAllPlatform(): int
    {
        //elasticsearch的使用说明
        //1.使用count时禁止使用source
        //2.distance单位km,m,cm,mm,nmi
        //3.date 时间戳或Zulu ISO8601
        $clientInfo = json_decode($this->data->order->client_info, true);
        if(empty($clientInfo['latitude']) || empty($clientInfo['longitude'])){
            return -1;
        }
        $time = $this->data->order->order_time;
        $orderNum = EsUserLoanOrder::find()
            ->query([
                'bool' => [
                    'must' => [
                        'range' => [
                            'order_time' => [
                                'lte' => Carbon::createFromTimestamp($time)->toIso8601ZuluString()
                            ]
                        ]
                    ],
                    'filter' => [
                        'geo_distance' => [
                            'distance' => '500m',
                            'location' => [
                                'lat' => $clientInfo['latitude'],
                                'lon' => $clientInfo['longitude'],
                            ]
                        ]
                    ]
                ]
            ])
            ->count();

        return $orderNum;
    }

    /**
     * 此手机号是近1个月内(本平台申请用户)的(本平台的紧急联系人手机号码)的不同(本平台申请用户数量)
     * @return int|string
     */
    public function checkMobileSameAsContactMobileCntLast1MonthSelf()
    {
        $phone = $this->data->loanPerson->phone;
        $userIds = array_unique(ArrayHelper::getColumn(UserContact::find()->select(['user_id'])
            ->where(['phone' => $phone])
            ->orWhere(['other_phone' => $phone])
            ->asArray()->all(),
            'user_id'));
        $lastTime = strtotime('last month');
        $count = 0;
        if (!empty($userIds)) {
            $count = UserLoanOrder::find()
                ->from(UserLoanOrder::tableName().' as o')
                ->leftJoin(LoanPerson::tableName().' as p', 'p.id=o.user_id')
                ->where(['o.user_id' => $userIds, 'p.source_id' => $this->data->loanPerson->source_id])
                ->andWhere(['>=', 'o.order_time', $lastTime])
                ->asArray()->groupBy(['o.user_id'])->count();
        }

        return $count;
    }

    /**
     * 此手机号在本平台命中(逾期用户的本平台紧急联系人的手机号)个数
     * @return int
     */
    public function checkMobileSameAsOverdueContactMobileCntSelf()
    {
        $count = UserOverdueContact::find()
            ->from(UserOverdueContact::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id=o.user_id')
            ->where(['o.phone' => $this->data->loanPerson->phone,
                     'p.source_id' => $this->data->loanPerson->source_id])->count();
        return $count;
    }

    /**
     * 获取该pan下所有用户紧急联系人
     * @param $phone
     * @return array|mixed
     */
    protected function getUserContacts($user_id)
    {
        $key = "{$user_id}";
        if (isset($this->userContactsSelf[$key])) {
            return $this->userContactsSelf[$key];
        } else {
            $userContact = UserContact::find()->select(['phone', 'other_phone'])->where(['user_id' => $user_id])->asArray()->all();

            $phones = array_unique(array_merge(ArrayHelper::getColumn($userContact, 'phone'),
                ArrayHelper::getColumn($userContact, 'other_phone')));
            return $this->userContactsSelf[$key] = $phones;
        }
    }

    /**
     * 近1个月内此Pan卡下的(本平台的紧急联系人)的手机号码作为本平台紧急联系人在本平台出现的最大次数
     * @return mixed
     */
    public function checkSameContactCntLast1MonthSelf()
    {
        $phones = $this->getUserContacts($this->data->loanPerson->id);

        $lastTime = strtotime('last month');
        $count1 = UserContact::find()
            ->from(UserContact::tableName().' as c')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id=c.user_id')
            ->where(['c.phone' => $phones, 'p.source_id' => $this->data->loanPerson->source_id])
            ->andWhere(['!=', 'c.user_id', $this->data->loanPerson->id])
            ->andWhere(['>=', 'c.created_at', $lastTime])
            ->groupBy(['c.user_id'])->count();
        $count2 = UserContact::find()
            ->from(UserContact::tableName().' as c')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id=c.user_id')
            ->where(['c.other_phone' => $phones, 'p.source_id' => $this->data->loanPerson->source_id])
            ->andWhere(['!=', 'c.user_id', $this->data->loanPerson->id])
            ->andWhere(['>=', 'c.created_at', $lastTime])
            ->groupBy(['c.user_id'])->count();
        return max($count1, $count2);
    }

    /**
     * 此Pan卡号下的本平台紧急联系人的手机号码是否为有效号码
     * @return int  1 两个号码均有效   0 至少有一个号码无效
     */
    public function checkContactMobileIsValidSelf()
    {
        $phones = $this->getUserContacts($this->data->loanPerson->id);

        $i = 0;
        foreach ($phones as $phone) {
            $phone_arr = explode(':',$phone);
            foreach ($phone_arr as $v){
                if (preg_match(Util::getPhoneMatch(),$v)) {
                    $i++;
                    break;
                }
            }
        }

        if($i == count($phones)){
            return 1;
        }
        return 0;
    }

    /**
     * 此Pan卡号下的本平台紧急联系人的手机号是否为此Pan卡号下的本平台申请手机号
     * @return int  0 不是   1 是
     */
    public function checkContactMobileSameAsApplyMobileSelf()
    {
        $phones = $this->getUserContacts($this->data->loanPerson->id);
        if (in_array($this->data->loanPerson->phone, $phones)
        ) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * 此Pan卡号下的本平台紧急联系人的手机号命中(本平台逾期用户Pan卡下的本平台手机号)的数量
     * @return int
     */
    public function checkContactNameMobileHitOverdueUserMobileCntSelf()
    {
        $phones = $this->getUserContacts($this->data->loanPerson->id);

        $userIds = ArrayHelper::getColumn(
            LoanPerson::find()->select(['id'])->where(['phone' => $phones, 'source_id' => $this->data->loanPerson->source_id])->asArray()->all(),
            'id');
        $count = 0;
        if (!empty($userIds)) {
            $count = UserLoanOrderRepayment::find()
                ->where([
                    'user_id' => $userIds,
                    'is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_YES
                ])
                ->andWhere(['!=', 'user_id', $this->data->loanPerson->id])
                ->count('distinct user_id');
        }

        return $count;
    }

    /**
     * 此Pan卡号下的本平台紧急联系人的手机号命中(本平台逾期用户Pan卡下的本平台紧急联系人的手机号)数量
     * @return int|string
     */
    public function checkContactNameMobileHitOverdueUserContactMobileCntSelf()
    {
        $phones = $this->getUserContacts($this->data->loanPerson->id);
        $userIds1 = ArrayHelper::getColumn(
            UserContact::find()
                ->from(UserContact::tableName().' as c')
                ->leftJoin(LoanPerson::tableName().' as p', 'c.user_id=p.id')
                ->select(['c.user_id'])
                ->where(['c.phone' => $phones, 'p.source_id' => $this->data->loanPerson->source_id])
                ->groupBy(['c.user_id'])->asArray()->all(),
            'user_id');

        $userIds2 = ArrayHelper::getColumn(
            UserContact::find()
                ->from(UserContact::tableName().' as c')
                ->leftJoin(LoanPerson::tableName().' as p', 'c.user_id=p.id')
                ->select(['c.user_id'])
                ->where(['c.other_phone' => $phones, 'p.source_id' => $this->data->loanPerson->source_id])
                ->groupBy(['c.user_id'])->asArray()->all(),
            'user_id');

        $userIds = array_merge($userIds1, $userIds2);
        $count = 0;
        if (!empty($userIds)) {
            $count = UserLoanOrderRepayment::find()
                ->where([
                    'user_id' => $userIds,
                    'is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_YES
                ])
                ->andWhere(['!=', 'user_id', $this->data->loanPerson->id])
                ->count('distinct user_id');
        }

        return $count;
    }

    /**
     * 此Pan卡号下的本平台紧急联系人的手机号命中(本平台逾期30+用户Pan卡下的本平台手机号)的数量
     * @return int|string
     */
    public function checkContactNameMobileHitOver30OverdueUserContactMobileCntSelf()
    {
        $phones = $this->getUserContacts($this->data->loanPerson->id);
        $userIds1 = ArrayHelper::getColumn(
            UserContact::find()
                ->from(UserContact::tableName().' as c')
                ->leftJoin(LoanPerson::tableName().' as p', 'c.user_id=p.id')
                ->select(['c.user_id'])
                ->where(['c.phone' => $phones, 'p.source_id' => $this->data->loanPerson->source_id])
                ->groupBy(['c.user_id'])->asArray()->all(),
            'user_id');

        $userIds2 = ArrayHelper::getColumn(
            UserContact::find()
                ->from(UserContact::tableName().' as c')
                ->leftJoin(LoanPerson::tableName().' as p', 'c.user_id=p.id')
                ->select(['c.user_id'])
                ->where(['c.other_phone' => $phones, 'p.source_id' => $this->data->loanPerson->source_id])
                ->groupBy(['c.user_id'])->asArray()->all(),
            'user_id');

        $userIds = array_merge($userIds1, $userIds2);
        $count = 0;
        if (!empty($userIds)) {
            $count = UserLoanOrderRepayment::find()
                ->where([
                    'user_id' => $userIds,
                    'is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_YES
                ])
                ->andWhere(['>=', 'overdue_day', 30])
                ->andWhere(['!=', 'user_id', $this->data->loanPerson->id])
                ->count('distinct user_id');
        }

        return $count;
    }

    /**
     * 此Pan卡号下的本平台紧急联系人的手机号命中(本平台逾期30+用户Pan卡下的本平台紧急联系人手机号)数量
     * @return int|string
     */
    public function checkContactNameMobileHitOver30OverdueUserMobileCntSelf()
    {
        $phones = $this->getUserContacts($this->data->loanPerson->id);
        $userIds = ArrayHelper::getColumn(
            LoanPerson::find()->select(['id'])->where(['phone' => $phones, 'source_id' => $this->data->loanPerson->source_id])->asArray()->all(),
            'id');
        $count = 0;
        if (!empty($userIds)) {
            $count = UserLoanOrderRepayment::find()
                ->where([
                    'user_id' => $userIds,
                    'is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_YES,
                ])
                ->andWhere(['>=', 'overdue_day', 30])
                ->andWhere(['!=', 'user_id', $this->data->loanPerson->id])
                ->count('distinct user_id');
        }

        return $count;
    }

    /**
     * 历史该Pan卡号在本平台申请被拒次数
     * @return int|string
     */
    public function checkHisRejectCntByPanSelf()
    {
        $userIds = $this->data->loanPerson->id;
        $count = UserLoanOrder::find()
            ->where(['status' => UserLoanOrder::STATUS_CHECK_REJECT, 'user_id' => $userIds])
            ->count();

        return $count;
    }

    /**
     * 近90天该Pan卡号在本平台申请被拒次数
     * @return int|string
     */
    public function checkLast90dRejectCntByPanSelf()
    {
        $lastTime = $this->data->order->order_time - 90 * 86400;
        $userIds = $this->data->loanPerson->id;
        $count = UserLoanOrder::find()
            ->where(['status' => UserLoanOrder::STATUS_CHECK_REJECT, 'user_id' => $userIds])
            ->andWhere(['>=', 'order_time', $lastTime])
            ->count();

        return $count;
    }

    /**
     * 近60天该Pan卡号在本平台申请被拒次数
     * @return int|string
     */
    public function checkLast60dRejectCntByPanSelf()
    {
        $lastTime = $this->data->order->order_time - 60 * 86400;
        $userIds = $this->data->loanPerson->id;
        $count = UserLoanOrder::find()
            ->where(['status' => UserLoanOrder::STATUS_CHECK_REJECT, 'user_id' => $userIds])
            ->andWhere(['>=', 'order_time', $lastTime])
            ->count();

        return $count;
    }

    /**
     * 近30天该Pan卡号在本平台申请被拒次数
     * @return int|string
     */
    public function checkLast30dRejectCntByPanSelf()
    {
        $lastTime = $this->data->order->order_time - 30 * 86400;
        $userIds = $this->data->loanPerson->id;
        $count = UserLoanOrder::find()
            ->where(['status' => UserLoanOrder::STATUS_CHECK_REJECT, 'user_id' => $userIds])
            ->andWhere(['>=', 'order_time', $lastTime])
            ->count();

        return $count;
    }

    /**
     * 近7天该Pan卡号在本平台申请被拒次数
     * @return int|string
     */
    public function checkLast7dRejectCntByPanSelf()
    {
        $lastTime = $this->data->order->order_time - 7 * 86400;
        $userIds = $this->data->loanPerson->id;
        $count = UserLoanOrder::find()
            ->where(['status' => UserLoanOrder::STATUS_CHECK_REJECT, 'user_id' => $userIds])
            ->andWhere(['>=', 'order_time', $lastTime])
            ->count();

        return $count;
    }

    /**
     * 历史该Pan卡号在本平台的申请次数
     * @return int|string
     */
    public function checkHisApplyCntByPanSelf()
    {
        $userIds = $this->data->loanPerson->id;
        $count = UserLoanOrder::find()
            ->where(['user_id' => $userIds])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->count();

        return $count;
    }

    /**
     * 近90天该Pan卡号在本平台的申请次数
     * @return int|string
     */
    public function checkLast90dApplyCntByPanSelf()
    {
        $lastTime = $this->data->order->order_time - 90 * 86400;
        $userIds = $this->data->loanPerson->id;
        $count = UserLoanOrder::find()
            ->where(['user_id' => $userIds])
            ->andWhere(['>=', 'order_time', $lastTime])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->count();

        return $count;
    }

    /**
     * 近60天该Pan卡号在本平台的申请次数
     * @return int|string
     */
    public function checkLast60dApplyCntByPanSelf()
    {
        $lastTime = $this->data->order->order_time - 60 * 86400;
        $userIds = $this->data->loanPerson->id;
        $count = UserLoanOrder::find()
            ->where(['user_id' => $userIds])
            ->andWhere(['>=', 'order_time', $lastTime])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->count();

        return $count;
    }

    /**
     * 近30天该Pan卡号在本平台的申请次数
     * @return int|string
     */
    public function checkLast30dApplyCntByPanSelf()
    {
        $lastTime = $this->data->order->order_time - 30 * 86400;
        $userIds = $this->data->loanPerson->id;
        $count = UserLoanOrder::find()
            ->where(['user_id' => $userIds])
            ->andWhere(['>=', 'order_time', $lastTime])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->count();

        return $count;
    }

    /**
     * 近7天该Pan卡号在本平台的申请次数
     * @return int|string
     */
    public function checkLast7dApplyCntByPanSelf()
    {
        $lastTime = $this->data->order->order_time - 7 * 86400;
        $userIds = $this->data->loanPerson->id;
        $count = UserLoanOrder::find()
            ->where(['user_id' => $userIds])
            ->andWhere(['>=', 'order_time', $lastTime])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->count();

        return $count;
    }

    /**
     * 近90天数盟设备ID在本平台的申请次数
     * @return int
     */
    public function checkLast90dApplyCntBySMDeviceIDSelf(){
        if(empty($this->data->order->did)){
            return -1;
        }
        $begin_time = $this->data->order->order_time - 90 * 86400;
        $count = UserLoanOrder::find()
            ->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id=o.user_id')
            ->where(['o.did' => $this->data->order->did, 'p.source_id' => $this->data->loanPerson->source_id])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->order->order_time])
            ->count();

        return $count;
    }

    /**
     * 近60天数盟设备ID在本平台的申请次数
     * @return int
     */
    public function checkLast60dApplyCntBySMDeviceIDSelf(){
        if(empty($this->data->order->did)){
            return -1;
        }
        $begin_time = $this->data->order->order_time - 60 * 86400;
        $count = UserLoanOrder::find()
            ->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id=o.user_id')
            ->where(['o.did' => $this->data->order->did, 'p.source_id' => $this->data->loanPerson->source_id])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->order->order_time])
            ->count();

        return $count;
    }

    /**
     * 近30天数盟设备ID在本平台的申请次数
     * @return int
     */
    public function checkLast30dApplyCntBySMDeviceIDSelf(){
        if(empty($this->data->order->did)){
            return -1;
        }
        $begin_time = $this->data->order->order_time - 30 * 86400;
        $count = UserLoanOrder::find()
            ->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id=o.user_id')
            ->where(['o.did' => $this->data->order->did, 'p.source_id' => $this->data->loanPerson->source_id])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->order->order_time])
            ->count();

        return $count;
    }

    /**
     * 近7天数盟设备ID在本平台的申请次数
     * @return int
     */
    public function checkLast7dApplyCntBySMDeviceIDSelf(){
        if(empty($this->data->order->did)){
            return -1;
        }
        $begin_time = $this->data->order->order_time - 7 * 86400;
        $count = UserLoanOrder::find()
            ->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id=o.user_id')
            ->where(['o.did' => $this->data->order->did, 'p.source_id' => $this->data->loanPerson->source_id])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->order->order_time])
            ->count();

        return $count;
    }

    /**
     * 数盟设备ID在本平台的历史申请次数
     * @return int
     */
    public function checkHisApplyCntBySMDeviceIDSelf(){
        if(empty($this->data->order->did)){
            return -1;
        }
        $count = UserLoanOrder::find()
            ->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id=o.user_id')
            ->where(['o.did' => $this->data->order->did, 'p.source_id' => $this->data->loanPerson->source_id])
            ->andWhere(['<=', 'o.order_time', $this->data->order->order_time])
            ->count();

        return $count;
    }

    /**
     * 近90天数盟设备ID在本平台的申请被拒次数
     * @return int
     */
    public function checkLast90dRejectCntBySMDeviceIDSelf(){
        if(empty($this->data->order->did)){
            return -1;
        }
        $begin_time = $this->data->order->order_time - 90 * 86400;
        $count = UserLoanOrder::find()
            ->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id=o.user_id')
            ->where(['o.did' => $this->data->order->did,
                     'o.status' => UserLoanOrder::STATUS_CHECK_REJECT,
                     'p.source_id' => $this->data->loanPerson->source_id])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->count();

        return $count;
    }

    /**
     * 近60天数盟设备ID在本平台的申请被拒次数
     * @return int
     */
    public function checkLast60dRejectCntBySMDeviceIDSelf(){
        if(empty($this->data->order->did)){
            return -1;
        }
        $begin_time = $this->data->order->order_time - 60 * 86400;
        $count = UserLoanOrder::find()
            ->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id=o.user_id')
            ->where(['o.did' => $this->data->order->did,
                     'o.status' => UserLoanOrder::STATUS_CHECK_REJECT,
                     'p.source_id' => $this->data->loanPerson->source_id])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->count();

        return $count;
    }

    /**
     * 近30天数盟设备ID在本平台的申请被拒次数
     * @return int
     */
    public function checkLast30dRejectCntBySMDeviceIDSelf(){
        if(empty($this->data->order->did)){
            return -1;
        }
        $begin_time = $this->data->order->order_time - 30 * 86400;
        $count = UserLoanOrder::find()
            ->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id=o.user_id')
            ->where(['o.did' => $this->data->order->did,
                     'o.status' => UserLoanOrder::STATUS_CHECK_REJECT,
                     'p.source_id' => $this->data->loanPerson->source_id])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->count();

        return $count;
    }

    /**
     * 近7天数盟设备ID在本平台的申请被拒次数
     * @return int
     */
    public function checkLast7dRejectCntBySMDeviceIDSelf(){
        if(empty($this->data->order->did)){
            return -1;
        }
        $begin_time = $this->data->order->order_time - 7 * 86400;
        $count = UserLoanOrder::find()
            ->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id=o.user_id')
            ->where(['o.did' => $this->data->order->did,
                     'o.status' => UserLoanOrder::STATUS_CHECK_REJECT,
                     'p.source_id' => $this->data->loanPerson->source_id])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->count();

        return $count;
    }

    /**
     * 数盟设备ID历史在本平台的申请被拒次数
     * @return int
     */
    public function checkHisRejectCntBySMDeviceIDSelf(){
        if(empty($this->data->order->did)){
            return -1;
        }
        $count = UserLoanOrder::find()
            ->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id=o.user_id')
            ->where(['o.did' => $this->data->order->did,
                     'o.status' => UserLoanOrder::STATUS_CHECK_REJECT,
                     'p.source_id' => $this->data->loanPerson->source_id])
            ->count();

        return $count;
    }

    /**
     * 历史该Pan卡号在本平台放款的次数
     * @return int
     */
    public function checkHisLoanCntByPanSelf(){
        $userIds = $this->data->loanPerson->id;
        $count = UserLoanOrderRepayment::find()->where(['user_id' => $userIds])->count();

        return $count;
    }

    /**
     * 近30天该Pan卡号在本平台放款的次数
     * @return int
     */
    public function checkLast30dLoanCntByPanSelf(){
        $begin_time = $this->data->order->order_time - 30 * 86400;
        $userIds = $this->data->loanPerson->id;

        $count = UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds])
            ->andWhere(['>=', 'loan_time', $begin_time])
            ->count();

        return $count;
    }

    /**
     * 本平台该Pan卡号本次申请订单距离上次申请订单时间的时间差
     * @return int
     */
    public function checkDateDiffOfOrderAndLastOrderApplyByPanSelf(){
        $userIds = $this->data->loanPerson->id;

        $data = UserLoanOrder::find()
            ->where(['user_id' => $userIds])
            ->andWhere(['!=', 'id', $this->data->order->id])
            ->orderBy(['order_time' => SORT_DESC])
            ->one();

        if(empty($data)){
            return -1;
        }

        $orderTime = $data['order_time'];

        return (strtotime(date('Y-m-d', $this->data->order->order_time)) - strtotime(date('Y-m-d', $orderTime))) / 86400;
    }

    /**
     * 本平台该Pan卡号本次申请订单距离上次放款订单时间的时间差
     * @return int
     */
    public function checkDateDiffOfOrderAndLastOrderLoanByPanSelf(){
        $userIds = $this->data->loanPerson->id;

        $data = UserLoanOrder::find()
            ->where(['user_id' => $userIds])
            ->andWhere(['>', 'loan_time', 0])
            ->orderBy(['loan_time' => SORT_DESC])
            ->one();

        if(empty($data)){
            return -1;
        }

        $orderTime = $data['loan_time'];

        return (strtotime(date('Y-m-d', $this->data->order->order_time)) - strtotime(date('Y-m-d', $orderTime))) / 86400;
    }

    /**
     * 近30天该Pan卡号在本平台已到期订单数
     * @return int
     */
    public function checkLast30dExpireCntByPanSelf(){
        $userIds = $this->data->loanPerson->id;

        $after = strtotime(date('Y-m-d', $this->data->order->order_time));
        $before = $after - 30 * 86400;

        $count = UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds])
            ->andWhere(['>=', 'plan_repayment_time', $before])
            ->andWhere(['<=', 'plan_repayment_time', $after])
            ->count();

        return $count;
    }

    /**
     * 历史该Pan卡号在本平台的还款次数
     * @return int
     */
    public function checkHisRepayCntByPanSelf(){
        $userIds = $this->data->loanPerson->id;

        $count = UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds,
                     'status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE])
            ->count();

        return $count;
    }

    /**
     * 近30天该Pan卡号在本平台的还款次数
     * @return int
     */
    public function checkLast30dRepayCntByPanSelf(){
        $before = $this->data->order->order_time - 30 * 86400;
        $userIds = $this->data->loanPerson->id;
        $count = UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds,
                     'status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE])
            ->andWhere(['>=', 'closing_time', $before])
            ->count();

        return $count;
    }

    /**
     * 历史该Pan卡号在本平台的逾期次数
     * @return int
     */
    public function checkHisDueCntByPanSelf(){
        $userIds = $this->data->loanPerson->id;

        $count = UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds,
                     'is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_YES])
            ->count();

        return $count;
    }

    /**
     * 近30天该Pan卡号在本平台的逾期次数
     * @return int
     */
    public function checkLast30dDueCntByPanSelf(){
        $before = $this->data->order->order_time - 30 * 86400;

        $userIds = $this->data->loanPerson->id;
        $count = UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds,
                     'is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_YES])
            ->andWhere(['>=', 'plan_repayment_time', $before])
            ->count();

        return $count;
    }

    /**
     * 本平台-根据Pan号查询的历史逾期天数的总和
     * @return int
     */
    public function checkHisDueSumDayByPanSelf(){
        $userIds = $this->data->loanPerson->id;
        $data = ArrayHelper::getColumn(UserLoanOrderRepayment::find()->select(['overdue_day'])
            ->where(['user_id' => $userIds])
            ->asArray()
            ->all(),
            'overdue_day');

        return array_sum($data);
    }

    /**
     * 本平台-根据Pan号查询的历史逾期天数的平均值
     * @return int
     */
    public function checkHisDueAvgDayByPanSelf(){
        $userIds = $this->data->loanPerson->id;
        $data = ArrayHelper::getColumn(UserLoanOrderRepayment::find()->select(['overdue_day'])
            ->where(['user_id' => $userIds, 'is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_YES])
            ->asArray()
            ->all(),
            'overdue_day');

        if(empty($data)){
            return 0;
        }

        return round(array_sum($data) / count($data));
    }

    /**
     * 本平台-根据Pan号查询的历史最大逾期天数
     * @return int
     */
    public function checkHistMaxOverdueDaysByPanSelf(){
        $userIds = $this->data->loanPerson->id;
        $data = UserLoanOrderRepayment::find()->select(['overdue_day'])
            ->where(['user_id' => $userIds])
            ->orderBy(['overdue_day' => SORT_DESC])
            ->asArray()
            ->one();

        return $data['overdue_day'] ?? 0;
    }

    /**
     * 用户在每次登录时的不同数盟设备ID的数量
     * @return int
     */
    public function checkSMDeviceIDOfLoginCnt(){
        $count = ClientInfoLog::find()
            ->where(['user_id' => $this->data->loanPerson->id,'event' => ClientInfoLog::EVENT_LOGIN])
            ->groupBy(['szlm_query_id'])
            ->count('*', \Yii::$app->db_read_1);
        return $count;
    }

    /**
     * 近90天用户在每次登录时的不同数盟设备ID的数量
     * @return int
     */
    public function checkLast90MSMDeviceIDOfLoginCnt(){
        $begin_time = strtotime("-90 day");
        $count = ClientInfoLog::find()
            ->where(['user_id' => $this->data->loanPerson->id,'event' => ClientInfoLog::EVENT_LOGIN])
            ->andWhere(['>=', 'created_at', $begin_time])
            ->groupBy(['szlm_query_id'])
            ->count('*', \Yii::$app->db_read_1);
        return $count;
    }

    /**
     * 老用户模型V2-近30天逾期还款的次数
     * @return int
     */
    public function checkLast30dDueRepayCnt(){
        $begin_time = strtotime(date('Y-m-d', $this->data->order->order_time)) - 30 * 86400;

        $count = UserLoanOrderRepayment::find()
            ->where(['is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_YES, 'user_id' => $this->data->loanPerson->id])
            ->andWhere(['>=', 'closing_time', $begin_time])->count();

        return $count;
    }

    /**
     * 老用户模型V2-历史逾期4天及以上还款的次数占历史逾期还款次数的比例
     * @return int
     */
    public function checkHisDue4RepayCntHisDueCntRate(){
        $count_all = UserLoanOrderRepayment::find()
            ->where(['status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE,
                     'is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_YES,
                     'user_id' => $this->data->loanPerson->id])->count();

        if(empty($count_all)){
            return -1;
        }

        $count = UserLoanOrderRepayment::find()
            ->where(['status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE, 'user_id' => $this->data->loanPerson->id])
            ->andWhere(['>=', 'overdue_day', 4])->count();

        return round($count / $count_all * 100, 2);
    }

    /**
     * 老用户模型V2-近7天还款次数占近30天还款次数的比例
     * @return int
     */
    public function checkLast7dRepayCntLast30dCntRate(){
        $begin_time = strtotime(date('Y-m-d', $this->data->order->order_time)) - 30 * 86400;
        $count_all = UserLoanOrderRepayment::find()
            ->where(['user_id' => $this->data->loanPerson->id])
            ->andWhere(['>=', 'closing_time', $begin_time])->count();

        if(empty($count_all)){
            return -1;
        }

        $begin_time = strtotime(date('Y-m-d', $this->data->order->order_time)) - 7 * 86400;
        $count = UserLoanOrderRepayment::find()
            ->where(['user_id' => $this->data->loanPerson->id])
            ->andWhere(['>=', 'closing_time', $begin_time])->count();

        return round($count / $count_all * 100, 2);
    }

    /**
     * 老用户模型V2-近30天正常还款的次数占历史放款订单数的比例
     * @return int
     */
    public function checkLast30dTiqianRepayCntHisCntRate(){
        $begin_time = strtotime(date('Y-m-d', $this->data->order->order_time)) - 30 * 86400;

        $count_all = UserLoanOrderRepayment::find()
            ->where(['user_id' => $this->data->loanPerson->id])->count();
        if(empty($count_all)){
            return -1;
        }

        $count = UserLoanOrderRepayment::find()
            ->where(['user_id' => $this->data->loanPerson->id, 'is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_NO])
            ->andWhere(['>=', 'closing_time', $begin_time])->count();


        return round($count / $count_all * 100, 2);
    }

    /**
     * 老用户模型V2-历史逾期天数的均值
     * @return int
     */
    public function checkHisAvgDueRepayDay(){
        $repaymentOrders = UserLoanOrderRepayment::find()->where(['user_id' => $this->data->loanPerson->id])->all();
        $overdueDays = [];
        /**
         * @var UserLoanOrderRepayment $repaymentOrder
         */
        foreach($repaymentOrders as $repaymentOrder){
            $overdueDays[] = $repaymentOrder->overdue_day;
        }

        if(empty($overdueDays)){
            return -1;
        }

        return round(array_sum($overdueDays) / count($overdueDays), 2);
    }

    /**
     * 老用户模型V2-本次订单申请时间与历史放款订单的申请时间差的最大值
     * @return int
     */
    public function checkMaxDateOfOrderToToday(){
        $data = UserLoanOrder::find()->where(['user_id' => $this->data->loanPerson->id])
            ->andWhere(['>', 'loan_time', 0])
            ->all();
        $count = [0];
        foreach ($data as $v){
            $count[] = (strtotime(date('Y-m-d', $this->data->order->order_time)) - strtotime(date('Y-m-d', $v['order_time'])))/86400;
        }

        return max($count);
    }

    /**
     * 老用户模型V2-本次订单申请时间与历史放款订单的申请时间差的均值
     * @return int
     */
    public function checkAvgDateOfOrderToToday(){
        $data = UserLoanOrder::find()->where(['user_id' => $this->data->loanPerson->id])
            ->andWhere(['>', 'loan_time', 0])
            ->all();
        $count = [];
        foreach ($data as $v){
            $count[] = (strtotime(date('Y-m-d', $this->data->order->order_time)) - strtotime(date('Y-m-d', $v['order_time'])))/86400;
        }

        if(empty($count)){
            return -1;
        }

        return round(array_sum($count) / count($count), 2);
    }


    /**
     * 老用户模型分V2
     * @return int
     */
    public function checkOldUserModelScoreV2(){
        $v356 = $this->checkIsSMSRecordGrabNormal();

        $score = 0;
        if($v356 == 0){
            $score += 337;
        }else{
            $v601 = $this->checkSMSCntOfLoanRejectionLast7Days();
            switch (true){
                case $v601 < 1:
                    $score += 50;
                    break;
                case $v601 < 2:
                    $score += 34;
                    break;
                case $v601 < 3:
                    $score += 13;
                    break;
                case $v601 >= 3:
                    $score += -7;
                    break;
            }

            $v606 = $this->checkSMSCntOfLoanApprovalLast7Days();
            switch (true){
                case $v606 < 1:
                    $score += 22;
                    break;
                case $v606 < 4:
                    $score += 33;
                    break;
                case $v606 < 9:
                    $score += 48;
                    break;
                case $v606 >= 9:
                    $score += 68;
                    break;
            }

            $v610 = $this->checkHistSMSCntOfLoanDisbursal();
            switch (true){
                case $v610 < 1:
                    $score += 28;
                    break;
                case $v610 < 5:
                    $score += 37;
                    break;
                case $v610 < 12:
                    $score += 46;
                    break;
                case $v610 < 21:
                    $score += 57;
                    break;
                case $v610 >= 21:
                    $score += 69;
                    break;
            }

            $v616 = $this->checkSMSCntOfLoanDueRemindLast7Days();
            switch (true){
                case $v616 < 2:
                    $score += 39;
                    break;
                case $v616 < 23:
                    $score += 44;
                    break;
                case $v616 < 33:
                    $score += 28;
                    break;
                case $v616 >= 33:
                    $score += 16;
                    break;
            }

            $v626 = $this->checkSMSCntOfOverdueRemindLast7Days();
            switch (true){
                case $v626 < 1:
                    $score += 46;
                    break;
                case $v626 < 2:
                    $score += 36;
                    break;
                case $v626 < 4:
                    $score += 28;
                    break;
                case $v626 >= 4:
                    $score += 17;
                    break;
            }

            $v611 = $this->checkSMSCntOfLoanDisbursalLast7Days();
            switch (true){
                case $v611 < 1:
                    $score += 37;
                    break;
                case $v611 < 2:
                    $score += 43;
                    break;
                case $v611 >= 2:
                    $score += 51;
                    break;
            }

            $v605 = $this->checkHistSMSCntOfLoanApproval();
            switch (true){
                case $v605 < 5:
                    $score += 28;
                    break;
                case $v605 < 40:
                    $score += 37;
                    break;
                case $v605 < 60:
                    $score += 42;
                    break;
                case $v605 >= 60:
                    $score += 50;
                    break;
            }

            $v627 = $this->checkSMSCntOfOverdueRemindLast30Days();
            switch (true){
                case $v627 < 1:
                    $score += 52;
                    break;
                case $v627 < 2:
                    $score += 44;
                    break;
                case $v627 < 5:
                    $score += 33;
                    break;
                case $v627 >= 5:
                    $score += 19;
                    break;
            }
        }

        $v896 = $this->checkLast30dDueRepayCnt();
        switch (true){
            case $v896 < 1:
                $score += 40;
                break;
            case $v896 >= 1:
                $score += 39;
                break;
        }

        $v897 = $this->checkHisDue4RepayCntHisDueCntRate();
        switch (true){
            case $v897 < 0:
                $score += 52;
                break;
            case $v897 < 33.33:
                $score += 26;
                break;
            case $v897 >= 33.33:
                $score += 3;
                break;
        }

        $v898 = $this->checkLast7dRepayCntLast30dCntRate();
        switch (true){
            case $v898 < 0:
                $score += 16;
                break;
            case $v898 < 30:
                $score += 37;
                break;
            case $v898 < 45:
                $score += 45;
                break;
            case $v898 < 80:
                $score += 43;
                break;
            case $v898 >= 80:
                $score += 35;
                break;
        }

        $v899 = $this->checkLast30dTiqianRepayCntHisCntRate();
        switch (true){
            case $v899 < 16:
                $score += 30;
                break;
            case $v899 < 30:
                $score += 39;
                break;
            case $v899 >= 30:
                $score += 41;
                break;
        }

        $v900 = $this->checkAvgDateOfOrderToToday();
        switch (true){
            case $v900 < 2:
                $score += 8;
                break;
            case $v900 < 26:
                $score += 32;
                break;
            case $v900 < 34:
                $score += 45;
                break;
            case $v900 >= 34:
                $score += 53;
                break;
        }

        $v901 = $this->checkHisAvgDueRepayDay();
        switch (true){
            case $v901 < 0.1:
                $score += 46;
                break;
            case $v901 < 0.3:
                $score += 40;
                break;
            case $v901 < 0.8:
                $score += 30;
                break;
            case $v901 >= 0.8:
                $score += 17;
                break;
        }

        $v902 = $this->checkMaxDateOfOrderToToday();
        switch (true){
            case $v902 < 12:
                $score += 27;
                break;
            case $v902 < 60:
                $score += 38;
                break;
            case $v902 < 96:
                $score += 50;
                break;
            case $v902 >= 96:
                $score += 57;
                break;
        }

        return $score;
    }

    /**
     * 获取用户短信 全平台
     * @param int $day
     * @param int $orderTime
     * @return array|mixed
     */
    protected function getUserAllSmsByDay($day = 0, $orderTime = 0)
    {
        $key = $day;
        if(isset($this->userAllSms[$key])){
            return $this->userAllSms[$key];
        }else{
            $user_id = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
            foreach ($user_id as &$v){
                $v = intval($v);
            }
            $user_id_loan = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);
            foreach ($user_id_loan as &$v){
                $v = intval($v);
            }
            if($day == 0){
                $sms = MgUserMobileSms::find()
                    ->select(['messageContent','messageDate'])
                    ->where(['user_id' => $user_id, 'type' => 1])
                    ->asArray()
                    ->all();
                $sms_other = MgUserMobileSms::find()
                    ->select(['messageContent','messageDate'])
                    ->where(['user_id' => $user_id_loan, 'type' => 1])
                    ->asArray()
                    ->all(Yii::$app->mongodb_loan);
            }else{
                $begin_time = $orderTime - $day * 86400;
                $sms = MgUserMobileSms::find()
                    ->select(['messageContent','messageDate'])
                    ->where(['user_id' => $user_id, 'type' => 1])
                    ->andWhere(['>=', 'messageDate',$begin_time])
                    ->asArray()
                    ->all();

                $sms_other = MgUserMobileSms::find()
                    ->select(['messageContent','messageDate'])
                    ->where(['user_id' => $user_id_loan, 'type' => 1])
                    ->andWhere(['>=', 'messageDate',$begin_time])
                    ->asArray()
                    ->all(Yii::$app->mongodb_loan);
            }
            $data = [];
            foreach ($sms as $values){
                $date = date('Y-m-d', $values['messageDate']);
                $data[$date][] = $values['messageContent'];
            }

            foreach ($sms_other as $values){
                $date = date('Y-m-d', $values['messageDate']);
                $data[$date][] = $values['messageContent'];
            }

            $arr = [];
            foreach ($data as $value){
                $arr = array_merge($arr,array_unique($value));
            }
            return $this->userAllSms[$key] = $arr;
        }
    }

    /**
     * 全平台-历史尝试申请贷款的短信数量
     * @return int
     */
    public function checkHistSMSCntOfLoanApplicationTrialTPF(){
        $data = $this->getUserAllSmsByDay();
        $count = 0;
        foreach ($data as $v){
            if($this->loanApplicationTrial($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 全平台-近7天内尝试申请贷款的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanApplicationTrialLast7DaysTPF(){
        $data = $this->getUserAllSmsByDay(7, $this->data->order->order_time);
        $count = 0;
        foreach ($data as $v){
            if($this->loanApplicationTrial($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 全平台-近30天内尝试申请贷款的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanApplicationTrialLast30DaysTPF(){
        $data = $this->getUserAllSmsByDay(30, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanApplicationTrial($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 全平台-近60天内尝试申请贷款的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanApplicationTrialLast60DaysTPF(){
        $data = $this->getUserAllSmsByDay(60, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanApplicationTrial($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 全平台-近90天内尝试申请贷款的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanApplicationTrialLast90DaysTPF(){
        $data = $this->getUserAllSmsByDay(90, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanApplicationTrial($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 全平台-历史申请贷款提交成功的短信数量
     * @return int
     */
    public function checkHistSMSCntOfLoanApplicationSubmissionTPF(){
        $data = $this->getUserAllSmsByDay();

        $count = 0;
        foreach ($data as $v){
            if($this->loanApplicationSubmission($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 全平台-近7天内申请贷款提交成功的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanApplicationSubmissionLast7DaysTPF(){
        $data = $this->getUserAllSmsByDay(7, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanApplicationSubmission($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 全平台-近30天内申请贷款提交成功的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanApplicationSubmissionLast30DaysTPF(){
        $data = $this->getUserAllSmsByDay(30, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanApplicationSubmission($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 全平台-近60天内申请贷款提交成功的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanApplicationSubmissionLast60DaysTPF(){
        $data = $this->getUserAllSmsByDay(60, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanApplicationSubmission($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 全平台-近90天内申请贷款提交成功的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanApplicationSubmissionLast90DaysTPF(){
        $data = $this->getUserAllSmsByDay(90, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanApplicationSubmission($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 全平台-历史贷款审批拒绝的短信数量
     * @return int
     */
    public function checkHistSMSCntOfLoanRejectionTPF(){
        $data = $this->getUserAllSmsByDay();

        $count = 0;
        foreach ($data as $v){
            if($this->loanRejection($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 全平台-近7天内贷款审批拒绝的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanRejectionLast7DaysTPF(){
        $data = $this->getUserAllSmsByDay(7, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanRejection($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 全平台-近30天内贷款审批拒绝的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanRejectionLast30DaysTPF(){
        $data = $this->getUserAllSmsByDay(30, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanRejection($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 全平台-近60天内贷款审批拒绝的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanRejectionLast60DaysTPF(){
        $data = $this->getUserAllSmsByDay(60, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanRejection($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 全平台-近90天内贷款审批拒绝的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanRejectionLast90DaysTPF(){
        $data = $this->getUserAllSmsByDay(90, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanRejection($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 全平台-历史贷款审批通过的短信数量
     * @return int
     */
    public function checkHistSMSCntOfLoanApprovalTPF(){
        $data = $this->getUserAllSmsByDay();

        $count = 0;
        foreach ($data as $v){
            if($this->loanApproval($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 全平台-近7天内贷款审批通过的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanApprovalLast7DaysTPF(){
        $data = $this->getUserAllSmsByDay(7, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanApproval($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 全平台-近30天内贷款审批通过的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanApprovalLast30DaysTPF(){
        $data = $this->getUserAllSmsByDay(30, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanApproval($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 全平台-近60天内贷款审批通过的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanApprovalLast60DaysTPF(){
        $data = $this->getUserAllSmsByDay(60, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanApproval($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 全平台-近90天内贷款审批通过的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanApprovalLast90DaysTPF(){
        $data = $this->getUserAllSmsByDay(90, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanApproval($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 全平台-历史放款成功的短信数量
     * @return int
     */
    public function checkHistSMSCntOfLoanDisbursalTPF(){
        $data = $this->getUserAllSmsByDay();

        $count = 0;
        foreach ($data as $v){
            if($this->loanDisbursal($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 全平台-近7天内放款成功的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanDisbursalLast7DaysTPF(){
        $data = $this->getUserAllSmsByDay(7, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanDisbursal($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 全平台-近30天内放款成功的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanDisbursalLast30DaysTPF(){
        $data = $this->getUserAllSmsByDay(30, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanDisbursal($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 全平台-近60天内放款成功的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanDisbursalLast60DaysTPF(){
        $data = $this->getUserAllSmsByDay(60, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanDisbursal($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 全平台-近90天内放款成功的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanDisbursalLast90DaysTPF(){
        $data = $this->getUserAllSmsByDay(90, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanDisbursal($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 全平台-历史到期前提醒还款的短信数量
     * @return int
     */
    public function checkHistSMSCntOfLoanDueRemindTPF(){
        $data = $this->getUserAllSmsByDay();

        $count = 0;
        foreach ($data as $v){
            if($this->loanDueRemind($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 全平台-近7天内到期前提醒还款的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanDueRemindLast7DaysTPF(){
        $data = $this->getUserAllSmsByDay(7, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanDueRemind($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 全平台-近30天内到期前提醒还款的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanDueRemindLast30DaysTPF(){
        $data = $this->getUserAllSmsByDay(30, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanDueRemind($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 全平台-近60天内到期前提醒还款的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanDueRemindLast60DaysTPF(){
        $data = $this->getUserAllSmsByDay(60, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanDueRemind($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 全平台-近90天内到期前提醒还款的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanDueRemindLast90DaysTPF(){
        $data = $this->getUserAllSmsByDay(90, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanDueRemind($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 全平台-历史所有还款成功的短信数量
     * @return int
     */
    public function checkHistSMSCntOfLoanPayOffTPF(){
        $data = $this->getUserAllSmsByDay();

        $count = 0;
        foreach ($data as $v){
            if($this->loanPayOff($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 全平台-近7天内所有还款成功的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanPayOffLast7DaysTPF(){
        $data = $this->getUserAllSmsByDay(7, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanPayOff($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 全平台-近30天内所有还款成功的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanPayOffLast30DaysTPF(){
        $data = $this->getUserAllSmsByDay(30, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanPayOff($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 全平台-近60天内所有还款成功的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanPayOffLast60DaysTPF(){
        $data = $this->getUserAllSmsByDay(60, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanPayOff($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 全平台-近90天内所有还款成功的短信数量
     * @return int
     */
    public function checkSMSCntOfLoanPayOffLast90DaysTPF(){
        $data = $this->getUserAllSmsByDay(90, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->loanPayOff($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 全平台-历史所有逾期提醒还款的短信数量
     * @return int
     */
    public function checkHistSMSCntOfOverdueRemindTPF(){
        $data = $this->getUserAllSmsByDay();

        $count = 0;
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 全平台-近7天内逾期提醒还款的短信数量
     * @return int
     */
    public function checkSMSCntOfOverdueRemindLast7DaysTPF(){
        $data = $this->getUserAllSmsByDay(7, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 全平台-近30天内逾期提醒还款的短信数量
     * @return int
     */
    public function checkSMSCntOfOverdueRemindLast30DaysTPF(){
        $data = $this->getUserAllSmsByDay(30, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 全平台-近60天内逾期提醒还款的短信数量
     * @return int
     */
    public function checkSMSCntOfOverdueRemindLast60DaysTPF(){
        $data = $this->getUserAllSmsByDay(60, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 全平台-近90天内逾期提醒还款的短信数量
     * @return int
     */
    public function checkSMSCntOfOverdueRemindLast90DaysTPF(){
        $data = $this->getUserAllSmsByDay(90, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 全平台-历史短信中贷款授信额度合计
     * @return int
     */
    public function checkHistSumOfSMSLoanCreditAmtTPF(){
        $data = $this->getUserAllSmsByDay();

        $money = 0;
        foreach ($data as $v){
            if($this->loanApproval($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 全平台-历史短信中贷款授信额度最大值
     * @return int
     */
    public function checkHistMaxOfSMSLoanCreditAmtTPF(){
        $data = $this->getUserAllSmsByDay();

        $money = [];
        foreach ($data as $v){
            if($this->loanApproval($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 全平台-历史短信中贷款授信额度最小值
     * @return int
     */
    public function checkHistMinOfSMSLoanCreditAmtTPF(){
        $data = $this->getUserAllSmsByDay();

        $money = [];
        foreach ($data as $v){
            if($this->loanApproval($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return min($money);
    }

    /**
     * 全平台-历史短信中贷款授信额度平均值
     * @return int
     */
    public function checkHistAvgOfSMSLoanCreditAmtTPF(){
        $data = $this->getUserAllSmsByDay();

        $money = [];
        foreach ($data as $v){
            if($this->loanApproval($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 全平台-近7天内短信中贷款授信额度合计
     * @return int
     */
    public function checkSumOfSMSLoanCreditAmtLast7DaysTPF(){
        $data = $this->getUserAllSmsByDay(7, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->loanApproval($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 全平台-近7天内短信中贷款授信额度最大值
     * @return int
     */
    public function checkMaxOfSMSLoanCreditAmtLast7DaysTPF(){
        $data = $this->getUserAllSmsByDay(7, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanApproval($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 全平台-近7天内短信中贷款授信额度最小值
     * @return int
     */
    public function checkMinOfSMSLoanCreditAmtLast7DaysTPF(){
        $data = $this->getUserAllSmsByDay(7, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanApproval($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return min($money);
    }

    /**
     * 全平台-近7天内短信中贷款授信额平均值
     * @return int
     */
    public function checkAvgOfSMSLoanCreditAmtLast7DaysTPF(){
        $data = $this->getUserAllSmsByDay(7, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanApproval($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 全平台-近30天内短信中贷款授信额度合计
     * @return int
     */
    public function checkSumOfSMSLoanCreditAmtLast30DaysTPF(){
        $data = $this->getUserAllSmsByDay(30, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->loanApproval($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 全平台-近30天内短信中贷款授信额度最大值
     * @return int
     */
    public function checkMaxOfSMSLoanCreditAmtLast30DaysTPF(){
        $data = $this->getUserAllSmsByDay(30, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanApproval($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 全平台-近30天内短信中贷款授信额度最小值
     * @return int
     */
    public function checkMinOfSMSLoanCreditAmtLast30DaysTPF(){
        $data = $this->getUserAllSmsByDay(30, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanApproval($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return min($money);
    }

    /**
     * 全平台-近30天内短信中贷款授信额平均值
     * @return int
     */
    public function checkAvgOfSMSLoanCreditAmtLast30DaysTPF(){
        $data = $this->getUserAllSmsByDay(30, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanApproval($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 全平台-近60天内短信中贷款授信额度合计
     * @return int
     */
    public function checkSumOfSMSLoanCreditAmtLast60DaysTPF(){
        $data = $this->getUserAllSmsByDay(60, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->loanApproval($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 全平台-近60天内短信中贷款授信额度最大值
     * @return int
     */
    public function checkMaxOfSMSLoanCreditAmtLast60DaysTPF(){
        $data = $this->getUserAllSmsByDay(60, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanApproval($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 全平台-近60天内短信中贷款授信额度最小值
     * @return int
     */
    public function checkMinOfSMSLoanCreditAmtLast60DaysTPF(){
        $data = $this->getUserAllSmsByDay(60, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanApproval($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return min($money);
    }

    /**
     * 全平台-近60天内短信中贷款授信额平均值
     * @return int
     */
    public function checkAvgOfSMSLoanCreditAmtLast60DaysTPF(){
        $data = $this->getUserAllSmsByDay(60, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanApproval($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 全平台-近90天内短信中贷款授信额度合计
     * @return int
     */
    public function checkSumOfSMSLoanCreditAmtLast90DaysTPF(){
        $data = $this->getUserAllSmsByDay(90, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->loanApproval($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 全平台-近90天内短信中贷款授信额度最大值
     * @return int
     */
    public function checkMaxOfSMSLoanCreditAmtLast90DaysTPF(){
        $data = $this->getUserAllSmsByDay(90, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanApproval($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 全平台-近90天内短信中贷款授信额度最小值
     * @return int
     */
    public function checkMinOfSMSLoanCreditAmtLast90DaysTPF(){
        $data = $this->getUserAllSmsByDay(90, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanApproval($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return min($money);
    }

    /**
     * 全平台-近90天内短信中贷款授信额平均值
     * @return int
     */
    public function checkAvgOfSMSLoanCreditAmtLast90DaysTPF(){
        $data = $this->getUserAllSmsByDay(90, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanApproval($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 全平台-历史短信中贷款放款金额合计
     * @return int
     */
    public function checkHistSumOfSMSLoanDisburseAmtTPF(){
        $data = $this->getUserAllSmsByDay();

        $money = 0;
        foreach ($data as $v){
            if($this->loanDisbursal($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 全平台-历史短信中贷款放款金额最大值
     * @return int
     */
    public function checkHistMaxOfSMSLoanDisburseAmtTPF(){
        $data = $this->getUserAllSmsByDay();

        $money = [];
        foreach ($data as $v){
            if($this->loanDisbursal($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 全平台-历史短信中贷款放款金额最小值
     * @return int
     */
    public function checkHistMinOfSMSLoanDisburseAmtTPF(){
        $data = $this->getUserAllSmsByDay();

        $money = [];
        foreach ($data as $v){
            if($this->loanDisbursal($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return min($money);
    }

    /**
     * 全平台-历史短信中贷款放款金额平均值
     * @return int
     */
    public function checkHistAvgOfSMSLoanDisburseAmtTPF(){
        $data = $this->getUserAllSmsByDay();

        $money = [];
        foreach ($data as $v){
            if($this->loanDisbursal($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 全平台-近7天内短信中贷款放款金额合计
     * @return int
     */
    public function checkSumOfSMSLoanDisburseAmtLast7DaysTPF(){
        $data = $this->getUserAllSmsByDay(7, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->loanDisbursal($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 全平台-近7天内短信中贷款放款金额最大值
     * @return int
     */
    public function checkMaxOfSMSLoanDisburseAmtLast7DaysTPF(){
        $data = $this->getUserAllSmsByDay(7, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanDisbursal($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 全平台-近7天内短信中贷款放款金额最小值
     * @return int
     */
    public function checkMinOfSMSLoanDisburseAmtLast7DaysTPF(){
        $data = $this->getUserAllSmsByDay(7, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanDisbursal($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return min($money);
    }

    /**
     * 全平台-近7天内短信中贷款放款金额平均值
     * @return int
     */
    public function checkAvgOfSMSLoanDisburseAmtLast7DaysTPF(){
        $data = $this->getUserAllSmsByDay(7, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanDisbursal($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 全平台-近30天内短信中贷款放款金额合计
     * @return int
     */
    public function checkSumOfSMSLoanDisburseAmtLast30DaysTPF(){
        $data = $this->getUserAllSmsByDay(30, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->loanDisbursal($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 全平台-近30天内短信中贷款放款金额最大值
     * @return int
     */
    public function checkMaxOfSMSLoanDisburseAmtLast30DaysTPF(){
        $data = $this->getUserAllSmsByDay(30, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanDisbursal($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 全平台-近30天内短信中贷款放款金额最小值
     * @return int
     */
    public function checkMinOfSMSLoanDisburseAmtLast30DaysTPF(){
        $data = $this->getUserAllSmsByDay(30, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanDisbursal($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return min($money);
    }

    /**
     * 全平台-近30天内短信中贷款放款金额平均值
     * @return int
     */
    public function checkAvgOfSMSLoanDisburseAmtLast30DaysTPF(){
        $data = $this->getUserAllSmsByDay(30, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanDisbursal($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 全平台-近60天内短信中贷款放款金额合计
     * @return int
     */
    public function checkSumOfSMSLoanDisburseAmtLast60DaysTPF(){
        $data = $this->getUserAllSmsByDay(60, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->loanDisbursal($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 全平台-近60天内短信中贷款放款金额最大值
     * @return int
     */
    public function checkMaxOfSMSLoanDisburseAmtLast60DaysTPF(){
        $data = $this->getUserAllSmsByDay(60, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanDisbursal($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 全平台-近60天内短信中贷款放款金额最小值
     * @return int
     */
    public function checkMinOfSMSLoanDisburseAmtLast60DaysTPF(){
        $data = $this->getUserAllSmsByDay(60, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanDisbursal($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return min($money);
    }

    /**
     * 全平台-近60天内短信中贷款放款金额平均值
     * @return int
     */
    public function checkAvgOfSMSLoanDisburseAmtLast60DaysTPF(){
        $data = $this->getUserAllSmsByDay(60, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanDisbursal($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 全平台-近90天内短信中贷款放款金额合计
     * @return int
     */
    public function checkSumOfSMSLoanDisburseAmtLast90DaysTPF(){
        $data = $this->getUserAllSmsByDay(90, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->loanDisbursal($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 全平台-近90天内短信中贷款放款金额最大值
     * @return int
     */
    public function checkMaxOfSMSLoanDisburseAmtLast90DaysTPF(){
        $data = $this->getUserAllSmsByDay(90, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanDisbursal($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 全平台-近90天内短信中贷款放款金额最小值
     * @return int
     */
    public function checkMinOfSMSLoanDisburseAmtLast90DaysTPF(){
        $data = $this->getUserAllSmsByDay(90, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanDisbursal($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return min($money);
    }

    /**
     * 全平台-近90天内短信中贷款放款金额平均值
     * @return int
     */
    public function checkAvgOfSMSLoanDisburseAmtLast90DaysTPF(){
        $data = $this->getUserAllSmsByDay(90, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanDisbursal($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 全平台-历史短信每月还款金额之和
     * @return int
     */
    public function checkSumOfHistSMSEMIAmtTPF(){
        $data = $this->getUserAllSmsByDay();

        $money = 0;
        foreach ($data as $v){
            if($this->smsEmi($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money += min($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 全平台-历史短信每月还款金额的最大值
     * @return int
     */
    public function checkMaxOfHistSMSEMIAmtTPF(){
        $data = $this->getUserAllSmsByDay();

        $money = [];
        foreach ($data as $v){
            if($this->smsEmi($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = min($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 全平台-历史短信每月还款金额的最小值
     * @return int
     */
    public function checkMinOfHistSMSEMIAmtTPF(){
        $data = $this->getUserAllSmsByDay();

        $money = [];
        foreach ($data as $v){
            if($this->smsEmi($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = min($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return min($money);
    }

    /**
     * 全平台-历史短信每月还款金额的平均值
     * @return int
     */
    public function checkAvgOfHistSMSEMIAmtTPF(){
        $data = $this->getUserAllSmsByDay();

        $money = [];
        foreach ($data as $v){
            if($this->smsEmi($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = min($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 全平台-近7天内短信每月还款金额之和
     * @return int
     */
    public function checkSumOfSMSEMIAmtLast7DaysTPF(){
        $data = $this->getUserAllSmsByDay(7, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->smsEmi($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money += min($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 全平台-近7天内短信每月还款金额的最大值
     * @return int
     */
    public function checkMaxOfSMSEMIAmtLast7DaysTPF(){
        $data = $this->getUserAllSmsByDay(7, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->smsEmi($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = min($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 全平台-近7天内短信每月还款金额的最小值
     * @return int
     */
    public function checkMinOfSMSEMIAmtLast7DaysTPF(){
        $data = $this->getUserAllSmsByDay(7, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->smsEmi($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = min($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return min($money);
    }

    /**
     * 全平台-近7天内短信每月还款金额的平均值
     * @return int
     */
    public function checkAvgOfSMSEMIAmtLast7DaysTPF(){
        $data = $this->getUserAllSmsByDay(7, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->smsEmi($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = min($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 全平台-近30天内短信每月还款金额之和
     * @return int
     */
    public function checkSumOfSMSEMIAmtLast30DaysTPF(){
        $data = $this->getUserAllSmsByDay(30, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->smsEmi($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money += min($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 全平台-近30天内短信每月还款金额的最大值
     * @return int
     */
    public function checkMaxOfSMSEMIAmtLast30DaysTPF(){
        $data = $this->getUserAllSmsByDay(30, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->smsEmi($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = min($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 全平台-近30天内短信每月还款金额的最小值
     * @return int
     */
    public function checkMinOfSMSEMIAmtLast30DaysTPF(){
        $data = $this->getUserAllSmsByDay(30, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->smsEmi($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = min($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return min($money);
    }

    /**
     * 全平台-近30天内短信每月还款金额的平均值
     * @return int
     */
    public function checkAvgOfSMSEMIAmtLast30DaysTPF(){
        $data = $this->getUserAllSmsByDay(30, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->smsEmi($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = min($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 全平台-近60天内短信每月还款金额之和
     * @return int
     */
    public function checkSumOfSMSEMIAmtLast60DaysTPF(){
        $data = $this->getUserAllSmsByDay(60, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->smsEmi($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money += min($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 全平台-近60天内短信每月还款金额的最大值
     * @return int
     */
    public function checkMaxOfSMSEMIAmtLast60DaysTPF(){
        $data = $this->getUserAllSmsByDay(60, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->smsEmi($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = min($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 全平台-近60天内短信每月还款金额的最小值
     * @return int
     */
    public function checkMinOfSMSEMIAmtLast60DaysTPF(){
        $data = $this->getUserAllSmsByDay(60, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->smsEmi($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = min($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return min($money);
    }

    /**
     * 全平台-近60天内短信每月还款金额的平均值
     * @return int
     */
    public function checkAvgOfSMSEMIAmtLast60DaysTPF(){
        $data = $this->getUserAllSmsByDay(60, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->smsEmi($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = min($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 全平台-近90天内短信每月还款金额之和
     * @return int
     */
    public function checkSumOfSMSEMIAmtLast90DaysTPF(){
        $data = $this->getUserAllSmsByDay(90, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->smsEmi($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money += min($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 全平台-近90天内短信每月还款金额的最大值
     * @return int
     */
    public function checkMaxOfSMSEMIAmtLast90DaysTPF(){
        $data = $this->getUserAllSmsByDay(90, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->smsEmi($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = min($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 全平台-近90天内短信每月还款金额的最小值
     * @return int
     */
    public function checkMinOfSMSEMIAmtLast90DaysTPF(){
        $data = $this->getUserAllSmsByDay(90, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->smsEmi($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = min($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return min($money);
    }

    /**
     * 全平台-近90天内短信每月还款金额的平均值
     * @return int
     */
    public function checkAvgOfSMSEMIAmtLast90DaysTPF(){
        $data = $this->getUserAllSmsByDay(90, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->smsEmi($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = min($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 全平台-历史到期前提醒还款的贷款金额之和
     * @return int
     */
    public function checkSumOfHistSMSDueRemindLoanAmtTPF(){
        $data = $this->getUserAllSmsByDay();

        $money = 0;
        foreach ($data as $v){
            if($this->loanDueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 全平台-历史到期前提醒还款的贷款金额的最大值
     * @return int
     */
    public function checkMaxOfHistSMSDueRemindLoanAmtTPF(){
        $data = $this->getUserAllSmsByDay();

        $money = [];
        foreach ($data as $v){
            if($this->loanDueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 全平台-历史到期前提醒还款的贷款金额的最小值
     * @return int
     */
    public function checkMinOfHistSMSDueRemindLoanAmtTPF(){
        $data = $this->getUserAllSmsByDay();

        $money = [];
        foreach ($data as $v){
            if($this->loanDueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return min($money);
    }

    /**
     * 全平台-历史到期前提醒还款的贷款金额的平均值
     * @return int
     */
    public function checkAvgOfHistSMSDueRemindLoanAmtTPF(){
        $data = $this->getUserAllSmsByDay();

        $money = [];
        foreach ($data as $v){
            if($this->loanDueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 全平台-近7天内到期前提醒还款的贷款金额之和
     * @return int
     */
    public function checkSumOfSMSDueRemindLoanAmtLast7DaysTPF(){
        $data = $this->getUserAllSmsByDay(7, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->loanDueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 全平台-近7天内到期前提醒还款的贷款金额的最大值
     * @return int
     */
    public function checkMaxOfSMSDueRemindLoanAmtLast7DaysTPF(){
        $data = $this->getUserAllSmsByDay(7, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanDueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 全平台-近7天内到期前提醒还款的贷款金额的最小值
     * @return int
     */
    public function checkMinOfSMSDueRemindLoanAmtLast7DaysTPF(){
        $data = $this->getUserAllSmsByDay(7, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanDueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return min($money);
    }

    /**
     * 全平台-近7天内到期前提醒还款的贷款金额的平均值
     * @return int
     */
    public function checkAvgOfSMSDueRemindLoanAmtLast7DaysTPF(){
        $data = $this->getUserAllSmsByDay(7, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanDueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 全平台-近30天内到期前提醒还款的贷款金额之和
     * @return int
     */
    public function checkSumOfSMSDueRemindLoanAmtLast30DaysTPF(){
        $data = $this->getUserAllSmsByDay(30, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->loanDueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 全平台-近30天内到期前提醒还款的贷款金额的最大值
     * @return int
     */
    public function checkMaxOfSMSDueRemindLoanAmtLast30DaysTPF(){
        $data = $this->getUserAllSmsByDay(30, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanDueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 全平台-近30天内到期前提醒还款的贷款金额的最小值
     * @return int
     */
    public function checkMinOfSMSDueRemindLoanAmtLast30DaysTPF(){
        $data = $this->getUserAllSmsByDay(30, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanDueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return min($money);
    }

    /**
     * 全平台-近30天内到期前提醒还款的贷款金额的平均值
     * @return int
     */
    public function checkAvgOfSMSDueRemindLoanAmtLast30DaysTPF(){
        $data = $this->getUserAllSmsByDay(30, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanDueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 全平台-近60天内到期前提醒还款的贷款金额之和
     * @return int
     */
    public function checkSumOfSMSDueRemindLoanAmtLast60DaysTPF(){
        $data = $this->getUserAllSmsByDay(60, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->loanDueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 全平台-近60天内到期前提醒还款的贷款金额的最大值
     * @return int
     */
    public function checkMaxOfSMSDueRemindLoanAmtLast60DaysTPF(){
        $data = $this->getUserAllSmsByDay(60, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanDueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 全平台-近60天内到期前提醒还款的贷款金额的最小值
     * @return int
     */
    public function checkMinOfSMSDueRemindLoanAmtLast60DaysTPF(){
        $data = $this->getUserAllSmsByDay(60, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanDueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return min($money);
    }

    /**
     * 全平台-近60天内到期前提醒还款的贷款金额的平均值
     * @return int
     */
    public function checkAvgOfSMSDueRemindLoanAmtLast60DaysTPF(){
        $data = $this->getUserAllSmsByDay(60, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanDueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 全平台-近90天内到期前提醒还款的贷款金额之和
     * @return int
     */
    public function checkSumOfSMSDueRemindLoanAmtLast90DaysTPF(){
        $data = $this->getUserAllSmsByDay(90, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->loanDueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 全平台-近90天内到期前提醒还款的贷款金额的最大值
     * @return int
     */
    public function checkMaxOfSMSDueRemindLoanAmtLast90DaysTPF(){
        $data = $this->getUserAllSmsByDay(90, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanDueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 全平台-近90天内到期前提醒还款的贷款金额的最小值
     * @return int
     */
    public function checkMinOfSMSDueRemindLoanAmtLast90DaysTPF(){
        $data = $this->getUserAllSmsByDay(90, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanDueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return min($money);
    }

    /**
     * 全平台-近90天内到期前提醒还款的贷款金额的平均值
     * @return int
     */
    public function checkAvgOfSMSDueRemindLoanAmtLast90DaysTPF(){
        $data = $this->getUserAllSmsByDay(90, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->loanDueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 全平台-历史短信中的逾期天数之和
     * @return int
     */
    public function checkHistSumOfSMSLoanOverdueDaysTPF(){
        $data = $this->getUserAllSmsByDay();

        $overdueDay = 0;
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $days = $this->overdueDayPreg($v);
                if(!empty($days)){
                    $overdueDay += max($days);
                }
            }
        }

        if(empty($overdueDay)){
            return -1;
        }

        return $overdueDay;
    }

    /**
     * 全平台-历史短信中的逾期天数最大值
     * @return int
     */
    public function checkHistMaxOfSMSLoanOverdueDaysTPF(){
        $data = $this->getUserAllSmsByDay();

        $overdueDay = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $days = $this->overdueDayPreg($v);
                if(!empty($days)){
                    $overdueDay[] = max($days);
                }
            }
        }

        if(empty($overdueDay)){
            return -1;
        }

        return max($overdueDay);
    }

    /**
     * 全平台-历史短信中的逾期天数最小值
     * @return int
     */
    public function checkHistMinOfSMSLoanOverdueDaysTPF(){
        $data = $this->getUserAllSmsByDay();

        $overdueDay = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $days = $this->overdueDayPreg($v);
                if(!empty($days)){
                    $overdueDay[] = max($days);
                }
            }
        }

        if(empty($overdueDay)){
            return -1;
        }

        return min($overdueDay);
    }

    /**
     * 全平台-历史短信中的逾期天数平均值
     * @return int
     */
    public function checkHistAvgOfSMSLoanOverdueDaysTPF(){
        $data = $this->getUserAllSmsByDay();

        $overdueDay = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $days = $this->overdueDayPreg($v);
                if(!empty($days)){
                    $overdueDay[] = max($days);
                }
            }
        }

        if(empty($overdueDay)){
            return -1;
        }

        return round(array_sum($overdueDay) / count($overdueDay));
    }

    /**
     * 全平台-近7天内短信中的逾期天数之和
     * @return int
     */
    public function checkSumOfSMSLoanOverdueDaysLast7DaysTPF(){
        $data = $this->getUserAllSmsByDay(7, $this->data->order->order_time);

        $overdueDay = 0;
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $days = $this->overdueDayPreg($v);
                if(!empty($days)){
                    $overdueDay += max($days);
                }
            }
        }

        if(empty($overdueDay)){
            return -1;
        }

        return $overdueDay;
    }

    /**
     * 全平台-近7天内短信中的逾期天数最大值
     * @return int
     */
    public function checkMaxOfSMSLoanOverdueDaysLast7DaysTPF(){
        $data = $this->getUserAllSmsByDay(7, $this->data->order->order_time);

        $overdueDay = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $days = $this->overdueDayPreg($v);
                if(!empty($days)){
                    $overdueDay[] = max($days);
                }
            }
        }

        if(empty($overdueDay)){
            return -1;
        }

        return max($overdueDay);
    }

    /**
     * 全平台-近7天内短信中的逾期天数最小值
     * @return int
     */
    public function checkMinOfSMSLoanOverdueDaysLast7DaysTPF(){
        $data = $this->getUserAllSmsByDay(7, $this->data->order->order_time);

        $overdueDay = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $days = $this->overdueDayPreg($v);
                if(!empty($days)){
                    $overdueDay[] = max($days);
                }
            }
        }

        if(empty($overdueDay)){
            return -1;
        }

        return min($overdueDay);
    }

    /**
     * 全平台-近7天内短信中的逾期天数平均值
     * @return int
     */
    public function checkAvgOfSMSLoanOverdueDaysLast7DaysTPF(){
        $data = $this->getUserAllSmsByDay(7, $this->data->order->order_time);

        $overdueDay = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $days = $this->overdueDayPreg($v);
                if(!empty($days)){
                    $overdueDay[] = max($days);
                }
            }
        }

        if(empty($overdueDay)){
            return -1;
        }

        return round(array_sum($overdueDay) / count($overdueDay));
    }

    /**
     * 全平台-近30天内短信中的逾期天数之和
     * @return int
     */
    public function checkSumOfSMSLoanOverdueDaysLast30DaysTPF(){
        $data = $this->getUserAllSmsByDay(30, $this->data->order->order_time);

        $overdueDay = 0;
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $days = $this->overdueDayPreg($v);
                if(!empty($days)){
                    $overdueDay += max($days);
                }
            }
        }

        if(empty($overdueDay)){
            return -1;
        }

        return $overdueDay;
    }

    /**
     * 全平台-近30天内短信中的逾期天数最大值
     * @return int
     */
    public function checkMaxOfSMSLoanOverdueDaysLast30DaysTPF(){
        $data = $this->getUserAllSmsByDay(30, $this->data->order->order_time);

        $overdueDay = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $days = $this->overdueDayPreg($v);
                if(!empty($days)){
                    $overdueDay[] = max($days);
                }
            }
        }

        if(empty($overdueDay)){
            return -1;
        }

        return max($overdueDay);
    }

    /**
     * 全平台-近30天内短信中的逾期天数最小值
     * @return int
     */
    public function checkMinOfSMSLoanOverdueDaysLast30DaysTPF(){
        $data = $this->getUserAllSmsByDay(30, $this->data->order->order_time);

        $overdueDay = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $days = $this->overdueDayPreg($v);
                if(!empty($days)){
                    $overdueDay[] = max($days);
                }
            }
        }

        if(empty($overdueDay)){
            return -1;
        }

        return min($overdueDay);
    }

    /**
     * 全平台-近30天内短信中的逾期天数平均值
     * @return int
     */
    public function checkAvgOfSMSLoanOverdueDaysLast30DaysTPF(){
        $data = $this->getUserAllSmsByDay(30, $this->data->order->order_time);

        $overdueDay = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $days = $this->overdueDayPreg($v);
                if(!empty($days)){
                    $overdueDay[] = max($days);
                }
            }
        }

        if(empty($overdueDay)){
            return -1;
        }

        return round(array_sum($overdueDay) / count($overdueDay));
    }

    /**
     * 全平台-近60天内短信中的逾期天数之和
     * @return int
     */
    public function checkSumOfSMSLoanOverdueDaysLast60DaysTPF(){
        $data = $this->getUserAllSmsByDay(60, $this->data->order->order_time);

        $overdueDay = 0;
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $days = $this->overdueDayPreg($v);
                if(!empty($days)){
                    $overdueDay += max($days);
                }
            }
        }

        if(empty($overdueDay)){
            return -1;
        }

        return $overdueDay;
    }

    /**
     * 全平台-近60天内短信中的逾期天数最大值
     * @return int
     */
    public function checkMaxOfSMSLoanOverdueDaysLast60DaysTPF(){
        $data = $this->getUserAllSmsByDay(60, $this->data->order->order_time);

        $overdueDay = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $days = $this->overdueDayPreg($v);
                if(!empty($days)){
                    $overdueDay[] = max($days);
                }
            }
        }

        if(empty($overdueDay)){
            return -1;
        }

        return max($overdueDay);
    }

    /**
     * 全平台-近60天内短信中的逾期天数最小值
     * @return int
     */
    public function checkMinOfSMSLoanOverdueDaysLast60DaysTPF(){
        $data = $this->getUserAllSmsByDay(60, $this->data->order->order_time);

        $overdueDay = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $days = $this->overdueDayPreg($v);
                if(!empty($days)){
                    $overdueDay[] = max($days);
                }
            }
        }

        if(empty($overdueDay)){
            return -1;
        }

        return min($overdueDay);
    }

    /**
     * 全平台-近60天内短信中的逾期天数平均值
     * @return int
     */
    public function checkAvgOfSMSLoanOverdueDaysLast60DaysTPF(){
        $data = $this->getUserAllSmsByDay(60, $this->data->order->order_time);

        $overdueDay = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $days = $this->overdueDayPreg($v);
                if(!empty($days)){
                    $overdueDay[] = max($days);
                }
            }
        }

        if(empty($overdueDay)){
            return -1;
        }

        return round(array_sum($overdueDay) / count($overdueDay));
    }

    /**
     * 全平台-近90天内短信中的逾期天数之和
     * @return int
     */
    public function checkSumOfSMSLoanOverdueDaysLast90DaysTPF(){
        $data = $this->getUserAllSmsByDay(90, $this->data->order->order_time);

        $overdueDay = 0;
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $days = $this->overdueDayPreg($v);
                if(!empty($days)){
                    $overdueDay += max($days);
                }
            }
        }

        if(empty($overdueDay)){
            return -1;
        }

        return $overdueDay;
    }

    /**
     * 全平台-近90天内短信中的逾期天数最大值
     * @return int
     */
    public function checkMaxOfSMSLoanOverdueDaysLast90DaysTPF(){
        $data = $this->getUserAllSmsByDay(90, $this->data->order->order_time);

        $overdueDay = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $days = $this->overdueDayPreg($v);
                if(!empty($days)){
                    $overdueDay[] = max($days);
                }
            }
        }

        if(empty($overdueDay)){
            return -1;
        }

        return max($overdueDay);
    }

    /**
     * 全平台-近90天内短信中的逾期天数最小值
     * @return int
     */
    public function checkMinOfSMSLoanOverdueDaysLast90DaysTPF(){
        $data = $this->getUserAllSmsByDay(90, $this->data->order->order_time);

        $overdueDay = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $days = $this->overdueDayPreg($v);
                if(!empty($days)){
                    $overdueDay[] = max($days);
                }
            }
        }

        if(empty($overdueDay)){
            return -1;
        }

        return min($overdueDay);
    }

    /**
     * 全平台-近90天内短信中的逾期天数平均值
     * @return int
     */
    public function checkAvgOfSMSLoanOverdueDaysLast90DaysTPF(){
        $data = $this->getUserAllSmsByDay(90, $this->data->order->order_time);

        $overdueDay = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $days = $this->overdueDayPreg($v);
                if(!empty($days)){
                    $overdueDay[] = max($days);
                }
            }
        }

        if(empty($overdueDay)){
            return -1;
        }

        return round(array_sum($overdueDay) / count($overdueDay));
    }

    /**
     * 全平台-历史短信中的逾期金额之和
     * @return int
     */
    public function checkHistSumOfSMSLoanOverdueAmtTPF(){
        $data = $this->getUserAllSmsByDay();

        $money = 0;
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 全平台-历史短信中的逾期金额最大值
     * @return int
     */
    public function checkHistMaxOfSMSLoanOverdueAmtTPF(){
        $data = $this->getUserAllSmsByDay();

        $money = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 全平台-历史短信中的逾期金额最小值
     * @return int
     */
    public function checkHistMinOfSMSLoanOverdueAmtTPF(){
        $data = $this->getUserAllSmsByDay();

        $money = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return min($money);
    }

    /**
     * 全平台-历史短信中的逾期金额平均值
     * @return int
     */
    public function checkHistAvgOfSMSLoanOverdueAmtTPF(){
        $data = $this->getUserAllSmsByDay();

        $money = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 全平台-近7天内短信中的逾期金额之和
     * @return int
     */
    public function checkSumOfSMSLoanOverdueAmtLast7DaysTPF(){
        $data = $this->getUserAllSmsByDay(7, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 全平台-近7天内短信中的逾期金额最大值
     * @return int
     */
    public function checkMaxOfSMSLoanOverdueAmtLast7DaysTPF(){
        $data = $this->getUserAllSmsByDay(7, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 全平台-近7天内短信中的逾期金额最小值
     * @return int
     */
    public function checkMinOfSMSLoanOverdueAmtLast7DaysTPF(){
        $data = $this->getUserAllSmsByDay(7, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return min($money);
    }

    /**
     * 全平台-近7天内短信中的逾期金额平均值
     * @return int
     */
    public function checkAvgOfSMSLoanOverdueAmtLast7DaysTPF(){
        $data = $this->getUserAllSmsByDay(7, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 全平台-近30天内短信中的逾期金额之和
     * @return int
     */
    public function checkSumOfSMSLoanOverdueAmtLast30DaysTPF(){
        $data = $this->getUserAllSmsByDay(30, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 全平台-近30天内短信中的逾期金额最大值
     * @return int
     */
    public function checkMaxOfSMSLoanOverdueAmtLast30DaysTPF(){
        $data = $this->getUserAllSmsByDay(30, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 全平台-近30天内短信中的逾期金额最小值
     * @return int
     */
    public function checkMinOfSMSLoanOverdueAmtLast30DaysTPF(){
        $data = $this->getUserAllSmsByDay(30, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return min($money);
    }

    /**
     * 全平台-近30天内短信中的逾期金额平均值
     * @return int
     */
    public function checkAvgOfSMSLoanOverdueAmtLast30DaysTPF(){
        $data = $this->getUserAllSmsByDay(30, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 全平台-近60天内短信中的逾期金额之和
     * @return int
     */
    public function checkSumOfSMSLoanOverdueAmtLast60DaysTPF(){
        $data = $this->getUserAllSmsByDay(60, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 全平台-近60天内短信中的逾期金额最大值
     * @return int
     */
    public function checkMaxOfSMSLoanOverdueAmtLast60DaysTPF(){
        $data = $this->getUserAllSmsByDay(60, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 全平台-近60天内短信中的逾期金额最小值
     * @return int
     */
    public function checkMinOfSMSLoanOverdueAmtLast60DaysTPF(){
        $data = $this->getUserAllSmsByDay(60, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return min($money);
    }

    /**
     * 全平台-近60天内短信中的逾期金额平均值
     * @return int
     */
    public function checkAvgOfSMSLoanOverdueAmtLast60DaysTPF(){
        $data = $this->getUserAllSmsByDay(60, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 全平台-近90天内短信中的逾期金额之和
     * @return int
     */
    public function checkSumOfSMSLoanOverdueAmtLast90DaysTPF(){
        $data = $this->getUserAllSmsByDay(90, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 全平台-近90天内短信中的逾期金额最大值
     * @return int
     */
    public function checkMaxOfSMSLoanOverdueAmtLast90DaysTPF(){
        $data = $this->getUserAllSmsByDay(90, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 全平台-近90天内短信中的逾期金额最小值
     * @return int
     */
    public function checkMinOfSMSLoanOverdueAmtLast90DaysTPF(){
        $data = $this->getUserAllSmsByDay(90, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return min($money);
    }

    /**
     * 全平台-近90天内短信中的逾期金额平均值
     * @return int
     */
    public function checkAvgOfSMSLoanOverdueAmtLast90DaysTPF(){
        $data = $this->getUserAllSmsByDay(90, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $moneyArr = $this->amountPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 全平台-历史累计收到的发薪短信数量
     * @return int
     */
    public function checkHistSMSCntOfSalaryTPF(){
        $data = $this->getUserAllSmsByDay();

        $count = 0;
        foreach ($data as $v){
            if($this->smsSalary($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 全平台-近30天内收到的发薪短信数量
     * @return int
     */
    public function checkSMSCntOfSalaryLast30DaysTPF(){
        $data = $this->getUserAllSmsByDay(30, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->smsSalary($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 全平台-近90天内收到的发薪短信数量
     * @return int
     */
    public function checkSMSCntOfSalaryLast90DaysTPF(){
        $data = $this->getUserAllSmsByDay(90, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->smsSalary($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 全平台-近180天内收到的发薪短信数量
     * @return int
     */
    public function checkSMSCntOfSalaryLast180DaysTPF(){
        $data = $this->getUserAllSmsByDay(180, $this->data->order->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->smsSalary($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 全平台-历史累计收到发薪短信中的工资之和
     * @return int
     */
    public function checkSumOfHistSMSSalaryTPF(){
        $data = $this->getUserAllSmsByDay();

        $money = 0;
        foreach ($data as $v){
            if($this->smsSalary($v)){
                $moneyArr = $this->salaryPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 全平台-历史累计收到发薪短信中的工资最大值
     * @return int
     */
    public function checkMaxOfHistSMSSalaryTPF(){
        $data = $this->getUserAllSmsByDay();

        $money = [];
        foreach ($data as $v){
            if($this->smsSalary($v)){
                $moneyArr = $this->salaryPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 全平台-历史累计收到发薪短信中的工资最小值
     * @return int
     */
    public function checkMinOfHistSMSSalaryTPF()
    {
        $data = $this->getUserAllSmsByDay();

        $money = [];
        foreach ($data as $v) {
            if ($this->smsSalary($v)) {
                $moneyArr = $this->salaryPreg($v);
                if (!empty($moneyArr)) {
                    $money[] = max($moneyArr);
                }
            }
        }

        if (empty($money)) {
            return -1;
        }

        return min($money);
    }

    /**
     * 全平台-历史累计收到发薪短信中的工资平均值
     * @return int
     */
    public function checkAvgOfHistSMSSalaryTPF()
    {
        $data = $this->getUserAllSmsByDay();

        $money = [];
        foreach ($data as $v) {
            if ($this->smsSalary($v)) {
                $moneyArr = $this->salaryPreg($v);
                if (!empty($moneyArr)) {
                    $money[] = max($moneyArr);
                }
            }
        }

        if (empty($money)) {
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 全平台-近30天内收到发薪短信中的工资之和
     * @return int
     */
    public function checkSumOfSMSSalaryLast30DaysTPF(){
        $data = $this->getUserAllSmsByDay(30, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->smsSalary($v)){
                $moneyArr = $this->salaryPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 全平台-近30天内收到发薪短信中的工资最大值
     * @return int
     */
    public function checkMaxOfSMSSalaryLast30DaysTPF(){
        $data = $this->getUserAllSmsByDay(30, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->smsSalary($v)){
                $moneyArr = $this->salaryPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 全平台-近30天内收到发薪短信中的工资最小值
     * @return int
     */
    public function checkMinOfSMSSalaryLast30DaysTPF()
    {
        $data = $this->getUserAllSmsByDay(30, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v) {
            if ($this->smsSalary($v)) {
                $moneyArr = $this->salaryPreg($v);
                if (!empty($moneyArr)) {
                    $money[] = max($moneyArr);
                }
            }
        }

        if (empty($money)) {
            return -1;
        }

        return min($money);
    }

    /**
     * 全平台-近30天内收到发薪短信中的工资平均值
     * @return int
     */
    public function checkAvgOfSMSSalaryLast30DaysTPF()
    {
        $data = $this->getUserAllSmsByDay(30, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v) {
            if ($this->smsSalary($v)) {
                $moneyArr = $this->salaryPreg($v);
                if (!empty($moneyArr)) {
                    $money[] = max($moneyArr);
                }
            }
        }

        if (empty($money)) {
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 全平台-近90天内收到发薪短信中的工资之和
     * @return int
     */
    public function checkSumOfSMSSalaryLast90DaysTPF(){
        $data = $this->getUserAllSmsByDay(90, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->smsSalary($v)){
                $moneyArr = $this->salaryPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 全平台-近90天内收到发薪短信中的工资最大值
     * @return int
     */
    public function checkMaxOfSMSSalaryLast90DaysTPF(){
        $data = $this->getUserAllSmsByDay(90, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->smsSalary($v)){
                $moneyArr = $this->salaryPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 全平台-近90天内收到发薪短信中的工资最小值
     * @return int
     */
    public function checkMinOfSMSSalaryLast90DaysTPF()
    {
        $data = $this->getUserAllSmsByDay(90, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v) {
            if ($this->smsSalary($v)) {
                $moneyArr = $this->salaryPreg($v);
                if (!empty($moneyArr)) {
                    $money[] = max($moneyArr);
                }
            }
        }

        if (empty($money)) {
            return -1;
        }

        return min($money);
    }

    /**
     * 全平台-近90天内收到发薪短信中的工资平均值
     * @return int
     */
    public function checkAvgOfSMSSalaryLast90DaysTPF()
    {
        $data = $this->getUserAllSmsByDay(90, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v) {
            if ($this->smsSalary($v)) {
                $moneyArr = $this->salaryPreg($v);
                if (!empty($moneyArr)) {
                    $money[] = max($moneyArr);
                }
            }
        }

        if (empty($money)) {
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 全平台-近180天内收到发薪短信中的工资之和
     * @return int
     */
    public function checkSumOfSMSSalaryLast180DaysTPF(){
        $data = $this->getUserAllSmsByDay(180, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->smsSalary($v)){
                $moneyArr = $this->salaryPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 全平台-近180天内收到发薪短信中的工资最大值
     * @return int
     */
    public function checkMaxOfSMSSalaryLast180DaysTPF(){
        $data = $this->getUserAllSmsByDay(180, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->smsSalary($v)){
                $moneyArr = $this->salaryPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 全平台-近180天内收到发薪短信中的工资最小值
     * @return int
     */
    public function checkMinOfSMSSalaryLast180DaysTPF()
    {
        $data = $this->getUserAllSmsByDay(180, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v) {
            if ($this->smsSalary($v)) {
                $moneyArr = $this->salaryPreg($v);
                if (!empty($moneyArr)) {
                    $money[] = max($moneyArr);
                }
            }
        }

        if (empty($money)) {
            return -1;
        }

        return min($money);
    }

    /**
     * 全平台-近180天内收到发薪短信中的工资平均值
     * @return int
     */
    public function checkAvgOfSMSSalaryLast180DaysTPF()
    {
        $data = $this->getUserAllSmsByDay(180, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v) {
            if ($this->smsSalary($v)) {
                $moneyArr = $this->salaryPreg($v);
                if (!empty($moneyArr)) {
                    $money[] = max($moneyArr);
                }
            }
        }

        if (empty($money)) {
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 全平台-历史累计收到的发薪短信中的账户余额之和
     * @return int
     */
    public function checkSumOfHistSMSSalaryAvlBalTPF(){
        $data = $this->getUserAllSmsByDay();

        $money = 0;
        foreach ($data as $v){
            if($this->smsSalaryAvlBal($v)){
                $moneyArr = $this->salaryAvlBalPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 全平台-历史累计收到的发薪短信中的账户余额最大值
     * @return int
     */
    public function checkMaxOfHistSMSSalaryAvlBalTPF(){
        $data = $this->getUserAllSmsByDay();

        $money = [];
        foreach ($data as $v){
            if($this->smsSalaryAvlBal($v)){
                $moneyArr = $this->salaryAvlBalPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 全平台-历史累计收到的发薪短信中的账户余额最小值
     * @return int
     */
    public function checkMinOfHistSMSSalaryAvlBalTPF()
    {
        $data = $this->getUserAllSmsByDay();

        $money = [];
        foreach ($data as $v) {
            if ($this->smsSalaryAvlBal($v)) {
                $moneyArr = $this->salaryAvlBalPreg($v);
                if (!empty($moneyArr)) {
                    $money[] = max($moneyArr);
                }
            }
        }

        if (empty($money)) {
            return -1;
        }

        return min($money);
    }

    /**
     * 全平台-历史累计收到的发薪短信中的账户余额平均值
     * @return int
     */
    public function checkAvgOfHistSMSSalaryAvlBalTPF()
    {
        $data = $this->getUserAllSmsByDay();

        $money = [];
        foreach ($data as $v) {
            if ($this->smsSalaryAvlBal($v)) {
                $moneyArr = $this->salaryAvlBalPreg($v);
                if (!empty($moneyArr)) {
                    $money[] = max($moneyArr);
                }
            }
        }

        if (empty($money)) {
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 全平台-近30天内收到的发薪短信中的账户余额之和
     * @return int
     */
    public function checkSumOfSMSSalaryAvlBalLast30DaysTPF(){
        $data = $this->getUserAllSmsByDay(30, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->smsSalaryAvlBal($v)){
                $moneyArr = $this->salaryAvlBalPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 全平台-近30天内收到的发薪短信中的账户余额最大值
     * @return int
     */
    public function checkMaxOfSMSSalaryAvlBalLast30DaysTPF(){
        $data = $this->getUserAllSmsByDay(30, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->smsSalaryAvlBal($v)){
                $moneyArr = $this->salaryAvlBalPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 全平台-近30天内收到的发薪短信中的账户余额最小值
     * @return int
     */
    public function checkMinOfSMSSalaryAvlBalLast30DaysTPF()
    {
        $data = $this->getUserAllSmsByDay(30, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v) {
            if ($this->smsSalaryAvlBal($v)) {
                $moneyArr = $this->salaryAvlBalPreg($v);
                if (!empty($moneyArr)) {
                    $money[] = max($moneyArr);
                }
            }
        }

        if (empty($money)) {
            return -1;
        }

        return min($money);
    }

    /**
     * 全平台-近30天内收到的发薪短信中的账户余额平均值
     * @return int
     */
    public function checkAvgOfSMSSalaryAvlBalLast30DaysTPF()
    {
        $data = $this->getUserAllSmsByDay(30, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v) {
            if ($this->smsSalaryAvlBal($v)) {
                $moneyArr = $this->salaryAvlBalPreg($v);
                if (!empty($moneyArr)) {
                    $money[] = max($moneyArr);
                }
            }
        }

        if (empty($money)) {
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 全平台-近90天内收到的发薪短信中的账户余额之和
     * @return int
     */
    public function checkSumOfSMSSalaryAvlBalLast90DaysTPF(){
        $data = $this->getUserAllSmsByDay(90, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->smsSalaryAvlBal($v)){
                $moneyArr = $this->salaryAvlBalPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 全平台-近90天内收到的发薪短信中的账户余额最大值
     * @return int
     */
    public function checkMaxOfSMSSalaryAvlBalLast90DaysTPF(){
        $data = $this->getUserAllSmsByDay(90, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->smsSalaryAvlBal($v)){
                $moneyArr = $this->salaryAvlBalPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 全平台-近90天内收到的发薪短信中的账户余额最小值
     * @return int
     */
    public function checkMinOfSMSSalaryAvlBalLast90DaysTPF()
    {
        $data = $this->getUserAllSmsByDay(90, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v) {
            if ($this->smsSalaryAvlBal($v)) {
                $moneyArr = $this->salaryAvlBalPreg($v);
                if (!empty($moneyArr)) {
                    $money[] = max($moneyArr);
                }
            }
        }

        if (empty($money)) {
            return -1;
        }

        return min($money);
    }

    /**
     * 全平台-近90天内收到的发薪短信中的账户余额平均值
     * @return int
     */
    public function checkAvgOfSMSSalaryAvlBalLast90DaysTPF()
    {
        $data = $this->getUserAllSmsByDay(90, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v) {
            if ($this->smsSalaryAvlBal($v)) {
                $moneyArr = $this->salaryAvlBalPreg($v);
                if (!empty($moneyArr)) {
                    $money[] = max($moneyArr);
                }
            }
        }

        if (empty($money)) {
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 全平台-近180天内收到的发薪短信中的账户余额之和
     * @return int
     */
    public function checkSumOfSMSSalaryAvlBalLast180DaysTPF(){
        $data = $this->getUserAllSmsByDay(180, $this->data->order->order_time);

        $money = 0;
        foreach ($data as $v){
            if($this->smsSalaryAvlBal($v)){
                $moneyArr = $this->salaryAvlBalPreg($v);
                if(!empty($moneyArr)){
                    $money += max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return $money;
    }

    /**
     * 全平台-近180天内收到的发薪短信中的账户余额最大值
     * @return int
     */
    public function checkMaxOfSMSSalaryAvlBalLast180DaysTPF(){
        $data = $this->getUserAllSmsByDay(180, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v){
            if($this->smsSalaryAvlBal($v)){
                $moneyArr = $this->salaryAvlBalPreg($v);
                if(!empty($moneyArr)){
                    $money[] = max($moneyArr);
                }
            }
        }

        if(empty($money)){
            return -1;
        }

        return max($money);
    }

    /**
     * 全平台-近180天内收到的发薪短信中的账户余额最小值
     * @return int
     */
    public function checkMinOfSMSSalaryAvlBalLast180DaysTPF()
    {
        $data = $this->getUserAllSmsByDay(180, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v) {
            if ($this->smsSalaryAvlBal($v)) {
                $moneyArr = $this->salaryAvlBalPreg($v);
                if (!empty($moneyArr)) {
                    $money[] = max($moneyArr);
                }
            }
        }

        if (empty($money)) {
            return -1;
        }

        return min($money);
    }

    /**
     * 全平台-近180天内收到的发薪短信中的账户余额平均值
     * @return int
     */
    public function checkAvgOfSMSSalaryAvlBalLast180DaysTPF()
    {
        $data = $this->getUserAllSmsByDay(180, $this->data->order->order_time);

        $money = [];
        foreach ($data as $v) {
            if ($this->smsSalaryAvlBal($v)) {
                $moneyArr = $this->salaryAvlBalPreg($v);
                if (!empty($moneyArr)) {
                    $money[] = max($moneyArr);
                }
            }
        }

        if (empty($money)) {
            return -1;
        }

        return round(array_sum($money) / count($money));
    }

    /**
     * 活体检测的来源
     * @return int
     */
    public function checkSourceOfLivenessDetect(){
        if(!$this->data->userFrReport || $this->data->userFrReport->report_status == 0){
            return -1;
        }

        if($this->data->userFrReport->type == UserCreditReportFrLiveness::SOURCE_ACCUAUTH){
            return 1;
        }

        if($this->data->userFrReport->type == UserCreditReportFrLiveness::SOURCE_ADVANCE){
            return 2;
        }

        return -1;
    }

    /**
     * 获取该aadhaar下所有用户ID
     * @param $aadhaar
     * @return array|mixed
     */
    protected function getAadhaarUserIds($aadhaar)
    {
        $key = "{$aadhaar}";
        if (isset($this->aadhaarUserIds[$key])) {
            return $this->aadhaarUserIds[$key];
        } else {
            $userIds = ArrayHelper::getColumn(
                LoanPerson::find()->select(['id'])
                    ->where(['aadhaar_md5' => $aadhaar])
                    ->asArray()->all(),
                'id');
            return $this->aadhaarUserIds[$key] = $userIds;
        }
    }

    /**
     * 获取该aadhaar下所有用户ID loan
     * @param $aadhaar
     * @return array|mixed
     */
    protected function getAadhaarOtherUserIds($aadhaar)
    {
        $key = "{$aadhaar}";
        if (isset($this->aadhaarOtherUserIds[$key])) {
            return $this->aadhaarOtherUserIds[$key];
        } else {
            $userIds = ArrayHelper::getColumn(
                LoanPerson::find()->select(['id'])
                    ->where(['aadhaar_md5' => $aadhaar])
                    ->asArray()->all(Yii::$app->db_loan),
                'id');
            return $this->aadhaarOtherUserIds[$key] = $userIds;
        }
    }

    /**
     * 该手机号历史下单环节关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkPhoneHIsOrderMatchSMDeviceIDCnt(){
        return ClientInfoLog::find()->where(['event' => ClientInfoLog::EVENT_APPLY_ORDER, 'user_id' => $this->data->loanPerson->id])->count('DISTINCT szlm_query_id');
    }

    /**
     * 近30天内该手机号下单环节关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkPhoneLast30dOrderMatchSMdeviceIDCnt(){
        $begin_time = $this->data->order->order_time - 30 * 86400;

        $count = ClientInfoLog::find()
            ->where(['event' => ClientInfoLog::EVENT_APPLY_ORDER, 'user_id' => $this->data->loanPerson->id])
            ->andWhere(['>=', 'created_at', $begin_time])
            ->count('DISTINCT szlm_query_id');

        return $count;
    }

    /**
     * 近60天内该手机号下单环节关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkPhoneLast60dOrderMatchSMdeviceIDCnt(){
        $begin_time = $this->data->order->order_time - 60 * 86400;

        $count = ClientInfoLog::find()
            ->where(['event' => ClientInfoLog::EVENT_APPLY_ORDER, 'user_id' => $this->data->loanPerson->id])
            ->andWhere(['>=', 'created_at', $begin_time])
            ->count('DISTINCT szlm_query_id');

        return $count;
    }

    /**
     * 近90天内该手机号下单环节关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkPhoneLast90dOrderMatchSMdeviceIDCnt(){
        $begin_time = $this->data->order->order_time - 90 * 86400;

        $count = ClientInfoLog::find()
            ->where(['event' => ClientInfoLog::EVENT_APPLY_ORDER, 'user_id' => $this->data->loanPerson->id])
            ->andWhere(['>=', 'created_at', $begin_time])
            ->count('DISTINCT szlm_query_id');

        return $count;
    }

    /**
     * 该数盟设备ID历史下单环节关联过的不同手机号数量
     * @return int
     */
    public function checkSMDeviceIDHisOrderMatchPhoneCnt(){
        if(empty($this->data->order->did)){
            return -1;
        }

        $data = UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['p.phone'])
            ->where(['o.did' => $this->data->order->did, 'p.source_id' => $this->data->loanPerson->source_id])
            ->groupBy(['p.phone'])
            ->asArray()
            ->all();

        return count($data);
    }

    /**
     * 近30天内该数盟设备ID下单环节关联过的不同手机号数量
     * @return int
     */
    public function checkSMDeviceIDLast30dOrderMatchPhoneCnt(){
        if(empty($this->data->order->did)){
            return -1;
        }

        $begin_time = $this->data->order->order_time - 30 * 86400;
        $data = UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['p.phone'])
            ->where(['o.did' => $this->data->order->did, 'p.source_id' => $this->data->loanPerson->source_id])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->groupBy(['p.phone'])
            ->asArray()
            ->all();

        return count($data);
    }

    /**
     * 近60天内该数盟设备ID下单环节关联过的不同手机号数量
     * @return int
     */
    public function checkSMDeviceIDLast60dOrderMatchPhoneCnt(){
        if(empty($this->data->order->did)){
            return -1;
        }

        $begin_time = $this->data->order->order_time - 60 * 86400;
        $data = UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['p.phone'])
            ->where(['o.did' => $this->data->order->did, 'p.source_id' => $this->data->loanPerson->source_id])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->groupBy(['p.phone'])
            ->asArray()
            ->all();

        return count($data);
    }

    /**
     * 近90天内该数盟设备ID下单环节关联过的不同手机号数量
     * @return int
     */
    public function checkSMDeviceIDLast90dOrderMatchPhoneCnt(){
        if(empty($this->data->order->did)){
            return -1;
        }

        $begin_time = $this->data->order->order_time - 90 * 86400;
        $data = UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['p.phone'])
            ->where(['o.did' => $this->data->order->did, 'p.source_id' => $this->data->loanPerson->source_id])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->groupBy(['p.phone'])
            ->asArray()
            ->all();

        return count($data);
    }

    /**
     * 总平台该手机号历史成功关联过的不同Pan卡号数量
     * @return int
     */
    public function checkPhoneHisSuccessMatchPanCntTotPlatform(){
        $data = ArrayHelper::getColumn(
            LoanPerson::find()
                ->select(['pan_code'])
                ->where(['phone' => $this->data->loanPerson->phone])
                ->andWhere(['is not','pan_code',null])
                ->asArray()->all(),
            'pan_code');

        $dataOther = ArrayHelper::getColumn(
            LoanPerson::find()
                ->select(['pan_code'])
                ->where(['phone' => $this->data->loanPerson->phone])
                ->andWhere(['is not','pan_code',null])
                ->asArray()->all(Yii::$app->db_loan),
            'pan_code');
        return count(array_unique(array_merge($data, $dataOther)));
    }

    /**
     * 总平台该手机号历史成功关联过的不同Aadhaar卡号数量
     * @return int
     */
    public function checkPhoneHisSuccessMatchAadhaarCntTotPlatform(){
        $data = ArrayHelper::getColumn(
            LoanPerson::find()
                ->select(['aadhaar_md5'])
                ->where(['phone' => $this->data->loanPerson->phone])
                ->andWhere(['is not','aadhaar_md5',null])
                ->asArray()->all(),
            'aadhaar_md5');

        $dataOther = ArrayHelper::getColumn(
            LoanPerson::find()
                ->select(['aadhaar_md5'])
                ->where(['phone' => $this->data->loanPerson->phone])
                ->andWhere(['is not','aadhaar_md5',null])
                ->asArray()->all(Yii::$app->db_loan),
            'aadhaar_md5');
        return count(array_unique(array_merge($data, $dataOther)));
    }

    /**
     * 总平台该手机号历史下单环节关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkPhoneHIsOrderMatchSMDeviceIDCntTotPlatform(){
        $userIds = $this->getPhoneAllUserIds($this->data->loanPerson->phone);
        $userIds_other = $this->getLoanPhoneUserIds($this->data->loanPerson->phone);
        $data = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $userIds,'event' => ClientInfoLog::EVENT_APPLY_ORDER])
                ->groupBy(['szlm_query_id'])
                ->all(),
            'szlm_query_id');

        $dataOther = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $userIds_other,'event' => ClientInfoLog::EVENT_APPLY_ORDER])
                ->groupBy(['szlm_query_id'])
                ->all(Yii::$app->db_loan),
            'szlm_query_id');
        return count(array_unique(array_merge($data, $dataOther)));
    }

    /**
     * 总平台近30天内该手机号下单环节关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkPhoneLast30dOrderMatchSMdeviceIDCntTotPlatform(){
        $begin_time = $this->data->order->order_time - 30 * 86400;
        $userIds = $this->getPhoneAllUserIds($this->data->loanPerson->phone);
        $userIds_other = $this->getLoanPhoneUserIds($this->data->loanPerson->phone);
        $data = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $userIds,'event' => ClientInfoLog::EVENT_APPLY_ORDER])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['szlm_query_id'])
                ->all(),
            'szlm_query_id');

        $dataOther = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $userIds_other,'event' => ClientInfoLog::EVENT_APPLY_ORDER])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['szlm_query_id'])
                ->all(Yii::$app->db_loan),
            'szlm_query_id');
        return count(array_unique(array_merge($data, $dataOther)));
    }

    /**
     * 总平台近60天内该手机号下单环节关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkPhoneLast60dOrderMatchSMdeviceIDCntTotPlatform(){
        $begin_time = $this->data->order->order_time - 60 * 86400;
        $userIds = $this->getPhoneAllUserIds($this->data->loanPerson->phone);
        $userIds_other = $this->getLoanPhoneUserIds($this->data->loanPerson->phone);
        $data = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $userIds,'event' => ClientInfoLog::EVENT_APPLY_ORDER])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['szlm_query_id'])
                ->all(),
            'szlm_query_id');

        $dataOther = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $userIds_other,'event' => ClientInfoLog::EVENT_APPLY_ORDER])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['szlm_query_id'])
                ->all(Yii::$app->db_loan),
            'szlm_query_id');
        return count(array_unique(array_merge($data, $dataOther)));
    }

    /**
     * 总平台近90天内该手机号下单环节关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkPhoneLast90dOrderMatchSMdeviceIDCntTotPlatform(){
        $begin_time = $this->data->order->order_time - 90 * 86400;
        $userIds = $this->getPhoneAllUserIds($this->data->loanPerson->phone);
        $userIds_other = $this->getLoanPhoneUserIds($this->data->loanPerson->phone);
        $data = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $userIds,'event' => ClientInfoLog::EVENT_APPLY_ORDER])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['szlm_query_id'])
                ->all(),
            'szlm_query_id');

        $dataOther = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $userIds_other,'event' => ClientInfoLog::EVENT_APPLY_ORDER])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['szlm_query_id'])
                ->all(Yii::$app->db_loan),
            'szlm_query_id');
        return count(array_unique(array_merge($data, $dataOther)));
    }

    /**
     * 总平台该Pan卡号历史成功关联过的不同手机号数量
     * @return int
     */
    public function checkPanHisSuccessMatchPhoneCntTotPlatform()
    {
        $pan_code = $this->data->loanPerson->pan_code;
        $data = ArrayHelper::getColumn(
            LoanPerson::find()
                ->select(['phone'])
                ->where(['pan_code' => $pan_code])
                ->asArray()->all(),
            'phone');

        $dataOther = ArrayHelper::getColumn(
            LoanPerson::find()
                ->select(['phone'])
                ->where(['pan_code' => $pan_code])
                ->asArray()->all(Yii::$app->db_loan),
            'phone');
        return count(array_unique(array_merge($data, $dataOther)));
    }

    /**
     * 总平台该Pan卡号历史成功关联过的不同Aadhaar卡号数量
     * @return int
     */
    public function checkPanHisSuccessMatchAadhaarCntTotPlatform()
    {
        $pan_code = $this->data->loanPerson->pan_code;
        $data = ArrayHelper::getColumn(
            LoanPerson::find()
                ->select(['aadhaar_md5'])
                ->where(['pan_code' => $pan_code])
                ->andWhere(['is not', 'aadhaar_md5', null])
                ->asArray()->all(),
            'aadhaar_md5');

        $dataOther = ArrayHelper::getColumn(
            LoanPerson::find()
                ->select(['aadhaar_md5'])
                ->where(['pan_code' => $pan_code])
                ->andWhere(['is not', 'aadhaar_md5', null])
                ->asArray()->all(Yii::$app->db_loan),
            'aadhaar_md5');
        return count(array_unique(array_merge($data, $dataOther)));
    }

    /**
     * 总平台该Pan卡号历史下单环节关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkPanHisOrderMatchSMDeviceIDCntTotPlatform(){
        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $userIds_other = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);
        $data = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $userIds,'event' => ClientInfoLog::EVENT_APPLY_ORDER])
                ->groupBy(['szlm_query_id'])
                ->all(),
            'szlm_query_id');

        $dataOther = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $userIds_other,'event' => ClientInfoLog::EVENT_APPLY_ORDER])
                ->groupBy(['szlm_query_id'])
                ->all(Yii::$app->db_loan),
            'szlm_query_id');
        return count(array_unique(array_merge($data, $dataOther)));
    }

    /**
     * 总平台近30天内该Pan卡号下单环节关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkPanLast30dOrderMatchSMdeviceIDCntTotPlatform(){
        $begin_time = $this->data->order->order_time - 30 * 86400;
        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $userIds_other = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);
        $data = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $userIds,'event' => ClientInfoLog::EVENT_APPLY_ORDER])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['szlm_query_id'])
                ->all(),
            'szlm_query_id');

        $dataOther = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $userIds_other,'event' => ClientInfoLog::EVENT_APPLY_ORDER])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['szlm_query_id'])
                ->all(Yii::$app->db_loan),
            'szlm_query_id');
        return count(array_unique(array_merge($data, $dataOther)));
    }

    /**
     * 总平台近60天内该Pan卡号下单环节关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkPanLast60dOrderMatchSMdeviceIDCntTotPlatform(){
        $begin_time = $this->data->order->order_time - 60 * 86400;
        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $userIds_other = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);
        $data = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $userIds,'event' => ClientInfoLog::EVENT_APPLY_ORDER])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['szlm_query_id'])
                ->all(),
            'szlm_query_id');

        $dataOther = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $userIds_other,'event' => ClientInfoLog::EVENT_APPLY_ORDER])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['szlm_query_id'])
                ->all(Yii::$app->db_loan),
            'szlm_query_id');
        return count(array_unique(array_merge($data, $dataOther)));
    }

    /**
     * 总平台近90天内该Pan卡号下单环节关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkPanLast90dOrderMatchSMdeviceIDCntTotPlatform(){
        $begin_time = $this->data->order->order_time - 90 * 86400;
        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $userIds_other = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);
        $data = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $userIds,'event' => ClientInfoLog::EVENT_APPLY_ORDER])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['szlm_query_id'])
                ->all(),
            'szlm_query_id');

        $dataOther = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $userIds_other,'event' => ClientInfoLog::EVENT_APPLY_ORDER])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['szlm_query_id'])
                ->all(Yii::$app->db_loan),
            'szlm_query_id');
        return count(array_unique(array_merge($data, $dataOther)));
    }

    /**
     * 总平台该Aadhaar卡号历史成功关联过的不同Pan卡号数量
     * @return int
     */
    public function checkAadhaarHisSuccessMatchPanCntTotPlatform()
    {
        $aadhaar = $this->data->loanPerson->aadhaar_md5;
        $data = ArrayHelper::getColumn(
            LoanPerson::find()
                ->select(['pan_code'])
                ->where(['aadhaar_md5' => $aadhaar])
                ->andWhere(['is not', 'pan_code', null])
                ->asArray()->all(),
            'pan_code');

        $dataOther = ArrayHelper::getColumn(
            LoanPerson::find()
                ->select(['pan_code'])
                ->where(['aadhaar_md5' => $aadhaar])
                ->andWhere(['is not', 'pan_code', null])
                ->asArray()->all(Yii::$app->db_loan),
            'pan_code');
        return count(array_unique(array_merge($data, $dataOther)));
    }

    /**
     * 总平台该Aadhaar卡号成功环节关联过的不同手机号数量
     * @return int
     */
    public function checkAadhaarHisSuccessMatchPhoneCntTotPlatform()
    {
        $aadhaar = $this->data->loanPerson->aadhaar_md5;
        $data = ArrayHelper::getColumn(
            LoanPerson::find()
                ->select(['phone'])
                ->where(['aadhaar_md5' => $aadhaar])
                ->asArray()->all(),
            'phone');

        $dataOther = ArrayHelper::getColumn(
            LoanPerson::find()
                ->select(['phone'])
                ->where(['aadhaar_md5' => $aadhaar])
                ->asArray()->all(Yii::$app->db_loan),
            'phone');
        return count(array_unique(array_merge($data, $dataOther)));
    }

    /**
     * 总平台该Aadhaar卡号历史下单环节关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkAadhaarHisOrderMatchSMDeviceIDCntTotPlatform(){
        $userIds = $this->getAadhaarUserIds($this->data->loanPerson->aadhaar_md5);
        $userIds_other = $this->getAadhaarOtherUserIds($this->data->loanPerson->aadhaar_md5);
        $data = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $userIds,'event' => ClientInfoLog::EVENT_APPLY_ORDER])
                ->groupBy(['szlm_query_id'])
                ->all(),
            'szlm_query_id');

        $dataOther = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $userIds_other,'event' => ClientInfoLog::EVENT_APPLY_ORDER])
                ->groupBy(['szlm_query_id'])
                ->all(Yii::$app->db_loan),
            'szlm_query_id');
        return count(array_unique(array_merge($data, $dataOther)));
    }

    /**
     * 总平台近30天内该Aadhaar卡号下单环节关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkAadhaarLast30dOrderMatchSMDeviceIDCntTotPlatform(){
        $begin_time = $this->data->order->order_time - 30 * 86400;
        $userIds = $this->getAadhaarUserIds($this->data->loanPerson->aadhaar_md5);
        $userIds_other = $this->getAadhaarOtherUserIds($this->data->loanPerson->aadhaar_md5);
        $data = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $userIds,'event' => ClientInfoLog::EVENT_APPLY_ORDER])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['szlm_query_id'])
                ->all(),
            'szlm_query_id');

        $dataOther = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $userIds_other,'event' => ClientInfoLog::EVENT_APPLY_ORDER])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['szlm_query_id'])
                ->all(Yii::$app->db_loan),
            'szlm_query_id');
        return count(array_unique(array_merge($data, $dataOther)));
    }

    /**
     * 总平台近60天内该Aadhaar卡号下单环节关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkAadhaarLast60dOrderMatchSMDeviceIDCntTotPlatform(){
        $begin_time = $this->data->order->order_time - 60 * 86400;
        $userIds = $this->getAadhaarUserIds($this->data->loanPerson->aadhaar_md5);
        $userIds_other = $this->getAadhaarOtherUserIds($this->data->loanPerson->aadhaar_md5);
        $data = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $userIds,'event' => ClientInfoLog::EVENT_APPLY_ORDER])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['szlm_query_id'])
                ->all(),
            'szlm_query_id');

        $dataOther = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $userIds_other,'event' => ClientInfoLog::EVENT_APPLY_ORDER])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['szlm_query_id'])
                ->all(Yii::$app->db_loan),
            'szlm_query_id');
        return count(array_unique(array_merge($data, $dataOther)));
    }

    /**
     * 总平台近90天内该Aadhaar卡号下单环节关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkAadhaarLast90dOrderMatchSMDeviceIDCntTotPlatform(){
        $begin_time = $this->data->order->order_time - 90 * 86400;
        $userIds = $this->getAadhaarUserIds($this->data->loanPerson->aadhaar_md5);
        $userIds_other = $this->getAadhaarOtherUserIds($this->data->loanPerson->aadhaar_md5);
        $data = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $userIds,'event' => ClientInfoLog::EVENT_APPLY_ORDER])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['szlm_query_id'])
                ->all(),
            'szlm_query_id');

        $dataOther = ArrayHelper::getColumn(
            ClientInfoLog::find()
                ->select(['szlm_query_id'])
                ->where(['user_id' => $userIds_other,'event' => ClientInfoLog::EVENT_APPLY_ORDER])
                ->andWhere(['>=', 'created_at', $begin_time])
                ->groupBy(['szlm_query_id'])
                ->all(Yii::$app->db_loan),
            'szlm_query_id');
        return count(array_unique(array_merge($data, $dataOther)));
    }

    /**
     * 总平台该数盟设备ID历史下单环节关联过的不同手机号数量
     * @return int
     */
    public function checkSMDeviceIDHisOrderMatchPhoneCntTotPlatform(){
        if(empty($this->data->order->did)){
            return -1;
        }

        $data = ArrayHelper::getColumn(
            UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['p.phone'])
            ->where(['o.did' => $this->data->order->did])
            ->groupBy(['p.phone'])
            ->asArray()
            ->all(),
            'phone');

        $dataOther = ArrayHelper::getColumn(
            UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['p.phone'])
            ->where(['o.did' => $this->data->order->did])
            ->groupBy(['p.phone'])
            ->asArray()
            ->all(Yii::$app->db_loan),
            'phone');

        return count(array_unique(array_merge($data, $dataOther)));
    }

    /**
     * 近30天内该数盟设备ID下单环节关联过的不同手机号数量
     * @return int
     */
    public function checkSMDeviceIDLast30dOrderMatchPhoneCntTotPlatform(){
        if(empty($this->data->order->did)){
            return -1;
        }

        $begin_time = $this->data->order->order_time - 30 * 86400;
        $data = ArrayHelper::getColumn(
            UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['p.phone'])
            ->where(['o.did' => $this->data->order->did])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->groupBy(['p.phone'])
            ->asArray()
            ->all(),
            'phone');

        $dataOther = ArrayHelper::getColumn(
            UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['p.phone'])
            ->where(['o.did' => $this->data->order->did])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->groupBy(['p.phone'])
            ->asArray()
            ->all(Yii::$app->db_loan),
            'phone');

        return count(array_unique(array_merge($data, $dataOther)));
    }

    /**
     * 近60天内该数盟设备ID下单环节关联过的不同手机号数量
     * @return int
     */
    public function checkSMDeviceIDLast60dOrderMatchPhoneCntTotPlatform(){
        if(empty($this->data->order->did)){
            return -1;
        }

        $begin_time = $this->data->order->order_time - 60 * 86400;
        $data = ArrayHelper::getColumn(
            UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['p.phone'])
            ->where(['o.did' => $this->data->order->did])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->groupBy(['p.phone'])
            ->asArray()
            ->all(),
            'phone');

        $dataOther = ArrayHelper::getColumn(
            UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['p.phone'])
            ->where(['o.did' => $this->data->order->did])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->groupBy(['p.phone'])
            ->asArray()
            ->all(Yii::$app->db_loan),
            'phone');

        return count(array_unique(array_merge($data, $dataOther)));
    }

    /**
     * 近90天内该数盟设备ID下单环节关联过的不同手机号数量
     * @return int
     */
    public function checkSMDeviceIDLast90dOrderMatchPhoneCntTotPlatform(){
        if(empty($this->data->order->did)){
            return -1;
        }

        $begin_time = $this->data->order->order_time - 90 * 86400;
        $data = ArrayHelper::getColumn(
            UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['p.phone'])
            ->where(['o.did' => $this->data->order->did])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->groupBy(['p.phone'])
            ->asArray()
            ->all(),
            'phone');

        $dataOther = ArrayHelper::getColumn(
            UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['p.phone'])
            ->where(['o.did' => $this->data->order->did])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->groupBy(['p.phone'])
            ->asArray()
            ->all(Yii::$app->db_loan),
            'phone');

        return count(array_unique(array_merge($data, $dataOther)));
    }

    /**
     * 总平台该数盟设备ID历史下单环节关联过的不同Pan卡号数量
     * @return int
     */
    public function checkSMDeviceIDHisOrderMatchPanCntTotPlatform(){
        if(empty($this->data->order->did)){
            return -1;
        }

        $data = ArrayHelper::getColumn(
            UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['p.pan_code'])
            ->where(['o.did' => $this->data->order->did])
            ->andWhere(['is not', 'p.pan_code', null])
            ->groupBy(['p.pan_code'])
            ->asArray()
            ->all(),
            'pan_code');

        $dataOther = ArrayHelper::getColumn(
            UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['p.pan_code'])
            ->where(['o.did' => $this->data->order->did])
            ->andWhere(['is not', 'p.pan_code', null])
            ->groupBy(['p.pan_code'])
            ->asArray()
            ->all(Yii::$app->db_loan),
            'pan_code');

        return count(array_unique(array_merge($data, $dataOther)));
    }

    /**
     * 总平台近30天内该数盟设备ID下单环节关联过的不同Pan卡号数量
     * @return int
     */
    public function checkSMDeviceIDLast30dOrderMatchPanCntTotPlatform(){
        if(empty($this->data->order->did)){
            return -1;
        }

        $begin_time = $this->data->order->order_time - 30 * 86400;
        $data = ArrayHelper::getColumn(
            UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['p.pan_code'])
            ->where(['o.did' => $this->data->order->did])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['is not', 'p.pan_code', null])
            ->groupBy(['p.pan_code'])
            ->asArray()
            ->all(),
            'pan_code');

        $dataOther = ArrayHelper::getColumn(
            UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['p.pan_code'])
            ->where(['o.did' => $this->data->order->did])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['is not', 'p.pan_code', null])
            ->groupBy(['p.pan_code'])
            ->asArray()
            ->all(Yii::$app->db_loan),
            'pan_code');

        return count(array_unique(array_merge($data, $dataOther)));
    }

    /**
     * 总平台近60天内该数盟设备ID下单环节关联过的不同Pan卡号数量
     * @return int
     */
    public function checkSMDeviceIDLast60dOrderMatchPanCntTotPlatform(){
        if(empty($this->data->order->did)){
            return -1;
        }

        $begin_time = $this->data->order->order_time - 60 * 86400;
        $data = ArrayHelper::getColumn(
            UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['p.pan_code'])
            ->where(['o.did' => $this->data->order->did])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['is not', 'p.pan_code', null])
            ->groupBy(['p.pan_code'])
            ->asArray()
            ->all(),
            'pan_code');

        $dataOther = ArrayHelper::getColumn(
            UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['p.pan_code'])
            ->where(['o.did' => $this->data->order->did])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['is not', 'p.pan_code', null])
            ->groupBy(['p.pan_code'])
            ->asArray()
            ->all(Yii::$app->db_loan),
            'pan_code');

        return count(array_unique(array_merge($data, $dataOther)));
    }

    /**
     * 总平台近90天内该数盟设备ID下单环节关联过的不同Pan卡号数量
     * @return int
     */
    public function checkSMDeviceIDLast90dOrderMatchPanCntTotPlatform(){
        if(empty($this->data->order->did)){
            return -1;
        }

        $begin_time = $this->data->order->order_time - 90 * 86400;
        $data = ArrayHelper::getColumn(
            UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['p.pan_code'])
            ->where(['o.did' => $this->data->order->did])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['is not', 'p.pan_code', null])
            ->groupBy(['p.pan_code'])
            ->asArray()
            ->all(),
            'pan_code');

        $dataOther = ArrayHelper::getColumn(
            UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['p.pan_code'])
            ->where(['o.did' => $this->data->order->did])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['is not', 'p.pan_code', null])
            ->groupBy(['p.pan_code'])
            ->asArray()
            ->all(Yii::$app->db_loan),
            'pan_code');

        return count(array_unique(array_merge($data, $dataOther)));
    }

    /**
     * 总平台该数盟设备ID历史下单环节关联过的不同Aadhaar卡号数量
     * @return int
     */
    public function checkSMDeviceIDHisOrderMatchAadhaarCntTotPlatform(){
        if(empty($this->data->order->did)){
            return -1;
        }

        $data = ArrayHelper::getColumn(
            UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['p.aadhaar_md5'])
            ->where(['o.did' => $this->data->order->did])
            ->andWhere(['is not', 'p.aadhaar_md5', null])
            ->groupBy(['p.aadhaar_md5'])
            ->asArray()
            ->all(),
            'aadhaar_md5');

        $dataOther = ArrayHelper::getColumn(
            UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['p.aadhaar_md5'])
            ->where(['o.did' => $this->data->order->did])
            ->andWhere(['is not', 'p.aadhaar_md5', null])
            ->groupBy(['p.aadhaar_md5'])
            ->asArray()
            ->all(Yii::$app->db_loan),
            'aadhaar_md5');

        return count(array_unique(array_merge($data, $dataOther)));
    }

    /**
     * 总平台近30天内该数盟设备ID下单环节关联过的不同Aadhaar卡号数量
     * @return int
     */
    public function checkSMDeviceIDLast30dOrderMatchAadhaarCntTotPlatform(){
        if(empty($this->data->order->did)){
            return -1;
        }

        $begin_time = $this->data->order->order_time - 30 * 86400;
        $data = ArrayHelper::getColumn(
            UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['p.aadhaar_md5'])
            ->where(['o.did' => $this->data->order->did])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['is not', 'p.aadhaar_md5', null])
            ->groupBy(['p.aadhaar_md5'])
            ->asArray()
            ->all(),
            'aadhaar_md5');

        $dataOther = ArrayHelper::getColumn(
            UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['p.aadhaar_md5'])
            ->where(['o.did' => $this->data->order->did])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['is not', 'p.aadhaar_md5', null])
            ->groupBy(['p.aadhaar_md5'])
            ->asArray()
            ->all(Yii::$app->db_loan),
            'aadhaar_md5');

        return count(array_unique(array_merge($data, $dataOther)));
    }

    /**
     * 总平台近60天内该数盟设备ID下单环节关联过的不同Aadhaar卡号数量
     * @return int
     */
    public function checkSMDeviceIDLast60dOrderMatchAadhaarCntTotPlatform(){
        if(empty($this->data->order->did)){
            return -1;
        }

        $begin_time = $this->data->order->order_time - 60 * 86400;
        $data = ArrayHelper::getColumn(
            UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['p.aadhaar_md5'])
            ->where(['o.did' => $this->data->order->did])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['is not', 'p.aadhaar_md5', null])
            ->groupBy(['p.aadhaar_md5'])
            ->asArray()
            ->all(),
            'aadhaar_md5');

        $dataOther = ArrayHelper::getColumn(
            UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['p.aadhaar_md5'])
            ->where(['o.did' => $this->data->order->did])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['is not', 'p.aadhaar_md5', null])
            ->groupBy(['p.aadhaar_md5'])
            ->asArray()
            ->all(Yii::$app->db_loan),
            'aadhaar_md5');

        return count(array_unique(array_merge($data, $dataOther)));
    }

    /**
     * 总平台近90天内该数盟设备ID下单环节关联过的不同Aadhaar卡号数量
     * @return int
     */
    public function checkSMDeviceIDLast90dOrderMatchAadhaarCntTotPlatform(){
        if(empty($this->data->order->did)){
            return -1;
        }

        $begin_time = $this->data->order->order_time - 90 * 86400;
        $data = ArrayHelper::getColumn(
            UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['p.aadhaar_md5'])
            ->where(['o.did' => $this->data->order->did])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['is not', 'p.aadhaar_md5', null])
            ->groupBy(['p.aadhaar_md5'])
            ->asArray()
            ->all(),
            'aadhaar_md5');

        $dataOther = ArrayHelper::getColumn(
            UserLoanOrder::find()->from(UserLoanOrder::tableName().' as o')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id = o.user_id')
            ->select(['p.aadhaar_md5'])
            ->where(['o.did' => $this->data->order->did])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['is not', 'p.aadhaar_md5', null])
            ->groupBy(['p.aadhaar_md5'])
            ->asArray()
            ->all(Yii::$app->db_loan),
            'aadhaar_md5');

        return count(array_unique(array_merge($data, $dataOther)));
    }

    /**
     * 老用户-总平台近7天的申请订单量
     * @return int
     */
    public function checkLast7dOrderApplyCntTPF(){
        $begin_time = strtotime(date('Y-m-d', $this->data->order->order_time)) - 7 * 86400;
        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $userIds_other = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);

        $count = UserLoanOrder::find()
            ->where(['user_id' => $userIds])
            ->andWhere(['>=', 'order_time', $begin_time])
            ->andWhere(['<', 'order_time', $this->data->order->order_time])
            ->count();

        $countOther = UserLoanOrder::find()
            ->where(['user_id' => $userIds_other])
            ->andWhere(['>=', 'order_time', $begin_time])
            ->andWhere(['<', 'order_time', $this->data->order->order_time])
            ->count('*', Yii::$app->db_loan);

        return $count + $countOther;
    }

    /**
     * 老用户-总平台近15天的申请订单量
     * @return int
     */
    public function checkLast15dOrderApplyCntTPF(){
        $begin_time = strtotime(date('Y-m-d', $this->data->order->order_time)) - 15 * 86400;
        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $userIds_other = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);

        $count = UserLoanOrder::find()
            ->where(['user_id' => $userIds])
            ->andWhere(['>=', 'order_time', $begin_time])
            ->andWhere(['<', 'order_time', $this->data->order->order_time])
            ->count();

        $countOther = UserLoanOrder::find()
            ->where(['user_id' => $userIds_other])
            ->andWhere(['>=', 'order_time', $begin_time])
            ->andWhere(['<', 'order_time', $this->data->order->order_time])
            ->count('*', Yii::$app->db_loan);

        return $count + $countOther;
    }

    /**
     * 老用户-总平台近30天的申请订单量
     * @return int
     */
    public function checkLast30dOrderApplyCntTPF(){
        $begin_time = strtotime(date('Y-m-d', $this->data->order->order_time)) - 30 * 86400;
        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $userIds_other = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);

        $count = UserLoanOrder::find()
            ->where(['user_id' => $userIds])
            ->andWhere(['>=', 'order_time', $begin_time])
            ->andWhere(['<', 'order_time', $this->data->order->order_time])
            ->count();

        $countOther = UserLoanOrder::find()
            ->where(['user_id' => $userIds_other])
            ->andWhere(['>=', 'order_time', $begin_time])
            ->andWhere(['<', 'order_time', $this->data->order->order_time])
            ->count('*', Yii::$app->db_loan);

        return $count + $countOther;
    }

    /**
     * 老用户-总平台历史的申请量
     * @return int
     */
    public function checkHisOrderApplyCntTPF(){
        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $userIds_other = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);

        $count = UserLoanOrder::find()
            ->where(['user_id' => $userIds])
            ->andWhere(['<', 'order_time', $this->data->order->order_time])
            ->count();

        $countOther = UserLoanOrder::find()
            ->where(['user_id' => $userIds_other])
            ->andWhere(['<', 'order_time', $this->data->order->order_time])
            ->count('*', Yii::$app->db_loan);

        return $count + $countOther;
    }

    /**
     * 老用户-总平台近15天逾期还款的单数占近30天放款订单数的比例
     * @return int
     */
    public function checkLast15dDueRepayCntLast30dCntRateTPF(){
        $begin_time = strtotime(date('Y-m-d', $this->data->order->order_time)) - 30 * 86400;
        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $userIds_other = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);

        $count = UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds])
            ->andWhere(['>=', 'loan_time', $begin_time])
            ->andWhere(['<', 'loan_time', $this->data->order->order_time])
            ->count();

        $count += UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds_other])
            ->andWhere(['>=', 'loan_time', $begin_time])
            ->andWhere(['<', 'loan_time', $this->data->order->order_time])
            ->count('*', Yii::$app->db_loan);

        if(empty($count)){
            return -1;
        }

        $start_time = strtotime(date('Y-m-d', $this->data->order->order_time)) - 15 * 86400;

        $count_repay = UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds,
                     'status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE,
                     'is_overdue' => UserLoanOrderRepayment::IS_DELAY_YES])
            ->andWhere(['>=', 'closing_time', $start_time])
            ->andWhere(['<', 'closing_time', $this->data->order->order_time])
            ->count();

        $count_repay += UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds_other,
                     'status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE,
                     'is_overdue' => UserLoanOrderRepayment::IS_DELAY_YES])
            ->andWhere(['>=', 'closing_time', $start_time])
            ->andWhere(['<', 'closing_time', $this->data->order->order_time])
            ->count('*', Yii::$app->db_loan);

        return round($count_repay / $count * 100, 2);
    }

    /**
     * 老用户-总平台历史逾期4天及以上还款的次数占历史逾期还款次数的比例
     * @return int
     */
    public function checkHisDue4RepayCntHisDueCntRateTPF(){
        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $userIds_other = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);
        $count_all = UserLoanOrderRepayment::find()
            ->where(['status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE,
                     'is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_YES,
                     'user_id' => $userIds])->count();

        $count_all += UserLoanOrderRepayment::find()
            ->where(['status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE,
                     'is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_YES,
                     'user_id' => $userIds_other])->count('*', Yii::$app->db_loan);

        if(empty($count_all)){
            return -1;
        }

        $count = UserLoanOrderRepayment::find()
            ->where(['status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE,
                     'user_id' => $userIds])
            ->andWhere(['>=', 'overdue_day', 4])->count();

        $count += UserLoanOrderRepayment::find()
            ->where(['status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE,
                     'user_id' => $userIds_other])
            ->andWhere(['>=', 'overdue_day', 4])->count('*', Yii::$app->db_loan);

        return round($count / $count_all * 100, 2);
    }

    /**
     * 老用户-总平台近30天正常还款的订单数占历史放款订单数的比例
     * @return int
     */
    public function checkLast30dTiqianRepayCntHisCntRateTPF(){
        $begin_time = strtotime(date('Y-m-d', $this->data->order->order_time)) - 30 * 86400;
        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $userIds_other = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);

        $count_all = UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds])->count();

        $count_all += UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds_other])->count('*', Yii::$app->db_loan);
        if(empty($count_all)){
            return -1;
        }

        $count = UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds, 'is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_NO])
            ->andWhere(['>=', 'closing_time', $begin_time])
            ->andWhere(['<', 'closing_time', $this->data->order->order_time])
            ->count();

        $count += UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds_other, 'is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_NO])
            ->andWhere(['>=', 'closing_time', $begin_time])
            ->andWhere(['<', 'closing_time', $this->data->order->order_time])
            ->count('*', Yii::$app->db_loan);


        return round($count / $count_all * 100, 2);
    }

    /**
     * 老用户-总平台本单申请日期与首单的日期差
     * @return int
     */
    public function checkMaxDateOfOrderToTodayTPF(){
        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $data = UserLoanOrder::find()->where(['user_id' => $userIds])
            ->andWhere(['>', 'loan_time', 0])
            ->all();
        $count = [0];
        foreach ($data as $v){
            $count[] = (strtotime(date('Y-m-d', $this->data->order->order_time)) - strtotime(date('Y-m-d', $v['order_time'])))/86400;
        }

        $userIds_saas = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);
        $data_saas = UserLoanOrder::find()->where(['user_id' => $userIds_saas])
            ->andWhere(['>', 'loan_time', 0])
            ->all(Yii::$app->db_loan);
        foreach ($data_saas as $v){
            $count[] = (strtotime(date('Y-m-d', $this->data->order->order_time)) - strtotime(date('Y-m-d', $v['order_time'])))/86400;
        }

        return max($count);
    }

    /**
     * 老用户模型分V4
     * @return int
     */
    public function checkOldUserModelScoreV4(){
        $v356 = $this->checkIsSMSRecordGrabNormal();

        $score = 0;
        if($v356 == 0){
            $score += 221;
        }else{
            $v601 = $this->checkSMSCntOfLoanRejectionLast7Days();
            switch (true){
                case $v601 < 1:
                    $score += 55;
                    break;
                case $v601 < 2:
                    $score += 40;
                    break;
                case $v601 < 3:
                    $score += 24;
                    break;
                case $v601 >= 3:
                    $score += 9;
                    break;
            }

            $v610 = $this->checkHistSMSCntOfLoanDisbursal();
            switch (true){
                case $v610 < 1:
                    $score += 36;
                    break;
                case $v610 < 5:
                    $score += 45;
                    break;
                case $v610 < 12:
                    $score += 52;
                    break;
                case $v610 < 21:
                    $score += 61;
                    break;
                case $v610 >= 21:
                    $score += 68;
                    break;
            }

            $v605 = $this->checkHistSMSCntOfLoanApproval();
            switch (true){
                case $v605 < 10:
                    $score += 43;
                    break;
                case $v605 < 40:
                    $score += 46;
                    break;
                case $v605 < 60:
                    $score += 48;
                    break;
                case $v605 >= 60:
                    $score += 50;
                    break;
            }

            $v616 = $this->checkSMSCntOfLoanDueRemindLast7Days();
            switch (true){
                case $v616 < 2:
                    $score += 45;
                    break;
                case $v616 < 23:
                    $score += 53;
                    break;
                case $v616 < 33:
                    $score += 34;
                    break;
                case $v616 >= 33:
                    $score += 11;
                    break;
            }

            $v606 = $this->checkSMSCntOfLoanApprovalLast7Days();
            switch (true){
                case $v606 < 1:
                    $score += 25;
                    break;
                case $v606 < 4:
                    $score += 38;
                    break;
                case $v606 < 5:
                    $score += 49;
                    break;
                case $v606 < 9:
                    $score += 59;
                    break;
                case $v606 >= 9:
                    $score += 81;
                    break;
            }
        }

        $v1201 = $this->checkLast7dOrderApplyCntTPF();
        switch (true){
            case $v1201 < 1:
                $score += 70;
                break;
            case $v1201 < 2:
                $score += 21;
                break;
            case $v1201 < 3:
                $score += 10;
                break;
            case $v1201 >= 3:
                $score += -18;
                break;
        }

        $v1207 = $this->checkHisOrderApplyCntTPF();
        switch (true){
            case $v1207 < 1:
                $score += 67;
                break;
            case $v1207 < 18:
                $score += 45;
                break;
            case $v1207 >= 18:
                $score += 42;
                break;
        }

        $v1206 = $this->checkLast30dTiqianRepayCntHisCntRateTPF();
        switch (true){
            case $v1206 < 16:
                $score += 12;
                break;
            case $v1206 < 26:
                $score += 39;
                break;
            case $v1206 >= 26:
                $score += 49;
                break;
        }

        $v1203 = $this->checkHisDue4RepayCntHisDueCntRateTPF();
        switch (true){
            case $v1203 < 0:
                $score += 63;
                break;
            case $v1203 < 10:
                $score += 27;
                break;
            case $v1203 >= 10:
                $score += -6;
                break;
        }

        $v1198 = $this->checkMaxDateOfOrderToTodayTPF();
        switch (true){
            case $v1198 < 12:
                $score += 12;
                break;
            case $v1198 < 60:
                $score += 42;
                break;
            case $v1198 < 96:
                $score += 71;
                break;
            case $v1198 >= 96:
                $score += 91;
                break;
        }

        $v1202 = $this->checkLast15dDueRepayCntLast30dCntRateTPF();
        switch (true){
            case $v1202 < 0:
                $score += 8;
                break;
            case $v1202 < 5:
                $score += 58;
                break;
            case $v1202 < 35:
                $score += 24;
                break;
            case $v1202 >= 35:
                $score += 8;
                break;
        }

        $v1205 = $this->checkLast30dOrderApplyCntTPF();
        switch (true){
            case $v1205 < 1:
                $score += 48;
                break;
            case $v1205 < 4:
                $score += 47;
                break;
            case $v1205 < 8:
                $score += 46;
                break;
            case $v1205 >= 8:
                $score += 46;
                break;
        }

        $v1204 = $this->checkLast15dOrderApplyCntTPF();
        switch (true){
            case $v1204 < 1:
                $score += 67;
                break;
            case $v1204 < 2:
                $score += 55;
                break;
            case $v1204 >= 2:
                $score += 34;
                break;
        }

        return $score;
    }

    /**
     * 本平台用户历史各次登录、下单时关联的不同数盟设备ID的数量
     * @return int
     */
    public function checkHistSMDeviceCntOfLoginAndOrder(){
        $loginLog = ClientInfoLog::find()
            ->select(['szlm_query_id'])
            ->where(['user_id' => $this->data->loanPerson->id, 'event' => ClientInfoLog::EVENT_LOGIN])
            ->andWhere(['<=', 'created_at', $this->data->order->order_time])
            ->andWhere(['is not', 'szlm_query_id', null])
            ->groupBy(['szlm_query_id'])
            ->asArray()
            ->all();

        $data = UserLoanOrder::find()
            ->select(['did'])
            ->where(['user_id' => $this->data->loanPerson->id])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->andWhere(['is not', 'did', null])
            ->groupBy(['did'])
            ->asArray()
            ->all();

        return count(array_unique(array_merge(
            ArrayHelper::getColumn($loginLog, 'szlm_query_id'),
            ArrayHelper::getColumn($data, 'did')
        )));
    }

    /**
     * 本平台用户近30天内各次登录、下单时关联的不同数盟设备ID的数量
     * @return int
     */
    public function checkSMDeviceCntOfLoginAndOrderLast30Days(){
        $begin_time = $this->data->order->order_time - 30 * 86400;
        $loginLog = ClientInfoLog::find()
            ->select(['szlm_query_id'])
            ->where(['user_id' => $this->data->loanPerson->id, 'event' => ClientInfoLog::EVENT_LOGIN])
            ->andWhere(['>=', 'created_at', $begin_time])
            ->andWhere(['<=', 'created_at', $this->data->order->order_time])
            ->andWhere(['is not', 'szlm_query_id', null])
            ->groupBy(['szlm_query_id'])
            ->asArray()
            ->all();

        $data = UserLoanOrder::find()
            ->select(['did'])
            ->where(['user_id' => $this->data->loanPerson->id])
            ->andWhere(['>=', 'order_time', $begin_time])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->andWhere(['is not', 'did', null])
            ->groupBy(['did'])
            ->asArray()
            ->all();

        return count(array_unique(array_merge(
            ArrayHelper::getColumn($loginLog, 'szlm_query_id'),
            ArrayHelper::getColumn($data, 'did')
        )));
    }

    /**
     * 本平台用户近60天内各次登录、下单时关联的不同数盟设备ID的数量
     * @return int
     */
    public function checkSMDeviceCntOfLoginAndOrderLast60Days(){
        $begin_time = $this->data->order->order_time - 60 * 86400;
        $loginLog = ClientInfoLog::find()
            ->select(['szlm_query_id'])
            ->where(['user_id' => $this->data->loanPerson->id, 'event' => ClientInfoLog::EVENT_LOGIN])
            ->andWhere(['>=', 'created_at', $begin_time])
            ->andWhere(['<=', 'created_at', $this->data->order->order_time])
            ->andWhere(['is not', 'szlm_query_id', null])
            ->groupBy(['szlm_query_id'])
            ->asArray()
            ->all();

        $data = UserLoanOrder::find()
            ->select(['did'])
            ->where(['user_id' => $this->data->loanPerson->id])
            ->andWhere(['>=', 'order_time', $begin_time])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->andWhere(['is not', 'did', null])
            ->groupBy(['did'])
            ->asArray()
            ->all();

        return count(array_unique(array_merge(
            ArrayHelper::getColumn($loginLog, 'szlm_query_id'),
            ArrayHelper::getColumn($data, 'did')
        )));
    }

    /**
     * 本平台用户近90天内各次登录、下单时关联的不同数盟设备ID的数量
     * @return int
     */
    public function checkSMDeviceCntOfLoginAndOrderLast90Days(){
        $begin_time = $this->data->order->order_time - 90 * 86400;
        $loginLog = ClientInfoLog::find()
            ->select(['szlm_query_id'])
            ->where(['user_id' => $this->data->loanPerson->id, 'event' => ClientInfoLog::EVENT_LOGIN])
            ->andWhere(['>=', 'created_at', $begin_time])
            ->andWhere(['<=', 'created_at', $this->data->order->order_time])
            ->andWhere(['is not', 'szlm_query_id', null])
            ->groupBy(['szlm_query_id'])
            ->asArray()
            ->all();

        $data = UserLoanOrder::find()
            ->select(['did'])
            ->where(['user_id' => $this->data->loanPerson->id])
            ->andWhere(['>=', 'order_time', $begin_time])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->andWhere(['is not', 'did', null])
            ->groupBy(['did'])
            ->asArray()
            ->all();

        return count(array_unique(array_merge(
            ArrayHelper::getColumn($loginLog, 'szlm_query_id'),
            ArrayHelper::getColumn($data, 'did')
        )));
    }

    /**
     * 总平台该Pan卡号历史各次登录、下单时关联的不同数盟设备ID的数量
     * @return int
     */
    public function checkHistSMDeviceCntOfLoginAndOrderInTPF(){
        if(!$this->data->loanPerson->pan_code){
            return -1;
        }

        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $loginLog = ClientInfoLog::find()
            ->select(['szlm_query_id'])
            ->where(['user_id' => $userIds, 'event' => ClientInfoLog::EVENT_LOGIN])
            ->andWhere(['<=', 'created_at', $this->data->order->order_time])
            ->andWhere(['is not', 'szlm_query_id', null])
            ->groupBy(['szlm_query_id'])
            ->asArray()
            ->all();

        $data = UserLoanOrder::find()
            ->select(['did'])
            ->where(['user_id' => $userIds])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->andWhere(['is not', 'did', null])
            ->groupBy(['did'])
            ->asArray()
            ->all();

        $otherUserids = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);
        $loginLog_other = ClientInfoLog::find()
            ->select(['szlm_query_id'])
            ->where(['user_id' => $otherUserids, 'event' => ClientInfoLog::EVENT_LOGIN])
            ->andWhere(['<=', 'created_at', $this->data->order->order_time])
            ->andWhere(['is not', 'szlm_query_id', null])
            ->groupBy(['szlm_query_id'])
            ->asArray()
            ->all(Yii::$app->db_loan);

        $data_other = UserLoanOrder::find()
            ->select(['did'])
            ->where(['user_id' => $otherUserids])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->andWhere(['is not', 'did', null])
            ->groupBy(['did'])
            ->asArray()
            ->all(Yii::$app->db_loan);

        return count(array_unique(array_merge(
            ArrayHelper::getColumn($loginLog, 'szlm_query_id'),
            ArrayHelper::getColumn($data, 'did'),
            ArrayHelper::getColumn($loginLog_other, 'szlm_query_id'),
            ArrayHelper::getColumn($data_other, 'did')
        )));
    }

    /**
     * 总平台该Pan卡号近30天内各次登录、下单时关联的不同数盟设备ID的数量
     * @return int
     */
    public function checkSMDeviceCntOfLoginAndOrderInTPFLast30Days(){
        if(!$this->data->loanPerson->pan_code){
            return -1;
        }

        $begin_time = $this->data->order->order_time - 30 * 86400;

        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $loginLog = ClientInfoLog::find()
            ->select(['szlm_query_id'])
            ->where(['user_id' => $userIds, 'event' => ClientInfoLog::EVENT_LOGIN])
            ->andWhere(['>=', 'created_at', $begin_time])
            ->andWhere(['<=', 'created_at', $this->data->order->order_time])
            ->andWhere(['is not', 'szlm_query_id', null])
            ->groupBy(['szlm_query_id'])
            ->asArray()
            ->all();

        $data = UserLoanOrder::find()
            ->select(['did'])
            ->where(['user_id' => $userIds])
            ->andWhere(['>=', 'order_time', $begin_time])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->andWhere(['is not', 'did', null])
            ->groupBy(['did'])
            ->asArray()
            ->all();

        $otherUserids = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);
        $loginLog_other = ClientInfoLog::find()
            ->select(['szlm_query_id'])
            ->where(['user_id' => $otherUserids, 'event' => ClientInfoLog::EVENT_LOGIN])
            ->andWhere(['>=', 'created_at', $begin_time])
            ->andWhere(['<=', 'created_at', $this->data->order->order_time])
            ->andWhere(['is not', 'szlm_query_id', null])
            ->groupBy(['szlm_query_id'])
            ->asArray()
            ->all(Yii::$app->db_loan);

        $data_other = UserLoanOrder::find()
            ->select(['did'])
            ->where(['user_id' => $otherUserids])
            ->andWhere(['>=', 'order_time', $begin_time])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->andWhere(['is not', 'did', null])
            ->groupBy(['did'])
            ->asArray()
            ->all(Yii::$app->db_loan);

        return count(array_unique(array_merge(
            ArrayHelper::getColumn($loginLog, 'szlm_query_id'),
            ArrayHelper::getColumn($data, 'did'),
            ArrayHelper::getColumn($loginLog_other, 'szlm_query_id'),
            ArrayHelper::getColumn($data_other, 'did')
        )));
    }

    /**
     * 总平台该Pan卡号近60天内各次登录、下单时关联的不同数盟设备ID的数量
     * @return int
     */
    public function checkSMDeviceCntOfLoginAndOrderInTPFLast60Days(){
        if(!$this->data->loanPerson->pan_code){
            return -1;
        }

        $begin_time = $this->data->order->order_time - 60 * 86400;

        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $loginLog = ClientInfoLog::find()
            ->select(['szlm_query_id'])
            ->where(['user_id' => $userIds, 'event' => ClientInfoLog::EVENT_LOGIN])
            ->andWhere(['>=', 'created_at', $begin_time])
            ->andWhere(['<=', 'created_at', $this->data->order->order_time])
            ->andWhere(['is not', 'szlm_query_id', null])
            ->groupBy(['szlm_query_id'])
            ->asArray()
            ->all();

        $data = UserLoanOrder::find()
            ->select(['did'])
            ->where(['user_id' => $userIds])
            ->andWhere(['>=', 'order_time', $begin_time])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->andWhere(['is not', 'did', null])
            ->groupBy(['did'])
            ->asArray()
            ->all();

        $otherUserids = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);
        $loginLog_other = ClientInfoLog::find()
            ->select(['szlm_query_id'])
            ->where(['user_id' => $otherUserids, 'event' => ClientInfoLog::EVENT_LOGIN])
            ->andWhere(['>=', 'created_at', $begin_time])
            ->andWhere(['<=', 'created_at', $this->data->order->order_time])
            ->andWhere(['is not', 'szlm_query_id', null])
            ->groupBy(['szlm_query_id'])
            ->asArray()
            ->all(Yii::$app->db_loan);

        $data_other = UserLoanOrder::find()
            ->select(['did'])
            ->where(['user_id' => $otherUserids])
            ->andWhere(['>=', 'order_time', $begin_time])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->andWhere(['is not', 'did', null])
            ->groupBy(['did'])
            ->asArray()
            ->all(Yii::$app->db_loan);

        return count(array_unique(array_merge(
            ArrayHelper::getColumn($loginLog, 'szlm_query_id'),
            ArrayHelper::getColumn($data, 'did'),
            ArrayHelper::getColumn($loginLog_other, 'szlm_query_id'),
            ArrayHelper::getColumn($data_other, 'did')
        )));
    }

    /**
     * 总平台该Pan卡号近90天内各次登录、下单时关联的不同数盟设备ID的数量
     * @return int
     */
    public function checkSMDeviceCntOfLoginAndOrderInTPFLast90Days(){
        if(!$this->data->loanPerson->pan_code){
            return -1;
        }

        $begin_time = $this->data->order->order_time - 90 * 86400;

        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $loginLog = ClientInfoLog::find()
            ->select(['szlm_query_id'])
            ->where(['user_id' => $userIds, 'event' => ClientInfoLog::EVENT_LOGIN])
            ->andWhere(['>=', 'created_at', $begin_time])
            ->andWhere(['<=', 'created_at', $this->data->order->order_time])
            ->andWhere(['is not', 'szlm_query_id', null])
            ->groupBy(['szlm_query_id'])
            ->asArray()
            ->all();

        $data = UserLoanOrder::find()
            ->select(['did'])
            ->where(['user_id' => $userIds])
            ->andWhere(['>=', 'order_time', $begin_time])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->andWhere(['is not', 'did', null])
            ->groupBy(['did'])
            ->asArray()
            ->all();

        $otherUserids = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);
        $loginLog_other = ClientInfoLog::find()
            ->select(['szlm_query_id'])
            ->where(['user_id' => $otherUserids, 'event' => ClientInfoLog::EVENT_LOGIN])
            ->andWhere(['>=', 'created_at', $begin_time])
            ->andWhere(['<=', 'created_at', $this->data->order->order_time])
            ->andWhere(['is not', 'szlm_query_id', null])
            ->groupBy(['szlm_query_id'])
            ->asArray()
            ->all(Yii::$app->db_loan);

        $data_other = UserLoanOrder::find()
            ->select(['did'])
            ->where(['user_id' => $otherUserids])
            ->andWhere(['>=', 'order_time', $begin_time])
            ->andWhere(['<=', 'order_time', $this->data->order->order_time])
            ->andWhere(['is not', 'did', null])
            ->groupBy(['did'])
            ->asArray()
            ->all(Yii::$app->db_loan);

        return count(array_unique(array_merge(
            ArrayHelper::getColumn($loginLog, 'szlm_query_id'),
            ArrayHelper::getColumn($data, 'did'),
            ArrayHelper::getColumn($loginLog_other, 'szlm_query_id'),
            ArrayHelper::getColumn($data_other, 'did')
        )));
    }

    /**
     * 跑此笔订单风控时，该Pan卡号2020年5月1日及以后在本平台的还款订单数加1（用来表示此笔订单是第几笔订单）
     * @return int
     */
    public function checkHisSuccessClosingOrderCntPost20200501PlusOneSPF(){
        $begin_time = strtotime('2020-05-01');

        $count = UserLoanOrderRepayment::find()
            ->where(['user_id' => $this->data->loanPerson->id])
            ->andWhere(['>=', 'closing_time', $begin_time])
            ->andWhere(['<=', 'closing_time', $this->data->order->order_time])
            ->count();

        return $count + 1;
    }

    /**
     * 跑此笔订单风控时，该Pan卡号2020年5月1日及以后在总平台的还款订单数加1（用来表示此笔订单是第几笔订单）
     * @return int
     */
    public function checkHisSuccessClosingOrderCntPost20200501PlusOneTPF(){
        $begin_time = strtotime('2020-05-01');

        $userIds = $this->getPanAllUserIds($this->data->loanPerson->pan_code);
        $count = UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds])
            ->andWhere(['>=', 'closing_time', $begin_time])
            ->andWhere(['<=', 'closing_time', $this->data->order->order_time])
            ->count();

        $userIds_other = $this->getPanOtherUserIds($this->data->loanPerson->pan_code);
        $count_other = UserLoanOrderRepayment::find()
            ->where(['user_id' => $userIds_other])
            ->andWhere(['>=', 'closing_time', $begin_time])
            ->andWhere(['<=', 'closing_time', $this->data->order->order_time])
            ->count(Yii::$app->db_loan);

        return $count + $count_other + 1;
    }


}