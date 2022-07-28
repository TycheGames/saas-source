import PropTypes from "prop-types";

import { specialFontColor } from "../../../../vest";
import {
  STATUS_LOAN_COMPLETE,
  STATUS_OVERDUE,
} from "../../../../enum/orderStatusEnum";

const RepaymentDetailListCom = (props) => {
  const { data, status } = props;
  return (
    <div className="repayment-detail-list">
      {data.extendDate && (
        <div className="repayment-detail-list-group">
          <p
            className="repayment-detail-list-group-item"
            style={{ color: "red" }}
          >
            <span className="label">Repayment extended until</span>
            <span className="value">{data.extendDate}</span>
          </p>
        </div>
      )}
      <div className="repayment-detail-list-group">
        <p
          className="repayment-detail-list-group-item"
          style={_isShowItem(data.amount)}
        >
          <span className="label">Loan Amount</span>
          <span className="value">₹ {data.amount}</span>
        </p>
        {data.showDays && (
          <p
            className="repayment-detail-list-group-item"
            style={_isShowItem(data.days)}
          >
            <span className="label">Tenure</span>
            <span className="value">{data.days} days</span>
          </p>
        )}
        <p
          className="repayment-detail-list-group-item"
          style={_isShowItem(data.disbursalAmount)}
        >
          <span className="label">Disbursal Amount</span>
          <span className="value">₹ {data.disbursalAmount}</span>
        </p>
        <p
          className="repayment-detail-list-group-item"
          style={_isShowItem(data.fees)}
        >
          <span className="label">Processing Fee</span>
          <span className="value">₹ {data.fees}</span>
        </p>
        <p
          className="repayment-detail-list-group-item"
          style={_isShowItem(data.gst)}
        >
          <span className="label">GST 18% on Processing Fee</span>
          <span className="value">₹ {data.gst}</span>
        </p>
        <p
          className="repayment-detail-list-group-item"
          style={_isShowItem(data.interests)}
        >
          <span className="label">Total Interest @ {data.totalRate}%</span>
          <span className="value">₹ {data.interests}</span>
        </p>
      </div>
      {/* 逾期 */}
      <div className="repayment-detail-list-group">
        <p className="repayment-detail-list-group-item">
          <span className="label">
            Overdue period(
            <i style={specialFontColor("red")}>
              ₹ {data.overdueFeeAmount} per day
            </i>
            )
          </span>
          <span className="value">
            {data.overdueDay || 0}
            {(data.overdueDay && (data.overdueDay === 1 ? " day" : " days")) ||
              "day"}
          </span>
        </p>
        <p className="repayment-detail-list-group-item">
          <span className="label">Overdue Fee {data.overdueFeePercent}</span>
          <span className="value">₹ {data.overdueFee || 0}</span>
        </p>
        <p className="repayment-detail-list-group-item">
          <span className="label">GST 18% on Overdue Fee</span>
          <span className="value">₹ {data.overdueGST}</span>
        </p>
        {data.reduce && (
          <p className="repayment-detail-list-group-item">
            <span className="label" style={{ color: "red" }}>
              Waive
            </span>
            <span className="value" style={{ color: "red" }}>
              - ₹{data.reduce || 0}
            </span>
          </p>
        )}
      </div>

      <div
        className="repayment-detail-list-group coupon-list-group"
        style={_isShowItem(
          Number(data.repaidAmount) || Number(data.couponAmount)
        )}
      >
        <p
          className="repayment-detail-list-group-item"
          style={_isShowItem(Number(data.repaidAmount))}
        >
          <span className="label">Amount Paid</span>
          <span className="value">- ₹{data.repaidAmount}</span>
        </p>
        <p
          className="repayment-detail-list-group-item"
          style={_isShowItem(
            Number(data.couponAmount) &&
              (status.status === STATUS_LOAN_COMPLETE ||
                status.status === STATUS_OVERDUE)
          )}
        >
          <span className="label">Repayment Coupon</span>
          <span className="value" style={specialFontColor("red")}>
            <span className="mount">- ₹{data.couponAmount}</span>
            <span className="msg">Only for Today</span>
          </span>
        </p>
      </div>
      <div className="total-amount">
        <span className="label">Total Repayment Amount</span>
        <span className="value">
          <span>₹ {data.totalRepaymentAmount}</span>
          {(Number(data.couponAmount) && (
            <span className="discount-amount">₹ {_discountAmount()}</span>
          )) ||
            ""}
        </span>
      </div>
    </div>
  );

  /**
   * 是否显示dom
   * @param type
   */
  function _isShowItem(type) {
    return { display: type ? "flex" : "none" };
  }

  /**
   * 获取用户还款的源金额(当前折扣+优惠金额)
   * @returns {number}
   * @private
   */
  function _discountAmount() {
    return (
      Number(data.totalRepaymentAmount) + Number(data.couponAmount || 0)
    ).toFixed(2);
  }
};

RepaymentDetailListCom.propTypes = {
  data: PropTypes.object.isRequired,
};

export default RepaymentDetailListCom;
