/*
 * @Author: Always
 * @LastEditors: Always
 * @email: 740905172@qq.com
 * @Date: 2019-11-21 14:21:32
 * @LastEditTime: 2020-01-06 10:40:28
 * @FilePath: /saas_h5/src/containers/LoanRejected/index.js
 */
/**
 * Created by yaer on 2019/8/8;
 * @Email 740905172@qq.com
 * */
import { useState, useEffect } from "react";
import ShowPage from "../../components/Show-page";
import NextClick from "../../components/Next-click";
import { getLoanRejectedTime } from "../../api";
import { nativeType } from "../../nativeMethod";
import "./index.less";
import { useDocumentTitle } from "../../hooks";

import rejected from "../../images/audit/rejected.png";

const LoanRejected = props => {
  const [showPage, setShowPage] = useState(true);
  const [time, setTime] = useState("");
  useDocumentTitle(props);

  useEffect(() => {
    getLoanRejectedTime().then(res => {
      setTime((res.data.time && res.data.time) || "");
      setShowPage(true);
    });
    refreshTime && clearInterval(refreshTime);
    const refreshTime = setInterval(() => {
      nativeType({ path: "/main/refresh_tablist" });
    }, 5000);
  });

  return (
    <ShowPage show={showPage}>
      <div className="loan-rejected-wrapper">
        <img src={rejected} alt="" />

        <p className="msg">
          Sorry your applicaton hasn't been approved, look forward to your
          re-application
        </p>
        <ShowPage show={!!time}>
          <NextClick
            className="next"
            text={`NEXT APPLY TIME ${time}`}
          />
        </ShowPage>
      </div>
    </ShowPage>
  );
};

export default LoanRejected;
