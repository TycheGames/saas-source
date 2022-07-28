/**
 * Created by yaer on 2019/9/20;
 * @Email 740905172@qq.com
 * */

import {useState, useEffect} from "react";

import VerificationCode from "../../components/VerificationCode";
import NextClick from "../../components/Next-click";

import "./index.less";
import {setClassName} from "../../utils/utils";
import {useDocumentTitle} from "../../hooks";

import {Toast} from "antd-mobile";

import {getEkycInfo, getEkycOTP, submitEkycInfo} from "../../api";
import {nativeCustomMethod, pageJump} from "../../nativeMethod";

const EkycInfo = props => {

  useDocumentTitle(props);

  const [data, setData] = useState({
    name: "",
    aadhaarNum: "",
  });

  const [optNum, setOptNum] = useState("");

  const [transactionId, setTransactionId] = useState("");

  const [isNext, setIsNext] = useState(false);


  useEffect(() => {

    setIsNext(_checkData());

  }, [data, optNum, transactionId]);

  useEffect(() => {
    nativeCustomMethod("onShow", () => "htmlOnShow");
    window.htmlOnShow = () => {
      getData();
    };
    getData();
  },[]);

  return (
    <div className="ekyc-info-wrapper">
      <div className="item-group">
        <div className="item">
          <span className="label">Name</span>
          <input
            className="value"
            value={data.name}
            placeholder="input name"
            onChange={e => setData(Object.assign({}, data, {name: e.target.value}))}
          />
        </div>
        <div className="item">
          <span className="label">Aadhaar Number</span>
          <input
            className="value"
            value={data.aadhaarNum}
            placeholder="aadhaar No."
            onChange={e => setData(Object.assign({}, data, {aadhaarNum: e.target.value}))}
          />
        </div>
      </div>
      <div className="item-group">
        <div className="item">
          <input
            className="label opt"
            type="number"
            value={optNum}
            placeholder="OTP Number"
            onChange={e => setOptNum(e.target.value)}
          />
          <div className="value">
            <VerificationCode
              getCodeFn={getCodeFn}
            />
          </div>
        </div>
      </div>

      <NextClick
        className={setClassName([
          "next",
          isNext ? "on" : ""
        ])}
        text="SUBMIT"
        clickFn={nextClickFn}
      />
    </div>
  );

  function getData() {
    getEkycInfo().then(res => {
      if (res.code === 0) {
        setData(res.data);
      }
    })
  }

  /**
   * 倒计时
   * @param cb
   */
  function getCodeFn(cb) {
    getEkycOTP(data).then(res => {
      if (res.code === 0) {
        setTransactionId(res.data.transactionId);
        cb();
      }
    });
  }

  /**
   * 提交
   */
  function nextClickFn() {
    submitEkycInfo({transactionId, optNum}).then(res => {
      Toast.success("save success");
      if (!res.data) return;
      const timer = setTimeout(() => {
        pageJump(res.data);
        clearTimeout(timer);
      }, 1000);
    })

  }

  /**
   * 校验数据是否正确
   * @returns {boolean}
   * @private
   */
  function _checkData() {
    let canNext = true; // 默认没有错误
    for (let key in data) {
      if (!data[key]) {
        canNext = false;
        return;
      }
    }

    return canNext && transactionId && optNum;
  }
};

export default EkycInfo;