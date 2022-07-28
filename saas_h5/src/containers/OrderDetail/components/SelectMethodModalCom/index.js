import { Modal, Button } from "antd-mobile";
import PropTypes from "prop-types";

import {bgAndFc, recommendBgAndFc} from "../../../../vest";


const SelectMethodModalCom = props => {
  const { repaymentMethodList, closeFn, showType,selectRepaymentMethod } = props;
  return (
    <Modal
      visible={showType}
      transparent
      className="select-method-reset"
      onClose={closeFn}
    >
      <div className="select-method-modal">
        <h1 className="select-method-title">Select Method of Payment</h1>
        <div className="select-method-con">
          {repaymentMethodList
            .sort((a, b) => b.weight - a.weight)
            .map((item, index) => (
              <Button
                key={index}
                style={Object.assign(
                  {},
                  { display: item.showMethod ? "block" : "none" },
                  bgAndFc()
                )}
                activeClassName="button-active"
                className="select-method-item"
                onClick={() => selectRepaymentMethod(item.methodEnum)}
              >
                <span
                  className="recommend"
                  style={{
                    display: index ? "none" : "block",
                    ...recommendBgAndFc()
                  }}
                >
                  Recommend
                </span>
                {item.title}
              </Button>
            ))}
        </div>
      </div>
    </Modal>
  );
};

SelectMethodModalCom.propTypes = {
  showType: PropTypes.bool.isRequired,  // 显示状态
  closeFn: PropTypes.func.isRequired,   // 关闭方法
  repaymentMethodList: PropTypes.array.isRequired,  // 还款方式
  selectRepaymentMethod: PropTypes.func.isRequired, // 选择还款方式方法
};

SelectMethodModalCom.defaultProps = {
  repaymentMethodList: []
};

export default SelectMethodModalCom;
