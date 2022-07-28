/**
 * Created by yaer on 2019/6/24;
 * @Email 740905172@qq.com
 *
 * 借款认证->基本信息
 * */

import { Toast } from "antd-mobile";
import RootTemplate from "../../template/RootTemplate";
import ShowPage from "../../components/Show-page";
import InfoItem from "../../components/Info-item";
import SelectModal from "../../components/Select-modal";
import DatePicker from "../../components/Date-picker";
import RangePicker from "../../components/Range-picker";
import { getBasicInfo, setBasicInfo } from "../../api";
import NextClick from "../../components/Next-click";
import "./index.less";
import { nativeCustomMethod, pageJump } from "../../nativeMethod";
import { getUrlData } from "../../utils/utils";

const RELIGION_TYPE = "religion";
const STUDENT_TYPE = "student";
const MARITAL_STATUS_TYPE = "marital";
const EDUCATION = "education";
const INDUSTRY = "industry";

/**
 * params
 *  isInput
 *    0 不能输入
 *    1 能输入
 */
export default class BasicInfo extends RootTemplate {
  constructor(props) {
    super(props);
    this.query = getUrlData(props);
    this.isRefresh = true;
    this.state = {
      addressShowType: false, // 地址选择模态框
      isInput: true, // 是否能输入
      showPage: false,
      /* 输入情况下数据 */
      inputData: {
        birthday: "",
        fullName: "",
        educationVal: "",
        educationId: "",
        industryVal: "",
        industryId: "",
        studentVal: "",
        studentId: "",
        maritalStatusVal: "",
        maritalStatusId: "",
        emailVal: "",
        zipCodeVal: "",
        companyNameVal: "",
        monthlySalaryVal: "",
        residentialAddressVal: "",
        residentialAddressId: "",
        residentialDetailAddressVal: ""
      },

      /* modal相关 */
      nowModalClick: "", // 当前modal数据归属
      modalTitle: "",
      modalList: [],
      modalType: false, // modal 显示状态

      /*时间选择器相关*/
      datePickerType: false,

      /* 获取数据字段 */
      getData: {
        // 宗教列表
        religionList: [],
        // 学历列表
        studentList: [],
        // 婚姻列表
        maritalList: [],
        studentId: null,
        maritalStatusId: null,
        emailVal: "",
        zipCodeVal: "",
        bankStatementFile: [],
        educationList: [],
        educationId: null,
        industryList: [],
        industryId: "",
        addressList: []
      }
    };

    this.emailChange = this.emailChange.bind(this);
    this.zipChange = this.zipChange.bind(this);
    this.selectDateFn = this.selectDateFn.bind(this);
    this.fullNameChange = this.fullNameChange.bind(this);
    this.residentialDetailAddressChange = this.residentialDetailAddressChange.bind(
      this
    );
    this.monthlySalaryChange = this.monthlySalaryChange.bind(this);
    this.companyNameChange = this.companyNameChange.bind(this);
    this.educatedSchoolChange = this.educatedSchoolChange.bind(this);
    this.modalCancelClick = this.modalCancelClick.bind(this);
    this.modalConfirm = this.modalConfirm.bind(this);
    this.fileAdd = this.fileAdd.bind(this);
    this.fileDelete = this.fileDelete.bind(this);
    this.nextClick = this.nextClick.bind(this);
    this.loanPupposeChange = this.loanPupposeChange.bind(this);
    this.rangeSelect = this.rangeSelect.bind(this);
    this.rangeSelect = this.rangeSelect.bind(this);
  }

  UNSAFE_componentWillMount() {
    document.title = this.props.params.title;
    this.setState({
      isInput: !!Number(this.props.match.params.isInput)
    });
  }

  componentDidMount() {
    window.addEventListener("resize", function() {
      if (
        document.activeElement.tagName === "INPUT" ||
        document.activeElement.tagName === "TEXTAREA"
      ) {
        setTimeout(() => {
          document.activeElement.parentNode.parentNode.scrollIntoView();
        }, 0);
      }
    });
    nativeCustomMethod("onShow", () => "htmlOnShow");
    window.htmlOnShow = () => {
      if (this.isRefresh) {
        this.getData();
      }
    };
    nativeCustomMethod("reportAppsFlyerTrackEvent", () => ["authbase", null]);
    this.getData();
  }

  render() {
    const {
      showPage,
      modalList,
      modalTitle,
      modalType,
      isInput,
      addressShowType,
      datePickerType
    } = this.state;

    const {
      studentVal,
      maritalStatusVal,
      emailVal,
      zipCodeVal,
      educationVal
    } = this.state.inputData;
    const { addressList } = this.state.getData;

    const {
      birthday,
      fullName,
      industryVal,
      companyNameVal,
      monthlySalaryVal,
      residentialDetailAddressVal,
      residentialAddressVal
    } = this.state.inputData;

    const NEXT_BTN_TYPE = BasicInfo.watchInputData.call(this);
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
                inputFn={this.fullNameChange}
              />
              <InfoItem
                label="Birthday"
                value={birthday || ""}
                inputType="selectDate"
                placeholder="please choose"
                onClick={this.datePickerClick.bind(this)}
              />
            </div>

            <div className="info-group">
              <InfoItem
                label="Residential Address"
                value={residentialAddressVal || ""}
                inputType="select"
                placeholder="please choose"
                onClick={this.addressPicker.bind(this)}
              />
              <InfoItem
                inputDisabled={isInput}
                label="Detail Address"
                value={residentialDetailAddressVal || ""}
                inputType="input"
                placeholder="input detail location"
                inputFn={this.residentialDetailAddressChange}
              />
              <InfoItem
                inputDisabled={isInput}
                inputType="input"
                label="Zip Code"
                placeholder="input zip code"
                inputFn={this.zipChange}
                value={zipCodeVal || ""}
              />
              <InfoItem
                inputDisabled={isInput}
                inputType="input"
                label="Email ID"
                placeholder="input email address"
                inputFn={this.emailChange}
                value={emailVal || ""}
              />
            </div>

            <div className="info-group">
              <InfoItem
                label="Education"
                inputType="select"
                value={educationVal || ""}
                placeholder="please choose"
                onClick={this.selectClick.bind(this, 3)}
              />

              <InfoItem
                inputType="select"
                label="Student"
                placeholder="please choose"
                value={studentVal || ""}
                onClick={this.selectClick.bind(this, 1)}
              />

              <InfoItem
                inputType="select"
                label="Marital Status"
                placeholder="please choose"
                value={maritalStatusVal || ""}
                onClick={this.selectClick.bind(this, 2)}
              />
            </div>
            <div className="info-group">
                <InfoItem
                  label="Industry"
                  value={industryVal || ""}
                  inputType="select"
                  placeholder="please choose"
                  onClick={this.selectClick.bind(this, 4)}
                />
                <InfoItem
                  inputDisabled={isInput}
                  label="Company Name"
                  value={companyNameVal || ""}
                  inputType="input"
                  placeholder="input the full company name"
                  inputFn={this.companyNameChange}
                />
                <InfoItem
                  inputDisabled={isInput}
                  label="Monthly Salary Before Tax(₹)"
                  value={monthlySalaryVal || ""}
                  inputType="number"
                  placeholder="please input"
                  inputFn={this.monthlySalaryChange}
                  onClick={() => {
                    this.setState(prevState => ({
                      inputData: Object.assign({}, prevState.inputData, {
                        monthlySalaryVal: ""
                      })
                    }));
                  }}
                />
            </div>
          </div>

          <ShowPage show={isInput}>
            <NextClick
              className={this.$utils.setClassName([
                "next",
                NEXT_BTN_TYPE ? "" : "on"
              ])}
              text="CONTINUE"
              clickFn={this.nextClick}
            />
          </ShowPage>

          {/* 选择模态框 */}
          <SelectModal
            CloseFn={this.modalCancelClick}
            confirmDataClickFn={this.modalConfirm}
            modal_list={modalList}
            modal_title={modalTitle}
            modal_type={modalType}
            cancelDataClickFn={this.modalCancelClick}
          />
          {/*时间选择*/}
          <DatePicker
            show={datePickerType}
            showFn={this.datePickerShowTypeChange.bind(this)}
            confirmDateFn={this.datePickerConfirmDate.bind(this)}
          />

          {/* 区域选择框 */}
          <RangePicker
            data={addressList}
            SelectFn={this.rangeSelect}
            show={addressShowType}
            onCloseFn={type =>
              this.setState(
                Object.assign({}, this.state, { addressShowType: type })
              )
            }
          />
        </div>
      </ShowPage>
    );
  }

  getData() {
    this.isRefresh = false;
    getBasicInfo().then(res => {
      if (res.code === 0) {
        const { getData, selectData } = res.data;
        // 获取数据赋值
        this.setState(
          prevState => ({
            getData: Object.assign(
              {},
              prevState.getData,
              Object.assign({}, getData, selectData)
            )
          }),
          () => {
            // 数据渲染到页面上
            const {
              religionId,
              studentId,
              maritalStatusId,
              religionList,
              studentList,
              maritalList,
              educationList,
              educationId,
              industryList,
              industryId
            } = this.state.getData;
            // 设置select数组isSelect属性
            BasicInfo.setAllListSelectType.call(
              this,
              { religionId, religionList },
              {
                studentId,
                studentList
              },
              { maritalStatusId, maritalList },
              { educationId, educationList },
              { industryId, industryList }
            );
            // 根据id渲染label
            const religion_data = BasicInfo.whereIdSelectData(
              religionId,
              religionList
            );
            const student_data = BasicInfo.whereIdSelectData(
              studentId,
              studentList
            );
            const marital_data = BasicInfo.whereIdSelectData(
              maritalStatusId,
              maritalList
            );
            const education_data = BasicInfo.whereIdSelectData(
              educationId,
              educationList
            );

            this.setState(prevState => ({
              inputData: Object.assign(
                {},
                prevState.inputData,
                prevState.getData,
                {
                  studentVal: (student_data && student_data.label) || "",
                  maritalStatusVal: (marital_data && marital_data.label) || "",
                  educationVal: (education_data && education_data.label) || ""
                }
              )
            }));
          }
        );
        this.setState({
          showPage: true
        });
      }
    });
  }

  /**
   * input select点击
   * @param type
   */
  selectClick(type) {
    if (!this.state.isInput) return;
    const {
      religionList,
      studentList,
      maritalList,
      educationList,
      industryList
    } = this.state.getData;
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
    this.setState(
      prevState =>
        Object.assign({}, prevState, {
          nowModalClick: now_type,
          modalTitle: title,
          modalList: arr
        }),
      () => {
        BasicInfo.changeModaType.call(this);
      }
    );
  }

  /**
   * 地址选择
   */
  addressPicker() {
    this.setState(prevState =>
      Object.assign({}, prevState, {
        addressShowType: true
      })
    );
  }

  /**
   * 时间选择
   */
  datePickerClick() {
    this.datePickerShowTypeChange();
  }

  /**
   * 时间选择模态框显示状态
   */
  datePickerShowTypeChange() {
    this.setState(prevState =>
      Object.assign({}, prevState, {
        datePickerType: !prevState.datePickerType
      })
    );
  }

  /**
   * 时间选中
   */
  datePickerConfirmDate(dateArr) {
    this.setState(prevState =>
      Object.assign({}, prevState, {
        inputData: Object.assign({}, prevState.inputData, {
          birthday: dateArr.reverse().join("-")
        })
      })
    );
  }

  /**
   * 当前选中的区域
   * @param data
   * @param input_value 输入的数据
   */
  rangeSelect(data, input_value, input_id) {
    this.setState(prevState =>
      Object.assign(
        {},
        prevState,
        {
          addressShowType: false
        },
        {
          inputData: Object.assign({}, prevState.inputData, {
            residentialAddressId: input_id,
            residentialAddressVal: input_value
          })
        }
      )
    );
  }

  emailChange(e) {
    const inputData = Object.assign({}, this.state.inputData, {
      emailVal: e.target.value
    });
    this.setState({
      inputData
    });
  }

  zipChange(e) {
    const inputData = Object.assign({}, this.state.inputData, {
      zipCodeVal: e.target.value
    });
    this.setState({
      inputData
    });
  }

  selectDateFn(date) {
    console.log(date);
    const inputData = Object.assign({}, this.state.inputData, {
      birthday: date
    });
    this.setState({
      inputData
    });
  }

  fullNameChange(e) {
    const inputData = Object.assign({}, this.state.inputData, {
      fullName: e.target.value.toUpperCase()
    });
    this.setState({
      inputData
    });
  }

  loanPupposeChange(e) {
    const inputData = Object.assign({}, this.state.inputData, {
      loanPurposeVal: e.target.value
    });
    this.setState({
      inputData
    });
  }

  educatedSchoolChange(e) {
    const inputData = Object.assign({}, this.state.inputData, {
      educatedSchoolVal: e.target.value
    });
    this.setState({
      inputData
    });
  }

  companyNameChange(e) {
    const inputData = Object.assign({}, this.state.inputData, {
      companyNameVal: e.target.value
    });
    this.setState({
      inputData
    });
  }

  monthlySalaryChange(e) {
    const inputData = Object.assign({}, this.state.inputData, {
      monthlySalaryVal: e.target.value
    });
    this.setState({
      inputData
    });
  }

  residentialDetailAddressChange(e) {
    const inputData = Object.assign({}, this.state.inputData, {
      residentialDetailAddressVal: e.target.value
    });
    this.setState({
      inputData
    });
  }

  /**
   * 单选项点击
   * @param index
   */
  modalItemClick(index) {
    const { modalList } = this.state;

    this.setState({
      modalList: modalList.map((item, i) => {
        if (i === index) {
          return Object.assign({}, item, {
            isSelect: !item.isSelect
          });
        }
        return Object.assign({}, item, {
          isSelect: false
        });
      })
    });
  }

  /**
   * modal取消按钮
   */
  modalCancelClick() {
    BasicInfo.resetModalData.call(this);
  }

  /**
   * modal确认按钮
   * @param data 选中的数据
   * @param list 修改后的select数组
   */
  modalConfirm(data, list) {
    const { nowModalClick, modalList } = this.state;
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
      this.setState(prevState => ({
        getData: Object.assign({}, prevState.getData, {
          [`${origianl_list}List`]: list
        })
      }));
    }

    this.setState(
      prevState => ({
        inputData: Object.assign({}, prevState.inputData, params)
      }),
      () => {
        BasicInfo.resetModalData.call(this);
      }
    );
  }

  /**
   * 银行声明上传文件数组
   * @param filesArr
   */
  fileAdd(filesArr) {
    const arr = filesArr.map((item, index) =>
      Object.assign({}, item, { id: index })
    );
    this.setState(
      prevState => ({
        inputData: Object.assign({}, prevState.inputData, {
          bankStatementFileAddArr: arr
        })
      }),
      () => {
        console.log(this.state.inputData.bankStatementFileAddArr);
      }
    );
  }

  /**
   * 银行声明文件删除数组
   * @param filesArr
   */
  fileDelete(filesArr) {
    this.setState(
      prevState => ({
        inputData: Object.assign({}, prevState.inputData, {
          bankStatementFileDeleteArr: filesArr
        })
      }),
      () => {
        console.log(this.state.inputData);
      }
    );
  }

  /**
   * 下一步
   */
  nextClick() {
    console.log(this.state.inputData);
    window.htmlOnShow = () => {
      this.getData();
    };
    if (BasicInfo.watchInputData.call(this)) {
      Toast.info("Please complete the information and upload the data!");
      return;
    }
    setBasicInfo(this.state.inputData).then(res => {
      Toast.success("save success");
      if (!res.data) return;
      const timer = setTimeout(() => {
        pageJump(res.data);
        clearTimeout(timer);
      }, 1000);
    });
  }

  /**
   * 重置modal数据
   */
  static resetModalData() {
    BasicInfo.changeModaType.call(this).then(() => {
      this.setState({
        nowModalClick: "",
        modalTitle: "",
        modalList: []
      });
    });
  }

  /**
   * 设置选项框是否选中字段
   * @param id  选中的id
   * @param list  选择数组
   */
  static setSelectListForMat(id, list) {
    return list.map(item =>
      Object.assign({}, item, {
        isSelect: id === item.id
      })
    );
  }

  /**
   * 更改modal显示
   * @returns {Promise<any>}
   */
  static changeModaType() {
    return new Promise(res => {
      this.setState(
        prevState => ({ modalType: !prevState.modalType }),
        () => {
          res();
        }
      );
    });
  }

  /**
   * 设置选择框数据
   */
  static setAllListSelectType(
    { religionId, religionList },
    { studentId, studentList },
    { maritalStatusId, maritalList },
    { educationId, educationList },
    { industryId, industryList }
  ) {
    this.setState(prevState => ({
      getData: Object.assign({}, prevState.getData, {
        religionList: BasicInfo.setSelectListForMat(religionId, religionList),
        studentList: BasicInfo.setSelectListForMat(studentId, studentList),
        maritalList: BasicInfo.setSelectListForMat(
          maritalStatusId,
          maritalList
        ),
        educationList: BasicInfo.setSelectListForMat(
          educationId,
          educationList
        ),
        industryList: BasicInfo.setSelectListForMat(industryId, industryList)
      })
    }));
  }

  /**
   * 根据id选择对应的数据
   * @param id
   * @param list
   */
  static whereIdSelectData(id, list) {
    const data = list.filter(item => item.id === id)[0];
    return data || null;
  }

  /**
   * 监听inputData数据变化
   * @returns {boolean}
   */
  static watchInputData() {
    const { inputData, getData } = this.state;

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
}
