/* eslint-disable */
import { Base64 } from "js-base64";
/**
 * 获取系统
 * @returns {*}
 */
export function getSys() {
  let sys;
  const u = navigator.userAgent;
  const isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); // ios终端
  if (isiOS) {
    sys = "ios";
  } else {
    sys = "android";
  }
  return sys;
}

/**
 * 获取系统型号
 * @returns {{}}
 */
export function isBrowser() {
  const u = navigator.userAgent;
  window.browser = {};
  window.browser.iPhone = u.indexOf("iPhone") > -1; // iPhone or QQHD
  window.browser.android = u.indexOf("Android") > -1 || u.indexOf("Linux") > -1; // android or uc
  window.browser.ios = u.match(/Mac OS/); // ios
  window.browser.wx = u.match(/MicroMessenger/);
  return window.browser;
}

/**
 * 获取url的params
 * @param  props props对象
 * @returns {Object}
 */
export function getUrlData(props) {
  let { search } = props.location;
  let data = {};
  search = search.substr(1).split("&");
  search.forEach(item => {
    item = item.split("=");
    if (item[0]) {
      data[item[0]] = item[1];
    }
  });
  return data;
}

/**
 * fixed定位解决遮盖元素问题
 * @param cloneDom fixed定位的dom节点
 * @param company css单位 px rem
 * @returns {*}
 */
export function fixedClone(cloneDom, company = "rem") {
  // 获取需要克隆节点的宽高
  const height = cloneDom.offsetHeight;
  const width = cloneDom.offsetWidth;
  // 生成dom并且获取html的fontSize
  const dom = document.createElement("div");
  const htmlFontSize = Number(
    window.document.documentElement.style.fontSize.split("px")[0]
  );

  // dom赋值
  if (company === "rem") {
    dom.style.height = `${height / htmlFontSize}rem`;
    dom.style.width = `${width / htmlFontSize}rem`;
  } else {
    dom.style.height = `${height}px`;
    dom.style.width = `${width}px`;
  }

  // 插入dom 如果cloneDom的最后一个节点是cloneDom 那么就直接在最后插入dom 否则就在clone下一个节点前插入dom
  const parent = cloneDom.parentNode;
  if (parent.lastChild === cloneDom) {
    parent.appendChild(dom);
  } else {
    parent.insertBefore(dom, cloneDom.nextSibling);
  }
}

/**
 * 时间转换
 * @param date  毫秒值的时间
 * @param format  转换格式
 * @returns {string}
 */
export function setDate(date, format) {
  const time = new Date(date);
  let val = "";
  switch (format) {
    case "yy-mm":
      val = `${time.getFullYear()}年${time.getMonth() + 1}月`;
      break;
    case "yy-mm-dd":
      val = `${time.getFullYear()}/${time.getMonth() + 1}/${zero(
        time.getDate()
      )}`;
      break;
    case "yy-mm-dd-hh-ii":
      val = `${time.getFullYear()}/${time.getMonth() + 1}/${zero(
        time.getDate()
      )}  ${zero(time.getHours())}:${zero(time.getMinutes())}`;
      break;
    case "hh-ii":
      val = `${zero(time.getHours())}:${zero(time.getMinutes())}`;
      break;
    case "hh-ii-ss":
      val = `${zero(time.getHours())}:${zero(time.getMinutes())}:${zero(
        time.getSeconds()
      )}`;
      break;
  }
  return val;
}

/**
 * 时间倒计时
 * @param mss 毫秒时间
 * @returns {string}
 */
export function formatDuring(mss) {
  const hours = zero(
    parseInt((mss % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))
  );
  const minutes = zero(parseInt((mss % (1000 * 60 * 60)) / (1000 * 60)));
  const seconds = `${zero((mss % (1000 * 60)) / 1000)}`.split(".")[0];
  return `${hours}:${minutes}:${seconds}`;
}

/**
 * 获取当天23：59：59 毫秒值
 * @returns {number}
 */
export function getTodayDeadline() {
  return (
    new Date(new Date().toLocaleDateString()).getTime() +
    24 * 60 * 60 * 1000 -
    1
  );
}

/**
 * 补0
 * @param num Number数字
 * @returns {*}
 */
function zero(num) {
  if (num < 10) {
    return `0${num}`;
  }
  return num;
}

/**
 *  唤起APP
 * @param jumpLink  唤起APP的链接
 * @param download  唤起不成功的下载链接
 * @returns {*}
 */
export function AppJump(jumpLink, download) {
  // 判断浏览器
  const u = navigator.userAgent;
  if (/MicroMessenger/gi.test(u)) {
    // 引导用户在浏览器中打开
    alert("请在浏览器中打开");
    return false;
  }
  openApp(jumpLink);
  const delay = setTimeout(() => {
    window.location.href = download;
  }, 3000);

  window.addEventListener("pagehide", () => {
    clearTimeout(delay);
    alert("123");
  });

  function openApp(src) {
    // 通过iframe的方式试图打开APP，如果能正常打开，会直接切换到APP，并自动阻止a标签的默认行为
    // 否则打开a标签的href链接

    if (getSys() === "ios") {
      window.location.href = src;
    } else {
      const ifr = document.createElement("iframe");
      ifr.src = src;
      ifr.style.display = "none";
      document.body.appendChild(ifr);
      window.setTimeout(() => {
        document.body.removeChild(ifr);
      }, 2000);
    }
  }
}

/**
 * 版本判断
 * @returns {boolean}
 */
export function appVersion() {
  let header =
    window.nativeMethod &&
    window.nativeMethod.returnNativeMethod &&
    nativeMethod.getAppAttributes();
  let appVersion;
  if (header) {
    header = JSON.parse(header);
    console.log(`****** appVersion:${header.appVersion}`);
    appVersion = header.appVersion;
  }
  if (appVersion) {
    const Num = Number(appVersion.substring(0, 3));
    return Num < 2;
  }
  return false;
}

/**
 * 设置类名
 * @param params
 * @returns {string}
 */
export function setClassName(arr) {
  return arr.join(" ");
}

/**
 * 往请求链接里面塞入参数(不带？)
 * @param params  参数
 * @returns {string}
 */
export function setParamsInUrl(params) {
  let str = "";
  for (let i in params) {
    str += `${(!!str && "&") || ""}${i}=${params[i]}`;
  }
  return str;
}

/**
 * 输入框内容格式化
 * @param {*} value  内容
 */
export function inputForMat(value) {
  value = value.replace(/\s+/g, "").replace(/[0-9]{4}/g, "$& ");

  if (value.substring(value.length - 1) === " ") {
    value = value.replace(/\s$/, "");
  }
  return value;
}

/**
 * 浮点数计算
 * @param {*} value
 * @param {*} type add / sub
 */
export function getNum(value, type) {
  const [int, float] = String(value).split(".");
  const v = type === "sub" ? Number(int) - 100 : Number(int) + 100;
  return v + Number("0." + (float ? float : 0));
}
/**
 * base64加密
 * @param {*} url
 * @param {*} params
 */
export function setUrlParams(url, params) {
  let text = "";
  Object.keys(params).map((key) => {
    const val = Base64.encode(params[key]);
    text += text === "" ? `${key}=${val}` : `&${key}=${val}`;
  });
  return /\?$/g.test(url) ? `${url}${text}` : `${url}?${text}`;
}