/* eslint-disable react/no-unescaped-entities */
/**
 * Created by yaer on 2019/7/25;
 * @Email 740905172@qq.com
 * */
import "./index.less";
import { useDocumentTitle } from "../../hooks";
import { replaceAppName } from "../../vest";

const UserAgreement = props => {
  const { packageName, company } = replaceAppName(props.match.params.appName);
  useDocumentTitle(props);
  return (
    <div className="user-agreement-wrapper">
      <h1>{packageName}</h1>
      <h2>User Agreement</h2>
      <p>
        This user agreement (hereinafter referred to as "agreement" )is
        concluded between the user and &nbsp;
        {company}(hereinafter referred to as "Company")about the user's use of
        services related to "{packageName}" (hereinafter referred to as
        "Platform"). The user should carefully read and understand this
        agreement. when the user clicks on the platform to confirm this
        agreement, it indicates that the user agrees to abide by all the
        stipulations of this agreement in the event of legal disputes the user
        should not defer with the reason of failing to carefully read or
        understand this agreement.
      </p>
      <h3>1. User Confirmation and Service Acceptance.</h3>
      <p>
        1.1 The registered user shall be over 18 years old with full capacity
        for civil right and civil conduct. If the user fails to meet the above
        requirement, the platform has the right to terminate the services
        provided to the user at any time.
      </p>
      <p>
        1.2 The user shall promise to use the internet and abide by relevant
        state policies and laws, such as criminal law, national security act,
        secrecy law, security statutes for computer information system, and
        intellectual property law, etc.
      </p>
      <p>
        1.3 The user shall promise to provide accurate personal data and
        information (including but not limited to name, Addhaar card number, pan
        card number, contact way, permanent address, personal bank account,
        working condition, family information, marital status, social accounts,
        mobile service account password, contact information and other
        information hereinafter referred to as"user information") and update in
        time when there is a change of personal data. All the consequences
        arising from the inaccurate, untrue or untimely updates of the user's
        personal data shall be borne by the user, and the platform has the right
        to terminate the contract at any time and require the user to undertake
        all adverse responsibilities (including but not limited to the service
        charges, interest late payment penalty, and compensation for damage,
        etc.
      </p>
      <p>
        1.4 The user shall promise that a series of behaviors related to account
        registration, login and information change are operated by the user and
        should bear any legal consequences arising from the foregoing behaviors.
      </p>
      <p>
        1.5 If considering that the user information provided by the user is
        wrong, untrue, outdated and incomplete the platform has the right to
        send a correction notice to the user and has the right to directly
        delete or suspend some or all services to the user without taking the
        liability for breach of contract, and the platform has the right to
        require the user to perform the repayment obligations in advance.
      </p>
      <p>
        1.6 The user shall agree and authorize the platform and the platform's
        partners to get collect, inspect, store user information during the
        service operation and other information generated in the process of
        using this platform, including but not limited to all the information on
        user registration, the user's address list and corresponding contact
        addresses or other contact ways obtained by the platform and its
        partners through the third party, the user's portrait, work situation,
        family situation, marital status, social relations, recruitment,
        employment and transaction records and use in the platform or other
        similar platforms or institutions obtained y the platform and its
        partners or the third party authorized by the platform and its partners.
      </p>
      <p>
        1.7 Based on needs of operation, business or transaction security, the
        platform can temporar ily stop providing, limit, or change some or all
        functions of the platform, or provide new functions. In the event of any
        functional changes for the platform, if the user continues to use the
        platform, it is deemed that the user agrees with the agreement and
        recognizes the services after the platform changes.
      </p>
      <h3>2. Service Content</h3>
      <p>
        2.1 This platform provides the user's loan application and related
        personal information to the partners according to the user's
        authorization, this platform does not directly distribute the loan or
        financing to the user, and the loan applied by the user through this
        platform is provided by the NBFC KYC will be completed by the platform's
        partners (including but not limited to cooperation platforms,
        cooperative banks or third-party payment institutions), and this
        platform does not involve capital transactions.
      </p>
      <p>
        2.2 This platform will provide the loan collection service after the
        user's consent and authorization.
      </p>
      <p>
        2.3 Once the user successfully logins, the platform account and password
        will be obtained. The user shall be responsible for all activities and
        events carried out by this account on this platform. The user shall bear
        full responsibility for the damage to the user or any third party
        because the account and password get out of the user's control due to
        the user's fault.
      </p>
      <h3>3. User rules</h3>
      <p>
        3.1 The user shall understand and agree to comply with the following
        rules.
      </p>
      <p>
        (1) The user shall not use this software to conduct behaviors of
        violating the laws and regulations of India.
      </p>
      <p>
        (2) The user shall not make use of the services provided by the platform
        to conduct credit card cash, money laundering, illegal fund-raising,
        gambling or other unfair transactions;
      </p>
      <p>
        (3) The user shall not use the services provided by this platform in any
        illegal means for any illegal purposes or in any inconsistent ways with
        this agreement.
      </p>
      <p>
        3.2 This platform reserves the right to replace modify and update this
        platform at any time, and to charge for replacement, modification or
        update of this platform.
      </p>
      <p>
        3.3 The user shall understand and agree that this platform has the right
        to terminate or suspend the services to the user at any time in the
        following situations without notice to the user and taking any
        responsibility for the user or any third party.
      </p>
      <p>
        (1) This platform fails to provide services normally due to force
        majeure or requirements of government agencies;
      </p>
      <p>(2) Network equipment failure or sudden network interruption.</p>
      <p>
        (3) Temporarily stop providing services because of computer viruses and
        other reasons.
      </p>
      <p>(4) Necessary maintenance and update of this platform.</p>
      <h3>4. Rights and obligations</h3>
      <p>4.1 Rights and obligations of This platform.</p>
      <p>
        (1) The ownership of the platform account is owned by the company. The
        user can obtain the right to use the platform account after the
        completion of registration procedure, and the use right of the account
        is only belongs to the initial applicant for registration with
        prohibition of grant, borrow, lease, transfer or sell. The company has
        the right to recover the user's platform account based on business
        needs.
      </p>
      <p>
        (2) This platform and its partners have the right to analyze, obtain
        evidence through investigation on the user 's information and form the
        user database with the results of other customer ownership of the user
        database. The company Ste information analysis. The company has comple
        can provide, display, authorize and transfer the user database to the
        third party without the user consent, and there is no need to pay any
        cost to the user.
      </p>
      <p>4.2 Rights and Obligations of the User</p>
      <p>
        (1) The user has the right to change or delete personal data,
        registration information and forwarding content on platform account
      </p>
      <p>
        (2) The user has the responsibility to properly keep registration
        account information and otp security, and promise not to use other users
        account OTP under any circumstances.
      </p>
      <p>
        (3) The user will abide by the articles of this agreement to correctly
        and properly use the platform services. If the user violates any of the
        articles of this agreement, the platform has the right to stop providing
        services for the user and to retain the right to recover the account,
        username and so on at any time.
      </p>
      <h3>5. Credit authorization</h3>
      <p>
        5.1 The user hereby irrevocably authorizes this platform or the platform
        partners to understand, consult and investigate the user's personal
        information, credit status, performance capabilities and other
        information on evaluating the user's credit status, including the
        possible bad credit information of the user through the credit agencies
        set up by the law.
      </p>
      <p>
        5.2 The user hereby irrevocably authorizes this platform to provide the
        corresponding personal information loan information and subsequent
        repayment records on the acceptance of services provided by this
        platform to the credit agencies set up by the law. Disputes arising from
        the user information processing, production development and use by the
        credit agencies shall be settled by the user and credit agencies and
        this platform does not need to take any responsibilit.
      </p>
      <p>
        5.3 The user hereby irrevocably authorizes this platform to provide the
        user information to the platform's cooperative data processing
        institutions Based on the needs of cooperative data processing and data
        services provided by the institutions. the user also authorizes this
        platform to collect the information in all aspects allowed by the laws
        and regulations, such as user identity information, property
        information, transaction information behavioral information bad
        information and so on from the institutions that legally preserve the
        user information(the user also agrees that the institutions that legally
        preserve the user information to provide information to the cooperative
        data processing institutions without further authorized consent from the
        user), and the data shall be provided to this platform after processing,
        storing, sorting and analyzing the data by the cooperative data
        processing institutions.
      </p>
      <p>
        5.4 The user's authorization to this platform can be transferred to the
        partners or any third party.
      </p>
      <h3>6. Intellectual property rights</h3>
      <p>
        Any texts, pictures, graphics, audios or videos source codes, and ui
        design included in the platform's network services are protected by the
        copyright, trademark and other property rights laws. Without the consent
        of relevant right holders, the user may not copy, adapt, transmit,
        publish, display, or infringe on the platform's property rights in any
        way.
      </p>
      <h3>7. Privacy protection</h3>
      <p>
        7.1 The platform will not disclose the registration information of an
        individual user or the users private content stored in this platform to
        the public or the third party, except in the following
      </p>
      <p>(1) Obtain the user's explicit authorization in advance.</p>
      <p>
        (2) Provided or disclosed by the requirements of relevant laws and
        regulations;
      </p>
      <p>
        (3) Provided or disclosed by the requirements of relevant government
        authorities.
      </p>
      <p>
        (4) To maintain the legitimate interests of the platform and the
        company.
      </p>
      <p>
        (5) The platform provides user information to partners or affiliated
        parties due to the services provided to the user.
      </p>
      <p>
        7.2 The company has the right to analyze the user database and make
        commercial use of user data.
      </p>
      <h3>8. Laws and Dispute Settlement</h3>
      <p>8.1 This agreement is applicable to the Indian law.</p>
      <p>
        8.2 The parties shall settle the dispute arising from this agreement or
        related to this agreement by friendly negotiation; if the dispute cannot
        be resolved through negotiation, any party may submit the dispute to the
        court with the jurisdiction of the company registration place.
      </p>
      <h3>9. Notice and Delivery</h3>
      <p>
        9.1 Notices or documents made by either party of this agreement to the
        other party can be delivered by special person, registered mail, express
        mail service, text message, fax, e-mail or platform release.
      </p>
      <p>
        9.2 The specific delivery address/code address of either party of this
        agreement is subject to the user's registration information on the
        platform If the above delivery address/code address information is
        different it is deemed as an effective delivery address.
      </p>
      <p>9.3 Notices are deemed to be delivered on the following dates.</p>
      <p>(1) Deemed to be served by messages and emails.</p>
      <p>
        (2) For notices by personal delivery, it is deemed to be effectively
        delivered on the day of delivery.
      </p>
      <p>
        (3)Notices sent by registered mails (paid postage) are deemed to be
        delivered within three (3) working days after mailing (by postmark);
      </p>
      <p>
        (4) Notices sent by express mail service (paid postage)are deemed to be
        delivered within three(3) working days after mailing (by postmark);
      </p>
      <p>
        (5) Notices by the way of platform release are deemed to be effectively
        delivered on the date of issuance
      </p>
      <p>
        9.4 In the event of changes of the user name, address, contact person or
        communication outage, the information shall be updated within 3 days
        after the changes, or else the original service address is still valid.
      </p>
      <h3>10 Miscellaneous clause</h3>
      <p>
        10.1 If any of the articles of this agreement is completely or partially
        invalid, no longer in force or ineffective, the remainder of this
        agreement shall be valid and binding.
      </p>
      <p>
        10.2 The company has the right to adjust and modify this agreement
        according to the changes of laws and regulations, and the company 's
        state of operation and operating strategy without separately notifying
        the user. If there is any dispute, the latest agreement shall prevail.
        In case of disagreement with the company's modification to the relevant
        terms of this agreement, the user has the right to stop using the
        services of this platform; if the user continues to use this platform,
        it is deemed that the user accepts the company,'s modification to the
        relevant terms of this agreement.
      </p>
      <p>
        10.3 This agreement is always valid unless the company terminates this
        agreement or the user terminates this agreement and cancels the user
        account by the consent of the company.
      </p>
      <p>
        10.4 Unsettled issues of this agreement are performance according to the
        existing and occasional rules issued by the platform, and the user
        agrees to deem the rules as the content of this agreement.
      </p>
      <p>
        10.5 The company has the right to interpret this agreement within the
        fullest extent permitted by law.
      </p>
      <p>
        In regarding to this User Agreement, I consent to the NBFC/Platform
        accessing, viewing, using, and storing my credit information in their
        process of decision making for my loan application.
      </p>
    </div>
  );
};

export default UserAgreement;
