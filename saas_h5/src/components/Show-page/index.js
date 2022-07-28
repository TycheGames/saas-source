/**
 * Created by yaer on 2019/6/24;
 * @Email 740905172@qq.com
 * 页面显示控制
 * */
import PropTypes from "prop-types";
import RootTemplate from "../../template/RootTemplate";

export default class ShowPage extends RootTemplate {
  constructor(props) {
    super(props);
  }
  static defaultProps = {
    useStyle: "display",
    show: false,
  }


  render() {
    const { show, useStyle } = this.props;
    const style = {
      [useStyle]: useStyle === "display" ? show ? "block" : "none" : show ? "visible" : "hiddlen",
      width: "100%",
      height: "100%",
    };
    return (
      <div style={style}>
        {this.props.children}
      </div>
    );
  }
}

ShowPage.propTypes = {
  show: PropTypes.bool.isRequired,
  useStyle: PropTypes.string,
};
