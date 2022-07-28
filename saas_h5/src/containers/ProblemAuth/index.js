import { useState, useEffect } from "react";
import { Toast } from "antd-mobile";
import NextClick from "../../components/Next-click";
import ShowPage from "../../components/Show-page";

import { getProblemAuthData, saveProblemAuthData } from "../../api";

import { useDocumentTitle } from "../../hooks";
import { color, borderColor } from "../../vest";
import { setClassName } from "../../utils/utils";
import { pageJump } from "../../nativeMethod";
import "./index.less";

const { packageName } = window.appInfo;

/**
 * 选择框
 * @param {*} type  是否选中
 */
const Radio = type => {
  if (type) {
    return <i className={_radioClassName()} style={color()} />;
  } else {
    return <i className="iconfont icon-danxuanweixuanzhong" />;
  }
};

/**
 * 兼容选中框样式
 */
const _radioClassName = () =>
  setClassName([
    "iconfont",
    "icon-dui",
    packageName === "RupeePlus" ? "rupeeplus-select" : ""
  ]);

/**
 *
 * @param {*} data 问题数据
 * @param {*} index 问题数据下标
 * @param {*} cb 选择回调
 */
const ProblemItem = (data, index, cb) => {
  return (
    <div className="problem-item" key={index}>
      <div className="problem-item-con">
        <h1 className="title">
          {index + 1}. {data.questionContent}
        </h1>
        {data.img && (
          <img src={data.img} alt="" className="img" />
        )}
        {data.answerList.map((item, i) => (
          <div className="answer" key={i} onClick={() => answperClick(i)}>
            <div className="radio">{Radio(item.isSelect)}</div>
            <p>
              {item.label}. {item.val}
            </p>
          </div>
        ))}
      </div>
    </div>
  );

  /**
   *
   * @param {*} answperIndex 答案下标
   */
  function answperClick(answperIndex) {
    // 更新选择的答案
    const list = data.answerList.map((i, n) =>
      Object.assign({}, i, {
        isSelect: n === answperIndex
      })
    );
    // 回调数据
    cb(
      Object.assign({}, data, {
        answerList: list
      }),
      index
    );
  }
};

export default props => {
  useDocumentTitle(props);

  const [showPage, setShowPage] = useState(false);
  const [nextType, setNextType] = useState(false); // 是否可以提交
  const [selectProbleData, setSelectProbleData] = useState([]); // 问题答案
  const [data, setData] = useState([]); // 获取的数据
  const [inPageTime, setInPageTime] = useState(0); // 初始进入时间
  const [inPageTimeFrontEnd, setInPageTimeFrontEnd] = useState(0); // 前端记录开始时间，为了计算结束事件s

  const [paperId, setPaperId] = useState(0);

  useEffect(() => {
    getData();
  }, []);

  useEffect(() => {
    let selectData = [];
    data.forEach(item => {
      const data = item.answerList.filter(i => i.isSelect);
      // 如果有题目选中了答案
      if (data.length) {
        const { id } = item;
        const { label } = data[0];
        selectData.push({
          id,
          label
        });
      }
    });
    selectData.length && setSelectProbleData(selectData);
  }, [data]);

  useEffect(() => {
    setNextType(
      selectProbleData.length && selectProbleData.length === data.length
    );
  }, [selectProbleData]);
  return (
    <ShowPage show={showPage}>
      <div className="problem-auth-wrapper">
        {data.map((item, index) => ProblemItem(item, index, answperClick))}

        <NextClick
          className={setClassName(["next", nextType ? "on" : ""])}
          clickFn={submit}
          text="CONTINUE"
        />
      </div>
    </ShowPage>
  );

  function getData() {
    getProblemAuthData().then(res => {
      const { list, inPageTime, paperId } = res.data;
      setData(_setSelectInData(list));
      setInPageTime(inPageTime);
      setPaperId(paperId);
      setInPageTimeFrontEnd(new Date().getTime());
      setShowPage(true);
    });
  }

  function submit() {
    if (!nextType) {
      Toast.info("Please complete the verification!");
      return;
    }
    const nowTime = new Date().getTime();
    const params = {
      list: selectProbleData,
      inPageTime,
      outPageTime: Math.floor(
        (nowTime - inPageTimeFrontEnd) / 1000 + inPageTime
      ),
      paperId
    };
    saveProblemAuthData(params).then(res => {
      Toast.info("SUCCESS");
      const timer = setTimeout(() => {
        pageJump(res.data);
        clearTimeout(timer);
      }, 1000);
    });
  }

  /**
   * 问题选择
   * @param {*} answperData  选择后的数据
   * @param {*} index   更改的下标
   */
  function answperClick(answperData, index) {
    // 判断更改的数据是哪个下标，根据下标更改数据
    setData(data.map((item, i) => (index === i ? answperData : item)));
  }

  /**
   * 重置数据
   * @param {*} data
   */
  function _setSelectInData(data) {
    return data.map(item =>
      Object.assign({}, item, {
        answerList: item.answerList.map(i =>
          Object.assign({}, i, { isSelect: false })
        )
      })
    );
  }
};
