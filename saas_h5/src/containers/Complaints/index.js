/*
 * @Author: Always
 * @LastEditors: Always
 * @Date: 2020-04-22 14:50:08
 * @LastEditTime: 2020-04-24 14:30:41
 * @FilePath: /saas_h5/src/containers/Complaints/index.js
 */
import "./index.less";
import { useState, useEffect } from "react";
import { Toast } from "antd-mobile";
import {
  getComplaintsProblemList,
  getComplaintsRecordsList,
  saveComplaintsRecords,
} from "../../api";
import { useDocumentTitle } from "../../hooks";
import { nativeCustomMethod } from "../../nativeMethod";
import Complaint from "./components/Complaint";
import Records from "./components/Records";
import ShowPage from "../../components/Show-page";
// 投诉页面
const Complaints = (props) => {
  useDocumentTitle(props);
  const [problemList, setProblemList] = useState([]);
  const [recordsList, setRecordsList] = useState([]);

  const [nowSelectProblemId, setNowSelectProblemId] = useState(null); // 当前选择的问题
  const [nowTab, setNowTab] = useState("Complaint"); // 当前tab页

  const [description, setDescription] = useState(""); // 备注
  const [fileList, setFileList] = useState([]); // 文件列表
  const [contact, setContact] = useState(""); // 联系电话

  const [isSubmit, setIsSubmit] = useState(false); // 是否可以提交数据

  const [showPage, setShowPage] = useState(false);

  // 提交
  const submit = () => {
    const params = {
      problemId: nowSelectProblemId,
      fileList,
      contact,
      description,
    };
    saveComplaintsRecords(params).then(() => {
      getComplaintsRecordsList().then((res) => {
        Toast.info("SUCCESS");
        setNowTab("Records");
        setRecordsList(res.data);
        setNowSelectProblemId(null);
        setDescription("");
        // setFileList([]);
        setContact("");
      });
    });
  };

  const getData = () => {
    Promise.all([getComplaintsProblemList(), getComplaintsRecordsList()]).then(
      (res) => {
        setProblemList(res[0].data);
        setRecordsList(res[1].data);
        setShowPage(true);
      }
    );
  };

  useEffect(() => {
    setIsSubmit(Boolean(nowSelectProblemId && description));
  }, [nowSelectProblemId, description, fileList]);

  useEffect(() => {
    nativeCustomMethod("onShow", () => "htmlOnShow");
    window.htmlOnShow = () => {
      getData();
    };
    getData();
  }, []);

  return (
    <ShowPage show={showPage}>
      <div className="complatints-wrapper">
      {nowTab === "Records" && (
          <div className="message-wrapper">
            <i className="iconfont icon-xianshi_jinggao" />
            <p className="message">
              We will process your feedback in 3 working days.
            </p>
          </div>
        )}
        <div className="tabs">
          {["Complaint", "Records"].map((item, index) => (
            <div
              className="tab-item"
              key={index}
              onClick={() => setNowTab(item)}
            >
              {item}
              {nowTab === item && <p className="line fadeIn" />}
            </div>
          ))}
        </div>

        {(nowTab === "Complaint" && (
          <Complaint
            problemList={problemList}
            nowSelectProblemId={nowSelectProblemId}
            problemChange={setNowSelectProblemId}
            textareaValue={description}
            textareaChange={setDescription}
            fileChange={setFileList}
            contact={contact}
            contactChange={setContact}
            isSubmit={isSubmit}
            submit={submit}
          />
        )) || <Records recordsList={recordsList} />}
      </div>
    </ShowPage>
  );
};

export default Complaints;
