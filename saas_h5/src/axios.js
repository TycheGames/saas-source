/* eslint-disable */
import axios from "axios";
import Qs from "qs";
import { Toast } from "antd-mobile";
import codeType from "./codeType";
import { getAppAttributes, nativeCustomMethodNoAGS } from "./nativeMethod";
import { setParamsInUrl } from "./utils/utils";

// 设置全局axios默认值
axios.defaults.timeout = 30000; // 20000的超时验证
axios.defaults.withCredentials = true; // 跨域带cookie

export default function http(
  type,
  url,
  params,
  contentType,
  showLoading = true
) {
  const appInfo = JSON.parse(
    nativeCustomMethodNoAGS("getHeadersContent") ||
      JSON.stringify({ packageName: "rupeeplus" })
  );

  showLoading && Toast.loading("Loading", 0);
  const contentTypeUse =
    contentType === "json"
      ? "application/json"
      : "application/x-www-form-urlencoded";
  const paramsUse = contentType === "json" ? params : Qs.stringify(params);

  const requestParams = {
    method: type,
    headers:
      type === "get"
        ? Object.assign({}, appInfo)
        : Object.assign(
            {},
            {
              "Content-Type": contentTypeUse
            },
            appInfo
          ),
    url,
    [type === "get" ? "params" : "data"]: type === "get" ? params : paramsUse
  };

  for (const key in requestParams) {
    if (key === "" || !requestParams[key]) {
      Reflect.deleteProperty(requestParams, key);
    }
  }
  return new Promise((resolve, reject) => {
    axios(requestParams)
      .then(res => {
        showLoading && Toast.hide();
        codeType(res.data.code, res.data.message).then(() => {
          resolve(res.data);
        });
      })
      .catch(err => {
        showLoading && Toast.hide();
        codeType(-1, "server connection timed out!");
        // reject(err);
      });
  });
}

export function biHttp(type, url, params, contentType) {
  const contentTypeUse =
    contentType === "json"
      ? "application/json"
      : "application/x-www-form-urlencoded";
  const paramsUse = contentType === "json" ? params : Qs.stringify(params);
  const userData = {};

  const requestParams = {
    method: type,
    headers:
      type === "get"
        ? userData
        : Object.assign({}, { "Content-Type": contentTypeUse }, userData),
    url,
    [type === "get" ? "params" : "data"]: type === "get" ? params : paramsUse
  };

  for (const key in requestParams) {
    if (key === "" || !requestParams[key]) {
      Reflect.deleteProperty(requestParams, key);
    }
  }

  return new Promise(resolve => {
    axios(requestParams)
      .then(() => {
        resolve();
      })
      .catch(() => {
        resolve();
      });
  });
}

/**
 * 文件数据上传方法
 * @param type  传递类型
 * @param url  接口地址
 * @param params  数据<FormData>
 * @returns {Promise<any>}
 */
export function formDataHttp(type, url, params) {
  Toast.loading("Loading", 20);

  let userData = {};

  let requestParams = {
    method: type,
    headers:
      type === "get"
        ? userData
        : Object.assign(
            {},
            { "Content-Type": "multipart/form-data" },
            userData
          ),
    url,
    [type === "get" ? "params" : "data"]: type === "get" ? params : params
  };

  for (let key in requestParams) {
    if (key === "" || !requestParams[key]) {
      Reflect.deleteProperty(requestParams, key);
    }
  }

  return new Promise((resolve, reject) => {
    axios(requestParams)
      .then(res => {
        Toast.hide();
        codeType(res.data.code, res.data.message).then(() => {
          resolve(res.data);
        });
      })
      .catch(err => {
        Toast.hide();
        codeType(-1, "获取数据失败");
        reject(err);
      });
  });
}
