/**
 * 审核状态页
 * Created by yaer on 2019/8/20;
 * @Email 740905172@qq.com
 * */

import { useState, useEffect } from "react";
import { Toast } from "antd-mobile";
import { useDocumentTitle, useRefreshStatus } from "../../hooks";
import { getUrlData } from "../../utils/utils";
import { nativeCustomMethod, nativeType } from "../../nativeMethod";

import inProgress from "../../images/audit/in_progress.png";
import pendingRepayment from "../../images/audit/pending_repayment.png";

import SelectBankCard from "../../components/Select-bank-card";
import NextClick from "../../components/Next-click";
import ShowPage from "../../components/Show-page";

import { getBankAccountNumber, orderBindCard } from "../../api";

import { background } from "../../vest";
import "./index.less";

/**
 *
 * @param props
 * params
 *  status
 *    0 审核中
 *    1 绑卡/绑卡失败
 *    2 打款中
 *    3 待还款
 * @returns {*}
 * @constructor
 * query
 *  id  订单id
 */

const Audit = props => {
  useDocumentTitle(props);

  const { id } = getUrlData(props); // 订单id
  const { packageName } = window.appInfo;

  // 轮询查询订单状态
  useRefreshStatus(id);

  // 银行卡列表相关内容
  const [showSelectBank, setShowSelectBank] = useState(false); // 显示选择银行卡列表

  // status  0未验证 1可用 -1不可用
  const [bankCardList, setBankCardList] = useState([
    /*{
      "id": 3,
      "account": "916010060563575",
      "ifsc": "UTIB0000005",
      "status": 0
    }*/
  ]);

  const [selectBankCardData, setSelectBankCardData] = useState(null); // 选择的银行卡

  const [showAddBankCard, setShowAddBankCard] = useState(true); // 显示添加银行卡

  const status = Number(props.match.params.status);

  const { msg, btnText, img, title, btnIcon } = getParams(status);

  useEffect(() => {
    if (selectBankCardData) {
      BindCard(selectBankCardData.id);
    }
  }, [selectBankCardData]);

  useEffect(() => {
    nativeCustomMethod("onShow", () => "htmlOnShow");
    window.htmlOnShow = async () => {
      if (status === 1) {
        const BANK_ACCOUNT_CARD_ID = window.localStorage.getItem(
          "BANK_ACCOUNT_CARD_ID"
        );
        if (BANK_ACCOUNT_CARD_ID) {
          BindCard(BANK_ACCOUNT_CARD_ID);
          window.localStorage.removeItem("BANK_ACCOUNT_CARD_ID");
        }
      }
    };

    getBankCardList();
  }, []);

  return (
    <div className="audit-wrapper">
      <img src={img} alt="" />

      <h1 className="title">{title}</h1>

      <p className="msg" dangerouslySetInnerHTML={{ __html: msg }} />

      <ShowPage show={!status}>
        <div className="india-text">
          आपके आवेदन की समीक्षा चल रही है। हम आपको एसएमएस और अधिसूचना द्वारा
          सूचित करेंगे। कृपया अपने एसएमएस पर ध्यान दें और 1~3 मिनट के बाद अपना
          परिणाम देखें। एक बार जब आपका ऋण आवेदन स्वीकृत हो जाता है, तो कृपया नकद
          वापस लेने के लिए {packageName} खोलें। उसके बाद, पैसा सीधे आपके बैंक खाते को
          स्थानांतरित कर देगा।
        </div>
      </ShowPage>

      <NextClick
        clickFn={btnClick}
        className="next on"
        text={btnText}
        style={background()}
        title={title}
        btnIcon={btnIcon}
      />

      {/*选择银行卡*/}
      <SelectBankCard
        {...props}
        showNoUseBtn={false}
        show={showSelectBank}
        changeShow={type => setShowSelectBank(type)}
        bankCardList={bankCardList}
        defaultSelectBankCardId={null}
        selectBankCardFn={selectBankCardFn}
        showAddBtn={showAddBankCard}
      />
    </div>
  );

  /**
   * 选中的银行卡回调
   * @param data  银行卡数据
   */
  function selectBankCardFn(data) {
    setSelectBankCardData(data);
  }

  /**
   * 获取银行卡列表
   */
  function getBankCardList() {
    return new Promise(resolve => {
      getBankAccountNumber().then(res => {
        if (res.code === 0) {
          setShowAddBankCard(res.data.showAddBankCard);
          if (res.data.list.length) {
            setBankCardList(res.data.list);
            resolve(res.data.list);
          }
        }
      });
    });
  }

  /**
   * 获取当前状态的返回值
   * @param type  状态
   * @returns {{msg: string, btnText: string, img: string}}
   */
  function getParams(type) {
    let msg = "";
    let btnText = "";
    let img = "";
    let title = "";
    let btnIcon = "";
    switch (type) {
      case 0:
        title = "Under Review";
        msg = `<p>Your application is under review. We will notify you by SMS and Notification. Please pay attention to your SMS and <span style="color:#D0021B;font-weight:bold;">check your result after 1~3 minutes</span>.</p>
            <br />
          <p>Once your loan application has been approved, please <span style="color:#D0021B;font-weight:bold;">open ${packageName} to withdraw cash</span>. After withdraw, the money will directly transfer your bank account.</p>
          `;
        btnText = "REFRESH";
        img = require("../../images/audit/under_review.png");
        btnIcon = "icon-shuaxin";
        break;
      case 1:
        title = "Pls Bind Valid Bank Card";
        msg = `<p>You are just almost there! Please bind a <span style="color:#D0021B;font-weight:bold;">Valid Bank Card</span> which under <span style="color:#D0021B;font-weight:bold;">Your Name</span>. This Bank Card will be used to receive the disbursement that from our platform. </p>`;
        btnText = "ADD BANK CARD";
        img = require("../../images/audit/bind_card_icon.png");
        btnIcon = "";
        break;
      case 2:
        title = "Waiting for Disbursement";
        msg = `<p>Congratulations on passing the initial review! We will disburse the loan amount to you now.</p><br /><p>Meanwhile, we will notify you by SMS when the disbursemnet is successful. If the disbursment is unsuccessful, please submit the application again!</p>`;
        btnText = "VIEW ORDER STATUS";
        img = require("../../images/audit/in_lending_icon.png");
        btnIcon = "";
        break;
      case 3:
        title = "";
        msg = "Please repay the loan in time so as not to affect your credit.";
        btnText = "repayment";
        img = pendingRepayment;
        btnIcon = "";
        break;
      default:
        title = "";
        msg = "";
        btnText = "";
        img = "";
        btnIcon = "";
    }
    return { msg, btnText, img, title, btnIcon };
  }

  function btnClick() {
    if (status === 1) {
      if (bankCardList && bankCardList.length) {
        setShowSelectBank(true);
      } else {
        nativeType({
          path: "/h5/webview",
          url: `${window.originPath}bankAccountInfo/1?from=index`,
          isFinshPage: false
        });
      }
      return;
    }
    if (status === 0) {
      _refreshTabBar();
      return;
    }

    let url = "";
    switch (status) {
      case 0:
        url = `${window.originPath}orderDetail/${id}`;
        break;
      case 1:
        url = `${window.originPath}bankAccountInfo/1?from=addBank&id=${id}`;
        break;
      case 2:
        url = `${window.originPath}orderDetail/${id}`;
        break;
      case 3:
        url = `${window.originPath}orderDetail/${id}?autoRepayment=1`;
        break;
      default:
        url = "";
    }

    if (!url) return;

    nativeType({
      path: "/h5/webview",
      url,
      isFinshPage: false
    });
  }

  /**
   * 原本的功能按钮改为确认按钮
   */
  function BindCard(bankCardId) {
    if (status === 1) {
      orderBindCard({ orderId: id, bankCardId }).then(res => {
        if (res.code === 0) {
          Toast.info("Bind Success");
          const timer = setTimeout(() => {
            nativeType({ path: "/main/refresh_tablist" });
            clearTimeout(timer);
          }, 1000);
        }
      });
    }
  }

  /**
   * 刷新客户端tabbar
   */
  function _refreshTabBar() {
    nativeType({ path: "/main/refresh_tablist" });
  }
};

export default Audit;
