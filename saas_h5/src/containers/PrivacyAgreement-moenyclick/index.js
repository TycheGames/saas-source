/*
 * @Author: Always
 * @LastEditors: Always
 * @email: 740905172@qq.com
 * @Date: 2020-02-19 01:09:31
 * @LastEditTime: 2020-03-27 13:37:28
 * @FilePath: /saas_h5/src/containers/PrivacyAgreement-moenyclick/index.js
 */
/**
 * Created by yaer on 2019/7/26;
 * @Email 740905172@qq.com
 * */
import { useDocumentTitle } from "../../hooks";
import "./index.less";
import { replaceAppName } from "../../vest";

const PrivacyAgreement = props => {
  const {
    packageName,
    company,
    website,
    email,
    address,
    contact
  } = replaceAppName("moneyclick");
  useDocumentTitle(props);
  return (
    <div className="privacy-agreement-wrpper">
      <h1>{packageName}</h1>
      <h2>Privacy Policy</h2>
      <p>
        This Privacy Policy document (“Privacy Policy”) is published in
        accordance with the provisions of Rule 4 (1) of the Information
        Technology (Reasonable Security Practices and Procedures and Sensitive
        Personal Data or Information) Rules, 2011 which requires the publishing
        of a privacy policy for handling of or dealing in personal information
        including sensitive personal data or information.
      </p>
      <p>
        By visiting the web application (the “App”) (collectively, the
        “Platform”) and availing the services provided by us (“Services”) you
        agree to be bound by the terms and conditions of this Privacy Policy.
      </p>
      <p>
        By mere access to the Platform or any part thereof, you signify your
        assent to this Privacy Policy and consent to the processing of your
        personally identifiable information (Personal Information, Sensitive
        Personal Data or Information) to Liufang Technologies Private Limited
        (“we” or “our” or “us” or “MoneyClick”). This Privacy Policy is
        incorporated into and subject to the Terms of Use of the Platform.
      </p>
      <p>
        For the purpose of this Privacy Policy, the users of the Services may be
        customer/consumers/ buyers/ consumers, or any other persons using
        Services or accessing our Platform (“user” or “you” or “your”). If you
        do not agree to this Policy or any part thereof, please do not use/
        access/ download or install the Platform or any part thereof.
      </p>
      <h3>COLLECTION OF USER PERSONAL INFORMATION</h3>
      <p>
        When you use our Platform, we collect and store your information which
        is provided by you from time to time by explicitly seeking permissions
        from YOU to get the required information. Our primary goal in doing so
        is to provide you a safe, efficient, smooth and customized experience
        and Services. This allows us to provide services and features that meets
        your needs, and to customize our Platform to make your experience safer
        and easier and to improve the Services provided by us. More importantly,
        we collect personal information from you that we consider necessary for
        achieving the aforementioned purpose.
      </p>
      <p>
        In general, you can browse the Website or App without telling us who you
        are or revealing any information about yourself. However, to create an
        account on the Platform, you must provide us with certain basic
        information required to provide customized services. The information we
        collect from you, inter alia, includes:
      </p>
      <p>a.your full name;</p>
      <p>b.email;</p>
      <p>c.gender;</p>
      <p>d.photograph;</p>
      <p>e.mailing address;</p>
      <p>f.postal code;</p>
      <p>g.family details;</p>
      <p>h.university/college details;</p>
      <p>i.phone number;</p>
      <p>j.Permanent Account Number (PAN);</p>
      <p>k.Academic records and certificates.</p>
      <p>
        Wherever possible, we indicate the mandatory and the optional fields.
        You always have the option to not provide any information by choosing
        not to use a particular service or feature on the Platform. We also
        collect user account data which includes email address and user public
        profile information like name, photo, ASID depending on the social media
        or networking platform used by You like Google or Facebook to log-into
        an app. This information is required as a part of registration process
        to access our Service and it is also used to auto-populate relevant
        fields in the course of the interface of the App. We further collect
        other identifiable information such as your transactions history on the
        Platform when you set up a free account with us as further detailed
        below. While you can browse some sections of our Platform without being
        a registered member as mentioned above, certain activities (such as
        availing of loans from the third party lenders on the Platform) require
        registration and for you to provide the above details. The Platform will
        clearly display the personal information it is collecting from you, and
        you have the option to not provide such personal information. However,
        this will limit the services provided to you on the Platform.
      </p>
      <p>
        Our app also collects mobile number for verification to check the active
        SIM status on the device, uniquely identify you and prevent frauds and
        unauthorized access.
      </p>
      <p>
        As a part of our Video Know Your Customer (Video KYC) process,
        collection of your personal information has become mandatory to ensure
        the completion and authentication of your KYC. In this regard,
        permissions for microphone, camera will be mandatorily required.
      </p>
      <h3>COLLECTION OF FINANCIAL SMS INFORMATION</h3>
      <p>
        We don’t collect, read or store your personal SMS from your inbox. We
        collect and monitor only financial SMS sent by 6-digit alphanumeric
        senders from your inbox which helps us in identifying the various bank
        accounts that you may be holding, cash flow patterns, description and
        amount of the transactions undertaken by you as a user to help us
        perform a credit risk assessment which enables us to determine your risk
        profile and to provide you with the appropriate credit analysis. This
        process will enable you to take financial facilities from the regulated
        financial entities available on the Platform. This Financial SMS data
        also includes your historical data.
      </p>
      <p>
        While using the app, it periodically sends the financial SMS information
        to our affiliate server and to us.
      </p>
      <h3>COLLECTION OF DEVICE LOCATION AND DEVICE INFORMATION</h3>
      <p>
        We collect and monitor the information about the location of your device
        to provide serviceability of your loan application, to reduce risk
        associated with your loan application and to provide pre-approved
        customized loan offers. This also helps us to verify the address, make a
        better credit risk decision and expedite know your customer (KYC)
        process.
      </p>
      <p>
        Information the App collects, and its usage, depends on how you manage
        your privacy controls on your device. When you install the App, we store
        the information we collect with unique identifiers tied to the device
        you are using. We collect information from the device when you download
        and install the App and explicitly seek permissions from You to get the
        required information from the device.
      </p>
      <p>
        The information we collect from your device includes the hardware model,
        build model, RAM, storage; unique device identifiers like IMEI, serial
        number, SSAID; SIM information that includes network operator, roaming
        state, MNC and MCC codes, WIFI information that includes MAC address and
        mobile network information to uniquely identify the devices and ensure
        that no unauthorized device acts on your behalf to prevent frauds.
      </p>
      <p>
        We collect information about your device to provide automatic updates
        and additional security so that your account is not used in other
        people’s devices. In addition, the information provides us valuable
        feedback on your identity as a device holder as well as your device
        behavier, thereby allowing us to improve our services and provide an
        enhanced customized user experience to you.
      </p>
      <h3>COLLECTION OF CONTACT INFORMATION</h3>
      <p>
        As a part of our loan journey, we require references from the Loan
        applicant. In this regard, during filing the form on our App, we collect
        and monitor your contact information which includes name, phone number,
        account type, contact last modified, favorites and other optional data
        like relationship and structural address to enable you to autofill the
        data during the loan application process. This information is required
        for the purposes of risk analysis, enable us to detect credible
        references assess your risk profile and to determine your loan
        eligibility.
      </p>
      <h3>COLLECTION OF INSTALLED APPLICATIONS</h3>
      <p>
        We collect a list of the installed applications’ metadata information
        which includes the application name, package name, installed time,
        updated time, version name and version code of each installed
        application on your device to assess your credit worthiness and enrich
        your profile with pre-approved customized loan offers.
      </p>
      <h3>CAMERA</h3>
      <p>
        We require camera access to scan and capture the required KYC documents
        thereby allowing us to auto-fill relevant fields.
      </p>
      <p>
        As a part of our KYC journey, we require access to your camera to enable
        you to initiate your KYC process. This permission allows us or our
        authorized agents to perform your Video KYC while also taking
        screenshots of your original Officially Verified Documents that you
        present during your Video KYC journey. Video KYC enables you to complete
        your KYC digitally, smoothly and efficiently. Your video shall be
        recorded and retained for regulatory purpose along with screenshots of
        original Official Verified Documents.
      </p>
      <h3>MICROPHONE</h3>
      <p>
        We require microphone permissions to enable a two-way communication
        between our authorized agent and you for the purpose of performing and
        completing your Video KYC. Your audio shall be recorded for regulatory
        purpose.
      </p>
      <h3>STORAGE</h3>
      <p>
        We require storage permission so that your KYC and other relevant
        documents can be securely downloaded and saved on your phone. You can
        then easily upload the correct KYC related documents for faster loan
        application details filling and disbursal process. This ensures that you
        are provided with a seamless experience while using the application.
      </p>
      <h3>COLLECTION OF OTHER NON-PERSONAL INFORMATION</h3>
      <p>
        We automatically track certain information about you based upon your
        behavior on our Platform. We use this information to do internal
        research on our users’ demographics, interests, and behavior to better
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
        If you choose to make a purchase through the Platform, we collect
        information about your buying behavior.
      </p>
      <p>
        We retain this information as necessary to resolve disputes, provide
        customer support and troubleshoot problems as permitted by law.
      </p>
      <p>
        If you send us personal correspondence, such as emails or letters, or if
        other users or third parties send us correspondence about your
        activities or postings on the Website, we collect such information into
        a file specific to you.
      </p>
      <h3>USE AND DISCLOSURE OF YOUR PERSONAL AND OTHER INFORMATION</h3>
      <p>
        We understand the importance of your information and ensure that it is
        used for the intended purpose only. We access, store and use the
        information we collect from you in accordance with the applicable laws
        to provide our Services, to research and develop new one subject to the
        limitations set out in this Privacy Policy.
      </p>
      <p>We use the information to:</p>
      <p>a.resolve disputes;</p>
      <p>b.troubleshoot problems;</p>
      <p>c.help promote a safe service;</p>
      <p>d.analytical analysis;</p>
      <p>e.marketing purposes;</p>
      <p>
        f.measure consumer interest and satisfaction in our products and
        services;
      </p>
      <p>
        g.inform you about online and offline offers, products, services, and
        updates;
      </p>
      <p>h.customize your experience;</p>
      <p>
        i.detect and protect us against suspicious or illegal activity, fraud
        and other criminal activity;
      </p>
      <p>j.enforce our terms and conditions;</p>
      <p>
        k.improvement of our services and as otherwise described to you at the
        time of collection
      </p>
      <p>
        In our efforts to continually improve our product and service offerings,
        we collect and analyze demographic and profile data about our users'
        activity on our Platform.
      </p>
      <h3>PURPOSE OF COLLECTING INFORMATION</h3>
      <p>
        The intended purpose of collecting information provided by you is to:
      </p>
      <p>a.establish identity and verify the same;</p>
      <p>b.to complete your Video KYC;</p>
      <p>c.monitor, improve and administer our Platform;</p>
      <p>
        d.provide our service i.e. perform credit profiling for the purpose of
        facilitating loans to You.
      </p>
      <p>
        e.design and offer customized products and services offered by our third
        party financial partners;
      </p>
      <p>
        f.analyze how the Platform is used, diagnose service or technical
        problems and maintain security;
      </p>
      <p>
        g.send communications notifications, information regarding the products
        or services requested by You or process queries and applications that
        You have made on the Platform;
      </p>
      <p>
        h.manage Our relationship with You and inform You about other products
        or services We think You might find of some use;
      </p>
      <p>
        i.conduct data analysis in order to improve the Services / Products
        provided to the User;
      </p>
      <p>
        j.use the User information in order to comply with country laws and
        regulations;
      </p>
      <p>
        k.conduct KYC for our third party lending partners based on the
        information shared by the User;
      </p>
      <p>
        l.use the User information in other ways permitted by law to enable You
        to take financial services from our lending partners.
      </p>
      <p>
        We will use and retain the information for such periods as necessary to
        provide You the Services on the Platform, to comply with our legal
        obligations, to resolve disputes, and enforce our agreements.
      </p>
      <h3>DISCLOSURE TO THIRD PARTIES</h3>
      <p>
        We will share Your information with only our registered third parties
        including our regulated financial partners for provision of Services on
        the Website/ App. We will share Your information with third parties only
        in such manner as described below:
      </p>
      <p>
        a.We disclose and share Your information with the financial service
        providers, banks or NBFCs and our third party partners for facilitation
        of a loan or facility or line of credit or purchase of a product;
      </p>
      <p>
        b.We share Your information with our third party partners in order to
        conduct data analysis in order to serve You better and provide Services
        our Platform;
      </p>
      <p>
        c.We may disclose Your information, without prior notice, if we are
        under a duty to do so in order to comply with any legal obligation or an
        order from the government and/or a statutory authority, or in order to
        enforce or apply Our terms of use or assign such information in the
        course of corporate divestitures, mergers, or to protect the rights,
        property, or safety of Us, Our users, or others. This includes
        exchanging information with other companies and organizations for the
        purposes of fraud protection and credit risk reduction.
      </p>
      <p>
        d.We will disclose the data / information provided by a User with other
        technology partners to track how the User interact with the Platform on
        Our behalf.
      </p>
      <p>
        e.We and our affiliates may share Your information with another business
        entity should we (or our assets) merge with, or be acquired by that
        business entity, or re-organization, amalgamation, restructuring of
        business for continuity of business. Should such a transaction occur
        than any business entity (or the new combined entity) receiving any such
        information from Us shall be bound by this Policy with respect to your
        information.
      </p>
      <p>
        f.We will disclose the information to our third party technology and
        credit partners to perform credit checks and credit analysis like Credit
        Bureaus or third party data source providers;
      </p>
      <p>
        g.We will share Your information under a confidentiality agreement with
        the third parties and restrict use of the said Information by third
        parties only for the purposes detailed herein. We warrant that there
        will be no unauthorized disclosure of your information shared with third
        parties.
      </p>
      <p>
        h.By using the Platform, you hereby grant your consent to the Company to
        share/disclose your Personal Information (i) To the concerned third
        parties in connection with the Services; and (ii) With the governmental
        authorities, quasi-governmental authorities, judicial authorities and
        quasi-judicial authorities, in accordance with applicable laws of India.
      </p>
      <p>
        i.We shall disclose your Video KYC journey to the relevant regulatory
        authorities as a part of our statutory audit process. Please note that
        your Aadhaar number shall never be disclosed.
      </p>
      <p>
        In case we use or disclose your information for any purpose not
        specified above, we will take your explicit consent.
      </p>
      <h3>LINK TO THIRD-PARTY SDK</h3>
      <p>
        The App has a link to a registered third party SDK which collects data
        on our behalf and data is stored to a secured server to perform a credit
        risk assessment. We ensure that our third party service provider takes
        extensive security measures in order to protect your personal
        information against loss, misuse or alteration of the data.
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
      <h3>CHANGES IN THIS PRIVACY POLICY</h3>
      <p>
        We reserve the right to change, modify, add, or remove portions of this
        Privacy Policy at any time for any reason. In case, any changes are made
        in the Privacy Policy, we shall update the same on the Platform. Once
        posted, those changes are effective immediately, unless stated
        otherwise. We encourage you to periodically review this page for the
        latest information on our privacy practices. Continued access or use of
        the Services constitute Your acceptance of the changes and the amended
        Privacy Policy.
      </p>
      <h3>ACCESSING YOUR INFORMATION / CONTACTING US</h3>
      <p>
        At any point of time Users can choose to edit/modify or delete/withdraw
        any Personal Information shared for use of the Platform. Please note
        that deleting or withdrawing information may affect the Services we
        provide to you. In case of modification of Personal Information, Users
        will be required to furnish supporting documents relating to change in
        Personal Information for the purpose of verification by the Company.
      </p>
      <h3>YOUR PRIVACY CONTROLS</h3>
      <p>
        You have certain choices regarding the information we collect and how it
        is used:
      </p>
      <p>
        a.Device-level settings: Your device may have controls that determine
        what information we collect. For example, you can modify permissions on
        your Android device for access to Camera or Audio permissions.
      </p>
      <p>b.Delete your entire App account.</p>
      <p>
        c.You can also request to remove content from our servers based on
        applicable law or by writing to our Grievance Officer.
      </p>
      <h3>SECURITY PRECAUTIONS</h3>
      <p>
        The Platform intends to protect your information and to maintain its
        accuracy as confirmed by you. We implement reasonable physical,
        administrative and technical safeguards to help us protect your
        information from unauthorized access, use and disclosure. For example,
        we encrypt all information when we transmit over the internet. We also
        require that our registered third party service providers protect such
        information from unauthorized access, use and disclosure.
      </p>
      <p>
        Our Platform has stringent security measures in place to protect the
        loss, misuse and alteration of information under control. We safeguard
        and ensure the security of the information provided by you. We use
        Secure Sockets Layers (SSL) based encryption, for the transmission of
        the information, which is currently the required level of encryption in
        India as per applicable law.
      </p>
      <p>
        We blend security at multiple steps within our products with the state
        of the art technology to ensure our systems maintain strong security
        measures and the overall data and privacy security design allow us to
        defend our systems ranging from low hanging issue up to sophisticated
        attacks.
      </p>
      <p>
        We aim to protect from unauthorized access, alteration, disclosure or
        destruction of information we hold, including:
      </p>
      <p>a.We use encryption to keep your data private while in transit;</p>
      <p>
        b.We offer security feature like an OTP verification to help you protect
        your account;
      </p>
      <p>
        c.We restrict access to personal information to our employees,
        contractors, and agents who need that information in order to process
        it. Anyone with this access is subject to strict contractual
        confidentiality obligations and may be disciplined or terminated if they
        fail to meet these obligations;
      </p>
      <p>d.Compliance & Cooperation with Regulations and applicable laws;</p>
      <p>
        e.We regularly review this Privacy Policy and make sure that we process
        your information in ways that comply with it.
      </p>
      <p>f.Data transfers;</p>
      <p>g.We ensure that Aadhaar number is not disclosed in any manner.</p>
      <p>
        We or our affiliates maintain your information on servers located in
        India. Data protection laws vary among countries, with some providing
        more protection than others. We also comply with certain legal
        frameworks relating to the transfer of data as mentioned and required
        under the Information Technology Act, 2000 and rules made thereunder
      </p>
      <p>
        When we receive formal written complaints, we respond by contacting the
        person who made the complaint. We work with the appropriate regulatory
        authorities, including local data protection authorities, to resolve any
        complaints regarding the transfer of your data that we cannot resolve
        with you directly.
      </p>
      <h3>LINKS TO OTHER SITES</h3>
      <p>
        Our Platform links to other websites that may collect information about
        you. We are not responsible for the privacy practices or the content of
        those linked websites. With this Policy we’re only addressing the
        disclosure and use of data collected by Us. If You visit any websites
        through the links on the Website, please ensure You go through the
        privacy policies of each of those websites. Their data collection
        practices, and their policies might be different from this Policy and We
        do not have control over any of their policies neither do we have any
        liability in this regard.
      </p>
      <h3>YOUR CONSENT</h3>
      <p>
        By using the Platform and by providing your information, you consent to
        the collection, sharing, disclosure and usage of the information that
        you disclose on the Platform in accordance with this Privacy Policy.
      </p>
      <p>
        If we decide to change our Privacy Policy, we will post those changes on
        this page so to make you aware of the information we collect, how we use
        it, and under what circumstances we share and disclose it.
      </p>
      <h3>GRIEVANCE OFFICER</h3>
      <p>
        In accordance with Information Technology Act 2000 and rules made there
        under, the name and contact details of the Grievance Officer are
        provided below for your reference:
      </p>
      <p>Name: Ms. Mangala</p>
      <p>Address: Liufang Technologies Private Limited,</p>
      <p>
        S 515, South Block, 47, Manipal Centre, Dicksenon Road, Bangalore,
        Karnataka, India – 560042
      </p>
      <p>Phone: +91 919513760103</p>
      <p>Email: help@moneyclick.in</p>
      <p>Time: Mon - Sat (10:00 - 18:00)</p>
    </div>
  );
};

export default PrivacyAgreement;
