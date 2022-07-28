/**
 * Created by yaer on 2019/7/22;
 * @Email 740905172@qq.com
 * 税单填写公司信息
 * */
import { connect } from "react-redux";
import { bindActionCreators } from "redux";
import { useState, useEffect } from "react";

import { useDocumentTitle } from "../../hooks";
import InfoItem from "../../components/Info-item";
import Title from "../../components/Title";
import RangePicker from "../../components/Range-picker";
import NextClick from "../../components/Next-click";
import { setClassName } from "../../utils/utils";
import * as action from "../../actions";
import "./index.less";
import { pageJump } from "../../nativeMethod";
import SelectModal from "../../components/Select-modal";
import { getTaxBillInputCompanyInfo, setTaxBillInputCompanyInfo } from "../../api";
import ShowPage from "../../components/Show-page";
import AuthProgress from "../../components/Auth-progress";

const CATEGORY = "category";
const STATE = "state";


const TaxBillInputCompanyInfo = (props) => {
  useDocumentTitle(props);
  const { actions } = props;
  const { setIsTaxBillRepeatInput, setIsTaxBillRepeatInputTwo } = actions;

  // 下一步按钮是否可点击 false代表可点
  const [nextBtnType, setNextBtnType] = useState(true);

  const [showPage, setShowPage] = useState(false);

  // 获取的数据
  const [data, setData] = useState({
    stateList: [],
    categoryList: [],
  });

  // 上传的数据
  const [inputData, setInputData] = useState({
    categoryId: "",
    categoryVal: "",
    stateId: "",
    stateVal: "",
    companyName: "",
  });

  // 模态框数据
  const [modalType, setModalType] = useState(false);
  const [modalNowClick, setModalNowClick] = useState("");
  const [modalData, setModalData] = useState({
    modalList: [],
    modalTitle: "",
  });

  // 计数器
  const timer = null;

  useEffect(() => {
    let type = false;
    for (const key in inputData) {
      if (!inputData[key]) {
        type = true;
        return;
      }
    }
    setNextBtnType(type);
  }, [inputData]);

  useEffect(() => {
    getData();
  }, []);


  return (
    <ShowPage show={showPage}>
      <AuthProgress step={5} stepMsg="Basic info" />
      <div className="tax-bill-select-company-wrapper">
        <p className="massage">
          If the current company is employed for less than 6 months, the second certification, please enter the company information.
        </p>
        <div className="content">
          <Title title="Company Info" />
          <div onClick={() => selectClick(1)}>
            <InfoItem
              label="Company Category"
              value={inputData.categoryVal || ""}
              inputType="select"
              placeholder="please choose" />
          </div>
          <div onClick={() => selectClick(2)}>
            <InfoItem
              label="Company State"
              value={inputData.stateVal || ""}
              inputType="select"
              placeholder="please choose" />
          </div>
          <div>
            <InfoItem
              inputDisabled
              label="Company"
              value={inputData.companyName || ""}
              placeholder="please input"
              inputType="input"
              inputFn={e => setInputData(Object.assign({}, inputData, { companyName: e.target.value }))} />
          </div>


          <NextClick
            className={setClassName([
              "next",
              nextBtnType ? "" : "on",
            ])}
            clickFn={nextClick} />

          {/* 选择模态框 */}
          <SelectModal
            confirmDataClickFn={modalConfirm}
            modal_list={modalData.modalList}
            modal_title={modalData.modalTitle}
            modal_type={modalType}
            cancelDataClickFn={modalCancelClick} />
        </div>
      </div>
    </ShowPage>
  );

  function getData() {
    getTaxBillInputCompanyInfo().then((res) => {
      console.log(res);
      if (res.code === 0) {
        setData(res.data.selectData);
        props.actions.setIsTaxBillRepeatInput(res.data.getData.multi);
        setShowPage(true);
      }
    });
  }

  /**
   * 设置选框数据
   * @param type
   */
  function selectClick(type) {
    let modalTitle;
    let modalList;
    switch (type) {
    case 1:
      modalTitle = "Company Category";
      modalList = data.categoryList;
      setModalNowClick(CATEGORY);
      break;
    case 2:
      modalTitle = "Company State";
      modalList = data.stateList;
      setModalNowClick(STATE);
      break;
    default:
      modalTitle = "";
      modalList = "";
    }

    setModalData({ modalTitle, modalList });
    _changeModalShowType();
  }


  /**
   * 选择框确认按钮
   * @param selectData  选中的数据
   * @param list  选中后的数组
   */
  function modalConfirm(selectData, list) {
    const { id, label } = selectData;
    let params;
    let originList = "";
    switch (modalNowClick) {
    case CATEGORY:
      params = {
        categoryId: id,
        categoryVal: label,
      };
      originList = CATEGORY;
      break;
    case STATE:
      params = {
        stateId: id,
        stateVal: label,
      };
      originList = STATE;
      break;
    default:
      params = {};
    }

    if (originList) {
      setData(Object.assign({}, data, {
        [`${originList}List`]: list,
      }));
    }
    setInputData(Object.assign({}, inputData, params));
    setModalType(!modalType);
  }

  /**
   * 选择框取消按钮
   */
  function modalCancelClick() {
    _changeModalShowType();
  }

  /**
   * 下一步
   */
  function nextClick() {
    updateData();
  }

  /**
   * 提交数据
   */
  function updateData() {
    if (nextBtnType) return;
    const { isTaxBillRepeatInput, taxBillRepeatInputTwo } = props;

    const { setCompanyData, setReportId } = props.actions;

    setTaxBillInputCompanyInfo(inputData).then((res) => {
      if (res.code === 0) {
        setCompanyData(inputData);
        setReportId(res.data.reportId);
        props.history.push("/taxBillSelectCompany");
      }
    });
  }

  /**
   * 设置选择数组的格式
   * @param list  设置的数组
   * @private
   */
  function _setSelectDataForMat(list) {
    return list && list.map(item => Object.assign({}, item, { isSelect: false })) || [];
  }

  /**
   * 修改选择框显示状态
   * @private
   */
  function _changeModalShowType() {
    setModalType(!modalType);
  }
};

function mapStateToProps(state) {
  return {
    isTaxBillRepeatInput: state.taxBillRepeatInput,
    taxBillRepeatInputTwo: state.taxBillRepeatInputTwo,
  };
}

function mapDispatchToProps(dispatch) {
  return {
    actions: bindActionCreators(action, dispatch),
  };
}


export default connect(mapStateToProps, mapDispatchToProps)(TaxBillInputCompanyInfo);
