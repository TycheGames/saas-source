/*
 * @Author: Always
 * @LastEditors: Always
 * @Date: 2020-04-23 14:55:27
 * @LastEditTime: 2020-04-23 15:09:21
 * @FilePath: /saas_h5/src/containers/Withdrawals/component/RepaymentData/index.js
 */

const RepaymentData = ({ data, close, amount }) => {
  return (
    <div className="repayment-modal">
      <div className="title">
        <h1>Repayment Details</h1>
        <div className="icon-wrapper">
          <i onClick={() => close(false)} className="iconfont icon-guanbi" />
        </div>
      </div>
      <ul>
        <li>
          <div>
            <span>Repayment Amount</span>
            <span>₹ {data.repaymentAmount}</span>
          </div>
        </li>
        <li>
          <div>
            <span>Principal Amount</span>
            <span>₹ {data.repaymentDetail.principalAmount || 0}</span>
          </div>
        </li>
        <li>
          <div>
            <span>Disbursal Amount</span>
            <span>₹ {amount}</span>
          </div>
        </li>
        <li>
          <div>
            <span>Total Interest</span>
            <span>₹ {data.repaymentDetail.interest}</span>
          </div>
        </li>
        <li>
          <div>
            <span>Processing Fee</span>
            <span>₹ {data.repaymentDetail.fee}</span>
          </div>
        </li>
        <li>
          <div>
            <span>GST(18.0% on Processing Fee)</span>
            <span>₹ {data.repaymentDetail.gst}</span>
          </div>
        </li>
      </ul>
    </div>
  );
};

export default RepaymentData;
