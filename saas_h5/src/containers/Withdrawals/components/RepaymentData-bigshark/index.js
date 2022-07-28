/*
 * @Author: Always
 * @LastEditors: Always
 * @Date: 2020-04-23 14:54:12
 * @LastEditTime: 2020-05-14 16:07:53
 * @FilePath: /saas_h5/src/containers/Withdrawals/component/RepaymentData-bigshark/index.js
 */

const RepaymentDataBigShark = ({ data, close, amount }) => {
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
          <p>
            {amount}+{data.repaymentDetail.fee}+{data.repaymentDetail.interest}+
            {data.repaymentDetail.gst} =&nbsp;
            {data.repaymentAmount}
          </p>
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
            <span>Annual Interest Rate</span>
            <span>{(Number(data.dailyInterest) * 365).toFixed(2)}%</span>
          </div>
          <p>
            {data.dailyInterest}*365 =
            {(Number(data.dailyInterest) * 365).toFixed(2)}%
          </p>
        </li>
        <li>
          <div>
            <span>Processing Fee</span>
            <span>₹ {data.repaymentDetail.fee}</span>
          </div>
          <p>
            {data.repaymentAmount}*{data.dailyInterest}%*{data.duration} ={" "}
            {data.repaymentDetail.fee}
          </p>
        </li>
        <li>
          <div>
            <span>GST(18.0% on Processing Fee)</span>
            <span>₹ {data.repaymentDetail.gst}</span>
          </div>
        </li>
        <li>
          <div>
            <span>Minimum Repayment Deadline</span>
            <span>2 month</span>
          </div>
        </li>
        <li>
          <div>
            <span>Maximum Repayment Deadline</span>
            <span>{Number(data.duration) / 30} month</span>
          </div>
        </li>
      </ul>
    </div>
  );
};

export default RepaymentDataBigShark;
