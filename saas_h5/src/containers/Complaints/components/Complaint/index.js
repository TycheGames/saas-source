/*
 * @Author: Always
 * @LastEditors: Always
 * @Date: 2020-04-22 15:31:38
 * @LastEditTime: 2020-04-24 11:58:37
 * @FilePath: /saas_h5/src/containers/Complaints/components/Complaint/index.js
 */
import { TextareaItem } from "antd-mobile";
import PropTypes from "prop-types";
import UploadFile from "../../../../components/Upload-file";
import isCheck from "../../../../images/copmlaints/is_check.png";
import NextClick from "../../../../components/Next-click";
import { setClassName } from "../../../../utils/utils";
import "./index.less";

const Complaint = ({
  problemList,
  nowSelectProblemId,
  problemChange,
  textareaValue,
  textareaChange,
  contact,
  contactChange,
  submit,
  isSubmit,
  fileChange,
}) => {
  const fileAdd = (list) => {
    fileChange(list.map((item) => item.url));
  };
  return (
    <div className="complaint-con fadeIn">
      <ul className="problem-list">
        {problemList.map((item, index) => (
          <li
            className="problem-item"
            key={index}
            onClick={() => problemChange(item.id)}
          >
            <p>
              {index + 1}. {item.text}
            </p>
            {nowSelectProblemId === item.id && <img src={isCheck} alt="" />}
          </li>
        ))}
      </ul>
      <div className="iss-description">
        <h1 className="title">Issue/Description</h1>
        <div className="iss-description-con">
          <TextareaItem
            value={textareaValue}
            placeholder="Please Describe more than 10 words!"
            rows={4}
            onChange={(val) => textareaChange(val)}
          />
          {/* <UploadFile maxFileLength={4} fileAddFn={fileAdd} /> */}
        </div>
      </div>
      <div className="contact">
        <h1 className="title">Your Contact (Optional)</h1>
        <input
          value={contact}
          type="number"
          placeholder="Fill your phone number or email address"
          onChange={(e) => contactChange(e.target.value)}
        />
      </div>
      <NextClick
        clickFn={submit}
        className={setClassName(["next", isSubmit ? "on" : ""])}
        text="SUBMIT"
      />
    </div>
  );
};
Complaint.propTypes = {
  textareaValue: PropTypes.string.isRequired,
  textareaChange: PropTypes.func.isRequired,
  problemList: PropTypes.array.isRequired,
  nowSelectProblemId: PropTypes.oneOfType([PropTypes.string, PropTypes.number]),
  problemChange: PropTypes.func.isRequired,
  fileChange: PropTypes.func.isRequired,
  contact: PropTypes.string.isRequired,
  contactChange: PropTypes.func.isRequired,
  submit: PropTypes.func.isRequired,
  isSubmit: PropTypes.bool.isRequired,
};

export default Complaint;
