/*
 * @Author: Always
 * @LastEditors: Always
 * @email: 740905172@qq.com
 * @Date: 2019-11-15 18:39:49
 * @LastEditTime: 2020-07-10 19:19:12
 * @FilePath: /saas_h5/src/containers/HelpCenter/index.js
 */
/**
 * Created by yaer on 2019/7/4;
 * @Email 740905172@qq.com
 * 帮助中心
 * */

import { useState } from "react";
import { List, Button } from "antd-mobile";
import ShowPage from "../../components/Show-page";
import "./index.less";
import { background, fontColor, bgAndFc, mainColor } from "../../vest";
import { useDocumentTitle } from "../../hooks";
import { nativeCustomMethod } from "../../nativeMethod";

const { Item } = List;

const HelpCenter = props => {
  const { email, contactList } = window.appInfo;
  const [showPage, setShowpage] = useState(true);
  console.log(contactList);

  useDocumentTitle(props);
  return (
    <ShowPage show={showPage}>
      <div className="help-center-wrapper">
        <div className="contact">
          <i
            className="iconfont icon-xxhdpiShape"
            style={{ color: mainColor() }}
          />
          <p className="msg">Hi, can I help you?</p>
          {/* <div className="btn">Feedback</div> */}

          {contactList.map(data => (
            <Button
              key={data.phone}
              className="btn"
              activeClassName="button-active"
              style={bgAndFc()}
              onClick={() =>
                nativeCustomMethod("callPhoneMethod", () =>
                  data.phone.replace(/\+91\s*/, "")
                )
              }
            >
              Call us: {data.phone}
            </Button>
          ))}
          <div className="btn" style={bgAndFc()}>
            {email}
          </div>
        </div>
        {/* <List className="list">
          <Item
            onClick={() => {
              props.history.push("/faq");
            }}
            arrow="horizontal"
          >
            FAQ
          </Item>
        </List> */}
        <p className="time">Available at 9am-6pm from Monday to saturday</p>
      </div>
    </ShowPage>
  );
};

export default HelpCenter;
