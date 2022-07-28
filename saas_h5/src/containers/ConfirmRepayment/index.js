/*
 * @Author: Always
 * @LastEditors: Always
 * @Date: 2020-04-16 16:10:43
 * @LastEditTime: 2021-01-26 11:05:23
 * @FilePath: /saas_h5/src/containers/ConfirmRepayment/index.js
 */
import { useRef } from "react";
import { Base64 } from "js-base64";
import { getUrlData } from "../../utils/utils";
import "../../checkout";
import "./index.less";
import { nativeType } from "../../nativeMethod";
import { orderCheck } from "../../api";
import line from "../../images/confirmRepayment/line.png";
import NextClick from "../../components/Next-click";
import { useDocumentTitle } from "../../hooks";

/**
 * @param (query) repaymentMethod razorpay mpurse jpay
 * @param (query) amount
 *
 * razorpay还款
 * @param (query) orderId
 * @param (query) key
 * @param (query) email
 * @param (query) contact
 * @param (query) method
 *
 *  mpurse还款
 * @param (query) hash
 * @param (query) partnerId
 * @param (query) mpQueryId
 * @param (query) txnId
 *
 *
 * jpay还款
 * @param (query) app_key
 * @param (query) business_order_num
 * @param (query) amount
 * @param (query) firstname
 * @param (query) phone
 * @param (query) productinfo
 * @param (query) surl
 * @param (query) furl
 * @param (query) sign
 */
const ConfirmRepayment = (props) => {
  useDocumentTitle(props);
  const encodeParams = getUrlData(props);
  const mpurseRef = useRef();
  const jpayRef = useRef();
  const mpurseRequestUrl =
    process.env.REQUEST_ENV === "production"
      ? "https://api.mpursewallet.com"
      : "https://stg.mpursewallet.com";
  let data = {};

  Object.keys(encodeParams).map((key) => {
    data[key] = Base64.decode(encodeParams[key]);
  });

  const razorpay = () => {
    const options = {
      key: data.key,
      amount: data.amount,
      currency: "INR",
      prefill: {
        email: data.email || "",
        contact: data.contact || "",
        method: data.method || "",
      },
      order_id: data.orderId,
      handler() {
        const {
          razorpay_payment_id,
          razorpay_order_id,
          razorpay_signature,
        } = response;
        console.log(response);
        orderCheck(
          razorpay_payment_id,
          razorpay_order_id,
          razorpay_signature
        ).then((resData) => {
          if (resData.code === 0) {
            if (resData.data.isSuccess) {
              Toast.info("repayment success");
              const timer = setTimeout(() => {
                nativeType({
                  path: "/app/back",
                });
                clearTimeout(timer);
              }, 1000);
            } else {
              Toast.info("repayment failed, please try again");
            }
          }
        });
      },
      theme: {
        hide_topbar: true,
      },
    };
    const rzp = new Razorpay(options);
    rzp.open();
  };

  const mpursePay = () => {
    if (mpurseRef.current) {
      mpurseRef.current.submit();
    }
  };

  const jPay = () => {
    if (jpayRef.current) {
      jpayRef.current.submit();
    }
  };

  function pay() {
    switch (data.repaymentMethod) {
      case "mpurse":
        mpursePay();
        break;
      case "jpay":
        jPay();
        break;
      default:
        razorpay();
    }
  }

  return (
    <div className="confirm-repayment-wrapper">
      <div className="confirm-repayment-info">
        <p className="title">Your Repayment Amount</p>
        <h1 className="amount">₹ {data.amount}</h1>
        <img src={line} alt="" className="line" />
        <NextClick className="next on" clickFn={pay} text="REPAY NOW" />
      </div>
      {/* mpurse还款 */}
      {data.repaymentMethod === "mpurse" && (
        <form
          method="post"
          action={`${mpurseRequestUrl}/api/easyPay/buildEasyPayHash`}
          name="mpurse"
          ref={mpurseRef}
        >
          <input type="hidden" name="txnId" value={data.txnId} />
          <input type="hidden" name="partnerId" value={data.partnerId} />
          <input type="hidden" name="hash" value={data.hash} />
        </form>
      )}
      {data.repaymentMethod === "jpay" && (
        <form
          method="post"
          action={`https://api.jpayhome.com/app/open/collection/businessCollectionMoney`}
          name="jpay"
          ref={jpayRef}
        >
          <input type="hidden" name="app_key" value={data.app_key} />
          <input
            type="hidden"
            name="business_order_num"
            value={data.business_order_num}
          />
          <input type="hidden" name="amount" value={data.amount} />
          <input type="hidden" name="firstname" value={data.firstname} />
          <input type="hidden" name="phone" value={data.phone} />
          <input type="hidden" name="productinfo" value={data.productinfo} />
          <input type="hidden" name="surl" value={data.surl} />
          <input type="hidden" name="furl" value={data.furl} />
          <input type="hidden" name="sign" value={data.sign} />
        </form>
      )}
    </div>
  );
};

export default ConfirmRepayment;
