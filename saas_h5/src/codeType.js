/**
 * http状态码定义
 * Created by yaer on 2018/12/12;
 * @Email 740905172@qq.com
 * */
import {Toast} from "antd-mobile";
import {goLogin} from "./nativeMethod";

/**
 * code :
 *  0 成功
 *  -1  提示
 *  -2  登录态失效
 */
/**
 * 状态码处理
 * @param code
 * @param message
 * @returns {Promise<any>}
 */
export default function (code, message) {
  return new Promise((res) => {
    if (code === -1) {
      if (message.length < 20) {
        Toast.info(message);
      } else {
        Toast.info(message, 5, null, false);
      }

      return;
    }

    if (code === -2) {
      goLogin();
      return;
    }

    if (code === 0) {
      res();
      return;
    }

    Toast.info("server error!");

  });
}
