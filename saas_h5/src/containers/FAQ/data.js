/**
 * Created by yaer on 2019/7/4;
 * @Email 740905172@qq.com
 * */

const { packageName: PACKAGE_NAME, email: EMAIL } = window.appInfo;
// 分类
export const HOT_QUESTION = "Hot Question";
export const REGISTER_LOGIN = "Register/Login";
export const ABOUT_LOAN = "About Loan";
export const ABOUT_US = "About Us";
export const REPAYMENT = "Repayment";

export default [
  {
    categorize: [ABOUT_US],
    label: `What's ${PACKAGE_NAME}?`,
    data: `<p>
    ${PACKAGE_NAME} is an Instant Personal Loan Platform for salaryman. ${PACKAGE_NAME} is
    driven by a certified NBFC, P C Financial Services Private Limited. The
    application process is completely online and support 24*7*365. Just
    three steps no more than 10 minutes. The cash is immediately transferred
    to the bank account of user. Compare to the 4-7 days to avail a loan
    from a bank, which also requires a lot of documentation and frequent
    visits to the branch office, ${PACKAGE_NAME} will be a good helper to solve your
    temporary turnaround difficulties.
  </p>`
  },
  {
    categorize: [ABOUT_US],
    label: `How to contact ${PACKAGE_NAME}?`,
    data: `<p>You can contact us at ${EMAIL}</p>`
  },
  {
    categorize: [ABOUT_LOAN],
    label: `Who can apply for a loan from ${PACKAGE_NAME}?`,
    data: `<p>Indian Individual</p>
    <p> 21-56 years old</p>
    <p>has a monthly source of income.</p>`
  },
  {
    categorize: [ABOUT_LOAN],
    label: `How does ${PACKAGE_NAME} work?`,
    data: `
      <p>Install the ${PACKAGE_NAME} app from the Play Store.</p>
      <p>Register yourself via your phone number.</p>
      <p>Select the product you want to apply.</p>
      <p>Take 3-5 minutes to fill in your basic information and upload your KYC documents, then submit.</p>
      <p>After submitting your application, we will review it as soon as possible, during which time you may receive a call to verify the information. The results will be reviewed in the fastest 5 minutes, and we will notify you of the results by SMS.
      E-sign loan agreement after the approval.
      E-sign successfully, we will disburse to you within 5 minutes and notify you by SMS.</p>
      `
  },
  {
    categorize: [ABOUT_LOAN],
    label: `Which cities are ${PACKAGE_NAME} currently in?`,
    data: `<p>${PACKAGE_NAME} is available for all cities in India.</p>`
  },
  {
    categorize: [ABOUT_LOAN],
    label: `What products does ${PACKAGE_NAME} offer？`,
    data: `<p>A portfolio of products with different maturities and amounts.</p>`
  },
  {
    categorize: [ABOUT_LOAN],
    label: `What is the basis for reviewing ${PACKAGE_NAME}?`,
    data: `<p>
    Based on the basic information you submitted, KYC documents and your
    historical repayments. Among them, we need your pan card to verify your
    identity and need your aadhaar card to prove your address.
  </p>`
  },
  {
    categorize: [ABOUT_LOAN],
    label: `Why is my application not approved?`,
    data: `<p>
    When we review a loan, we will review it from multiple dimensions. If
    your loan is not approved, this will not affect your credit. It may just
    because your situation does not match our rules and credit model. If
    you are rejected by the review, we will let you know when you can apply
    again on the homepage.
  </p>`
  },
  {
    categorize: [ABOUT_LOAN],
    label: `My KYC file is correct, but why didn't it pass?`,
    data: ` <p>
        Please make sure that the file you uploaded is clear and not a remake of
        the photo.
      </p>`
  },
  {
    categorize: [ABOUT_LOAN],
    label: `What are the documents I must submit for the loan?`,
    data: `
      <p>Each user who applies for a personal loan will be required to submit three kinds of document:</p>
      <p>&nbsp;</p>
      <h1>a. Proof of Identity:</h1>
      <p>We collect your PAN details as a Photo ID and Proof of Identity and is mandatory;</p>
      <p>&nbsp;</p>
      <h1>b. Proof of Address: </h1>
      <p>You may provide us with a copy of your printed e-Aadhaar(masked) or Passport or Voter ID. Each user must mandatorily submit the Proof of Identity along with one of the documents mentioned above as Address Proof. Please note that submission of Aadhar is voluntary and you may wish to submit any one of the other alternatives provided above.</p>
      <p>&nbsp;</p>
      <h1>c. Your selfie</h1>
      `
  },
  {
    categorize: [ABOUT_LOAN],
    label: `What's Masked Aadhaar and Why we wish you to mask your Aadhaar?`,
    data: `<p>
      In a bid to make the Aadhaar safer, the Unique Identification Authority
      of India (UIDAI) has introduced a new feature. The UIDAI has
      introduced 'Masked Aadhaar' which is an option that allows one to cover
      the 12-digit unique identity in the downloaded Aadhaar or e-Aadhaar
      Masked. Aadhaar Card only shows the last 4 digits of the Aadhaar number
      instead of the 12 digits in the regular. Hence, for your information
      security, we wish you could mask your Aadhaar Number. And We do not
      store Aadhaar after reviewing your application and never reveal it to
      anyone.
    </p>`
  },
  {
    categorize: [ABOUT_LOAN],
    label: `How to get your Aadhaar masked?`,
    data: `
      <p>&nbsp;</p>
      <h1>a. Download from UIDAI</h1>
      <p>Log on to the UIDAI website uidai.gov.in</p>
      <p>Under My Aadhaar Tab, click on “Download Aadhaar” link. Or Under Get Aadhar Section, click on “Download Aadhaar” link.</p>
      <p>On the next page, you have an option to download e -Aadhar using any one of three methods mentioned below:</p>
      <p>i.Using Aadhaar number: Resident can download e-Aadhaar by using the 12 digit Aadhaar number.</p>
      <p>ii.Using Enrollment ID (EID): Resident can download e-Aadhaar using the 14 digit ENO and 14 digit Date-Time stamp printed on the Enrolment slip.</p>
      <p>iii.Using Virtual ID (VID): Resident can also use the 16 digit VID number to download the 'e-Aadhaar'.</p>
      <p>Select the Checkbox “I want a masked Aadhaar?”.</p>
      <p>Do the Captcha Verification.</p>
      <p>Click on “Send OTP” button.</p>
      <p>Enter the OTP you have received on your registered mobile number on the next page.</p>
      <p>Complete a Quick Survey on the OTP page.</p>
      <p>Click on Verify and Download Button.</p>
      <p>&nbsp;</p>
      <p>*Important Note</p>
      <p>The Aadhaar electronic copy is a password protected document.</p>
      <p>The Aadhaar letter PDF Password will be in 8 characters</p>
      <p>Combination of the first four letters of your name (as in Aadhaar) in CAPITAL letters and Year of Birth in YYYY format.</p>
      <p>Example : Your name is AKASH Y KUMAR Your Year of Birth is 1989
      Then your e-Aadhaar password is AKAS1989</p>
      <p>&nbsp;</p>
      <h1>b. Mask the number by yourself</h1>
      <p>If you have requirements to mask the Aadhaar but it’s not convenient to download from UIDAI, you could also mask it by cover the number by yourself.</p>
    `
  },
  {
    categorize: [ABOUT_LOAN],
    label: `Why didn't I receive the audit result?`,
    data: `<p>Because the review data takes a certain amount of time, the audit result will be given at the latest 1 working day. If it exceeds 1 working day, please contact customer service in time: ${EMAIL}</p>`
  },
  {
    categorize: [ABOUT_LOAN],
    label: `Why is my bank account not verified?`,
    data: `<p>Please confirm that your bank account number is yours. Please confirm that your IFSC and bank account are not filled in incorrectly. Make sure your account is a savings account and not a fixed asset account, credit account or virtual account.</p>`
  },
  {
    categorize: [ABOUT_LOAN],
    label: `What's mobile linked with Aadhaar and why is it required?`,
    data: `<p>Mobile linked with Aadhaar is the mobile number which is updated in your Aadhaar. For e-signing the Loan Agreement, UIDAI will send you an OTP on this number. You can get your Mobile Number linked to your Aadhaar Card. And to know the process, you may click here.</p>`
  },
  {
    categorize: [ABOUT_LOAN],
    label: `Why should I e-sign the Loan Agreement?`,
    data: `<p>The Loan Agreement is a legally binding agreement that you need to sign before availing a Loan. The agreement states that once a loan is disbursed to you, you are liable to pay back the entire amount with interest and other charges as applicable within the maturity of the Loan.</p>`
  },
  /* {
    categorize: [ABOUT_LOAN],
    label: `I am unable to get the OTP for E-sign on my phone. What could be the possible reason?`,
    data: `
    <p>You will receive two OTPs once you click on the 'e-sign' via digio option. The first OTP will be sent to the same mobile number with which you have registered with us. The OTP is delivered by your network operator and network issues can prevent you from receiving the OTP. In case of any issues in this OTP, try again after switching off and restarting your phone. Or else, you can try after some time.</p>
    <p>The second OTP will be sent to the contact number that is linked to your Aadhaar. In case you don't have ready access to the same, you may find it at the UIDAI website. The OTP would also be delivered to the E-mail ID linked to your Aadhaar. If you still don't receive the second OTP, please reach out UIDAI or your nearest Aadhaar centre.</p>
    `
  }, */
  {
    categorize: [HOT_QUESTION, ABOUT_LOAN],
    label: `How will I receive the loan?`,
    data: `<p>When you fill out your personal information, we will ask you to provide your bank account and we will verify your account. After you sign the agreement, we will directly transfer the money into your bank account.</p>`
  },
  {
    categorize: [ABOUT_LOAN],
    label: `Why didn't I receive the loan?`,
    data: `
    <p>Your account may be incorrect. Please re-verify.</p>
    <p>If you have not received the payment after more than one working day, this may be due to the banking system. Please contact us at: ${EMAIL}</p>
    `
  },
  {
    categorize: [ABOUT_LOAN],
    label: `I did not receive the loan on time, will the repayment date be recalculated?`,
    data: `<p>We will start to calculate interest when the money is successfully transferred.</p>`
  },
  {
    categorize: [ABOUT_LOAN],
    label: `Why are the processing fees charges deducted from my loan amount upfront?`,
    data: `<p>Since there is some operational effort that goes into performing credit checks and sanctioning the loan, the processing fee is charged upfront from the loan amount. However, this is a standard practice followed by all financial entities.</p>`
  },
  {
    categorize: [ABOUT_LOAN],
    label: `What is the amount of my repayment?`,
    data: `
    <p>You can see your due amount on the app homepage and we will also notify you the repayment amount by sms.</p>
    `
  },
  {
    categorize: [ABOUT_LOAN],
    label: `When do I need to repay?`,
    data: `<p>You can log in to the app to see your repayment date.</p>`
  },
  {
    categorize: [ABOUT_LOAN],
    label: `How can I repay my money?`,
    data: `<p>If we have successfully disbursed to you, you can click on the Razorpay repayment portal of the app repayment homepage, and through the payment gateway repayment, we offer four repayment methods: Debit Card, UPI, Netbanking, wallet. We will soon provide more repayment methods for you to choose from.</p>`
  },
  {
    categorize: [ABOUT_LOAN],
    label: `Is there a fee for repayment?`,
    data: `<p>A third party will charge you a fee, and you are advised to use netbanking to repay it because his fees are relatively low.</p>`
  },
  {
    categorize: [HOT_QUESTION, ABOUT_LOAN],
    label: `What happens if I don't pay back on time?`,
    data: `<p>We will charge penalty fee per day. Your Credit score will be updated as a defaulter with credit rating agencies which will make it difficult for you to take loans with any bank or financial institution in the future. Companies also check an individual's credit score and may not offer you employment if your credit score is bad.</p>`
  },
  {
    categorize: [ABOUT_LOAN],
    label: `I can't repay from APP. Is there any other way to repay?`,
    data: `<p>No. Your can only repay in the four ways provided on the mobile phone. If you encounter repayment problems, please contact customer service at ${EMAIL}</p>`
  },
  {
    categorize: [ABOUT_LOAN],
    label: `Can I repay in advance?`,
    data: `<p>You can pay back in advance at any time. No prepayment fee.</p>`
  },
  {
    categorize: [],
    label: `Can I make a partial payment on the due date?`,
    data: `<p>No, you need to pay off all the money on the repayment date.</p>`
  },
  {
    categorize: [ABOUT_LOAN],
    label: `Can I extend the loan period?`,
    data: `<p>No, we don't currently offer such a service.</p>`
  },
  {
    categorize: [HOT_QUESTION, ABOUT_LOAN],
    label: `How can I confirm that I have repaid successfully?`,
    data: `<p>When you pay off, we will notify you by text message. At the same time, log in to the app to see the records you have repaid.</p>`
  },
  {
    categorize: [HOT_QUESTION, ABOUT_LOAN],
    label: `Money got deducted from my account, but it is not showing in the system. Why？`,
    data: `<p>There can be times when, because of network issues or issues with the payment gateway, the money gets debited from your account but it does not get accepted by the payment gateway. In the case, you should get your money back within 7 working days. If you still do not get the money back to your account, please get in touch with us on ${EMAIL} with screenshots for us to help you.</p>`
  },
  {
    categorize: [ABOUT_LOAN],
    label: `When I borrow again, do I still need to fill in my personal information?`,
    data: `<p>You do not need to submit your information again, but if your information changes, please update your information in My Profile on app in time or contact customer service to change. In this regard, we will re-review.</p>`
  },
  {
    categorize: [REGISTER_LOGIN],
    label: `I can not receive the verification code for registration or login.`,
    data: `<p>Please make sure your mobile phone number is correct. If you still can't receive the verification code, it may be the operator's fault. Please re-send otp later or contact customer service.</p>`
  },
  {
    categorize: [ABOUT_US],
    label: `I am facing technical issues. What do I do?`,
    data: `<p>Please send a screenshot to our email and describe the problem: ${EMAIL}</p>`
  },
  {
    categorize: [ABOUT_LOAN],
    label: `Why can't I apply for a higher amount?`,
    data: `<p>You need to apply from the most basic products. According to your credit and repayment records, your credit will gradually accumulate and the amount will gradually increase.</p>`
  },
  {
    categorize: [REGISTER_LOGIN],
    label: `What if my app shuts down before I could complete my process?`,
    data: `<p>You can begin exactly from where you left off, once you re-start the app.</p>`
  },
  {
    categorize: [HOT_QUESTION, ABOUT_US],
    label: `Is my data safe with ${PACKAGE_NAME}?`,
    data: `<p>Yes, your data is safe with us. It is transferred over a secure connection to us, and we do not share it with anyone without your consent, except with the lenders.</p>`
  },
  {
    categorize: [ABOUT_US],
    label: `Does ${PACKAGE_NAME} cooperate with credit agencies?`,
    data: `<p>Yes, we have cooperate with credit agencies. We need to send the customer's repayment history to the credit institution every quarter.</p>`
  },
  /* {
    categorize: [ABOUT_US],
    label: `What is the interest charged on the loans that I avail with ${PACKAGE_NAME}?`,
    data: `<p>For all product we charge a flat interest rate of 33% (Annualized Interest Rate)</p>`
  }, */
  {
    categorize: [ABOUT_LOAN],
    label: `What is the processing fee charged on loans taken?`,
    data: `
    <p>For Personal Loan 15 Day tenure loan products - Processing fee is charged upto 0.66% per day.</p>
    <p>Note: GST charges &18% are applicable on Processing Fee</p>
    <p>The processing fee(including GST) are deducted from your loan amount and the remaining amount will be disbursed to your account.</p>
    `
  },
  /* {
    categorize: [ABOUT_LOAN],
    label: `Are there any late payment charges?`,
    data: `<p>You will be charged a per-day penalty charge of 2% of the installment principal amount for each and every installment that is overdue. Note that the late payment charges are to be paid in addition to the regular interest payment. To avoid late payment charges and reporting of delayed payments to Credit Bureaus, you are highly advised to repay on or before the due date.</p>`
  }, */
  {
    categorize: [],
    label: `What‘s the overview of ${PACKAGE_NAME} loan product?`,
    data: `
    <p>Loan Amount: from ₹ 1,000 to 60,000.</p>
    <p>Tenure: the shortest tenure is 91 days, the longest tenure is 365 days. Interest rate: Depends on the customer's risk profile and loan tenure. The maximum interest rate is 33% per annum.</p>
    <p>For example: If the loan amount is ₹10,000 and the interest rate is 30% per annum with the tenure of 91 days, after deducting the processing fee, the interest payable is as follows :</p>
    <p>Interest = ₹ 10,000 * 30% / 365 * 91 = ₹ 748.</p>
    <p></p>
    `
  },
  {
    categorize: [HOT_QUESTION, ABOUT_LOAN],
    label: `Can I borrow several loans at the same time?`,
    data: `<p>No. At the same time, you can only borrow one. Please pay off one and then borrow the next one.</p>`
  },
  {
    categorize: [ABOUT_LOAN],
    label: `Can I cancel my loan?`,
    data: `<p>Once the loan application has been submitted, the loan can't be cancelled.</p>`
  },
  {
    categorize: [REGISTER_LOGIN],
    label: `What if I need to change my mobile number?`,
    data: `<p>You can contact customer service to modify your mobile phone number. We do not support directly modifying the mobile phone number on the app.</p>`
  },
  {
    categorize: [REPAYMENT],
    label: `Payment not update`,
    data: `
    <p>1）Third party received，but ${PACKAGE_NAME} not update</p>
    <p>If you already paid your loan, but the application did not show repayment successful, please share with us the screenshot of your payment receipt, bank statement and loan ID or mobile number on ${EMAIL}, we will help take a look at your problem.</p>
    <br />
    <p>2）Neither ${PACKAGE_NAME} nor third party received，how to do？</p>
    <p>It will take 7 to 10 days of working days in order to return your money to your bank account, if after the 10 days period the money has not credited into your bank account, please share your transaction detail and bank statement with your loan ID or mobile number on ${EMAIL}, we will help take a look at your problem.</p>
    `
  },
  {
    categorize: [REPAYMENT],
    label: `Repay to a wrong bank account`,
    data: `
    <p>1）If you repaid to a wrong VA ID which is still ${PACKAGE_NAME}’s ,how to do？</p>
    <p>If there are any mistake or problem with Account number payment, please share your please share your transaction detail and bank statement with your loan ID or mobile number on ${EMAIL}, we will help take a look at your problem.</p>
    <br />
    <p>2）If you repaid to a wrong VA ID which is not ${PACKAGE_NAME}’s, how to do？</p>
    <p>If you repaid to a wrong VA ID which is not ${PACKAGE_NAME}’s, this issue caused by customer himself, so customer still need to repay. Due to mistake in account number, your payment is not received by us, please repay the amount of your loan in order to avoid paying late fees and interest. If you have further problem please contact us on ${EMAIL}, we will help take a look at your problem.</p>
    `
  },
  {
    categorize: [REPAYMENT],
    label: `Payment failed`,
    data: `
    <p>１）You payment failed because of bank system issues、 tech issues of third party or insufficient balance etc. How to do？</p>
    <p>Due to technical issues, with the bank your payment is unsuccessful and has been rejected, the amount that has been debited, will be return to your account within 7 to 10 days working days. We request you to repay your loan with debit card, net banking or wallet.</p>
    <br />
    <p>２）If your repayment failed by the above gateway，read this:</p>
    <p>Due to technical issues, with the bank your payment is unsuccessful and has been rejected, the amount that has been debited, will be return to your account within 7 to 10 days working days. We request you to repay your loan with bank transfer.</p>
    `
  },
  {
    categorize: [REPAYMENT],
    label: `Double paid `,
    data: `
    <p>If you double paid, how to do？</p>
    <p>If you accidently paid double, please share with us your please share your transaction detail and bank statement with your loan ID or mobile number on ${EMAIL}, we will help take a look at your problem.</p>
    `
  }
];
