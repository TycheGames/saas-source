// @ts-nocheck
/**
 * Created by yaer on 2019/9/27;
 * @Email 740905172@qq.com
 * */
import * as packageNameEnum from "../enum/packageNameEnum";

const bigSharkColor = "#F74545";
const moneyClickColor = "#FF2727";
const rupeeFantaColor = "#ff9f1c";
const rupeeCashColor = "#3296fa";
const dhanCashColor = "#04d301";
const luckyWalletColor = "#01ba40";
const wealthStewardColor = "#CD2A20";
const rupeeLaxmiColor = "#00ED0F";
const hindMoneyColor = "#F85544";
const easyCashColor = "#f68c1e";
const newCashColor = "#368AF4";
const happyWalletColor = "#65B794";

const loveCashColor = "#FF9A25";

const defaultFontColor = "#fff";

const recommendDefaultBg = "#fecd16";

const recommendDefaultColor = "#000";

const vestStyle = {
  [packageNameEnum.BIG_SHARK]: {
    color: bigSharkColor,
    background: bigSharkColor,
    fontColor: defaultFontColor,
    recommendBg: recommendDefaultBg,
    recommendColor: recommendDefaultColor,
    orderDetailBg: require("../images/orderDetail/order_detail_bg.png"),
    repaymentBg: require("../images/orderDetail/repayment_bg.png"),
    repaymentModalBg: require("../images/orderDetail/repayment_modal_bg.png"),
    noLoginIcon: require("../images/personalCenter/login_icon_v2.png"),
    loginIcon: require("../images/personalCenter/login_icon_2_v2.png"),
    personalCenterBg: require("../images/personalCenter/personal_center_bg.png"),
    sanctionHeaderImg: require("../images/sanctionLetter/aglow.png"),
  },
  [packageNameEnum.MONEY_CLICK]: {
    color: bigSharkColor,
    background: moneyClickColor,
    fontColor: defaultFontColor,
    recommendBg: recommendDefaultBg,
    recommendColor: recommendDefaultColor,
    orderDetailBg: require("../images/orderDetail/order_detail_bg.png"),
    repaymentBg: require("../images/orderDetail/vest/repayment_bg_money_click.png"),
    repaymentModalBg: require("../images/orderDetail/repayment_modal_bg.png"),
    noLoginIcon: require("../images/personalCenter/vest/no_login_icon_money_click.png"),
    loginIcon: require("../images/personalCenter/vest/login_icon_money_click.png"),
    personalCenterBg: require("../images/personalCenter/vest/personal_center_bg_money_click.png"),
  },
  [packageNameEnum.LOVE_CASH]: {
    color: loveCashColor,
    background: loveCashColor,
    fontColor: defaultFontColor,
    recommendBg: recommendDefaultBg,
    recommendColor: recommendDefaultColor,
    orderDetailBg: require("../images/orderDetail/order_detail_bg.png"),
    repaymentBg: require("../images/orderDetail/vest/repayment_bg_love_cash.png"),
    repaymentModalBg: require("../images/orderDetail/repayment_modal_bg.png"),
    noLoginIcon: require("../images/personalCenter/vest/no_login_icon_love_cash.png"),
    loginIcon: require("../images/personalCenter/vest/login_icon_love_cash.png"),
    personalCenterBg: require("../images/personalCenter/vest/personal_center_bg_love_cash.png"),
  },
  [packageNameEnum.RUPEE_FANTA]: {
    color: rupeeFantaColor,
    background: rupeeFantaColor,
    fontColor: defaultFontColor,
    recommendBg: recommendDefaultBg,
    recommendColor: recommendDefaultColor,
    orderDetailBg: require("../images/orderDetail/order_detail_bg.png"),
    repaymentBg: require("../images/orderDetail/vest/repayment_bg_rupee_fanta.png"),
    repaymentModalBg: require("../images/orderDetail/repayment_modal_bg.png"),
    noLoginIcon: require("../images/personalCenter/vest/no_login_icon_rupee_fanta.png"),
    loginIcon: require("../images/personalCenter/vest/login_icon_rupee_fanta.png"),
    personalCenterBg: require("../images/personalCenter/vest/personal_center_bg_rupee_fanta.png"),
  },
  [packageNameEnum.DHAN_CASH]: {
    color: dhanCashColor,
    background: dhanCashColor,
    fontColor: defaultFontColor,
    recommendBg: recommendDefaultBg,
    recommendColor: recommendDefaultColor,
    orderDetailBg: require("../images/orderDetail/order_detail_bg.png"),
    repaymentBg: require("../images/orderDetail/vest/repayment_bg_rupee_fanta.png"),
    repaymentModalBg: require("../images/orderDetail/repayment_modal_bg.png"),
    noLoginIcon: require("../images/personalCenter/vest/no_login_icon_rupee_fanta.png"),
    loginIcon: require("../images/personalCenter/vest/login_icon_rupee_fanta.png"),
    personalCenterBg: require("../images/personalCenter/vest/personal_center_bg_rupee_fanta.png"),
  },
  [packageNameEnum.RUPEE_CASH]: {
    color: rupeeCashColor,
    background: rupeeCashColor,
    fontColor: defaultFontColor,
    recommendBg: recommendDefaultBg,
    recommendColor: recommendDefaultColor,
    orderDetailBg: require("../images/orderDetail/order_detail_bg.png"),
    repaymentBg: require("../images/orderDetail/vest/repayment_bg_rupee_fanta.png"),
    repaymentModalBg: require("../images/orderDetail/repayment_modal_bg.png"),
    noLoginIcon: require("../images/personalCenter/vest/no_login_icon_rupee_fanta.png"),
    loginIcon: require("../images/personalCenter/vest/login_icon_rupee_fanta.png"),
    personalCenterBg: require("../images/personalCenter/vest/personal_center_bg_rupee_fanta.png"),
  },
  [packageNameEnum.LUCKY_WALLET]: {
    color: luckyWalletColor,
    background: luckyWalletColor,
    fontColor: defaultFontColor,
    recommendBg: recommendDefaultBg,
    recommendColor: recommendDefaultColor,
    orderDetailBg: require("../images/orderDetail/order_detail_bg.png"),
    repaymentBg: require("../images/orderDetail/vest/repayment_bg_rupee_fanta.png"),
    repaymentModalBg: require("../images/orderDetail/repayment_modal_bg.png"),
    noLoginIcon: require("../images/personalCenter/vest/no_login_icon_rupee_fanta.png"),
    loginIcon: require("../images/personalCenter/vest/login_icon_rupee_fanta.png"),
    personalCenterBg: require("../images/personalCenter/vest/personal_center_bg_rupee_fanta.png"),
  },
  [packageNameEnum.WEALTH_STEWARD]: {
    color: bigSharkColor,
    background: wealthStewardColor,
    fontColor: defaultFontColor,
    recommendBg: recommendDefaultBg,
    recommendColor: recommendDefaultColor,
    orderDetailBg: require("../images/orderDetail/order_detail_bg.png"),
    repaymentBg: require("../images/orderDetail/vest/repayment_bg_money_click.png"),
    repaymentModalBg: require("../images/orderDetail/repayment_modal_bg.png"),
    noLoginIcon: require("../images/personalCenter/vest/no_login_icon_money_click.png"),
    loginIcon: require("../images/personalCenter/vest/login_icon_money_click.png"),
    personalCenterBg: require("../images/personalCenter/vest/personal_center_bg_money_click.png"),
  },
  [packageNameEnum.RUPEE_LAXMI]: {
    color: rupeeLaxmiColor,
    background: rupeeLaxmiColor,
    fontColor: defaultFontColor,
    recommendBg: recommendDefaultBg,
    recommendColor: recommendDefaultColor,
    orderDetailBg: require("../images/orderDetail/order_detail_bg.png"),
    repaymentBg: require("../images/orderDetail/vest/repayment_bg_rupee_fanta.png"),
    repaymentModalBg: require("../images/orderDetail/repayment_modal_bg.png"),
    noLoginIcon: require("../images/personalCenter/vest/no_login_icon_rupee_fanta.png"),
    loginIcon: require("../images/personalCenter/vest/login_icon_rupee_fanta.png"),
    personalCenterBg: require("../images/personalCenter/vest/personal_center_bg_rupee_fanta.png"),
  },
  [packageNameEnum.HIND_MONEY]: {
    color: hindMoneyColor,
    background: hindMoneyColor,
    fontColor: defaultFontColor,
    recommendBg: recommendDefaultBg,
    recommendColor: recommendDefaultColor,
    orderDetailBg: require("../images/orderDetail/order_detail_bg.png"),
    repaymentBg: require("../images/orderDetail/vest/repayment_bg_money_click.png"),
    repaymentModalBg: require("../images/orderDetail/repayment_modal_bg.png"),
    noLoginIcon: require("../images/personalCenter/vest/no_login_icon_money_click.png"),
    loginIcon: require("../images/personalCenter/vest/login_icon_money_click.png"),
    personalCenterBg: require("../images/personalCenter/vest/personal_center_bg_money_click.png"),
  },
  [packageNameEnum.EASY_CASH]: {
    color: easyCashColor,
    background: easyCashColor,
    fontColor: defaultFontColor,
    recommendBg: recommendDefaultBg,
    recommendColor: recommendDefaultColor,
    orderDetailBg: require("../images/orderDetail/order_detail_bg.png"),
    repaymentBg: require("../images/orderDetail/vest/repayment_bg_love_cash.png"),
    repaymentModalBg: require("../images/orderDetail/repayment_modal_bg.png"),
    noLoginIcon: require("../images/personalCenter/vest/no_login_icon_love_cash.png"),
    loginIcon: require("../images/personalCenter/vest/login_icon_love_cash.png"),
    personalCenterBg: require("../images/personalCenter/vest/personal_center_bg_love_cash.png"),
  },

  [packageNameEnum.NEW_CASH]: {
    color: newCashColor,
    background: newCashColor,
    fontColor: defaultFontColor,
    recommendBg: recommendDefaultBg,
    recommendColor: recommendDefaultColor,
    orderDetailBg: require("../images/orderDetail/order_detail_bg.png"),
    repaymentBg: require("../images/orderDetail/vest/repayment_bg_rupee_fanta.png"),
    repaymentModalBg: require("../images/orderDetail/repayment_modal_bg.png"),
    noLoginIcon: require("../images/personalCenter/vest/no_login_icon_rupee_fanta.png"),
    loginIcon: require("../images/personalCenter/vest/login_icon_rupee_fanta.png"),
    personalCenterBg: require("../images/personalCenter/vest/personal_center_bg_new_cash.png"),
  },
  [packageNameEnum.HAPPY_WALLET]: {
    color: happyWalletColor,
    background: happyWalletColor,
    fontColor: defaultFontColor,
    recommendBg: recommendDefaultBg,
    recommendColor: recommendDefaultColor,
    orderDetailBg: require("../images/orderDetail/order_detail_bg.png"),
    repaymentBg: require("../images/orderDetail/vest/repayment_bg_rupee_fanta.png"),
    repaymentModalBg: require("../images/orderDetail/repayment_modal_bg.png"),
    noLoginIcon: require("../images/personalCenter/vest/no_login_icon_happy_wallet.png"),
    loginIcon: require("../images/personalCenter/vest/login_icon_happy_wallet.png"),
    personalCenterBg: require("../images/personalCenter/vest/personal_center_bg_happy_wallet.png"),
  },
};

/**
 * 字体色
 */
export function color() {
  return { color: _vestStyleParam("color") };
}

/**
 * 文字颜色（特殊处理）
 */
export function fontColor() {
  return { color: _vestStyleParam("fontColor") };
}

/**
 * 文字颜色（特殊处理）
 * @param {*} cc 颜色
 */
export function specialFontColor(cc) {
  const { packageName } = window.appInfo;
  return {
    color:
      packageName === packageNameEnum.BIG_SHARK ||
      packageName === packageNameEnum.LOVE_CASH
        ? cc
        : color().color,
  };
}

/**
 * 订单详情角标
 */
export function recommendBgAndFc() {
  return {
    color: _vestStyleParam("recommendColor"),
    background: _vestStyleParam("recommendBg"),
  };
}

/**
 * 纯主题色返回
 */
export function mainColor() {
  return _vestStyleParam("color");
}

/**
 * 背景色和文字颜色
 */
export function bgAndFc() {
  return { ...fontColor(), ...background() };
}

/**
 * 背景色
 */
export function background() {
  return { background: _vestStyleParam("background") };
}

/**
 * 订单详情背景
 */
export function orderDetailRepaymentBg() {
  return _setBackground("repaymentBg");
}

/**
 * 个人中心背景色
 */
export function personalCenterBg() {
  return _setBackground("personalCenterBg");
}

/**
 * 个人中心未登录icon
 */
export function personalCenterNoLoginIcon() {
  return _vestStyleParam("noLoginIcon");
}

/**
 * gerenzho登录icon
 */
export function personalCenterLoginIcon() {
  return _vestStyleParam("loginIcon");
}

/**
 * 订单详情还款背景
 */
export function orderDetailOrderDetailBg() {
  return _setBackground("orderDetailBg");
}

/**
 * 订单详情促还款弹窗背景
 */
export function orderDetailRepaymentModalBg() {
  return _setBackground("repaymentModalBg");
}

/**
 * 边框色
 */
export function borderColor() {
  return { borderColor: _vestStyleParam("color") };
}

/**
 * 返回背景
 * @param {} type 配置key
 */
function _setBackground(type) {
  return {
    backgroundSize: "100% 100%",
    backgroundImage: `url(${_vestStyleParam(type)})`,
    backgroundRepeat: "no-repeat",
  };
}

/**
 * 根据包名返回配置
 * @param {*} type
 */
function _vestStyleParam(type) {
  const { packageName } = window.appInfo;
  return (vestStyle[packageName] && vestStyle[packageName][type]) || {};
}
/**
 * 替换app信息
 * @param appName
 */
export function replaceAppName(appName) {
  let packageName,
    email,
    company,
    website,
    address,
    googleLink,
    logo,
    contactList;
  switch (appName) {
    case "bigshark":
      packageName = packageNameEnum.BIG_SHARK;
      email = "Huayetechcustomerdesk.ind@yahoo.com";
      company = "HUA YE TECHNOLOGY INDIA PRIVATE LIMITED";
      website = "http://www.ln-bigshark.com";
      address =
        "151,NATIONAL MEDIA CENTRE ,GURGAON,GURGAON,HARYANNA,INDIA,122002";
      googleLink = "";
      logo = require("../images/logo.png");
      contactList = [
        {
          name: "Mr.Hu",
          phone: "+91 9560822321",
        },
      ];
      break;
    case "moneyclick":
      packageName = packageNameEnum.MONEY_CLICK;
      email = "customer.service@moneyclick.vip";
      company = "PINPRINT TECHNOLOGIES PVT LTD";
      website = "";
      address = "";
      googleLink = "";
      logo = require("../images/logo_money_click.png");
      contactList = [
        /*  {
          name: "Sindhu",
          phone: "+91 7702042753"
        }, */
        {
          name: "Sarath ",
          phone: "+91 01204037520",
        },
      ];
      break;
    case "lovecash":
      packageName = packageNameEnum.LOVE_CASH;
      email = "cs@microsloop.com";
      company = "MICROSLOOP INFORMATION TECHNOLOGY PRIVATE LIMITED";
      website = "";
      address =
        "SF-221,2-FLOOR,1T COMPLEX,JMD MEJAPOLIS,VILLAGE, TIKRI,SOHNA ROAD,GURGAON,HARYANA,INDIA,122001";
      googleLink = "";
      logo = require("../images/logo_love_cash.png");
      contactList = [
        {
          name: "zhangxiaofeng",
          phone: "+91 96670 09859",
        },
      ];
      break;
    case "rupeefanta":
      packageName = packageNameEnum.RUPEE_FANTA;
      email = " rupeefanta@gmail.com";
      company = "PINPRINT TECHNOLOGIES PVT LTD";
      website = "";
      address = "";
      googleLink = "";
      logo = require("../images/logo_rupee_fanta.png");
      contactList = [
        /*  {
          name: "Sindhu",
          phone: "+91 7702042753"
        }, */
        {
          name: "Sarath ",
          phone: "+91 7093452336",
        },
      ];
      break;
    case "rupeecash":
      packageName = packageNameEnum.RUPEE_CASH;
      email = "RupeecashCS@hotmail.com";
      company = "PAISAXL FINTECH PRIVATE LIMITED";
      website = "";
      address =
        "FIRST FLOOR, H-55 SECTOR-63, NOIDA Gautam Buddha Nagar UP 201301 IN";
      googleLink = "";
      logo = require("../images/logo_rupee_cash.png");
      contactList = [
        {
          name: "Joey Zhao ",
          phone: "+91 8939704998",
        },
      ];
      break;
    case "dhancash":
      packageName = packageNameEnum.DHAN_CASH;
      email = "009Dhancash@gmail.com";
      company = "Huaye Tecnology Private Ltd";
      website = "";
      address = "Plot no. 136 udyog vihar phase 1 Gurgaon";
      googleLink = "";
      logo = require("../images/logo_dhan_cash.png");
      contactList = [
        {
          name: "Huaye",
          phone: "+91 9999038829",
        },
      ];
      break;
    case "rupeelaxmi":
      packageName = packageNameEnum.RUPEE_LAXMI;
      email = "009Rupeelaxmi@gmail.com";
      company = "MICROLEAP TECHNOLOGY PRIVATE LIMITED";
      website = "https://www.rupeelaxmi.com";
      address = "Plot no. 136 udyog vihar phase 1 Gurgaon";
      googleLink = "";
      logo = require("../images/logo_rupee_laxmi.png");
      contactList = [
        {
          name: "MICROLEAP",
          phone: "+91 9999031636",
        },
      ];
      break;
    case "excellentcash":
      packageName = packageNameEnum.EXCELLENT_CASH;
      email = "mandal999@gmail.com";
      company = "BSPRING TECH PRIVATE LIMITED";
      website = "";
      address =
        "Ground floor，Tower B，Building No.5 -Epitome，DLF Cyber City，Gurugram，Haryana 122002";
      googleLink = "";
      logo = "";
      contactList = [
        {
          name: "Dilip kumar",
          phone: "+91 8130595176",
        },
      ];
      break;
    case "hindmoney":
      packageName = packageNameEnum.HIND_MONEY;
      email = "osejenny266@yahoo.com";
      company = "WEILONG TECH INDIA PRIVATE LIMTED";
      website = "https://www.hindmoney.com";
      address = "A85, Vipul World, Sector 48, Gurugram, Haryana";
      googleLink = "";
      logo = require("../images/logo_hind_money.png");
      contactList = [
        {
          name: "Joes",
          phone: "+91 8447585261",
        },
      ];
      break;
    case "newcash":
      packageName = packageNameEnum.NEW_CASH;
      email = "kajalprasad71@gmail.com";
      company = "CLEAR WEEKDAY TECHNOLOGY INDIA PRIVATE LIMITED";
      website = "https://www.newcashnow.com";
      address =
        "1st floor, No 20, Alfa Centre, Venugopal Swamy Layout,Kormangala, Bengaluru (Bangalore) Urban, Karnataka, 560047";
      googleLink = "";
      logo = require("../images/logo_new_cash.png");
      contactList = [
        {
          name: "wukong",
          phone: "+91 8929724524",
        },
      ];
      break;
    case "wealthsteward":
      packageName = packageNameEnum.WEALTH_STEWARD;
      email = "diversityweekday2020@gmail.com";
      company = "Diversity weekday technology india private limited";
      website = "https://www.bigwealthsteward.com";
      address =
        "Diversity weekday technology india private limited No 20, 2nd floor,V enugopal Swamy Layout,, Ejipura, BANGALORE,Bangalore, K amataka, India, 560095";
      googleLink = "";
      logo = require("../images/wealth_steward_logo.png");
      contactList = [
        {
          name: "CHINNABBA RAJASEKHAR",
          phone: "+91 7838235390",
        },
      ];
      break;
    case "luckywallet":
      packageName = packageNameEnum.LUCKY_WALLET;
      email = "financelonni@gmail.com";
      company = "LONNI FINANCE TECHNOLOGY PRIVATE LIMITED";
      website = "https://www.getluckywallet.com";
      address =
        "A-1510, MAXBLIS GRAND KINGSTON, SECTOR-75, NOIDA, Gautam, Buddha Nagar, Uttar Pradesh, India, 201301";
      googleLink = "";
      logo = require("../images/logo_lucky_wallet.png");
      contactList = [
        {
          name: "RAM KRISHNA CHAURASIA",
          phone: "+91 8860493246",
        },
      ];
      break;
    case "easycash":
      packageName = packageNameEnum.EASY_CASH;
      email = "kajalprasad71@gmail.com";
      company = "CLEAR WEEKDAY TECHNOLOGY INDIA PRIVATE LIMITED";
      website = "https://www.alleasycash.com";
      address =
        "1st floor, No 20, Alfa Centre, Venugopal Swamy Layout, Kormangala, Bengaluru (Bangalore) Urban, Karnataka, 560047";
      googleLink = "";
      logo = require("../images/logo_easy_cash.png");
      contactList = [
        {
          name: "wukong",
          phone: "+91 8929724524",
        },
      ];
      break;
    case "bluecash":
      packageName = packageNameEnum.BLUE_CASH;
      email = "mandal999@gmail.com";
      company = "BSPRING TECH PRIVATE LIMITED";
      website = "https://www.bluecash888.com";
      address =
        "Ground floor，Tower B，Building No.5 -Epitome，DLF Cyber City，Gurugram，Haryana 122002";
      googleLink = "";
      logo = require("../images/logo_lucky_wallet.png");
      contactList = [
        {
          name: "wukong",
          phone: "+91 8130595176",
        },
      ];
      break;
    case "happywallet":
      packageName = packageNameEnum.HAPPY_WALLET;
      email = "huizimiss@gmail.com";
      company = "INNOVCASH TECHNOLOGY PRIVATE LIMITED";
      website = "https://www.happyhappywallet.com";
      address =
        "839P Sector 40 road Behind Community Center Sector-40 Gurgaon Gurgaon HR 122003 IN";
      googleLink = "";
      logo = require("../images/logo_happy_wallet.png");
      contactList = [
        {
          name: "Robin",
          phone: "+91 7838265291",
        },
      ];
      break;
    case "hipaisa":
      packageName = packageNameEnum.HIPAISA;
      email = "support@flashkashtech.in";
      company = "Flashkash Technology private limited";
      website = "https://www.hipaisa1.com";
      address = "2nd Floor, Plot 809A, udyog vihar Phase V, gurgaon";
      googleLink = "";
      logo = require("../images/logo_lucky_wallet.png");
      contactList = [
        {
          name: "Hemant Kumar",
          phone: "+91 8826596862",
        },
      ];
      break;
    case "rupeefirst":
      packageName = packageNameEnum.RUPEE_FIRST;
      email = "kajalprasad71@gmail.com";
      company = "CLEAR WEEKDAY TECHNOLOGY INDIA PRIVATE LIMITED";
      website = "https://www.rupeefirst.com";
      address =
        "1st floor, No 20, Alfa Centre, Venugopal Swamy Layout,Kormangala, Bengaluru (Bangalore) Urban, Karnataka, 560047";
      googleLink = "";
      logo = require("../images/logo_lucky_wallet.png");
      contactList = [
        {
          name: "wukong",
          phone: "+91 8929724524",
        },
      ];
      break;
    case "dailyrupee":
      packageName = packageNameEnum.DAILY_RUPEE;
      email = "info@dailyrupee.in";
      company = "AJAYA SOLUTIONS PRIVATE LIMITED";
      website = "https://www.dailyrupee888.com";
      address =
        "5TH FLOOR, 521, ARCADIA, HIRANANDANI ESTATE, PATLIPADA, GHODBUNDER ROAD, THANE, Thane, Maharashtra, 400607";
      googleLink = "";
      logo = require("../images/logo_lucky_wallet.png");
      contactList = [
        {
          name: "Sanchita Konai",
          phone: "+91 9136514349",
        },
      ];
      break;
    case "firstcash":
      packageName = packageNameEnum.FIRSTCASH;
      email = "kajalprasad71@gmail.com";
      company = "CLEAR WEEKDAY TECHNOLOGY INDIA PRIVATE LIMITED";
      website = "https://www.firstcash1.com";
      address =
        "1st floor, No 20, Alfa Centre, Venugopal Swamy Layout, Kormangala, Bengaluru (Bangalore) Urban, Karnataka, 560047";
      googleLink = "";
      logo = require("../images/logo_lucky_wallet.png");
      contactList = [
        {
          name: "wukong",
          phone: "+91 8929724524",
        },
      ];
      break;
    case "whaleloan":
      packageName = packageNameEnum.WHALE_LOAN;
      email = "info@x10corp.com";
      company = "Huidatech Technology Private limited";
      website = "https://www.thewhaleloan.com";
      address =
        "House No-628, Sector45, GURGAON, Gurgaon, Haryana, India, 122001";
      googleLink = "";
      logo = "";
      contactList = [
        {
          name: "Vinay Chaudhary",
          phone: "+91 8130699205",
        },
      ];
      break;
    case "orangkaya":
      packageName = packageNameEnum.ORANG_KAYA;
      email = "ypmtechnologies@gmail.com";
      company = "YPM TECHNOLOGY PTY LTD";
      website = "https://www.orangekaya.com";
      address =
        " Flat 102, Plot .57, SY.No.242, BS Reedys Enclave, Kakatiya Nagar, Nizampet, Hyderabad Rangareddi TG 500090 IN";
      googleLink = "";
      logo = "";
      contactList = [
        {
          name: packageNameEnum.ORANG_KAYA,
          phone: "",
        },
      ];
      break;
    case "dreamloan":
      packageName = packageNameEnum.DREAM_LOAN;
      email = "kajalprasad71@gmail.com";
      company = "CLEAR WEEKDAY TECHNOLOGY INDIA PRIVATE LIMITED";
      website = "https://dreamloan666.com";
      address =
        " 1st floor, No 20, Alfa Centre, Venugopal Swamy Layout, Kormangala, Bengaluru (Bangalore) Urban, Karnataka, 560047";
      googleLink = "";
      logo = "";
      contactList = [
        {
          name: packageNameEnum.DREAM_LOAN,
          phone: "8929724524",
        },
      ];
      break;
    case "chiefloan":
      packageName = packageNameEnum.CHIEF_LOAN;
      email = "visrotechnologies02@gmail.com";
      company = "Visro Technologies Private Limited";
      website = "https://chiefloanmax.com";
      address =
        "2nd Floor,Barrys group building, 21, Wood St, Ashok Nagar, Bengaluru, Karnataka - 560025.India";
      googleLink = "";
      logo = "";
      contactList = [
        {
          name: "PRADEEP  KUMAR",
          phone: "9513361951",
        },
      ];
      break;
    case "getrupee":
      packageName = packageNameEnum.GET_RUPEE;
      email = "kajalprasad71@gmail.com";
      company = "CLEAR WEEKDAY TECHNOLOGY INDIA PRIVATE LIMITED";
      website = "https://getrupeeone.com";
      address =
        "1st floor, No 20, Alfa Centre, Venugopal Swamy Layout, Kormangala, Bengaluru (Bangalore) Urban, Karnataka, 560047";
      googleLink = "";
      logo = "";
      contactList = [
        {
          name: "wukong",
          phone: "8929724524",
        },
      ];
      break;
    default:
      packageName = "";
      email = "";
      company = "";
      website = "";
      address = "";
      googleLink = "";
      logo = "";
      contactList = [];
  }
  return {
    packageName,
    email,
    company,
    website,
    address,
    googleLink,
    logo,
    contactList,
  };
}
