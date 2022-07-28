/**
 * Created by yaer on 2019/7/22;
 * @Email 740905172@qq.com
 * */
import { connect } from "react-redux";
import { bindActionCreators } from "redux";
import { useState, useEffect } from "react";
import "./index.less";
import { Toast } from "antd-mobile";
import VerificationCode from "../../components/VerificationCode";
import {
  setTaxBillInputCompanyInfo, taxBillCheckCode, setTaxBillCompanyData, getTaxBillLoopStatus, 
} from "../../api";
import SelectModal from "../../components/Select-modal";
import { setClassName } from "../../utils/utils";
import NextClick from "../../components/Next-click";
import { pageJump } from "../../nativeMethod";


import * as actions from "../../actions";
import { useDocumentTitle } from "../../hooks";
import AuthProgress from "../../components/Auth-progress";
import loading from "../../images/loading.gif";

const TaxBillSelectCompany = (props) => {
  useDocumentTitle(props);
  const { isTaxBillRepeatInput, isTaxBillRepeatInputTwo, action } = props;
  const {
    setIsTaxBillRepeatInput, setIsTaxBillRepeatInputTwo, setReportId, setCompanyData, 
  } = action;


  const [data, setData] = useState({});

  const [otp, setOtp] = useState("");

  const [companyList, setcompanyList] = useState([]);

  const [modalType, setModalType] = useState(false);

  const [showLoading, setShowLoading] = useState(false);

  const [nextBtnType, setNextBtnType] = useState(true);

  // 定时器名
  let timer = null;
  let num = 1; // 轮询次数 最大36次

  useEffect(() => {
    if (companyList.length) {
      setModalType(true);
    }
  }, [companyList]);

  return (
    <div className="tax-bill-select-company-wrapper">
      <AuthProgress step={5} stepMsg="Basic info" />
      <ul>
        <li>
          <input
            onChange={(e) => {
              setOtp(e.target.value);
            }}
            value={otp}
            type="number"
            placeholder="please enter the verification code" />
          <span>
            <VerificationCode getCodeFn={getCodeFn} />
          </span>
        </li>
      </ul>


      <NextClick
        className={setClassName([
          "next",
          !(otp.length) ? "" : "on",
        ])}
        clickFn={nextClick} />


      {/* 选择模态框 */}
      <SelectModal
        confirmDataClickFn={modalConfirm}
        modal_list={companyList}
        modal_title="select company"
        isShowCancelDataClick={false}
        modal_type={modalType} />
      <div className="loading-wrapper" style={{ display: showLoading ? "block" : "none" }}>
        <img src={loading} alt="" />
      </div>

    </div>
  );

  /**
   * 轮询查税单状态
   */
  function getLoopStatus() {
    if (num > 36) {
      clearTimeout(timer);
    }
    timer && clearTimeout(timer);
    getTaxBillLoopStatus({ reportId: props.reportId }).then((res) => {
      num++;
      const { isLoop, message } = res.data;
      // 判断是否继续轮询
      if (isLoop) {
        timer = setTimeout(() => {
          getLoopStatus();
        }, 5000);
        return;
      }
      // 判断是否出错
      if (message) {
        Toast.info(message);
        setReportId("");
        setCompanyData({});

        const msgTimer = setTimeout(() => {
          props.history.go(-1);
          clearTimeout(msgTimer);
        }, 1000);

        return;
      }
      // 判断是否需要重复输入 (需要重复输入信息&& !(重复输入过))
      if (isTaxBillRepeatInput && !(isTaxBillRepeatInputTwo)) {
        props.history.go(-1);
        // 设置重复输入过
        setIsTaxBillRepeatInputTwo(true);
      } else {
        pageJump(res.data);
      }
    });
  }

  /**
   * 获取验证码点击
   * @param countDownFn 验证码倒计时方法
   */
  function getCodeFn(countDownFn) {
    if (Object.keys(props.companyData).length) {
      setTaxBillInputCompanyInfo(props.companyData).then((res) => {
        if (res.code === 0) {
          countDownFn();
        }
      });
    }
  }

  /**
   * 确定按钮
   * @param selectData  选中后的数据
   * @param list  选中后的数组
   */
  function modalConfirm(selectData, list) {
    setTaxBillCompanyData({ reportId: props.reportId, companyName: selectData.label, companyId: selectData.id }).then((res) => {
      if (res.code === 0) {
        setModalType(false);
        setShowLoading(true);
        getLoopStatus();
      }
    });
  }

  function nextClick() {
    taxBillCheckCode({ otp, reportId: props.reportId }).then((res) => {
      if (res.code === 0) {
        const len = res.data.companyList.length;
        // 代表输入信息有误，清空信息重新认证
        if (len === 0) {
          setReportId("");
          setCompanyData({});
          props.history.go(-1);
        } else if (len === 1) {
          // 执行轮询的接口
          setShowLoading(true);
          getLoopStatus();
        } else {
          // 设置公司数组
          setcompanyList(res.data.companyList);
        }
      }
    });
  }
};

function mapStateToProps(state) {
  return {
    isTaxBillRepeatInput: state.taxBillRepeatInput,
    isTaxBillRepeatInputTwo: state.taxBillRepeatInputTwo,
    reportId: state.reportId,
    companyData: state.companyData,
  };
}

function mapDispatchToProps(dispatch) {
  return {
    action: bindActionCreators(actions, dispatch),
  };
}


export default connect(mapStateToProps, mapDispatchToProps)(TaxBillSelectCompany);
