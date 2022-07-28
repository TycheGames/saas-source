package com.bigshark.android.http.model.contact;

public class ContactInfoData {

    private int relativeContactPersonId = 0;
    private String relativeContactPersonVal;
    private String name;
    private String phone;

    private int otherRelativeContactPersonId = 0;
    private String otherRelativeContactPersonVal;
    private String otherName;
    private String otherPhone;

    private String facebookAccount;
    private String whatsAppAccount;
    private String skypeAccount;

    public int getRelativeContactPersonId() {
        return relativeContactPersonId;
    }

    public void setRelativeContactPersonId(int relativeContactPersonId) {
        this.relativeContactPersonId = relativeContactPersonId;
    }

    public String getRelativeContactPersonVal() {
        return relativeContactPersonVal;
    }

    public void setRelativeContactPersonVal(String relativeContactPersonVal) {
        this.relativeContactPersonVal = relativeContactPersonVal;
    }

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }

    public String getPhone() {
        return phone;
    }

    public void setPhone(String phone) {
        this.phone = phone;
    }

    public int getOtherRelativeContactPersonId() {
        return otherRelativeContactPersonId;
    }

    public void setOtherRelativeContactPersonId(int otherRelativeContactPersonId) {
        this.otherRelativeContactPersonId = otherRelativeContactPersonId;
    }

    public String getOtherRelativeContactPersonVal() {
        return otherRelativeContactPersonVal;
    }

    public void setOtherRelativeContactPersonVal(String otherRelativeContactPersonVal) {
        this.otherRelativeContactPersonVal = otherRelativeContactPersonVal;
    }

    public String getOtherName() {
        return otherName;
    }

    public void setOtherName(String otherName) {
        this.otherName = otherName;
    }

    public String getOtherPhone() {
        return otherPhone;
    }

    public void setOtherPhone(String otherPhone) {
        this.otherPhone = otherPhone;
    }

    public String getFacebookAccount() {
        return facebookAccount;
    }

    public void setFacebookAccount(String facebookAccount) {
        this.facebookAccount = facebookAccount;
    }

    public String getWhatsAppAccount() {
        return whatsAppAccount;
    }

    public void setWhatsAppAccount(String whatsAppAccount) {
        this.whatsAppAccount = whatsAppAccount;
    }

    public String getSkypeAccount() {
        return skypeAccount;
    }

    public void setSkypeAccount(String skypeAccount) {
        this.skypeAccount = skypeAccount;
    }
}
