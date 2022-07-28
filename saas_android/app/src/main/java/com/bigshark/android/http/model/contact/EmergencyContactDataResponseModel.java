package com.bigshark.android.http.model.contact;


public class EmergencyContactDataResponseModel {

    private ContactRelationshipConfig selectData; // 联系人的关系列表
    private ContactInfoData getData; //  用户自己填写的联系人信息

    public ContactRelationshipConfig getSelectData() {
        return selectData;
    }

    public void setSelectData(ContactRelationshipConfig selectData) {
        this.selectData = selectData;
    }

    public ContactInfoData getGetData() {
        return getData;
    }

    public void setGetData(ContactInfoData getData) {
        this.getData = getData;
    }
}
