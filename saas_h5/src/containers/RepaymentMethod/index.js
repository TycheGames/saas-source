/**
 * Created by yaer on 2019/7/2;
 * @Email 740905172@qq.com
 * 还款->还款方式
 * */
import { useState, useEffect } from "react";
import { Toast } from "antd-mobile";
import ShowPage from "../../components/Show-page";
import InfoItem from "../../components/Info-item";
import SelectModal from "../../components/Select-modal";
import NextClick from "../../components/Next-click";
import { getRepaymentMethod } from "../../api";
import "./index.less";
import { setClassName } from "../../utils/utils";
import { useDocumentTitle } from "../../hooks";

const RepaymentMethod = (props) => {
  useDocumentTitle(props);
  const [showPage, setShowPage] = useState(false);

  const [isInput, setIsInput] = useState(true);

  const [modalType, setModalType] = useState(false);

  const [nextBtnType, setNextBtnType] = useState(false);

  const [inputData, setInputData] = useState({
    repaymentMethodId: "",
    repaymentMethodVal: "",
  });
  const [getData, setGetData] = useState({
    repaymentMethodList: [
      {
        id: 1,
        label: "method_1",
      },
      {
        id: 2,
        label: "method_2",
      },
      {
        id: 3,
        label: "method_3",
      },
    ],
    repaymentMethodId: 1,
  });
  const { repaymentMethodId, repaymentMethodVal } = inputData;
  const { repaymentMethodList } = getData;

  useEffect(() => {
    setNextBtnType(_watchInputData());
  }, [inputData]);

  useEffect(() => {
    setIsInput(!!(Number(props.match.params.isInput)));
    getRepyamentMethodData();
  }, []);

  return (
    <ShowPage show={showPage}>
      <div className="repayment-method-wrapper">
        <div onClick={selectClick}>
          <InfoItem
            value={repaymentMethodVal}
            inputType="select"
            placeholder="please choose"
            label="Repayment Method" />
        </div>
        <SelectModal
          modal_type={modalType}
          modal_list={repaymentMethodList}
          modal_title="Repayment Method"
          cancelDataClickFn={selectModalCancelClick}
          confirmDataClickFn={selectModalConfirmClick} />

        <div className="msg">
          <i className="iconfont icon-hj1" />
          <p>This is concerned with your loan and repayment. Please fill in arefully.</p>
        </div>
        <ShowPage show={isInput}>

          <NextClick
            className={setClassName([
              "next",
              nextBtnType ? "" : "on",
            ])}
            clickFn={nextClick} />
        </ShowPage>
      </div>
    </ShowPage>
  );

  function getRepyamentMethodData() {
    getRepaymentMethod().then((res) => {
      setGetData({
        ...res.data,
        ...{
          repaymentMethodList: res.data.repaymentMethodList.map(item => ({ ...item, ...{ isSelect: res.data.repaymentMethodId === item.id } })),
        }, 
      });

      if (res.data.repaymentMethodId !== null) {
        const { id, label } = res.data.repaymentMethodList.filter(item => item.id === res.data.repaymentMethodId)[0];
        setInputData({
          ...inputData,
          ...{
            repaymentMethodId: id,
            repaymentMethodVal: label,
          }, 
        });
      }
      setShowPage(true);
    });
  }


  /**
   * 点击input select
   */
  function selectClick() {
    if (!isInput) return;
    _changeModalType();
  }

  /**
   * modal cancel click
   */
  function selectModalCancelClick() {
    _changeModalType();
  }

  /**
   * select modal  确认按钮
   * @param data  当前选中数据
   * @param list  修改后list
   */
  function selectModalConfirmClick(data, list) {
    const { id, label } = data;
    setInputData({ repaymentMethodId: id, repaymentMethodVal: label });
    setGetData({ repaymentMethodList: list });
    _changeModalType();
  }

  /**
   * 修改select modal 状态
   */
  function _changeModalType() {
    setModalType(!modalType);
  }

  /**
   * 下一步
   */
  function nextClick() {
    if (_watchInputData()) {
      Toast.info("Please complete the information and upload the data!");
      return;
    }
    console.log(inputData);
    console.log("success");
  }

  /**
   * 监听inputData
   * @returns {boolean}
   * @private
   */
  function _watchInputData() {
    let is_error = false;
    if (inputData.repaymentMethodId === null || inputData.repaymentMethodId === "") {
      is_error = true;
    }
    return is_error;
  }
};


export default RepaymentMethod;
