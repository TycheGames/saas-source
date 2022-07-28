import { useEffect, useState } from "react";
import { Toast } from "antd-mobile";

import ShowPage from "../../components/Show-page";
import InfoItem from "../../components/Info-item";
import SelectModal from "../../components/Select-modal";
import DatePicker from "../../components/Date-picker";
import RangePicker from "../../components/Range-picker";
import NextClick from "../../components/Next-click";
import GroupTitle from "./component/GroupTitle";

import { getBasicInfo, setBasicInfo } from "../../api";
import { nativeCustomMethod, pageJump } from "../../nativeMethod";
import { getUrlData, setClassName } from "../../utils/utils";
import { useDocumentTitle } from "../../hooks";

import "./index.less";

const RELIGION_TYPE = "religion";
const STUDENT_TYPE = "student";
const MARITAL_STATUS_TYPE = "marital";
const EDUCATION = "education";
const INDUSTRY = "industry";

const RESIDENTIAL = "RESIDENTIAL";
const AADHAAR = "AADHAAR";

export default props => {
  useDocumentTitle(props);

  const [isRefresh, setIsRefresh] = useState(true); // webview页面跳转回来后是否可以重新获取数据
  const [isInput, setIsInput] = useState(true); // 是否可以输入数据
  const [showPage, setShowPage] = useState(false);

  const [nowClickAddressModal, setNowClickAddressModal] = useState(""); // 点击哪个item显示的地址选择
  const [addressShowType, setAddressShowType] = useState(false); // 地址选择模态框显示状态

  const [inputData, setInputData] = useState({
    birthday: "",
    fullName: "",
    educationVal: "",
    educationId: "",
    industryVal: "",
    industryId: "",
    // studentVal: "",
    // studentId: "",
    maritalStatusVal: "",
    maritalStatusId: "",
    emailVal: "",
    zipCodeVal: "",
    companyNameVal: "",
    monthlySalaryVal: "",
    residentialAddressVal: "",
    residentialAddressId: "",
    residentialDetailAddressVal: "",
    aadhaarPinCode: "",
    aadhaarAddressId: "",
    aadhaarAddressVal: "",
    aadhaarDetailAddressVal: ""
  });

  // modal相关
  const [modalDataInfo, setModalDataInfo] = useState({
    nowModalClick: "",
    modalTitle: "",
    modalList: []
  });
  const [modalType, setModalType] = useState(false);

  /*时间选择器相关*/
  const [datePickerType, setDatePickerType] = useState(false);

  const [selectListData, setSelectListData] = useState({
    // 宗教列表
    religionList: [],
    // 学历列表
    studentList: [],
    // 婚姻列表
    maritalList: [],
    educationList: [],
    industryList: [],
    addressList: []
  });

  useEffect(() => {
    // 设置可输入状态
    setIsInput(!!Number(props.match.params.isInput));
    window.addEventListener("resize", resize);
    nativeCustomMethod("onShow", () => "htmlOnShow");
    nativeCustomMethod("reportAppsFlyerTrackEvent", () => ["authbase", null]);
    window.htmlOnShow = () => {
      if (isRefresh) {
        getData();
      }
    };
    getData();
    return () => {
      window.removeEventListener("resize", resize);
    };
  }, []);

  useEffect(() => {
    if (!modalType && modalDataInfo.modalList.length) {
      setModalDataInfo({
        nowModalClick: "",
        modalTitle: "",
        modalList: []
      });
    }
  }, [modalType]);

  const {
    fullName,
    birthday,
    residentialAddressVal,
    residentialDetailAddressVal,
    zipCodeVal,
    emailVal,
    educationVal,
    studentVal,
    maritalStatusVal,
    industryVal,
    companyNameVal,
    monthlySalaryVal,
    aadhaarPinCode,
    aadhaarAddressVal,
    aadhaarDetailAddressVal
  } = inputData;

  const { addressList } = selectListData;

  const { modalTitle, modalList } = modalDataInfo;

  const NEXT_BTN_TYPE = _watchInputData();
  return (
    <ShowPage show={showPage}>
      <div className="basic-info-wrapper">
        <p className="message">
          In case your application rejected, please make sure all information
          correct.
        </p>
        <div className="content">
          <div className="info-group">
            <InfoItem
              inputDisabled={isInput}
              label="Full Name"
              value={fullName || ""}
              inputType="input"
              placeholder="input full name"
              inputFn={e =>
                updateInputData("fullName", e.target.value.toUpperCase())
              }
            />

            <InfoItem
              label="Birthday"
              value={birthday || ""}
              inputType="selectDate"
              placeholder="please choose"
              onClick={() => setDatePickerType(isInput && !datePickerType)}
            />
          </div>

          <div className="info-group">
            <InfoItem
              inputDisabled={isInput}
              inputType="input"
              label="Email ID"
              placeholder="input email address"
              inputFn={e => updateInputData("emailVal", e.target.value)}
              value={emailVal || ""}
            />
          </div>

          <GroupTitle title="Current Residential Address" />

          <div className="info-group">
            <InfoItem
              label="Current City"
              value={residentialAddressVal || ""}
              inputType="select"
              placeholder="please choose"
              onClick={() => setShowAddressModalInItem(RESIDENTIAL)}
            />
            <InfoItem
              inputDisabled={isInput}
              label="Detail Address"
              value={residentialDetailAddressVal || ""}
              inputType="input"
              placeholder="input detail location"
              inputFn={e =>
                updateInputData("residentialDetailAddressVal", e.target.value)
              }
            />
            <InfoItem
              inputDisabled={isInput}
              inputType="input"
              label="Zip Code"
              placeholder="input zip code"
              inputFn={e => updateInputData("zipCodeVal", e.target.value)}
              value={zipCodeVal || ""}
            />
          </div>

          <GroupTitle title="Aadhaar Address" />

          <div className="info-group">
            <InfoItem
              label="Aadhaar City"
              value={aadhaarAddressVal || ""}
              inputType="select"
              placeholder="please choose"
              onClick={() => setShowAddressModalInItem(AADHAAR)}
            />
            <InfoItem
              inputDisabled={isInput}
              label="Detail Address"
              value={aadhaarDetailAddressVal || ""}
              inputType="input"
              placeholder="input your detail address"
              inputFn={e =>
                updateInputData("aadhaarDetailAddressVal", e.target.value)
              }
            />
            <InfoItem
              inputDisabled={isInput}
              label="Zip Code"
              value={aadhaarPinCode || ""}
              inputType="input"
              placeholder="input your zip code"
              inputFn={e => updateInputData("aadhaarPinCode", e.target.value)}
            />
          </div>

          <div className="info-group">
            <InfoItem
              label="Education"
              inputType="select"
              value={educationVal || ""}
              placeholder="please choose"
              onClick={() => selectClick(3)}
            />
            {/* <InfoItem
              inputType="select"
              label="Student"
              placeholder="please choose"
              value={studentVal || ""}
              onClick={() => selectClick(1)}
            /> */}
            <InfoItem
              inputType="select"
              label="Marital Status"
              placeholder="please choose"
              value={maritalStatusVal || ""}
              onClick={() => selectClick(2)}
            />
          </div>
          <div className="info-group">
            <InfoItem
              label="Industry"
              value={industryVal || ""}
              inputType="select"
              placeholder="please choose"
              onClick={() => selectClick(4)}
            />
            <InfoItem
              inputDisabled={isInput}
              label="Company Name"
              value={companyNameVal || ""}
              inputType="input"
              placeholder="input the full company name"
              inputFn={e => updateInputData("companyNameVal", e.target.value)}
            />
            <InfoItem
              inputDisabled={isInput}
              label="Monthly Salary Before Tax(₹)"
              value={monthlySalaryVal || ""}
              inputType="number"
              placeholder="please input"
              inputFn={e => updateInputData("monthlySalaryVal", e.target.value)}
              onClick={() =>
                setInputData(
                  Object.assign({}, inputData, { monthlySalaryVal: "" })
                )
              }
            />
          </div>
        </div>

        <ShowPage show={isInput}>
          <NextClick
            className={setClassName(["next", NEXT_BTN_TYPE ? "" : "on"])}
            text="CONTINUE"
            clickFn={nextClick}
          />
        </ShowPage>
      </div>

      {/*时间选择*/}
      <DatePicker
        show={datePickerType}
        showFn={() => setDatePickerType(!datePickerType)}
        confirmDateFn={datePickerConfirmDate}
      />

      {/* 区域选择框 */}
      <RangePicker
        data={addressList}
        SelectFn={rangeSelect}
        show={addressShowType}
        onCloseFn={type => setAddressShowType(type)}
      />

      {/* 选择模态框 */}
      <SelectModal
        CloseFn={modalCancelClick}
        confirmDataClickFn={modalConfirm}
        modal_list={modalList}
        modal_title={modalTitle}
        modal_type={modalType}
        cancelDataClickFn={modalCancelClick}
      />
    </ShowPage>
  );

  /**
   * 键盘自动顶起页面
   */
  function resize() {
    if (
      document.activeElement.tagName === "INPUT" ||
      document.activeElement.tagName === "TEXTAREA"
    ) {
      setTimeout(() => {
        document.activeElement.parentNode.parentNode.scrollIntoView();
      }, 0);
    }
  }

  /**
   * 获取数据
   */
  function getData() {
    setIsRefresh(true);
    getBasicInfo().then(res => {
      const { getData, selectData } = res.data;
      setInputData(getData);

      // 设置选择项的选中判断字段
      const data = Object.keys(selectData)
        .map(key => {
          const data = selectData[key];
          if (key === "addressList") return { [key]: data };
          // 获取选择项对应选中的id
          let idKey = _switchSelectListInId(key);
          return { [key]: _setListSelectType(getData[idKey], data) };
        })
        .reduce((prev, cur) => ({ ...prev, ...cur }), {});

      setSelectListData(data);
      setShowPage(true);
    });
  }

  /**
   * 下一步
   */
  function nextClick() {
    console.log(inputData);
    window.htmlOnShow = () => {
      getData();
    };
    if (_watchInputData()) {
      Toast.info("Please complete the information and upload the data!");
      return;
    }
    setBasicInfo(inputData).then(res => {
      Toast.success("save success");
      if (!res.data) return;
      const timer = setTimeout(() => {
        pageJump(res.data);
        clearTimeout(timer);
      }, 1000);
    });
  }

  /**
   * 点击显示地址选择框
   * @param {*} type 唤起的item
   */
  function setShowAddressModalInItem(type) {
    setNowClickAddressModal(type);
    setAddressShowType(isInput && true);
  }

  /**
   * 更新输入框数据
   * @param {*} key 更改的字段key
   * @param {*} val 更改的数据
   */
  function updateInputData(key, val) {
    setInputData(
      Object.assign({}, inputData, {
        [key]: val
      })
    );
  }

  /**
   * 时间选择
   * @param {*} dateArr
   */
  function datePickerConfirmDate(dateArr) {
    setInputData(
      Object.assign({}, inputData, {
        birthday: dateArr.reverse().join("-")
      })
    );
  }

  /**
   * 选择框input点击
   * @param {*} type
   */
  function selectClick(type) {
    if (!isInput) return;
    const {
      religionList,
      studentList,
      maritalList,
      educationList,
      industryList
    } = selectListData;
    let now_type;
    let title;
    let arr;

    // 根据点击传值进行绑定modal数据
    switch (type) {
      case 0:
        now_type = RELIGION_TYPE;
        title = "Select Religion";
        arr = religionList;
        break;
      case 1:
        now_type = STUDENT_TYPE;
        title = "Select Student";
        arr = studentList;
        break;
      case 2:
        now_type = MARITAL_STATUS_TYPE;
        title = "Select Marital Status";
        arr = maritalList;
        break;
      case 3:
        now_type = EDUCATION;
        title = "Select Education";
        arr = educationList;
        break;
      case 4:
        now_type = INDUSTRY;
        title = "Select Industry";
        arr = industryList;
        break;
      default:
        now_type = "";
        title = "";
        arr = [];
    }
    setModalDataInfo({
      nowModalClick: now_type,
      modalTitle: title,
      modalList: arr
    });
    setModalType(!modalType);
  }

  /**
   *
   * @param {*} data 选中项
   * @param {*} input_value 选中数据
   * @param {*} input_id 选中id
   */
  function rangeSelect(data, input_value, input_id) {
    setAddressShowType(false);
    let key;
    switch (nowClickAddressModal) {
      case RESIDENTIAL:
        key = "residentialAddress";
        break;
      case AADHAAR:
        key = "aadhaarAddress";
        break;
      default:
        key = "";
    }
    setInputData(
      Object.assign({}, inputData, {
        [`${key}Id`]: input_id,
        [`${key}Val`]: input_value
      })
    );
  }

  /**
   * modal取消按钮
   */
  function modalCancelClick() {
    _resetModalData();
  }

  /**
   * modal确认按钮
   * @param data 选中的数据
   * @param list 修改后的select数组
   */
  function modalConfirm(data, list) {
    const { nowModalClick, modalList } = modalDataInfo;
    let params;
    let origianl_list = "";

    const { id } = data;
    const val = data.label;

    // 根据不同的点击记录进行不同字段赋值
    switch (nowModalClick) {
      case RELIGION_TYPE:
        params = {
          religionVal: val,
          religionId: id
        };
        origianl_list = RELIGION_TYPE;
        break;
      case STUDENT_TYPE:
        params = {
          studentVal: val,
          studentId: id
        };
        origianl_list = STUDENT_TYPE;
        break;
      case MARITAL_STATUS_TYPE:
        params = {
          maritalStatusVal: val,
          maritalStatusId: id
        };
        origianl_list = MARITAL_STATUS_TYPE;
        break;
      case EDUCATION:
        params = {
          educationId: id,
          educationVal: val
        };
        origianl_list = EDUCATION;
        break;
      case INDUSTRY:
        params = {
          industryId: id,
          industryVal: val
        };
        origianl_list = INDUSTRY;
        break;
      default:
        params = {};
        origianl_list = "";
    }

    // 修改原数组
    if (origianl_list) {
      setSelectListData(
        Object.assign({}, selectListData, {
          [`${origianl_list}List`]: list
        })
      );
    }

    setInputData(Object.assign({}, inputData, params));
    _resetModalData();
  }

  /**
   * 重置选择框数据
   */
  function _resetModalData() {
    setModalType(false);
  }

  /**
   *
   * @param {*} id 当前选中的list id
   * @param {*} list  需要添加选中状态的数组
   */
  function _setListSelectType(id, list) {
    return list.map(item =>
      Object.assign({}, item, {
        isSelect: item.id === id
      })
    );
  }

  /**
   *
   * @param {*} type 需要判断的选择数组
   */
  function _switchSelectListInId(type) {
    let id;
    switch (type) {
      case "religionList":
        id = "religionId";
        break;
      case "studentList":
        id = "studentId";
        break;
      case "maritalList":
        id = "maritalStatusId";
        break;
      case "educationList":
        id = "educationId";
        break;
      case "industryList":
        id = "industryId";
        break;
      default:
        id = null;
    }
    return id;
  }

  /**
   * 监听inputData数据变化
   * @returns {boolean}
   */
  function _watchInputData() {
    let is_error = false;
    // 遍历查错
    for (const key in inputData) {
      const value = inputData[key];

      if (
        value === null ||
        value === undefined ||
        value === "" ||
        value === "0.00"
      ) {
        is_error = true;
      }
    }

    return is_error;
  }
};
