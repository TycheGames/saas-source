/**
 * Created by yaer on 2019/6/27;
 * @Email 740905172@qq.com
 * 借款认证->工作信息
 * */
import { useState, useEffect } from "react";
import { Toast } from "antd-mobile";
import AuthProgress from "../../components/Auth-progress";
import InfoItem from "../../components/Info-item";
import UploadFile from "../../components/Upload-file";
import RangePicker from "../../components/Range-picker";
import ShowPage from "../../components/Show-page";
import SelectModal from "../../components/Select-modal";
import NextClick from "../../components/Next-click";
import Title from "../../components/Title";
import { getWorkInfo, setWorkInfo } from "../../api";
import { setClassName } from "../../utils/utils";
import "./index.less";
import { useDocumentTitle } from "../../hooks";
import { nativeCustomMethod, pageJump } from "../../nativeMethod";

const EDUCATION = "education";
const INDUSTRY = "industry";
const WORKING_SENIORITY = "workingSeniority";
const ENTRY_PERIOD = "companySeniority";


/**
 * params
 *  isInput
 *    0 不能输入
 *    1 能输入
 */
const WorkInfo = (props) => {
  useDocumentTitle(props);
  const [showPage, setShowPage] = useState(true);

  const [isInput, setIsInput] = useState(true);

  const [nextBtnType, setNextBtnType] = useState(false);


  const [inputData, setInputData] = useState({
    educatedSchoolVal: "",
    educationVal: "",
    educationId: "",
    residentialAddressVal: "",
    residentialAddressId: "",
    residentialDetailAddressVal: "",
    industryVal: "",
    industryId: "",
    companyNameVal: "",
    companyPhoneVal: "",
    companyAddressVal: "",
    companyAddressId: "",
    companyDetailAddressVal: "",
    workPositionVal: "",
    workingSeniorityId: "",
    workingSeniorityVal: "",
    monthlySalaryVal: "",
    companySeniorityId: "",
    companySeniorityVal: "",
    companyDocsAddArr: [],
    companyDocsDeleteArr: [],
    currPinCode: "",
    // permPinCode: "",
  });

  /* select and range 相关 */
  // 地址选择器显示状态
  const [addressShowType, setAddressShowType] = useState(false);

  // 点击的哪个input唤起的地址选择器
  const [addressInputSelectType, setAddressInputSelectType] = useState("");

  /* select modal params */
  const [modalType, setModalType] = useState(false);
  const [modalTitle, setModalTitle] = useState("");
  const [modalList, setModalList] = useState([]);
  const [nowModalClick, setNowModalClick] = useState("");


  const [getData, setGetData] = useState({
    companyDocs: [],
    educationList: [],
    addressList: [],
    industryList: [],
    workingSeniorityList: [],
    companySeniorityList: [],
  });

  const {
    educatedSchoolVal,
    educationVal,
    residentialAddressVal,
    residentialDetailAddressVal,
    industryVal,
    companyNameVal,
    companyPhoneVal,
    companyAddressVal,
    companyDetailAddressVal,
    workPositionVal,
    workingSeniorityVal,
    monthlySalaryVal,
    companySeniorityId,
    companySeniorityVal,
    currPinCode,
    // permPinCode,
  } = inputData;

  const { companyDocs, addressList, companySeniorityList } = getData;

  useEffect(() => {
    // 如果modal关闭 reset数据
    if (!modalType) {
      _resetModalParams();
    }
  }, [modalType]);

  useEffect(() => {
    setNextBtnType(_watchInputData());
  }, [inputData]);

  useEffect(() => {
    setIsInput(!!(Number(props.match.params.isInput)));
    nativeCustomMethod("onShow", () => "htmlOnShow");
    window.htmlOnShow = () => {
      getWorkData();
    };
    getWorkData();
  }, []);
  return (
    <ShowPage show={showPage}>
      <div className="work-info-wrapper">
        <ShowPage show={isInput}>
          <AuthProgress step={3} stepMsg="Work info" />
        </ShowPage>
        <p className="message">
          In case your application rejected, please make sure all information correct.
        </p>
        <Title title="The degree of education" />
        <div className="content">
          <div>
            <InfoItem
              inputDisabled={isInput}
              label="Educated School"
              value={educatedSchoolVal || ""}
              placeholder="please input"
              inputType="input"
              inputFn={e => inputChage(e, 1)} />
          </div>
          <div onClick={() => selectClick(0)}>
            <InfoItem
              label="Education"
              inputType="select"
              value={educationVal || ""}
              placeholder="please choose" />
          </div>
          <Title title="Address information" />
          <div onClick={() => addressPicker(1)}>
            <InfoItem
              label="Residential Address"
              value={residentialAddressVal || ""}
              inputType="select"
              placeholder="please choose" />
          </div>
          <div>
            <InfoItem
              inputDisabled={isInput}
              label="Detail Address"
              value={residentialDetailAddressVal || ""}
              inputType="input"
              placeholder="input detail location"
              inputFn={e => inputChage(e, 2)} />
          </div>
          <div>
            <InfoItem
              inputDisabled={isInput}
              label="Current  Pincode"
              value={currPinCode || ""}
              placeholder="please input"
              inputType="number"
              inputFn={e => inputChage(e, 8)} />
          </div>
          {/* <div>
            <InfoItem
              inputDisabled={isInput}
              label="Permanent Pincode"
              value={permPinCode || ""}
              placeholder="please input"
              inputType="number"
              inputFn={e => inputChage(e, 9)} />
          </div> */}
          <Title title="Job infomation" />
          <div onClick={() => selectClick(1)}>
            <InfoItem
              label="Industry"
              value={industryVal || ""}
              inputType="select"
              placeholder="please choose" />
          </div>
          <div>
            <InfoItem
              inputDisabled={isInput}
              label="Company Name"
              value={companyNameVal || ""}
              inputType="input"
              placeholder="input the full company name"
              inputFn={e => inputChage(e, 3)} />
          </div>
          <div>
            <InfoItem
              inputDisabled={isInput}
              label="Company Phone"
              value={companyPhoneVal || ""}
              inputType="number"
              placeholder="input the full company phone"
              inputFn={e => inputChage(e, 4)} />
          </div>
          <div onClick={() => addressPicker(2)}>
            <InfoItem
              label="Company Address"
              value={companyAddressVal || ""}
              inputType="select"
              placeholder="please choose" />
          </div>
          <div>
            <InfoItem
              inputDisabled={isInput}
              label="Detail Address"
              value={companyDetailAddressVal || ""}
              inputType="input"
              placeholder="input detail location"
              inputFn={e => inputChage(e, 5)} />
          </div>
          <div>
            <InfoItem
              inputDisabled={isInput}
              label="Work Position"
              value={workPositionVal || ""}
              inputType="input"
              placeholder="input work postion"
              inputFn={e => inputChage(e, 6)} />
          </div>
          <div onClick={() => selectClick(2)}>
            <InfoItem
              label="Working Seniority"
              value={workingSeniorityVal || ""}
              inputType="select"
              placeholder="please choose" />
          </div>
          <div onClick={() => selectClick(3)}>
            <InfoItem
              label="Company Seniority"
              value={companySeniorityVal || " "}
              inputType="select"
              placeholder="please choose" />
          </div>
          <div>
            <InfoItem
              inputDisabled={isInput}
              label="Monthly Salary before tax(₹)"
              value={monthlySalaryVal || ""}
              inputType="number"
              placeholder="please input"
              inputFn={e => inputChage(e, 7)} />
          </div>
        </div>

        {/* <ShowPage show={isInput}>
          <Title title="Certificate of Company Docs (up to 1 pictures)" />
          <div className="company-docs">
            <UploadFile
              isUpload={isInput}
              maxFileLength={Number(1)}
              filesArr={companyDocs}
              fileDeleteFn={fileDelete}
              fileAddFn={fileAdd} />
          </div>
          <p className="company-msg">More documents the better (business card/contract/salary slips etc)</p>
        </ShowPage> */}

        {/* 区域选择框 */}
        <RangePicker
          data={addressList}
          SelectFn={rangeSelect}
          show={addressShowType}
          onCloseFn={type => setAddressShowType(type)} />

        {/* 选择模态框 */}
        <SelectModal
          confirmDataClickFn={modalConfirm}
          modal_list={modalList}
          modal_title={modalTitle}
          modal_type={modalType}
          cancelDataClickFn={modalCancelClick} />

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


  function getWorkData() {
    getWorkInfo().then(({ data }) => {
      const view_data = data.getData || {};
      const {
        educationList, industryList, workingSeniorityList,
      } = data.selectData;
      const {
        educationId, industryId, workingSeniorityId,
      } = view_data;

      // 已有的数据包括选择框数据会渲染到getData

      setGetData({
        ...getData,
        ...data.selectData,
        ...{
          companyDocs: view_data.companyDocs,
        },
        ...setAllListSelectType({ educationList, educationId }, { industryList, industryId }, {
          workingSeniorityList,
          workingSeniorityId,
        },
        {
          companySeniorityId: view_data.companySeniorityId,
          companySeniorityList: data.selectData.companySeniorityList,
        }),
      });


      // 设置输入内容
      const education_data = whereIdSelectData(educationId, educationList);
      const industry_data = whereIdSelectData(industryId, industryList);
      const working_seniority_data = whereIdSelectData(workingSeniorityId, workingSeniorityList);
      setInputData({
        ...inputData,
        ...view_data,
        ...{
          educationVal: education_data && education_data.label || "",
          industryVal: industry_data && industry_data.label || "",
          workingSeniorityVal: working_seniority_data && working_seniority_data.label || "",
        },
      });

      setShowPage(true);


      // 设置select type
    });
  }

  /**
   * input select 点击
   * @param type
   */
  function selectClick(type) {
    if (!isInput) return;
    const { educationList, industryList, workingSeniorityList } = getData;
    let now_type;
    let title;
    let
      list;

    // 根据type设定当前点击项
    switch (type) {
    case 0:
      now_type = EDUCATION;
      title = "Select Education";
      list = educationList;
      break;
    case 1:
      now_type = INDUSTRY;
      title = "Select Industry";
      list = industryList;
      break;
    case 2:
      now_type = WORKING_SENIORITY;
      title = "Select Working Seniority";
      list = workingSeniorityList;
      break;
    case 3:
      now_type = ENTRY_PERIOD;
      title = "Select company seniority";
      list = companySeniorityList;
      break;
    default:
      now_type = "";
      title = "";
      list = [];
    }

    // 塞入数据
    setNowModalClick(now_type);
    setModalTitle(title);
    setModalList(list);
    setModalType(!modalType);
  }

  /**
   * select modal确认按钮
   * @param data  选中的数据
   * @param list  修改后的list
   */
  function modalConfirm(data, list) {
    const { id, label } = data;
    let params;
    switch (nowModalClick) {
    case EDUCATION:
      params = {
        educationVal: label,
        educationId: id,
      };
      break;
    case INDUSTRY:
      params = {
        industryVal: label,
        industryId: id,
      };
      break;
    case WORKING_SENIORITY:
      params = {
        workingSeniorityVal: label,
        workingSeniorityId: id,
      };
      break;
    case ENTRY_PERIOD:
      params = {
        companySeniorityId: id,
        companySeniorityVal: label,
      };
      break;
    default:
      params = null;
    }
    setInputData({ ...inputData, ...params });
    setModalType(false);
    setGetData({ ...getData, ...{ [`${nowModalClick}List`]: list } });
  }

  /**
   * select modal 取消按钮
   */
  function modalCancelClick() {
    setModalType(false);
  }

  /**
   * 设置各选择框isSelect属性
   * @param educationList
   * @param educationId
   * @param industryList
   * @param industryId
   * @param workingSeniorityList
   * @param workingSeniorityId
   * @returns {{educationList: *, industryList: *, workingSeniorityList: *}}
   */
  function setAllListSelectType({ educationList, educationId }, { industryList, industryId }, { workingSeniorityList, workingSeniorityId }, companySeniority) {
    return {
      educationList: setSelectListForMat(educationId, educationList),
      industryList: setSelectListForMat(industryId, industryList),
      workingSeniorityList: setSelectListForMat(workingSeniorityId, workingSeniorityList),
      companySeniorityList: setSelectListForMat(companySeniority.companySeniorityId, companySeniority.companySeniorityList),
    };
  }

  /**
   * 设置选择框格式
   * @param id
   * @param list
   */
  function setSelectListForMat(id, list) {
    return list.map(item => ({ ...item, ...{ isSelect: id === item.id } }));
  }

  /**
   * 根据id选择数据
   * @param id
   * @param list
   * @returns {*|null}
   */
  function whereIdSelectData(id, list) {
    const data = list.filter(item => (item.id === id))[0];
    return data || null;
  }

  /**
   * 输入框输入
   * @param type
   */
  function inputChage(e, type) {
    let label = "";
    switch (type) {
    case 1:
      label = "educatedSchoolVal";
      break;
    case 2:
      label = "residentialDetailAddressVal";
      break;
    case 3:
      label = "companyNameVal";
      break;
    case 4:
      label = "companyPhoneVal";
      break;
    case 5:
      label = "companyDetailAddressVal";
      break;
    case 6:
      label = "workPositionVal";
      break;
    case 7:
      label = "monthlySalaryVal";
      break;
    case 8:
      label = "currPinCode";
      break;
    case 9:
      label = "permPinCode";
      break;
    default:
      label = "";
    }
    if (!label) return;
    setInputData({ ...inputData, [label]: type === 7 ? e.target.value.replace(/[^\d]/g, "") : e.target.value });
  }

  /**
   * 文件删除
   * @param filesArr  文件数组
   */
  function fileDelete(filesArr) {
    setInputData({ ...inputData, companyDocsDeleteArr: filesArr });
  }

  /**
   * 文件添加
   * @param filesArr  文件数组
   */
  function fileAdd(filesArr) {
    const arr = filesArr.map((item, index) => Object.assign({}, item, { id: index }));
    setInputData({ ...inputData, companyDocsAddArr: arr });
  }

  /**
   * 地址选择
   * @param type 点击的输入框
   */
  function addressPicker(type) {
    if (!isInput) return;
    switch (type) {
    case 1:
      setAddressInputSelectType("residentialAddress");
      break;
    case 2:
      setAddressInputSelectType("companyAddress");
      break;
    default:
      setAddressInputSelectType("");
    }
    setAddressShowType(!addressShowType);
  }

  /**
   * 当前选中的区域
   * @param data
   * @param input_value 输入的数据
   */
  function rangeSelect(data, input_value, input_id) {
    console.log(input_id);
    setInputData({
      ...inputData,
      [`${addressInputSelectType}Id`]: input_id,
      [`${addressInputSelectType}Val`]: input_value,
    });
  }

  /**
   * 下一步
   */
  function nextClick() {
    window.htmlOnShow = () => {
      getWorkData();
    };
    if (_watchInputData()) {
      Toast.info("Please complete the information and upload the data!");
      return;
    }

    console.log("success");
    setWorkInfo(inputData).then((res) => {
      if (res.code === 0) {
        Toast.success("save success");
        if (!res.data) return;
        const timer = setTimeout(() => {
          clearTimeout(timer);
          pageJump(res.data);
        }, 1000);
      }
    });
  }

  /**
   * 重置select modal数据
   * @private
   */
  function _resetModalParams() {
    setModalList([]);
    setModalTitle("");
    setNowModalClick("");
  }

  /**
   * 监听输入数据
   * @private
   */
  function _watchInputData() {
    let is_error = false;
    console.log(inputData);
    const companyDocsAddArr = "companyDocsAddArr";
    const companyDocsDeleteArr = "companyDocsDeleteArr";
    const companyAddressId = "companyAddressId";
    const residentialAddressId = "residentialAddressId";
    for (const key in inputData) {
      const value = inputData[key];
      // 过滤上传文件key
      // TODO 上传图片校验
      if (key === companyDocsAddArr || key === companyDocsDeleteArr || key === companyAddressId || key === residentialAddressId) {
        if (key === companyDocsDeleteArr && value.length >= (companyDocs && companyDocs.length || 0) && !inputData[companyDocsAddArr].length) {
          // is_error = true;
        }
      } else if (value === null || value === undefined || value === "") {
        is_error = true;
      }
    }
    return is_error;
  }
};

export default WorkInfo;
