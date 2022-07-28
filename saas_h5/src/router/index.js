/**
 * Created by yaer on 2019/3/4;
 * @Email 740905172@qq.com
 * */
import React from "react";
import { Switch, Route } from "react-router-dom";
import NotFonund from "../containers/NotFound";

import AsyncLoader from "../components/Async-loader";

import { replaceAppName } from "../vest";
import { getAppAttributes } from "../nativeMethod";
const { packageName } = replaceAppName(getAppAttributes().packageName);
let routerList = [
  {
    path: "/test",
    component: AsyncLoader(() => import("../containers/Test")),
    params: {
      title: "test",
    },
  },

  // 基本信息
  {
    path: "/basicInfo/:isInput",
    component: AsyncLoader(() => import("../containers/BasicInfo")),
    params: {
      title: "Basic Info",
    },
  },

  // 工作信息
  {
    path: "/workInfo/:isInput",
    component: AsyncLoader(() => import("../containers/WorkInfo")),
    params: {
      title: "Work Info",
    },
  },

  // 银行卡信息
  {
    path: "/bankAccountInfo/:isInput",
    component: AsyncLoader(() => import("../containers/BankAccountInfo")),
    params: {
      title: "Bank Account Details",
    },
  },

  // 还款方式
  {
    path: "/repaymentMethod/:isInput",
    component: AsyncLoader(() => import("../containers/RepaymentMethod")),
    params: {
      title: "Repayment Method",
    },
  },

  // 个人中心
  {
    path: "/PersonalCenter",
    component: AsyncLoader(() => import("../containers/PersonalCenter")),
    params: {
      title: "Personal Center",
    },
  },

  // 帮助中心
  {
    path: "/helpCenter",
    component: AsyncLoader(() => import("../containers/HelpCenter")),
    params: {
      title: "Customer Service",
    },
  },

  // 问题解答
  {
    path: "/faq",
    component: AsyncLoader(() => import("../containers/FAQ")),
    params: {
      title: "FAQ",
    },
  },

  // 银行卡列表
  {
    path: "/bankAccountNumber/:isInput",
    component: AsyncLoader(() => import("../containers/BankAccountNumber")),
    params: {
      title: "Bank Account Number",
    },
  },

  // 用户信息
  {
    path: "/userInfo",
    component: AsyncLoader(() => import("../containers/UserInfo")),
    params: {
      title: "Personal Center",
    },
  },

  // 订单
  {
    path: "/paymentOrder/:recordsType",
    component: AsyncLoader(() => import("../containers/Order")),
    params: {
      title: "Personal Center",
    },
  },

  // 订单详情
  {
    path: "/orderDetail/:orderId",
    component: AsyncLoader(() => import("../containers/OrderDetail")),
    params: {
      title: packageName,
    },
  },

  // 关于我们
  {
    path: "/aboutUs",
    component: AsyncLoader(() => import("../containers/AboutUs")),
    params: {
      title: "About Us",
    },
  },

  // 征信报告
  {
    path: "/creditReport",
    component: AsyncLoader(() => import("../containers/CreditReport")),
    params: {
      title: "Credit Report",
    },
  },

  // 认证状态
  {
    path: "/authStatus",
    component: AsyncLoader(() => import("../containers/AuthStatus")),
    params: {
      title: "Auth Status",
    },
  },

  // 税单填写公司信息
  {
    path: "/taxBillInputCompanyInfo",
    component: AsyncLoader(() =>
      import("../containers/TaxBillInputCompanyInfo")
    ),
    params: {
      title: "Tax Bill",
    },
  },

  // 税单选择公司
  {
    path: "/taxBillSelectCompany",
    component: AsyncLoader(() => import("../containers/TaxBillSelectCompany")),
    params: {
      title: "Tax Bill",
    },
  },

  // 用户协议v2 应用于第三个包（rupperPlus）起
  {
    path: "/agreement/:appName/user",
    component: AsyncLoader(() => import("../containers/UserAgreement-V2")),
    params: {
      title: "User Agreement",
    },
  },

  // 使用协议v2 应用于第三个包（rupperPlus）起
  {
    path: "/agreement/:appName/use",
    component: AsyncLoader(() => import("../containers/UseAgreement-V2")),
    params: {
      title: "Use Agreement",
    },
  },

  // 隐私协议
  {
    path: "/agreement/bigshark/privacy",
    component: AsyncLoader(() =>
      import("../containers/PrivacyAgreement-bigshark-v2")
    ),
    params: {
      title: "Privacy Agreement",
    },
  },
  // 隐私协议
  {
    path: "/agreement/getRupee/privacy",
    component: AsyncLoader(() =>
      import("../containers/PrivacyAgreement-getRupee")
    ),
    params: {
      title: "Privacy Agreement",
    },
  },

  {
    path: "/agreement/hipaisa/privacy",
    component: AsyncLoader(() =>
      import("../containers/PrivacyAgreement-hipaisa")
    ),
    params: {
      title: "Privacy Agreement",
    },
  },
  {
    path: "/agreement/dailyrupee/privacy",
    component: AsyncLoader(() =>
      import("../containers/PrivacyAgreement-dailyRupee")
    ),
    params: {
      title: "Privacy Agreement",
    },
  },
  {
    path: "/agreement/firstcash/privacy",
    component: AsyncLoader(() =>
      import("../containers/PrivacyAgreement-firstCash")
    ),
    params: {
      title: "Privacy Agreement",
    },
  },
  {
    path: "/agreement/whaleloan/privacy",
    component: AsyncLoader(() =>
      import("../containers/PrivacyAgreement-whaleLoan")
    ),
    params: {
      title: "Privacy Agreement",
    },
  },
  {
    path: "/agreement/rupeefirst/privacy",
    component: AsyncLoader(() =>
      import("../containers/PrivacyAgreement-rupeeFirst")
    ),
    params: {
      title: "Privacy Agreement",
    },
  },

  // 隐私协议
  {
    path: "/agreement/moneyclick/privacy",
    component: AsyncLoader(() =>
      import("../containers/PrivacyAgreement-moenyclick/")
    ),
    params: {
      title: "Privacy Agreement",
    },
  },

  // 隐私协议
  {
    path: "/agreement/newcash/privacy",
    component: AsyncLoader(() =>
      import("../containers/PrivacyAgreement-newCash")
    ),
    params: {
      title: "Privacy Agreement",
    },
  },

  // 隐私协议
  {
    path: "/agreement/wealthsteward/privacy",
    component: AsyncLoader(() =>
      import("../containers/PrivacyAgreement-wealthSteward")
    ),
    params: {
      title: "Privacy Agreement",
    },
  },

  // rupeefanta隐私协议
  {
    path: "/agreement/rupeefanta/privacy",
    component: AsyncLoader(() =>
      import("../containers/PrivacyAgreement-rupeeFanta")
    ),
    params: {
      title: "Privacy Agreement",
    },
  },

  {
    path: "/agreement/hindmoney/privacy",
    component: AsyncLoader(() =>
      import("../containers/PrivacyAgreement-hindMoney")
    ),
    params: {
      title: "Privacy Agreement",
    },
  },

  {
    path: "/agreement/luckywallet/privacy",
    component: AsyncLoader(() =>
      import("../containers/PrivacyAgreement-luckyWallet")
    ),
    params: {
      title: "Privacy Agreement",
    },
  },
  {
    path: "/agreement/dreamloan/privacy",
    component: AsyncLoader(() =>
      import("../containers/PrivacyAgreement-dreamLoan")
    ),
    params: {
      title: "Privacy Agreement",
    },
  },
  {
    path: "/agreement/chiefloan/privacy",
    component: AsyncLoader(() =>
      import("../containers/PrivacyAgreement-chiefLoan")
    ),
    params: {
      title: "Privacy Agreement",
    },
  },
  {
    path: "/agreement/orangkaya/privacy",
    component: AsyncLoader(() =>
      import("../containers/PrivacyAgreement-orangKaya")
    ),
    params: {
      title: "Privacy Agreement",
    },
  },
  {
    path: "/agreement/easycash/privacy",
    component: AsyncLoader(() =>
      import("../containers/PrivacyAgreement-easyCash")
    ),
    params: {
      title: "Privacy Agreement",
    },
  },
  {
    path: "/agreement/happywallet/privacy",
    component: AsyncLoader(() =>
      import("../containers/PrivacyAgreement-happyWallet")
    ),
    params: {
      title: "Privacy Agreement",
    },
  },
  {
    path: "/agreement/bluecash/privacy",
    component: AsyncLoader(() =>
      import("../containers/PrivacyAgreement-blueCash")
    ),
    params: {
      title: "Privacy Agreement",
    },
  },

  {
    path: "/agreement/rupeecash/privacy",
    component: AsyncLoader(() =>
      import("../containers/PrivacyAgreement-rupeeCash")
    ),
    params: {
      title: "Privacy Agreement",
    },
  },

  // rupeecash隐私协议
  {
    path: "/agreement/dhancash/privacy",
    component: AsyncLoader(() =>
      import("../containers/PrivacyAgreement-dhanCash")
    ),
    params: {
      title: "Privacy Agreement",
    },
  },

  // rupeecash隐私协议
  {
    path: "/agreement/rupeelaxmi/privacy",
    component: AsyncLoader(() =>
      import("../containers/PrivacyAgreement-rupeeLaxmi")
    ),
    params: {
      title: "Privacy Agreement",
    },
  },
  // excellentcash隐私协议
  {
    path: "/agreement/excellentcash/privacy",
    component: AsyncLoader(() =>
      import("../containers/PrivacyAgreement-excellentCash")
    ),
    params: {
      title: "Privacy Agreement",
    },
  },

  // 隐私协议
  {
    path: "/agreement/lovecash/privacy",
    component: AsyncLoader(() =>
      import("../containers/PrivacyAgreement-loveCash")
    ),
    params: {
      title: "Privacy Agreement",
    },
  },

  // 用户委托协议
  {
    path: "/userCommissionedAgreement",
    component: AsyncLoader(() =>
      import("../containers/UserCommissionedAgreement")
    ),
    params: {
      title: "User Commissioned Agreement",
    },
  },

  // 用户授权协议
  {
    path: "/userAuthorizationAgreement",
    component: AsyncLoader(() =>
      import("../containers/UserAuthorizationAgreement")
    ),
    params: {
      title: "User Authorization Agreement",
    },
  },
  // 贷款服务合同
  {
    path: "/loanServiceContract",
    component: AsyncLoader(() => import("../containers/LoanServiceContract")),
    params: {
      title: "Loan Service Contract",
    },
  },

  // 借款被拒
  {
    path: "/loanRejected",
    component: AsyncLoader(() => import("../containers/LoanRejected")),
    params: {
      title: "Loan Rejected",
    },
  },

  // 借款被拒
  {
    path: "/audit/:status",
    component: AsyncLoader(() => import("../containers/Audit")),
    params: {
      title: packageName,
    },
  },

  // 优惠券列表
  {
    path: "/couponsList/:isSelect",
    component: AsyncLoader(() => import("../containers/CouponsList")),
    params: {
      title: "Coupons",
    },
  },

  // 设置
  {
    path: "/settings",
    component: AsyncLoader(() => import("../containers/Settings")),
    params: {
      title: "settings",
    },
  },

  // 提示下载
  {
    path: "/download",
    component: AsyncLoader(() => import("../containers/Download")),
    params: {
      title: "Notification",
    },
  },

  // 协议
  {
    path: "/sanctionLetter",
    component: AsyncLoader(() => import("../containers/SanctionLetter")),
    params: {
      title: packageName,
    },
  },

  // 协议
  {
    path: "/demandPromissoryNote",
    component: AsyncLoader(() => import("../containers/DemandPromissoryNote")),
    params: {
      title: packageName,
    },
  },

  // 借款确认页面
  {
    path: "/loanConfirm",
    component: AsyncLoader(() => import("../containers/LoanConfirm")),
    params: {
      title: packageName,
    },
  },

  // ekyc
  {
    path: "/ekycInfo",
    component: AsyncLoader(() => import("../containers/EkycInfo")),
    params: {
      title: "EKYC Info",
    },
  },

  // 唤起页面
  {
    path: "/arousal",
    component: AsyncLoader(() => import("../containers/ArousalPage")),
    params: {
      title: packageName,
    },
  },

  // 线下还款页面
  {
    path: "/repayTransferBank",
    component: AsyncLoader(() => import("../containers/RepayTransferBank")),
    params: {
      title: "Repayment",
    },
  },

  // 代扣信息填写
  {
    path: "/withholding",
    component: AsyncLoader(() => import("../containers/Withholding")),
    params: {
      title: "Info",
    },
  },

  // 提现页面（新确认借款页面）
  {
    path: "/withdrawals",
    component: AsyncLoader(() => import("../containers/Withdrawals")),
    params: {
      title: packageName,
    },
  },

  // 申请页面
  {
    path: "/applyLoan",
    component: AsyncLoader(() => import("../containers/ApplyLoan")),
    params: {
      title: packageName,
    },
  },
  // 问题认证
  {
    path: "/problemAuth",
    component: AsyncLoader(() => import("../containers/ProblemAuth")),
    params: {
      title: "Verification",
    },
  },

  // 新借款申请协议v2
  {
    path: "/summary",
    component: AsyncLoader(() => import("../containers/Summary")),
    params: {
      title: packageName,
    },
  },

  // 暂停放款
  {
    path: "/suspendLending",
    component: AsyncLoader(() => import("../containers/SuspendLending")),
    params: {
      title: packageName,
    },
  },

  // 确认还款页面
  {
    path: "/confirmRepayment",
    component: AsyncLoader(() => import("../containers/ConfirmRepayment")),
    params: {
      title: "Confirm Repayment",
    },
  },
  // 投诉页面
  {
    path: "/complaints",
    component: AsyncLoader(() => import("../containers/Complaints")),
    params: {
      title: "Feedback",
    },
  },
  // 减免审核页面
  {
    path: "/applyReduction",
    component: AsyncLoader(() => import("../containers/ApplyReduction")),
    params: {
      title: "Apply Reduction",
    },
  },

  // 减免审核结果页面
  {
    path: "/applyReductionResult",
    component: AsyncLoader(() => import("../containers/ApplyReductionResult")),
    params: {
      title: packageName,
    },
  },

  // mpurse upi repayment
  {
    path: "/mpurseUpiRepayment",
    component: AsyncLoader(() => import("../containers/MpurseUpiRepayment")),
    params: {
      title: "Mpurse Upi",
    },
  },

  // mpurse upi repayment success
  {
    path: "/mpurseUpiRepaymentSuccess",
    component: AsyncLoader(() =>
      import("../containers/MpurseUpiRepaymentSuccess")
    ),
    params: {
      title: "SUCCESS",
    },
  },
  // sifang repayment success
  {
    path: "/siFangRepaymentSuccess",
    component: AsyncLoader(() =>
      import("../containers/SiFangRepaymentSuccess")
    ),
    params: {
      title: "SUCCESS",
    },
  },

  // transfer还款页面
  {
    path: "/transferRepayment",
    component: AsyncLoader(() => import("../containers/TransferRepayment")),
    params: {
      title: "Repayment",
    },
  },
  {
    path: "/payU",
    component: AsyncLoader(() => import("../containers/PayU")),
    params: {
      title: "PayU",
    },
  },
];

// 根据包名生成链接
routerList = routerList.concat(
  routerList.map((item) =>
    Object.assign({}, item, {
      path: item.path.replace(/\//, `/${getAppAttributes().packageName}!`),
    })
  )
);
const Router = () => (
  <Switch>
    {routerList.map((item, index) => (
      <Route
        key={index}
        exact
        path={item.path}
        render={(routeProps) => {
          const { match } = routeProps;
          if (/paymentOrder/.test(match.url)) {
            let value = "";
            if (match.params.recordsType === "loanRecords") {
              value = "Loan Records";
            } else {
              value = "Repay Records";
            }
            item.params = { title: value };
          }
          return <item.component {...Object.assign({}, routeProps, item)} />;
        }}
      />
    ))}
    <Route component={NotFonund} />
  </Switch>
);

export default Router;
