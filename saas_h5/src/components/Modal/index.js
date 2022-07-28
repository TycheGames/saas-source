/**
 * Created by yaer on 2019/3/21;
 * @Email 740905172@qq.com
 * */

import { Modal } from "antd-mobile";
import RootTemplate from "../../template/RootTemplate";
import "./index.less";

export default class extends RootTemplate {
  constructor(props) {
    super(props);

    this.state = {
      show: false,
    };
    this.changeShow = this.changeShow.bind(this);
  }

  render() {
    const { show } = this.state;
    return (
      <Modal
        transparent
        visible={show}>
        {this.props.children}
      </Modal>
    );
  }

  changeShow() {
    this.setState(prevState => ({ show: !prevState.show }));
  }
}
