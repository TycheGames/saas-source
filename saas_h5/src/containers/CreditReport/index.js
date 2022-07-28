/**
 * Created by yaer on 2019/7/19;
 * @Email 740905172@qq.com
 * */
import { useState, useEffect } from "react";
import "./index.less";
import { Toast } from "antd-mobile";
import VerificationCode from "../../components/VerificationCode";
import NextClick from "../../components/Next-click";
import AuthProgress from "../../components/Auth-progress";
import { setClassName } from "../../utils/utils";
import ShowPage from "../../components/Show-page";
import { nativeCustomMethod, pageJump } from "../../nativeMethod";
import { getCreditReport, getCreditReportOTP, saveCreditReport } from "../../api";
import { useDocumentTitle } from "../../hooks";

const CreditReport = (props) => {
  useDocumentTitle(props);
  const [showPage, setShowPage] = useState(true);
  const [data, setData] = useState({
    pan_code: "123",
    full_name: "name",
    phone_number: "989123123",
  });

  const [inputData, setInputData] = useState({
    otp: "",
    code: "",
  });

  useEffect(() => {
    getData();
    nativeCustomMethod("onShow", () => "htmlOnShow");
    window.htmlOnShow = () => {
      getData();
    };
    getCodeFn();
  }, []);

  return (
    <ShowPage show={showPage}>
      <div className="credit-report-wrapper">
        <AuthProgress step={6} stepMsg="credit report" />
        <ul>
          <li>
            <span className="label">pan code</span>
            <span className="value">{data.pan_code}</span>
          </li>
          <li>
            <span className="label">full name</span>
            <span className="value">{data.full_name}</span>
          </li>
          <li>
            <span className="label">phone number</span>
            <span className="value">{data.phone_number}</span>
          </li>
          <li>
            <input
              onChange={(e) => {
                setInputData(Object.assign({}, inputData, { otp: e.target.value }));
              }}
              value={inputData.otp}
              type="number"
              placeholder="verification code" />
            <span>
              <VerificationCode getCodeFn={getCodeFn} />
            </span>
          </li>
        </ul>
        <NextClick
          className={setClassName([
            "next",
            inputData.code ? "on" : "",
          ])}
          clickFn={nextClick} />
      </div>
    </ShowPage>
  );

  function getData() {
    getCreditReport().then((res) => {
      if (res.code === 0) {
        setData(res.data);
        setShowPage(true);
      }
    });
  }

  /**
   * 获取验证码点击
   * @param countDownFn 验证码倒计时方法
   */
  function getCodeFn(countDownFn) {
    getCreditReportOTP().then((res) => {
      if (res.code === 0) {
        setInputData(Object.assign({}, inputData, {
          code: res.data.code,
        }));
        countDownFn && countDownFn();
      }
    });
  }

  /**
   * 下一步
   */
  function nextClick() {
    if (inputData.code && inputData.otp) {
      saveCreditReport(inputData).then((res) => {
        if (res.code === 0) {
          if (res.data) {
            pageJump(res.data);
          }
        }
      });
    } else {
      Toast.info("please enter the verification code");
    }
  }
};

export default CreditReport;
