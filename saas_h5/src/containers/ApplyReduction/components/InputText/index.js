/*
 * @Author: Always
 * @LastEditors: Always
 * @Date: 2020-04-17 14:18:20
 * @LastEditTime: 2020-04-23 19:01:08
 * @FilePath: /saas_h5/src/containers/ApplyReduction/components/InputText/index.js
 */
import PropTypes from "prop-types";
import { DatePicker } from "antd-mobile";
import { TextareaItem } from "antd-mobile";
import { borderColor } from "../../../../vest";
import "./index.less";

//输入框以及多行输入框
const InputText = ({
  label,
  value,
  onChange,
  inputType,
  type,
  placeholder,
}) => {
  return (
    <div className="input-text-item">
      <h1 className="input-text-title">{label}</h1>
      {inputType === "input" ? (
        <input
          style={borderColor()}
          type={type}
          value={value}
          placeholder={placeholder}
          onChange={(e) => onChange(e.target.value)}
          className="input"
        />
      ) : inputType === "textareal" ? (
        <TextareaItem
          style={borderColor()}
          value={value}
          placeholder={placeholder}
          rows={5}
          onChange={onChange}
        />
      ) : (
        <DatePicker
          mode="date"
          value={(value && new Date(value)) || new Date()}
          onChange={(date) => onChange(date)}
        >
          <div className="input" style={borderColor()}>
            {value
              ? value
              : `${new Date().getFullYear()}-${
                  new Date().getMonth() + 1
                }-${new Date().getDate()}
                `}
          </div>
        </DatePicker>
      )}
    </div>
  );
};

InputText.propTypes = {
  label: PropTypes.string.isRequired,
  value: PropTypes.string.isRequired,
  onChange: PropTypes.func.isRequired,
  inputType: PropTypes.oneOf(["input", "textareal", "date"]).isRequired,
  type: PropTypes.string.isRequired,
  placeholder: PropTypes.string.isRequired,
};

InputText.defaultProps = {
  label: "",
  value: "",
  onChange: () => {},
  inputType: "input",
  type: "text",
  placeholder: "",
};

export default InputText;
