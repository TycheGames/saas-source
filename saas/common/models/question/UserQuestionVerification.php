<?php

namespace common\models\question;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%user_question_verification}}".
 *
 * @property int $id
 * @property int $user_id
 * @property int $merchant_id
 * @property string $questions 分配的题目
 * @property string $answers 配置的答案
 * @property string $user_answers 用户的答案
 * @property int $question_num 题目数量
 * @property int $correct_num 正确数量
 * @property int $enter_time 前端收集，进入时间
 * @property int $submit_time 前端收集，提交时间
 * @property int $data_status 0:分配题目 1:提交答案
 * @property int $created_at
 * @property int $updated_at
 */
class UserQuestionVerification extends ActiveRecord
{
    const STATUS_INIT = 0;
    const STATUS_SUBMIT = 1;

    public static $cityMap = [
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
        'Pinjore',
        'Patna',
        'Ajmer',
        'Jamshedpur',
        'Bhilwara',
        'Alwar',
        'Rewa',
        'Guwahati',
        'Palwal',
        'Sikar',
        'Karnal',
        'Saharanpur',
        'Barmer',
        'Rewari',
        'Pali',
        'Madhubani',
        'Panipat',
        'Rohtak',
        'Tonk',
        'Uttar Pradesh',
        'Firozpur',
        'Sirsa',
        'Morena',
        'Rae Bareli',
        'Sonipat',
        'Bhagalpur',
        'Loni',
        'Samastipur',
        'Lakhimpur',
        'Bikaner',
        'Kota',
        'Bahraich',
        'Patiala',
        'Dispur',
        'Bharatpur',
        'Narnaul',
        'Raigarh',
        'Nagaur',
        'Unnao',
        'Sehore',
        'Siliguri',
        'Hardwar',
        'Durg',
        'Hoshiarpur',
        'Motihari',
        'Rajsamand',
        'Shikarpur',
        'Sitamarhi',
        'Pilibhit',
        'Jalpaiguri',
        'Firozabad',
        'Bilaspur',
        'Hapur',
        'Lalganj',
        'Manali',
        'Gujarat',
        'Hajipur',
        'Fatehabad',
        'Jind',
        'Imphal',
        'Sultanpur',
        'Palampur',
        'Yamunanagar',
        'Kothagudem',
        'Etawah',
        'Rudrapur',
        'Virudhunagar',
        'Sohna',
        'Haldwani-cum-Kathgodam',
        'Pauri',
        'Lalitpur',
        'Bahadurgarh',
        'Mahendragarh',
        'Malegaon',
        'Makrana',
        'Shahjahanpur',
        'Katihar',
        'Sasaram',
        'Sitapur',
        'Silvassa',
        'Dibrugarh',
        'Rajgarh (Churu)',
        'Madhepura',
        'North Lakhimpur',
        'Udaipurwati',
        'Sidlaghatta',
        'Telangana',
        'Khanna',
        'Nawada',
        'Kerala',
        'Mussoorie',
        'Sundarnagar',
        'Moga',
        'Gobindgarh',
        'Fazilka',
        'Kishanganj',
        'Kaithal',
        'Pathankot',
        'Khair',
        'Dimapur',
        'Lalgudi',
        'Ratnagiri',
        'Narwana',
        'Sirohi',
        'Shahpura',
        'Manasa',
        'Tiruvethipuram',
        'Punch',
        'Una',
        'Phagwara',
        'Tundla',
        'Lar',
        'Anantnag',
        'Godhra',
        'Pilkhuwa',
        'Sangamner',
        'Punjab',
        'Zirakpur',
        'Jhumri Tilaiya',
        'Kullu',
        'Vrindavan',
        'Phulera',
        'Udhampur',
        'Neem-Ka-Thana',
        'Habra',
        'Medinipur',
        'Brahmapur',
        'Nandyal',
        'Tirunelveli',
        'Pudukkottai',
        'Bhadrak',
        'Tezpur',
        'Mancherial',
        'Hugli-Chinsurah',
        'Kannur',
        'Korba',
        'Marigaon',
        'Miryalaguda',
        'Jharsuguda',
        'Purulia',
        'Sundargarh',
        'Vidisha',
        'Yavatmal',
        'Hardoi',
        'Saharsa',
        'Alappuzha',
        'Tiruchendur',
        'Sambalpur',
        'Pipar City',
        'Srirampore',
        'Nabadwip',
        'Thanesar',
        'Balangir',
        'Bhongir',
        'Bhawanipatna',
        'Jangaon',
        'Shahabad',
        'Tarakeswar',
        'Ranaghat',
        'Naihati',
        'Amalner',
        'Bargarh',
        'Robertsganj',
        'Giridih',
        'Sumerpur',
        'Sojat',
        'Nimbahera',
        'Jamui',
        'Adityapur',
        'Markapur',
        'Nagaon',
        'Memari',
        'Raiganj',
        'Tumsar',
        'Nuzvid',
        'Pandhurna',
        'Pipariya',
        'Sircilla',
        'Pacode',
        'Chatra',
        'Washim',
        'Soro',
        'Manuguru',
        'Forbesganj',
        'Ranebennuru',
        'Palladam',
        'Karimganj',
        'Sitarganj',
        'Ron',
        'Sanchore',
        'AlipurdUrban Agglomerationr',
        'Pen',
        'Rasipuram',
        'Nalbari',
        'Porsa',
        'Tura',
        'Vita',
        'Gudivada',
        'Akot',
        'Maner',
        'Naidupet',
        'Sindhnur',
        'Adilabad',
        'Arvi',
        'Shamli',
        'Jagraon',
        'Tinsukia',
        'Arakkonam',
        'Raisinghnagar',
        'Yerraguntla',
        'Rudauli',
        'Multai',
        'Thodupuzha',
        'Sirkali',
        'Gumia',
        'Renigunta',
        'Sadulshahar',
        'Narkatiaganj',
        'Ambikapur',
        'Mihijam',
        'Rawatsar',
        'Tanda',
        'Salaya',
        'Silapathar',
        'Sunam',
        'Mukhed',
        'Ujhani',
        'Usilampatti',
        'Lumding',
        'Jhargram',
        'Todabhim',
        'Mahidpur',
        'Nellikuppam',
        'Valparai',
        'Nandivaram-Guduvancheri',
        'Vatakara',
        'Manawar',
        'Mundi',
        'Rasra',
        'Tenkasi',
        'Kashipur',
        'Bhatapara',
        'Zira',
        'Rosera',
        'Sheikhpura',
        'Raxaul Bazar',
        'Patti',
        'Barnala',
        'Maihar',
        'Lakheri',
        'Mankachar',
        'Sawantwadi',
        'Panna',
        'Nehtaur',
        'Zamania',
        'Dhamtari',
        'English Bazar',
        'Savanur',
        'Sadasivpet',
        'Bhainsa',
        'Palasa Kasibugga',
        'Ganjbasoda',
        'Nagla',
        'Puliyankudi',
        'Bongaigaon City',
        'Aruppukkottai',
        'Nelamangala',
        'Kadiri',
        'Taki',
        'Tarbha',
        'Safidon',
        'Baripada Town',
        'Sakaleshapura',
        'Umbergaon',
        'Wokha',
        'Chirmiri',
        'Thiruthuraipoondi',
        'Jorhat',
        'Sironj',
        'Motipur',
        'Orai',
        'Nandurbar',
        'Simdega',
        'Gopalganj',
        'Morinda',
        'Balurghat',
        'Sanand',
        'Khowai',
        'Tehri',
        'Chandausi',
        'Pardi',
        'Sullurpeta',
        'Venkatagiri',
        'Sikandra Rao',
        'Alirajpur',
        'Srisailam Project (Right Flank Colony) Township',
        'Paravoor',
        'Vaikom',
        'Pathanamthitta',
        'Nagina',
        'Mattannur',
        'Punganur',
        'Punalur',
        'Sattur',
        'Mangrol',
        'Vadgaon Kasba',
        'Marmagao',
        'Nepanagar',
        'Rabkavi Banhatti',
        'Aizawl',
        'Margherita',
        'Taranagar',
        'Ratangarh',
        'Palakkad',
        'Sheopur',
        'Renukoot',
        'Yadgir',
        'Nohar',
        'Thiruvalla',
        'Uran',
        'Nokha'
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_question_verification}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'question_num', 'correct_num', 'data_status', 'enter_time', 'submit_time', 'created_at', 'updated_at'], 'integer'],
            [['questions', 'answers', 'user_answers'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'           => 'ID',
            'user_id'      => 'User ID',
            'questions'    => 'Questions',
            'answers'      => 'Answers',
            'user_answers' => 'User Answers',
            'question_num' => 'Question Num',
            'correct_num'  => 'Correct Num',
            'enter_time'   => 'Enter Time',
            'submit_time'  => 'Submit Time',
            'created_at'   => 'Created At',
            'updated_at'   => 'Updated At',
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * 判断是否命中准入城市
     * @param $city
     * @return bool
     */
    public static function checkCity($city){
        foreach (self::$cityMap as $v){
            if(strtoupper($city) == strtoupper($v)){
                return true;
            }
        }

        return false;
    }
}
