/* eslint-disable */
/**
 * Created by yaer on 2018/10/31;
 * @Email 740905172@qq.com
 * */
import { getSys } from "./utils/utils";
import * as clipboard from "clipboard-polyfill";
import { Toast } from "antd-mobile";

/**
 * 调用客户端指令方法
 * @param params  指令信息
 */
export function nativeType(params) {
  console.log(params);
  if (typeof params !== "object") {
    throw new Error("请传递对象信息");
  }
  window.nativeMethod &&
    window.nativeMethod.returnNativeMethod &&
    nativeMethod.returnNativeMethod(JSON.stringify(params));
}

/**
 * 打开登录页面
 */
export function goLogin() {
  window.ORDER_DETAIL_TIMER && clearInterval(window.ORDER_DETAIL_TIMER);
  nativeType({ path: "/user/login" });
}

/**
 * 客户端自定义方法
 * @param methodName  方法名
 * @param callback  方法操作回调
 */
export function nativeCustomMethod(methodName, callback) {
  if (!methodName || methodName === "") {
    throw new Error("请传递方法名");
  }
  if (!callback) {
    throw new Error("请设置回调方法");
  }
  let val = callback();
  val = typeof val === "string" ? [val] : val;

  window.nativeMethod &&
    window.nativeMethod[methodName] &&
    nativeMethod[methodName](...val);
}

/**
 * 客户端自定义方法（不带参数直接执行的方法）
 * @param methodName  方法名
 */
export function nativeCustomMethodNoAGS(methodName) {
  return (
    window.nativeMethod &&
    window.nativeMethod[methodName] &&
    nativeMethod[methodName]()
  );
}

/**
 * ios以及安卓的兼容参数处理
 * @param adrParams 安卓参数
 * @param iosParams ios参数
 */
export function nativeCompatible(adrParams, iosParams) {
  if (!adrParams || !iosParams) {
    throw new Error("必须传递安卓以及ios参数");
  }
  return getSys() === "ios" ? iosParams : adrParams;
}

/**
 * 获取客户端getAppAttributes方法  async/await方式调用
 * @returns {Promise<any>}
 */
export function getAppAttributes() {
  return JSON.parse(nativeCustomMethodNoAGS("getAppAttributes") || "{}");
}

/**
 * 设置页面标题
 * @param title
 */
export function setTitle(title) {
  const timer = setTimeout(() => {
    nativeCustomMethod("setWebNavigationTitle", () => title);
    clearTimeout(timer);
  }, 1000);
  document.title = title;
}

/**
 * 页面跳转
 * @param (data)  type  h5/client
 * @param (data)  jump  object
 * @param (data)  link  h5 link
 */
export function pageJump(data) {
  if (data.type === "h5") {
    nativeType({
      path: "/h5/webview",
      url: data.link,
      isFinshPage: false
    })
  } else {
    nativeType(JSON.parse(data.jump));
  }
}

/**
 * 判断客户端方法是否存在
 * @param funcName
 * @returns {*|undefined}
 */
export function checkFunc(funcName) {
  return window.nativeMethod && window.nativeMethod[funcName];
}

/**
 * 复制方法
 * @param text
 * @param tip  复制成功提示
 */
export function copy(text, tip) {
  let nativeCopy = checkFunc("copyTextMethod");

  // 判断客户端copy是否存在
  if (nativeCopy) {
    nativeCustomMethod("copyTextMethod",()=>[text,tip])
  } else {
    clipboard.writeText(text).then(() => {
      Toast.info(tip);
    });
  }
}
