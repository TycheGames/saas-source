/*
 * @Author: Always
 * @LastEditors: Always
 * @email: 740905172@qq.com
 * @Date: 2019-11-26 16:51:24
 * @LastEditTime: 2020-12-14 18:49:06
 * @FilePath: /saas_h5/src/config/index.js
 */
import { getAppAttributes } from "../nativeMethod";
import { replaceAppName } from "../vest";

export default () => {
  const { pathname, origin } = window.location;

  window.originPath = `${origin + pathname}#/`;

  let data = getAppAttributes();
  // data.packageName = "happywallet";
  const {
    packageName,
    email,
    company,
    website,
    address,
    googleLink,
    logo,
    contactList,
  } = replaceAppName(data.packageName);
  window.appInfo = Object.assign({}, data, {
    packageName,
    email,
    company,
    website,
    address,
    googleLink,
    logo,
    contactList,
  });
};
