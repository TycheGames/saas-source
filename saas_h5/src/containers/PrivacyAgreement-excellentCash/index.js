/*
 * @Author: Always
 * @LastEditors: Always
 * @email: 740905172@qq.com
 * @Date: 2020-02-19 01:09:31
 * @LastEditTime: 2020-07-30 16:30:18
 * @FilePath: /saas_h5/src/containers/PrivacyAgreement-excellentCash/index.js
 */
/**
 * Created by yaer on 2019/7/26;
 * @Email 740905172@qq.com
 * */
import { useDocumentTitle } from "../../hooks";
import "./index.less";
import { replaceAppName } from "../../vest";

const PrivacyAgreement = (props) => {
  const {
    packageName,
    company,
    website,
    email,
    address,
    contactList,
  } = replaceAppName("excellentcash");
  useDocumentTitle(props);
  return (
    <div className="privacy-agreement-wrpper">
      <h1>{packageName}</h1>
      <h2>Privacy Policy</h2>
      <p>
        {company} whose registered office is {address}
      </p>
      <p>
        By visiting this website, <a href={website}>{website}</a> (“Website”)
        and availing the services provided by us (“Services”) you agree to be
        bound by the terms and conditions of this Privacy Policy.
      </p>
      <p>
        By mere access to the Platform or any part thereof, you expressly
        consent to {company} (“we” or “our” or “us” or “{packageName}”) use and
        disclosure of your personal information in accordance with this Privacy
        Policy. This Privacy Policy is incorporated into and subject to the
        Terms of Use of the Platform.
      </p>
      <p>
        For the purpose of this Privacy Policy, the users of the Services may be
        customer/consumers, or any other persons using Services or accessing our
        Platform (“user” or “you” or “You” or “YOU” or “your” or “Your”). If you
        do not agree to this Policy or any part thereof, please do not use or
        access our Platform or any part thereof.
      </p>
      <h3>1.COLLECTION OF PERSONALLY IDENTIFIABLE INFORMATION</h3>
      <p>
        When you use our Platform, whether our Website or our App, we collect
        and store your information (personal information) which is provided by
        you from time to time by explicitly seeking permissions from YOU to get
        the required information. Our primary goal in doing so is to provide you
        a safe, efficient, smooth and customized experience and services. This
        allows us to provide services and features that meets your needs, and to
        customize our Platform to make your experience safer and easier and to
        improve the services provided by us. More importantly, we collect
        personal information from you that we consider necessary for achieving
        the aforementioned purpose.
      </p>
      <p>
        in general, you can browse the Website or App without telling us who you
        are or revealing any personal information about yourself. However, to
        create an account on the Website or App, you must provide us with
        certain basic information required to provide customized services. The
        information we collect from you, inter alia, including:
      </p>
      <p>a. your full name;</p>
      <p>b. email;</p>
      <p>c. mailing address;</p>
      <p>d. postal code;</p>
      <p>e. family details;</p>
      <p>f. university/college details;</p>
      <p>g. phone number;</p>
      <p>h. Permanent Account Number (PAN);</p>
      <p>i. Academic records and certificates.</p>
      <p>j. Aadhaar card</p>
      <p>k.face picture</p>
      <p>l.Driving License</p>
      <p>m.Voter ID</p>
      <p>
        Where possible, we indicate the mandatory and the optional fields. You
        always have the option to not provide your personal information by
        choosing not to use a particular service or feature on the Platform.
      </p>
      <p>
        We also collect other identifiable information (your payment card
        details and transaction histories on the Platform) from you when you set
        up a free account with us. While you can browse some sections of our
        Platform without being a registered member as mentioned above, certain
        activities (such as availing of loans from the third-party lenders on
        the Platform) requires registration and for you to provide the above
        details. The Platform shall clearly display the personal information it
        is collecting from you, and you have the option to not provide such
        personal information. However, this will limit the services provided to
        you on the Platform.
      </p>

      <h3>2.COLLECTION OF MOBILE NUMBER AND EMAIL ADDRESS</h3>
      <p>
        When you sign up with us, we collect your mobile number and email
        address to uniquely identify you. This helps us ensure that no
        unauthorised device or person is acting on your behalf.
      </p>
      <h3>3.COLLECTION OF LOCATION</h3>
      <p>
        We collect location information from your device to reduce the risk
        associated with your account.
      </p>
      <h3>4.COLLECTION OF CONTACT</h3>
      <p>
        As a part of our loan journey, we require references from the Loan
        applicant. In this regard, during filing the form on our App, we collect
        your contact information to detect close contacts to enable you to
        autofill the data during the loan application process.
      </p>
      <p>
        Furthermore, we collect contact information from your device for the
        purposes of risk analysis by enabling us to detect credible references.
        The more credible the references are, the lower is the risk associated
        to a User.
      </p>
      <h3>5.COLLECTION OF INSTALLED APPLICATIONS</h3>
      <p>
        We collect a list of the installed applications’ metadata information
        which includes the application name, package name, installed time,
        updated time, version name and version code of each installed
        application on your device to assess your credit worthiness and enrich
        your profile with pre-approved customized loan offers.
      </p>
      <h3>6.STORAGE</h3>
      <p>
        We require the storage information permission that allows the App to
        enable you to upload photos and/or documents to complete the application
        form during your loan application journey.
      </p>
      <h3>7.CAMERA</h3>
      <p>
        We require the camera information permission to provide you an
        easy/smooth experience and to enable you to click photos of your KYC
        documents along with other requisite documents and upload the same on
        the App during your loan application journey.
      </p>
      <h3>8.COLLECTION OF OTHER NON-PERSONAL INFORMATION</h3>
      <p>
        We automatically track certain information about you based upon your
        behavior on our Platform. We use this information to do internal
        research on our users' demographics, interests, and behavior to better
        understand, protect and serve our users and improve our services. This
        information is compiled and analyzed on an aggregated basis. We also
        collect your Internet Protocol (IP) address and the URL used by you to
        connect your computer to the internet, etc. This information may include
        the URL that you just came from (whether this URL is on our Website or
        not), which URL you next go to (whether this URL is on our Website or
        not), your computer browser information, and your IP address.
      </p>
      <p>
        Cookies are small data files that a Website stores on Your computer. We
        will use cookies on our Website similar to other lending websites / apps
        and online marketplace websites / apps. Use of this information helps Us
        identify You in order to make our Website more user friendly. Most
        browsers will permit You to decline cookies but if You choose to do this
        it might affect service on some parts of Our Website.
      </p>
      <p>
        If you choose to make a purchase through the Website, we collect
        information about your buying behavior. We retain this information as
        necessary to resolve disputes, provide customer support and troubleshoot
        problems as permitted by law.
      </p>
      <p>
        If you send us personal correspondence, such as emails or letters, or if
        other users or third parties send us correspondence about your
        activities or postings on the Website, we collect such information into
        a file specific to you.
      </p>
      <h3>9.COLLECTION OF DEVICE INFORMATION</h3>
      <p>
        The information the App collects, and how that information is used,
        depends on how you manage your privacy controls on your device.
      </p>
      <p>
        When you install the App, we store the information we collect with
        unique identifiers tied to the device you’re using.
      </p>
      <p>
        We collect information from the device when you download and install the
        App and explicitly seek permissions from YOU to get the required
        information from the device.
      </p>
      <p>
        The information we collect from your device includes the unique ID i.e.
        IMEI number, information on operating system, SDK version and mobile
        network information including carrier name, SIM Serial and SIM Slot,
        your profile information, list of installed apps, wi-fi information.
      </p>
      <p>
        We collect information about your device to provide automatic updates
        and additional security so that your account is not used in other
        people’s devices. In addition, the information provides us valuable
        feedback on your identity as a device holder as well as your device
        behavior, thereby allowing us to improve our services and provide an
        enhanced customized user experience to you.
      </p>
      <h3>10.USE AND DISCLOSURE OF YOUR PERSONAL AND OTHER INFORMATION</h3>
      <p>
        We access, store and use the information we collect from you to provide
        our Services, to research and develop new ones.
      </p>
      <p>
        We use personal information to provide the services you request, to
        customize your user experience and to improve our services. To the
        extent we intent to use your personal information to market any product
        to you, we will provide you the ability to opt-out of such uses.
      </p>
      <p>We use your personal information to:</p>
      <p>a. resolve disputes;</p>
      <p>b. troubleshoot problems;</p>
      <p>c. help promote a safe service;</p>
      <p>d. analytical analysis;</p>
      <p>
        e. measure consumer interest and satisfaction in our products and
        services;
      </p>
      <p>
        f. inform you about online and offline offers, products, services, and
        updates;
      </p>
      <p>g. customize your experience;</p>
      <p>
        h. detect and protect us against suspicious or illegal activity, fraud
        and other criminal activity;
      </p>
      <p>i. enforce our terms and conditions;</p>
      <p>
        j. improvement of our services and as otherwise described to you at the
        time of collection. In our efforts to continually improve our product
        and service offerings, we collect and analyze demographic and profile
        data about our users' activity on our Website.
      </p>
      <h3>11.USE OF YOUR DEVICE INFORMATION</h3>
      <p>We use the information provided by You in the following ways:</p>
      <p>a. to establish identity and verify the same;</p>
      <p>b. monitor, improve and administer our Website / Platform ;</p>
      <p>
        c. provide our service i.e. perform credit profiling for the purpose of
        facilitating loans to You.
      </p>
      <p>
        d. design and offer customized products and services offered by our
        third-party financial partners;
      </p>
      <p>
        e. analyse how the Website is used, diagnose service or technical
        problems and maintain security;
      </p>
      <p>
        f. send communications notifications, information regarding the products
        or services requested by You or process queries and applications that
        You have made on the Website;
      </p>
      <p>
        g. manage Our relationship with You and inform You about other products
        or services We think You might find of some use;
      </p>
      <p>
        h. conduct data analysis in order to improve the Services / Products
        provided to the User;
      </p>
      <p>
        i. use the User information in order to comply with country laws and
        regulations;
      </p>
      <p>
        j. to conduct KYC for our third-party lending partners based on the
        information shared by the User;
      </p>
      <p>
        k. use the User information in other ways permitted by law to enable You
        to take financial services from our lending partners.
      </p>
      <p>
        We will use and retain Your information for such periods as necessary to
        provide You the Services on our Website, to comply with our legal
        obligations, to resolve disputes, and enforce our agreements.
      </p>
      <h3>12.DISCLOSURE TO THIRD PARTIES:</h3>
      <p>
        We will share Your information with only our registered third parties
        including our regulated financial partners for provision of services on
        the Website and/or for facilitation of a loan / facility to a User. We
        will share Your information with third-parties only in such manner as
        described below:
      </p>
      <p>
        a. We disclose and share Your information with the financial service
        providers, banks or NBFCs and Our third-party partners for facilitation
        of a loan or facility or line of credit or purchase of a product;
      </p>
      <p>
        b. We share Your information with our third-party partners in order to
        conduct data analysis in order to serve You better and provide services
        or Products on our Website;
      </p>
      <p>
        c. We may disclose Your information, without prior notice, if We are
        under a duty to do so in order to comply with any legal obligation or an
        order from the government and/or a statutory authority, or in order to
        enforce or apply Our terms of use or assign such information in the
        course of corporate divestitures, mergers, or to protect the rights,
        property, or safety of Us, Our users, or others. This includes
        exchanging information with other companies and organizations for the
        purposes of fraud protection and credit risk reduction.
      </p>
      <p>
        d. We will disclose the data / information provided by a User with other
        technology partners to track how the User interact with Website on Our
        behalf.
      </p>
      <p>
        e. We and our affiliates may share Your information with another
        business entity should we (or our assets) merge with, or be acquired by
        that business entity, or re-organization, amalgamation, restructuring of
        business for continuity of business. Should such a transaction occur
        than any business entity (or the new combined entity) receiving any such
        information from Us shall be bound by this Policy with respect to your
        information.
      </p>
      <p>
        f. We will disclose the information to our third-party technology and
        credit partners to perform credit checks and credit analysis like Credit
        Bureaus or third-party data source providers;
      </p>
      <p>
        g. We will share Your information under a confidentiality agreement with
        the third parties and restrict use of the said Information by
        third-parties only for the purposes detailed herein. We warrant that
        there will be no unauthorised disclosure of your information shared with
        third-parties.
      </p>
      <p>
        h. By using the Platform, you hereby grant your consent to the Company
        to share/disclose your Personal Information (i) To the concerned third
        parties in connection with the Services; and (ii) With the governmental
        authorities, quasi-governmental authorities, judicial authorities and
        quasi-judicial authorities, in accordance with applicable laws of India.
      </p>
      <p>
        In case we use or disclose your information for any purpose not
        specified above, we will take your explicit consent.
      </p>
      <h3>13.LINK TO THIRD-PARTY SDK</h3>
      <p>
        Our application has a link to a registered third party SDK which
        collects data on our behalf and data is stored to a secured server to
        perform a credit risk assessment. We ensure that our third party service
        provider takes extensive security measures in order to protect your
        personal information against loss, misuse or alteration of the data.
      </p>
      <p>
        Our third-party service provider employs separation of environments and
        segregation of duties and have strict role-based access control on a
        documented, authorized, need-to-use basis. The stored data is protected
        and stored by application-level encryption. They enforce key management
        services to limit access to data.
      </p>
      <p>
        Furthermore, our registered third party service provider provides
        hosting security – they use industry-leading anti-virus, anti-malware,
        intrusion prevention systems, intrusion detection systems, file
        integrity monitoring, and application control solutions.
      </p>
      <h3>14.CHANGES IN THIS PRIVACY POLICY</h3>
      <p>
        {packageName} reserves the right to change, modify, add, or remove
        portions of this Privacy Policy at any time for any reason. In case, any
        changes are made in the Privacy Policy, we shall update the same on the
        Website. Once posted, those changes are effective immediately, unless
        stated otherwise. We encourage you to periodically review this page for
        the latest information on our privacy practices. Continued access or use
        of the Services constitute Your acceptance of the changes and the
        amended Privacy Policy.
      </p>
      <h3>15.ACCESSING YOUR INFORMATION / CONTACTING US</h3>
      <p>
        At any point of time Users can choose to edit/modify or delete/withdraw
        any Personal Information shared for use of the Platform. Please note
        that deleting or withdrawing information may affect the Services we
        provide to you. In case of modification of Personal Information, Users
        will be required to furnish supporting documents relating to change in
        Personal Information for the purpose of verification by the Company.
      </p>
      <h3>16.YOUR PRIVACY CONTROLS</h3>
      <p>
        You have certain choices regarding the information we collect and how it
        is used:
      </p>
      <p>
        a. Device-level settings: Your device may have controls that determine
        what information we collect. For example, you can modify permissions on
        your Android device for access to Camera or Audio permissions.
      </p>
      <p>b. Delete your entire App account.</p>
      <p>
        c. You can also request to remove content from our servers based on
        applicable law or by writing to our Grievance Officer.
      </p>
      <h3>17.SECURITY PRECAUTIONS</h3>
      <p>
        The Website/App intends to protect your personal information and to
        maintain its accuracy as confirmed by you. We implement reasonable
        physical, administrative and technical safeguards to help us protect
        your personal information from unauthorized access, use and disclosure.
        For example, we encrypt all sensitive personal information when we
        transmit such information over the internet. We also require that our
        registered third party service providers protect such information from
        unauthorized access, use and disclosure.
      </p>
      <p>
        Our Platform has stringent security measures in place to protect the
        loss, misuse and alteration of information under control. We endeavor to
        safeguard and ensure the security of the information provided by you. We
        use Secure Sockets Layers (SSL) based encryption, for the transmission
        of the information, which is currently the required level of encryption
        in India as per the law.
      </p>
      <p>
        We blend security at multiple steps within our products with the state
        of the art technology to ensure our systems maintain strong security
        measures and the overall data and privacy security design allow us to
        defend our systems ranging from low hanging issue up to sophisticated
        attacks.
      </p>
      <p>
        We work hard to protect from unauthorized access, alteration, disclosure
        or destruction of information we hold, including:
      </p>
      <p>a. We use encryption to keep your data private while in transit;</p>
      <p>
        b. We offer security feature like an OTP verification to help you
        protect your account;
      </p>
      <p>
        c. We review our information collection, storage, and processing
        practices, including physical security measures, to prevent unauthorized
        access to our systems;
      </p>
      <p>
        d. We restrict access to personal information to our employees,
        contractors, and agents who need that information in order to process
        it. Anyone with this access is subject to strict contractual
        confidentiality obligations and may be disciplined or terminated if they
        fail to meet these obligations.
      </p>
      <p>e. Compliance & Cooperation with Regulations and applicable laws;</p>
      <p>
        f. We regularly review this Privacy Policy and make sure that we process
        your information in ways that comply with it.
      </p>
      <p>g. Data transfers</p>
      <p>
        We or our affiliates maintain your information on servers located in
        India. Data protection laws vary among countries, with some providing
        more protection than others. We also comply with certain legal
        frameworks relating to the transfer of data as mentioned and required
        under the Information Technology Act, 2000.
      </p>
      <p>
        When we receive formal written complaints, we respond by contacting the
        person who made the complaint. We work with the appropriate regulatory
        authorities, including local data protection authorities, to resolve any
        complaints regarding the transfer of your data that we cannot resolve
        with you directly.
      </p>
      <p> h. Bureau Enquiry</p>
      <p>
        We will enquire with one or more Credit Bureaus on one or more affiliate
        National Banking Financial Company’s (NBFC) behalf to provide you with
        your loan amount.
      </p>
      <h3>18.LINKS TO OTHER SITES</h3>
      <p>
        Our Website links to other websites that may collect personally
        identifiable information about you. We are not responsible for the
        privacy practices or the content of those linked websites. With this
        Policy we’re only addressing the disclosure and use of data collected by
        Us. If You visit any websites through the links on the Website, please
        ensure You go through the privacy policies of each of those websites.
        Their data collection practices and their policies might be different
        from this Policy and We do not have control over any of their policies
        neither do we have any liability in this regard.
      </p>
      <h3>19.YOUR CONSENT</h3>
      <p>
        By using the Website/App and/ or by providing your information, you
        consent to the collection and use of the information you disclose on the
        Website in accordance with this Privacy Policy, including but not
        limited to Your consent for collecting, using, sharing and disclosing
        your information as per this privacy policy.
      </p>
      <p>
        If we decide to change our privacy policy, we will post those changes on
        this page so that you are always aware of what information we collect,
        how we use it, and under what circumstances we disclose it.
      </p>
      <h3>20.GRIEVANCE OFFICER</h3>
      <p>
        In accordance with Information Technology Act 2000 and rules made there
        under, the name and contact details of the Grievance Officer are
        provided below:
      </p>
      <div>
        <p>Name</p>
        {contactList.map((data) => (
          <p key={data.name}>{data.name}</p>
        ))}
        <p>Phone</p>
        {contactList.map((data) => (
          <p key={data.phone}>{data.phone}</p>
        ))}
        <p>Email</p>
        <p>{email}</p>
        <p>Time</p>
        <p>Mon -Fri (10:00 - 19:00)</p>
      </div>
    </div>
  );
};

export default PrivacyAgreement;
