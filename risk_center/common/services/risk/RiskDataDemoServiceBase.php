<?php
namespace common\services\risk;

use Carbon\Carbon;
use common\helpers\CommonHelper;
use common\helpers\Util;
use common\models\InfoCollectionSuggestion;
use common\models\InfoDevice;
use common\models\InfoOrder;
use common\models\InfoRepayment;
use common\models\InfoUser;
use common\models\LoginLog;
use common\models\order\EsUserLoanOrder;
use common\models\risk\RiskResultSnapshot;
use common\models\RiskOrder;
use common\models\third_data\ThirdDataGoogleMaps;
use common\models\third_data\ThirdDataShumeng;
use common\models\user\MgUserCallReports;
use common\models\user\MgUserInstalledApps;
use common\models\user\MgUserMobileContacts;
use common\models\user\UserCreditReportBangaloreExperian;
use common\models\user\UserCreditReportCibil;
use common\models\user\UserCreditReportExperian;
use common\models\user\UserCreditReportMobiExperian;
use common\models\user\UserCreditReportShanyunExperian;
use common\models\UserQuestionVerification;
use common\models\whiteList\PhoneBrand;
use common\models\risk\RiskDataContainer;
use common\models\whiteList\Pin;
use common\services\MgUserMobileSmsService;
use common\services\third_data\GoogleMapsService;
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
class RiskDataDemoServiceBase extends RiskDataService
{

    const RL = 2;   # 低
    const RM = 1;   # 中
    const RH = 0;   # 高

    //是否获取第三方数据
    public $isGetData = true;

    # attrs
    protected $data;
    //pan对应的用户紧急联系人
    protected $panUserContacts = [];
    //本平台用户紧急联系人
    protected $userContactsSelf = [];
    //该ip近天数时间内的申请数
    protected $ipInDayOrderApplyCount = [];
    //该ip近天数时间内的申请数 本平台
    protected $ipInDayOrderApplyCountSelf = [];
    //用户短信数据
    protected $userSms = [];
    //用户短信数据  全平台
    protected $userAllSms = [];
    protected $smsIsNormal = null;

    protected $cibilReport;
    protected $experianReport;
    protected $experian_updated_at;

    protected $bangaloreExperianReport;
    protected $bangalore_experian_updated_at;

    protected $shumengReport;
    protected $googleMapsReport;

    protected $assistData;
    protected $remindData;

    protected $teleCollectionTime;
    protected $teleCollectionLevel;

    //该pan_code在全平台订单信息
    protected $orderData = [];

    //该pan_code在本产品订单信息
    protected $productOrderData = [];

    //该数盟id在全平台逾期订单信息
    protected $szlmOrderData = [];

    //该手机号在全平台逾期订单信息
    protected $phoneOrderData = [];


    //用户手机通讯录
    protected $userContacts = [];

    protected $lawPoliceList = [
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

    protected $addressWhiteList = [
        'AURANGABAD',
        'BENGALURU',
        'BANGALORE',
        'BHOPAL',
        'BHUBANESWAR',
        'CHENNAI',
        'COIMBATORE',
        'DELHI',
        'NEW DELHI',
        'HYDERABAD',
        'INDORE',
        'KOCHI',
        'KOLLAM',
        'KOTTAYAM',
        'MANGALURU',
        'MUMBAI',
        'MYSORE',
        'NAGPUR',
        'NASHIK',
        'PONDICHERRY',
        'PUNE',
        'SURAT',
        'THIRUVANANTHAPURAM',
        'VADODARA',
        'VIJAYAWADA',
        'VISAKHAPATNAM',
        'AGRA',
        'ALLAHABAD',
        'FARIDABAD',
        'GURGAON',
        'JAIPUR',
        'NOIDA',
        'AMRAVATI',
        'GUNTUR',
        'HUBLI-DHARWAD',
        'KARIMNAGAR',
        'KANCHEEPURAM',
        'KHAMMAM',
        'KOZHIKODE',
        'SANGAREDDY',
        'SALEM',
        'THIRUVALLUR',
        'WARANGAL',
        'BHILAI NAGAR',
        'GWALIOR',
        'JABALPUR',
        'RAIPUR',
        'THANE',
        'AGARTALA',
        'AHMEDABAD',
        'AMRITSAR',
        'CHANDIGARH',
        'DEHRADUN',
        'HISAR',
        'JODHPUR',
        'KANPUR',
        'KOLKATA',
        'LUCKNOW',
        'LUDHIANA',
        'MEERUT',
        'RAJKOT',
        'SHILLONG',
        'SHIMLA',
        'SRINAGAR',
        'UDAIPUR',
        'VARANASI',
        'VELLORE',
        'MADURAI',
        'MATHURA',
        'ERODE',
        'BHIWANDI',
        'JHANSI',
        'UJJAIN',
        'TIRUCHIRAPPALLI',
        'NELLORE',
        'RANCHI',
        'THRISSUR',
        'MALAPPURAM',
        'TIRUPATI',
        'RAJAHMUNDRY',
        'ALIGARH',
        'TIRUPPUR',
        'CUTTACK',
        'CHITTOOR',
        'ONGOLE',
        'KADAPA',
        'ANANTAPUR',
        'AHMEDNAGAR',
        'SOLAPUR',
        'VILUPPURAM',
        'SATARA',
        'BHAVNAGAR',
        'KURNOOL',
        'THANJAVUR',
        'BELAGAVI',
        'VIZIANAGARAM',
        'AKOLA',
        'KOLAR',
        'LATUR',
        'ICHALKARANJI',
        'TIRUVANNAMALAI',
        'NAMAKKAL',
        'TENALI',
        'SANGLI',
        'TUMKUR',
        'BALLARI',
        'BALESHWAR TOWN',
        'BANKURA',
        'KAKINADA',
        'PURI',
        'SURYAPET',
        'NAGERCOIL',
        'MEDAK',
        'RAMAGUNDAM',
        'SRIKAKULAM',
        'NANDED-WAGHALA',
        'NAVSARI',
        'SHIVAMOGGA',
        'JAMNAGAR',
        'RATLAM',
        'KALYAN-DOMBIVALI',
        'TAMLUK',
        'PARBHANI',
        'MACHILIPATNAM',
        'ELURU',
        'AMALAPURAM',
        'VASAI-VIRAR',
        'NARASARAOPET',
        'KARUR',
        'ANAND',
        'SIDDIPET',
        'SAGAR',
        'MAHBUBNAGAR',
        'MAHESANA',
        'PANVEL',
        'BHARUCH',
        'BOKARO STEEL CITY',
        'NIZAMABAD',
        'THENI ALLINAGARAM',
        'OSMANABAD',
        'MOHALI',
        'DAVANAGERE',
        'VALSAD',
        'BATHINDA',
        'GANDHINAGAR',
        'MANDYA',
        'VAPI',
        'VIJAYAPURA',
        'NAGAPATTINAM',
        'ROORKEE',
        'DHULE',
        'BHIWANI',
        'WARDHA',
        'KENDRAPARA',
        'PALANPUR',
        'PANCHKULA',
        'NADIAD',
        'UDUPI',
        'VIRUDHACHALAM',
        'DARJILING',
        'RAMANATHAPURAM',
        'KHARAGPUR',
        'BHUJ',
        'RAAYACHURU',
        'MANDSAUR',
        'KENDUJHAR',
        'KARWAR',
        'TIRUCHENGODE',
        'AMRELI',
        'HINDUPUR',
        'CHIKKAMAGALURU',
        'GUDUR',
        'BHIMAVARAM',
        'PRODDATUR',
        'SAWAI MADHOPUR',
        'SOLAN',
        'PITHAMPUR',
        'RUPNAGAR',
        'SATNA',
        'PERAMBALUR',
        'RAURKELA',
        'BEGUSARAI',
        'MALDA',
        'PACHORA',
        'ADONI',
        'ITARSI',
        'TADEPALLIGUDEM',
        'BAHARAMPUR',
        'SIVAGANGA',
        'MUKTSAR',
        'SHIVPURI',
        'POLLACHI',
        'ANJAR',
        'CHIRALA',
        'FARIDKOT',
        'SHAHDOL',
        'KAVALI',
        'PALGHAR',
        'VINUKONDA',
        'ODDANCHATRAM',
        'GURDASPUR',
        'NARSINGHGARH',
        'CHILAKALURIPET',
        'NEEMUCH',
        'UDHAGAMANDALAM',
        'SANGRUR',
        'MODASA',
        'RAJGARH',
        'TIRUPATHUR',
        'MANSA',
        'LUNAWADA',
        'ARARIA',
        'THIRUVARUR',
        'MEDININAGAR (DALTONGANJ)',
        'PATAN',
        'KOVVUR',
        'JALANDHAR CANTT.',
        'MARGAO',
        'RANIPET',
        'ASHOK NAGAR',
        'SIVAKASI',
        'NAWABGANJ',
        'MORVI',
        'NEYYATTINKARA',
        'RAISEN',
        'NEYVELI (TS)',
        'GUNTAKAL',
        'SEONI',
        'MURWARA (KATNI)',
        'BYASANAGAR',
        'SRIVILLIPUTHUR',
        'PILANI',
        'KAMAREDDY',
        'RAYACHOTI',
        'SAHJANWA',
        'PADRAUNA',
        'NAINITAL',
        'VIKARABAD',
        'SANTIPUR',
        'MATHABHANGA',
        'ANDHRA PRADESH',
        'PANAJI',
        'SANAWAD',
        'SINGRAULI',
        'DHARMAVARAM',
        'SAUSAR',
        'MANDI',
        'VANIYAMBADI',
        'MADIKERI',
        'MAHARASHTRA',
        'SHAJAPUR',
        'BALAGHAT',
        'TANUKU',
        'VADALUR',
        'RAJNANDGAON',
        'SATTENAPALLE',
        'RAMPURHAT',
        'MAHASAMUND',
        'MANDAPETA',
        'PRATAPGARH',
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
        'NANDED',
        'WAGHALA',
        'KALYAN',
        'DOMBIVALI',
        'BALESHWAR',
        'DALTONGANJ',
        'JALANDHAR',
        'JALANDHAR CANTT',
        'BHILAI',
        'NAGAR',
        'HUBLI',
        'DHARWAD',
        'THENI',
        'ALLINAGARAM',
        'MURWARA',
        'KATNI',
        'VASAI',
        'SEDAM',
        'TAMIL NADU',
        'KANDUKUR',
        'MANDLA',
        'REPALLE',
        'KASARAGOD',
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
        'JAMMU',
        'VYARA',
        'MALKANGIRI',
        'ANKLESHWAR',
        'ARAMBAGH',
        'NAILA JANJGIR',
        'MANDIDEEP',
        'KAPURTHALA',
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
        'KARNATAKA',
        'PEHOWA',
        'RAYAGADA',
        'AFZALPUR',
        'DEOGHAR',
        'PERUMBAVOOR',
        'KARAIKAL',
        'NAGARKURNOOL',
        'OTTAPPALAM',
        'RISHIKESH',
        'WADI',
        'NIRMAL',
        'PANRUTI',
        'RAVER',
        'SENDHWA',
        'TUNI',
        'SHEGAON',
        'SRIKALAHASTI',
        'PORBANDAR',
        'PARLI',
        'MANDVI',
        'TARN TARAN',
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
        'PINJORE',
        'PATNA',
        'AJMER',
        'JAMSHEDPUR',
        'BHILWARA',
        'ALWAR',
        'REWA',
        'PALWAL',
        'SIKAR',
        'KARNAL',
        'SAHARANPUR',
        'BARMER',
        'REWARI',
        'PALI',
        'MADHUBANI',
        'PANIPAT',
        'ROHTAK',
        'TONK',
        'FIROZPUR',
        'SIRSA',
        'MORENA',
        'RAE BARELI',
        'SONIPAT',
        'BHAGALPUR',
        'LONI',
        'SAMASTIPUR',
        'LAKHIMPUR',
        'BIKANER',
        'KOTA',
        'BAHRAICH',
        'PATIALA',
        'BHARATPUR',
        'NARNAUL',
        'RAIGARH',
        'NAGAUR',
        'UNNAO',
        'SEHORE',
        'SILIGURI',
        'HARDWAR',
        'DURG',
        'HOSHIARPUR',
        'MOTIHARI',
        'RAJSAMAND',
        'SHIKARPUR',
        'SITAMARHI',
        'PILIBHIT',
        'JALPAIGURI',
        'FIROZABAD',
        'BILASPUR',
        'HAPUR',
        'LALGANJ',
        'MANALI',
        'GUJARAT',
        'HAJIPUR',
        'FATEHABAD',
        'JIND',
        'IMPHAL',
        'SULTANPUR',
        'PALAMPUR',
        'YAMUNANAGAR',
        'KOTHAGUDEM',
        'ETAWAH',
        'RUDRAPUR',
        'VIRUDHUNAGAR',
        'SOHNA',
        'HALDWANI-CUM-KATHGODAM',
        'PAURI',
        'LALITPUR',
        'BAHADURGARH',
        'MAHENDRAGARH',
        'MALEGAON',
        'MAKRANA',
        'SHAHJAHANPUR',
        'KATIHAR',
        'SASARAM',
        'SITAPUR',
        'SILVASSA',
        'RAJGARH (CHURU)',
        'MADHEPURA',
        'UDAIPURWATI',
        'SIDLAGHATTA',
        'TELANGANA',
        'KHANNA',
        'NAWADA',
        'KERALA',
        'MUSSOORIE',
        'SUNDARNAGAR',
        'MOGA',
        'GOBINDGARH',
        'FAZILKA',
        'KISHANGANJ',
        'KAITHAL',
        'PATHANKOT',
        'KHAIR',
        'DIMAPUR',
        'LALGUDI',
        'RATNAGIRI',
        'NARWANA',
        'SIROHI',
        'SHAHPURA',
        'MANASA',
        'TIRUVETHIPURAM',
        'PUNCH',
        'UNA',
        'PHAGWARA',
        'TUNDLA',
        'LAR',
        'ANANTNAG',
        'GODHRA',
        'PILKHUWA',
        'SANGAMNER',
        'PUNJAB',
        'ZIRAKPUR',
        'JHUMRI TILAIYA',
        'KULLU',
        'VRINDAVAN',
        'PHULERA',
        'UDHAMPUR',
        'NEEM-KA-THANA',
        'HABRA',
        'MEDINIPUR',
        'BRAHMAPUR',
        'NANDYAL',
        'TIRUNELVELI',
        'PUDUKKOTTAI',
        'BHADRAK',
        'MANCHERIAL',
        'HUGLI-CHINSURAH',
        'KANNUR',
        'KORBA',
        'MIRYALAGUDA',
        'JHARSUGUDA',
        'PURULIA',
        'SUNDARGARH',
        'VIDISHA',
        'YAVATMAL',
        'HARDOI',
        'SAHARSA',
        'ALAPPUZHA',
        'TIRUCHENDUR',
        'SAMBALPUR',
        'PIPAR CITY',
        'SRIRAMPORE',
        'NABADWIP',
        'THANESAR',
        'BALANGIR',
        'BHONGIR',
        'BHAWANIPATNA',
        'JANGAON',
        'SHAHABAD',
        'TARAKESWAR',
        'RANAGHAT',
        'NAIHATI',
        'AMALNER',
        'BARGARH',
        'ROBERTSGANJ',
        'GIRIDIH',
        'SUMERPUR',
        'SOJAT',
        'NIMBAHERA',
        'JAMUI',
        'ADITYAPUR',
        'MARKAPUR',
        'MEMARI',
        'RAIGANJ',
        'TUMSAR',
        'NUZVID',
        'PANDHURNA',
        'PIPARIYA',
        'SIRCILLA',
        'PACODE',
        'CHATRA',
        'WASHIM',
        'SORO',
        'MANUGURU',
        'FORBESGANJ',
        'RANEBENNURU',
        'PALLADAM',
        'SITARGANJ',
        'RON',
        'SANCHORE',
        'ALIPURDURBAN AGGLOMERATIONR',
        'PEN',
        'RASIPURAM',
        'PORSA',
        'TURA',
        'VITA',
        'GUDIVADA',
        'MANER',
        'NAIDUPET',
        'SINDHNUR',
        'ADILABAD',
        'ARVI',
        'SHAMLI',
        'JAGRAON',
        'ARAKKONAM',
        'RAISINGHNAGAR',
        'YERRAGUNTLA',
        'RUDAULI',
        'MULTAI',
        'THODUPUZHA',
        'SIRKALI',
        'GUMIA',
        'RENIGUNTA',
        'SADULSHAHAR',
        'NARKATIAGANJ',
        'AMBIKAPUR',
        'MIHIJAM',
        'RAWATSAR',
        'TANDA',
        'SALAYA',
        'SUNAM',
        'MUKHED',
        'UJHANI',
        'USILAMPATTI',
        'JHARGRAM',
        'TODABHIM',
        'MAHIDPUR',
        'NELLIKUPPAM',
        'NANDIVARAM-GUDUVANCHERI',
        'VATAKARA',
        'MANAWAR',
        'MUNDI',
        'RASRA',
        'TENKASI',
        'KASHIPUR',
        'BHATAPARA',
        'ZIRA',
        'ROSERA',
        'SHEIKHPURA',
        'RAXAUL BAZAR',
        'PATTI',
        'BARNALA',
        'MAIHAR',
        'LAKHERI',
        'SAWANTWADI',
        'PANNA',
        'NEHTAUR',
        'ZAMANIA',
        'DHAMTARI',
        'ENGLISH BAZAR',
        'SAVANUR',
        'SADASIVPET',
        'BHAINSA',
        'PALASA KASIBUGGA',
        'GANJBASODA',
        'NAGLA',
        'PULIYANKUDI',
        'ARUPPUKKOTTAI',
        'NELAMANGALA',
        'KADIRI',
        'TAKI',
        'TARBHA',
        'SAFIDON',
        'BARIPADA TOWN',
        'SAKALESHAPURA',
        'UMBERGAON',
        'WOKHA',
        'CHIRMIRI',
        'THIRUTHURAIPOONDI',
        'SIRONJ',
        'MOTIPUR',
        'ORAI',
        'NANDURBAR',
        'SIMDEGA',
        'GOPALGANJ',
        'MORINDA',
        'BALURGHAT',
        'SANAND',
        'KHOWAI',
        'TEHRI',
        'CHANDAUSI',
        'PARDI',
        'SULLURPETA',
        'VENKATAGIRI',
        'SIKANDRA RAO',
        'ALIRAJPUR',
        'SRISAILAM PROJECT (RIGHT FLANK COLONY) TOWNSHIP',
        'PARAVOOR',
        'VAIKOM',
        'PATHANAMTHITTA',
        'NAGINA',
        'MATTANNUR',
        'PUNGANUR',
        'PUNALUR',
        'SATTUR',
        'MANGROL',
        'VADGAON KASBA',
        'MARMAGAO',
        'NEPANAGAR',
        'RABKAVI BANHATTI',
        'AIZAWL',
        'TARANAGAR',
        'RATANGARH',
        'PALAKKAD',
        'SHEOPUR',
        'RENUKOOT',
        'YADGIR',
        'NOHAR',
        'THIRUVALLA',
        'URAN',
        'NOKHA'
    ];

    protected $addressBlackList = [
        'BENGALURU',
        'BANGALORE',
        'MANGALURU',
        'MYSORE',
        'HUBLI-DHARWAD',
        'BELAGAVI',
        'KOLAR',
        'TUMKUR',
        'BALLARI',
        'SHIVAMOGGA',
        'DAVANAGERE',
        'MANDYA',
        'VIJAYAPURA',
        'UDUPI',
        'RAAYACHURU',
        'KARWAR',
        'CHIKKAMAGALURU',
        'MADIKERI',
        'PUTTUR',
        'HUBLI',
        'DHARWAD',
        'SEDAM',
        'ARSIKERE',
        'NANJANGUD',
        'KARNATAKA',
        'AFZALPUR',
        'WADI',
        'SIDLAGHATTA',
        'SHAHABAD',
        'RANEBENNURU',
        'RON',
        'SINDHNUR',
        'SAVANUR',
        'NELAMANGALA',
        'SAKALESHAPURA',
        'RABKAVI BANHATTI',
        'YADGIR',
        'HYDERABAD',
        'KARIMNAGAR',
        'KHAMMAM',
        'SANGAREDDY',
        'WARANGAL',
        'SURYAPET',
        'MEDAK',
        'RAMAGUNDAM',
        'SIDDIPET',
        'MAHBUBNAGAR',
        'NIZAMABAD',
        'KAMAREDDY',
        'VIKARABAD',
        'YELLANDU',
        'WANAPARTHY',
        'NAGARKURNOOL',
        'NIRMAL',
        'KOTHAGUDEM',
        'TELANGANA',
        'MANCHERIAL',
        'MIRYALAGUDA',
        'BHONGIR',
        'JANGAON',
        'SIRCILLA',
        'MANUGURU',
        'ADILABAD',
        'SADASIVPET',
        'BHAINSA',
        'KOCHI',
        'KOLLAM',
        'KOTTAYAM',
        'THIRUVANANTHAPURAM',
        'KOZHIKODE',
        'THRISSUR',
        'MALAPPURAM',
        'NEYYATTINKARA',
        'TIRUR',
        'KASARAGOD',
        'MUVATTUPUZHA',
        'CHERTHALA',
        'PERUMBAVOOR',
        'OTTAPPALAM',
        'MAVELIKKARA',
        'NILAMBUR',
        'KERALA',
        'KANNUR',
        'ALAPPUZHA',
        'THODUPUZHA',
        'VATAKARA',
        'PARAVOOR',
        'VAIKOM',
        'PATHANAMTHITTA',
        'MATTANNUR',
        'PUNALUR',
        'PALAKKAD',
        'THIRUVALLA',
        'CHENNAI',
        'PUNE'
    ];

    //传销敏感词
    protected $pyramidWords = [
        'pyramid scheme',
        'pyramid selling',
        'pyramid sale'
    ];
    //毒品敏感词
    protected $drugsWords = [
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
    protected $gamblingWords = [
        'gambling',
        'casino',
        'sands',
    ];
    //黑敏感词
    protected $blackWords = [
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
    protected $loanAppList = [
        '1MINUTEMEAADHARLOAN',
        '10MINUTELOAN',
        '360LOAN',
        '5NANCE',
        'AADHARPELOAN',
        'ABCASH',
        'ABHIPAISA',
        'AFLOANS',
        'AFINOZ',
        'AGRILOANAPPRAISER',
        'AIRLOAN',
        'ANYTIMELOAN',
        'APNAPAISA',
        'ASAPLOANFINDER',
        'ATDMONEY',
        'ATOMECREDIT',
        'AVAIL',
        'AVAILFINANCE',
        'BROBOCASH',
        'BAJAJFINSERVWALLET',
        'BAJAJFINSERV',
        'BALIKBAYADOFWANDSEAMANLOANS',
        'BANKLOANPROVIDER',
        'BETTRCREDIT',
        'BILLIONCASH',
        'BIZCAPITAL.IN',
        'BRANCH',
        'CAPITALFIRSTLIMITED',
        'CAPZEST',
        'CASHCREDIT',
        'CASHPOCKET',
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
        'CASHTMCASHTHRUMOBILE',
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
        'CUTEMONEY',
        'DEALSOFLOAN',
        'DHANADHAN',
        'DHANI',
        'DIGILEND',
        'EARLYSALARY',
        'EARNWEALTH',
        'EASYLOANS',
        'EASYMONEY',
        'EASYLOAN',
        'ERUPEE',
        'FAIRCENT',
        'FASTCASH',
        'FASTCASHLOAN',
        'FASTRUPEE',
        'FINACARI',
        'FINGERDEV',
        'FINNABLE',
        'FINSERVMARKETS',
        'FINWEGO',
        'FINZY',
        'FLASHCASH',
        'FLASHPAISA',
        'FLEXIMONEY',
        'FLEXILOANS',
        'FLEXSALARY',
        'FREELOAN',
        'FULLERTON',
        'FULLERTONINDIA',
        'FULLERTONINDIAINSTALOAN',
        'FULLERTONINDIAMCONNECT',
        'GOLDENLIGHTNING',
        'GORUPEE',
        'GOTOCASH',
        'HAPPYMONIES',
        'HDBFINANCIALSERVICESONTHEGO',
        'HDFCHOMELOANS',
        'HEROFINCORP',
        'HICASH',
        'HOMECREDIT',
        'HOMECREDITLOAN',
        'HOMEFIRSTCUSTOMERPORTAL',
        'I2IFUNDING',
        'ICREDIT',
        'IDFCFIRSTLOANS',
        'IEASYLOAN',
        'IGNITIVEAPPS',
        'IIFLLOANS',
        'I-LEND',
        'INCRED',
        'INDIABULLS',
        'INDIABULLSDHANIBIZ',
        'INDIABULLSHOMELOANS',
        'INDIALENDS',
        'INDIAMONEYMART',
        'INSTACASH7',
        'INSTAMONEY',
        'INSTANTMUDRA',
        'INSTANTPERSONALLOAN',
        'INSTAPAISA',
        'IRUPEE',
        'IZWALOANS',
        'KISSHT',
        'K-KCASH',
        'KRAZYBEE',
        'KREDITBEE',
        'KREDITONE',
        'KREDITZY',
        'KYEPOT',
        'LAZYPAY',
        'LECRED',
        'LEEWAYLOAN',
        'LENDENCLUB',
        'LENDINGADDA',
        'LENDINGKART',
        'LENDINGKARTINSTANTBUSINESSLOANS',
        'LENDKARO',
        'LENDTEK',
        'LEYLANDORCHARD',
        'LIGHTNINGLOAN',
        'LOANAGENT',
        'LOANASSIST',
        'LOANDOST',
        'LOANFRAME',
        'LOANFRAMEPARTNER',
        'LOANGLOBALLY',
        'LOANNOW',
        'LOANRAJA',
        'LOANSHARK',
        'LOANTIGER',
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
        'LOANSCHAPCHAP',
        'LOANS.COM.AUSMARTMONEY',
        'LOANSIMPLE',
        'LOANSINGH',
        'LOANSUMO',
        'LOANTAP',
        'LOANU',
        'LOANWIX',
        'LOANX',
        'L-PESA',
        'MAHINDRAFINANCE',
        'MANAPPURAMPERSONALLOAN',
        'MAXLOAN',
        'MICREDIT',
        'MOLP2P',
        'MOMO',
        'MONCASH',
        'MONEED',
        'MONEXO',
        'MONEYINMINUTES',
        'MONEYVIEW',
        'MONEYVIEWLOANS',
        'MONEYENJOY',
        'MONEYMORE',
        'MONEYTAP',
        'MONEYTAPCREDIT',
        'MONEYWOW',
        'MORERUPEE',
        'MPOKKET',
        'MUDRAKIWK',
        'MUDRAKWIK',
        'MUTHOOTBLUE',
        'MYLOANCARE',
        'MYLOANMITRA',
        'MYSTRO',
        'NAMASTEBIZ',
        'NAMASTECREDIT',
        'NAMASTECREDITLOANHUB',
        'NAMASTECREDITNEWLOAN',
        'NANOCRED',
        'NIRA',
        'OCASH',
        'OFFERMELOAN',
        'OKCASH',
        'OKOACASHLOANS',
        'ONLINELOANINFORMATION',
        'OPTACREDIT',
        'OYELOANS',
        'PAISABAZAAR',
        'PAISADUKAAN',
        'PAYME',
        'PAYMEINDIA',
        'PAYSENSE',
        'PAYSENSEPARTNER',
        'PEERLEND',
        'PERKFINANCE',
        'PHOCKET',
        'PHONEPARLOAN',
        'POCKETLOAN',
        'QBERA',
        'QUICKLOAN',
        'QUICKCREDIT',
        'QUIKKLOAN',
        'RAPIDRUPEE',
        'REDCARPET',
        'REVFIN',
        'ROBOCASH',
        'RSLOAN',
        'RUBIQUE',
        'RUPEEMAX',
        'RUPEEBOX',
        'RUPEEBUS',
        'RUPEECASH',
        'RUPEECIRCLE',
        'RUPEECLUB',
        'RUPEEHUB',
        'RUPEEK',
        'RUPEELEND',
        'RUPEELOAN',
        'RUPEEPLUS',
        'RUPEEREDEE',
        'RUPEESTAR',
        'SAHUKAR',
        'SALARYDOST',
        'SALARYNOW',
        'SALT',
        'SALTAPP',
        'SANKALPINDIAFINANCE',
        'SBIHOMELOANS',
        'SBILOANS',
        'SHUBH',
        'SHUBHLOANS',
        'SIMPLYCASH',
        'SLICEPAY',
        'SMARTMONEY',
        'SMARTCOIN',
        'SNAPMINT',
        'SPEEDCASH',
        'STASHFIN',
        'STASHFINELEV8',
        'STUCRED',
        'SUPERCASH',
        'TACHYLOANS',
        'TATACAPITALMOBILEAPP',
        'THIRDFEDERALSAVINGS&LOAN',
        'TRUEBALANCE',
        'TVSCREDITSAATHI',
        'UA',
        'UCASH',
        'UPWARDS',
        'VSERVELOANS',
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
        'SWEETRUPEE',
        'UUCASH',
        'CASHWIN',
        'FLASHLOAN',
        'CASHKING',
        'CASHKART',
        'GOCASH',
        'CASHHOUSE',
        'HAPPYLOAN',
        'INSTANTLOAN',
        'CASHFLOW',
        'FREECREDITSCORE,LOANS,CARDS&MUTUALFUNDS',
        'CASHBANK',
        'MONEYVIEWMONEYMANAGERANDINSTANTPERSONALLOAN',
        'INSTARUPEE',
        'CREDITCASH',
        'LOANS&CASHADVANCE',
        'IIFLLOANS',
        'FTCASH',
        'POCKETLY',
        'MIRUPEES',
        'ANYDAYMONEY',
        'STUDENTLOANSMONEYCOMPASS',
        '5PAISALOANS',
        'TAMAM',
        'WAY2MONEY-BUSINESSLOANS',
        'SPEEDYPAYDAYLOANS',
        'LENDBOX|P2PLENDING',
        'CASHMONEY',
        'FASTINR',
        'LOSTAR',
        'READYCASH',
        'LCLOAN',
        'LEADINGCASH',
        'KREDITBEAR',
        'TALALOAN',
        'MM',
        'MMELON',
        'INDILOAN',
        'CASHMAP',
        'RSRUSH',
        'RAYTHEONLOAN',
        'FLASHRUPEE',
        'INSTALOAN',
        'KRAZYRUPEE',
        'RUPEETIME',
        'CASHCAT',
        'SPEEDYRUPEE',
        'MONEYUP',
        'LOANFRESH',
        'CREDITFINCH',
        'MONEYTRIP',
        'RUPEEPLUS&FLASHKASH',
        'SMARTLOAN',
        'MICROLOAN',
        'WISEKREDIT',
        'MXLLTD',
        'CASHMENU',
        'LENDINGBEAN',
        'RUPEEM',
        'RUPEEHOME',
        'GOLDCASH',
        'GETACASH',
        'SILVERKREDIT',
        'MONEYHOME',
        'TAPCREDIT',
        'RUPEEDAY',
        'QUICKCASH',
        'DOOLOAN',
        'MCASH',
        'RUPEEK',
        'FACASH',
        'KRAZYRUPEE',
        'CASHDADDY',
        'LOANSTAR',
        'MAGICRUPEES',
        'UJJIVAN',
        'SUPERRUPEE',
        'CASHBIRD',
        'BAJAJFINANCELIMITED',
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
        if (InfoOrder::ENUM_IS_FIRST_Y == $this->data->infoOrder->is_first) {
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
        $birthday = $this->data->infoUser->pan_birthday;
        $date = Carbon::rawCreateFromFormat('Y-m-d', $birthday);
        return $date->diffInYears(Carbon::now());
    }

    /**
     * 教育程度
     * @return int  1 大学及以上   0 大学以下
     */
    public function checkHighEducationLevel()
    {
        return $this->data->infoUser->education_level;
    }

    /**
     * 行业
     * @return int
     */
    public function checkIndustry()
    {
        return $this->data->infoUser->occupation;
    }

    /**
     * 居住地址是否命中本平台白名单地区
     * @return int   1 命中   0 未命中
     */
    public function checkResidentialAddressHitWhiteList()
    {
        $city = $this->data->infoUser->residential_city;

        if(in_array(strtoupper($city), $this->addressWhiteList)){
            return 1;
        }

        return 0;
    }

    /**
     * 居住地址是否命中黑名单地区
     * @return int   1 命中   0 未命中
     */
    public function checkResidentialAddressHitBlacklist()
    {
        $city = $this->data->infoUser->residential_city;

        if(in_array(strtoupper($city), $this->addressBlackList)){
            return 1;
        }

        return 0;
    }

    /**
     * 身份证号是否命中自有黑名单
     * @return int   1 命中   0 未命中
     */
    public function checkIDCardHitBlackList()
    {
        if(!isset($this->data->infoUser->aadhaar_md5)){
            return 0;
        }
       $aadhaarIds = [
            $this->data->infoUser->aadhaar_md5
        ];
        $service = new RiskBlackListService();
        if($service->checkHitByAadhaar($aadhaarIds))
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
            $this->data->infoUser->phone
        ];
        $service = new RiskBlackListService();
        if($service->checkHitByPhones($phones))
        {
            return 1;
        }else{
            return 0;
        }
    }

    /**
     * Pan卡号是否命中自有黑名单
     * @return int
     */
    public function checkPanCardHitBlackList(){
        $pan = [
            $this->data->infoUser->pan_code
        ];
        $service = new RiskBlackListService();
        if($service->checkHitByPan($pan))
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
        if(!isset($this->data->infoDevice->device_id)){
            return 0;
        }
        $deviceIds = [
            $this->data->infoDevice->device_id
        ];
        $service = new RiskBlackListService();
        if ($service->checkHitByDeviceIds($deviceIds)) {
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
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }
        $deviceIds = [
            $this->data->infoDevice->szlm_query_id
        ];
        $service = new RiskBlackListService();
        if ($service->checkHitBySMDeviceIds($deviceIds)) {
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
        return InfoRepayment::find()->alias('r')
            ->leftJoin(InfoUser::tableName(). ' as u', 'r.user_id=u.user_id and r.order_id=u.order_id and r.app_name=u.app_name')
            ->where(['r.is_overdue' => InfoRepayment::OVERDUE_YES])
            ->andWhere(['or', ['u.contact1_mobile_number' => $this->data->infoUser->phone], ['u.contact2_mobile_number' => $this->data->infoUser->phone]])
            ->count('DISTINCT r.app_name,r.user_id');
    }

    /**
     * 紧急联系人的手机号命中自有黑名单
     * @return int   0 未命中   1 命中  -1 获取异常
     */
    public function checkContactMobileHitBlackList()
    {
        return 0;
    }

    /**
     * 申请手机号是近1个月内申请用户紧急联系人手机号码的数量
     * @return int|string
     */
    public function checkMobileSameAsContactMobileCntLast1Month()
    {
        $phone = $this->data->infoUser->phone;
        $lastTime = strtotime('last month');
        return InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'o.user_id=u.user_id and o.order_id=u.order_id and o.app_name=u.app_name')
            ->where(['>=', 'o.order_time', $lastTime])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->andWhere(['or', ['u.contact1_mobile_number' => $phone], ['u.contact2_mobile_number' => $phone]])
            ->groupBy(['u.pan_code'])->count();
    }

    /**
     * 获取该pan下所有用户紧急联系人
     * @param $pan
     * @return array|mixed
     */
    protected function getPanUserContacts($pan)
    {
        $key = "{$pan}";
        if (isset($this->panUserContacts[$key])) {
            return $this->panUserContacts[$key];
        } else {
            $userContact = InfoUser::find()
                ->select(['contact1_mobile_number', 'contact2_mobile_number'])
                ->where(['pan_code' => $pan])
                ->asArray()->all();
            $phones = array_unique(array_merge(
                ArrayHelper::getColumn($userContact, 'contact1_mobile_number'),
                ArrayHelper::getColumn($userContact, 'contact2_mobile_number')
            ));
            return $this->panUserContacts[$key] = $phones;
        }
    }

    /**
     * 紧急联系人的手机号码近1个月内在本平台出现的次数
     * @return mixed
     */
    public function checkSameContactCntLast1Month()
    {
        $phones = $this->getPanUserContacts($this->data->infoUser->pan_code);
        $lastTime = strtotime('last month');
        $count1 = InfoUser::find()
            ->where(['contact1_mobile_number' => $phones])
            ->andWhere(['>=', 'created_at', $lastTime])
            ->andWhere(['<=', 'created_at', $this->data->infoOrder->order_time])
            ->groupBy(['pan_code'])
            ->count();
        $count2 = InfoUser::find()
            ->where(['contact2_mobile_number' => $phones])
            ->andWhere(['>=', 'created_at', $lastTime])
            ->andWhere(['<=', 'created_at', $this->data->infoOrder->order_time])
            ->groupBy(['pan_code'])
            ->count();
        return max($count1, $count2);
    }

    /**
     * 紧急联系人的手机号是否为有效手机号
     * @return int  1 两个号码均有效   0 至少有一个号码无效
     */
    public function checkContactMobileIsValid()
    {
        $phones = $this->getPanUserContacts($this->data->infoUser->pan_code);
        foreach ($phones as $phone) {
            if (!preg_match(Util::getPhoneMatch(),$phone)) {
                return 0;
            }
        }

        return 1;
    }

    /**
     * 紧急联系人的手机号是否为申请手机号
     * @return int  0 不是   1 是
     */
    public function checkContactMobileSameAsApplyMobile()
    {
        $phones = $this->getPanUserContacts($this->data->infoUser->pan_code);
        if (in_array($this->data->infoUser->phone, $phones)
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
        $phones = $this->getPanUserContacts($this->data->infoUser->pan_code);

        return InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName().' as u', 'r.order_id=u.order_id and r.user_id=u.user_id and r.app_name=u.app_name')
            ->where(['r.is_overdue' => InfoRepayment::OVERDUE_YES, 'u.phone' => $phones])
            ->groupBy(['u.phone'])
            ->count();
    }

    /**
     * 紧急联系人的手机号命中逾期30+用户手机号的数量
     * @return int|string
     */
    public function checkContactNameMobileHitOver30OverdueUserMobileCnt()
    {
        $phones = $this->getPanUserContacts($this->data->infoUser->pan_code);
        return InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName().' as u', 'r.order_id=u.order_id and r.user_id=u.user_id and r.app_name=u.app_name')
            ->where(['r.is_overdue' => InfoRepayment::OVERDUE_YES, 'u.phone' => $phones])
            ->andWhere(['>=', 'r.overdue_day', 30])
            ->groupBy(['u.phone'])
            ->count();
    }

    /**
     * 紧急联系人的手机号命中逾期用户的紧急联系人手机号数量
     * @return int|string
     */
    public function checkContactNameMobileHitOverdueUserContactMobileCnt()
    {
        $phones = $this->getPanUserContacts($this->data->infoUser->pan_code);

        return InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName().' as u', 'r.order_id=u.order_id and r.user_id=u.user_id and r.app_name=u.app_name')
            ->where(['r.is_overdue' => InfoRepayment::OVERDUE_YES])
            ->andWhere(['or', ['u.contact1_mobile_number' => $phones], ['u.contact2_mobile_number' => $phones]])
            ->groupBy(['u.pan_code'])
            ->count();
    }

    /**
     * 紧急联系人的手机号命中逾期30+用户的紧急联系人手机号数量
     * @return int|string
     */
    public function checkContactNameMobileHitOver30OverdueUserContactMobileCnt()
    {
        $phones = $this->getPanUserContacts($this->data->infoUser->pan_code);

        return InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName().' as u', 'r.order_id=u.order_id and r.user_id=u.user_id and r.app_name=u.app_name')
            ->where(['r.is_overdue' => InfoRepayment::OVERDUE_YES])
            ->andWhere(['>=', 'r.overdue_day', 30])
            ->andWhere(['or', ['u.contact1_mobile_number' => $phones], ['u.contact2_mobile_number' => $phones]])
            ->groupBy(['u.pan_code'])
            ->count();
    }

    /**
     * 通讯录信息
     * @param $phone
     * @param int $type
     * @return mixed
     */
    protected function getContactByUserId($phone, $type=0)
    {
        if (isset($this->userContacts[$phone])) {
            return $this->userContacts[$phone][$type];
        } else {
            if($this->data->infoOrder->is_external == 'y'){
                $app_name = $this->data->infoOrder->external_app_name;
            }else{
                $app_name = $this->data->order->app_name;
            }
            $mobiles = MgUserMobileContacts::find()
                ->where(['user_phone' => intval($phone),
                         'app_name' => $app_name])
                ->asArray()
                ->all();

            $count1 = 0;
            $count7 = 0;
            $count30 = 0;
            $time = strtotime(date('Y-m-d', $this->data->infoOrder->order_time));

            $order_begin1 = $time - 86400;
            $order_begin7 = $time - 86400 * 7;
            $order_begin30 = $time - 86400 * 30;
            $arr = [];
            $arr2 = [];
            foreach ($mobiles as $v){
                if(!isset($v['contactLastUpdatedTimestamp'])){
                    continue;
                }
                if($v['contactLastUpdatedTimestamp'] >= $order_begin1){
                    $count1++;
                }

                if($v['contactLastUpdatedTimestamp'] >= $order_begin7){
                    $count7++;
                }

                if($v['contactLastUpdatedTimestamp'] >= $order_begin30){
                    $count30++;
                }

                $day = ($time - strtotime(date('Y-m-d', $v['contactLastUpdatedTimestamp']))) / 86400;
                $arr[] = $day;

                if(in_array(substr($v['mobile'], -10), [$this->data->infoUser->contact1_mobile_number, $this->data->infoUser->contact2_mobile_number])){
                    $arr2[] = $day;
                }
            }

            $data = [
                0 => $mobiles,
                1 => $count1,
                7 => $count7,
                30 => $count30,
                31 => $arr,
                32 => $arr2,
            ];

            $this->userContacts[$phone] = $data;
            return $this->userContacts[$phone][$type];
        }
    }

    /**
     * 通讯录中联系人数量
     * @return int
     */
    public function checkAddressBookContactCnt()
    {
        $mobiles = $this->getContactByUserId($this->data->infoUser->phone);
        return count($mobiles);
    }

    /**
     * 有效号码占比
     * @return int （%）
     */
    public function checkValidMobileRatio()
    {
        $validCount = 0;
        $totalCount = 0;
        $mobiles = $this->getContactByUserId($this->data->infoUser->phone);
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
     * 数字组合出现的最高频率
     */
    public function checkDigitComboMaxFrequency()
    {
        $allCombination = [];
        $totalCount = 0;
        $mobiles = $this->getContactByUserId($this->data->infoUser->phone);
        foreach ($mobiles as $mobile) {
            $totalCount++;
            $mobile = substr($mobile['mobile'], 4);
            $length = strlen($mobile);
            $two = [];
            $three = [];
            for ($i = 0; $i < $length; $i++) {
                if (isset($mobile[$i + 1])) {
                    //取两位
                    $two[] = $mobile[$i] . $mobile[$i + 1];
                }
                if (isset($mobile[$i + 2])) {
                    //取三位
                    $three[] = $mobile[$i] . $mobile[$i + 1] . $mobile[$i + 2];
                }
            }
            $two = array_unique($two);
            $three = array_unique($three);
            $allCombination = array_merge($two, $three, $allCombination);
        }
        $allCombination = array_count_values($allCombination);
        if ($totalCount == 0 || empty($allCombination)) {
            return 0;
        }
        return intval(round(max($allCombination) / $totalCount * 100));
    }

    /**
     * 通讯录备注名中涉赌类人数
     */
    public function checkContactInvolveGambleCnt()
    {
        $count = 0;
        $mobiles = $this->getContactByUserId($this->data->infoUser->phone);
        foreach ($mobiles as $mobile){
            foreach ($this->gamblingWords as $word){
                $wList = explode(' ',$word);
                $flag = 0;
                foreach ($wList as $sWord){
                    if(isset($mobile['name']) && strstr(strtolower($mobile['name']),strtolower($sWord))){
                        $flag = 1;
                    }else{   //只要有一个单词找不到就不包含
                        $flag = 0;
                        break;
                    }
                }
                $count += $flag;
            }
        }
        return $count;
    }

    /**
     * 通讯录备注名中涉毒类人数
     */
    public function checkContactInvolveNarcoticsCnt()
    {
        $count = 0;
        $mobiles = $this->getContactByUserId($this->data->infoUser->phone);
        foreach ($mobiles as $mobile){
            foreach ($this->drugsWords as $word){
                $wList = explode(' ',$word);
                $flag = 0;
                foreach ($wList as $sWord){
                    if(isset($mobile['name']) && strstr(strtolower($mobile['name']),strtolower($sWord))){
                        $flag = 1;
                    }else{   //只要有一个单词找不到就不包含
                        $flag = 0;
                        break;
                    }
                }
                $count += $flag;
            }
        }
        return $count;
    }

    /**
     * 通讯录备注名中涉黑类人数
     */
    public function checkContactInvolveGanglandCnt()
    {
        $count = 0;
        $mobiles = $this->getContactByUserId($this->data->infoUser->phone);
        foreach ($mobiles as $mobile){
            foreach ($this->blackWords as $word){
                $wList = explode(' ',$word);
                $flag = 0;
                foreach ($wList as $sWord){
                    if(isset($mobile['name']) && strstr(strtolower($mobile['name']),strtolower($sWord))){
                        $flag = 1;
                    }else{   //只要有一个单词找不到就不包含
                        $flag = 0;
                        break;
                    }
                }
                $count += $flag;
            }
        }
        return $count;
    }


    /**
     * 通讯录备注名中涉传销类人数
     */
    public function checkContactInvolvePyramidSaleCnt()
    {
        $count = 0;
        $mobiles = $this->getContactByUserId($this->data->infoUser->phone);
        foreach ($mobiles as $mobile){
            foreach ($this->pyramidWords as $word){
                $wList = explode(' ',$word);
                $flag = 0;
                foreach ($wList as $sWord){
                    if(isset($mobile['name']) && strstr(strtolower($mobile['name']),strtolower($sWord))){
                        $flag = 1;
                    }else{   //只要有一个单词找不到就不包含
                        $flag = 0;
                        break;
                    }
                }
                $count += $flag;
            }
        }
        return $count;
    }

    /**
     * 近30天内紧急联系人为互通联系人人数
     * @return int
     */
    public function checkMutualCallContactCntLast30Days(){
        $begin_time = strtotime('-30 day', $this->data->infoOrder->order_time);

        if(empty($this->data->infoUser->contact1_mobile_number) || empty($this->data->infoUser->contact2_mobile_number)){
            return -1;
        }

        $phone_arr = [
            $this->data->infoUser->contact1_mobile_number,
            $this->data->infoUser->contact2_mobile_number
        ];

        $phone = array_unique($phone_arr);

        if($this->data->infoOrder->is_external == 'y'){
            $app_name = $this->data->infoOrder->external_app_name;
        }else{
            $app_name = $this->data->order->app_name;
        }
        $data = MgUserCallReports::find()
            ->select(['callNumber'])
            ->where(['user_phone' => intval($this->data->infoUser->phone),
                     'app_name' => $app_name,
                     'callType' => [1, 2]])
            ->andWhere(['>=', 'callDateTime', $begin_time])
            ->asArray()
            ->column();

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
     * @throws \yii\mongodb\Exception
     */
    public function checkTotalDialCalledCntLast30Days(){
        $begin_time = strtotime('-30 day', $this->data->infoOrder->order_time);

        if($this->data->infoOrder->is_external == 'y'){
            $app_name = $this->data->infoOrder->external_app_name;
        }else{
            $app_name = $this->data->order->app_name;
        }
        return MgUserCallReports::find()
            ->select(['callNumber'])
            ->where(['user_phone' => intval($this->data->infoUser->phone),
                     'app_name' => $app_name,
                     'callType' => [1, 2]])
            ->andWhere(['>=', 'callDateTime', $begin_time])
            ->andWhere(['>', 'callDuration', 0])
            ->asArray()
            ->count();
    }

    /**
     * 近30天内的总通话时长(s)
     * @return int
     */
    public function checkTotalDialCalledDurationLast30Days(){
        $begin_time = strtotime('-30 day', $this->data->infoOrder->order_time);

        if($this->data->infoOrder->is_external == 'y'){
            $app_name = $this->data->infoOrder->external_app_name;
        }else{
            $app_name = $this->data->order->app_name;
        }
        $data = MgUserCallReports::find()
            ->select(['callDuration'])
            ->where(['user_phone' => intval($this->data->infoUser->phone),
                     'app_name' => $app_name,
                     'callType' => [1, 2]])
            ->andWhere(['>=', 'callDateTime', $begin_time])
            ->andWhere(['>', 'callDuration', 0])
            ->asArray()
            ->column();

        return array_sum($data);
    }

    /**
     * 近30天内1-5点的通话次数
     * @return int
     */
    public function checkTotal1amTo5amDialCalledCntLast30Days(){
        $begin_time = strtotime('-30 day', $this->data->infoOrder->order_time);

        if($this->data->infoOrder->is_external == 'y'){
            $app_name = $this->data->infoOrder->external_app_name;
        }else{
            $app_name = $this->data->order->app_name;
        }
        $data = MgUserCallReports::find()
            ->select(['callDateTime'])
            ->where(['user_phone' => intval($this->data->infoUser->phone),
                     'app_name' => $app_name,
                     'callType' => [1, 2]])
            ->andWhere(['>=', 'callDateTime', $begin_time])
            ->andWhere(['>', 'callDuration', 0])
            ->asArray()
            ->column();

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
     * @return float|int
     * @throws \yii\mongodb\Exception
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
        $begin_time = strtotime('-30 day', $this->data->infoOrder->order_time);

        if($this->data->infoOrder->is_external == 'y'){
            $app_name = $this->data->infoOrder->external_app_name;
        }else{
            $app_name = $this->data->order->app_name;
        }
        $data = MgUserCallReports::find()
            ->select(['callNumber'])
            ->where(['user_phone' => intval($this->data->infoUser->phone),
                     'app_name' => $app_name,
                     'callType' => [1, 2]])
            ->andWhere(['>=', 'callDateTime', $begin_time])
            ->andWhere(['>', 'callDuration', 0])
            ->asArray()
            ->column();

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
        $begin_time = strtotime('-30 day', $this->data->infoOrder->order_time);

        if($this->data->infoOrder->is_external == 'y'){
            $app_name = $this->data->infoOrder->external_app_name;
        }else{
            $app_name = $this->data->order->app_name;
        }
        $data = MgUserCallReports::find()
            ->select(['callDateTime','callNumber'])
            ->where(['user_phone' => intval($this->data->infoUser->phone),
                     'app_name' => $app_name,
                     'callType' => [1, 2]])
            ->andWhere(['>=', 'callDateTime', $begin_time])
            ->andWhere(['>', 'callDuration', 0])
            ->asArray()
            ->all();

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
        $begin_time = strtotime('-30 day', $this->data->infoOrder->order_time);

        if($this->data->infoOrder->is_external == 'y'){
            $app_name = $this->data->infoOrder->external_app_name;
        }else{
            $app_name = $this->data->order->app_name;
        }
        $data = MgUserCallReports::find()
            ->select(['callDateTime'])
            ->where(['user_phone' => intval($this->data->infoUser->phone),
                     'app_name' => $app_name,
                     'callType' => 2])
            ->andWhere(['>=', 'callDateTime', $begin_time])
            ->andWhere(['>', 'callDuration', 0])
            ->asArray()
            ->column();

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
        $begin_time = strtotime('-30 day', $this->data->infoOrder->order_time);

        if($this->data->infoOrder->is_external == 'y'){
            $app_name = $this->data->infoOrder->external_app_name;
        }else{
            $app_name = $this->data->order->app_name;
        }
        $data = MgUserCallReports::find()
            ->select(['callDateTime','callNumber'])
            ->where(['user_phone' => intval($this->data->infoUser->phone),
                     'app_name' => $app_name,
                     'callType' => 2])
            ->andWhere(['>=', 'callDateTime', $begin_time])
            ->andWhere(['>', 'callDuration', 0])
            ->asArray()
            ->all();

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
        $begin_time = strtotime('-30 day', $this->data->infoOrder->order_time);

        if($this->data->infoOrder->is_external == 'y'){
            $app_name = $this->data->infoOrder->external_app_name;
        }else{
            $app_name = $this->data->order->app_name;
        }
        $data = MgUserCallReports::find()
            ->select(['callDateTime'])
            ->where(['user_phone' => intval($this->data->infoUser->phone),
                     'app_name' => $app_name,
                     'callType' => [1, 2]])
            ->andWhere(['>=', 'callDateTime', $begin_time])
            ->andWhere(['>', 'callDuration', 0])
            ->andWhere(['<=', 'callDuration', 5])
            ->asArray()
            ->column();

        return count($data);
    }

    /**
     * 近30天内通话时长为(0s,5s]的通话次数占比=162/153
     * @return float|int
     * @throws \yii\mongodb\Exception
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
        $begin_time = strtotime('-30 day', $this->data->infoOrder->order_time);

        if($this->data->infoOrder->is_external == 'y'){
            $app_name = $this->data->infoOrder->external_app_name;
        }else{
            $app_name = $this->data->order->app_name;
        }
        $data = MgUserCallReports::find()
            ->select(['callNumber'])
            ->where(['user_phone' => intval($this->data->infoUser->phone),
                     'app_name' => $app_name,
                     'callType' => [1, 2]])
            ->andWhere(['>=', 'callDateTime', $begin_time])
            ->andWhere(['>', 'callDuration', 0])
            ->andWhere(['<=', 'callDuration', 5])
            ->asArray()
            ->column();

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
        $begin_time = strtotime('-30 day', $this->data->infoOrder->order_time);

        if($this->data->infoOrder->is_external == 'y'){
            $app_name = $this->data->infoOrder->external_app_name;
        }else{
            $app_name = $this->data->order->app_name;
        }
        $data = MgUserCallReports::find()
            ->select(['callDateTime'])
            ->where(['user_phone' => intval($this->data->infoUser->phone),
                     'app_name' => $app_name,
                     'callType' => 2])
            ->andWhere(['>=', 'callDateTime', $begin_time])
            ->andWhere(['>', 'callDuration', 0])
            ->andWhere(['<=', 'callDuration', 5])
            ->asArray()
            ->column();

        return count($data);
    }

    /**
     * 近30天内呼出时长为(0s,5s]的去重号码数
     * @return int
     */
    public function checkTotalLessThan5sDialMobileCntLast30Days(){
        $begin_time = strtotime('-30 day', $this->data->infoOrder->order_time);

        if($this->data->infoOrder->is_external == 'y'){
            $app_name = $this->data->infoOrder->external_app_name;
        }else{
            $app_name = $this->data->order->app_name;
        }
        $data = MgUserCallReports::find()
            ->select(['callNumber'])
            ->where(['user_phone' => intval($this->data->infoUser->phone),
                     'app_name' => $app_name,
                     'callType' => 2])
            ->andWhere(['>=', 'callDateTime', $begin_time])
            ->andWhere(['>', 'callDuration', 0])
            ->andWhere(['<=', 'callDuration', 5])
            ->asArray()
            ->column();

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
        $begin_time = strtotime('-3 month', $this->data->infoOrder->order_time);

        if($this->data->infoOrder->is_external == 'y'){
            $app_name = $this->data->infoOrder->external_app_name;
        }else{
            $app_name = $this->data->order->app_name;
        }
        $data = MgUserCallReports::find()
            ->select(['callNumber','callDateTime'])
            ->where(['user_phone' => intval($this->data->infoUser->phone),
                     'app_name' => $app_name,
                     'callType' => 2])
            ->andWhere(['>=', 'callDateTime', $begin_time])
            ->andWhere(['>', 'callDuration', 0])
            ->asArray()
            ->all();

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
        $begin_time = strtotime('-6 month', $this->data->infoOrder->order_time);

        if($this->data->infoOrder->is_external == 'y'){
            $app_name = $this->data->infoOrder->external_app_name;
        }else{
            $app_name = $this->data->order->app_name;
        }
        $data = MgUserCallReports::find()
            ->select(['callNumber'])
            ->where(['user_phone' => intval($this->data->infoUser->phone),
                     'app_name' => $app_name,
                     'callType' => 2])
            ->andWhere(['>=', 'callDateTime', $begin_time])
            ->andWhere(['>', 'callDuration', 0])
            ->asArray()
            ->column();

        $zj_phone_arr = [];
        foreach ($data as $v){
            $zj_phone_arr[] = substr($v, -10);
        }

        $data = MgUserCallReports::find()
            ->select(['callNumber'])
            ->where(['user_phone' => intval($this->data->infoUser->phone),
                     'app_name' => $app_name,
                     'callType' => 1])
            ->andWhere(['>=', 'callDateTime', $begin_time])
            ->andWhere(['>', 'callDuration', 0])
            ->asArray()
            ->column();

        $bj_phone_arr = [];
        foreach ($data as $v){
            $bj_phone_arr[] = substr($v, -10);
        }

        return count(array_intersect(array_unique($zj_phone_arr), array_unique($bj_phone_arr)));
    }

    /**
     * 近6个月内通话记录主叫次数
     * @return int
     */
    public function checkDialRatioLast6Months(){
        $begin_time = strtotime('-6 month', $this->data->infoOrder->order_time);

        if($this->data->infoOrder->is_external == 'y'){
            $app_name = $this->data->infoOrder->external_app_name;
        }else{
            $app_name = $this->data->order->app_name;
        }
        $data = MgUserCallReports::find()
            ->select(['callNumber'])
            ->where(['user_phone' => intval($this->data->infoUser->phone),
                     'app_name' => $app_name,
                     'callType' => 2])
            ->andWhere(['>=', 'callDateTime', $begin_time])
            ->andWhere(['>', 'callDuration', 0])
            ->asArray()
            ->column();

        $data_num = MgUserCallReports::find()
            ->select(['callNumber'])
            ->where(['user_phone' => intval($this->data->infoUser->phone),
                     'app_name' => $app_name,
                     'callType' => [1, 2]])
            ->andWhere(['>=', 'callDateTime', $begin_time])
            ->andWhere(['>', 'callDuration', 0])
            ->asArray()
            ->column();

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
        $begin_time = strtotime('-6 month', $this->data->infoOrder->order_time);

        if($this->data->infoOrder->is_external == 'y'){
            $app_name = $this->data->infoOrder->external_app_name;
        }else{
            $app_name = $this->data->order->app_name;
        }
        $data = MgUserCallReports::find()
            ->select(['callNumber'])
            ->where(['user_phone' => intval($this->data->infoUser->phone),
                     'app_name' => $app_name,
                     'callType' => 1])
            ->andWhere(['>=', 'callDateTime', $begin_time])
            ->andWhere(['>', 'callDuration', 0])
            ->asArray()
            ->column();

        $data_num = MgUserCallReports::find()
            ->select(['callNumber'])
            ->where(['user_phone' => intval($this->data->infoUser->phone),
                     'app_name' => $app_name,
                     'callType' => [1, 2]])
            ->andWhere(['>=', 'callDateTime', $begin_time])
            ->andWhere(['>', 'callDuration', 0])
            ->asArray()
            ->column();

        if(empty($data_num)){
            return 0;
        }

        return round(count($data) / count($data_num) * 100);
    }

    /**
     * 发出短信信息文本命中毒、赌、黑或传销等不良产业的敏感词的短信数量
     * @return int
     */
    public function checkSensitiveWordSentSMSCnt(){
        $class = MgUserMobileSmsService::getModelName($this->data->infoUser->pan_code);
        $sms = $class::find()
            ->where(['pan_code' => $this->data->infoUser->pan_code,
                     'type' => 2])
            ->select(['messageContent'])
            ->asArray()
            ->all();

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
        $begin_time = strtotime('-6 month', $this->data->infoOrder->order_time);
        $class = MgUserMobileSmsService::getModelName($this->data->infoUser->pan_code);
        $sms = $class::find()
            ->select(['messageContent'])
            ->where(['pan_code' => $this->data->infoUser->pan_code,
                     'type' => 1])
            ->andWhere(['>=', 'messageDate',$begin_time])
            ->asArray()
            ->all();

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
     * 用户申请时间段
     * @return int
     */
    public function checkApplyTimeHour()
    {
        return intval(date('H', $this->data->infoOrder->order_time));
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
            $count = InfoOrder::find()
                ->alias('o')
                ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
                ->where(['d.ip' => $ip])
                ->andWhere(['>=', 'o.order_time', $before])
                ->andWhere(['<=', 'o.order_time', $orderTime])
                ->count();
            return $this->ipInDayOrderApplyCount[$key] = $count;
        }
    }

    /**
     * 同一个IP下近7天内的申请数 (7天00:00至前1天24:00的时间)
     * @return int
     */
    public function checkApplyCntLast7daysByIP()
    {
        $ip = $this->data->infoDevice->ip;
        return $this->getApplyCntByBeforeDayIP($ip, 7, $this->data->infoOrder->order_time);
    }

    /**
     * 同一个IP下近1天内的申请数 (比如今日申请时间为17：50，则统计从昨日的17：50至今日17：50的申请数)
     * @return int
     */
    public function checkApplyCntLast1dayByIP()
    {
        $ip = $this->data->infoDevice->ip;
        return $this->getApplyCntByBeforeDayIP($ip, 1, $this->data->infoOrder->order_time);
    }

    /**
     * 同一IP下近1小时内申请数
     * @return int
     */
    public function checkApplyCntLast1hourByIP()
    {
        $ip = $this->data->infoDevice->ip;
        $before = $this->data->infoOrder->order_time - 3600;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where(['d.ip' => $ip])
            ->andWhere(['>=', 'o.order_time', $before])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();
        return $count;
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
        $latitude = $this->data->infoDevice->latitude;
        $longitude = $this->data->infoDevice->longitude;
        if(empty($latitude) || empty($longitude)){
            return -1;
        }
        $time = $this->data->infoOrder->order_time;
        $orderNum = EsUserLoanOrder::find()
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
                                'lat' => $latitude,
                                'lon' => $longitude,
                            ]
                        ]
                    ]
                ]
            ])
            ->count();

        return $orderNum;
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
        $latitude = $this->data->infoDevice->latitude;
        $longitude = $this->data->infoDevice->longitude;
        if(empty($latitude) || empty($longitude)){
            return -1;
        }

        $time = $this->data->infoOrder->order_time;
        $orderNum = EsUserLoanOrder::find()
            ->query([
                'bool' => [
                    'must' => [
                        'range' => [
                            'order_time' => [
                                'gte' => Carbon::createFromTimestamp($time)->subHours(1)->toIso8601ZuluString(),
                                'lte' => Carbon::createFromTimestamp($time)->toIso8601ZuluString()
                            ]
                        ],
                    ],
                    'filter' => [
                        'geo_distance' => [
                            'distance' => '500m',
                            'location' => [
                                'lat' => $latitude,
                                'lon' => $longitude,
                            ]
                        ]
                    ]
                ]
            ])
            ->count();

        return $orderNum;
    }

    /**
     * 同一定位地址的500米半径内、近1天内申请贷款数
     *
     * @return int
     * @throws
     */
    public function checkApplyCnt500mAwayFromGPSlocLast1Day(): int
    {
        $latitude = $this->data->infoDevice->latitude;
        $longitude = $this->data->infoDevice->longitude;
        if(empty($latitude) || empty($longitude)){
            return -1;
        }

        $time = $this->data->infoOrder->order_time;
        $orderNum = EsUserLoanOrder::find()
            ->query([
                'bool' => [
                    'must' => [
                        'range' => [
                            'order_time' => [
                                'gte' => Carbon::createFromTimestamp($time)->subDays(1)->toIso8601ZuluString(),
                                'lte' => Carbon::createFromTimestamp($time)->toIso8601ZuluString()
                            ]
                        ],
                    ],
                    'filter' => [
                        'geo_distance' => [
                            'distance' => '500m',
                            'location' => [
                                'lat' => $latitude,
                                'lon' => $longitude,
                            ]
                        ]
                    ]
                ]
            ])
            ->count();

        return $orderNum;
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
        $latitude = $this->data->infoDevice->latitude;
        $longitude = $this->data->infoDevice->longitude;
        if(empty($latitude) || empty($longitude)){
            return -1;
        }
        $time = $this->data->infoOrder->order_time;
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
                                'lat' => $latitude,
                                'lon' => $longitude,
                            ]
                        ]
                    ]
                ]
            ])
            ->count();

        return $orderNum;
    }

    /**
     * 获取用户安装app列表
     * @param int $phone
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
            if($this->data->infoOrder->is_external == 'y'){
                $app_name = $this->data->infoOrder->external_app_name;
            }else{
                $app_name = $this->data->order->app_name;
            }
            $appLineInfo = MgUserInstalledApps::find()
                ->select(['addeds'])
                ->where(['user_phone' => intval($this->data->infoUser->phone),
                         'app_name' => $app_name])
                ->asArray()
                ->all();

            $start_time = strtotime(date('Y-m-d', $this->data->infoOrder->order_time));
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
            $app = str_replace(' ', '', $v);
            if(in_array(strtoupper($app), $this->loanAppList)){
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
            $app = str_replace(' ', '', $v);
            if(in_array(strtoupper($app), $this->loanAppList)){
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
            $app = str_replace(' ', '', $v);
            if(in_array(strtoupper($app), $this->loanAppList)){
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
            $app = str_replace(' ', '', $v);
            if(in_array(strtoupper($app), $this->loanAppList)){
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
            $app = str_replace(' ', '', $v);
            if(in_array(strtoupper($app), $this->loanAppList)){
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
            $app = str_replace(' ', '', $v);
            if(in_array(strtoupper($app), $this->loanAppList)){
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
            $app = str_replace(' ', '', $v);
            if(in_array(strtoupper($app), $this->loanAppList)){
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
            $app = str_replace(' ', '', $v);
            if(in_array(strtoupper($app), $this->loanAppList)){
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
     * 申请信息填写的税前工资
     * @return int
     */
    public function checkAppFormSalaryBeforeTax()
    {
        return intval(CommonHelper::CentsToUnit($this->data->infoUser->monthly_salary));
    }

    /**
     * 是否命中准入手机品牌白名单
     * @return int   1 命中  0 未命中
     */
    public function checkMobileBrandHitWhiteList()
    {
        $brandName = $this->data->infoDevice->brand_name;
        if(empty($brandName)){
            return -1;
        }
        if(in_array(strtoupper($brandName), PhoneBrand::$whiteList)){
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
                'infoUser' => $this->data->infoUser,
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
            $this->data->order = RiskOrder::findOne($this->data->order->id);
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
                \Yii::error(['risk_order_id'=>$this->data->order->id,'err_msg'=>$e->getMessage(),'trace'=>$e->getTraceAsString()], 'RiskAutoCheck');
                $this->cibilReport = [];
            }
            return $this->cibilReport;
        } else {
            return $this->cibilReport;
        }
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
     * Accuauth-Pan卡OCR返回的状态
     * @return int
     */
    public function checkStatusOfPanOCR()
    {
        return 1;
    }

    /**
     * Accuauth-Pan验真返回的状态
     * @return int
     */
    public function checkStatusOfPanVertify()
    {
        return 1;
    }

    /**
     * Accuauth-Aadhaar卡OCR返回的状态
     * @return int
     */
    public function checkStatusOfAadhaarOCR()
    {
        return 1;
    }

    /**
     * 定位地址是否在印度
     * @return int
     */
    public function checkIsIndiaGPSRough(){
        $longitude = $this->data->infoDevice->longitude;
        $latitude = $this->data->infoDevice->latitude;
        $i = 0;
        if(!empty($longitude) && !empty($latitude)){
            $i++;
            if($longitude < 68.7 || $longitude > 97.25 || $latitude < 8.4 || $latitude > 37.6){
                return 0;
            }
        }

        $login = LoginLog::find()
            ->where(['user_id' => $this->data->order->user_id,
                     'app_name' => $this->data->order->app_name])
            ->andWhere(['<=', 'event_time', $this->data->infoOrder->order_time])
            ->orderBy(['id' => SORT_DESC])
            ->one();
        if(!empty($login)){
            $longitude = $login['longitude'];
            $latitude = $login['latitude'];
            if(!empty($longitude) && !empty($latitude)){
                $i++;
                if($longitude < 68.7 || $longitude > 97.25 || $latitude < 8.4 || $latitude > 37.6){
                    return 0;
                }
            }
        }

        if($i == 0){
            return -1;
        }
        return 1;
    }

    /**
     * 定位地址间的最大距离
     * @return int
     */
    public function checkMaxDistAmongGPS(){
        $begin_time = $this->data->infoOrder->order_time - 86400 * 30;
        $dist = [];

        $loginData = LoginLog::find()
            ->alias('l')
            ->leftJoin(InfoUser::tableName().' as u', 'l.app_name=u.app_name and l.user_id=u.user_id')
            ->select(['l.longitude', 'l.latitude'])
            ->where(['u.pan_code' => $this->data->infoUser->pan_code])
            ->andWhere(['>=', 'l.event_time', $begin_time])
            ->andWhere(['<=', 'l.event_time', $this->data->infoOrder->order_time])
            ->groupBy(['l.id'])
            ->asArray()
            ->all();
        foreach ($loginData as $login) {
            if (!empty($login['longitude']) && !empty($login['latitude'])) {
                $dist[] = ['longitude' => $login['longitude'], 'latitude' => $login['latitude']];
            }
        }

        $data = InfoDevice::find()
            ->select(['longitude', 'latitude'])
            ->where(['pan_code' => $this->data->infoUser->pan_code])
            ->andWhere(['>=', 'event_time', $begin_time])
            ->andWhere(['<=', 'event_time', $this->data->infoOrder->order_time])
            ->asArray()
            ->all();

        foreach ($data as $clientInfo) {
            if (!empty($clientInfo['longitude']) && !empty($clientInfo['latitude'])) {
                $dist[] = ['longitude' => $clientInfo['longitude'], 'latitude' => $clientInfo['latitude']];
            }
        }

        if(empty($dist) || count($dist) < 2){
            return -1;
        }
        $arr = [];
        for ($i = 0; $i < count($dist); $i++){
            for ($j = count($dist) - 1; $j > $i; $j--){
                $arr[] = CommonHelper::GetDistance($dist[$i]['longitude'],$dist[$i]['latitude'],$dist[$j]['longitude'],$dist[$j]['latitude']);
            }
        }

        return intval(max($arr));
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
                    'infoUser' => $this->data->infoUser,
                    'order' => $this->data->order,
                    'retryLimit' => 3,
                ];
                $service = new ShuMengService($params);
                if(!$service->getData()){
                    throw new Exception('shumeng数据拉取失败，等待重试', 1001);
                }
            }

            $data = ThirdDataShumeng::findOne(['order_id' => $this->data->order->order_id, 'app_name' => $this->data->order->app_name]);
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
     * 本次下单时间距离注册时间之间的时间差
     * @return int
     */
    public function checkDiffDateOfThisOrderAndFirstRegister(){
        if(empty($this->data->infoOrder->order_time) || empty($this->data->infoUser->register_time)){
            return -1;
        }

        return intval(($this->data->infoOrder->order_time - $this->data->infoUser->register_time)/86400);
    }

    /**
     * 历史手机照片的数量
     * @return int
     */
    public function checkHisMobilePhotoAmount(){
        return $this->data->infoPictureMeta->number_all ?? -1;
    }

    /**
     * 最近30天手机照片的数量
     * @return int
     */
    public function checkLast30MobilePhotoAmount(){
        return $this->data->infoPictureMeta->number30 ?? -1;
    }

    /**
     * 最近90天手机照片的数量
     * @return int
     */
    public function checkLast90MobilePhotoAmount(){
        return $this->data->infoPictureMeta->number90 ?? -1;
    }

    /**
     * 本产品相册照片中定位地址不在印度的照片张数
     * @return int
     */
    public function checkAlbumPhotoCntGPSNotInIndiaSelf(){
        return $this->data->infoPictureMeta->gps_notin_india_number ?? 0;
    }

    /**
     * 本产品相册照片中定位地址在印度的照片张数
     * @return int
     */
    public function checkAlbumPhotoCntGPSInIndiaSelf(){
        return $this->data->infoPictureMeta->gps_in_india_number ?? 0;
    }

    /**
     * 本产品相册照片中定位地址为空的照片张数
     * @return int
     */
    public function checkAlbumPhotoCntGPSNullSelf(){
        return $this->data->infoPictureMeta->gps_null_number ?? 0;
    }

    /**
     * 本产品相册照片中定位地址不在印度的照片张数占比
     * @return int
     */
    public function checkAlbumPhotoCntRatioGPSNotInIndiaSelf(){
        if(empty($this->data->infoPictureMeta->number_all)){
            return -1;
        }
        return intval(round(($this->data->infoPictureMeta->gps_notin_india_number ?? 0) / $this->data->infoPictureMeta->number_all * 100));
    }

    /**
     * 本产品相册照片中定位地址在印度的照片张数占比
     * @return int
     */
    public function checkAlbumPhotoCntRatioGPSInIndiaSelf(){
        if(empty($this->data->infoPictureMeta->number_all)){
            return -1;
        }
        return intval(round(($this->data->infoPictureMeta->gps_in_india_number ?? 0) / $this->data->infoPictureMeta->number_all * 100));
    }

    /**
     * 本产品相册照片中定位地址为空的照片张数占比
     * @return int
     */
    public function checkAlbumPhotoCntRatioGPSNullSelf(){
        if(empty($this->data->infoPictureMeta->number_all)){
            return -1;
        }
        return intval(round(($this->data->infoPictureMeta->gps_null_number ?? 0) / $this->data->infoPictureMeta->number_all * 100));
    }

    /**
     * 手机最早的照片时间距今的时间
     * @return int
     */
    public function checkFirstPhotoTimeToNow(){
        $info = json_decode($this->data->infoPictureMeta->metadata_earliest, true);
        if(!empty($info['AlbumFileLastModifiedTime'])){
            return intval((strtotime("today") - strtotime(date('Y-m-d', intval($info['AlbumFileLastModifiedTime']/1000)))) / 86400);
        }

        return -1;
    }

    /**
     * 手机最晚的照片时间距今的时间
     * @return int
     */
    public function checkLastPhotoTimeToNow(){
        $info = json_decode($this->data->infoPictureMeta->metadata_latest, true);
        if(!empty($info['AlbumFileLastModifiedTime'])){
            return intval((strtotime("today") - strtotime(date('Y-m-d', intval($info['AlbumFileLastModifiedTime']/1000)))) / 86400);
        }

        return -1;
    }

    /**
     * 最早一张手机照片定位地址与手机当前定位地址的距离
     * @return int
     */
    public function checkDistanceOfFirstPhotoAndMobileGPS(){
        $clientInfo = $this->data->infoDevice;
        if(empty($clientInfo['longitude']) || empty($clientInfo['latitude'])) {
            return -1;
        }

        $info = json_decode($this->data->infoPictureMeta->metadata_earliest_positioned, true);
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

    /**
     * 最近一张手机照片定位地址与手机当前定位地址的距离
     * @return int
     */
    public function checkDistanceOfLastPhotoAndMobileGPS(){
        $clientInfo = $this->data->infoDevice;
        if(empty($clientInfo['longitude']) || empty($clientInfo['latitude'])) {
            return -1;
        }

        $info = json_decode($this->data->infoPictureMeta->metadata_latest_positioned, true);
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

    /**
     * 屏幕分辨率是否大于等于1080*720的标准
     * @return int
     */
    public function checkIsScreenResolutionOver1080And720(){
        $screen_height = $this->data->infoDevice->screen_height;
        $screen_width = $this->data->infoDevice->screen_width;
        if(!isset($screen_height) || !isset($screen_width)){
            return -1;
        }
        $arr = [$screen_height,$screen_width];
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
        if (empty($this->data->infoUser->aadhaar_address)) {
            return -1;
        }

        $address = $this->data->infoUser->aadhaar_address;
        foreach ($this->addressWhiteList as $value) {
            if(stripos($address,$value) !== false){
                return 1;
            }
        }
        return 0;
    }

    /**
     * Accuauth-AadhaarOCR返回的城市地址是否命中准入地区黑名单
     * @return int
     */
    public function checkAadhaarOCRAddressHitBlacklist(){
        if (empty($this->data->infoUser->aadhaar_address)) {
            return -1;
        }

        $address = $this->data->infoUser->aadhaar_address;
        foreach ($this->addressBlackList as $value) {
            if(stripos($address,$value) !== false){
                return 1;
            }
        }
        return 0;
    }

    /**
     * 填写的姓名跟PanOCR返回的姓名模糊匹配的结果
     * @return int
     */
    public function checkNameMatchResultOfFillAndPanOCR(){
        $full_name = $this->data->infoUser->filled_name;
        if(empty($this->data->infoUser->pan_ocr_name) || !$full_name){
            return -1;
        }

        $ocr_name = $this->data->infoUser->pan_ocr_name;

        return CommonHelper::nameDiff($full_name,$ocr_name);
    }

    /**
     * 填写的姓名跟Pan验真返回的姓名模糊匹配的结果
     * @return int
     */
    public function checkNameMatchResultOfFillAndPanVertify(){
        $full_name = $this->data->infoUser->filled_name;
        if(!$this->data->infoUser->pan_verify_name || !$full_name){
            return -1;
        }

        $pan_name = $this->data->infoUser->pan_verify_name;

        return CommonHelper::nameDiff($full_name,$pan_name);
    }

    /**
     * 填写的姓名跟AadhaarOCR返回的姓名模糊匹配的结果
     * @return int
     */
    public function checkNameMatchResultOfFillAndAadhaarOCR(){
        $full_name = $this->data->infoUser->filled_name;
        if(empty($this->data->infoUser->aadhaar_ocr_name) || !$full_name){
            return -1;
        }

        $ocr_name = $this->data->infoUser->aadhaar_ocr_name;

        return CommonHelper::nameDiff($full_name,$ocr_name);
    }

    /**
     * PanOCR出来的卡号与填写的Pan卡号一致的位数
     * @return int
     */
    public function checkSameCntOfPanOCRAndFill(){
        $pan_input = $this->data->infoUser->pan_code;
        $pan_ocr = $this->data->infoUser->pan_ocr_code;
        if(empty($pan_input) || empty($pan_ocr)){
            return -1;
        }
        if(strlen($pan_input) == 10 && strlen($pan_ocr) == 10){
            $count = 0;
            for ($i = 0; $i < 10; $i++){
                if($pan_input[$i] == $pan_ocr[$i]){
                    $count++;
                }
            }
            return $count;
        }

        return -2;
    }

    /**
     * 活体检测的分数
     * @return int
     */
    public function checkScoreOFLivenessDetect(){
        if(empty($this->data->infoUser->fr_liveness_score)){
            return -1;
        }

        return $this->data->infoUser->fr_liveness_score;
    }

    /**
     * 活体检测的来源
     * @return int
     */
    public function checkSourceOfLivenessDetect(){
        if(empty($this->data->infoUser->fr_liveness_source)){
            return -1;
        }

        if($this->data->infoUser->fr_liveness_source == 'accu_auth'){
            return 1;
        }

        if($this->data->infoUser->fr_liveness_source == 'advance_ai'){
            return 2;
        }

        return -1;
    }

    /**
     * 多个生日的年份核对是否一致
     * @return int
     */
    public function checkIsYOBSame(){
        if($this->data->infoUser->pan_birthday && $this->data->infoUser->filled_birthday){
            $pan_date = str_replace(['/', ' '], '-', $this->data->infoUser->pan_birthday);
            $pan_year = date('Y', strtotime($pan_date));
            if($pan_year == date('Y', strtotime($this->data->infoUser->filled_birthday))){
                return 1;
            }

            return 0;
        }
        return -1;
    }

    /**
     * 用户生日年份的最大差值
     * @return int
     */
    public function checkMaxYearGapOfYOB(){
        if($this->data->infoUser->pan_birthday && $this->data->infoUser->filled_birthday){
            $pan_date = str_replace(['/', ' '], '-', $this->data->infoUser->pan_birthday);
            $pan_year = date('Y', strtotime($pan_date));
            return abs($pan_year - date('Y', strtotime($this->data->infoUser->filled_birthday)));
        }
        return -1;
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
        if (empty($this->data->infoUser->aadhaar_ocr_pin_code)) {
            return -1;
        }

        $pin = $this->data->infoUser->aadhaar_ocr_pin_code;
        if(in_array($pin, Pin::$whiteList)){
            return 1;
        }
        return 0;
    }

    /**
     * Accuauth-AadhaarOCR返回的邮编是否命中准入地区邮编黑名单
     * @return int
     */
    public function checkAadhaarOCRPostalCodeHitBlacklist(){
        if (empty($this->data->infoUser->aadhaar_ocr_pin_code)) {
            return -1;
        }

        $pin = $this->data->infoUser->aadhaar_ocr_pin_code;
        if(in_array($pin, Pin::$blackList)){
            return 1;
        }
        return 0;
    }

    /**
     * 通话记录爬取是否正常
     * @return int
     */
    public function checkIsCallRecordGrabNormal(){
        if($this->data->infoOrder->is_external == 'y'){
            $app_name = $this->data->infoOrder->external_app_name;
        }else{
            $app_name = $this->data->order->app_name;
        }
        $data = MgUserCallReports::find()
            ->where(['user_phone' => intval($this->data->infoUser->phone),
                     'app_name' => $app_name])
            ->orderBy(['created_at' => SORT_DESC])
            ->asArray()
            ->one();

        if(!empty($data)){
            $time = strtotime(date('Y-m-d', $this->data->infoOrder->order_time)) - 7 * 86400;
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
        if(!is_null($this->smsIsNormal)){
            return $this->smsIsNormal;
        }else{
            $class = MgUserMobileSmsService::getModelName($this->data->infoUser->pan_code);
            $data = $class::find()
                ->where(['pan_code' => $this->data->infoUser->pan_code])
                ->asArray()
                ->one();

            if(!empty($data)){
                return $this->smsIsNormal = 1;
            }

            return $this->smsIsNormal = 0;
        }
    }

    /**
     * 该Pan卡号是否成功爬取近30天内创建的短信
     * @return int
     */
    public function checkIsLast30DaysSMSRecordGrabNormal(){
        $begin_time = $this->data->infoOrder->order_time - 86400 * 30;

        $class = MgUserMobileSmsService::getModelName($this->data->infoUser->pan_code);
        $data = $class::find()
            ->where(['pan_code' => $this->data->infoUser->pan_code])
            ->andWhere(['>=', 'messageDate',$begin_time])
            ->asArray()
            ->one();

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
                'infoUser' => $this->data->infoUser,
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
            $this->data->order = RiskOrder::findOne($this->data->order->id);
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
                \Yii::error(['risk_order_id'=>$this->data->order->id,'err_msg'=>$e->getMessage(),'trace'=>$e->getTraceAsString()], 'RiskAutoCheck');
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
     * Experian中的账户是否有命中负面信息的状态
     * @return int
     * @throws \Exception
     */
    public function checkNegativeExperianAccountStatus(){
        $report = $this->getExperianReport();

        if(empty($report) || empty($report['CAIS_Account']['CAIS_Account_DETAILS'])){
            return -1;
        }

        foreach ($report['CAIS_Account']['CAIS_Account_DETAILS'] as $v){
            if(!empty($v['Account_Status']) && in_array($v['Account_Status'], [93,97,53,54,55,56,57,58,59,60,61,62,63,79,81,85,86,87,88,94,90,91])){
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
        if(empty($this->data->infoUser->contact1_mobile_number)){
            return -1;
        }

        $phone = $this->data->infoUser->contact1_mobile_number;

        if($this->data->infoOrder->is_external == 'y'){
            $app_name = $this->data->infoOrder->external_app_name;
        }else{
            $app_name = $this->data->order->app_name;
        }
        $data = MgUserCallReports::find()
            ->select(['callNumber','callDuration'])
            ->where(['user_phone' => intval($this->data->infoUser->phone),
                     'app_name' => $app_name,
                     'callType' => [1, 2]])
            ->andWhere(['>', 'callDuration', 0])
            ->asArray()
            ->all();

        if(empty($data)){
            return 0;
        }

        $callDuration = 0;
        foreach ($data as $val){
            if(substr($val['callNumber'], -10) == $phone){
                $callDuration += $val['callDuration'];
            }
        }

        return $callDuration;
    }

    /**
     * 紧急联系人A与用户通话的总次数(包括主被叫)
     * @return int
     */
    public function checkTotalCallCntOfContactAWithUser(){
        if(empty($this->data->infoUser->contact1_mobile_number)){
            return -1;
        }

        $phone = $this->data->infoUser->contact1_mobile_number;

        if($this->data->infoOrder->is_external == 'y'){
            $app_name = $this->data->infoOrder->external_app_name;
        }else{
            $app_name = $this->data->order->app_name;
        }
        $data = MgUserCallReports::find()
            ->select(['callNumber','callDuration'])
            ->where(['user_phone' => intval($this->data->infoUser->phone),
                     'app_name' => $app_name,
                     'callType' => [1, 2]])
            ->andWhere(['>', 'callDuration', 0])
            ->asArray()
            ->all();

        if(empty($data)){
            return 0;
        }

        $count = 0;
        foreach ($data as $val){
            if(substr($val['callNumber'], -10) == $phone){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 近30天紧急联系人A与用户通话的总时长(包括主被叫)
     * @return int
     */
    public function checkLast30dTotalCallTimeOfContactAWithUser(){
        $begin_time = strtotime('-30 day', $this->data->infoOrder->order_time);

        if(empty($this->data->infoUser->contact1_mobile_number)){
            return -1;
        }

        $phone = $this->data->infoUser->contact1_mobile_number;

        if($this->data->infoOrder->is_external == 'y'){
            $app_name = $this->data->infoOrder->external_app_name;
        }else{
            $app_name = $this->data->order->app_name;
        }
        $data = MgUserCallReports::find()
            ->select(['callNumber','callDuration'])
            ->where(['user_phone' => intval($this->data->infoUser->phone),
                     'app_name' => $app_name,
                     'callType' => [1, 2]])
            ->andWhere(['>=', 'callDateTime', $begin_time])
            ->andWhere(['>', 'callDuration', 0])
            ->asArray()
            ->all();

        if(empty($data)){
            return 0;
        }
        $callDuration = 0;
        foreach ($data as $val){
            if(substr($val['callNumber'], -10) == $phone){
                $callDuration += $val['callDuration'];
            }
        }

        return $callDuration;
    }

    /**
     * 近30天紧急联系人A与用户通话的总次数(包括主被叫)
     * @return int
     */
    public function checkLast30dTotalCallCntOfContactAWithUser(){
        $begin_time = strtotime('-30 day', $this->data->infoOrder->order_time);

        if(empty($this->data->infoUser->contact1_mobile_number)){
            return -1;
        }

        $phone = $this->data->infoUser->contact1_mobile_number;

        if($this->data->infoOrder->is_external == 'y'){
            $app_name = $this->data->infoOrder->external_app_name;
        }else{
            $app_name = $this->data->order->app_name;
        }
        $data = MgUserCallReports::find()
            ->select(['callNumber','callDuration'])
            ->where(['user_phone' => intval($this->data->infoUser->phone),
                     'app_name' => $app_name,
                     'callType' => [1, 2]])
            ->andWhere(['>=', 'callDateTime', $begin_time])
            ->andWhere(['>', 'callDuration', 0])
            ->asArray()
            ->all();

        if(empty($data)){
            return 0;
        }

        $count = 0;
        foreach ($data as $val){
            if(substr($val['callNumber'], -10) == $phone){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 紧急联系人A与用户最近一次通话距今时间(包括主被叫)
     * @return int
     */
    public function checkLastCallTimeDiffOfContactAWithUser(){
        if(empty($this->data->infoUser->contact1_mobile_number)){
            return -1;
        }

        $phone = $this->data->infoUser->contact1_mobile_number;

        if($this->data->infoOrder->is_external == 'y'){
            $app_name = $this->data->infoOrder->external_app_name;
        }else{
            $app_name = $this->data->order->app_name;
        }
        $data = MgUserCallReports::find()
            ->select(['callNumber','callDuration', 'callDateTime'])
            ->where(['user_phone' => intval($this->data->infoUser->phone),
                     'app_name' => $app_name,
                     'callType' => [1, 2]])
            ->andWhere(['>', 'callDuration', 0])
            ->asArray()
            ->all();

        if(empty($data)){
            return -1;
        }

        $callDuration = [];
        foreach ($data as $val){
            if(substr($val['callNumber'], -10) == $phone){
                $callDuration[] = $val['callDateTime'];
            }
        }

        if(empty($callDuration)){
            return -1;
        }

        return ceil((strtotime('today') - max($callDuration)) / 86400);
    }

    /**
     * 近30天紧急联系人A与用户通话的最大时长(包括主被叫)
     * @return int
     */
    public function checkLast30MaxCallTimeOfContactAWithUser(){
        $begin_time = strtotime('-30 day', $this->data->infoOrder->order_time);

        if(empty($this->data->infoUser->contact1_mobile_number)){
            return -1;
        }

        $phone = $this->data->infoUser->contact1_mobile_number;

        if($this->data->infoOrder->is_external == 'y'){
            $app_name = $this->data->infoOrder->external_app_name;
        }else{
            $app_name = $this->data->order->app_name;
        }
        $data = MgUserCallReports::find()
            ->select(['callNumber','callDuration'])
            ->where(['user_phone' => intval($this->data->infoUser->phone),
                     'app_name' => $app_name,
                     'callType' => [1, 2]])
            ->andWhere(['>=', 'callDateTime', $begin_time])
            ->andWhere(['>', 'callDuration', 0])
            ->asArray()
            ->all();

        if(empty($data)){
            return 0;
        }

        $callDuration = [];
        foreach ($data as $val){
            if(substr($val['callNumber'], -10) == $phone){
                $callDuration[] = $val['callDuration'];
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
        $begin_time = strtotime('-30 day', $this->data->infoOrder->order_time);

        if(empty($this->data->infoUser->contact1_mobile_number)){
            return -1;
        }

        $phone = $this->data->infoUser->contact1_mobile_number;

        if($this->data->infoOrder->is_external == 'y'){
            $app_name = $this->data->infoOrder->external_app_name;
        }else{
            $app_name = $this->data->order->app_name;
        }
        $data = MgUserCallReports::find()
            ->select(['callNumber','callDuration'])
            ->where(['user_phone' => intval($this->data->infoUser->phone),
                     'app_name' => $app_name,
                     'callType' => [1, 2]])
            ->andWhere(['>=', 'callDateTime', $begin_time])
            ->andWhere(['>', 'callDuration', 0])
            ->asArray()
            ->all();

        if(empty($data)){
            return 0;
        }

        $count = 0;
        $callDuration = 0;
        foreach ($data as $val){
            if(substr($val['callNumber'], -10) == $phone){
                $count++;
                $callDuration += $val['callDuration'];
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
        $begin_time = strtotime('-30 day', $this->data->infoOrder->order_time);

        if(empty($this->data->infoUser->contact1_mobile_number)){
            return -1;
        }

        $phone = $this->data->infoUser->contact1_mobile_number;

        if($this->data->infoOrder->is_external == 'y'){
            $app_name = $this->data->infoOrder->external_app_name;
        }else{
            $app_name = $this->data->order->app_name;
        }
        $data = MgUserCallReports::find()
            ->select(['callNumber','callDuration'])
            ->where(['user_phone' => intval($this->data->infoUser->phone),
                     'app_name' => $app_name,
                     'callType' => [1, 2]])
            ->andWhere(['>=', 'callDateTime', $begin_time])
            ->andWhere(['>', 'callDuration', 0])
            ->asArray()
            ->all();

        if(empty($data)){
            return 0;
        }

        $callDuration = [];
        foreach ($data as $val){
            if(substr($val['callNumber'], -10) == $phone){
                $callDuration[] = $val['callDuration'];
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
        if(empty($this->data->infoUser->contact2_mobile_number)){
            return -1;
        }

        $phone = $this->data->infoUser->contact2_mobile_number;

        if($this->data->infoOrder->is_external == 'y'){
            $app_name = $this->data->infoOrder->external_app_name;
        }else{
            $app_name = $this->data->order->app_name;
        }
        $data = MgUserCallReports::find()
            ->select(['callNumber','callDuration'])
            ->where(['user_phone' => intval($this->data->infoUser->phone),
                     'app_name' => $app_name,
                     'callType' => [1, 2]])
            ->andWhere(['>', 'callDuration', 0])
            ->asArray()
            ->all();

        if(empty($data)){
            return 0;
        }

        $callDuration = 0;
        foreach ($data as $val){
            if(substr($val['callNumber'], -10) == $phone){
                $callDuration += $val['callDuration'];
            }
        }

        return $callDuration;
    }

    /**
     * 紧急联系人B与用户通话的总次数(包括主被叫)
     * @return int
     */
    public function checkTotalCallCntOfContactBWithUser(){
        if(empty($this->data->infoUser->contact2_mobile_number)){
            return -1;
        }

        $phone = $this->data->infoUser->contact2_mobile_number;

        if($this->data->infoOrder->is_external == 'y'){
            $app_name = $this->data->infoOrder->external_app_name;
        }else{
            $app_name = $this->data->order->app_name;
        }
        $data = MgUserCallReports::find()
            ->select(['callNumber','callDuration'])
            ->where(['user_phone' => intval($this->data->infoUser->phone),
                     'app_name' => $app_name,
                     'callType' => [1, 2]])
            ->andWhere(['>', 'callDuration', 0])
            ->asArray()
            ->all();

        if(empty($data)){
            return 0;
        }

        $count = 0;
        foreach ($data as $val){
            if(substr($val['callNumber'], -10) == $phone){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 近30天紧急联系人B与用户通话的总时长(包括主被叫)
     * @return int
     */
    public function checkLast30dTotalCallTimeOfContactBWithUser(){
        $begin_time = strtotime('-30 day', $this->data->infoOrder->order_time);

        if(empty($this->data->infoUser->contact2_mobile_number)){
            return -1;
        }

        $phone = $this->data->infoUser->contact2_mobile_number;

        if($this->data->infoOrder->is_external == 'y'){
            $app_name = $this->data->infoOrder->external_app_name;
        }else{
            $app_name = $this->data->order->app_name;
        }
        $data = MgUserCallReports::find()
            ->select(['callNumber','callDuration'])
            ->where(['user_phone' => intval($this->data->infoUser->phone),
                     'app_name' => $app_name,
                     'callType' => [1, 2]])
            ->andWhere(['>=', 'callDateTime', $begin_time])
            ->andWhere(['>', 'callDuration', 0])
            ->asArray()
            ->all();

        if(empty($data)){
            return 0;
        }

        $callDuration = 0;
        foreach ($data as $val){
            if(substr($val['callNumber'], -10) == $phone){
                $callDuration += $val['callDuration'];
            }
        }

        return $callDuration;
    }

    /**
     * 近30天紧急联系人B与用户通话的总次数(包括主被叫)
     * @return int
     */
    public function checkLast30dTotalCallCntOfContactBWithUser(){
        $begin_time = strtotime('-30 day', $this->data->infoOrder->order_time);

        if(empty($this->data->infoUser->contact2_mobile_number)){
            return -1;
        }

        $phone = $this->data->infoUser->contact2_mobile_number;

        if($this->data->infoOrder->is_external == 'y'){
            $app_name = $this->data->infoOrder->external_app_name;
        }else{
            $app_name = $this->data->order->app_name;
        }
        $data = MgUserCallReports::find()
            ->select(['callNumber','callDuration'])
            ->where(['user_phone' => intval($this->data->infoUser->phone),
                     'app_name' => $app_name,
                     'callType' => [1, 2]])
            ->andWhere(['>=', 'callDateTime', $begin_time])
            ->andWhere(['>', 'callDuration', 0])
            ->asArray()
            ->all();

        if(empty($data)){
            return 0;
        }

        $count = 0;
        foreach ($data as $val){
            if(substr($val['callNumber'], -10) == $phone){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 紧急联系人B与用户最近一次通话距今时间(包括主被叫)
     * @return int
     */
    public function checkLastCallTimeDiffOfContactBWithUser(){
        if(empty($this->data->infoUser->contact2_mobile_number)){
            return -1;
        }

        $phone = $this->data->infoUser->contact2_mobile_number;

        if($this->data->infoOrder->is_external == 'y'){
            $app_name = $this->data->infoOrder->external_app_name;
        }else{
            $app_name = $this->data->order->app_name;
        }
        $data = MgUserCallReports::find()
            ->select(['callNumber','callDuration', 'callDateTime'])
            ->where(['user_phone' => intval($this->data->infoUser->phone),
                     'app_name' => $app_name,
                     'callType' => [1, 2]])
            ->andWhere(['>', 'callDuration', 0])
            ->asArray()
            ->all();

        if(empty($data)){
            return -1;
        }

        $callDuration = [];
        foreach ($data as $val){
            if(substr($val['callNumber'], -10) == $phone){
                $callDuration[] = $val['callDateTime'];
            }
        }

        if(empty($callDuration)){
            return -1;
        }

        return ceil((strtotime('today') - max($callDuration)) / 86400);
    }

    /**
     * 近30天紧急联系人B与用户通话的最大时长(包括主被叫)
     * @return int
     */
    public function checkLast30MaxCallTimeOfContactBWithUser(){
        $begin_time = strtotime('-30 day', $this->data->infoOrder->order_time);

        if(empty($this->data->infoUser->contact2_mobile_number)){
            return -1;
        }

        $phone = $this->data->infoUser->contact2_mobile_number;

        if($this->data->infoOrder->is_external == 'y'){
            $app_name = $this->data->infoOrder->external_app_name;
        }else{
            $app_name = $this->data->order->app_name;
        }
        $data = MgUserCallReports::find()
            ->select(['callNumber','callDuration'])
            ->where(['user_phone' => intval($this->data->infoUser->phone),
                     'app_name' => $app_name,
                     'callType' => [1, 2]])
            ->andWhere(['>=', 'callDateTime', $begin_time])
            ->andWhere(['>', 'callDuration', 0])
            ->asArray()
            ->all();

        if(empty($data)){
            return 0;
        }

        $callDuration = [];
        foreach ($data as $val){
            if(substr($val['callNumber'], -10) == $phone){
                $callDuration[] = $val['callDuration'];
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
        $begin_time = strtotime('-30 day', $this->data->infoOrder->order_time);

        if(empty($this->data->infoUser->contact2_mobile_number)){
            return -1;
        }

        $phone = $this->data->infoUser->contact2_mobile_number;

        if($this->data->infoOrder->is_external == 'y'){
            $app_name = $this->data->infoOrder->external_app_name;
        }else{
            $app_name = $this->data->order->app_name;
        }
        $data = MgUserCallReports::find()
            ->select(['callNumber','callDuration'])
            ->where(['user_phone' => intval($this->data->infoUser->phone),
                     'app_name' => $app_name,
                     'callType' => [1, 2]])
            ->andWhere(['>=', 'callDateTime', $begin_time])
            ->andWhere(['>', 'callDuration', 0])
            ->asArray()
            ->all();

        if(empty($data)){
            return 0;
        }

        $count = 0;
        $callDuration = 0;
        foreach ($data as $val){
            if(substr($val['callNumber'], -10) == $phone){
                $count++;
                $callDuration += $val['callDuration'];
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
        $begin_time = strtotime('-30 day', $this->data->infoOrder->order_time);

        if(empty($this->data->infoUser->contact2_mobile_number)){
            return -1;
        }

        $phone = $this->data->infoUser->contact2_mobile_number;

        if($this->data->infoOrder->is_external == 'y'){
            $app_name = $this->data->infoOrder->external_app_name;
        }else{
            $app_name = $this->data->order->app_name;
        }
        $data = MgUserCallReports::find()
            ->select(['callNumber','callDuration'])
            ->where(['user_phone' => intval($this->data->infoUser->phone),
                     'app_name' => $app_name,
                     'callType' => [1, 2]])
            ->andWhere(['>=', 'callDateTime', $begin_time])
            ->andWhere(['>', 'callDuration', 0])
            ->asArray()
            ->all();

        if(empty($data)){
            return 0;
        }

        $callDuration = [];
        foreach ($data as $val){
            if(substr($val['callNumber'], -10) == $phone){
                $callDuration[] = $val['callDuration'];
            }
        }

        if(empty($callDuration)){
            return 0;
        }

        return min($callDuration);
    }

    /*
     * 语言校验项回答正确的问题数量
     * @return int
     */
    public function checkCorrectQuesNumOfLanguageValidation(){
        return $this->data->infoUser->language_correct_number;
    }

    /**
     * 语言校验项问题回答的用时时间
     * @return int
     */
    public function checkTimeUsedInLanguageValidation(){
        return $this->data->infoUser->language_time;
    }

    /**
     * 居住地址城市是否为11月及以后新增的城市
     * @return int
     */
    public function checkResidentialCityHitNewlyAddedCityList(){
        $city = $this->data->infoUser->residential_city;

        if(empty($city)){
            return -1;
        }

        if(UserQuestionVerification::checkCity($city)){
            return 1;
        }

        return 0;
    }

    /**
     * 老用户最近一笔的到账金额
     * @return int
     */
    public function checkOldUserLastLoanOrderAmount(){
        $data = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoOrder::tableName().' as o', 'o.user_id=r.user_id and o.order_id=r.order_id and o.app_name=r.app_name')
            ->select(['r.principal', 'r.cost_fee'])
            ->where([
                'r.user_id' => $this->data->infoUser->user_id,
                'r.app_name' => $this->data->infoUser->app_name,
                'o.product_id' => $this->data->infoOrder->product_id
            ])
            ->orderBy(['r.id' => SORT_DESC])
            ->one();
        if(empty($data)){
            return 1700;
        }

        return intval(CommonHelper::CentsToUnit($data['principal'] - $data['cost_fee']));
    }

    /**
     * 全平台人脸比对分
     * @return int
     */
    public function checkAllPlatformFaceComparisonScore(){
        if(empty($this->data->infoUser->fr_verify_score)){
            return -1;
        }
        return $this->data->infoUser->fr_verify_score;
    }

    /**
     * 全平台人脸比对报告类型
     * @return int
     */
    public function checkAllPlatformFaceComparisonType(){
        if(empty($this->data->infoUser->fr_verify_type)){
            return -1;
        }

        if($this->data->infoUser->fr_verify_type == 'fr'){
            return 2;
        }else{
            return 1;
        }
    }

    /**
     * Aadhaar手填地址的城市是否命中准入地区白名单
     * @return int
     */
    public function checkWrittenAadhaarAddressHitWhiteList(){
        if(empty($this->data->infoUser->aadhaar_filled_city)){
            return -1;
        }

        if(in_array(strtoupper($this->data->infoUser->aadhaar_filled_city), $this->addressWhiteList)){
            return 1;
        }

        return 0;
    }

    /**
     * Aadhaar手填地址的邮编是否命中准入地区白名单
     * @return int
     */
    public function checkWrittenAadhaarAddressPincodeHitWhiteList(){
        if(empty($this->data->infoUser->aadhaar_pin_code)){
            return -1;
        }

        if(in_array($this->data->infoUser->aadhaar_pin_code, Pin::$whiteList)){
            return 1;
        }
        return 0;
    }

    /**
     * Aadhaar手填地址的城市是否命中准入地区黑名单
     * @return int
     */
    public function checkWrittenAadhaarAddressHitBlacklist(){
        if(empty($this->data->infoUser->aadhaar_filled_city)){
            return -1;
        }

        if(in_array(strtoupper($this->data->infoUser->aadhaar_filled_city), $this->addressBlackList)){
            return 1;
        }

        return 0;
    }

    /**
     * Aadhaar手填地址的邮编是否命中准入地区黑名单
     * @return int
     */
    public function checkWrittenAadhaarAddressPostalCodeHitBlacklist(){
        if(empty($this->data->infoUser->aadhaar_pin_code)){
            return -1;
        }

        if(in_array($this->data->infoUser->aadhaar_pin_code, Pin::$blackList)){
            return 1;
        }
        return 0;
    }

    /**
     * 近1个月内该手机号在本平台申请被拒次数
     * @return int|string
     */
    public function checkRejectCntLast1MonthByMobileInThisPlat()
    {
        $lastTime = strtotime('last month');
        $count = InfoOrder::find()
            ->where([
                'status' => [InfoOrder::STATUS_RISK_AUTO_REJECT, InfoOrder::STATUS_RISK_MANUAL_REJECT],
                'user_id' => $this->data->order->user_id,
                'app_name' => $this->data->order->app_name,
                'product_id' => $this->data->infoOrder->product_id
            ])
            ->andWhere(['>=', 'order_time', $lastTime])
            ->andWhere(['<=', 'order_time', $this->data->infoOrder->order_time])
            ->count();
        return $count;
    }

    /**
     * 近1个月内手机号在本平台的申请次数
     * @return int|string
     */
    public function checkApplyCntLast1MonthByMobileInThisPlat()
    {
        $lastTime = strtotime('last month');
        $count = InfoOrder::find()
            ->where([
                'user_id' => $this->data->order->user_id,
                'app_name' => $this->data->order->app_name,
                'product_id' => $this->data->infoOrder->product_id
            ])
            ->andWhere(['>=', 'order_time', $lastTime])
            ->andWhere(['<=', 'order_time', $this->data->infoOrder->order_time])
            ->count();
        return $count;
    }

    /**
     * 数盟设备ID在本平台的历史申请次数
     * @return int
     */
    public function checkHisSMDeviceIDApplyCntInThisPlat(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }

        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where([
                'd.szlm_query_id' => $this->data->infoDevice->szlm_query_id,
                'o.app_name' => $this->data->order->app_name,
                'o.product_id' => $this->data->infoOrder->product_id,
            ])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 数盟设备ID在本平台的历史申请次数
     * @return int
     */
    public function checkHisApplyCntBySMDeviceIDSelf(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }

        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where(['d.szlm_query_id' => $this->data->infoDevice->szlm_query_id,
                     'o.product_id' => $this->data->infoOrder->product_id,
                     'o.app_name' => $this->data->order->app_name])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近90天数盟设备ID在本平台的申请次数
     * @return int
     */
    public function checkLast90ApplyCntBySMDeviceIDInThisPlat(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }

        $begin_time = $this->data->infoOrder->order_time - 90 * 86400;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where(['d.szlm_query_id' => $this->data->infoDevice->szlm_query_id,
                     'o.app_name' => $this->data->order->app_name,
                     'o.product_id' => $this->data->infoOrder->product_id,
            ])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近90天数盟设备ID在本平台的申请次数
     * @return int
     */
    public function checkLast90dApplyCntBySMDeviceIDSelf(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }

        $begin_time = $this->data->infoOrder->order_time - 90 * 86400;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where(['d.szlm_query_id' => $this->data->infoDevice->szlm_query_id,
                     'o.product_id' => $this->data->infoOrder->product_id,
                     'o.app_name' => $this->data->order->app_name])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近60天数盟设备ID在本平台的申请次数
     * @return int
     */
    public function checkLast60dApplyCntBySMDeviceIDSelf(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }

        $begin_time = $this->data->infoOrder->order_time - 60 * 86400;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where(['d.szlm_query_id' => $this->data->infoDevice->szlm_query_id,
                     'o.product_id' => $this->data->infoOrder->product_id,
                     'o.app_name' => $this->data->order->app_name])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近30天数盟设备ID在本平台的申请次数
     * @return int
     */
    public function checkLast30ApplyCntBySMDeviceIDInThisPlat(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }

        $begin_time = $this->data->infoOrder->order_time - 30 * 86400;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where(['d.szlm_query_id' => $this->data->infoDevice->szlm_query_id,
                     'o.app_name' => $this->data->order->app_name,
                     'o.product_id' => $this->data->infoOrder->product_id,
            ])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近30天数盟设备ID在本平台的申请次数
     * @return int
     */
    public function checkLast30dApplyCntBySMDeviceIDSelf(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }

        $begin_time = $this->data->infoOrder->order_time - 30 * 86400;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where(['d.szlm_query_id' => $this->data->infoDevice->szlm_query_id,
                     'o.product_id' => $this->data->infoOrder->product_id,
                     'o.app_name' => $this->data->order->app_name])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近7天数盟设备ID在本平台的申请次数
     * @return int
     */
    public function checkLast7dApplyCntBySMDeviceIDSelf(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }

        $begin_time = $this->data->infoOrder->order_time - 7 * 86400;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where(['d.szlm_query_id' => $this->data->infoDevice->szlm_query_id,
                     'o.product_id' => $this->data->infoOrder->product_id,
                     'o.app_name' => $this->data->order->app_name])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近1天数盟设备ID在本平台的申请次数
     * @return int
     */
    public function checkLast1dApplyCntBySMDeviceIDSelf(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }

        $begin_time = $this->data->infoOrder->order_time - 86400;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where(['d.szlm_query_id' => $this->data->infoDevice->szlm_query_id,
                     'o.product_id' => $this->data->infoOrder->product_id,
                     'o.app_name' => $this->data->order->app_name])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近1小时数盟设备ID在本平台的申请次数
     * @return int
     */
    public function checkLast1hApplyCntBySMDeviceIDSelf(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }

        $begin_time = $this->data->infoOrder->order_time - 3600;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where(['d.szlm_query_id' => $this->data->infoDevice->szlm_query_id,
                     'o.product_id' => $this->data->infoOrder->product_id,
                     'o.app_name' => $this->data->order->app_name])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 数盟设备ID历史在本平台的申请被拒次数
     * @return int
     */
    public function checkHisSMDeviceIDApplyRejectCntInThisPlat(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }

        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where(['d.szlm_query_id' => $this->data->infoDevice->szlm_query_id,
                     'o.app_name' => $this->data->order->app_name,
                     'o.product_id' => $this->data->infoOrder->product_id,
                     'o.status' => [InfoOrder::STATUS_RISK_AUTO_REJECT, InfoOrder::STATUS_RISK_MANUAL_REJECT]])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 数盟设备ID历史在本平台的申请被拒次数
     * @return int
     */
    public function checkHisRejectCntBySMDeviceIDSelf(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }

        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where(['d.szlm_query_id' => $this->data->infoDevice->szlm_query_id,
                     'o.product_id' => $this->data->infoOrder->product_id,
                     'o.app_name' => $this->data->order->app_name,
                     'o.status' => [InfoOrder::STATUS_RISK_AUTO_REJECT, InfoOrder::STATUS_RISK_MANUAL_REJECT]])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近90天数盟设备ID在本平台的申请被拒次数
     * @return int
     */
    public function checkLast90RejectCntBySMDeviceIDInThisPlat(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }

        $begin_time = $this->data->infoOrder->order_time - 90 * 86400;

        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where(['d.szlm_query_id' => $this->data->infoDevice->szlm_query_id,
                     'o.app_name' => $this->data->order->app_name,
                     'o.product_id' => $this->data->infoOrder->product_id,
                     'o.status' => [InfoOrder::STATUS_RISK_AUTO_REJECT, InfoOrder::STATUS_RISK_MANUAL_REJECT]])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近90天数盟设备ID在本平台的申请被拒次数
     * @return int
     */
    public function checkLast90dRejectCntBySMDeviceIDSelf(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }

        $begin_time = $this->data->infoOrder->order_time - 90 * 86400;

        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where(['d.szlm_query_id' => $this->data->infoDevice->szlm_query_id,
                     'o.product_id' => $this->data->infoOrder->product_id,
                     'o.app_name' => $this->data->order->app_name,
                     'o.status' => [InfoOrder::STATUS_RISK_AUTO_REJECT, InfoOrder::STATUS_RISK_MANUAL_REJECT]])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近60天数盟设备ID在本平台的申请被拒次数
     * @return int
     */
    public function checkLast60dRejectCntBySMDeviceIDSelf(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }

        $begin_time = $this->data->infoOrder->order_time - 60 * 86400;

        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where(['d.szlm_query_id' => $this->data->infoDevice->szlm_query_id,
                     'o.app_name' => $this->data->order->app_name,
                     'o.product_id' => $this->data->infoOrder->product_id,
                     'o.status' => [InfoOrder::STATUS_RISK_AUTO_REJECT, InfoOrder::STATUS_RISK_MANUAL_REJECT]])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近30天数盟设备ID在本平台的申请被拒次数
     * @return int
     */
    public function checkLast30RejectCntBySMDeviceIDInThisPlat(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }

        $begin_time = $this->data->infoOrder->order_time - 30 * 86400;

        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where(['d.szlm_query_id' => $this->data->infoDevice->szlm_query_id,
                     'o.app_name' => $this->data->order->app_name,
                     'o.product_id' => $this->data->infoOrder->product_id,
                     'o.status' => [InfoOrder::STATUS_RISK_AUTO_REJECT, InfoOrder::STATUS_RISK_MANUAL_REJECT]])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近30天数盟设备ID在本平台的申请被拒次数
     * @return int
     */
    public function checkLast30dRejectCntBySMDeviceIDSelf(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }

        $begin_time = $this->data->infoOrder->order_time - 30 * 86400;

        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where(['d.szlm_query_id' => $this->data->infoDevice->szlm_query_id,
                     'o.app_name' => $this->data->order->app_name,
                     'o.product_id' => $this->data->infoOrder->product_id,
                     'o.status' => [InfoOrder::STATUS_RISK_AUTO_REJECT, InfoOrder::STATUS_RISK_MANUAL_REJECT]])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近7天数盟设备ID在本平台的申请被拒次数
     * @return int
     */
    public function checkLast7dRejectCntBySMDeviceIDSelf(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }

        $begin_time = $this->data->infoOrder->order_time - 7 * 86400;

        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where(['d.szlm_query_id' => $this->data->infoDevice->szlm_query_id,
                     'o.product_id' => $this->data->infoOrder->product_id,
                     'o.app_name' => $this->data->order->app_name,
                     'o.status' => [InfoOrder::STATUS_RISK_AUTO_REJECT, InfoOrder::STATUS_RISK_MANUAL_REJECT]])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近1天数盟设备ID在本平台的申请被拒次数
     * @return int
     */
    public function checkLast1dRejectCntBySMDeviceIDSelf(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }

        $begin_time = $this->data->infoOrder->order_time - 86400;

        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where(['d.szlm_query_id' => $this->data->infoDevice->szlm_query_id,
                     'o.product_id' => $this->data->infoOrder->product_id,
                     'o.app_name' => $this->data->order->app_name,
                     'o.status' => [InfoOrder::STATUS_RISK_AUTO_REJECT, InfoOrder::STATUS_RISK_MANUAL_REJECT]])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近1小时数盟设备ID在本平台的申请被拒次数
     * @return int
     */
    public function checkLast1hRejectCntBySMDeviceIDSelf(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }

        $begin_time = $this->data->infoOrder->order_time - 3600;

        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where(['d.szlm_query_id' => $this->data->infoDevice->szlm_query_id,
                     'o.product_id' => $this->data->infoOrder->product_id,
                     'o.app_name' => $this->data->order->app_name,
                     'o.status' => [InfoOrder::STATUS_RISK_AUTO_REJECT, InfoOrder::STATUS_RISK_MANUAL_REJECT]])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 本平台最近1笔订单的提前还款天数
     * @return int
     */
    public function checkPrerepaymentDaysLastOrderThisApp(){
        /** @var InfoRepayment  $infoRepayment */
        $infoRepayment = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoOrder::tableName(). ' as o', 'r.app_name=o.app_name and r.user_id=o.user_id and r.order_id=o.order_id')
            ->where([
                'r.user_id' => $this->data->infoUser->user_id,
                'r.app_name' => $this->data->order->app_name,
                'o.product_id' => $this->data->infoOrder->product_id,
            ])
            ->andWhere(['<', 'r.order_id', $this->data->order->order_id])
            ->orderBy(['r.order_id' => SORT_DESC])
            ->one();
        if(empty($infoRepayment)){
            return -2;
        }

        if($infoRepayment->is_overdue == InfoRepayment::OVERDUE_YES){
            return -1;
        }

        return (strtotime(date('Y-m-d', $infoRepayment->plan_repayment_time)) - strtotime(date('Y-m-d', $infoRepayment->closing_time))) / 86400;
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
     * 老用户复杂规则V1-历史提前还款订单数量
     * @return int
     */
    public function checkOldUserComplexRuleV1HisTiqianOrderCnt(){
        $count = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoOrder::tableName().' as o', 'o.order_id=r.order_id and o.user_id=r.user_id and o.app_name=r.app_name')
            ->where(['r.user_id' => $this->data->infoOrder->user_id,
                     'r.app_name' => $this->data->order->app_name,
                     'r.status' => InfoRepayment::STATUS_CLOSED,
                     'o.product_id' => $this->data->infoOrder->product_id,
                     'r.is_overdue' => InfoRepayment::OVERDUE_NO])
            ->count();
        return $count;
    }

    /**
     * 老用户复杂规则V1-历史逾期订单数量
     * @return int
     */
    public function checkOldUserComplexRuleV1HisDueOrderCnt(){
        if(empty($this->getProductOrderData())){
            return -9999;
        }

        $data = $this->getProductOrderData(0, 15);

        return count($data);
    }

    /**
     * 老用户复杂规则V1-历史还款日期与应还款日期之差的和
     * @return int
     */
    public function checkOldUserComplexRuleV1HisCpDaySum(){
        $time = strtotime(date('Y-m-d', $this->data->infoOrder->order_time));

        $data = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoOrder::tableName().' as o', 'o.order_id=r.order_id and o.user_id=r.user_id and o.app_name=r.app_name')
            ->select(['r.plan_repayment_time', 'r.closing_time', 'r.status'])
            ->where(['r.user_id' => $this->data->infoOrder->user_id,
                     'r.app_name' => $this->data->order->app_name,
                     'o.product_id' => $this->data->infoOrder->product_id])
            ->andWhere(['<', 'r.plan_repayment_time', $time])
            ->asArray()
            ->all();
        if(empty($data)){
            return -9999;
        }

        $count = 0;
        foreach ($data as $v){
            if($v['status'] == InfoRepayment::STATUS_CLOSED){
                $count += (strtotime(date('Y-m-d', $v['closing_time'])) - strtotime(date('Y-m-d', $v['plan_repayment_time'])))/86400;
            }
        }
        return $count;
    }

    /**
     * 老用户复杂规则V1-历史逾期订单的还款日期与应还款日期之差的和
     * @return int
     */
    public function checkOldUserComplexRuleV1HisDueCpDaySum(){
        $time = strtotime(date('Y-m-d', $this->data->infoOrder->order_time));

        $data = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoOrder::tableName().' as o', 'o.order_id=r.order_id and o.user_id=r.user_id and o.app_name=r.app_name')
            ->select(['r.plan_repayment_time', 'r.closing_time', 'r.is_overdue', 'r.status'])
            ->where(['r.user_id' => $this->data->infoOrder->user_id,
                     'r.app_name' => $this->data->order->app_name,
                     'o.product_id' => $this->data->infoOrder->product_id])
            ->andWhere(['<', 'r.plan_repayment_time', $time])
            ->asArray()
            ->all();
        if(empty($data)){
            return -9999;
        }

        $count = 0;
        foreach ($data as $v){
            if($v['is_overdue'] == InfoRepayment::OVERDUE_YES && $v['status'] == InfoRepayment::STATUS_CLOSED){
                $count += (strtotime(date('Y-m-d', $v['closing_time'])) - strtotime(date('Y-m-d', $v['plan_repayment_time'])))/86400;
            }
        }
        return $count;
    }

    /**
     * 老用户复杂规则V1-历史逾期订单的数量占历史总放款订单数量的比例
     * @return int
     */
    public function checkOldUserComplexRuleV1HisDueOrderCntHisOrderCntRate(){
        $data = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoOrder::tableName().' as o', 'o.order_id=r.order_id and o.user_id=r.user_id and o.app_name=r.app_name')
            ->where(['r.user_id' => $this->data->infoOrder->user_id,
                     'o.product_id' => $this->data->infoOrder->product_id,
                     'r.app_name' => $this->data->order->app_name])
            ->count();
        if(empty($data)){
            return -9999;
        }

        $count = $this->checkOldUserComplexRuleV1HisDueOrderCnt();
        if($count == -9999){
            return -9999;
        }

        return round($count / $data * 100 ,2);
    }

    /**
     * 老用户复杂规则V1-历史逾期订单的还款日期与应还款日期之差的最大值
     * @return int
     */
    public function checkOldUserComplexRuleV1HisDueCpDayMax(){
        $time = strtotime(date('Y-m-d', $this->data->infoOrder->order_time));

        $data = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoOrder::tableName().' as o', 'o.order_id=r.order_id and o.user_id=r.user_id and o.app_name=r.app_name')
            ->select(['r.plan_repayment_time', 'r.closing_time', 'r.is_overdue', 'r.status'])
            ->where(['r.user_id' => $this->data->infoOrder->user_id,
                     'r.app_name' => $this->data->order->app_name,
                     'o.product_id' => $this->data->infoOrder->product_id])
            ->andWhere(['<', 'r.plan_repayment_time', $time])
            ->asArray()
            ->all();
        if(empty($data)){
            return -9999;
        }

        $arr = [0];
        foreach ($data as $v){
            if($v['is_overdue'] == InfoRepayment::OVERDUE_YES && $v['status'] == InfoRepayment::STATUS_CLOSED){
                $arr[] = (strtotime(date('Y-m-d', $v['closing_time'])) - strtotime(date('Y-m-d', $v['plan_repayment_time'])))/86400;
            }
        }
        return max($arr);
    }

    /**
     * 用户上一笔订单贷款的实际还款日期与应还款日期的天数差
     * @return int
     */
    public function checkLastLoanOrderCpDay(){
        $data = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoOrder::tableName().' as o', 'o.order_id=r.order_id and o.user_id=r.user_id and o.app_name=r.app_name')
            ->where(['r.user_id' => $this->data->infoOrder->user_id,
                     'r.app_name' => $this->data->order->app_name,
                     'o.product_id' => $this->data->infoOrder->product_id,
                     'r.status' => InfoRepayment::STATUS_CLOSED])
            ->orderBy(['r.order_id' => SORT_DESC])
            ->one();
        if(empty($data)){
            return -9999;
        }

        return (strtotime(date('Y-m-d', $data['closing_time'])) - strtotime(date('Y-m-d', $data['plan_repayment_time'])))/86400;
    }

    /**
     * 近30天内贷款的实际还款日期与应还款日期的天数差的最大值
     * @return int
     */
    public function checkLast30dCpDayMax(){
        $begin_time = strtotime(date('Y-m-d', $this->data->infoOrder->order_time)) - 30 * 86400;
        $data = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoOrder::tableName().' as o', 'o.order_id=r.order_id and o.user_id=r.user_id and o.app_name=r.app_name')
            ->where(['r.user_id' => $this->data->infoOrder->user_id,
                     'r.app_name' => $this->data->order->app_name,
                     'o.product_id' => $this->data->infoOrder->product_id,
                     'r.status' => InfoRepayment::STATUS_CLOSED])
            ->andWhere(['>=', 'r.closing_time', $begin_time])
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
     * 跑此笔订单风控时用户曾成功还款的订单数加1(用来代表此笔订单是第几笔订单)
     * @return int
     */
    public function checkHisSuccessClosingOrderCntPlusOne(){
        $count = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoOrder::tableName().' as o', 'o.order_id=r.order_id and o.user_id=r.user_id and o.app_name=r.app_name')
            ->where(['r.user_id' => $this->data->infoOrder->user_id,
                     'r.app_name' => $this->data->order->app_name,
                     'o.product_id' => $this->data->infoOrder->product_id,
                     'r.status' => InfoRepayment::STATUS_CLOSED])
            ->count();

        return $count + 1;
    }

    /**
     * 近1个月内该手机号在总平台(内外)申请被拒次数
     * @return int|string
     */
    public function checkRejectCntLast1MonthByMobileInTotPlatporm()
    {
        $phone = $this->data->infoUser->phone;
        $lastTime = strtotime('last month');
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'o.order_id=u.order_id and o.user_id=u.user_id and o.app_name=u.app_name')
            ->where(['o.status' => [InfoOrder::STATUS_RISK_AUTO_REJECT, InfoOrder::STATUS_RISK_MANUAL_REJECT],
                     'u.phone' => $phone])
            ->andWhere(['>=', 'o.order_time', $lastTime])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 历史该手机号在总平台申请被拒次数
     * @return int|string
     */
    public function checkHisRejectCntByPhoneTotPlatform()
    {
        $phone = $this->data->infoUser->phone;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'o.order_id=u.order_id and o.user_id=u.user_id and o.app_name=u.app_name')
            ->where(['o.status' => [InfoOrder::STATUS_RISK_AUTO_REJECT, InfoOrder::STATUS_RISK_MANUAL_REJECT],
                     'u.phone' => $phone])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近90天该手机号在总平台申请被拒次数
     * @return int|string
     */
    public function checkLast90dRejectCntByPhoneTotPlatform()
    {
        $lastTime = $this->data->infoOrder->order_time - 90 * 86400;

        $phone = $this->data->infoUser->phone;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'o.order_id=u.order_id and o.user_id=u.user_id and o.app_name=u.app_name')
            ->where(['o.status' => [InfoOrder::STATUS_RISK_AUTO_REJECT, InfoOrder::STATUS_RISK_MANUAL_REJECT],
                     'u.phone' => $phone])
            ->andWhere(['>=', 'o.order_time', $lastTime])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近60天该手机号在总平台申请被拒次数
     * @return int|string
     */
    public function checkLast60dRejectCntByPhoneTotPlatform()
    {
        $lastTime = $this->data->infoOrder->order_time - 60 * 86400;

        $phone = $this->data->infoUser->phone;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'o.order_id=u.order_id and o.user_id=u.user_id and o.app_name=u.app_name')
            ->where(['o.status' => [InfoOrder::STATUS_RISK_AUTO_REJECT, InfoOrder::STATUS_RISK_MANUAL_REJECT],
                     'u.phone' => $phone])
            ->andWhere(['>=', 'o.order_time', $lastTime])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近7天该手机号在总平台申请被拒次数
     * @return int|string
     */
    public function checkLast7dRejectCntByPhoneTotPlatform()
    {
        $lastTime = $this->data->infoOrder->order_time - 7 * 86400;

        $phone = $this->data->infoUser->phone;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'o.order_id=u.order_id and o.user_id=u.user_id and o.app_name=u.app_name')
            ->where(['o.status' => [InfoOrder::STATUS_RISK_AUTO_REJECT, InfoOrder::STATUS_RISK_MANUAL_REJECT],
                     'u.phone' => $phone])
            ->andWhere(['>=', 'o.order_time', $lastTime])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近1个月内手机号在总平台(内外)的申请次数
     * @return int|string
     */
    public function checkApplyCntLast1MonthByMobileInTotPlatporm()
    {
        $phone = $this->data->infoUser->phone;
        $lastTime = strtotime('last month');
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'o.order_id=u.order_id and o.user_id=u.user_id and o.app_name=u.app_name')
            ->where(['u.phone' => $phone])
            ->andWhere(['>=', 'o.order_time', $lastTime])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 历史该手机号在总平台申请次数
     * @return int|string
     */
    public function checkHisApplyCntByPhoneTotPlatform()
    {
        $phone = $this->data->infoUser->phone;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'o.order_id=u.order_id and o.user_id=u.user_id and o.app_name=u.app_name')
            ->where(['u.phone' => $phone])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近90天该手机号在总平台申请次数
     * @return int|string
     */
    public function checkLast90dApplyCntByPhoneTotPlatform()
    {
        $lastTime = $this->data->infoOrder->order_time - 90 * 86400;

        $phone = $this->data->infoUser->phone;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'o.order_id=u.order_id and o.user_id=u.user_id and o.app_name=u.app_name')
            ->where(['u.phone' => $phone])
            ->andWhere(['>=', 'o.order_time', $lastTime])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近60天该手机号在总平台申请次数
     * @return int|string
     */
    public function checkLast60dApplyCntByPhoneTotPlatform()
    {
        $lastTime = $this->data->infoOrder->order_time - 60 * 86400;

        $phone = $this->data->infoUser->phone;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'o.order_id=u.order_id and o.user_id=u.user_id and o.app_name=u.app_name')
            ->where(['u.phone' => $phone])
            ->andWhere(['>=', 'o.order_time', $lastTime])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近7天该手机号在总平台申请次数
     * @return int|string
     */
    public function checkLast7dApplyCntByPhoneTotPlatform()
    {
        $lastTime = $this->data->infoOrder->order_time - 7 * 86400;

        $phone = $this->data->infoUser->phone;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'o.order_id=u.order_id and o.user_id=u.user_id and o.app_name=u.app_name')
            ->where(['u.phone' => $phone])
            ->andWhere(['>=', 'o.order_time', $lastTime])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 数盟设备ID在总平台(内外)的历史申请次数
     * @return int
     */
    public function checkHisSMDeviceIDApplyCntInTotPlatporm(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }

        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where(['d.szlm_query_id' => $this->data->infoDevice->szlm_query_id])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近90天数盟设备ID在总平台(内外)的申请次数
     * @return int
     */
    public function checkLast90ApplyCntBySMDeviceIDInTotPlatporm(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }

        $begin_time = $this->data->infoOrder->order_time - 90 * 86400;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where(['d.szlm_query_id' => $this->data->infoDevice->szlm_query_id])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近60天数盟设备ID在总平台(内外)的申请次数
     * @return int
     */
    public function checkLast60dApplyCntBySMDeviceIDTotPlatform(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }

        $begin_time = $this->data->infoOrder->order_time - 60 * 86400;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where(['d.szlm_query_id' => $this->data->infoDevice->szlm_query_id])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近30天数盟设备ID在总平台(内外)的申请次数
     * @return int
     */
    public function checkLast30ApplyCntBySMDeviceIDInTotPlatporm(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }

        $begin_time = $this->data->infoOrder->order_time - 30 * 86400;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where(['d.szlm_query_id' => $this->data->infoDevice->szlm_query_id])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近7天数盟设备ID在总平台(内外)的申请次数
     * @return int
     */
    public function checkLast7dApplyCntBySMDeviceIDTotPlatform(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }

        $begin_time = $this->data->infoOrder->order_time - 7 * 86400;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where(['d.szlm_query_id' => $this->data->infoDevice->szlm_query_id])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近1天数盟设备ID在总平台(内外)的申请次数
     * @return int
     */
    public function checkLast1dApplyCntBySMDeviceIDTPF(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }

        $begin_time = $this->data->infoOrder->order_time - 86400;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where(['d.szlm_query_id' => $this->data->infoDevice->szlm_query_id])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近1小时数盟设备ID在总平台(内外)的申请次数
     * @return int
     */
    public function checkLast1hApplyCntBySMDeviceIDTPF(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }

        $begin_time = $this->data->infoOrder->order_time - 3600;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where(['d.szlm_query_id' => $this->data->infoDevice->szlm_query_id])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 数盟设备ID历史在总平台(内外)的申请被拒次数
     * @return int
     */
    public function checkHisSMDeviceIDApplyRejectCntInTotPlatporm(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }

        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where(['d.szlm_query_id' => $this->data->infoDevice->szlm_query_id,
                     'o.status' => [InfoOrder::STATUS_RISK_AUTO_REJECT, InfoOrder::STATUS_RISK_MANUAL_REJECT]])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近90天数盟设备ID在总平台(内外)的申请被拒次数
     * @return int
     */
    public function checkLast90RejectCntBySMDeviceIDInTotPlatporm(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }

        $begin_time = $this->data->infoOrder->order_time - 90 * 86400;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where(['d.szlm_query_id' => $this->data->infoDevice->szlm_query_id,
                     'o.status' => [InfoOrder::STATUS_RISK_AUTO_REJECT, InfoOrder::STATUS_RISK_MANUAL_REJECT]])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近60天数盟设备ID在总平台(内外)的申请被拒次数
     * @return int
     */
    public function checkLast60dRejectCntBySMDeviceIDTotPlatform(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }

        $begin_time = $this->data->infoOrder->order_time - 60 * 86400;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where(['d.szlm_query_id' => $this->data->infoDevice->szlm_query_id,
                     'o.status' => [InfoOrder::STATUS_RISK_AUTO_REJECT, InfoOrder::STATUS_RISK_MANUAL_REJECT]])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近30天数盟设备ID在总平台(内外)的申请被拒次数
     * @return int
     */
    public function checkLast30RejectCntBySMDeviceIDInTotPlatporm(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }

        $begin_time = $this->data->infoOrder->order_time - 30 * 86400;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where(['d.szlm_query_id' => $this->data->infoDevice->szlm_query_id,
                     'o.status' => [InfoOrder::STATUS_RISK_AUTO_REJECT, InfoOrder::STATUS_RISK_MANUAL_REJECT]])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近7天数盟设备ID在总平台(内外)的申请被拒次数
     * @return int
     */
    public function checkLast7dRejectCntBySMDeviceIDTotPlatform(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }

        $begin_time = $this->data->infoOrder->order_time - 7 * 86400;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where(['d.szlm_query_id' => $this->data->infoDevice->szlm_query_id,
                     'o.status' => [InfoOrder::STATUS_RISK_AUTO_REJECT, InfoOrder::STATUS_RISK_MANUAL_REJECT]])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近1天数盟设备ID在总平台(内外)的申请被拒次数
     * @return int
     */
    public function checkLast1dRejectCntBySMDeviceIDTPF(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }

        $begin_time = $this->data->infoOrder->order_time - 86400;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where(['d.szlm_query_id' => $this->data->infoDevice->szlm_query_id,
                     'o.status' => [InfoOrder::STATUS_RISK_AUTO_REJECT, InfoOrder::STATUS_RISK_MANUAL_REJECT]])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近1小时数盟设备ID在总平台(内外)的申请被拒次数
     * @return int
     */
    public function checkLast1hRejectCntBySMDeviceIDTPF(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }

        $begin_time = $this->data->infoOrder->order_time - 3600;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where(['d.szlm_query_id' => $this->data->infoDevice->szlm_query_id,
                     'o.status' => [InfoOrder::STATUS_RISK_AUTO_REJECT, InfoOrder::STATUS_RISK_MANUAL_REJECT]])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 本次订单Pan卡号在总平台(内外)的当前处于待还款状态的订单数
     * @return int
     */
    public function checkPendingRepaymentCntOfPanInTotPlatporm(){
        $data = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName().' as u', 'r.user_id=u.user_id and r.order_id=u.order_id and r.app_name=u.app_name')
            ->select(['r.status'])
            ->where(['u.pan_code' => $this->data->infoUser->pan_code])
            ->asArray()
            ->all();
        if(empty($data)){
            return -9999;
        }

        $i = 0;
        foreach ($data as $v){
            if($v['status'] == InfoRepayment::STATUS_PENDING){
                $i++;
            }
        }

        return $i;
    }

    /**
     * 本次订单Pan卡号在总平台(内外)当前待还款订单的最大逾期天数
     * @return int
     */
    public function checkMaxDueDaysOfPendingRepaymentOrderOfPanInTotPlatporm(){
        $data = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName().' as u', 'r.user_id=u.user_id and r.order_id=u.order_id and r.app_name=u.app_name')
            ->where(['u.pan_code' => $this->data->infoUser->pan_code,
                     'r.status' => InfoRepayment::STATUS_PENDING])
            ->orderBy(['r.overdue_day' => SORT_DESC])
            ->asArray()
            ->one();

        return $data['overdue_day'] ?? -9999;
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
            $class = MgUserMobileSmsService::getModelName($this->data->infoUser->pan_code);

            if($day == 0){
                $sms = $class::find()
                    ->select(['messageContent','messageDate'])
                    ->where(['pan_code' => $this->data->infoUser->pan_code,
                             'type' => 1])
                    ->asArray()
                    ->all();

            }else{
                $begin_time = $orderTime - $day * 86400;
                $sms = $class::find()
                    ->select(['messageContent','messageDate'])
                    ->where(['pan_code' => $this->data->infoUser->pan_code,
                             'type' => 1])
                    ->andWhere(['>=', 'messageDate',$begin_time])
                    ->asArray()
                    ->all();
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
    protected function loanApplicationTrial($sms){
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
        $data = $this->getUserSmsByDay(7, $this->data->infoOrder->order_time);
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
        $data = $this->getUserSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(90, $this->data->infoOrder->order_time);

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
    protected function loanApplicationSubmission($sms){
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
        $data = $this->getUserSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(90, $this->data->infoOrder->order_time);

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
    protected function loanRejection($sms){
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
        $data = $this->getUserSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(90, $this->data->infoOrder->order_time);

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
    protected function loanApproval($sms){
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
        $data = $this->getUserSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(90, $this->data->infoOrder->order_time);

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
    protected function loanDisbursal($sms){
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
        $data = $this->getUserSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(90, $this->data->infoOrder->order_time);

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
    protected function loanDueRemind($sms){
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
        $data = $this->getUserSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(90, $this->data->infoOrder->order_time);

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
    protected function loanPayOff($sms){
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
        $data = $this->getUserSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(90, $this->data->infoOrder->order_time);

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
    protected function overdueRemind($sms){
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
        $data = $this->getUserSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(90, $this->data->infoOrder->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 同一个IP下历史在总平台的申请数
     * @return int
     */
    public function checkHisApplyCntByIPTotPlatform()
    {
        $ip = $this->data->infoDevice->ip;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where(['d.ip' => $ip])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 同一个IP下历史在总平台申请的拒绝数
     * @return int
     */
    public function checkHisApplyRejectCntByIPTotPlatform()
    {
        $ip = $this->data->infoDevice->ip;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where(['d.ip' => $ip,
                     'o.status' => [InfoOrder::STATUS_RISK_AUTO_REJECT, InfoOrder::STATUS_RISK_MANUAL_REJECT]])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();
        return $count;
    }

    /**
     * 同一个IP下近7天内在总平台申请的拒绝数
     * @return int
     */
    public function checkLast7dApplyRejectCntByIPTotPlatform()
    {
        $ip = $this->data->infoDevice->ip;
        $before = $this->data->infoOrder->order_time - 86400 * 7;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where(['d.ip' => $ip,
                     'o.status' => [InfoOrder::STATUS_RISK_AUTO_REJECT, InfoOrder::STATUS_RISK_MANUAL_REJECT]])
            ->andWhere(['>=', 'o.order_time', $before])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();
        return $count;
    }

    /**
     * 同一IP下1天内在总平台申请的拒绝数
     * @return int
     */
    public function checkLast1dApplyRejectCntByIPTotPlatform()
    {
        $ip = $this->data->infoDevice->ip;
        $before = $this->data->infoOrder->order_time - 86400;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where(['d.ip' => $ip,
                     'o.status' => [InfoOrder::STATUS_RISK_AUTO_REJECT, InfoOrder::STATUS_RISK_MANUAL_REJECT]])
            ->andWhere(['>=', 'o.order_time', $before])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();
        return $count;
    }

    /**
     * 同一IP下1小时内在总平台申请的拒绝数
     * @return int
     */
    public function checkLast1hApplyRejectCntByIPTotPlatform()
    {
        $ip = $this->data->infoDevice->ip;
        $before = $this->data->infoOrder->order_time - 3600;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where(['d.ip' => $ip,
                     'o.status' => [InfoOrder::STATUS_RISK_AUTO_REJECT, InfoOrder::STATUS_RISK_MANUAL_REJECT]])
            ->andWhere(['>=', 'o.order_time', $before])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();
        return $count;
    }

    /**
     * 同一个IP下历史在总平台的已到期订单数
     * @return int
     */
    public function checkHisExpireCntByIPTotPlatform()
    {
        $ip = $this->data->infoDevice->ip;
        $after = strtotime(date('Y-m-d', $this->data->infoOrder->order_time));
        $count = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoDevice::tableName(). ' as d', 'd.user_id=r.user_id and d.order_id=r.order_id and d.app_name=r.app_name')
            ->where(['d.ip' => $ip])
            ->andWhere(['<=', 'r.plan_repayment_time', $after])
            ->count();

        return $count;
    }

    /**
     * 同一个IP下历史在总平台的已到期订单中的逾期订单数
     * @return int
     */
    public function checkHisExpireDueCntByIPTotPlatform()
    {
        $ip = $this->data->infoDevice->ip;
        $after = strtotime(date('Y-m-d', $this->data->infoOrder->order_time));
        $data = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoDevice::tableName(). ' as d', 'd.user_id=r.user_id and d.order_id=r.order_id and d.app_name=r.app_name')
            ->where(['d.ip' => $ip])
            ->andWhere(['<', 'r.plan_repayment_time', $after])
            ->asArray()
            ->all();
        if(empty($data)){
            return -9999;
        }

        $count = 0;
        foreach ($data as $v){
            if($v['is_overdue'] == InfoRepayment::OVERDUE_YES){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 是否为总平台新用户
     * @return int
     */
    public function checkIsNewUserInTotPlatform(){
        if($this->data->infoOrder->is_all_first == InfoOrder::ENUM_IS_ALL_FIRST_Y){
            return 1;
        }
        return 0;
    }

    /**
     * 本次订单下单的包名
     * @return int
     */
    public function checkPackageName(){
        if($this->data->infoOrder->is_external == 'y'){
            $app_name = $this->data->infoOrder->external_app_name;
        }else{
            $app_name = $this->data->order->app_name;
        }

        return strtolower($app_name);
    }

    /**
     * 总平台(内外)-根据Pan号查询的历史最大逾期天数
     * @return int
     */
    public function checkHistMaxOverdueDaysByPanTotPlatform(){
        if(empty($this->getOrderData())){
            return -9999;
        }

        $data = $this->getOrderData(0, 15);

        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 总平台历史该数盟设备ID关联的手机序列号IMEI的数量
     * @return int
     */
    public function checkHisSMDeviceIDMatchImeiCntTotPlatform(){
        if(!$this->data->infoDevice->szlm_query_id){
            return -1;
        }

        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where(['d.szlm_query_id' => $this->data->infoDevice->szlm_query_id])
            ->andWhere(['!=', 'd.device_id', ''])
            ->andWhere(['is not', 'd.device_id', null])
            ->groupBy(['d.device_id'])
            ->count();

        return $count;
    }

    /**
     * 本次订单Pan卡号在总平台(内外)的当前处于待还款状态的订单总金额
     * @return int
     */
    public function checkPendingRepaymentTotAmtOfPanTotPlatform(){
        $data = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName().' as u', 'r.user_id=u.user_id and r.order_id=u.order_id and r.app_name=u.app_name')
            ->select(['r.principal', 'r.cost_fee'])
            ->where(['u.pan_code' => $this->data->infoUser->pan_code,
                     'r.status' => InfoRepayment::STATUS_PENDING])
            ->all();

        $money = 0;
        foreach ($data as $v){
            $money += $v['principal'] - $v['cost_fee'];
        }

        return round($money / 100);
    }

    /**
     * 总平台该手机号注册的账户体系个数
     * @return int
     */
    public function checkAccountCntOfPhoneTotPlatform(){
        return InfoUser::find()->where(['phone' => $this->data->infoUser->phone])->groupBy(['app_name'])->count();
    }

    /**
     * 总平台该手机号最早注册时间距今的时间
     * @return int
     */
    public function checkMaxDateDiffOfRegisterAndOrderByPhoneTotPlatform(){
        $data = InfoUser::find()
            ->where(['phone' => $this->data->infoUser->phone])
            ->orderBy(['register_time' => SORT_ASC])->one();

        $time = $data['register_time'];

        return (strtotime(date('Y-m-d', $this->data->infoOrder->order_time)) - strtotime(date('Y-m-d', $time))) / 86400;
    }

    /**
     * 总平台该手机号最晚注册时间距今的时间
     * @return int
     */
    public function checkMinDateDiffOfRegisterAndOrderByPhoneTotPlatform(){
        $data = InfoUser::find()
            ->where(['phone' => $this->data->infoUser->phone])
            ->orderBy(['register_time' => SORT_DESC])->one();

        $time = $data['register_time'];

        return (strtotime(date('Y-m-d', $this->data->infoOrder->order_time)) - strtotime(date('Y-m-d', $time))) / 86400;
    }

    /**
     * 历史该Pan卡号在总平台申请被拒次数
     * @return int|string
     */
    public function checkHisRejectCntByPanInTotPlatform()
    {
        $pan_code = $this->data->infoUser->pan_code;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'o.order_id=u.order_id and o.user_id=u.user_id and o.app_name=u.app_name')
            ->where(['o.status' => [InfoOrder::STATUS_RISK_AUTO_REJECT, InfoOrder::STATUS_RISK_MANUAL_REJECT],
                     'u.pan_code' => $pan_code])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近90天该Pan卡号在总平台申请被拒次数
     * @return int|string
     */
    public function checkLast90dRejectCntByPanInTotPlatform()
    {
        $lastTime = $this->data->infoOrder->order_time - 90 * 86400;
        $pan_code = $this->data->infoUser->pan_code;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'o.order_id=u.order_id and o.user_id=u.user_id and o.app_name=u.app_name')
            ->where(['o.status' => [InfoOrder::STATUS_RISK_AUTO_REJECT, InfoOrder::STATUS_RISK_MANUAL_REJECT],
                     'u.pan_code' => $pan_code])
            ->andWhere(['>=', 'o.order_time', $lastTime])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近60天该Pan卡号在总平台申请被拒次数
     * @return int|string
     */
    public function checkLast60dRejectCntByPanInTotPlatform()
    {
        $lastTime = $this->data->infoOrder->order_time - 60 * 86400;
        $pan_code = $this->data->infoUser->pan_code;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'o.order_id=u.order_id and o.user_id=u.user_id and o.app_name=u.app_name')
            ->where(['o.status' => [InfoOrder::STATUS_RISK_AUTO_REJECT, InfoOrder::STATUS_RISK_MANUAL_REJECT],
                     'u.pan_code' => $pan_code])
            ->andWhere(['>=', 'o.order_time', $lastTime])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近30天该Pan卡号在总平台申请被拒次数
     * @return int|string
     */
    public function checkLast30dRejectCntByPanInTotPlatform()
    {
        $lastTime = $this->data->infoOrder->order_time - 30 * 86400;
        $pan_code = $this->data->infoUser->pan_code;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'o.order_id=u.order_id and o.user_id=u.user_id and o.app_name=u.app_name')
            ->where(['o.status' => [InfoOrder::STATUS_RISK_AUTO_REJECT, InfoOrder::STATUS_RISK_MANUAL_REJECT],
                     'u.pan_code' => $pan_code])
            ->andWhere(['>=', 'o.order_time', $lastTime])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近7天该Pan卡号在总平台申请被拒次数
     * @return int|string
     */
    public function checkLast7dRejectCntByPanInTotPlatform()
    {
        $lastTime = $this->data->infoOrder->order_time - 7 * 86400;
        $pan_code = $this->data->infoUser->pan_code;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'o.order_id=u.order_id and o.user_id=u.user_id and o.app_name=u.app_name')
            ->where(['o.status' => [InfoOrder::STATUS_RISK_AUTO_REJECT, InfoOrder::STATUS_RISK_MANUAL_REJECT],
                     'u.pan_code' => $pan_code])
            ->andWhere(['>=', 'o.order_time', $lastTime])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 历史该Pan卡号在总平台的申请次数
     * @return int|string
     */
    public function checkHisApplyCntByPanInTotPlatform()
    {
        $pan_code = $this->data->infoUser->pan_code;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'o.order_id=u.order_id and o.user_id=u.user_id and o.app_name=u.app_name')
            ->where(['u.pan_code' => $pan_code])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近90天该Pan卡号在总平台的申请次数
     * @return int|string
     */
    public function checkLast90dApplyCntByPanInTotPlatform()
    {
        $lastTime = $this->data->infoOrder->order_time - 90 * 86400;

        $pan_code = $this->data->infoUser->pan_code;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'o.order_id=u.order_id and o.user_id=u.user_id and o.app_name=u.app_name')
            ->where(['u.pan_code' => $pan_code])
            ->andWhere(['>=', 'o.order_time', $lastTime])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近60天该Pan卡号在总平台的申请次数
     * @return int|string
     */
    public function checkLast60dApplyCntByPanInTotPlatform()
    {
        $lastTime = $this->data->infoOrder->order_time - 60 * 86400;

        $pan_code = $this->data->infoUser->pan_code;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'o.order_id=u.order_id and o.user_id=u.user_id and o.app_name=u.app_name')
            ->where(['u.pan_code' => $pan_code])
            ->andWhere(['>=', 'o.order_time', $lastTime])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近30天该Pan卡号在总平台的申请次数
     * @return int|string
     */
    public function checkLast30dApplyCntByPanInTotPlatform()
    {
        $lastTime = $this->data->infoOrder->order_time - 30 * 86400;

        $pan_code = $this->data->infoUser->pan_code;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'o.order_id=u.order_id and o.user_id=u.user_id and o.app_name=u.app_name')
            ->where(['u.pan_code' => $pan_code])
            ->andWhere(['>=', 'o.order_time', $lastTime])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近7天该Pan卡号在总平台的申请次数
     * @return int|string
     */
    public function checkLast7dApplyCntByPanInTotPlatform()
    {
        $lastTime = $this->data->infoOrder->order_time - 7 * 86400;

        $pan_code = $this->data->infoUser->pan_code;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'o.order_id=u.order_id and o.user_id=u.user_id and o.app_name=u.app_name')
            ->where(['u.pan_code' => $pan_code])
            ->andWhere(['>=', 'o.order_time', $lastTime])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 历史该Pan卡号在总平台放款的次数
     * @return int
     */
    public function checkHisLoanCntByPanTotPlatform(){
        $count = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName().' as u', 'r.order_id=u.order_id and r.user_id=u.user_id and r.app_name=u.app_name')
            ->where(['u.pan_code' => $this->data->infoUser->pan_code])
            ->andWhere(['<=', 'r.loan_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近30天该Pan卡号在总平台放款的次数
     * @return int
     */
    public function checkLast30dLoanCntByPanTotPlatform(){
        $begin_time = $this->data->infoOrder->order_time - 30 * 86400;

        $count = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName().' as u', 'r.order_id=u.order_id and r.user_id=u.user_id and r.app_name=u.app_name')
            ->where(['u.pan_code' => $this->data->infoUser->pan_code])
            ->andWhere(['>=', 'r.loan_time', $begin_time])
            ->andWhere(['<=', 'r.loan_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 历史该Pan卡号在总平台放款的不同账户体系数
     * @return int
     */
    public function checkHisLoanAccountCntByPanTotPlatform(){
        $count = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName().' as u', 'r.order_id=u.order_id and r.user_id=u.user_id and r.app_name=u.app_name')
            ->where(['u.pan_code' => $this->data->infoUser->pan_code])
            ->andWhere(['<=', 'r.loan_time', $this->data->infoOrder->order_time])
            ->groupBy(['u.app_name'])
            ->count();
        return $count;
    }

    /**
     * 近30天该Pan卡号在总平台放款的不同账户体系数
     * @return int
     */
    public function checkLast30dLoanAccountCntByPanTotPlatform(){
        $begin_time = $this->data->infoOrder->order_time - 30 * 86400;
        $count = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName().' as u', 'r.order_id=u.order_id and r.user_id=u.user_id and r.app_name=u.app_name')
            ->where(['u.pan_code' => $this->data->infoUser->pan_code])
            ->andWhere(['>=', 'r.loan_time', $begin_time])
            ->andWhere(['<=', 'r.loan_time', $this->data->infoOrder->order_time])
            ->groupBy(['u.app_name'])
            ->count();
        return $count;
    }

    /**
     * 总平台该Pan卡号本次申请订单距离上次申请订单时间的时间差
     * @return int
     */
    public function checkDateDiffOfOrderAndLastOrderApplyByPanTotPlatform(){
        $data = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'o.order_id=u.order_id and o.user_id=u.user_id and o.app_name=u.app_name')
            ->where(['u.pan_code' => $this->data->infoUser->pan_code])
            ->andWhere(['<', 'o.order_time', $this->data->infoOrder->order_time])
            ->orderBy(['o.order_time' => SORT_DESC])
            ->one();

        if(empty($data)){
            return -1;
        }

        $orderTime = $data['order_time'];

        return (strtotime(date('Y-m-d', $this->data->infoOrder->order_time)) - strtotime(date('Y-m-d', $orderTime))) / 86400;
    }

    /**
     * 总平台该Pan卡号本次申请订单距离上次放款订单时间的时间差
     * @return int
     */
    public function checkDateDiffOfOrderAndLastOrderLoanByPanTotPlatform(){
        $data = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName().' as u', 'r.order_id=u.order_id and r.user_id=u.user_id and r.app_name=u.app_name')
            ->where(['u.pan_code' => $this->data->infoUser->pan_code])
            ->andWhere(['<', 'r.loan_time', $this->data->infoOrder->order_time])
            ->orderBy(['r.loan_time' => SORT_DESC])
            ->one();

        if(empty($data)){
            return -1;
        }

        $orderTime = $data['loan_time'];

        return (strtotime(date('Y-m-d', $this->data->infoOrder->order_time)) - strtotime(date('Y-m-d', $orderTime))) / 86400;
    }

    /**
     * 总平台该Pan卡号已到期订单数
     * @return int
     */
    public function checkHisExpireCntByPanTotPlatform(){
        $after = strtotime(date('Y-m-d', $this->data->infoOrder->order_time));

        $count = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName().' as u', 'r.order_id=u.order_id and r.user_id=u.user_id and r.app_name=u.app_name')
            ->where(['u.pan_code' => $this->data->infoUser->pan_code])
            ->andWhere(['<=', 'r.plan_repayment_time', $after])
            ->count();

        return $count;
    }

    /**
     * 近30天该Pan卡号在总平台已到期订单数
     * @return int
     */
    public function checkLast30dExpireCntByPanTotPlatform(){
        $after = strtotime(date('Y-m-d', $this->data->infoOrder->order_time));
        $before = $after - 30 * 86400;

        $count = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName().' as u', 'r.order_id=u.order_id and r.user_id=u.user_id and r.app_name=u.app_name')
            ->where(['u.pan_code' => $this->data->infoUser->pan_code])
            ->andWhere(['>=', 'r.plan_repayment_time', $before])
            ->andWhere(['<=', 'r.plan_repayment_time', $after])
            ->count();

        return $count;
    }

    /**
     * 历史该Pan卡号在总平台的还款次数
     * @return int
     */
    public function checkHisRepayCntByPanTotPlatform(){
        $count = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName().' as u', 'r.order_id=u.order_id and r.user_id=u.user_id and r.app_name=u.app_name')
            ->where(['u.pan_code' => $this->data->infoUser->pan_code,
                     'r.status' => InfoRepayment::STATUS_CLOSED])
            ->andWhere(['<=', 'r.closing_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近30天该Pan卡号在总平台的还款次数
     * @return int
     */
    public function checkLast30dRepayCntByPanTotPlatform(){
        $before = $this->data->infoOrder->order_time - 30 * 86400;

        $count = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName().' as u', 'r.order_id=u.order_id and r.user_id=u.user_id and r.app_name=u.app_name')
            ->where(['u.pan_code' => $this->data->infoUser->pan_code,
                     'r.status' => InfoRepayment::STATUS_CLOSED])
            ->andWhere(['>=', 'r.closing_time', $before])
            ->andWhere(['<=', 'r.closing_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 历史该Pan卡号在总平台的逾期次数
     * @return int
     */
    public function checkHisDueCntByPanTotPlatform(){
        $count = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName().' as u', 'r.order_id=u.order_id and r.user_id=u.user_id and r.app_name=u.app_name')
            ->where(['u.pan_code' => $this->data->infoUser->pan_code,
                     'r.is_overdue' => InfoRepayment::OVERDUE_YES])
            ->count();

        return $count;
    }

    /**
     * 近30天该Pan卡号在总平台的逾期次数
     * @return int
     */
    public function checkLast30dDueCntByPanTotPlatform(){
        $before = $this->data->infoOrder->order_time - 30 * 86400;

        $count = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName().' as u', 'r.order_id=u.order_id and r.user_id=u.user_id and r.app_name=u.app_name')
            ->where(['u.pan_code' => $this->data->infoUser->pan_code,
                     'r.is_overdue' => InfoRepayment::OVERDUE_YES])
            ->andWhere(['>=', 'r.plan_repayment_time', $before])
            ->count();

        return $count;
    }

    /**
     * 总平台-根据Pan号查询的历史逾期天数的总和
     * @return int
     */
    public function checkHisDueSumDayByPanTotPlatform(){
        if(empty($this->getOrderData())){
            return -9999;
        }

        $data = $this->getOrderData(0, 15);
        if(empty($data)){
            return 0;
        }

        return array_sum($data);
    }

    /**
     * 总平台-根据Pan号查询的历史逾期天数的平均值
     * @return int
     */
    public function checkHisDueAvgDayByPanTotPlatform(){
        if(empty($this->getOrderData())){
            return -9999;
        }

        $data = $this->getOrderData(0, 15);

        if(empty($data)){
            return 0;
        }

        return round(array_sum($data) / count($data));
    }


    /**
     * 总平台催收建议是否拒绝
     * @return int
     */
    public function checkIsCollectionAdviceRejectTotPlatform(){
        $query = InfoCollectionSuggestion::find()
            ->where(['pan_code' => $this->data->infoUser->pan_code])
            ->orWhere(['phone' => $this->data->infoUser->phone]);

        if(!empty($this->data->infoDevice->szlm_query_id)){
            $query->orWhere(['szlm_query_id' => $this->data->infoDevice->szlm_query_id]);
        }

        $data = $query->one();

        if(empty($data)){
            return 0;
        }

        return 1;
    }

    /**
     * 老用户复杂规则V1-总平台历史提前还款订单数量
     * @return int
     */
    public function checkOldUserComplexRuleV1HisTiqianOrderCntTotPlatform(){
        $count = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName().' as u', 'r.order_id=u.order_id and r.user_id=u.user_id and r.app_name=u.app_name')
            ->select(['r.overdue_day'])
            ->where(['u.pan_code' => $this->data->infoUser->pan_code,
                     'r.status' => InfoRepayment::STATUS_CLOSED,
                     'r.is_overdue' => InfoRepayment::OVERDUE_NO])
            ->count();

        return $count;
    }

    /**
     * 老用户复杂规则V1-总平台历史逾期订单数量
     * @return int
     */
    public function checkOldUserComplexRuleV1HisDueOrderCntTotPlatform(){
        if(empty($this->getOrderData())){
            return -9999;
        }
        $data = $this->getOrderData(0, 1);

        return count($data);
    }

    /**
     * 老用户复杂规则V1-总平台历史还款日期与应还款日期之差的和
     * @return int
     */
    public function checkOldUserComplexRuleV1HisCpDaySumTotPlatform(){
        $time = strtotime(date('Y-m-d', $this->data->infoOrder->order_time));

        $data = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName().' as u', 'r.order_id=u.order_id and r.user_id=u.user_id and r.app_name=u.app_name')
            ->select(['r.plan_repayment_time', 'r.closing_time', 'r.status'])
            ->where(['u.pan_code' => $this->data->infoUser->pan_code])
            ->andWhere(['<', 'r.plan_repayment_time', $time])
            ->asArray()
            ->all();
        if(empty($data)){
            return -9999;
        }

        $count = 0;
        foreach ($data as $v){
            if($v['status'] == InfoRepayment::STATUS_CLOSED){
                $count += (strtotime(date('Y-m-d', $v['closing_time'])) - strtotime(date('Y-m-d', $v['plan_repayment_time'])))/86400;
            }
        }

        return $count;
    }

    /**
     * 老用户复杂规则V1-总平台历史逾期订单的还款日期与应还款日期之差的和
     * @return int
     */
    public function checkOldUserComplexRuleV1HisDueCpDaySumTotPlatform(){
        $time = strtotime(date('Y-m-d', $this->data->infoOrder->order_time));

        $data = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName().' as u', 'r.order_id=u.order_id and r.user_id=u.user_id and r.app_name=u.app_name')
            ->select(['r.plan_repayment_time', 'r.closing_time', 'r.is_overdue', 'r.status'])
            ->where(['u.pan_code' => $this->data->infoUser->pan_code])
            ->andWhere(['<', 'r.plan_repayment_time', $time])
            ->all();
        if(empty($data)){
            return -9999;
        }

        $count = 0;
        foreach ($data as $v){
            if($v['is_overdue'] == InfoRepayment::OVERDUE_YES && $v['status'] == InfoRepayment::STATUS_CLOSED){
                $count += (strtotime(date('Y-m-d', $v['closing_time'])) - strtotime(date('Y-m-d', $v['plan_repayment_time'])))/86400;
            }
        }
        return $count;
    }

    /**
     * 老用户复杂规则V1-总平台历史逾期订单的数量占历史总放款订单数量的比例
     * @return int
     */
    public function checkOldUserComplexRuleV1HisDueOrderCntHisOrderCntRateTotPlatform(){
        $data = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName().' as u', 'r.order_id=u.order_id and r.user_id=u.user_id and r.app_name=u.app_name')
            ->where(['u.pan_code' => $this->data->infoUser->pan_code])
            ->count();
        if(empty($data)){
            return -9999;
        }

        $count = $this->checkOldUserComplexRuleV1HisDueOrderCntTotPlatform();
        if($count == -9999){
            return -9999;
        }

        return round($count / $data * 100,2);
    }

    /**
     * 老用户复杂规则V1-总平台历史逾期订单的还款日期与应还款日期之差的最大值
     * @return int
     */
    public function checkOldUserComplexRuleV1HisDueCpDayMaxTotPlatform(){
        $time = strtotime(date('Y-m-d', $this->data->infoOrder->order_time));

        $data = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName().' as u', 'r.order_id=u.order_id and r.user_id=u.user_id and r.app_name=u.app_name')
            ->select(['r.plan_repayment_time', 'r.closing_time', 'r.is_overdue', 'r.status'])
            ->where(['u.pan_code' => $this->data->infoUser->pan_code])
            ->andWhere(['<', 'r.plan_repayment_time', $time])
            ->asArray()
            ->all();
        if(empty($data)){
            return -9999;
        }

        $arr = [0];
        foreach ($data as $v){
            if($v['is_overdue'] == InfoRepayment::OVERDUE_YES && $v['status'] == InfoRepayment::STATUS_CLOSED){
                $arr[] = (strtotime(date('Y-m-d', $v['closing_time'])) - strtotime(date('Y-m-d', $v['plan_repayment_time'])))/86400;
            }
        }
        return max($arr);
    }

    /**
     * 该用户在总平台上一笔订单贷款的实际还款日期与应还款日期的天数差
     * @return int
     */
    public function checkLastLoanOrderCpDayTotPlatform(){
        $data = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName().' as u', 'r.order_id=u.order_id and r.user_id=u.user_id and r.app_name=u.app_name')
            ->where(['u.pan_code' => $this->data->infoUser->pan_code,
                     'r.status' => InfoRepayment::STATUS_CLOSED])
            ->orderBy(['r.loan_time' => SORT_DESC])
            ->one();

        if(empty($data)){
            return -9999;
        }

        return (strtotime(date('Y-m-d', $data['closing_time'])) - strtotime(date('Y-m-d', $data['plan_repayment_time'])))/86400;
    }

    /**
     * 该用户在近30天内在总平台内贷款的实际还款日期与应还款日期的天数差的最大值
     * @return int
     */
    public function checkLast30dCpDayMaxTotPlatform(){
        $begin_time = strtotime(date('Y-m-d', $this->data->infoOrder->order_time)) - 30 * 86400;
        $data = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName().' as u', 'r.order_id=u.order_id and r.user_id=u.user_id and r.app_name=u.app_name')
            ->where(['u.pan_code' => $this->data->infoUser->pan_code,
                     'r.status' => InfoRepayment::STATUS_CLOSED])
            ->andWhere(['>=', 'r.closing_time', $begin_time])
            ->asArray()
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
     * 该用户跑此笔订单风控时用户曾成功在总平台还款的订单数加1(用来代表此笔订单是第几笔订单)
     * @return int
     */
    public function checkHisSuccessClosingOrderCntPlusOneTotPlatform(){
        $count = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName().' as u', 'r.order_id=u.order_id and r.user_id=u.user_id and r.app_name=u.app_name')
            ->where(['u.pan_code' => $this->data->infoUser->pan_code,
                     'r.status' => InfoRepayment::STATUS_CLOSED])
            ->count();

        return $count + 1;
    }

    /**
     * 总平台该Pan卡号历史关联过的不同Aadhaar卡号数量
     * @return int
     */
    public function checkPanHisMatchAadhaarCntTotPlatform(){
        $pan_code = $this->data->infoUser->pan_code;

        $count = InfoUser::find()
                ->select(['aadhaar_md5'])
                ->where(['pan_code' => $pan_code])
                ->andWhere(['is not','aadhaar_md5',null])
                ->groupBy(['aadhaar_md5'])->count();
        return $count;
    }

    /**
     * 总平台该Aadhaar卡号历史关联过的不同Pan卡号数量
     * @return int
     */
    public function checkAadhaarHisMatchPanCntTotPlatform()
    {
        if(empty($this->data->infoUser->aadhaar_md5)){
            return -1;
        }
        $aadhaar_number = $this->data->infoUser->aadhaar_md5;
        $count = InfoUser::find()
                ->select(['pan_code'])
                ->where(['aadhaar_md5' => $aadhaar_number])
                ->andWhere(['is not','pan_code',null])
                ->groupBy(['pan_code'])->count();

        return $count;
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

//        $report = $this->getExperianReport();
//        if(isset($report['Header']['SystemCode']) && $report['Header']['SystemCode'] == 0){
//            if(!empty($report['UserMessage']['UserMessageText']) && $report['UserMessage']['UserMessageText'] == 'Normal Response'){
//                return 2;
//            }
//        }

        $report = $this->getBangaloreExperianReport();
        if(isset($report['Header']['SystemCode']) && $report['Header']['SystemCode'] == 0){
            if(!empty($report['UserMessage']['UserMessageText']) && $report['UserMessage']['UserMessageText'] == 'Normal Response'){
                return 3;
            }
        }

        return -1;
    }

    /**
     * 老用户模型V2-近30天逾期还款的次数
     * @return int
     */
    public function checkLast30dDueRepayCnt(){
        $begin_time = strtotime(date('Y-m-d', $this->data->infoOrder->order_time)) - 30 * 86400;

        $data = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoOrder::tableName().' as o', 'o.order_id=r.order_id and o.user_id=r.user_id and o.app_name=r.app_name')
            ->select(['r.is_overdue'])
            ->where(['r.app_name' => $this->data->order->app_name,
                     'o.product_id' => $this->data->infoOrder->product_id,
                     'r.user_id' => $this->data->order->user_id])
            ->andWhere(['>=', 'r.closing_time', $begin_time])
            ->asArray()
            ->all();

        if(empty($data)){
            return -9999;
        }

        $count = 0;
        foreach ($data as $v){
            if($v['is_overdue'] == InfoRepayment::OVERDUE_YES){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 老用户模型V2-历史逾期4天及以上还款的次数占历史逾期还款次数的比例
     * @return int
     */
    public function checkHisDue4RepayCntHisDueCntRate(){
        $count_all = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoOrder::tableName().' as o', 'o.order_id=r.order_id and o.user_id=r.user_id and o.app_name=r.app_name')
            ->where(['r.status' => InfoRepayment::STATUS_CLOSED,
                     'r.is_overdue' => InfoRepayment::OVERDUE_YES,
                     'o.product_id' => $this->data->infoOrder->product_id,
                     'r.app_name' => $this->data->order->app_name,
                     'r.user_id' => $this->data->order->user_id])
            ->count();

        if(empty($count_all)){
            return -9999;
        }

        $count = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoOrder::tableName().' as o', 'o.order_id=r.order_id and o.user_id=r.user_id and o.app_name=r.app_name')
            ->where(['r.status' => InfoRepayment::STATUS_CLOSED,
                     'r.app_name' => $this->data->order->app_name,
                     'o.product_id' => $this->data->infoOrder->product_id,
                     'r.user_id' => $this->data->order->user_id])
            ->andWhere(['>=', 'r.overdue_day', 4])->count();

        return round($count / $count_all * 100, 2);
    }

    /**
     * 老用户模型V2-近7天还款次数占近30天还款次数的比例
     * @return int
     */
    public function checkLast7dRepayCntLast30dCntRate(){
        $begin_time = strtotime(date('Y-m-d', $this->data->infoOrder->order_time)) - 30 * 86400;
        $count_all = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoOrder::tableName().' as o', 'o.order_id=r.order_id and o.user_id=r.user_id and o.app_name=r.app_name')
            ->where(['r.user_id' => $this->data->infoUser->user_id,
                     'o.product_id' => $this->data->infoOrder->product_id,
                     'r.app_name' => $this->data->order->app_name])
            ->andWhere(['>=', 'r.closing_time', $begin_time])->count();

        if(empty($count_all)){
            return -9999;
        }

        $begin_time = strtotime(date('Y-m-d', $this->data->infoOrder->order_time)) - 7 * 86400;
        $count = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoOrder::tableName().' as o', 'o.order_id=r.order_id and o.user_id=r.user_id and o.app_name=r.app_name')
            ->where(['r.user_id' => $this->data->infoUser->user_id,
                     'o.product_id' => $this->data->infoOrder->product_id,
                     'r.app_name' => $this->data->order->app_name])
            ->andWhere(['>=', 'r.closing_time', $begin_time])->count();

        return round($count / $count_all * 100, 2);
    }

    /**
     * 老用户模型V2-近30天正常还款的次数占历史放款订单数的比例
     * @return int
     */
    public function checkLast30dTiqianRepayCntHisCntRate(){
        $begin_time = strtotime(date('Y-m-d', $this->data->infoOrder->order_time)) - 30 * 86400;

        $count_all = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoOrder::tableName().' as o', 'o.order_id=r.order_id and o.user_id=r.user_id and o.app_name=r.app_name')
            ->where(['r.user_id' => $this->data->infoUser->user_id,
                     'o.product_id' => $this->data->infoOrder->product_id,
                     'r.app_name' => $this->data->order->app_name])
            ->count();
        if(empty($count_all)){
            return -9999;
        }

        $count = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoOrder::tableName().' as o', 'o.order_id=r.order_id and o.user_id=r.user_id and o.app_name=r.app_name')
            ->where(['r.user_id' => $this->data->infoUser->user_id,
                     'r.app_name' => $this->data->order->app_name,
                     'o.product_id' => $this->data->infoOrder->product_id,
                     'r.is_overdue' => InfoRepayment::OVERDUE_NO])
            ->andWhere(['>=', 'closing_time', $begin_time])->count();


        return round($count / $count_all * 100, 2);
    }

    /**
     * 老用户模型V2-历史逾期天数的均值
     * @return int
     */
    public function checkHisAvgDueRepayDay(){
        if(empty($this->getProductOrderData())){
            return -9999;
        }

        $data = $this->getProductOrderData(0, 15);

        if(empty($data)){
            return 0;
        }

        return round(array_sum($data) / count($data));
    }

    /**
     * 老用户模型V2-本次订单申请时间与历史放款订单的申请时间差的最大值
     * @return int
     */
    public function checkMaxDateOfOrderToToday(){
        $data = InfoOrder::find()
            ->where(['user_id' => $this->data->order->user_id,
                     'product_id' => $this->data->infoOrder->product_id,
                     'app_name' => $this->data->order->app_name])
            ->andWhere(['>', 'loan_time', 0])
            ->all();
        $count = [0];
        foreach ($data as $v){
            $count[] = (strtotime(date('Y-m-d', $this->data->infoOrder->order_time)) - strtotime(date('Y-m-d', $v['order_time'])))/86400;
        }

        return max($count);
    }

    /**
     * 老用户模型V2-本次订单申请时间与历史放款订单的申请时间差的均值
     * @return int
     */
    public function checkAvgDateOfOrderToToday(){
        $data = InfoOrder::find()
            ->where([
                'user_id' => $this->data->order->user_id,
                'product_id' => $this->data->infoOrder->product_id
            ])
            ->andWhere(['>', 'loan_time', 0])
            ->all();
        $count = [];
        foreach ($data as $v){
            $count[] = (strtotime(date('Y-m-d', $this->data->infoOrder->order_time)) - strtotime(date('Y-m-d', $v['order_time'])))/86400;
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
     * 根据指定IP在多少天之内的申请数
     * @param $ip
     * @param int $day 至少为1
     * @param int $orderTime
     * @return int
     */
    protected function getApplyCntByBeforeDayIPSelf($ip, $day=0, $orderTime=0)
    {
        $key = $day;
        if(isset($this->ipInDayOrderApplyCountSelf[$key])){
            return $this->ipInDayOrderApplyCountSelf[$key];
        }else{
            if($day == 0){
                $count = InfoOrder::find()
                    ->alias('o')
                    ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
                    ->where(['d.ip' => $ip,
                             'o.product_id' => $this->data->infoOrder->product_id,
                             'o.app_name' => $this->data->order->app_name])
                    ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
                    ->count();
            }else{
                $before = $orderTime - 86400 * $day;
                $count = InfoOrder::find()
                    ->alias('o')
                    ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
                    ->where(['d.ip' => $ip,
                             'o.product_id' => $this->data->infoOrder->product_id,
                             'o.app_name' => $this->data->order->app_name])
                    ->andWhere(['>=', 'o.order_time', $before])
                    ->andWhere(['<=', 'o.order_time', $orderTime])
                    ->count();
            }

            return $this->ipInDayOrderApplyCountSelf[$key] = $count;
        }
    }

    /**
     * 同一个IP下近7天内在本平台的申请数
     * @return int
     */
    public function checkApplyCntLast7daysByIPSelf()
    {
        $ip = $this->data->infoDevice->ip;
        $count = $this->getApplyCntByBeforeDayIPSelf($ip, 7, $this->data->infoOrder->order_time);
        return $count;
    }

    /**
     * 同一IP下1天内在本平台的申请数
     * @return int
     */
    public function checkApplyCntLast1dayByIPSelf()
    {
        $ip = $this->data->infoDevice->ip;
        $count = $this->getApplyCntByBeforeDayIPSelf($ip, 1, $this->data->infoOrder->order_time);
        return $count;
    }

    /**
     * 同一IP下1小时内在本平台的申请数
     * @return int
     */
    public function checkApplyCntLast1hourByIPSelf()
    {
        $ip = $this->data->infoDevice->ip;
        $before = $this->data->infoOrder->order_time - 3600;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where(['d.ip' => $ip,
                     'o.product_id' => $this->data->infoOrder->product_id,
                     'o.app_name' => $this->data->order->app_name])
            ->andWhere(['>=', 'o.order_time', $before])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 同一个IP下历史在本平台的申请数
     * @return int
     */
    public function checkHisApplyCntByIPSelf()
    {
        $ip = $this->data->infoDevice->ip;
        $count = $this->getApplyCntByBeforeDayIPSelf($ip);
        return $count;
    }

    /**
     * 同一个IP下历史在本平台申请的拒绝数
     * @return int
     */
    public function checkHisApplyRejectCntByIPSelf()
    {
        $ip = $this->data->infoDevice->ip;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where(['d.ip' => $ip,
                     'o.status' => [InfoOrder::STATUS_RISK_AUTO_REJECT, InfoOrder::STATUS_RISK_MANUAL_REJECT],
                     'o.product_id' => $this->data->infoOrder->product_id,
                     'o.app_name' => $this->data->order->app_name])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();
        return $count;
    }

    /**
     * 同一个IP下近7天内在本平台申请的拒绝数
     * @return int
     */
    public function checkLast7dApplyRejectCntByIPSelf()
    {
        $ip = $this->data->infoDevice->ip;
        $before = $this->data->infoOrder->order_time - 86400 * 7;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where(['d.ip' => $ip,
                     'o.status' => [InfoOrder::STATUS_RISK_AUTO_REJECT, InfoOrder::STATUS_RISK_MANUAL_REJECT],
                     'o.product_id' => $this->data->infoOrder->product_id,
                     'o.app_name' => $this->data->order->app_name])
            ->andWhere(['>=', 'o.order_time', $before])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 同一IP下1天内在本平台申请的拒绝数
     * @return int
     */
    public function checkLast1dApplyRejectCntByIPSelf()
    {
        $ip = $this->data->infoDevice->ip;
        $before = $this->data->infoOrder->order_time - 86400;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where(['d.ip' => $ip,
                     'o.status' => [InfoOrder::STATUS_RISK_AUTO_REJECT, InfoOrder::STATUS_RISK_MANUAL_REJECT],
                     'o.product_id' => $this->data->infoOrder->product_id,
                     'o.app_name' => $this->data->order->app_name])
            ->andWhere(['>=', 'o.order_time', $before])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 同一IP下1小时内在本平台申请的拒绝数
     * @return int
     */
    public function checkLast1hApplyRejectCntByIPSelf()
    {
        $ip = $this->data->infoDevice->ip;
        $before = $this->data->infoOrder->order_time - 3600;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where(['d.ip' => $ip,
                     'o.status' => [InfoOrder::STATUS_RISK_AUTO_REJECT, InfoOrder::STATUS_RISK_MANUAL_REJECT],
                     'o.product_id' => $this->data->infoOrder->product_id,
                     'o.app_name' => $this->data->order->app_name])
            ->andWhere(['>=', 'o.order_time', $before])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 同一个IP下历史在本平台的已到期订单数
     * @return int
     */
    public function checkHisExpireCntByIPSelf()
    {
        $ip = $this->data->infoDevice->ip;
        $after = strtotime(date('Y-m-d', $this->data->infoOrder->order_time));
        $count = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoDevice::tableName().' as d', 'r.user_id=d.user_id and r.order_id=d.order_id and r.app_name=d.app_name')
            ->leftJoin(InfoOrder::tableName().' as o', 'r.user_id=o.user_id and r.order_id=o.order_id and r.app_name=o.app_name')
            ->where(['d.ip' => $ip,
                     'o.product_id' => $this->data->infoOrder->product_id,
                     'r.app_name' => $this->data->order->app_name])
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
        $ip = $this->data->infoDevice->ip;
        $after = strtotime(date('Y-m-d', $this->data->infoOrder->order_time));
        $data = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoDevice::tableName().' as d', 'r.user_id=d.user_id and r.order_id=d.order_id and r.app_name=d.app_name')
            ->leftJoin(InfoOrder::tableName().' as o', 'r.user_id=o.user_id and r.order_id=o.order_id and r.app_name=o.app_name')
            ->select(['r.is_overdue'])
            ->where(['d.ip' => $ip,
                     'o.product_id' => $this->data->infoOrder->product_id,
                     'r.app_name' => $this->data->order->app_name])
            ->andWhere(['<=', 'r.plan_repayment_time', $after])
            ->asArray()
            ->all();

        $count = 0;
        foreach ($data as $v){
            if($v['is_overdue'] == InfoRepayment::OVERDUE_YES){
                $count++;
            }
        }

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
        $latitude = $this->data->infoDevice->latitude;
        $longitude = $this->data->infoDevice->longitude;
        if(empty($latitude) || empty($longitude)){
            return -1;
        }
        $time = $this->data->infoOrder->order_time;
        $orderNum = EsUserLoanOrder::find()
            ->query([
                'bool' => [
                    'must' => [
                        [
                            'term' => [
                                'app_name' => $this->data->order->app_name,
                            ],
                        ],
                        [
                            'range' => [
                                'order_time' => [
                                    'gte' => Carbon::createFromTimestamp($time)->subDays(7)->toIso8601ZuluString(),
                                    'lte' => Carbon::createFromTimestamp($time)->toIso8601ZuluString()
                                ]
                            ],
                        ],
                    ],
                    'filter' => [
                        'geo_distance' => [
                            'distance' => '500m',
                            'location' => [
                                'lat' => $latitude,
                                'lon' => $longitude,
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
        //elasticsearch的使用说明
        //1.使用count时禁止使用source
        //2.distance单位km,m,cm,mm,nmi
        //3.date 时间戳或Zulu ISO8601
        $latitude = $this->data->infoDevice->latitude;
        $longitude = $this->data->infoDevice->longitude;
        if(empty($latitude) || empty($longitude)){
            return -1;
        }
        $time = $this->data->infoOrder->order_time;
        $orderNum = EsUserLoanOrder::find()
            ->query([
                'bool' => [
                    'must' => [
                        [
                            'term' => [
                                'app_name' => $this->data->order->app_name,
                            ],
                        ],
                        [
                            'range' => [
                                'order_time' => [
                                    'gte' => Carbon::createFromTimestamp($time)->subDays(1)->toIso8601ZuluString(),
                                    'lte' => Carbon::createFromTimestamp($time)->toIso8601ZuluString()
                                ]
                            ],
                        ],
                    ],
                    'filter' => [
                        'geo_distance' => [
                            'distance' => '500m',
                            'location' => [
                                'lat' => $latitude,
                                'lon' => $longitude,
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
        $latitude = $this->data->infoDevice->latitude;
        $longitude = $this->data->infoDevice->longitude;
        if(empty($latitude) || empty($longitude)){
            return -1;
        }
        $time = $this->data->infoOrder->order_time;
        $orderNum = EsUserLoanOrder::find()
            ->query([
                'bool' => [
                    'must' => [
                        [
                            'term' => [
                                'app_name' => $this->data->order->app_name,
                            ],
                        ],
                        [
                            'range' => [
                                'order_time' => [
                                    'gte' => Carbon::createFromTimestamp($time)->subHours(1)->toIso8601ZuluString(),
                                    'lte' => Carbon::createFromTimestamp($time)->toIso8601ZuluString()
                                ]
                            ],
                        ],
                    ],
                    'filter' => [
                        'geo_distance' => [
                            'distance' => '500m',
                            'location' => [
                                'lat' => $latitude,
                                'lon' => $longitude,
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
        $latitude = $this->data->infoDevice->latitude;
        $longitude = $this->data->infoDevice->longitude;
        if(empty($latitude) || empty($longitude)){
            return -1;
        }
        $time = $this->data->infoOrder->order_time;
        $orderNum = EsUserLoanOrder::find()
            ->query([
                'bool' => [
                    'must' => [
                        [
                            'term' => [
                                'app_name' => $this->data->order->app_name,
                            ],
                        ],
                        [
                            'range' => [
                                'order_time' => [
                                    'lte' => Carbon::createFromTimestamp($time)->toIso8601ZuluString()
                                ]
                            ],
                        ],
                    ],
                    'filter' => [
                        'geo_distance' => [
                            'distance' => '500m',
                            'location' => [
                                'lat' => $latitude,
                                'lon' => $longitude,
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
        $phone = $this->data->infoUser->phone;

        $lastTime = strtotime('last month');
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'u.user_id=o.user_id and u.order_id=o.order_id and u.app_name=o.app_name')
            ->where([
                'o.app_name' => $this->data->order->app_name,
                'o.product_id' => $this->data->infoOrder->product_id
            ])
            ->andWhere(['>=', 'o.order_time', $lastTime])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->andWhere(['or', ['u.contact1_mobile_number' => $phone], ['u.contact2_mobile_number' => $phone]])
            ->groupBy(['o.user_id'])
            ->count();

        return $count;
    }

    /**
     * 此手机号在本平台命中(逾期用户的本平台紧急联系人的手机号)个数
     * @return int
     */
    public function checkMobileSameAsOverdueContactMobileCntSelf()
    {
        $phone = $this->data->infoUser->phone;
        return InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName(). ' as u', 'r.user_id=u.user_id and r.order_id=u.order_id and r.app_name=u.app_name')
            ->leftJoin(InfoOrder::tableName(). ' as o', 'r.user_id=o.user_id and r.order_id=o.order_id and r.app_name=o.app_name')
            ->where(['r.is_overdue' => InfoRepayment::OVERDUE_YES,
                     'o.product_id' => $this->data->infoOrder->product_id,
                     'r.app_name' => $this->data->order->app_name])
            ->andWhere(['or', ['u.contact1_mobile_number' => $phone], ['u.contact2_mobile_number' => $phone]])
            ->count('DISTINCT r.app_name,r.user_id');
    }

    /**
     * 获取该pan下所有用户紧急联系人
     * @param $user_id
     * @return array|mixed
     */
    protected function getUserContacts($user_id)
    {
        $key = "{$user_id}";
        if (isset($this->userContactsSelf[$key])) {
            return $this->userContactsSelf[$key];
        } else {
            $userContact = InfoUser::find()
                ->alias('u')
                ->leftJoin(InfoOrder::tableName().' as o', 'o.order_id=u.order_id and o.user_id=u.user_id and o.app_name=u.app_name')
                ->select(['u.contact1_mobile_number', 'u.contact2_mobile_number'])
                ->where(['u.user_id' => $user_id,
                         'o.product_id' => $this->data->infoOrder->product_id,
                         'u.app_name' => $this->data->order->app_name])
                ->asArray()->all();
            $phones = array_unique(array_merge(
                ArrayHelper::getColumn($userContact, 'contact1_mobile_number'),
                ArrayHelper::getColumn($userContact, 'contact2_mobile_number')
            ));
            return $this->userContactsSelf[$key] = $phones;
        }
    }

    /**
     * 近1个月内此Pan卡下的(本平台的紧急联系人)的手机号码作为本平台紧急联系人在本平台出现的最大次数
     * @return mixed
     */
    public function checkSameContactCntLast1MonthSelf()
    {
        $phones = $this->getUserContacts($this->data->infoOrder->user_id);
        $lastTime = strtotime('last month');

        $count1 = InfoUser::find()
            ->alias('u')
            ->leftJoin(InfoOrder::tableName().' as o', 'o.order_id=u.order_id and o.user_id=u.user_id and o.app_name=u.app_name')
            ->where(['u.contact1_mobile_number' => $phones,
                     'o.product_id' => $this->data->infoOrder->product_id,
                     'u.app_name' => $this->data->order->app_name])
            ->andWhere(['>=', 'u.created_at', $lastTime])
            ->andWhere(['<=', 'u.created_at', $this->data->infoOrder->order_time])
            ->groupBy(['u.user_id'])
            ->count();

        $count2 = InfoUser::find()
            ->alias('u')
            ->leftJoin(InfoOrder::tableName().' as o', 'o.order_id=u.order_id and o.user_id=u.user_id and o.app_name=u.app_name')
            ->where(['u.contact2_mobile_number' => $phones,
                     'o.product_id' => $this->data->infoOrder->product_id,
                     'u.app_name' => $this->data->order->app_name])
            ->andWhere(['>=', 'u.created_at', $lastTime])
            ->andWhere(['<=', 'u.created_at', $this->data->infoOrder->order_time])
            ->groupBy(['u.user_id'])
            ->count();

        return max($count1, $count2);
    }

    /**
     * 此Pan卡号下的本平台紧急联系人的手机号码是否为有效号码
     * @return int  1 两个号码均有效   0 至少有一个号码无效
     */
    public function checkContactMobileIsValidSelf()
    {
        $phones = $this->getUserContacts($this->data->infoUser->user_id);

        $i = 0;
        foreach ($phones as $phone) {
            if (preg_match(Util::getPhoneMatch(),$phone)) {
                $i++;
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
        $phones = $this->getUserContacts($this->data->infoUser->user_id);
        if (in_array($this->data->infoUser->phone, $phones)
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
        $phones = $this->getUserContacts($this->data->infoUser->user_id);

        $count = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName().' as u', 'u.order_id=r.order_id and u.user_id=r.user_id and u.app_name=r.app_name')
            ->leftJoin(InfoOrder::tableName().' as o', 'o.order_id=r.order_id and o.user_id=r.user_id and o.app_name=r.app_name')
            ->where([
                'u.phone' => $phones,
                'r.app_name' => $this->data->order->app_name,
                'o.product_id' => $this->data->infoOrder->product_id,
                'r.is_overdue' => InfoRepayment::OVERDUE_YES
            ])
            ->andWhere(['!=', 'r.user_id', $this->data->infoUser->user_id])
            ->groupBy(['r.user_id'])
            ->count();

        return $count;
    }

    /**
     * 此Pan卡号下的本平台紧急联系人的手机号命中(本平台逾期用户Pan卡下的本平台紧急联系人的手机号)数量
     * @return int|string
     */
    public function checkContactNameMobileHitOverdueUserContactMobileCntSelf()
    {
        $phones = $this->getUserContacts($this->data->infoUser->user_id);

        $count = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName().' as u', 'u.order_id=r.order_id and u.user_id=r.user_id and u.app_name=r.app_name')
            ->leftJoin(InfoOrder::tableName().' as o', 'o.order_id=r.order_id and o.user_id=r.user_id and o.app_name=r.app_name')
            ->where([
                'r.app_name' => $this->data->order->app_name,
                'o.product_id' => $this->data->infoOrder->product_id,
                'r.is_overdue' => InfoRepayment::OVERDUE_YES
            ])
            ->andWhere(['!=', 'r.user_id', $this->data->infoUser->user_id])
            ->andWhere(['or', ['u.contact1_mobile_number' => $phones], ['u.contact2_mobile_number' => $phones]])
            ->groupBy(['r.user_id'])
            ->count();

        return $count;
    }

    /**
     * 此Pan卡号下的本平台紧急联系人的手机号命中(本平台逾期30+用户Pan卡下的本平台手机号)的数量
     * @return int|string
     */
    public function checkContactNameMobileHitOver30OverdueUserContactMobileCntSelf()
    {
        $phones = $this->getUserContacts($this->data->order->user_id);

        $count = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName().' as u', 'u.order_id=r.order_id and u.user_id=r.user_id and u.app_name=r.app_name')
            ->leftJoin(InfoOrder::tableName().' as o', 'o.order_id=r.order_id and o.user_id=r.user_id and o.app_name=r.app_name')
            ->where([
                'r.app_name' => $this->data->order->app_name,
                'o.product_id' => $this->data->infoOrder->product_id,
                'r.is_overdue' => InfoRepayment::OVERDUE_YES
            ])
            ->andWhere(['!=', 'r.user_id', $this->data->infoUser->user_id])
            ->andWhere(['>=', 'r.overdue_day', 30])
            ->andWhere(['or', ['u.contact1_mobile_number' => $phones], ['u.contact2_mobile_number' => $phones]])
            ->groupBy(['r.user_id'])
            ->count();

        return $count;
    }

    /**
     * 此Pan卡号下的本平台紧急联系人的手机号命中(本平台逾期30+用户Pan卡下的本平台紧急联系人手机号)数量
     * @return int|string
     */
    public function checkContactNameMobileHitOver30OverdueUserMobileCntSelf()
    {
        $phones = $this->getUserContacts($this->data->order->user_id);

        $count = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName().' as u', 'u.order_id=r.order_id and u.user_id=r.user_id and u.app_name=r.app_name')
            ->leftJoin(InfoOrder::tableName().' as o', 'o.order_id=r.order_id and o.user_id=r.user_id and o.app_name=r.app_name')
            ->where([
                'r.app_name' => $this->data->order->app_name,
                'r.is_overdue' => InfoRepayment::OVERDUE_YES,
                'o.product_id' => $this->data->infoOrder->product_id,
                'u.phone' => $phones
            ])
            ->andWhere(['!=', 'r.user_id', $this->data->infoUser->user_id])
            ->andWhere(['>=', 'r.overdue_day', 30])
            ->andWhere(['or', ['u.contact1_mobile_number' => $phones], ['u.contact2_mobile_number' => $phones]])
            ->groupBy(['r.user_id'])
            ->count();

        return $count;
    }

    /**
     * 历史该Pan卡号在本平台申请被拒次数
     * @return int|string
     */
    public function checkHisRejectCntByPanSelf()
    {
        $userIds = $this->data->order->user_id;
        $count = InfoOrder::find()
            ->where(['status' => [InfoOrder::STATUS_RISK_AUTO_REJECT, InfoOrder::STATUS_RISK_MANUAL_REJECT],
                     'app_name' => $this->data->order->app_name,
                     'product_id' => $this->data->infoOrder->product_id,
                     'user_id' => $userIds])
            ->andWhere(['<=', 'order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近90天该Pan卡号在本平台申请被拒次数
     * @return int|string
     */
    public function checkLast90dRejectCntByPanSelf()
    {
        $lastTime = $this->data->infoOrder->order_time - 90 * 86400;
        $userIds = $this->data->order->user_id;
        $count = InfoOrder::find()
            ->where([
                'status' => [InfoOrder::STATUS_RISK_AUTO_REJECT, InfoOrder::STATUS_RISK_MANUAL_REJECT],
                'product_id' => $this->data->infoOrder->product_id,
                'app_name' => $this->data->order->app_name,
                'user_id' => $userIds
            ])
            ->andWhere(['>=', 'order_time', $lastTime])
            ->andWhere(['<=', 'order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近60天该Pan卡号在本平台申请被拒次数
     * @return int|string
     */
    public function checkLast60dRejectCntByPanSelf()
    {
        $lastTime = $this->data->infoOrder->order_time - 60 * 86400;
        $userIds = $this->data->order->user_id;
        $count = InfoOrder::find()
            ->where([
                'status' => [InfoOrder::STATUS_RISK_AUTO_REJECT, InfoOrder::STATUS_RISK_MANUAL_REJECT],
                'app_name' => $this->data->order->app_name,
                'product_id' => $this->data->infoOrder->product_id,
                'user_id' => $userIds
            ])
            ->andWhere(['>=', 'order_time', $lastTime])
            ->andWhere(['<=', 'order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近30天该Pan卡号在本平台申请被拒次数
     * @return int|string
     */
    public function checkLast30dRejectCntByPanSelf()
    {
        $lastTime = $this->data->infoOrder->order_time - 30 * 86400;
        $userIds = $this->data->order->user_id;
        $count = InfoOrder::find()
            ->where([
                'status' => [InfoOrder::STATUS_RISK_AUTO_REJECT, InfoOrder::STATUS_RISK_MANUAL_REJECT],
                'app_name' => $this->data->order->app_name,
                'product_id' => $this->data->infoOrder->product_id,
                'user_id' => $userIds
            ])
            ->andWhere(['>=', 'order_time', $lastTime])
            ->andWhere(['<=', 'order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近7天该Pan卡号在本平台申请被拒次数
     * @return int|string
     */
    public function checkLast7dRejectCntByPanSelf()
    {
        $lastTime = $this->data->infoOrder->order_time - 7 * 86400;
        $userIds = $this->data->order->user_id;
        $count = InfoOrder::find()
            ->where([
                'status' => [InfoOrder::STATUS_RISK_AUTO_REJECT, InfoOrder::STATUS_RISK_MANUAL_REJECT],
                'app_name' => $this->data->order->app_name,
                'product_id' => $this->data->infoOrder->product_id,
                'user_id' => $userIds
            ])
            ->andWhere(['>=', 'order_time', $lastTime])
            ->andWhere(['<=', 'order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 历史该Pan卡号在本平台的申请次数
     * @return int|string
     */
    public function checkHisApplyCntByPanSelf()
    {
        $userIds = $this->data->order->user_id;
        $count = InfoOrder::find()
            ->where([
                'user_id' => $userIds,
                'product_id' => $this->data->infoOrder->product_id,
                'app_name' => $this->data->order->app_name,
            ])
            ->andWhere(['<=', 'order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近90天该Pan卡号在本平台的申请次数
     * @return int|string
     */
    public function checkLast90dApplyCntByPanSelf()
    {
        $lastTime = $this->data->infoOrder->order_time - 90 * 86400;
        $userIds = $this->data->order->user_id;
        $count = InfoOrder::find()
            ->where([
                'user_id' => $userIds,
                'product_id' => $this->data->infoOrder->product_id,
                'app_name' => $this->data->order->app_name,
            ])
            ->andWhere(['>=', 'order_time', $lastTime])
            ->andWhere(['<=', 'order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近60天该Pan卡号在本平台的申请次数
     * @return int|string
     */
    public function checkLast60dApplyCntByPanSelf()
    {
        $lastTime = $this->data->infoOrder->order_time - 60 * 86400;
        $userIds = $this->data->order->user_id;
        $count = InfoOrder::find()
            ->where([
                'user_id' => $userIds,
                'product_id' => $this->data->infoOrder->product_id,
                'app_name' => $this->data->order->app_name,
            ])
            ->andWhere(['>=', 'order_time', $lastTime])
            ->andWhere(['<=', 'order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近30天该Pan卡号在本平台的申请次数
     * @return int|string
     */
    public function checkLast30dApplyCntByPanSelf()
    {
        $lastTime = $this->data->infoOrder->order_time - 30 * 86400;
        $userIds = $this->data->order->user_id;
        $count = InfoOrder::find()
            ->where([
                'user_id' => $userIds,
                'product_id' => $this->data->infoOrder->product_id,
                'app_name' => $this->data->order->app_name,
            ])
            ->andWhere(['>=', 'order_time', $lastTime])
            ->andWhere(['<=', 'order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近7天该Pan卡号在本平台的申请次数
     * @return int|string
     */
    public function checkLast7dApplyCntByPanSelf()
    {
        $lastTime = $this->data->infoOrder->order_time - 7 * 86400;
        $userIds = $this->data->order->user_id;
        $count = InfoOrder::find()
            ->where([
                'user_id' => $userIds,
                'product_id' => $this->data->infoOrder->product_id,
                'app_name' => $this->data->order->app_name,
            ])
            ->andWhere(['>=', 'order_time', $lastTime])
            ->andWhere(['<=', 'order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 历史该Pan卡号在本平台放款的次数
     * @return int
     */
    public function checkHisLoanCntByPanSelf(){
        $userIds = $this->data->order->user_id;
        $count = InfoOrder::find()
            ->where([
                'user_id' => $userIds,
                'product_id' => $this->data->infoOrder->product_id,
                'app_name' => $this->data->order->app_name
            ])
            ->andWhere(['<=', 'loan_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近30天该Pan卡号在本平台放款的次数
     * @return int
     */
    public function checkLast30dLoanCntByPanSelf(){
        $begin_time = $this->data->infoOrder->order_time - 30 * 86400;
        $userIds = $this->data->order->user_id;

        $count = InfoOrder::find()
            ->where([
                'user_id' => $userIds,
                'product_id' => $this->data->infoOrder->product_id,
                'app_name' => $this->data->order->app_name
            ])
            ->andWhere(['>=', 'loan_time', $begin_time])
            ->andWhere(['<=', 'loan_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 本平台该Pan卡号本次申请订单距离上次申请订单时间的时间差
     * @return int
     */
    public function checkDateDiffOfOrderAndLastOrderApplyByPanSelf(){
        $userIds = $this->data->order->user_id;

        $data = InfoOrder::find()
            ->where(['user_id' => $userIds,
                     'product_id' => $this->data->infoOrder->product_id,
                     'app_name' => $this->data->order->app_name])
            ->andWhere(['<', 'order_time', $this->data->infoOrder->order_time])
            ->orderBy(['order_time' => SORT_DESC])
            ->one();

        if(empty($data)){
            return -1;
        }

        $orderTime = $data['order_time'];

        return (strtotime(date('Y-m-d', $this->data->infoOrder->order_time)) - strtotime(date('Y-m-d', $orderTime))) / 86400;
    }

    /**
     * 本平台该Pan卡号本次申请订单距离上次放款订单时间的时间差
     * @return int
     */
    public function checkDateDiffOfOrderAndLastOrderLoanByPanSelf(){
        $userIds = $this->data->order->user_id;

        $data = InfoOrder::find()
            ->where([
                'user_id' => $userIds,
                'product_id' => $this->data->infoOrder->product_id,
                'app_name' => $this->data->order->app_name
            ])
            ->andWhere(['>', 'loan_time', 0])
            ->andWhere(['<=', 'loan_time', $this->data->infoOrder->order_time])
            ->orderBy(['loan_time' => SORT_DESC])
            ->one();

        if(empty($data)){
            return -1;
        }

        $orderTime = $data['loan_time'];

        return (strtotime(date('Y-m-d', $this->data->infoOrder->order_time)) - strtotime(date('Y-m-d', $orderTime))) / 86400;
    }

    /**
     * 近30天该Pan卡号在本平台已到期订单数
     * @return int
     */
    public function checkLast30dExpireCntByPanSelf(){
        $userIds = $this->data->order->user_id;

        $after = strtotime(date('Y-m-d', $this->data->infoOrder->order_time));
        $before = $after - 30 * 86400;

        $count = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoOrder::tableName().' as o', 'o.order_id=r.order_id and o.user_id=r.user_id and o.app_name=r.app_name')
            ->where([
                'r.user_id' => $userIds,
                'o.product_id' => $this->data->infoOrder->product_id,
                'r.app_name' => $this->data->order->app_name
            ])
            ->andWhere(['>=', 'r.plan_repayment_time', $before])
            ->andWhere(['<=', 'r.plan_repayment_time', $after])
            ->count();

        return $count;
    }

    /**
     * 历史该Pan卡号在本平台的还款次数
     * @return int
     */
    public function checkHisRepayCntByPanSelf(){
        $userIds = $this->data->order->user_id;

        $count = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoOrder::tableName().' as o', 'o.order_id=r.order_id and o.user_id=r.user_id and o.app_name=r.app_name')
            ->where([
                'r.user_id' => $userIds,
                'r.app_name' => $this->data->order->app_name,
                'o.product_id' => $this->data->infoOrder->product_id,
                'r.status' => InfoRepayment::STATUS_CLOSED
            ])
            ->andWhere(['<=', 'r.closing_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 近30天该Pan卡号在本平台的还款次数
     * @return int
     */
    public function checkLast30dRepayCntByPanSelf(){
        $before = $this->data->infoOrder->order_time - 30 * 86400;
        $userIds = $this->data->order->user_id;
        $count = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoOrder::tableName().' as o', 'o.order_id=r.order_id and o.user_id=r.user_id and o.app_name=r.app_name')
            ->where([
                'r.user_id' => $userIds,
                'r.app_name' => $this->data->order->app_name,
                'o.product_id' => $this->data->infoOrder->product_id,
                'r.status' => InfoRepayment::STATUS_CLOSED
            ])
            ->andWhere(['>=', 'r.closing_time', $before])
            ->andWhere(['<=', 'r.closing_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 历史该Pan卡号在本平台的逾期次数
     * @return int
     */
    public function checkHisDueCntByPanSelf(){
        if(empty($this->getProductOrderData())){
            return -9999;
        }

        $data = $this->getProductOrderData(0, 15);

        return count($data);
    }

    /**
     * 近30天该Pan卡号在本平台的逾期次数
     * @return int
     */
    public function checkLast30dDueCntByPanSelf(){
        if(empty($this->getProductOrderData(30))){
            return -9999;
        }

        $data = $this->getProductOrderData(30, 15);

        return count($data);
    }

    /**
     * 本平台-根据Pan号查询的历史逾期天数的总和
     * @return int
     */
    public function checkHisDueSumDayByPanSelf(){
        if(empty($this->getProductOrderData())){
            return -9999;
        }

        $data = $this->getProductOrderData(0, 15);
        if(empty($data)){
            return 0;
        }

        return array_sum($data);
    }

    /**
     * 本平台-根据Pan号查询的历史逾期天数的平均值
     * @return int
     */
    public function checkHisDueAvgDayByPanSelf(){
        if(empty($this->getProductOrderData())){
            return -9999;
        }

        $data = $this->getProductOrderData(0, 15);

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
        if(empty($this->getProductOrderData())){
            return -9999;
        }

        $data = $this->getProductOrderData(0, 15);

        if(empty($data)){
            return 0;
        }

        return max($data);
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
            $orderTime = $this->data->infoOrder->order_time;
            $class = MgUserMobileSmsService::getModelName($this->data->infoUser->pan_code);

            $sms = $class::find()
                ->select(['messageContent','messageDate'])
                ->where(['type' => 1, 'pan_code' => $this->data->infoUser->pan_code])
                ->asArray()
                ->all();

            $begin_time7 = $orderTime - 86400 * 7;
            $begin_time30 = $orderTime - 86400 * 30;
            $begin_time60 = $orderTime - 86400 * 60;
            $begin_time90 = $orderTime - 86400 * 90;
            $begin_time180 = $orderTime - 86400 * 180;

            $data = [
                0 => [],
                7 => [],
                30 => [],
                60 => [],
                90 => [],
                180 => [],
            ];
            foreach ($sms as $values){
                $date = date('Y-m-d', $values['messageDate']);
                $data[0][$date][] = $values['messageContent'];

                if($values['messageDate'] >= $begin_time7){
                    $data[7][$date][] = $values['messageContent'];
                }

                if($values['messageDate'] >= $begin_time30){
                    $data[30][$date][] = $values['messageContent'];
                }

                if($values['messageDate'] >= $begin_time60){
                    $data[60][$date][] = $values['messageContent'];
                }

                if($values['messageDate'] >= $begin_time90){
                    $data[90][$date][] = $values['messageContent'];
                }

                if($values['messageDate'] >= $begin_time180){
                    $data[180][$date][] = $values['messageContent'];
                }
            }

            foreach ($data as $k => $value){
                $arr = [];
                foreach ($value as $v){
                    $arr = array_merge($arr,array_unique($v));
                }

                $this->userAllSms[$k] = $arr;
            }

            return $this->userAllSms[$key];
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
        $data = $this->getUserAllSmsByDay(7, $this->data->infoOrder->order_time);
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
        $data = $this->getUserAllSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(90, $this->data->infoOrder->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->overdueRemind($v)){
                $count++;
            }
        }

        return $count;
    }

    protected function amountPreg($sms){
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
        $data = $this->getUserAllSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(90, $this->data->infoOrder->order_time);

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
    protected function smsEmi($sms){
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
        $data = $this->getUserAllSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(90, $this->data->infoOrder->order_time);

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

    protected function overdueDayPreg($sms){
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
        $data = $this->getUserAllSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(90, $this->data->infoOrder->order_time);

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
    protected function smsSalary($sms){
        if(stripos($sms, 'salary') === false
            || stripos($sms, 'credited to') === false
            || stripos($sms, 'a/c') === false
        ){
            return false;
        }

        return true;
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
        $data = $this->getUserAllSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(180, $this->data->infoOrder->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->smsSalary($v)){
                $count++;
            }
        }

        return $count;
    }

    protected function salaryPreg($sms){
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
        $data = $this->getUserAllSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(180, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(180, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(180, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(180, $this->data->infoOrder->order_time);

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
    protected function smsSalaryAvlBal($sms){
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

    protected function salaryAvlBalPreg($sms){
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
        $data = $this->getUserAllSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(180, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(180, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(180, $this->data->infoOrder->order_time);

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
        $data = $this->getUserAllSmsByDay(180, $this->data->infoOrder->order_time);

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
     * 该手机号历史下单环节关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkPhoneHIsOrderMatchSMDeviceIDCnt(){
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where([
                'o.user_id' => $this->data->order->user_id,
                'o.product_id' => $this->data->infoOrder->product_id,
                'o.app_name' => $this->data->order->app_name
            ])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->groupBy(['d.szlm_query_id'])
            ->count();

        return $count;
    }

    /**
     * 近30天内该手机号下单环节关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkPhoneLast30dOrderMatchSMdeviceIDCnt(){
        $begin_time = $this->data->infoOrder->order_time - 30 * 86400;

        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where([
                'o.user_id' => $this->data->order->user_id,
                'o.product_id' => $this->data->infoOrder->product_id,
                'o.app_name' => $this->data->order->app_name
            ])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->groupBy(['d.szlm_query_id'])
            ->count();

        return $count;
    }

    /**
     * 近60天内该手机号下单环节关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkPhoneLast60dOrderMatchSMdeviceIDCnt(){
        $begin_time = $this->data->infoOrder->order_time - 60 * 86400;

        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where([
                'o.user_id' => $this->data->order->user_id,
                'o.product_id' => $this->data->infoOrder->product_id,
                'o.app_name' => $this->data->order->app_name
            ])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->groupBy(['d.szlm_query_id'])
            ->count();

        return $count;
    }

    /**
     * 近90天内该手机号下单环节关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkPhoneLast90dOrderMatchSMdeviceIDCnt(){
        $begin_time = $this->data->infoOrder->order_time - 90 * 86400;

        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where([
                'o.user_id' => $this->data->order->user_id,
                'o.product_id' => $this->data->infoOrder->product_id,
                'o.app_name' => $this->data->order->app_name
            ])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->groupBy(['d.szlm_query_id'])
            ->count();

        return $count;
    }

    /**
     * 该数盟设备ID历史下单环节关联过的不同手机号数量
     * @return int
     */
    public function checkSMDeviceIDHisOrderMatchPhoneCnt(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }

        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where([
                'd.szlm_query_id' => $this->data->infoDevice->szlm_query_id,
                'o.product_id' => $this->data->infoOrder->product_id,
                'o.app_name' => $this->data->order->app_name
            ])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->groupBy(['o.user_id'])
            ->count();

        return $count;
    }

    /**
     * 近30天内该数盟设备ID下单环节关联过的不同手机号数量
     * @return int
     */
    public function checkSMDeviceIDLast30dOrderMatchPhoneCnt(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }

        $begin_time = $this->data->infoOrder->order_time - 30 * 86400;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where([
                'd.szlm_query_id' => $this->data->infoDevice->szlm_query_id,
                'o.product_id' => $this->data->infoOrder->product_id,
                'o.app_name' => $this->data->order->app_name
            ])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->groupBy(['o.user_id'])
            ->count();

        return $count;
    }

    /**
     * 近60天内该数盟设备ID下单环节关联过的不同手机号数量
     * @return int
     */
    public function checkSMDeviceIDLast60dOrderMatchPhoneCnt(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }

        $begin_time = $this->data->infoOrder->order_time - 60 * 86400;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where([
                'd.szlm_query_id' => $this->data->infoDevice->szlm_query_id,
                'o.product_id' => $this->data->infoOrder->product_id,
                'o.app_name' => $this->data->order->app_name
            ])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->groupBy(['o.user_id'])
            ->count();

        return $count;
    }

    /**
     * 近90天内该数盟设备ID下单环节关联过的不同手机号数量
     * @return int
     */
    public function checkSMDeviceIDLast90dOrderMatchPhoneCnt(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }

        $begin_time = $this->data->infoOrder->order_time - 90 * 86400;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
            ->where([
                'd.szlm_query_id' => $this->data->infoDevice->szlm_query_id,
                'o.product_id' => $this->data->infoOrder->product_id,
                'o.app_name' => $this->data->order->app_name
            ])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->groupBy(['o.user_id'])
            ->count();

        return $count;
    }

    /**
     * 总平台该手机号历史成功关联过的不同Pan卡号数量
     * @return int
     */
    public function checkPhoneHisSuccessMatchPanCntTotPlatform(){
        $count = InfoUser::find()
            ->where(['phone' => $this->data->infoUser->phone])
            ->andWhere(['is not','pan_code',null])
            ->groupBy(['pan_code'])
            ->count();

        return $count;
    }

    /**
     * 总平台该手机号历史成功关联过的不同Aadhaar卡号数量
     * @return int
     */
    public function checkPhoneHisSuccessMatchAadhaarCntTotPlatform(){
        $count = InfoUser::find()
            ->where(['phone' => $this->data->infoUser->phone])
            ->andWhere(['is not','aadhaar_md5',null])
            ->groupBy(['aadhaar_md5'])
            ->count();

        return $count;
    }

    /**
     * 总平台该手机号历史下单环节关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkPhoneHIsOrderMatchSMDeviceIDCntTotPlatform(){
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'u.user_id=o.user_id and u.order_id=o.order_id and u.app_name=o.app_name')
            ->leftJoin(InfoDevice::tableName(). ' as d', 'd.user_id=o.user_id and d.order_id=o.order_id and d.app_name=o.app_name')
            ->where(['u.phone' => $this->data->infoUser->phone])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->groupBy(['d.szlm_query_id'])
            ->count();

        return $count;
    }

    /**
     * 总平台近30天内该手机号下单环节关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkPhoneLast30dOrderMatchSMdeviceIDCntTotPlatform(){
        $begin_time = $this->data->infoOrder->order_time - 30 * 86400;

        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'u.user_id=o.user_id and u.order_id=o.order_id and u.app_name=o.app_name')
            ->leftJoin(InfoDevice::tableName(). ' as d', 'd.user_id=o.user_id and d.order_id=o.order_id and d.app_name=o.app_name')
            ->where(['u.phone' => $this->data->infoUser->phone])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->groupBy(['d.szlm_query_id'])
            ->count();

        return $count;
    }

    /**
     * 总平台近60天内该手机号下单环节关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkPhoneLast60dOrderMatchSMdeviceIDCntTotPlatform(){
        $begin_time = $this->data->infoOrder->order_time - 60 * 86400;

        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'u.user_id=o.user_id and u.order_id=o.order_id and u.app_name=o.app_name')
            ->leftJoin(InfoDevice::tableName(). ' as d', 'd.user_id=o.user_id and d.order_id=o.order_id and d.app_name=o.app_name')
            ->where(['u.phone' => $this->data->infoUser->phone])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->groupBy(['d.szlm_query_id'])
            ->count();

        return $count;
    }

    /**
     * 总平台近90天内该手机号下单环节关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkPhoneLast90dOrderMatchSMdeviceIDCntTotPlatform(){
        $begin_time = $this->data->infoOrder->order_time - 90 * 86400;

        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'u.user_id=o.user_id and u.order_id=o.order_id and u.app_name=o.app_name')
            ->leftJoin(InfoDevice::tableName(). ' as d', 'd.user_id=o.user_id and d.order_id=o.order_id and d.app_name=o.app_name')
            ->where(['u.phone' => $this->data->infoUser->phone])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->groupBy(['d.szlm_query_id'])
            ->count();

        return $count;
    }

    /**
     * 总平台该Pan卡号历史成功关联过的不同手机号数量
     * @return int
     */
    public function checkPanHisSuccessMatchPhoneCntTotPlatform()
    {
        $pan_code = $this->data->infoUser->pan_code;
        $count = InfoUser::find()
            ->where(['pan_code' => $pan_code])
            ->groupBy(['phone'])
            ->count();
        return $count;
    }


    /**
     * 总平台该Pan卡号历史成功关联过的不同Aadhaar卡号数量
     * @return int
     */
    public function checkPanHisSuccessMatchAadhaarCntTotPlatform()
    {
        $pan_code = $this->data->infoUser->pan_code;
        $count = InfoUser::find()
            ->where(['pan_code' => $pan_code])
            ->andWhere(['is not', 'aadhaar_md5', null])
            ->groupBy(['aadhaar_md5'])
            ->count();
        return $count;
    }

    /**
     * 总平台该Pan卡号历史下单环节关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkPanHisOrderMatchSMDeviceIDCntTotPlatform(){
        $pan_code = $this->data->infoUser->pan_code;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'u.user_id=o.user_id and u.order_id=o.order_id and u.app_name=o.app_name')
            ->leftJoin(InfoDevice::tableName(). ' as d', 'd.user_id=o.user_id and d.order_id=o.order_id and d.app_name=o.app_name')
            ->where(['u.pan_code' => $pan_code])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->groupBy(['d.szlm_query_id'])
            ->count();

        return $count;
    }

    /**
     * 总平台近30天内该Pan卡号下单环节关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkPanLast30dOrderMatchSMdeviceIDCntTotPlatform(){
        $begin_time = $this->data->infoOrder->order_time - 30 * 86400;
        $pan_code = $this->data->infoUser->pan_code;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'u.user_id=o.user_id and u.order_id=o.order_id and u.app_name=o.app_name')
            ->leftJoin(InfoDevice::tableName(). ' as d', 'd.user_id=o.user_id and d.order_id=o.order_id and d.app_name=o.app_name')
            ->where(['u.pan_code' => $pan_code])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->groupBy(['d.szlm_query_id'])
            ->count();

        return $count;
    }

    /**
     * 总平台近60天内该Pan卡号下单环节关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkPanLast60dOrderMatchSMdeviceIDCntTotPlatform(){
        $begin_time = $this->data->infoOrder->order_time - 60 * 86400;
        $pan_code = $this->data->infoUser->pan_code;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'u.user_id=o.user_id and u.order_id=o.order_id and u.app_name=o.app_name')
            ->leftJoin(InfoDevice::tableName(). ' as d', 'd.user_id=o.user_id and d.order_id=o.order_id and d.app_name=o.app_name')
            ->where(['u.pan_code' => $pan_code])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->groupBy(['d.szlm_query_id'])
            ->count();

        return $count;
    }

    /**
     * 总平台近90天内该Pan卡号下单环节关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkPanLast90dOrderMatchSMdeviceIDCntTotPlatform(){
        $begin_time = $this->data->infoOrder->order_time - 90 * 86400;
        $pan_code = $this->data->infoUser->pan_code;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'u.user_id=o.user_id and u.order_id=o.order_id and u.app_name=o.app_name')
            ->leftJoin(InfoDevice::tableName(). ' as d', 'd.user_id=o.user_id and d.order_id=o.order_id and d.app_name=o.app_name')
            ->where(['u.pan_code' => $pan_code])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->groupBy(['d.szlm_query_id'])
            ->count();

        return $count;
    }

    /**
     * 总平台该Aadhaar卡号历史成功关联过的不同Pan卡号数量
     * @return int
     */
    public function checkAadhaarHisSuccessMatchPanCntTotPlatform()
    {
        $count = InfoUser::find()
            ->where(['aadhaar_md5' => $this->data->infoUser->aadhaar_md5])
            ->andWhere(['is not','pan_code',null])
            ->groupBy(['pan_code'])
            ->count();

        return $count;
    }

    /**
     * 总平台该Aadhaar卡号成功环节关联过的不同手机号数量
     * @return int
     */
    public function checkAadhaarHisSuccessMatchPhoneCntTotPlatform()
    {
        $count = InfoUser::find()
            ->where(['aadhaar_md5' => $this->data->infoUser->aadhaar_md5])
            ->groupBy(['phone'])
            ->count();

        return $count;
    }

    /**
     * 总平台该Aadhaar卡号历史下单环节关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkAadhaarHisOrderMatchSMDeviceIDCntTotPlatform(){
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'u.user_id=o.user_id and u.order_id=o.order_id and u.app_name=o.app_name')
            ->leftJoin(InfoDevice::tableName(). ' as d', 'd.user_id=o.user_id and d.order_id=o.order_id and d.app_name=o.app_name')
            ->where(['u.aadhaar_md5' => $this->data->infoUser->aadhaar_md5])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->groupBy(['d.szlm_query_id'])
            ->count();

        return $count;
    }

    /**
     * 总平台近30天内该Aadhaar卡号下单环节关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkAadhaarLast30dOrderMatchSMDeviceIDCntTotPlatform(){
        $begin_time = $this->data->infoOrder->order_time - 30 * 86400;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'u.user_id=o.user_id and u.order_id=o.order_id and u.app_name=o.app_name')
            ->leftJoin(InfoDevice::tableName(). ' as d', 'd.user_id=o.user_id and d.order_id=o.order_id and d.app_name=o.app_name')
            ->where(['u.aadhaar_md5' => $this->data->infoUser->aadhaar_md5])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->groupBy(['d.szlm_query_id'])
            ->count();

        return $count;
    }

    /**
     * 总平台近60天内该Aadhaar卡号下单环节关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkAadhaarLast60dOrderMatchSMDeviceIDCntTotPlatform(){
        $begin_time = $this->data->infoOrder->order_time - 60 * 86400;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'u.user_id=o.user_id and u.order_id=o.order_id and u.app_name=o.app_name')
            ->leftJoin(InfoDevice::tableName(). ' as d', 'd.user_id=o.user_id and d.order_id=o.order_id and d.app_name=o.app_name')
            ->where(['u.aadhaar_md5' => $this->data->infoUser->aadhaar_md5])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->groupBy(['d.szlm_query_id'])
            ->count();

        return $count;
    }

    /**
     * 总平台近90天内该Aadhaar卡号下单环节关联过的不同数盟设备ID的数量
     * @return int
     */
    public function checkAadhaarLast90dOrderMatchSMDeviceIDCntTotPlatform(){
        $begin_time = $this->data->infoOrder->order_time - 90 * 86400;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'u.user_id=o.user_id and u.order_id=o.order_id and u.app_name=o.app_name')
            ->leftJoin(InfoDevice::tableName(). ' as d', 'd.user_id=o.user_id and d.order_id=o.order_id and d.app_name=o.app_name')
            ->where(['u.aadhaar_md5' => $this->data->infoUser->aadhaar_md5])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->groupBy(['d.szlm_query_id'])
            ->count();

        return $count;
    }

    /**
     * 总平台该数盟设备ID历史下单环节关联过的不同手机号数量
     * @return int
     */
    public function checkSMDeviceIDHisOrderMatchPhoneCntTotPlatform(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }

        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'u.user_id=o.user_id and u.order_id=o.order_id and u.app_name=o.app_name')
            ->leftJoin(InfoDevice::tableName(). ' as d', 'd.user_id=o.user_id and d.order_id=o.order_id and d.app_name=o.app_name')
            ->where(['d.szlm_query_id' => $this->data->infoDevice->szlm_query_id])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->groupBy(['u.phone'])
            ->count();

        return $count;
    }

    /**
     * 近30天内该数盟设备ID下单环节关联过的不同手机号数量
     * @return int
     */
    public function checkSMDeviceIDLast30dOrderMatchPhoneCntTotPlatform(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }

        $begin_time = $this->data->infoOrder->order_time - 30 * 86400;

        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'u.user_id=o.user_id and u.order_id=o.order_id and u.app_name=o.app_name')
            ->leftJoin(InfoDevice::tableName(). ' as d', 'd.user_id=o.user_id and d.order_id=o.order_id and d.app_name=o.app_name')
            ->where(['d.szlm_query_id' => $this->data->infoDevice->szlm_query_id])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->groupBy(['u.phone'])
            ->count();

        return $count;
    }

    /**
     * 近60天内该数盟设备ID下单环节关联过的不同手机号数量
     * @return int
     */
    public function checkSMDeviceIDLast60dOrderMatchPhoneCntTotPlatform(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }

        $begin_time = $this->data->infoOrder->order_time - 60 * 86400;

        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'u.user_id=o.user_id and u.order_id=o.order_id and u.app_name=o.app_name')
            ->leftJoin(InfoDevice::tableName(). ' as d', 'd.user_id=o.user_id and d.order_id=o.order_id and d.app_name=o.app_name')
            ->where(['d.szlm_query_id' => $this->data->infoDevice->szlm_query_id])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->groupBy(['u.phone'])
            ->count();

        return $count;
    }

    /**
     * 近90天内该数盟设备ID下单环节关联过的不同手机号数量
     * @return int
     */
    public function checkSMDeviceIDLast90dOrderMatchPhoneCntTotPlatform(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }

        $begin_time = $this->data->infoOrder->order_time - 90 * 86400;

        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'u.user_id=o.user_id and u.order_id=o.order_id and u.app_name=o.app_name')
            ->leftJoin(InfoDevice::tableName(). ' as d', 'd.user_id=o.user_id and d.order_id=o.order_id and d.app_name=o.app_name')
            ->where(['d.szlm_query_id' => $this->data->infoDevice->szlm_query_id])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->groupBy(['u.phone'])
            ->count();

        return $count;
    }

    /**
     * 总平台该数盟设备ID历史下单环节关联过的不同Pan卡号数量
     * @return int
     */
    public function checkSMDeviceIDHisOrderMatchPanCntTotPlatform(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }

        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'u.user_id=o.user_id and u.order_id=o.order_id and u.app_name=o.app_name')
            ->leftJoin(InfoDevice::tableName(). ' as d', 'd.user_id=o.user_id and d.order_id=o.order_id and d.app_name=o.app_name')
            ->where(['d.szlm_query_id' => $this->data->infoDevice->szlm_query_id])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->andWhere(['is not', 'u.pan_code', null])
            ->groupBy(['u.pan_code'])
            ->count();

        return $count;
    }

    /**
     * 总平台近30天内该数盟设备ID下单环节关联过的不同Pan卡号数量
     * @return int
     */
    public function checkSMDeviceIDLast30dOrderMatchPanCntTotPlatform(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }

        $begin_time = $this->data->infoOrder->order_time - 30 * 86400;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'u.user_id=o.user_id and u.order_id=o.order_id and u.app_name=o.app_name')
            ->leftJoin(InfoDevice::tableName(). ' as d', 'd.user_id=o.user_id and d.order_id=o.order_id and d.app_name=o.app_name')
            ->where(['d.szlm_query_id' => $this->data->infoDevice->szlm_query_id])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->andWhere(['is not', 'u.pan_code', null])
            ->groupBy(['u.pan_code'])
            ->count();

        return $count;
    }

    /**
     * 总平台近60天内该数盟设备ID下单环节关联过的不同Pan卡号数量
     * @return int
     */
    public function checkSMDeviceIDLast60dOrderMatchPanCntTotPlatform(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }

        $begin_time = $this->data->infoOrder->order_time - 60 * 86400;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'u.user_id=o.user_id and u.order_id=o.order_id and u.app_name=o.app_name')
            ->leftJoin(InfoDevice::tableName(). ' as d', 'd.user_id=o.user_id and d.order_id=o.order_id and d.app_name=o.app_name')
            ->where(['d.szlm_query_id' => $this->data->infoDevice->szlm_query_id])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->andWhere(['is not', 'u.pan_code', null])
            ->groupBy(['u.pan_code'])
            ->count();

        return $count;
    }

    /**
     * 总平台近90天内该数盟设备ID下单环节关联过的不同Pan卡号数量
     * @return int
     */
    public function checkSMDeviceIDLast90dOrderMatchPanCntTotPlatform(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }

        $begin_time = $this->data->infoOrder->order_time - 90 * 86400;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'u.user_id=o.user_id and u.order_id=o.order_id and u.app_name=o.app_name')
            ->leftJoin(InfoDevice::tableName(). ' as d', 'd.user_id=o.user_id and d.order_id=o.order_id and d.app_name=o.app_name')
            ->where(['d.szlm_query_id' => $this->data->infoDevice->szlm_query_id])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->andWhere(['is not', 'u.pan_code', null])
            ->groupBy(['u.pan_code'])
            ->count();

        return $count;
    }

    /**
     * 总平台该数盟设备ID历史下单环节关联过的不同Aadhaar卡号数量
     * @return int
     */
    public function checkSMDeviceIDHisOrderMatchAadhaarCntTotPlatform(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }

        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'u.user_id=o.user_id and u.order_id=o.order_id and u.app_name=o.app_name')
            ->leftJoin(InfoDevice::tableName(). ' as d', 'd.user_id=o.user_id and d.order_id=o.order_id and d.app_name=o.app_name')
            ->where(['d.szlm_query_id' => $this->data->infoDevice->szlm_query_id])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->andWhere(['is not', 'u.aadhaar_md5', null])
            ->groupBy(['u.aadhaar_md5'])
            ->count();

        return $count;
    }

    /**
     * 总平台近30天内该数盟设备ID下单环节关联过的不同Aadhaar卡号数量
     * @return int
     */
    public function checkSMDeviceIDLast30dOrderMatchAadhaarCntTotPlatform(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }

        $begin_time = $this->data->infoOrder->order_time - 30 * 86400;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'u.user_id=o.user_id and u.order_id=o.order_id and u.app_name=o.app_name')
            ->leftJoin(InfoDevice::tableName(). ' as d', 'd.user_id=o.user_id and d.order_id=o.order_id and d.app_name=o.app_name')
            ->where(['d.szlm_query_id' => $this->data->infoDevice->szlm_query_id])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->andWhere(['is not', 'u.aadhaar_md5', null])
            ->groupBy(['u.aadhaar_md5'])
            ->count();

        return $count;
    }

    /**
     * 总平台近60天内该数盟设备ID下单环节关联过的不同Aadhaar卡号数量
     * @return int
     */
    public function checkSMDeviceIDLast60dOrderMatchAadhaarCntTotPlatform(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }

        $begin_time = $this->data->infoOrder->order_time - 60 * 86400;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'u.user_id=o.user_id and u.order_id=o.order_id and u.app_name=o.app_name')
            ->leftJoin(InfoDevice::tableName(). ' as d', 'd.user_id=o.user_id and d.order_id=o.order_id and d.app_name=o.app_name')
            ->where(['d.szlm_query_id' => $this->data->infoDevice->szlm_query_id])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->andWhere(['is not', 'u.aadhaar_md5', null])
            ->groupBy(['u.aadhaar_md5'])
            ->count();

        return $count;
    }

    /**
     * 总平台近90天内该数盟设备ID下单环节关联过的不同Aadhaar卡号数量
     * @return int
     */
    public function checkSMDeviceIDLast90dOrderMatchAadhaarCntTotPlatform(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }

        $begin_time = $this->data->infoOrder->order_time - 90 * 86400;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'u.user_id=o.user_id and u.order_id=o.order_id and u.app_name=o.app_name')
            ->leftJoin(InfoDevice::tableName(). ' as d', 'd.user_id=o.user_id and d.order_id=o.order_id and d.app_name=o.app_name')
            ->where(['d.szlm_query_id' => $this->data->infoDevice->szlm_query_id])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->andWhere(['is not', 'u.aadhaar_md5', null])
            ->groupBy(['u.aadhaar_md5'])
            ->count();

        return $count;
    }

    /**
     * 老用户-总平台近7天还款次数占历史放款订单的比例
     * @return int
     */
    public function checkLast7dRepayCntHisCntRateTPF(){
        $pan_code = $this->data->infoUser->pan_code;
        $count = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName().' as u', 'u.user_id=r.user_id and u.order_id=r.order_id and u.app_name=r.app_name')
            ->where(['u.pan_code' => $pan_code])
            ->count();

        if(empty($count)){
            return -9999;
        }

        $begin_time = strtotime(date('Y-m-d', $this->data->infoOrder->order_time)) - 7 * 86400;
        $repay_count = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName().' as u', 'u.user_id=r.user_id and u.order_id=r.order_id and u.app_name=r.app_name')
            ->where(['u.pan_code' => $pan_code])
            ->andWhere(['>=', 'r.closing_time', $begin_time])
            ->count();

        return round($repay_count / $count * 100);
    }

    /**
     * 老用户-总平台近7天正常还款的订单数
     * @return int
     */
    public function checkLast7dTiqianRepayCntTPF(){
        $begin_time = strtotime(date('Y-m-d', $this->data->infoOrder->order_time)) - 7 * 86400;

        $pan_code = $this->data->infoUser->pan_code;
        $data = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName().' as u', 'u.user_id=r.user_id and u.order_id=r.order_id and u.app_name=r.app_name')
            ->select(['r.is_overdue'])
            ->where([
                'u.pan_code' => $pan_code,
            ])
            ->andWhere(['>=', 'r.closing_time', $begin_time])
            ->asArray()
            ->all();

        if(empty($data)){
            return -9999;
        }

        $count = 0;
        foreach ($data as $v){
            if($v['is_overdue'] == InfoRepayment::OVERDUE_YES){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 老用户-总平台本单申请日期与首单的日期差
     * @return int
     */
    public function checkMaxDateOfOrderToTodayTPF(){
        $pan_code = $this->data->infoUser->pan_code;

        $data = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'u.user_id=o.user_id and u.order_id=o.order_id and u.app_name=o.app_name')
            ->select(['o.order_time'])
            ->where(['u.pan_code' => $pan_code])
            ->andWhere(['>', 'o.loan_time', 0])
            ->orderBy(['o.order_time' => SORT_ASC])
            ->asArray()
            ->one();

        return (strtotime(date('Y-m-d', $this->data->infoOrder->order_time)) - strtotime(date('Y-m-d', $data['order_time'])))/86400;
    }

    /**
     * 老用户模型分V3
     * @return int
     */
    public function checkOldUserModelScoreV3(){
        $v356 = $this->checkIsSMSRecordGrabNormal();

        $score = 0;
        if($v356 == 0){
            $score += 321;
        }else{
            $v601 = $this->checkSMSCntOfLoanRejectionLast7Days();
            switch (true){
                case $v601 < 1:
                    $score += 65;
                    break;
                case $v601 < 2:
                    $score += 48;
                    break;
                case $v601 < 3:
                    $score += 30;
                    break;
                case $v601 >= 3:
                    $score += 9;
                    break;
            }

            $v606 = $this->checkSMSCntOfLoanApprovalLast7Days();
            switch (true){
                case $v606 < 1:
                    $score += 37;
                    break;
                case $v606 < 4:
                    $score += 48;
                    break;
                case $v606 < 9:
                    $score += 62;
                    break;
                case $v606 >= 9:
                    $score += 84;
                    break;
            }

            $v610 = $this->checkHistSMSCntOfLoanDisbursal();
            switch (true){
                case $v610 < 1:
                    $score += 38;
                    break;
                case $v610 < 5:
                    $score += 51;
                    break;
                case $v610 < 12:
                    $score += 63;
                    break;
                case $v610 < 21:
                    $score += 82;
                    break;
                case $v610 >= 21:
                    $score += 94;
                    break;
            }

            $v616 = $this->checkSMSCntOfLoanDueRemindLast7Days();
            switch (true){
                case $v616 < 2:
                    $score += 54;
                    break;
                case $v616 < 23:
                    $score += 59;
                    break;
                case $v616 < 33:
                    $score += 45;
                    break;
                case $v616 >= 33:
                    $score += 32;
                    break;
            }

            $v626 = $this->checkSMSCntOfOverdueRemindLast7Days();
            switch (true){
                case $v626 < 1:
                    $score += 59;
                    break;
                case $v626 < 2:
                    $score += 52;
                    break;
                case $v626 < 4:
                    $score += 47;
                    break;
                case $v626 >= 4:
                    $score += 39;
                    break;
            }

            $v627 = $this->checkSMSCntOfOverdueRemindLast30Days();
            switch (true){
                case $v627 < 1:
                    $score += 64;
                    break;
                case $v627 < 2:
                    $score += 59;
                    break;
                case $v627 < 5:
                    $score += 50;
                    break;
                case $v627 >= 5:
                    $score += 38;
                    break;
            }
        }

        $v1196 = $this->checkLast7dRepayCntHisCntRateTPF();
        switch (true){
            case $v1196 < 18:
                $score += 53;
                break;
            case $v1196 < 22:
                $score += 62;
                break;
            case $v1196 < 32:
                $score += 58;
                break;
            case $v1196 < 84:
                $score += 56;
                break;
            case $v1196 >= 84:
                $score += 48;
                break;
        }

        $v1197 = $this->checkLast7dTiqianRepayCntTPF();
        switch (true){
            case $v1197 < 1:
                $score += 40;
                break;
            case $v1197 < 3:
                $score += 56;
                break;
            case $v1197 < 4:
                $score += 70;
                break;
            case $v1197 >= 4:
                $score += 62;
                break;
        }

        $v675 = $this->checkHistMaxOverdueDaysByPanTotPlatform();
        switch (true){
            case $v675 < 1:
                $score += 81;
                break;
            case $v675 < 2:
                $score += 48;
                break;
            case $v675 < 3:
                $score += 30;
                break;
            case $v675 < 5:
                $score += 13;
                break;
            case $v675 >= 5:
                $score += -53;
                break;
        }

        $v1198 = $this->checkMaxDateOfOrderToTodayTPF();
        switch (true){
            case $v1198 < 12:
                $score += 18;
                break;
            case $v1198 < 60:
                $score += 50;
                break;
            case $v1198 < 96:
                $score += 78;
                break;
            case $v1198 >= 96:
                $score += 101;
                break;
        }

        $v722 = $this->checkOldUserComplexRuleV1HisCpDaySumTotPlatform();
        switch (true){
            case $v722 < -6:
                $score += 58;
                break;
            case $v722 < 1:
                $score += 56;
                break;
            case $v722 < 3:
                $score += 49;
                break;
            case $v722 >= 3:
                $score += 41;
                break;
        }

        return $score;
    }

    /**
     * 人脸对比的服务来源
     * @return int
     */
    public function checkSourceOfFaceCompare(){
        if(!$this->data->infoUser->fr_verify_source){
            return -1;
        }

        if($this->data->infoUser->fr_verify_source == 'accu_auth'){
            return 1;
        }

        if($this->data->infoUser->fr_verify_source == 'advance_ai'){
            return 2;
        }

        return -1;
    }


    /**
     * 老用户-总平台近7天的申请订单量
     * @return int
     */
    public function checkLast7dOrderApplyCntTPF(){
        $begin_time = strtotime(date('Y-m-d', $this->data->infoOrder->order_time)) - 7 * 86400;
        $pan_code = $this->data->infoUser->pan_code;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'u.user_id=o.user_id and u.order_id=o.order_id and u.app_name=o.app_name')
            ->where(['u.pan_code' => $pan_code])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 老用户-总平台近15天的申请订单量
     * @return int
     */
    public function checkLast15dOrderApplyCntTPF(){
        $begin_time = strtotime(date('Y-m-d', $this->data->infoOrder->order_time)) - 15 * 86400;
        $pan_code = $this->data->infoUser->pan_code;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'u.user_id=o.user_id and u.order_id=o.order_id and u.app_name=o.app_name')
            ->where(['u.pan_code' => $pan_code])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 老用户-总平台近30天的申请订单量
     * @return int
     */
    public function checkLast30dOrderApplyCntTPF(){
        $begin_time = strtotime(date('Y-m-d', $this->data->infoOrder->order_time)) - 30 * 86400;
        $pan_code = $this->data->infoUser->pan_code;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'u.user_id=o.user_id and u.order_id=o.order_id and u.app_name=o.app_name')
            ->where(['u.pan_code' => $pan_code])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 老用户-总平台历史的申请量
     * @return int
     */
    public function checkHisOrderApplyCntTPF(){
        $pan_code = $this->data->infoUser->pan_code;
        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'u.user_id=o.user_id and u.order_id=o.order_id and u.app_name=o.app_name')
            ->where(['u.pan_code' => $pan_code])
            ->andWhere(['<', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 老用户-总平台近15天逾期还款的单数占近30天放款订单数的比例
     * @return int
     */
    public function checkLast15dDueRepayCntLast30dCntRateTPF(){
        $begin_time = $this->data->infoOrder->order_time - 30 * 86400;
        $pan_code = $this->data->infoUser->pan_code;
        $count = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName().' as u', 'u.user_id=r.user_id and u.order_id=r.order_id and u.app_name=r.app_name')
            ->where([
                'u.pan_code' => $pan_code
            ])
            ->andWhere(['>=', 'r.loan_time', $begin_time])
            ->andWhere(['<', 'r.loan_time', $this->data->infoOrder->order_time])
            ->count();

        if(empty($count)){
            return -9999;
        }

        $start_time = $this->data->infoOrder->order_time - 15 * 86400;
        $count_repay = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName().' as u', 'u.user_id=r.user_id and u.order_id=r.order_id and u.app_name=r.app_name')
            ->where([
                'u.pan_code' => $pan_code,
                'r.status' => InfoRepayment::STATUS_CLOSED,
                'r.is_overdue' => InfoRepayment::OVERDUE_YES
            ])
            ->andWhere(['>=', 'r.closing_time', $start_time])
            ->andWhere(['<', 'r.closing_time', $this->data->infoOrder->order_time])
            ->count();

        return round($count_repay / $count * 100, 2);
    }

    /**
     * 老用户-总平台历史逾期4天及以上还款的次数占历史逾期还款次数的比例
     * @return int
     */
    public function checkHisDue4RepayCntHisDueCntRateTPF(){
        $pan_code = $this->data->infoUser->pan_code;
        $count_all = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName().' as u', 'u.user_id=r.user_id and u.order_id=r.order_id and u.app_name=r.app_name')
            ->where([
                'u.pan_code' => $pan_code,
                'r.status' => InfoRepayment::STATUS_CLOSED,
                'r.is_overdue' => InfoRepayment::OVERDUE_YES
            ])
            ->count();

        if(empty($count_all)){
            return -9999;
        }

        $count = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName().' as u', 'u.user_id=r.user_id and u.order_id=r.order_id and u.app_name=r.app_name')
            ->where([
                'u.pan_code' => $pan_code,
                'r.status' => InfoRepayment::STATUS_CLOSED,
            ])
            ->andWhere(['>=', 'r.overdue_day', 4])
            ->count();

        return round($count / $count_all * 100, 2);
    }

    /**
     * 老用户-总平台近30天正常还款的订单数占历史放款订单数的比例
     * @return int
     */
    public function checkLast30dTiqianRepayCntHisCntRateTPF(){
        $begin_time = strtotime(date('Y-m-d', $this->data->infoOrder->order_time)) - 30 * 86400;
        $pan_code = $this->data->infoUser->pan_code;
        $count_all = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName().' as u', 'u.user_id=r.user_id and u.order_id=r.order_id and u.app_name=r.app_name')
            ->where([
                'u.pan_code' => $pan_code
            ])
            ->count();

        if(empty($count_all)){
            return -9999;
        }

        $count = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName() . ' as u', 'u.user_id=r.user_id and u.order_id=r.order_id and u.app_name=r.app_name')
            ->where([
                'u.pan_code'   => $pan_code,
                'r.is_overdue' => InfoRepayment::OVERDUE_NO,
            ])
            ->andWhere(['>=', 'r.closing_time', $begin_time])
            ->andWhere(['<', 'r.closing_time', $this->data->infoOrder->order_time])
            ->count();


        return round($count / $count_all * 100, 2);
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
     * 老用户模型分V5
     * @return int
     */
    public function checkOldUserModelScoreV5(){
        $v356 = $this->checkIsSMSRecordGrabNormal();

        $score = 0;
        if($v356 == 0){
            $score += 136;
        }else{
            $v1039 = $this->checkSumOfHistSMSEMIAmtTPF();

            switch (true){
                case $v1039 < 60000:
                    $score += 87;
                    break;
                case $v1039 < 400000:
                    $score += 95;
                    break;
                case $v1039 < 660000:
                    $score += 111;
                    break;
                case $v1039 >= 660000:
                    $score += 153;
                    break;
            }
        }

        $v690 = $this->checkLast30dApplyCntByPanInTotPlatform();
        switch (true){
            case $v690 < 2:
                $score += 80;
                break;
            case $v690 < 3:
                $score += 97;
                break;
            case $v690 < 5:
                $score += 101;
                break;
            case $v690 >= 5:
                $score += 120;
                break;
        }

        $v585 = $this->checkHisSMDeviceIDApplyRejectCntInTotPlatporm();
        switch (true){
            case $v585 < 1:
                $score += 113;
                break;
            case $v585 < 6:
                $score += 101;
                break;
            case $v585 < 10:
                $score += 72;
                break;
            case $v585 >= 10:
                $score += 26;
                break;
        }

        $v101 = $this->checkUserAge();
        switch (true){
            case $v101 < 23:
                $score += 107;
                break;
            case $v101 < 27:
                $score += 67;
                break;
            case $v101 < 31:
                $score += 94;
                break;
            case $v101 < 37:
                $score += 107;
                break;
            case $v101 >= 37:
                $score += 142;
                break;
        }

        $v715 = $this->checkLast30dRepayCntByPanTotPlatform();
        switch (true){
            case $v715 < 1:
                $score += 82;
                break;
            case $v715 < 2:
                $score += 122;
                break;
            case $v715 >= 2:
                $score += 157;
                break;
        }

        $v720 = $this->checkOldUserComplexRuleV1HisTiqianOrderCntTotPlatform();
        switch (true){
            case $v720 < 2:
                $score += 28;
                break;
            case $v720 < 4:
                $score += 63;
                break;
            case $v720 < 14:
                $score += 114;
                break;
            case $v720 >= 14:
                $score += 150;
                break;
        }

        return $score;
    }

    /**
     * 本平台用户历史各次登录、下单时关联的不同数盟设备ID的数量
     * @return int
     */
    public function checkHistSMDeviceCntOfLoginAndOrder(){
        $loginLog = LoginLog::find()
            ->select(['szlm_query_id'])
            ->where(['app_name' => $this->data->order->app_name,
                     'user_id' => $this->data->order->user_id])
            ->andWhere(['<=', 'event_time', $this->data->infoOrder->order_time])
            ->andWhere(['is not', 'szlm_query_id', null])
            ->groupBy(['szlm_query_id'])
            ->asArray()
            ->all();

        $infoOrder = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName(). ' as d', 'd.user_id=o.user_id and d.order_id=o.order_id and d.app_name=o.app_name')
            ->select(['d.szlm_query_id'])
            ->where(['o.user_id' => $this->data->order->user_id,
                     'o.app_name' => $this->data->order->app_name])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->andWhere(['is not', 'd.szlm_query_id', null])
            ->groupBy(['d.szlm_query_id'])
            ->asArray()
            ->all();

         return count(array_unique(array_merge(
            ArrayHelper::getColumn($loginLog, 'szlm_query_id'),
            ArrayHelper::getColumn($infoOrder, 'szlm_query_id')
        )));
    }

    /**
     * 本平台用户近30天内各次登录、下单时关联的不同数盟设备ID的数量
     * @return int
     */
    public function checkSMDeviceCntOfLoginAndOrderLast30Days(){
        $begin_time = $this->data->infoOrder->order_time - 30 * 86400;

        $loginLog = LoginLog::find()
            ->select(['szlm_query_id'])
            ->where(['app_name' => $this->data->order->app_name,
                     'user_id' => $this->data->order->user_id])
            ->andWhere(['>=', 'event_time', $begin_time])
            ->andWhere(['<=', 'event_time', $this->data->infoOrder->order_time])
            ->andWhere(['is not', 'szlm_query_id', null])
            ->groupBy(['szlm_query_id'])
            ->asArray()
            ->all();

        $infoOrder = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName(). ' as d', 'd.user_id=o.user_id and d.order_id=o.order_id and d.app_name=o.app_name')
            ->select(['d.szlm_query_id'])
            ->where(['o.user_id' => $this->data->order->user_id,
                     'o.app_name' => $this->data->order->app_name])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->andWhere(['is not', 'd.szlm_query_id', null])
            ->groupBy(['d.szlm_query_id'])
            ->asArray()
            ->all();

        return count(array_unique(array_merge(
            ArrayHelper::getColumn($loginLog, 'szlm_query_id'),
            ArrayHelper::getColumn($infoOrder, 'szlm_query_id')
        )));
    }

    /**
     * 本平台用户近60天内各次登录、下单时关联的不同数盟设备ID的数量
     * @return int
     */
    public function checkSMDeviceCntOfLoginAndOrderLast60Days(){
        $begin_time = $this->data->infoOrder->order_time - 60 * 86400;

        $loginLog = LoginLog::find()
            ->select(['szlm_query_id'])
            ->where(['app_name' => $this->data->order->app_name,
                     'user_id' => $this->data->order->user_id])
            ->andWhere(['>=', 'event_time', $begin_time])
            ->andWhere(['<=', 'event_time', $this->data->infoOrder->order_time])
            ->andWhere(['is not', 'szlm_query_id', null])
            ->groupBy(['szlm_query_id'])
            ->asArray()
            ->all();

        $infoOrder = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName(). ' as d', 'd.user_id=o.user_id and d.order_id=o.order_id and d.app_name=o.app_name')
            ->select(['d.szlm_query_id'])
            ->where(['o.user_id' => $this->data->order->user_id,
                     'o.app_name' => $this->data->order->app_name])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->andWhere(['is not', 'd.szlm_query_id', null])
            ->groupBy(['d.szlm_query_id'])
            ->asArray()
            ->all();

        return count(array_unique(array_merge(
            ArrayHelper::getColumn($loginLog, 'szlm_query_id'),
            ArrayHelper::getColumn($infoOrder, 'szlm_query_id')
        )));
    }

    /**
     * 本平台用户近90天内各次登录、下单时关联的不同数盟设备ID的数量
     * @return int
     */
    public function checkSMDeviceCntOfLoginAndOrderLast90Days(){
        $begin_time = $this->data->infoOrder->order_time - 90 * 86400;

        $loginLog = LoginLog::find()
            ->select(['szlm_query_id'])
            ->where(['app_name' => $this->data->order->app_name,
                     'user_id' => $this->data->order->user_id])
            ->andWhere(['>=', 'event_time', $begin_time])
            ->andWhere(['<=', 'event_time', $this->data->infoOrder->order_time])
            ->andWhere(['is not', 'szlm_query_id', null])
            ->groupBy(['szlm_query_id'])
            ->asArray()
            ->all();

        $infoOrder = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName(). ' as d', 'd.user_id=o.user_id and d.order_id=o.order_id and d.app_name=o.app_name')
            ->select(['d.szlm_query_id'])
            ->where(['o.user_id' => $this->data->order->user_id,
                     'o.app_name' => $this->data->order->app_name])
            ->andWhere(['>=', 'o.order_time', $begin_time])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->andWhere(['is not', 'd.szlm_query_id', null])
            ->groupBy(['d.szlm_query_id'])
            ->asArray()
            ->all();

        return count(array_unique(array_merge(
            ArrayHelper::getColumn($loginLog, 'szlm_query_id'),
            ArrayHelper::getColumn($infoOrder, 'szlm_query_id')
        )));
    }

    /**
     * 总平台该笔订单的pan_code各次登录、下单时关联的不同数盟设备ID数量
     * @param int $before
     * @return int
     */
    protected function getSMDeviceCntOfLoginAndOrderInTPF($before=0){
        $loginLog = LoginLog::find()
            ->select(['szlm_query_id'])
            ->where(['pan_code' => $this->data->infoUser->pan_code])
            ->andWhere(['>=', 'event_time', $before])
            ->andWhere(['<=', 'event_time', $this->data->infoOrder->order_time])
            ->groupBy(['szlm_query_id'])
            ->asArray()
            ->all();

        $infoOrder = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'u.user_id=o.user_id and u.order_id=o.order_id and u.app_name=o.app_name')
            ->leftJoin(InfoDevice::tableName(). ' as d', 'd.user_id=o.user_id and d.order_id=o.order_id and d.app_name=o.app_name')
            ->select(['d.szlm_query_id'])
            ->where(['u.pan_code' => $this->data->infoUser->pan_code])
            ->andWhere(['>=', 'o.order_time', $before])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->groupBy(['d.szlm_query_id'])
            ->asArray()
            ->all();

        return count(array_unique(array_merge(
            ArrayHelper::getColumn($loginLog, 'szlm_query_id'),
            ArrayHelper::getColumn($infoOrder, 'szlm_query_id')
        )));
    }

    /**
     * 总平台该Pan卡号历史各次登录、下单时关联的不同数盟设备ID的数量
     * @return int
     */
    public function checkHistSMDeviceCntOfLoginAndOrderInTPF(){
        return $this->getSMDeviceCntOfLoginAndOrderInTPF();
    }

    /**
     * 总平台该Pan卡号近30天内各次登录、下单时关联的不同数盟设备ID的数量
     * @return int
     */
    public function checkSMDeviceCntOfLoginAndOrderInTPFLast30Days(){
        $begin_time = $this->data->infoOrder->order_time - 30 * 86400;
        return $this->getSMDeviceCntOfLoginAndOrderInTPF($begin_time);
    }

    /**
     * 总平台该Pan卡号近60天内各次登录、下单时关联的不同数盟设备ID的数量
     * @return int
     */
    public function checkSMDeviceCntOfLoginAndOrderInTPFLast60Days(){
        $begin_time = $this->data->infoOrder->order_time - 60 * 86400;
        return $this->getSMDeviceCntOfLoginAndOrderInTPF($begin_time);
    }

    /**
     * 总平台该Pan卡号近90天内各次登录、下单时关联的不同数盟设备ID的数量
     * @return int
     */
    public function checkSMDeviceCntOfLoginAndOrderInTPFLast90Days(){
        $begin_time = $this->data->infoOrder->order_time - 90 * 86400;
        return $this->getSMDeviceCntOfLoginAndOrderInTPF($begin_time);
    }

    /**
     * 该笔订单的手机号各次登录、下单时关联的不同数盟设备ID数量
     * @param int $before
     * @return int
     */
    protected function getPhoneMatchSMDIDCntInLoginAndOrder($before=0){
        $loginLog = LoginLog::find()
            ->select(['szlm_query_id'])
            ->where(['phone' => $this->data->infoUser->phone])
            ->andWhere(['>=', 'event_time', $before])
            ->andWhere(['<=', 'event_time', $this->data->infoOrder->order_time])
            ->groupBy(['szlm_query_id'])
            ->asArray()
            ->all();

        $infoOrder = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'u.user_id=o.user_id and u.order_id=o.order_id and u.app_name=o.app_name')
            ->leftJoin(InfoDevice::tableName(). ' as d', 'd.user_id=o.user_id and d.order_id=o.order_id and d.app_name=o.app_name')
            ->select(['d.szlm_query_id'])
            ->where(['u.phone' => $this->data->infoUser->phone])
            ->andWhere(['>=', 'o.order_time', $before])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->groupBy(['d.szlm_query_id'])
            ->asArray()
            ->all();

        return count(array_unique(array_merge(
            ArrayHelper::getColumn($loginLog, 'szlm_query_id'),
            ArrayHelper::getColumn($infoOrder, 'szlm_query_id')
        )));
    }

    /**
     * 该笔订单的数盟设备id各次登录、下单时关联的不同身份证数量
     * @param int $before
     * @return int
     */
    protected function getSMDIDMatchIDCardCntInLoginAndOrderTPF($before=0){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }
        $loginLog = LoginLog::find()
            ->select(['pan_code'])
            ->where(['szlm_query_id' => $this->data->infoDevice->szlm_query_id])
            ->andWhere(['>=', 'event_time', $before])
            ->andWhere(['<=', 'event_time', $this->data->infoOrder->order_time])
            ->groupBy(['pan_code'])
            ->asArray()
            ->all();

        $infoOrder = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'u.user_id=o.user_id and u.order_id=o.order_id and u.app_name=o.app_name')
            ->leftJoin(InfoDevice::tableName(). ' as d', 'd.user_id=o.user_id and d.order_id=o.order_id and d.app_name=o.app_name')
            ->select(['u.pan_code'])
            ->where(['d.szlm_query_id' => $this->data->infoDevice->szlm_query_id])
            ->andWhere(['>=', 'o.order_time', $before])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->groupBy(['d.pan_code'])
            ->asArray()
            ->all();

        return count(array_unique(array_merge(
            ArrayHelper::getColumn($loginLog, 'pan_code'),
            ArrayHelper::getColumn($infoOrder, 'pan_code')
        )));
    }

    /**
     * 该笔订单的数盟设备id各次登录、下单时关联的不同手机号数量
     * @param int $before
     * @return int
     */
    protected function getSMDIDMatchPhoneNumberCntInLoginAndOrderTPF($before=0){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }
        $loginLog = LoginLog::find()
            ->select(['phone'])
            ->where(['szlm_query_id' => $this->data->infoDevice->szlm_query_id])
            ->andWhere(['>=', 'event_time', $before])
            ->andWhere(['<=', 'event_time', $this->data->infoOrder->order_time])
            ->groupBy(['phone'])
            ->asArray()
            ->all();

        $infoOrder = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'u.user_id=o.user_id and u.order_id=o.order_id and u.app_name=o.app_name')
            ->leftJoin(InfoDevice::tableName(). ' as d', 'd.user_id=o.user_id and d.order_id=o.order_id and d.app_name=o.app_name')
            ->select(['u.phone'])
            ->where(['d.szlm_query_id' => $this->data->infoDevice->szlm_query_id])
            ->andWhere(['>=', 'o.order_time', $before])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->groupBy(['d.phone'])
            ->asArray()
            ->all();

        return count(array_unique(array_merge(
            ArrayHelper::getColumn($loginLog, 'phone'),
            ArrayHelper::getColumn($infoOrder, 'phone')
        )));
    }

    /**
     * 该笔订单的数盟设备id各次登录、下单时关联的不同身份证数量
     * @param int $before
     * @return int
     */
    protected function getSMDIDMatchIDCardCntInLoginAndOrderTP($before=0){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -1;
        }
        if($this->data->infoOrder->is_external == 'y'){
            $app_name = $this->data->infoOrder->external_app_name;
        }else{
            $app_name = $this->data->order->app_name;
        }
        $loginLog = LoginLog::find()
            ->select(['pan_code'])
            ->where(['szlm_query_id' => $this->data->infoDevice->szlm_query_id, 'app_name' => $app_name])
            ->andWhere(['>=', 'event_time', $before])
            ->andWhere(['<=', 'event_time', $this->data->infoOrder->order_time])
            ->groupBy(['pan_code'])
            ->asArray()
            ->all();

        $infoOrder = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'u.user_id=o.user_id and u.order_id=o.order_id and u.app_name=o.app_name')
            ->leftJoin(InfoDevice::tableName(). ' as d', 'd.user_id=o.user_id and d.order_id=o.order_id and d.app_name=o.app_name')
            ->select(['u.pan_code', 'o.external_app_name', 'o.app_name', 'o.is_external'])
            ->where(['d.szlm_query_id' => $this->data->infoDevice->szlm_query_id])
            ->andWhere(['>=', 'o.order_time', $before])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->asArray()
            ->all();

        $data = [];
        foreach ($infoOrder as $v){
            if($v['is_external'] == 'y'){
                $data[$v['external_app_name']][] = $v['pan_code'];
            }else{
                $data[$v['app_name']][] = $v['pan_code'];
            }
        }

        return count(array_unique(array_merge(
            ArrayHelper::getColumn($loginLog, 'pan_code'),
            $data[$app_name]
        )));
    }

    /**
     * 该笔订单的身份证号码各次登录、下单时关联的不同数盟设备ID数量
     * @param int $before
     * @return int
     */
    protected function getIDCardMatchSMDIDCntInLoginAndOrderTP($before=0){
        if($this->data->infoOrder->is_external == 'y'){
            $app_name = $this->data->infoOrder->external_app_name;
        }else{
            $app_name = $this->data->order->app_name;
        }
        $loginLog = LoginLog::find()
            ->select(['szlm_query_id'])
            ->where(['pan_code' => $this->data->infoUser->pan_code, 'app_name' => $app_name])
            ->andWhere(['>=', 'event_time', $before])
            ->andWhere(['<=', 'event_time', $this->data->infoOrder->order_time])
            ->groupBy(['szlm_query_id'])
            ->asArray()
            ->all();

        $infoOrder = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'u.user_id=o.user_id and u.order_id=o.order_id and u.app_name=o.app_name')
            ->leftJoin(InfoDevice::tableName(). ' as d', 'd.user_id=o.user_id and d.order_id=o.order_id and d.app_name=o.app_name')
            ->select(['d.szlm_query_id', 'o.is_external', 'o.external_app_name', 'o.app_name'])
            ->where(['u.pan_code' => $this->data->infoUser->pan_code])
            ->andWhere(['>=', 'o.order_time', $before])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->asArray()
            ->all();

        $data = [];
        foreach ($infoOrder as $v){
            if($v['is_external'] == 'y'){
                $data[$v['external_app_name']][] = $v['szlm_query_id'];
            }else{
                $data[$v['app_name']][] = $v['szlm_query_id'];
            }
        }

        return count(array_unique(array_merge(
            ArrayHelper::getColumn($loginLog, 'szlm_query_id'),
            $data[$app_name]
        )));
    }

    /**
     * 跑此笔订单风控时，该Pan卡号2021年2月25日及以后在本产品的还款订单数加1（用来表示此笔订单是第几笔订单）
     * @return int
     */
    public function checkHisSuccessClosingOrderCntPost20210225PlusOneSPF(){
        $begin_time = strtotime('2021-02-25');

        $count = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoOrder::tableName().' as o', 'o.order_id=r.order_id and o.user_id=r.user_id and o.app_name=r.app_name')
            ->where(['r.user_id' => $this->data->infoOrder->user_id,
                     'o.product_id' => $this->data->infoOrder->product_id,
                     'r.app_name' => $this->data->order->app_name])
            ->andWhere(['>=', 'r.closing_time', $begin_time])
            ->andWhere(['<=', 'r.closing_time', $this->data->infoOrder->order_time])
            ->count();

        return $count + 1;
    }

    /**
     * 跑此笔订单风控时，该Pan卡号2021年2月25日及以后在总平台的还款订单数加1（用来表示此笔订单是第几笔订单）
     * @return int
     */
    public function checkHisSuccessClosingOrderCntPost20210225PlusOneTPF(){
        $begin_time = strtotime('2021-02-25');

        $count = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName().' as u', 'u.order_id=r.order_id and u.user_id=r.user_id and u.app_name=r.app_name')
            ->where(['u.pan_code' => $this->data->infoUser->pan_code])
            ->andWhere(['>=', 'r.closing_time', $begin_time])
            ->andWhere(['<=', 'r.closing_time', $this->data->infoOrder->order_time])
            ->count();

        return $count + 1;
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
        $data = $this->getUserSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(7, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(60, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(180, $this->data->infoOrder->order_time);

        $count = 0;
        foreach ($data as $v){
            if($this->smsSalary($v)){
                $count++;
            }
        }

        return $count;
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
        $data = $this->getUserSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(180, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(180, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(180, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(180, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(30, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(90, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(180, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(180, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(180, $this->data->infoOrder->order_time);

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
        $data = $this->getUserSmsByDay(180, $this->data->infoOrder->order_time);

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
     * 最近30天最近一次成功调用的征信报告
     * @return int
     */
    public function checkIsLast30dCreditReportReturned()
    {
        $time = $this->data->infoOrder->order_time - 30 * 86400;
        $cibilReport = UserCreditReportCibil::find()
            ->select(['id','query_time'])
            ->where(['pan_code' => $this->data->infoUser->pan_code, 'status' => UserCreditReportCibil::STATUS_SUCCESS])
            ->andWhere(['>=', 'query_time', $time])
            ->orderBy(['query_time' => SORT_DESC])
            ->one();

        $experianReport = UserCreditReportExperian::find()
            ->select(['id','query_time'])
            ->where(['pan_code' => $this->data->infoUser->pan_code, 'status' => UserCreditReportExperian::STATUS_SUCCESS])
            ->andWhere(['>=', 'query_time', $time])
            ->orderBy(['query_time' => SORT_DESC])
            ->one();

        $bangaloreExperianReport = UserCreditReportBangaloreExperian::find()
            ->select(['id','query_time'])
            ->where(['pan_code' => $this->data->infoUser->pan_code, 'status' => UserCreditReportBangaloreExperian::STATUS_SUCCESS])
            ->andWhere(['>=', 'query_time', $time])
            ->orderBy(['query_time' => SORT_DESC])
            ->one();

        $mobiExperianReport = UserCreditReportMobiExperian::find()
            ->select(['id','query_time'])
            ->where(['pan_code' => $this->data->infoUser->pan_code, 'status' => UserCreditReportMobiExperian::STATUS_SUCCESS])
            ->andWhere(['>=', 'query_time', $time])
            ->orderBy(['query_time' => SORT_DESC])
            ->one();

        $shanyunExperianReport = UserCreditReportShanyunExperian::find()
            ->select(['id','query_time'])
            ->where(['pan_code' => $this->data->infoUser->pan_code, 'status' => UserCreditReportShanyunExperian::STATUS_SUCCESS])
            ->andWhere(['>=', 'query_time', $time])
            ->orderBy(['query_time' => SORT_DESC])
            ->one();

        if(empty($cibilReport)
            && empty($experianReport)
            && empty($bangaloreExperianReport)
            && empty($mobiExperianReport)
            && empty($shanyunExperianReport)){
            return -1;
        }

        $cibil_updated_at = $cibilReport['query_time'] ?? 0;
        $experian_updated_at = $experianReport['query_time'] ?? 0;
        $bangalore_experian_updated_at = $bangaloreExperianReport['query_time'] ?? 0;
        $mobi_experian_updated_at = $mobiExperianReport['query_time'] ?? 0;
        $shanyun_experian_updated_at = $shanyunExperianReport['query_time'] ?? 0;

        $arr = [
            0 => $experian_updated_at,
            1 => $cibil_updated_at,
            2 => $bangalore_experian_updated_at,
            3 => $mobi_experian_updated_at,
            4 => $shanyun_experian_updated_at,
        ];

        return array_search(max($arr), $arr);
    }

    /**
     * 本次订单征信报告均调用失败
     * @throws \Exception
     */
    public function checkActionOfReportCall(){
        if(!$this->isGetData){
            return -1;
        }
        $report = $this->getBangaloreExperianReport();
        $experian_retry_num = $this->data->order->userCreditReportBangaloreExperian->retry_num ?? 0;
        $mobi_retry_num = $this->data->order->userCreditReportMobiExperian->retry_num ?? 0;
        $shanyun_retry_num = $this->data->order->userCreditReportShanyunExperian->retry_num ?? 0;
        $retry_num = $this->data->order->userCreditReportExperian->retry_num ?? 0;

        if(empty($report)){
            if(isset($this->data->order->userCreditReportShanyunExperian->status)
                && $this->data->order->userCreditReportShanyunExperian->status == 1
            ){
                return -1;
            }

            if($experian_retry_num < 3 || $retry_num < 3 || $mobi_retry_num < 3 || $shanyun_retry_num < 3){
                throw new Exception('征信报告调用失败，等待重试', 1001);
            }
        }

        return -1;
    }

    /**
     * 调用BangaloreExperian报告
     * @return array
     * @throws \Exception
     */
    protected function getBangaloreExperianRequest(){
        if($this->isGetData){
            $params = [
                'infoUser' => $this->data->infoUser,
                'order' => $this->data->order,
                'retryLimit' => 3,
            ];

            $service = new CreditReportMobiExperianService($params);
            if(!$service->getData()) {
                $service = new CreditReportShanyunExperianService($params);
                if(!$service->getData()){
                    $service = new CreditReportExperianService($params);
                    if (!$service->getData()) {
                        $service = new CreditReportBangaloreExperianService($params);
                        $service->getData();
                    }
                }
            }
        }

        return $this->getBangaloreExperianReport();
    }

    /**
     * 获取BangaloreExperian征信报告
     * @return array
     * @throws \Exception
     */
    protected function getBangaloreExperianReport()
    {
        if (is_null($this->bangaloreExperianReport)) {
            $this->data->order = RiskOrder::findOne($this->data->order->id);
            $xml_result = $this->data->order->userCreditReportShanyunExperian->data ?? [];
            $query_time = $this->data->order->userCreditReportShanyunExperian->query_time ?? 0;

            if(!empty($xml_result)){
                $this->bangaloreExperianReport = json_decode($xml_result, true);
                $this->bangalore_experian_updated_at = $query_time;

                return $this->bangaloreExperianReport;
            }

            $xml_result = $this->data->order->userCreditReportBangaloreExperian->data ?? [];
            $query_time = $this->data->order->userCreditReportBangaloreExperian->query_time ?? 0;
            if(empty($xml_result)){
                $xml_result = $this->data->order->userCreditReportExperian->data ?? [];
                $query_time = $this->data->order->userCreditReportExperian->query_time ?? 0;
                if(empty($xml_result)){
                    $xml_result = $this->data->order->userCreditReportMobiExperian->data ?? [];
                    $query_time = $this->data->order->userCreditReportMobiExperian->query_time ?? 0;
                }
            }

            if(empty($xml_result)){
                return $this->bangaloreExperianReport = [];
            }

            try {
                $result = simplexml_load_string($xml_result);
                $result = $result->children('http://schemas.xmlsoap.org/soap/envelope/')->Body;
                $result = $result->children('urn:cbv2');
                $result = json_decode(json_encode($result), true);
                $result = json_decode(json_encode(simplexml_load_string($result['processResponse']['out'])), true);

                $this->bangaloreExperianReport = $result;
                $this->bangalore_experian_updated_at = $query_time;
            } catch (\Exception $e){
                \Yii::error(['risk_order_id'=>$this->data->order->id,'err_msg'=>$e->getMessage(),'trace'=>$e->getTraceAsString()], 'RiskAutoCheck');
                $this->bangaloreExperianReport = [];
            }
            return $this->bangaloreExperianReport;
        } else {
            return $this->bangaloreExperianReport;
        }
    }

    /**
     * BangaloreExperian征信报告返回是否正常
     * @return int
     * @throws \Exception
     */
    public function checkIsBangaloreExperianCreditReportReturnedNormal(){
        $report = $this->getBangaloreExperianRequest();

        if(isset($report['Header']['SystemCode']) && $report['Header']['SystemCode'] == 0){
            if(!empty($report['UserMessage']['UserMessageText']) && $report['UserMessage']['UserMessageText'] == 'Normal Response'){
                return 1;
            }
            return 0;
        }

        return -1;
    }

    /**
     * BangaloreExperian中的账户是否有命中负面信息的状态
     * @return int
     * @throws \Exception
     */
    public function checkBangaloreExperianAccountStatusCntOfClosed(){
        $report = $this->getBangaloreExperianReport();

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
     * BangloreExperian中的账户是否有命中负面信息的状态
     * @return int
     * @throws \Exception
     */
    public function checkBangaloreNegativeExperianAccountStatus(){
        $report = $this->getBangaloreExperianReport();

        if(empty($report) || empty($report['CAIS_Account']['CAIS_Account_DETAILS'])){
            return -1;
        }

        foreach ($report['CAIS_Account']['CAIS_Account_DETAILS'] as $v){
            if(!empty($v['Account_Status']) && in_array($v['Account_Status'], [93,97,53,54,55,56,57,58,59,60,61,62,63,79,81,85,86,87,88,94,90,91])){
                return 1;
            }
        }

        return 0;
    }

    /**
     * BangaloreExperian信贷账户总数
     * @return int
     * @throws \Exception
     */
    public function checkBangaloreExperianCreditAccountTotal(){
        $report = $this->getBangaloreExperianReport();

        if(empty($report) || empty($report['CAIS_Account']['CAIS_Summary']['Credit_Account']['CreditAccountTotal'])){
            return -1;
        }

        return intval($report['CAIS_Account']['CAIS_Summary']['Credit_Account']['CreditAccountTotal']);
    }

    /**
     * BangaloreExperian信贷账户Active数
     * @return int
     * @throws \Exception
     */
    public function checkBangaloreExperianCreditAccountActive(){
        $report = $this->getBangaloreExperianReport();

        if(empty($report) || empty($report['CAIS_Account']['CAIS_Summary']['Credit_Account']['CreditAccountActive'])){
            return -1;
        }

        return intval($report['CAIS_Account']['CAIS_Summary']['Credit_Account']['CreditAccountActive']);
    }

    /**
     * BangaloreExperian信贷账户Closed数
     * @return int
     * @throws \Exception
     */
    public function checkBangaloreExperianCreditAccountClosed(){
        $report = $this->getBangaloreExperianReport();

        if(empty($report) || empty($report['CAIS_Account']['CAIS_Summary']['Credit_Account']['CreditAccountClosed'])){
            return -1;
        }

        return intval($report['CAIS_Account']['CAIS_Summary']['Credit_Account']['CreditAccountClosed']);
    }

    /**
     * BangaloreExperian抵押贷的待还余额
     * @return int
     * @throws \Exception
     */
    public function checkBangaloreExperianOutstandingBalanceSecured(){
        $report = $this->getBangaloreExperianReport();

        if(empty($report) || empty($report['CAIS_Account']['CAIS_Summary']['Total_Outstanding_Balance']['Outstanding_Balance_Secured'])){
            return -1;
        }

        return intval($report['CAIS_Account']['CAIS_Summary']['Total_Outstanding_Balance']['Outstanding_Balance_Secured']);
    }

    /**
     * BangaloreExperian抵押贷的待还余额的占比
     * @return int
     * @throws \Exception
     */
    public function checkBangaloreExperianOutstandingBalanceSecuredPercentage(){
        $report = $this->getBangaloreExperianReport();

        if(empty($report) || empty($report['CAIS_Account']['CAIS_Summary']['Total_Outstanding_Balance']['Outstanding_Balance_Secured_Percentage'])){
            return -1;
        }

        return intval($report['CAIS_Account']['CAIS_Summary']['Total_Outstanding_Balance']['Outstanding_Balance_Secured_Percentage']);
    }

    /**
     * BangaloreExperian非抵押贷的待还余额
     * @return int
     * @throws \Exception
     */
    public function checkBangaloreExperianOutstandingBalanceUnSecured(){
        $report = $this->getBangaloreExperianReport();

        if(empty($report) || empty($report['CAIS_Account']['CAIS_Summary']['Total_Outstanding_Balance']['Outstanding_Balance_UnSecured'])){
            return -1;
        }

        return intval($report['CAIS_Account']['CAIS_Summary']['Total_Outstanding_Balance']['Outstanding_Balance_UnSecured']);
    }

    /**
     * BangaloreExperian非抵押贷的待还余额的占比
     * @return int
     * @throws \Exception
     */
    public function checkBangaloreExperianOutstandingBalanceUnSecuredPercentage(){
        $report = $this->getBangaloreExperianReport();

        if(empty($report) || empty($report['CAIS_Account']['CAIS_Summary']['Total_Outstanding_Balance']['Outstanding_Balance_UnSecured_Percentage'])){
            return -1;
        }

        return intval($report['CAIS_Account']['CAIS_Summary']['Total_Outstanding_Balance']['Outstanding_Balance_UnSecured_Percentage']);
    }

    /**
     * BangaloreExperian总待还余额
     * @return int
     * @throws \Exception
     */
    public function checkBangaloreExperianOutstandingBalanceAll(){
        $report = $this->getBangaloreExperianReport();

        if(empty($report) || empty($report['CAIS_Account']['CAIS_Summary']['Total_Outstanding_Balance']['Outstanding_Balance_All'])){
            return -1;
        }

        return intval($report['CAIS_Account']['CAIS_Summary']['Total_Outstanding_Balance']['Outstanding_Balance_All']);
    }

    /**
     * BangaloreExperian近180天被查询征信的次数
     * @return int
     * @throws \Exception
     */
    public function checkBangaloreExperianLast180dEnquiryCnt(){
        $report = $this->getBangaloreExperianReport();

        if(empty($report) || empty($report['CAPS']['CAPS_Summary']['CAPSLast180Days'])){
            return -1;
        }

        return intval($report['CAPS']['CAPS_Summary']['CAPSLast180Days']);
    }

    /**
     * BangaloreExperian近90天被查询征信的次数
     * @return int
     * @throws \Exception
     */
    public function checkBangaloreExperianLast90dEnquiryCnt(){
        $report = $this->getBangaloreExperianReport();

        if(empty($report) || empty($report['CAPS']['CAPS_Summary']['CAPSLast90Days'])){
            return -1;
        }

        return intval($report['CAPS']['CAPS_Summary']['CAPSLast90Days']);
    }

    /**
     * BangaloreExperian近30天被查询征信的次数
     * @return int
     * @throws \Exception
     */
    public function checkBangaloreExperianLast30dEnquiryCnt(){
        $report = $this->getBangaloreExperianReport();

        if(empty($report) || empty($report['CAPS']['CAPS_Summary']['CAPSLast30Days'])){
            return -1;
        }

        return intval($report['CAPS']['CAPS_Summary']['CAPSLast30Days']);
    }

    /**
     * BangaloreExperian近7天被查询征信的次数
     * @return int
     * @throws \Exception
     */
    public function checkBangaloreExperianLast7dEnquiryCnt(){
        $report = $this->getBangaloreExperianReport();

        if(empty($report) || empty($report['CAPS']['CAPS_Summary']['CAPSLast7Days'])){
            return -1;
        }

        return intval($report['CAPS']['CAPS_Summary']['CAPSLast7Days']);
    }

    /**
     * BangaloreExperianNonCredit近180天被查询征信的次数
     * @return int
     * @throws \Exception
     */
    public function checkBangaloreExperianNonCreditLast180dEnquiryCnt(){
        $report = $this->getBangaloreExperianReport();

        if(empty($report) || empty($report['NonCreditCAPS']['NonCreditCAPS_Summary']['NonCreditCAPSLast180Days'])){
            return -1;
        }

        return intval($report['NonCreditCAPS']['NonCreditCAPS_Summary']['NonCreditCAPSLast180Days']);
    }

    /**
     * BangaloreExperianNonCredit近90天被查询征信的次数
     * @return int
     * @throws \Exception
     */
    public function checkBangaloreExperianNonCreditLast90dEnquiryCnt(){
        $report = $this->getBangaloreExperianReport();

        if(empty($report) || empty($report['NonCreditCAPS']['NonCreditCAPS_Summary']['NonCreditCAPSLast90Days'])){
            return -1;
        }

        return intval($report['NonCreditCAPS']['NonCreditCAPS_Summary']['NonCreditCAPSLast90Days']);
    }

    /**
     * BangaloreExperianNonCredit近30天被查询征信的次数
     * @return int
     * @throws \Exception
     */
    public function checkBangaloreExperianNonCreditLast30dEnquiryCnt(){
        $report = $this->getBangaloreExperianReport();

        if(empty($report) || empty($report['NonCreditCAPS']['NonCreditCAPS_Summary']['NonCreditCAPSLast30Days'])){
            return -1;
        }

        return intval($report['NonCreditCAPS']['NonCreditCAPS_Summary']['NonCreditCAPSLast30Days']);
    }

    /**
     * BangaloreExperianNonCredit近7天被查询征信的次数
     * @return int
     * @throws \Exception
     */
    public function checkBangaloreExperianNonCreditLast7dEnquiryCnt(){
        $report = $this->getBangaloreExperianReport();

        if(empty($report) || empty($report['NonCreditCAPS']['NonCreditCAPS_Summary']['NonCreditCAPSLast7Days'])){
            return -1;
        }

        return intval($report['NonCreditCAPS']['NonCreditCAPS_Summary']['NonCreditCAPSLast7Days']);
    }

    /**
     * BangaloreExperian历史最大授信金额
     * @return int
     * @throws \Exception
     */
    public function checkBangaloreExperianHisMaxCreditAmt(){
        $report = $this->getBangaloreExperianReport();

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
     * BangaloreExperian最近6个月的最大授信金额
     * @return int
     * @throws \Exception
     */
    public function checkBangaloreExperianLast6mMaxCreditAmt(){
        $report = $this->getBangaloreExperianReport();

        if(empty($report) || empty($report['CAIS_Account']['CAIS_Account_DETAILS'])){
            return -1;
        }

        $time = strtotime("-6 month", $this->bangalore_experian_updated_at);

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
     * BangaloreExperian最近1个月的最大授信金额
     * @return int
     * @throws \Exception
     */
    public function checkBangaloreExperianLast1mMaxCreditAmt(){
        $report = $this->getBangaloreExperianReport();

        if(empty($report) || empty($report['CAIS_Account']['CAIS_Account_DETAILS'])){
            return -1;
        }

        $time = strtotime("-1 month", $this->bangalore_experian_updated_at);

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
     * BangaloreExperian历史平均授信金额
     * @return int
     * @throws \Exception
     */
    public function checkBangaloreExperianHisAvgCreditAmt(){
        $report = $this->getBangaloreExperianReport();

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
     * BangaloreExperian最近6个月的平均授信金额
     * @return int
     * @throws \Exception
     */
    public function checkBangaloreExperianLast6mAvgCreditAmt(){
        $report = $this->getBangaloreExperianReport();

        if(empty($report) || empty($report['CAIS_Account']['CAIS_Account_DETAILS'])){
            return -1;
        }

        $time = strtotime("-6 month", $this->bangalore_experian_updated_at);

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
     * BangaloreExperian最近1个月的平均授信金额
     * @return int
     * @throws \Exception
     */
    public function checkBangaloreExperianLast1mAvgCreditAmt(){
        $report = $this->getBangaloreExperianReport();

        if(empty($report) || empty($report['CAIS_Account']['CAIS_Account_DETAILS'])){
            return -1;
        }

        $time = strtotime("-1 month", $this->bangalore_experian_updated_at);

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
     * BangaloreExperian最近一次授信距今的时间
     * @return int
     * @throws \Exception
     */
    public function checkBangaloreExperianTimeOfLastCreditTimeToNow(){
        $report = $this->getBangaloreExperianReport();

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

        return intval((strtotime(date('Y-m-d', $this->bangalore_experian_updated_at)) - max($arr)) / 86400);
    }

    /**
     * BangaloreExperian首次授信距今的时间
     * @return int
     * @throws \Exception
     */
    public function checkBangaloreExperianTimeOfFirstCreditTimeToNow(){
        $report = $this->getBangaloreExperianReport();

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

        return intval((strtotime(date('Y-m-d', $this->bangalore_experian_updated_at)) - min($arr)) / 86400);
    }

    /**
     * BangaloreExperian历史逾期总金额
     * @return int
     * @throws \Exception
     */
    public function checkBangaloreExperianHisDueTotAmt(){
        $report = $this->getBangaloreExperianReport();

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
     * BangaloreExperian最近6个月的逾期总金额
     * @return int
     * @throws \Exception
     */
    public function checkBangaloreExperianLast6mDueTotAmt(){
        $report = $this->getBangaloreExperianReport();

        if(empty($report) || empty($report['CAIS_Account']['CAIS_Account_DETAILS'])){
            return -1;
        }

        $time = strtotime("-6 month", $this->bangalore_experian_updated_at);

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
     * BangaloreExperian最近1个月的逾期总金额
     * @return int
     * @throws \Exception
     */
    public function checkBangaloreExperianLast1mDueTotAmt(){
        $report = $this->getBangaloreExperianReport();

        if(empty($report) || empty($report['CAIS_Account']['CAIS_Account_DETAILS'])){
            return -1;
        }

        $time = strtotime("-1 month", $this->bangalore_experian_updated_at);

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
     * BangaloreExperian历史逾期总次数
     * @return int
     * @throws \Exception
     */
    public function checkBangaloreExperianHisDueTotCnt(){
        $report = $this->getBangaloreExperianReport();

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
     * BangaloreExperian最近6个月的逾期次数
     * @return int
     * @throws \Exception
     */
    public function checkBangaloreExperianLast6mDueTotCnt(){
        $report = $this->getBangaloreExperianReport();

        if(empty($report) || empty($report['CAIS_Account']['CAIS_Account_DETAILS'])){
            return -1;
        }

        $time = strtotime("-6 month", $this->bangalore_experian_updated_at);

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
     * BangaloreExperian最近1个月的逾期次数
     * @return int
     * @throws \Exception
     */
    public function checkBangaloreExperianLast1mDueTotCnt(){
        $report = $this->getBangaloreExperianReport();

        if(empty($report) || empty($report['CAIS_Account']['CAIS_Account_DETAILS'])){
            return -1;
        }

        $time = strtotime("-1 month", $this->bangalore_experian_updated_at);

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
     * BangaloreExperian历史最大逾期天数段
     * @return int
     * @throws \Exception
     */
    public function checkBangaloreExperianHisMaxDueDaysLevel(){
        $report = $this->getBangaloreExperianReport();

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
     * BangaloreExperian历史最大逾期天数
     * @return int
     * @throws \Exception
     */
    public function checkBangaloreExperianHisMaxDueDays(){
        $report = $this->getBangaloreExperianReport();

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
     * BangaloreExperian最近一次还款距今的时间
     * @return int
     * @throws \Exception
     */
    public function checkBangaloreExperianTimeOfLastPayMent(){
        $report = $this->getBangaloreExperianReport();

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

        return intval((strtotime(date('Y-m-d', $this->bangalore_experian_updated_at)) - max($arr)) / 86400);
    }

    /**
     * BangaloreExperian征信分
     * @return int
     * @throws \Exception
     */
    public function checkBangaloreExperianCreditScore(){
        $report = $this->getBangaloreExperianReport();

        if(empty($report) || empty($report['SCORE']['BureauScore'])){
            return -9;
        }

        return intval($report['SCORE']['BureauScore']);
    }

    /**
     * 新用户Banglore Experian模型分V3
     * @return int
     * @throws \Exception
     */
    public function checkNewUserBangloreExperianModelScoreV3(){
        $this->isGetData = false;
        $v357 = $this->checkIsBangaloreExperianCreditReportReturnedNormal();
        if($v357 != 1){
            return $v357;
        }

        $score = 0;
        $v392 = $this->checkBangaloreExperianCreditScore();
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

        $v368 = $this->checkBangaloreExperianLast90dEnquiryCnt();
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

        $v369 = $this->checkBangaloreExperianLast30dEnquiryCnt();
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

        $v391 = $this->checkBangaloreExperianTimeOfLastPayMent();
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

        $v386 = $this->checkBangaloreExperianHisDueTotCnt();
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

        $v364 = $this->checkBangaloreExperianOutstandingBalanceUnSecured();
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
     * 用户下单前授予的最高预授信额度
     * @return int
     * @throws \Exception
     */
    public function checkMaxPreCreditLine(){
        $order = InfoOrder::find()
            ->where(['user_id' => $this->data->order->user_id,
                     'product_id' => $this->data->infoOrder->product_id,
                     'app_name' => $this->data->order->app_name])
            ->andWhere(['<', 'order_id', $this->data->order->order_id])
            ->orderBy(['order_id' => SORT_DESC])
            ->one();

        if(empty($order)){
            return -1;
        }

        $result = RiskResultSnapshot::find()
            ->where(['order_id' => $order['order_id'],
                     'app_name' => $this->data->order->app_name,
                     'tree_code' => 'RepayC101'])
            ->one();
        return $result['result'] ?? -1;
    }

    /**
     * 用户下单时选择的预授信额度
     * @return int
     * @throws \Exception
     */
    public function checkSelectedPreCreditLine(){
        return intval($this->data->infoOrder->loan_amount / 100);
    }

    /**
     * 该笔订单所在用户体系下的进行中订单数量
     * @return bool|int|string|null
     */
    public function checkCountOfOrderInProgressOfThisOrderUserSystem(){
        return InfoOrder::find()
            ->where(['user_id' => $this->data->order->user_id,
                     'app_name' => $this->data->order->app_name,
                     'product_id' => $this->data->infoOrder->product_id,
                     'status' => [InfoOrder::STATUS_DEFAULT, InfoOrder::STATUS_PENDING_REPAYMENT]
            ])
            ->count();
    }

    /**
     * 产品名
     * @return string
     */
    public function checkProductName(){
        if(empty($this->data->infoOrder->product_name)){
            return -1;
        }

        return str_replace(' ', '', strtolower($this->data->infoOrder->product_name));
    }

    /**
     * 产品不含税PF%
     * @return int
     */
    public function checkTaxExclusivePFRate(){
        if(empty($this->data->infoOrder->principal)){
            return -1;
        }
        return round(($this->data->infoOrder->principal - $this->data->infoOrder->loan_amount) / 1.18 / $this->data->infoOrder->principal, 2);
    }

    /**
     * 全新本新用户模型分V4
     * @return int
     */
    public function checkQXBXUserModelV4(){
        $this->isGetData = false;
        $v1219 = $this->checkIsBangaloreExperianCreditReportReturnedNormal();

        $score = 0;
        $v103 = $this->checkHighEducationLevel();
        switch (true){
            case $v103 < 4:
                $score += 56;
                break;
            case $v103 < 5:
                $score += 90;
                break;
            case $v103 >= 5:
                $score += 103;
                break;
        }

        if($v1219 != 1){
            $score += 312;
        }else{
            $v1223 = $this->checkBangaloreExperianCreditAccountClosed();
            switch (true){
                case $v1223 < 3:
                    $score += 59;
                    break;
                case $v1223 < 12:
                    $score += 81;
                    break;
                case $v1223 < 18:
                    $score += 117;
                    break;
                case $v1223 >= 18:
                    $score += 137;
                    break;
            }

            $v1255 = $this->checkNewUserBangloreExperianModelScoreV3();
            switch (true){
                case $v1255 < 535:
                    $score += 19;
                    break;
                case $v1255 < 550:
                    $score += 59;
                    break;
                case $v1255 < 570:
                    $score += 109;
                    break;
                case $v1255 < 580:
                    $score += 148;
                    break;
                case $v1255 >= 580:
                    $score += 191;
                    break;
            }

            $v1224 = $this->checkBangaloreExperianOutstandingBalanceSecured();
            switch (true){
                case $v1224 < 30000:
                    $score += 73;
                    break;
                case $v1224 < 110000:
                    $score += 62;
                    break;
                case $v1224 >= 110000:
                    $score += 153;
                    break;
            }

            $v1232 = $this->checkBangaloreExperianLast7dEnquiryCnt();
            switch (true){
                case $v1232 < 1:
                    $score += 73;
                    break;
                case $v1232 < 2:
                    $score += 83;
                    break;
                case $v1232 >= 2:
                    $score += 101;
                    break;
            }
        }

        $v681 = $this->checkMaxDateDiffOfRegisterAndOrderByPhoneTotPlatform();
        switch (true){
            case $v681 < 5:
                $score += 81;
                break;
            case $v681 < 25:
                $score += 128;
                break;
            case $v681 < 55:
                $score += 108;
                break;
            case $v681 >= 55:
                $score += 65;
                break;
        }

        $v680 = $this->checkAccountCntOfPhoneTotPlatform();
        switch (true){
            case $v680 < 2:
                $score += 102;
                break;
            case $v681 < 3:
                $score += 82;
                break;
            case $v681 >= 3:
                $score += 30;
                break;
        }

        return $score;
    }

    /**
     * 全老本新用户模型分V4
     * @return int
     */
    public function checkQLBXUserModelV4(){
        $score = 0;
        $v710 = $this->checkDateDiffOfOrderAndLastOrderLoanByPanTotPlatform();
        switch (true){
            case $v710 < 54:
                $score += 139;
                break;
            case $v710 < 78:
                $score += 89;
                break;
            case $v710 < 98:
                $score += 44;
                break;
            case $v710 >= 98:
                $score += 34;
                break;
        }

        $v103 = $this->checkHighEducationLevel();
        switch (true){
            case $v103 < 4:
                $score += 61;
                break;
            case $v103 < 5:
                $score += 74;
                break;
            case $v103 >= 5:
                $score += 101;
                break;
        }

        $v356 = $this->checkIsSMSRecordGrabNormal();
        if($v356 == 0){
            $score += 81;
        }else{
            $v993 = $this->checkSMSCntOfLoanPayOffLast90DaysTPF();
            switch (true){
                case $v993 < 1:
                    $score += 72;
                    break;
                case $v993 < 3:
                    $score += 84;
                    break;
                case $v993 < 5:
                    $score += 97;
                    break;
                case $v993 >= 5:
                    $score += 107;
                    break;
            }
        }

        $v682 = $this->checkMinDateDiffOfRegisterAndOrderByPhoneTotPlatform();
        switch (true){
            case $v682 < 4:
                $score += 86;
                break;
            case $v682 < 10:
                $score += 108;
                break;
            case $v682 < 52:
                $score += 96;
                break;
            case $v682 >= 52:
                $score += 78;
                break;
        }

        $v949 = $this->checkDateDiffOfOrderAndLastOrderApplyByPanSelf();
        switch (true){
            case $v949 < 45:
                $score += 92;
                break;
            case $v949 < 65:
                $score += 50;
                break;
            case $v949 < 80:
                $score += 60;
                break;
            case $v949 >= 80:
                $score += 88;
                break;
        }

        $this->isGetData = false;
        $v1219 = $this->checkIsBangaloreExperianCreditReportReturnedNormal();
        if($v1219 != 1){
            $score += 146;
        }else{
            $v1244 = $this->checkBangaloreExperianTimeOfFirstCreditTimeToNow();
            switch (true){
                case $v1244 < 1500:
                    $score += 75;
                    break;
                case $v1244 < 2100:
                    $score += 119;
                    break;
                case $v1244 >= 2100:
                    $score += 137;
                    break;
            }

            $v1231 = $this->checkBangaloreExperianLast30dEnquiryCnt();
            switch (true){
                case $v1231 < 1:
                    $score += 68;
                    break;
                case $v1231 < 2:
                    $score += 85;
                    break;
                case $v1231 < 3:
                    $score += 100;
                    break;
                case $v1231 >= 3:
                    $score += 103;
                    break;
            }
        }

        return $score;
    }

    /**
     * 产品ID
     * @return string|int
     */
    public function checkProductID(){
        if(empty($this->data->infoOrder->product_id) || empty($this->data->infoOrder->product_source)){
            return -1;
        }

        return $this->data->infoOrder->product_source.'_'.$this->data->infoOrder->product_id;

    }

    /**
     * 老用户模型分v6
     * @return int
     */
    public function checkOldUserModelScoreV6(){
        $score = 0;
        $v356 = $this->checkIsSMSRecordGrabNormal();
        if($v356 == 0){
            $score += 167;
        }else{
            $v989 = $this->checkHistSMSCntOfLoanPayOffTPF();
            switch (true){
                case $v989 < 2:
                    $score += 87;
                    break;
                case $v989 < 6:
                    $score += 90;
                    break;
                case $v989 < 14:
                    $score += 96;
                    break;
                case $v989 >= 14:
                    $score += 102;
                    break;
            }

            $v976 = $this->checkSMSCntOfLoanApprovalLast30DaysTPF();
            switch (true){
                case $v976 < 2:
                    $score += 82;
                    break;
                case $v976 < 4:
                    $score += 86;
                    break;
                case $v976 < 14:
                    $score += 101;
                    break;
                case $v976 >= 14:
                    $score += 119;
                    break;
            }
        }

        $v715 = $this->checkLast30dRepayCntByPanTotPlatform();
        switch (true){
            case $v715 < 1:
                $score += 69;
                break;
            case $v715 < 2:
                $score += 90;
                break;
            case $v715 < 3:
                $score += 121;
                break;
            case $v715 >= 3:
                $score += 152;
                break;
        }

        $v722 = $this->checkOldUserComplexRuleV1HisCpDaySumTotPlatform();
        switch (true){
            case $v722 < -10:
                $score += 146;
                break;
            case $v722 < -2:
                $score += 110;
                break;
            case $v722 < 0:
                $score += 81;
                break;
            case $v722 < 5:
                $score += 66;
                break;
            case $v722 >= 5:
                $score += 37;
                break;
        }

        $v506 = $this->checkLast30ApplyCntBySMDeviceIDInThisPlat();
        switch (true){
            case $v506 < 2:
                $score += 83;
                break;
            case $v506 < 3:
                $score += 95;
                break;
            case $v506 < 4:
                $score += 102;
                break;
            case $v506 >= 4:
                $score += 117;
                break;
        }

        $v720 = $this->checkOldUserComplexRuleV1HisTiqianOrderCntTotPlatform();
        switch (true){
            case $v720 < 2:
                $score += 69;
                break;
            case $v720 < 4:
                $score += 80;
                break;
            case $v720 < 16:
                $score += 98;
                break;
            case $v720 >= 16:
                $score += 129;
                break;
        }

        $v952 = $this->checkHisRepayCntByPanSelf();
        switch (true){
            case $v952 < 3:
                $score += 84;
                break;
            case $v952 < 4:
                $score += 92;
                break;
            case $v952 < 8:
                $score += 105;
                break;
            case $v952 >= 8:
                $score += 119;
                break;
        }

        return $score;
    }

    /**
     * 获取googleMaps报告
     * @return array|ThirdDataGoogleMaps|null
     * @throws \Exception
     */
    protected function getGoogleMapsReport()
    {
        if (is_null($this->googleMapsReport)) {
            $params = [
                'infoUser' => $this->data->infoUser,
                'order' => $this->data->order,
                'retryLimit' => 1,
            ];
            $service = new GoogleMapsService($params);
            $service->getData();

            $data = ThirdDataGoogleMaps::find()
                ->where([
                    'order_id' => $this->data->order->order_id,
                    'app_name' => $this->data->order->app_name,
                    'user_id' => $this->data->order->user_id,
                ])
                ->one();
            return $this->googleMapsReport = $data;
        } else {
            return $this->googleMapsReport;
        }
    }

    /**
     * 获取催收数据
     * @return mixed
     * @throws Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getAssistData()
    {
        if (is_null($this->assistData)) {
            $params = [
                'pan_code'    => $this->data->infoUser->pan_code,
                'aadhaar_md5' => $this->data->infoUser->aadhaar_md5,
                'phone'       => $this->data->infoUser->phone,
                'order_time'  => $this->data->infoOrder->order_time,
                'device_id'   => $this->data->infoDevice->device_id
            ];
            $service = new AssistDataService();
            $result = $service->getRiskData($params);

            if(isset($result['code']) && $result['code'] == 0){
                $this->assistData = $result['data'];
            }else{
                throw new Exception('催收数据获取失败，'.$result['message']);
            }

            return $this->assistData;
        } else {
            return $this->assistData;
        }
    }

    /**
     * 获取提醒数据
     * @return mixed
     * @throws Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getRemindData()
    {
        if (is_null($this->remindData)) {
            $params = [
                'pan_code'    => $this->data->infoUser->pan_code,
                'order_time'  => $this->data->infoOrder->order_time,
            ];
            $service = new RemindDataService();
            $result = $service->getRiskData($params);

            if(isset($result['code']) && $result['code'] == 0){
                $this->remindData = $result['data'];
            }else{
                throw new Exception('提醒数据获取失败，'.$result['message']);
            }

            return $this->remindData;
        } else {
            return $this->remindData;
        }
    }

    /**
     * 获取该pan_code在全平台应还订单信息
     * @param $day
     * @return array|\yii\db\ActiveRecord[]
     */
    protected function getOrderData($day=0, $type=0)
    {
        if (isset($this->orderData[$day])) {
            return $this->orderData[$day][$type];
        } else {
            $info = InfoRepayment::find()
                ->alias('r')
                ->leftJoin(InfoUser::tableName().' as u', 'r.app_name=u.app_name and r.order_id=u.order_id and r.user_id=u.user_id')
                ->select([
                    'r.total_money',
                    'r.plan_repayment_time',
                    'r.is_overdue',
                    'r.true_total_money',
                    'r.status',
                    'r.closing_time',
                    'r.cost_fee',
                    'r.principal',
                    'r.overdue_day',
                ])
                ->where(['u.pan_code' => $this->data->infoUser->pan_code])
                ->asArray()
                ->all();

            $data = [
                0 => [
                    0 => [],//到期应还金额
                    1 => [],//入催应还金额
                    2 => [],//逾期剩余应还金额
                    3 => [],//待还款订单的剩余应还金额
                    4 => [],//逾期到现在未还的应还金额
                    5 => [],//已还订单的应还金额
                    6 => [],//入催低档息费应还金额
                    7 => [],//入催中档息费应还金额
                    8 => [],//入催高档息费应还金额
                    9 => [],//入催未还低档息费应还金额
                    10 => [],//入催未还中档息费应还金额
                    11 => [],//入催未还高档息费应还金额
                    12 => [],//低档息费待还款订单的剩余应还金额
                    13 => [],//中档息费待还款订单的剩余应还金额
                    14 => [],//高档息费待还款订单的剩余应还金额
                    15 => [],//逾期天数
                    23 => [],//订单的结清日期与应还款日期差  应还时间|结清时间    结清日期-应还款日期  未还款订单已本笔订单申请时间作为结清时间
                    24 => [],//未逾期已还订单的结清日期与应还款日期差    应还时间|结清时间     结清日期-应还款日期
                    25 => [],
                ],
                7 => [
                    0 => [],
                    1 => [],
                    4 => [],//逾期到现在未还的应还金额
                    5 => [],//已还订单的应还金额
                    6 => [],//入催低档息费应还金额
                    7 => [],//入催中档息费应还金额
                    8 => [],//入催高档息费应还金额
                    9 => [],//入催未还低档息费应还金额
                    10 => [],//入催未还中档息费应还金额
                    11 => [],//入催未还高档息费应还金额
                    21 => [],//逾期天数    应还时间|结清时间
                    22 => [],//逾期金额    应还时间|结清时间
                    23 => [],//订单的结清日期与应还款日期差  应还时间|结清时间    结清日期-应还款日期  未还款订单已本笔订单申请时间作为结清时间
                    24 => [],//未逾期已还订单的结清日期与应还款日期差    应还时间|结清时间     结清日期-应还款日期
                    25 => [],
                ],
                30 => [
                    0 => [],
                    1 => [],
                    4 => [],//逾期到现在未还的应还金额
                    5 => [],//已还订单的应还金额
                    6 => [],//入催低档息费应还金额
                    7 => [],//入催中档息费应还金额
                    8 => [],//入催高档息费应还金额
                    9 => [],//入催未还低档息费应还金额
                    10 => [],//入催未还中档息费应还金额
                    11 => [],//入催未还高档息费应还金额
                    21 => [],//逾期天数    应还时间|结清时间
                    22 => [],//逾期金额    应还时间|结清时间
                    23 => [],//订单的结清日期与应还款日期差  应还时间|结清时间    结清日期-应还款日期  未还款订单已本笔订单申请时间作为结清时间
                    24 => [],//未逾期已还订单的结清日期与应还款日期差    应还时间|结清时间     结清日期-应还款日期
                    25 => [],
                ],
                60 => [
                    0 => [],
                    21 => [],//逾期天数    应还时间|结清时间
                    22 => [],//逾期金额    应还时间|结清时间
                    23 => [],//订单的结清日期与应还款日期差  应还时间|结清时间    结清日期-应还款日期  未还款订单已本笔订单申请时间作为结清时间
                    24 => [],//未逾期已还订单的结清日期与应还款日期差    应还时间|结清时间     结清日期-应还款日期
                    25 => [],
                ],
            ];

            $end_time = strtotime(date('Y-m-d', $this->data->infoOrder->order_time));
            $begin_time7 = $end_time - 7 * 86400;
            $begin_time30 = $end_time - 30 * 86400;
            $begin_time60 = $end_time - 60 * 86400;

            $order_time = $this->data->infoOrder->order_time;
            $order_begin7 = $order_time - 86400 * 7;
            $order_begin30 = $order_time - 86400 * 30;
            $order_begin60 = $order_time - 86400 * 60;
            foreach ($info as $v){
                $pfType = 0;
                if(!empty($v['principal'])){
                    $pf = round($v['cost_fee'] / 1.18 / $v['principal'] * 100);
                    if($pf < 20) {
                        $pfType = 1;
                    }

                    if($pf >= 20 && $pf < 24){
                        $pfType = 2;
                    }

                    if($pf >= 24){
                        $pfType = 3;
                    }
                }

                if ($v['status'] == InfoRepayment::STATUS_PENDING) {
                    $data[0][3][] = $v['total_money'] - $v['true_total_money'];

                    if($pfType == 1){
                        $data[0][12][] = $v['total_money'] - $v['true_total_money'];
                    }

                    if($pfType == 2){
                        $data[0][13][] = $v['total_money'] - $v['true_total_money'];
                    }

                    if($pfType == 3){
                        $data[0][14][] = $v['total_money'] - $v['true_total_money'];
                    }
                }

                if($v['plan_repayment_time'] < $end_time) {
                    $data[0][0][] = $v['total_money'];

                    if ($v['status'] == InfoRepayment::STATUS_PENDING) {
                        $data[0][2][] = $v['total_money'] - $v['true_total_money'];
                    }

                    if($v['is_overdue'] == InfoRepayment::OVERDUE_YES){
                        $data[0][1][] = $v['total_money'];
                        $data[0][15][] = $v['overdue_day'];
                        if($pfType == 1){
                            $data[0][6][] = $v['total_money'];
                        }

                        if($pfType == 2){
                            $data[0][7][] = $v['total_money'];
                        }

                        if($pfType == 3){
                            $data[0][8][] = $v['total_money'];
                        }


                        if ($v['status'] == InfoRepayment::STATUS_PENDING) {
                            $data[0][4][] = $v['total_money'];

                            if($pfType == 1){
                                $data[0][9][] = $v['total_money'];
                            }

                            if($pfType == 2){
                                $data[0][10][] = $v['total_money'];
                            }

                            if($pfType == 3){
                                $data[0][11][] = $v['total_money'];
                            }
                        }
                    }
                }

                if($v['plan_repayment_time'] >= $begin_time7 && $v['plan_repayment_time'] < $end_time){
                    $data[7][0][] = $v['total_money'];

                    if($v['is_overdue'] == InfoRepayment::OVERDUE_YES){
                        $data[7][1][] = $v['total_money'];

                        if($pfType == 1){
                            $data[7][6][] = $v['total_money'];
                        }

                        if($pfType == 2){
                            $data[7][7][] = $v['total_money'];
                        }

                        if($pfType == 3){
                            $data[7][8][] = $v['total_money'];
                        }

                        if ($v['status'] == InfoRepayment::STATUS_PENDING) {
                            $data[7][4][] = $v['total_money'];

                            if($pfType == 1){
                                $data[7][9][] = $v['total_money'];
                            }

                            if($pfType == 2){
                                $data[7][10][] = $v['total_money'];
                            }

                            if($pfType == 3){
                                $data[7][11][] = $v['total_money'];
                            }
                        }
                    }
                }

                if($v['plan_repayment_time'] >= $begin_time30 && $v['plan_repayment_time'] < $end_time) {
                    $data[30][0][] = $v['total_money'];

                    if ($v['is_overdue'] == InfoRepayment::OVERDUE_YES) {
                        $data[30][1][] = $v['total_money'];

                        if($pfType == 1){
                            $data[30][6][] = $v['total_money'];
                        }

                        if($pfType == 2){
                            $data[30][7][] = $v['total_money'];
                        }

                        if($pfType == 3){
                            $data[30][8][] = $v['total_money'];
                        }

                        if ($v['status'] == InfoRepayment::STATUS_PENDING) {
                            $data[30][4][] = $v['total_money'];

                            if($pfType == 1){
                                $data[30][9][] = $v['total_money'];
                            }

                            if($pfType == 2){
                                $data[30][10][] = $v['total_money'];
                            }

                            if($pfType == 3){
                                $data[30][11][] = $v['total_money'];
                            }
                        }
                    }
                }

                if($v['plan_repayment_time'] >= $begin_time60 && $v['plan_repayment_time'] < $end_time) {
                    $data[60][0][] = $v['total_money'];
                }

                if(
                    ($v['plan_repayment_time'] < $end_time)
                    || ($v['closing_time'] >= 1 && $v['closing_time'] <= $order_time)
                ){
                    $data[0][25][] = $v['total_money'];

                    if($v['is_overdue'] == InfoRepayment::OVERDUE_YES){

                    }else{
                        if($v['status'] == InfoRepayment::STATUS_CLOSED){
                            $data[0][24][] = (strtotime(date('Y-m-d', $v['closing_time'])) - strtotime(date('Y-m-d', $v['plan_repayment_time']))) / 86400;
                        }
                    }

                    if ($v['status'] == InfoRepayment::STATUS_PENDING) {
                        $data[0][23][] = ($end_time - strtotime(date('Y-m-d', $v['plan_repayment_time']))) / 86400;
                    }else{
                        $data[0][23][] = (strtotime(date('Y-m-d', $v['closing_time'])) - strtotime(date('Y-m-d', $v['plan_repayment_time']))) / 86400;
                    }
                }

                if(
                    ($v['plan_repayment_time'] >= $begin_time7 && $v['plan_repayment_time'] < $end_time)
                    || ($v['closing_time'] >= $order_begin7 && $v['closing_time'] <= $order_time)
                ){
                    $data[7][25][] = $v['total_money'];

                    if($v['is_overdue'] == InfoRepayment::OVERDUE_YES){
                        $data[7][21][] = $v['overdue_day'];
                        $data[7][22][] = $v['total_money'];
                    }else{
                        if($v['status'] == InfoRepayment::STATUS_CLOSED){
                            $data[7][24][] = (strtotime(date('Y-m-d', $v['closing_time'])) - strtotime(date('Y-m-d', $v['plan_repayment_time']))) / 86400;
                        }
                    }

                    if ($v['status'] == InfoRepayment::STATUS_PENDING) {
                        $data[7][23][] = ($end_time - strtotime(date('Y-m-d', $v['plan_repayment_time']))) / 86400;
                    }else{
                        $data[7][23][] = (strtotime(date('Y-m-d', $v['closing_time'])) - strtotime(date('Y-m-d', $v['plan_repayment_time']))) / 86400;
                    }
                }

                if(
                    ($v['plan_repayment_time'] >= $begin_time30 && $v['plan_repayment_time'] < $end_time)
                    || ($v['closing_time'] >= $order_begin30 && $v['closing_time'] <= $order_time)
                ){
                    $data[30][25][] = $v['total_money'];

                    if($v['is_overdue'] == InfoRepayment::OVERDUE_YES){
                        $data[30][21][] = $v['overdue_day'];
                        $data[30][22][] = $v['total_money'];
                    }else{
                        if($v['status'] == InfoRepayment::STATUS_CLOSED){
                            $data[30][24][] = (strtotime(date('Y-m-d', $v['closing_time'])) - strtotime(date('Y-m-d', $v['plan_repayment_time']))) / 86400;
                        }
                    }

                    if ($v['status'] == InfoRepayment::STATUS_PENDING) {
                        $data[30][23][] = ($end_time - strtotime(date('Y-m-d', $v['plan_repayment_time']))) / 86400;
                    }else{
                        $data[30][23][] = (strtotime(date('Y-m-d', $v['closing_time'])) - strtotime(date('Y-m-d', $v['plan_repayment_time']))) / 86400;
                    }
                }

                if(
                    ($v['plan_repayment_time'] >= $begin_time60 && $v['plan_repayment_time'] < $end_time)
                    || ($v['closing_time'] >= $order_begin60 && $v['closing_time'] <= $order_time)
                ){
                    $data[60][25][] = $v['total_money'];

                    if($v['is_overdue'] == InfoRepayment::OVERDUE_YES){
                        $data[60][21][] = $v['overdue_day'];
                        $data[60][22][] = $v['total_money'];
                    }else{
                        if($v['status'] == InfoRepayment::STATUS_CLOSED){
                            $data[60][24][] = (strtotime(date('Y-m-d', $v['closing_time'])) - strtotime(date('Y-m-d', $v['plan_repayment_time']))) / 86400;
                        }
                    }

                    if ($v['status'] == InfoRepayment::STATUS_PENDING) {
                        $data[60][23][] = ($end_time - strtotime(date('Y-m-d', $v['plan_repayment_time']))) / 86400;
                    }else{
                        $data[60][23][] = (strtotime(date('Y-m-d', $v['closing_time'])) - strtotime(date('Y-m-d', $v['plan_repayment_time']))) / 86400;
                    }
                }

                if($v['closing_time'] >= $order_begin7 && $v['closing_time'] <= $order_time){
                    $data[7][5][] = $v['total_money'];
                }

                if($v['closing_time'] >= $order_begin30 && $v['closing_time'] <= $order_time){
                    $data[30][5][] = $v['total_money'];
                }

                if($v['closing_time'] >= 1 && $v['closing_time'] <= $order_time){
                    $data[0][5][] = $v['total_money'];
                }
            }

            $this->orderData = $data;

            return $this->orderData[$day][$type];
        }
    }

    /**
     * 获取该pan_code在本产品订单信息
     * @param int $day
     * @param int $type
     * @return array|mixed
     */
    protected function getProductOrderData($day=0, $type=0){
        if(isset($this->productOrderData[$day])){
            return $this->productOrderData[$day][$type];
        }else{
            $info = InfoRepayment::find()
                ->alias('r')
                ->leftJoin(InfoOrder::tableName().' as o', 'r.order_id=o.order_id and r.user_id=o.user_id and r.app_name=o.app_name')
                ->leftJoin(InfoUser::tableName().' as u', 'r.order_id=u.order_id and r.user_id=u.user_id and r.app_name=u.app_name')
                ->select([
                    'r.total_money',
                    'r.closing_time',
                    'r.plan_repayment_time',
                    'r.is_overdue',
                    'r.overdue_day',
                    'r.status',
                    'r.principal',
                    'r.cost_fee',
                    'r.loan_time',
                ])
                ->where([
                    'u.pan_code' => $this->data->infoUser->pan_code,
                    'o.app_name' => $this->data->infoOrder->app_name,
                    'o.product_id' => $this->data->infoOrder->product_id,
                ])
                ->asArray()
                ->all();

            $data = [
                0 => [
                    0  => [],
                    15 => [],//逾期天数
                    19 => [],
                    23 => [],//订单的结清日期与应还款日期差  应还时间|结清时间    结清日期-应还款日期  未还款订单已本笔订单申请时间作为结清时间
                    24 => [],//未逾期已还订单的结清日期与应还款日期差    应还时间|结清时间     结清日期-应还款日期
                    25 => [],
                ],
                7 => [
                    0  => [],
                    21 => [],//逾期天数    应还时间|结清时间
                    22 => [],//逾期金额    应还时间|结清时间
                    23 => [],//订单的结清日期与应还款日期差  应还时间|结清时间    结清日期-应还款日期  未还款订单已本笔订单申请时间作为结清时间
                    24 => [],//未逾期已还订单的结清日期与应还款日期差    应还时间|结清时间     结清日期-应还款日期
                    25 => [],
                ],
                30 => [
                    0  => [],
                    15 => [],//逾期天数
                    21 => [],//逾期天数    应还时间|结清时间
                    22 => [],//逾期金额    应还时间|结清时间
                    23 => [],//订单的结清日期与应还款日期差  应还时间|结清时间    结清日期-应还款日期  未还款订单已本笔订单申请时间作为结清时间
                    24 => [],//未逾期已还订单的结清日期与应还款日期差    应还时间|结清时间     结清日期-应还款日期
                    25 => [],
                ],
                60 => [
                    0  => [],
                    21 => [],//逾期天数    应还时间|结清时间
                    22 => [],//逾期金额    应还时间|结清时间
                    23 => [],//订单的结清日期与应还款日期差  应还时间|结清时间    结清日期-应还款日期  未还款订单已本笔订单申请时间作为结清时间
                    24 => [],//未逾期已还订单的结清日期与应还款日期差    应还时间|结清时间     结清日期-应还款日期
                    25 => [],
                ],
            ];

            $end_time = strtotime(date('Y-m-d', $this->data->infoOrder->order_time));
            $begin_time7 = $end_time - 7 * 86400;
            $begin_time30 = $end_time - 30 * 86400;
            $begin_time60 = $end_time - 60 * 86400;

            $order_time = $this->data->infoOrder->order_time;
            $order_begin7 = $order_time - 86400 * 7;
            $order_begin30 = $order_time - 86400 * 30;
            $order_begin60 = $order_time - 86400 * 60;

            foreach ($info as $v){
                if($v['plan_repayment_time'] < $end_time) {
                    $data[0][0][] = $v['total_money'];

                    if($v['is_overdue'] == InfoRepayment::OVERDUE_YES){
                        $data[0][15][] = $v['overdue_day'];
                    }
                }

                if($v['plan_repayment_time'] >= $begin_time7 && $v['plan_repayment_time'] < $end_time) {
                    $data[7][0][] = $v['total_money'];
                }

                if($v['plan_repayment_time'] >= $begin_time30 && $v['plan_repayment_time'] < $end_time) {
                    $data[30][0][] = $v['total_money'];

                    if($v['is_overdue'] == InfoRepayment::OVERDUE_YES){
                        $data[30][15][] = $v['overdue_day'];
                    }
                }

                if($v['plan_repayment_time'] >= $begin_time60 && $v['plan_repayment_time'] < $end_time) {
                    $data[60][0][] = $v['total_money'];
                }

                if(
                    ($v['plan_repayment_time'] < $end_time)
                    || ($v['closing_time'] >= 1 && $v['closing_time'] <= $order_time)
                ){
                    $data[0][25][] = $v['total_money'];

                    if($v['is_overdue'] == InfoRepayment::OVERDUE_YES){

                    }else{
                        if($v['status'] == InfoRepayment::STATUS_CLOSED){
                            $data[0][24][] = (strtotime(date('Y-m-d', $v['closing_time'])) - strtotime(date('Y-m-d', $v['plan_repayment_time']))) / 86400;
                        }
                    }

                    if ($v['status'] == InfoRepayment::STATUS_PENDING) {
                        $data[0][23][] = ($end_time - strtotime(date('Y-m-d', $v['plan_repayment_time']))) / 86400;
                    }else{
                        $data[0][23][] = (strtotime(date('Y-m-d', $v['closing_time'])) - strtotime(date('Y-m-d', $v['plan_repayment_time']))) / 86400;
                    }
                }

                if(
                    ($v['plan_repayment_time'] >= $begin_time7 && $v['plan_repayment_time'] < $end_time)
                    || ($v['closing_time'] >= $order_begin7 && $v['closing_time'] <= $order_time)
                ){
                    $data[7][25][] = $v['total_money'];

                    if($v['is_overdue'] == InfoRepayment::OVERDUE_YES){
                        $data[7][21][] = $v['overdue_day'];
                        $data[7][22][] = $v['total_money'];
                    }else{
                        if($v['status'] == InfoRepayment::STATUS_CLOSED){
                            $data[7][24][] = (strtotime(date('Y-m-d', $v['closing_time'])) - strtotime(date('Y-m-d', $v['plan_repayment_time']))) / 86400;
                        }
                    }

                    if ($v['status'] == InfoRepayment::STATUS_PENDING) {
                        $data[7][23][] = ($end_time - strtotime(date('Y-m-d', $v['plan_repayment_time']))) / 86400;
                    }else{
                        $data[7][23][] = (strtotime(date('Y-m-d', $v['closing_time'])) - strtotime(date('Y-m-d', $v['plan_repayment_time']))) / 86400;
                    }
                }

                if(
                    ($v['plan_repayment_time'] >= $begin_time30 && $v['plan_repayment_time'] < $end_time)
                    || ($v['closing_time'] >= $order_begin30 && $v['closing_time'] <= $order_time)
                ){
                    $data[30][25][] = $v['total_money'];

                    if($v['is_overdue'] == InfoRepayment::OVERDUE_YES){
                        $data[30][21][] = $v['overdue_day'];
                        $data[30][22][] = $v['total_money'];
                    }else{
                        if($v['status'] == InfoRepayment::STATUS_CLOSED){
                            $data[30][24][] = (strtotime(date('Y-m-d', $v['closing_time'])) - strtotime(date('Y-m-d', $v['plan_repayment_time']))) / 86400;
                        }
                    }

                    if ($v['status'] == InfoRepayment::STATUS_PENDING) {
                        $data[30][23][] = ($end_time - strtotime(date('Y-m-d', $v['plan_repayment_time']))) / 86400;
                    }else{
                        $data[30][23][] = (strtotime(date('Y-m-d', $v['closing_time'])) - strtotime(date('Y-m-d', $v['plan_repayment_time']))) / 86400;
                    }
                }

                if(
                    ($v['plan_repayment_time'] >= $begin_time60 && $v['plan_repayment_time'] < $end_time)
                    || ($v['closing_time'] >= $order_begin60 && $v['closing_time'] <= $order_time)
                ){
                    $data[60][25][] = $v['total_money'];

                    if($v['is_overdue'] == InfoRepayment::OVERDUE_YES){
                        $data[60][21][] = $v['overdue_day'];
                        $data[60][22][] = $v['total_money'];
                    }else{
                        if($v['status'] == InfoRepayment::STATUS_CLOSED){
                            $data[60][24][] = (strtotime(date('Y-m-d', $v['closing_time'])) - strtotime(date('Y-m-d', $v['plan_repayment_time']))) / 86400;
                        }
                    }

                    if ($v['status'] == InfoRepayment::STATUS_PENDING) {
                        $data[60][23][] = ($end_time - strtotime(date('Y-m-d', $v['plan_repayment_time']))) / 86400;
                    }else{
                        $data[60][23][] = (strtotime(date('Y-m-d', $v['closing_time'])) - strtotime(date('Y-m-d', $v['plan_repayment_time']))) / 86400;
                    }
                }
            }

            $this->productOrderData = $data;

            return $this->productOrderData[$day][$type];
        }
    }

    protected function getSzlmOrderData($day=0, $type=0){
        if(isset($this->szlmOrderData[$day])){
            return $this->szlmOrderData[$day][$type];
        }else{
            $order_time = $this->data->infoOrder->order_time;
            $time = strtotime(date('Y-m-d', $order_time));
            $begin_time7 = $time - 86400 * 7;
            $begin_time30 = $time - 86400 * 30;
            $begin_time60 = $time - 86400 * 60;

            $order_begin7 = $order_time - 86400 * 7;
            $order_begin30 = $order_time - 86400 * 30;
            $order_begin60 = $order_time - 86400 * 60;
            $item = InfoRepayment::find()
                ->alias('r')
                ->leftJoin(InfoDevice::tableName().' as d', 'r.user_id=d.user_id and r.order_id=d.order_id and r.app_name=d.app_name')
                ->select(['r.overdue_day', 'r.is_overdue', 'r.total_money', 'r.plan_repayment_time', 'r.closing_time'])
                ->where([
                    'd.szlm_query_id' => $this->data->infoDevice->szlm_query_id,
                ])
                ->asArray()
                ->all();
            $data = [
                0 => [
                    0 => [],
                    1 => [],
                    4 => [],
                ],
                7 => [
                    0 => [],
                    1 => [],
                    2 => [],
                    3 => [],
                    4 => [],
                    5 => [],
                ],
                30 => [
                    0 => [],
                    1 => [],
                    2 => [],
                    3 => [],
                    4 => [],
                    5 => [],
                ],
                60 => [
                    0 => [],
                    1 => [],
                    2 => [],
                    3 => [],
                    4 => [],
                    5 => [],
                ],
            ];
            foreach ($item as $v){
                if($v['plan_repayment_time'] < $time){
                    $data[0][4][] = $v['total_money'];

                    if($v['is_overdue'] == InfoRepayment::OVERDUE_YES){
                        $data[0][0][] = $v['overdue_day'];
                        $data[0][1][] = $v['total_money'];
                    }
                }

                if($v['plan_repayment_time'] >= $begin_time7 && $v['plan_repayment_time'] < $time){
                    $data[7][4][] = $v['total_money'];

                    if($v['is_overdue'] == InfoRepayment::OVERDUE_YES){
                        $data[7][0][] = $v['overdue_day'];
                        $data[7][1][] = $v['total_money'];
                    }
                }

                if($v['plan_repayment_time'] >= $begin_time30 && $v['plan_repayment_time'] < $time){
                    $data[30][4][] = $v['total_money'];

                    if($v['is_overdue'] == InfoRepayment::OVERDUE_YES){
                        $data[30][0][] = $v['overdue_day'];
                        $data[30][1][] = $v['total_money'];
                    }
                }

                if($v['plan_repayment_time'] >= $begin_time60 && $v['plan_repayment_time'] < $time){
                    $data[60][4][] = $v['total_money'];

                    if($v['is_overdue'] == InfoRepayment::OVERDUE_YES){
                        $data[60][0][] = $v['overdue_day'];
                        $data[60][1][] = $v['total_money'];
                    }
                }

                if(
                    ($v['plan_repayment_time'] >= $begin_time7 && $v['plan_repayment_time'] < $time)
                    || ($v['closing_time'] >= $order_begin7 && $v['closing_time'] <= $order_time)
                ){
                    $data[7][5][] = $v['total_money'];

                    if($v['is_overdue'] == InfoRepayment::OVERDUE_YES){
                        $data[7][2][] = $v['overdue_day'];
                        $data[7][3][] = $v['total_money'];
                    }
                }

                if(
                    ($v['plan_repayment_time'] >= $begin_time30 && $v['plan_repayment_time'] < $time)
                    || ($v['closing_time'] >= $order_begin30 && $v['closing_time'] <= $order_time)
                ){
                    $data[30][5][] = $v['total_money'];

                    if($v['is_overdue'] == InfoRepayment::OVERDUE_YES){
                        $data[30][2][] = $v['overdue_day'];
                        $data[30][3][] = $v['total_money'];
                    }
                }

                if(
                    ($v['plan_repayment_time'] >= $begin_time60 && $v['plan_repayment_time'] < $time)
                    || ($v['closing_time'] >= $order_begin60 && $v['closing_time'] <= $order_time)
                ){
                    $data[60][5][] = $v['total_money'];

                    if($v['is_overdue'] == InfoRepayment::OVERDUE_YES){
                        $data[60][2][] = $v['overdue_day'];
                        $data[60][3][] = $v['total_money'];
                    }
                }
            }

            $this->szlmOrderData = $data;

            return $this->szlmOrderData[$day][$type];
        }
    }

    protected function getPhoneOrderData($day=0, $type=0){
        if(isset($this->phoneOrderData[$day])){
            return $this->phoneOrderData[$day][$type];
        }else{
            $order_time = $this->data->infoOrder->order_time;
            $time = strtotime(date('Y-m-d', $order_time));
            $begin_time7 = $time - 86400 * 7;
            $begin_time30 = $time - 86400 * 30;
            $begin_time60 = $time - 86400 * 60;

            $order_begin7 = $order_time - 86400 * 7;
            $order_begin30 = $order_time - 86400 * 30;
            $order_begin60 = $order_time - 86400 * 60;
            $item = InfoRepayment::find()
                ->alias('r')
                ->leftJoin(InfoUser::tableName().' as u', 'r.user_id=u.user_id and r.order_id=u.order_id and r.app_name=u.app_name')
                ->select(['r.overdue_day', 'r.is_overdue', 'r.total_money', 'r.plan_repayment_time', 'r.closing_time'])
                ->where([
                    'u.phone' => $this->data->infoUser->phone,
                ])
                ->asArray()
                ->all();
            $data = [
                0 => [
                    0 => [],
                    1 => [],
                    4 => [],
                ],
                7 => [
                    0 => [],
                    1 => [],
                    2 => [],
                    3 => [],
                    4 => [],
                    5 => [],
                ],
                30 => [
                    0 => [],
                    1 => [],
                    2 => [],
                    3 => [],
                    4 => [],
                    5 => [],
                ],
                60 => [
                    0 => [],
                    1 => [],
                    2 => [],
                    3 => [],
                    4 => [],
                    5 => [],
                ],
            ];
            foreach ($item as $v){
                if($v['plan_repayment_time'] < $time){
                    $data[0][4][] = $v['total_money'];

                    if($v['is_overdue'] == InfoRepayment::OVERDUE_YES){
                        $data[0][0][] = $v['overdue_day'];
                        $data[0][1][] = $v['total_money'];
                    }
                }

                if($v['plan_repayment_time'] >= $begin_time7 && $v['plan_repayment_time'] < $time){
                    $data[7][4][] = $v['total_money'];

                    if($v['is_overdue'] == InfoRepayment::OVERDUE_YES){
                        $data[7][0][] = $v['overdue_day'];
                        $data[7][1][] = $v['total_money'];
                    }
                }

                if($v['plan_repayment_time'] >= $begin_time30 && $v['plan_repayment_time'] < $time){
                    $data[30][4][] = $v['total_money'];

                    if($v['is_overdue'] == InfoRepayment::OVERDUE_YES){
                        $data[30][0][] = $v['overdue_day'];
                        $data[30][1][] = $v['total_money'];
                    }
                }

                if($v['plan_repayment_time'] >= $begin_time60 && $v['plan_repayment_time'] < $time){
                    $data[60][4][] = $v['total_money'];

                    if($v['is_overdue'] == InfoRepayment::OVERDUE_YES){
                        $data[60][0][] = $v['overdue_day'];
                        $data[60][1][] = $v['total_money'];
                    }
                }

                if(
                    ($v['plan_repayment_time'] >= $begin_time7 && $v['plan_repayment_time'] < $time)
                    || ($v['closing_time'] >= $order_begin7 && $v['closing_time'] <= $order_time)
                ){
                    $data[7][5][] = $v['total_money'];

                    if($v['is_overdue'] == InfoRepayment::OVERDUE_YES){
                        $data[7][2][] = $v['overdue_day'];
                        $data[7][3][] = $v['total_money'];
                    }
                }

                if(
                    ($v['plan_repayment_time'] >= $begin_time30 && $v['plan_repayment_time'] < $time)
                    || ($v['closing_time'] >= $order_begin30 && $v['closing_time'] <= $order_time)
                ){
                    $data[30][5][] = $v['total_money'];

                    if($v['is_overdue'] == InfoRepayment::OVERDUE_YES){
                        $data[30][2][] = $v['overdue_day'];
                        $data[30][3][] = $v['total_money'];
                    }
                }

                if(
                    ($v['plan_repayment_time'] >= $begin_time60 && $v['plan_repayment_time'] < $time)
                    || ($v['closing_time'] >= $order_begin60 && $v['closing_time'] <= $order_time)
                ){
                    $data[60][5][] = $v['total_money'];

                    if($v['is_overdue'] == InfoRepayment::OVERDUE_YES){
                        $data[60][2][] = $v['overdue_day'];
                        $data[60][3][] = $v['total_money'];
                    }
                }
            }

            $this->phoneOrderData = $data;

            return $this->phoneOrderData[$day][$type];
        }
    }
}