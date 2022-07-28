/*
 * @Author: Always
 * @LastEditors: Always
 * @Date: 2020-04-22 17:12:48
 * @LastEditTime: 2020-04-24 14:21:47
 * @FilePath: /saas_h5/src/containers/Complaints/components/Records/index.js
 */
import PropTypes from "prop-types";
import "./index.less";
const Records = ({ recordsList }) => {
  return (
    <div className="records-wrapper">
      {(recordsList.length &&
        recordsList.map((data, index) => (
          <div className="records-item" key={index}>
            <p>
              <span>Date:</span> {data.date}
            </p>
            <p>
              <span>Complaint Reason:</span> {data.reason}
            </p>
            <p>
              <span>Issus/Description:</span> {data.description}
            </p>
          </div>
        ))) || <p className="no-records">No Records</p>}
    </div>
  );
};

Records.propTypes = {
  recordsList: PropTypes.array.isRequired,
};

export default Records;
