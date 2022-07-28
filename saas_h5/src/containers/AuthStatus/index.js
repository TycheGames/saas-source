/**
 * Created by yaer on 2019/7/22;
 * @Email 740905172@qq.com
 * 认证状态
 * */
import { useState, useEffect } from "react";


import ShowPage from "../../components/Show-page";
import { useDocumentTitle } from "../../hooks";
import { getAuthStatus } from "../../api";

import {color} from "../../vest";
import "./index.less";

const addHardLogo = require("../../images/authLogo/aadhaar.png");
const voterIdCardLogo = require("../../images/authLogo/voterIdCardLogo.png");
const panCardLogo = require("../../images/authLogo/panCard.png");



const AuthStatus = (props) => {
  useDocumentTitle(props);
  const [showPage, setShowPage] = useState(false);
  const [data, setData] = useState({
    aadhaarStatus: false,
    panCardStatus: false,
    voterIdCardStatus: false,
  });

  const { aadhaarStatus, panCardStatus, voterIdCardStatus } = data;

  useEffect(() => {
    getAuthStatus().then((res) => {
      if (res.code === 0) {
        setData(res.data);
        setShowPage(true);
      }
    });
  }, []);

  return (
    <ShowPage show={showPage}>
      <div className="auth-status-wrapper">
        <h1 className="title">Necessary Authorization</h1>
        <div className="item">
          <img src={addHardLogo} alt="" />
          <div className="info">
            <h1>Aadhaar</h1>
            <p>Reduce approval time</p>
          </div>
          {aadhaarStatus && <i className="iconfont icon-duihao" style={color()} />}
        </div>
        <div className="item">
          <img src={panCardLogo} alt="" />
          <div className="info">
            <h1>PAN CARD</h1>
            <p>Reduce approval time</p>
          </div>
          {panCardStatus && <i className="iconfont icon-duihao" style={color()} />}
        </div>
        <h1 className="title">Optional Authorization</h1>
        <p className="sub-title">More authorization,more success!</p>
        <div className="item">
          <img src={voterIdCardLogo} alt="" />
          <div className="info">
            <h1>Voter ID Card</h1>
            <p>Imporve the pass rate</p>
          </div>
          {voterIdCardStatus && <i className="iconfont icon-duihao" style={color()} />}
        </div>
      </div>
    </ShowPage>

  );
};


export default AuthStatus;
