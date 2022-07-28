package com.bigshark.android.http.model.param;

import com.truecaller.android.sdk.TrueProfile;

import java.util.Locale;

/**
 * 该类可以修改，使用代理模式、使用复制、两个相互使用(方法也是)、去除掉setter方法
 */
public class TrueProfileParam {

//    private final TrueProfile trueProfile;

    private String firstName;
    private String lastName;
    private String phoneNumber;
    private String gender;
    private String street;
    private String city;
    private String zipcode;
    private String countryCode;
    private String facebookId;
    private String twitterId;
    private String email;
    private String url;
    private String avatarUrl;
    private boolean isTrueName;
    private boolean isAmbassador;
    private String companyName;
    private String jobTitle;
    private String payload;
    private String signature;
    private String signatureAlgorithm;
    private String requestNonce;
    private boolean isSimChanged;
    private String verificationMode;
    private long verificationTimestamp;
    private Locale userLocale;

    public TrueProfileParam(TrueProfile trueProfile) {
//        this.trueProfile = trueProfile;
        this.firstName = trueProfile.firstName;
        this.lastName = trueProfile.lastName;
        this.phoneNumber = trueProfile.phoneNumber;
        this.gender = trueProfile.gender;
        this.street = trueProfile.street;
        this.city = trueProfile.city;
        this.zipcode = trueProfile.zipcode;
        this.countryCode = trueProfile.countryCode;
        this.facebookId = trueProfile.facebookId;
        this.twitterId = trueProfile.twitterId;
        this.email = trueProfile.email;
        this.url = trueProfile.url;
        this.avatarUrl = trueProfile.avatarUrl;
        this.isTrueName = trueProfile.isTrueName;
        this.isAmbassador = trueProfile.isAmbassador;
        this.companyName = trueProfile.companyName;
        this.jobTitle = trueProfile.jobTitle;
        this.payload = trueProfile.payload;
        this.signature = trueProfile.signature;
        this.signatureAlgorithm = trueProfile.signatureAlgorithm;
        this.requestNonce = trueProfile.requestNonce;
        this.isSimChanged = trueProfile.isSimChanged;
        this.verificationMode = trueProfile.verificationMode;
        this.verificationTimestamp = trueProfile.verificationTimestamp;
        this.userLocale = trueProfile.userLocale;
    }

    public String getFirstName() {
        return /*trueProfile.*/firstName;
    }

    public void setFirstName(String firstName) {
        this./*trueProfile.*/firstName = firstName;
    }

    public String getLastName() {
        return /*trueProfile.*/lastName;
    }

    public void setLastName(String lastName) {
        this./*trueProfile.*/lastName = lastName;
    }

    public String getPhoneNumber() {
        return /*trueProfile.*/phoneNumber;
    }

    public void setPhoneNumber(String phoneNumber) {
        this./*trueProfile.*/phoneNumber = phoneNumber;
    }

    public String getGender() {
        return /*trueProfile.*/gender;
    }

    public void setGender(String gender) {
        this./*trueProfile.*/gender = gender;
    }

    public String getStreet() {
        return /*trueProfile.*/street;
    }

    public void setStreet(String street) {
        this./*trueProfile.*/street = street;
    }

    public String getCity() {
        return /*trueProfile.*/city;
    }

    public void setCity(String city) {
        this./*trueProfile.*/city = city;
    }

    public String getZipcode() {
        return /*trueProfile.*/zipcode;
    }

    public void setZipcode(String zipcode) {
        this./*trueProfile.*/zipcode = zipcode;
    }

    public String getCountryCode() {
        return /*trueProfile.*/countryCode;
    }

    public void setCountryCode(String countryCode) {
        this./*trueProfile.*/countryCode = countryCode;
    }

    public String getFacebookId() {
        return /*trueProfile.*/facebookId;
    }

    public void setFacebookId(String facebookId) {
        this./*trueProfile.*/facebookId = facebookId;
    }

    public String getTwitterId() {
        return /*trueProfile.*/twitterId;
    }

    public void setTwitterId(String twitterId) {
        this./*trueProfile.*/twitterId = twitterId;
    }

    public String getEmail() {
        return /*trueProfile.*/email;
    }

    public void setEmail(String email) {
        this./*trueProfile.*/email = email;
    }

    public String getUrl() {
        return /*trueProfile.*/url;
    }

    public void setUrl(String url) {
        this./*trueProfile.*/url = url;
    }

    public String getAvatarUrl() {
        return /*trueProfile.*/avatarUrl;
    }

    public void setAvatarUrl(String avatarUrl) {
        this./*trueProfile.*/avatarUrl = avatarUrl;
    }

    public boolean isTrueName() {
        return /*trueProfile.*/isTrueName;
    }

    public void setTrueName(boolean trueName) {
        this./*trueProfile.*/isTrueName = trueName;
    }

    public boolean isAmbassador() {
        return /*trueProfile.*/isAmbassador;
    }

    public void setAmbassador(boolean ambassador) {
        this./*trueProfile.*/isAmbassador = ambassador;
    }

    public String getCompanyName() {
        return /*trueProfile.*/companyName;
    }

    public void setCompanyName(String companyName) {
        this./*trueProfile.*/companyName = companyName;
    }

    public String getJobTitle() {
        return /*trueProfile.*/jobTitle;
    }

    public void setJobTitle(String jobTitle) {
        this./*trueProfile.*/jobTitle = jobTitle;
    }

    public String getPayload() {
        return /*trueProfile.*/payload;
    }

    public void setPayload(String payload) {
        this./*trueProfile.*/payload = payload;
    }

    public String getSignature() {
        return /*trueProfile.*/signature;
    }

    public void setSignature(String signature) {
        this./*trueProfile.*/signature = signature;
    }

    public String getSignatureAlgorithm() {
        return /*trueProfile.*/signatureAlgorithm;
    }

    public void setSignatureAlgorithm(String signatureAlgorithm) {
        this./*trueProfile.*/signatureAlgorithm = signatureAlgorithm;
    }

    public String getRequestNonce() {
        return /*trueProfile.*/requestNonce;
    }

    public void setRequestNonce(String requestNonce) {
        this./*trueProfile.*/requestNonce = requestNonce;
    }

    public boolean isSimChanged() {
        return /*trueProfile.*/isSimChanged;
    }

    public void setSimChanged(boolean simChanged) {
        /*trueProfile.*/
        isSimChanged = simChanged;
    }

    public String getVerificationMode() {
        return /*trueProfile.*/verificationMode;
    }

    public void setVerificationMode(String verificationMode) {
        this./*trueProfile.*/verificationMode = verificationMode;
    }

    /**
     * 验证时间
     */
    public long getVerificationTimestamp() {
        return /*trueProfile.*/verificationTimestamp;
    }

    public void setVerificationTimestamp(long verificationTimestamp) {
        this./*trueProfile.*/verificationTimestamp = verificationTimestamp;
    }

    public Locale getUserLocale() {
        return /*trueProfile.*/userLocale;
    }

    public void setUserLocale(Locale userLocale) {
        this./*trueProfile.*/userLocale = userLocale;
    }
}
