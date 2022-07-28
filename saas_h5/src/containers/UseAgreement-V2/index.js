/* eslint-disable react/no-unescaped-entities */
/**
 * Created by yaer on 2019/7/26;
 * @Email 740905172@qq.com
 * */
import { useDocumentTitle } from "../../hooks";
import { replaceAppName } from "../../vest";

import "./index.less";

const UseAgreement = props => {
  const {
    packageName,
    company,
    address
  } = replaceAppName(props.match.params.appName);
  useDocumentTitle(props);
  return (
    <div className="use-agreement-wrapper">
      <h1>{packageName}</h1>
      <h2>Terms of use</h2>

      <p>
        These are the terms of use of {packageName} {company}({packageName})with
        its corporate office {address}
      </p>
      <p>
        Thank you for visiting our website. These Terms outline the usage policy
        governing this Website and {packageName}&nbsp;
        {company} ( {packageName}) technology-based services which enable Pay
        Day Loan lending transactions. By visiting the Website,you expressly
        agree to use the Website and services in the manner described in this
        Policy. If you do not wish to agree to these T&C( Terms Conditions),
        please refrain from using this site.
      </p>
      <p>Refund/Return/ Cancellation Policy.</p>
      <p>
        Currently we do not have refund cancellation policy. If users have any
        issues on cancellation pls feel free to contact with our help center
        through {packageName} App. This Website is protected by copyright and
        other intellectual property laws of India.
      </p>

      <h3>1. COLLECTION OF INFORMATION</h3>
      <p>
        During your use of the {packageName} Platform, we will collect
        information and data including but not limited to your personal
        information and sensitive personal information / data (“SPD & I”) as
        defined under the Information Technology (Reasonable security practices
        and procedures and sensitive personal data or information) Rules, 2011.
        You hereby provide us explicit consent to collect the following data
        from you as a data provider which also includes SPD & I:
      </p>
      <p>
        1.11 Facilitating purchase and sale of Product between the Users on its
        Platform;{" "}
      </p>
      <p>
        1.12 Assisting its customers to obtain a Product from various banks who
        are partnered with {packageName};
      </p>
      <p>
        1.13、Assisting its Users to obtain their credit report through
        authorized agents partnered with {packageName}. {packageName}
        will monitor and update the credit report obtained by the customer
        through the use of the Platform as and when
        {packageName} receives such report from the authorized agents;
      </p>
      <p>
        1.14、Provision of support to the Users in verifying the financial
        capabilities of Users;
      </p>
      <p>
        1.15、{packageName} will also provide You with spend analysis which
        analyses Your income and expenses which is obtained by {packageName} by
        way of bank SMS scraping and through its integration with service
        provider.
      </p>
      <p>
        1.16、Provision of a social score based on User’s Facebook, Twitter,
        Linkedin or any other social media accounts that are linked with User’s
        account on the Platform.
      </p>
      <p>
        1.17、In the event You have registered Your phone number on the ‘Do Not
        Disturb’ registers with Your network provider, You shall ensure to take
        all steps to enable the Company’s representative’s to contact You via
        phone to provide details about different financial products and You
        shall ensure that such calls received by You are pursuant to You
        providing Us with information and You shall not register a compliant
        with the relevant authorities for the same.
      </p>
      <p>
        As detailed above, we also collect your personal information, including
        financial information, from the registration or other completed forms /
        questionnaires that You provide to Us. We will also receive your
        personal information, including financial information, from documents
        that you may provide to Us and/or from documents like the credit report
        that You authorize Us to obtain on Your behalf from credit information
        companies.
      </p>
      <p>
        The information We collect about You will depend on the products and
        services we offer, on an ongoing basis. The information collected from
        You will be used in order to effectively provide Services to You. If You
        do not allow Us to collect all the information We request, We may not be
        able to deliver all of these services effectively. You hereby provide us
        explicit consent to use the data specified above as per the terms of
        this Policy.
      </p>
      <h3>1.2. USE OF INFORMATION</h3>
      <p>
        You hereby provide explicit consent to Us for the use of Your
        information provided (including SPD & I) provided by You to Us (as
        specified above) and to share such information with third parties for
        the following purposes:
      </p>
      <p>1.21、Monitor, improve and administer our {packageName} Platform</p>
      <p>
        1.22、Manage our risks including the risk of fraud that may be committed
        against us or our partners;
      </p>
      <p>
        1.23、Analyze how the {packageName} Platform is used, diagnose service
        or technical problems and maintain security;
      </p>
      <p>
        1.24、Send communications notifications, information regarding the
        products or services requested by You or process queries and
        applications that you have made on the Website, including marketing and
        promotion of our Services or our {packageName} Platform by way of
        tele-messages or emails or calls;
      </p>
      <p>
        1.25、Manage our relationship with you and provide You with or inform
        you about other products or services we think you might find of some
        use;
      </p>
      <p>
        1.26、Conduct data analysis in order to improve the Services provide
      </p>
      <h3>1.3 STORAGE AND SECURITY OF INFORMATION</h3>
      <p>
        We store and process Your personal information, including the
        information received from Your Device on Amazon Cloud Servers and other
        secure cloud service providers. All information received by Us, either
        directly from You or through Your Device is protected and secured by Us.
        We adopt multiple safeguards to protect the security of the information
        and data provided by You, some of the safeguards We use are firewalls
        and data encryption using SSL, and information access authorization
        controls.
      </p>
      <p>
        We use reasonable safeguards to preserve the integrity and security of
        Your information against loss, theft, unauthorized access, disclosure,
        reproduction, use or amendment. To achieve the same, We use reasonable
        security practices and procedures as mandated under applicable laws for
        the protection of Your information. Information You provide to Us may be
        stored on Our secure servers located within or outside India. However,
        You understand and accept that there’s no guarantee that data
        transmission over the Internet will be completely secure and that any
        information that You transmit to Us is at Your own risk. We assume no
        liability for any disclosure of information due to errors in
        transmission, unauthorized third-party access to our Platform and data
        bases or other acts of third parties, or acts or omissions beyond Our
        reasonable control and You shall not be entitled to hold {packageName}{" "}
        responsible for any breach of security.
      </p>
      <h3>1.4 PHISHING</h3>
      <p>
        "Phishing" usually occurs when users of a website are induced by an
        individual/entity into divulging sensitive personal data by using
        fraudulent websites and/ or e-mail addresses. In the event of You
        providing information to a website or responding to an e-mail which does
        not belong to Us or is not connected with Us in any manner, You will be
        a victim of Phishing. We do not send e-mails requesting a user for
        payment information, user name or passwords. However, We may verify the
        user name, password etc. provided by You from time to time.
      </p>
      <p>
        2.No portion of this website may be copied in any manner whatsoever.
      </p>
      <p>
        3. This Website is for informational purposes only and provides details
        about {packageName} {company}({packageName}) Dand its offerings.
      </p>
      <p>
        4.While every attempt has been made to update the Website with latest
        information monied {company}({packageName} )does not warrant or
        represent that the information on the Website will be complete correct
        and accurate at all times.
      </p>
      <p>
        5.Only registered users shall be allowed to conduct transactions on the
        website. The terms for such transactions shall be as agreed with each
        user.
      </p>
      <p>
        6.Persons under age of 18 years are not permitted to get engaged with
        this site and have no permission to call for any services /to borrow
        through this site.
      </p>
      <p>
        7.You agree to protect, defend and indemnify us and hold us and our
        representatives harmless from and against any and all claims, damages,
        costs and expenses, arising from or related to your access and use of
        the App in violation of these T&C and/or your infringement, or
        infringement by any other user of your Account, of any intellectual
        property or other right of anyone.
      </p>
      <p>
        8. {packageName} {company}( {packageName} ) shall not be liable for any
        Direct, Indirect, Punitive Incidental, Special/ Consequential damages
        for any damages whatsoever including, without limitation, damages for
        loss of use, data / profits arising out of any way connected with the
        access/use/ Performance of this APP's Function Features Interruptions,
        Delay etc.
      </p>
      <p>
        9.These T&C shall apply when you complete the authentication process and
        create an account and shall remain valid and binding on you for so long
        as you maintain the Account.
      </p>
      <p>
        10. Your Use of this site and any t& c related to this site are subject
        to laws of India.
      </p>
      <p>
        11.Courts of Delhi have exclusive Jurisdiction of dealing with all
        disputes arising from use of this site.
      </p>
      <p>
        In regarding to this User Agreement, I consent to the NBFC/Platform
        accessing, viewing, using, and storing my credit information in their
        process of decision making for my loan application.
      </p>
    </div>
  );
};

export default UseAgreement;
