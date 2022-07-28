/**
 * Created by yaer on 2019/7/4;
 * @Email 740905172@qq.com
 * */
import { useState, Fragment, useEffect } from "react";
import { Accordion, List } from "antd-mobile";
import data from "./data";
import "./index.less";

import { background, borderColor, bgAndFc } from "../../vest";
import { useDocumentTitle } from "../../hooks";
import * as categorizeList from "./data";

const FAQ = props => {
  useDocumentTitle(props);

  // 过滤掉faq集合
  const categorizeListData = Object.keys(categorizeList).filter(
    item => item !== "default"
  );

  // 当前选择的类型
  const [selectCategorize, setSelectCategorize] = useState(
    categorizeList[categorizeListData[0]]
  );

  const [list, setList] = useState([]);

  useEffect(() => {
    setList(
      data.filter(item => item.categorize.indexOf(selectCategorize) > -1)
    );
  }, [selectCategorize]);
  return (
    <Fragment>
      <div className="faq-wrapper">
        <div className="faq-categorize-list">
          {categorizeListData.map((item, index) => (
            <span
              key={index}
              style={switchStyle(categorizeList[item] === selectCategorize)}
              className={categorizeList[item] === selectCategorize ? "on" : ""}
              onClick={() => setSelectCategorize(categorizeList[item])}
            >
              {categorizeList[item]}
            </span>
          ))}
        </div>
        <div className="faq-list">
          {list.map((item, index) => (
            <Accordion key={index} accordion className="my-accordion">
              <Accordion.Panel
                className="pad"
                header={
                  <span>
                    {index + 1}. {item.label}
                  </span>
                }
              >
                <div
                  dangerouslySetInnerHTML={{
                    __html: item.data
                  }}
                />
              </Accordion.Panel>
            </Accordion>
          ))}
        </div>
      </div>
    </Fragment>
  );

  /**
   * 设置选择的背景色
   * @param {*} type
   */
  function switchStyle(type) {
    return type ? { ...bgAndFc(), ...borderColor() } : borderColor();
  }
};

export default FAQ;
