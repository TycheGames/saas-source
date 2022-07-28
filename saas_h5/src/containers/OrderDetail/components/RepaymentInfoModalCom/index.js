import { Modal, Button } from "antd-mobile";
import PropTypes from "prop-types";

import { bgAndFc } from "../../../../vest";

const RepaymentInfoModalCom = props => {
  const { showType, closeFn, userData, saveUserData, setUserData } = props;
  return (
    <Modal
      visible={showType}
      transparent
      className="user-data-modal-wrapper"
      onClose={closeFn}
    >
      <div className="user-data-modal">
        <div className="title-wrapper">
          <p className="title">Repayment</p>
          <div className="close-wrapper" onClick={closeFn}>
            <i className="iconfont icon-guanbi" />
          </div>
        </div>
        <div className="input-item phone">
          <span>+91</span>
          <input
            type="number"
            value={userData.contact}
            placeholder="Phone Number"
            onChange={e =>
              setUserData(
                Object.assign({}, userData, { contact: e.target.value })
              )
            }
          />
        </div>
        <div className="input-item">
          <input
            type="text"
            value={userData.email}
            placeholder="Email"
            onChange={e =>
              setUserData(
                Object.assign({}, userData, { email: e.target.value })
              )
            }
          />
        </div>

        <Button
          style={bgAndFc()}
          className="continue"
          activeClassName="button-active"
          onClick={saveUserData}
        >
          CONTINUE
        </Button>
      </div>
    </Modal>
  );
};

RepaymentInfoModalCom.propTypes = {
  showType: PropTypes.bool.isRequired,
  closeFn: PropTypes.func.isRequired,
  userData: PropTypes.object.isRequired,
  saveUserData: PropTypes.func.isRequired,
  setUserData: PropTypes.func.isRequired
};

RepaymentInfoModalCom.defaultProps = {
  userData: {
    contact: "",
    email: ""
  }
};

export default RepaymentInfoModalCom;
