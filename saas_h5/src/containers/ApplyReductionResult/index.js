/*
 * @Author: Always
 * @LastEditors: Always
 * @Date: 2020-04-17 15:58:58
 * @LastEditTime: 2020-04-24 15:44:04
 * @FilePath: /saas_h5/src/containers/ApplyReductionResult/index.js
 */
import "./index.less";
import { useDocumentTitle } from "../../hooks";
import processing from "../../images/appluReductionResult/processing.png";
const ApplyReductionResult = (props) => {
  useDocumentTitle(props);

  return (
    <div className="apply-reduction-result-wrapper">
      <img src={processing} alt="" className="processing-img" />
      <h1 className="title">Processing</h1>
      <p className="msg">
        Dear Customer,due to the COVID-19 epidemic, our support team will reach
        you within 3 working days ! Please keep your phone unblocked.
      </p>
    </div>
  );
};

export default ApplyReductionResult;
