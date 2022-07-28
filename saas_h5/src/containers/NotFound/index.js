/**
 * Created by yaer on 2019/3/5;
 * @Email 740905172@qq.com
 * */
import RootTemplate from "../../template/RootTemplate";
import "./index.less";

import notFonundImg from "../../images/notFound/not_found.png";

export default class NotFound extends RootTemplate {
  render() {
    return (
      <div className="not-fonund-wrapper">
        <div className="con">
          <img src={notFonundImg} alt="" />
          <p>404 Not Found</p>
        </div>
      </div>
    );
  }
}
