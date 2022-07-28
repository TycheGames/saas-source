/**
 * Created by yaer on 2019/7/8;
 * @Email 740905172@qq.com
 * */
import { useState } from "react";
import { Toast } from "antd-mobile";
import Title from "../../components/Title";
import "./index.less";
import { useDocumentTitle } from "../../hooks";
import { nativeType } from "../../nativeMethod";

const BASIC_INFO_ICON = require("../../images/icon/basic_info_icon.png");
const WORK_INFO_ICON = require("../../images/icon/work_info_icon.png");
const CONTACT_PERSON_INFO_ICON = require("../../images/icon/contact_person_info_icon.png");


const UserInfo = (props) => {
  useDocumentTitle(props);
  const [data, setData] = useState([
    {
      label: "Information",
      list: [
        {
          type: "h5",
          icon: BASIC_INFO_ICON,
          label: "Basic Info",
          link: "/basicInfo/0",
        },
        /* {
          type: "h5",
          icon: WORK_INFO_ICON,
          label: "Work Info",
          link: "/workInfo/0",
        }, */
        {
          type: "client",
          icon: CONTACT_PERSON_INFO_ICON,
          label: "Contact Person Info",
          link: {
            path: "/auth/contact",
            totalNum: 0,
            currentPosition: 0,
            isCheck: false,
          },
        },
      ],
    },
  ]);
  return (
    <div className="user-info-wrapper">
      {
        data.map((item, index) => (
          <div className="group" key={index}>
            <Title title={item.label} />
            <ul>
              {
                item.list.map((i, n) => (
                  <li onClick={() => navItemClick(i)} key={n}>
                    <img src={i.icon} alt="" />
                    <p>{i.label}</p>
                    <i className="iconfont icon-youjiantou" />
                  </li>
                ))
              }
            </ul>
          </div>
        ))
      }

      <div className="log-out" onClick={() => logOut()}>LOG OUT</div>
    </div>
  );

  /**
   * 导航点击
   * @param item
   */
  function navItemClick(item) {
    const { type, link } = item;
    if (type === "h5") {
      props.history.push(link);
    } else {
      nativeType(link);
    }
  }

  /**
   * 退出
   */
  function logOut() {
    nativeType({ path: "/user/logout" });
    Toast.info("log out success");
    const timer = setTimeout(() => {
      props.history.go(-1);
    }, 1000);
  }
};

export default UserInfo;
