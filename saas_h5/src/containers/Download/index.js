/*
 * @Author: Always
 * @LastEditors  : Always
 * @email: 740905172@qq.com
 * @Date: 2019-11-12 18:06:10
 * @LastEditTime : 2019-12-31 11:12:24
 * @FilePath: /saas_h5/src/containers/Download/index.js
 */
/**
 * Created by yaer on 2019/9/6;
 * @Email 740905172@qq.com
 * */
import { useDocumentTitle } from "../../hooks";
import { nativeType } from "../../nativeMethod";
import NextClick from "../../components/Next-click";

import "./index.less";

import downloadIcon from "../../images/download/download_icon.png";
/**
 * 提示下载
 * @param props
 * @constructor
 */
const Download = props => {
  useDocumentTitle(props);
  const {packageName} = window.appInfo;
  return (
    <div className="download-wrapper">
      <div className="container">
        <img src={downloadIcon} alt="" />
        <p>Dear Customer,</p>
        <br />
        <p>
          Our app has been updated and renamed as {packageName}! We noted that your
          current version is out of date.
        </p>
        <br />
        <p>
          Please go to <i>Google Play</i> download <br/><i>[ {packageName} ]</i> now and get your
          <span> ₹100,000</span> in credit account as soon as possible!
        </p>
      <NextClick className="next on" clickFn={btnClick} text="UPDATE NOW" />
      </div>
    </div>
  );

  function btnClick() {
    nativeType({
      path: "/app/open_browser",
      url: window.appInfo.googleLink,
      isFinishPage: true
    });
  }
};

export default Download;
