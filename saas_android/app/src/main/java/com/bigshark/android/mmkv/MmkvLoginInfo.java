package com.bigshark.android.mmkv;

import android.content.SharedPreferences;

import com.bigshark.android.http.model.user.UserInfoModel;
import com.bigshark.android.utils.StringConstant;
import com.tencent.mmkv.MMKV;


/**
 * Created by Administrator on 2017/3/31.
 * 用户相关的
 */
public class MmkvLoginInfo {

    private final MMKV mmkv;

    private MmkvLoginInfo() {
        mmkv = MMKV.mmkvWithID(StringConstant.MMKV_GROUP_LOGIN_INFO);
    }

    private static final class Helper {
        private static final MmkvLoginInfo INSTANCE = new MmkvLoginInfo();
    }

    public static MmkvLoginInfo instance() {
        return Helper.INSTANCE;
    }

    public void clearUserInfo() {
        mmkv.removeValueForKey(StringConstant.MMKV_API_LOGIN_INFO_KEY_PHONE);
        mmkv.removeValueForKey(StringConstant.MMKV_API_LOGIN_INFO_KEY_USER_NAME);
        mmkv.removeValueForKey(StringConstant.MMKV_API_LOGIN_INFO_KEY_SESSION_ID);
    }

    public void setUserInfo(String username, String sessionId) {
        mmkv.encode(StringConstant.MMKV_API_LOGIN_INFO_KEY_PHONE, username);
        mmkv.encode(StringConstant.MMKV_API_LOGIN_INFO_KEY_USER_NAME, username);
        mmkv.encode(StringConstant.MMKV_API_LOGIN_INFO_KEY_SESSION_ID, sessionId);
    }

    public String getPhone() {
        return mmkv.decodeString(StringConstant.MMKV_API_LOGIN_INFO_KEY_PHONE, "");
    }

    public String getUserName() {
        return mmkv.decodeString(StringConstant.MMKV_API_LOGIN_INFO_KEY_USER_NAME, "");
    }

    public String getSessionId() {
        return mmkv.decodeString(StringConstant.MMKV_API_LOGIN_INFO_KEY_SESSION_ID, "");
    }

    public String getUserData() {
        return mmkv.decodeString(StringConstant.MMKV_API_LOGIN_INFO_KEY_USER_DATA, "");
    }

    public void setUserInfo(String userInfo) {
        mmkv.encode(StringConstant.MMKV_API_LOGIN_INFO_KEY_USER_DATA, userInfo);
    }


    //<editor-fold desc="code">

    public void setUserInfoBean(UserInfoModel user) {
        final boolean isClearUserInfo = user == null;
        SharedPreferences.Editor editor = mmkv;
        editor.putString(StringConstant.MMKV_API_LOGIN_INFO_KEY_UID, isClearUserInfo ? "" : user.getUser_id() + "");
        editor.putInt(StringConstant.MMKV_API_LOGIN_INFO_KEY_STATUS, isClearUserInfo ? 0 : user.getStatus());
        editor.putInt(StringConstant.MMKV_API_LOGIN_INFO_KEY_GENDER, isClearUserInfo ? 0 : user.getSex());
        editor.putString(StringConstant.MMKV_API_LOGIN_INFO_KEY_JPUSH_REGISTRATION_ID, isClearUserInfo ? "" : user.getJpush_registration_id());
        editor.putString(StringConstant.MMKV_API_LOGIN_INFO_KEY_NICKNAME, isClearUserInfo ? "" : user.getNickname());
        editor.putInt(StringConstant.MMKV_API_LOGIN_INFO_KEY_IDENTIFY_STATE, isClearUserInfo ? 0 : user.getIsIdentify());
        editor.putInt(StringConstant.MMKV_API_LOGIN_INFO_KEY_VIP_STATE, isClearUserInfo ? 0 : user.getIsVip());

        editor.putString(StringConstant.MMKV_API_LOGIN_INFO_KEY_ACCID, isClearUserInfo ? "" : user.getAccid());
        editor.putString(StringConstant.MMKV_API_LOGIN_INFO_KEY_TOKEN, isClearUserInfo ? "" : user.getToken());

        editor.apply();
    }

    public int getUserGender() {
        return mmkv.getInt(StringConstant.MMKV_API_LOGIN_INFO_KEY_GENDER, 0);
    }

    public void setUserGender(int sex) {
        mmkv.putInt(StringConstant.MMKV_API_LOGIN_INFO_KEY_GENDER, sex).apply();
    }

    public void setVipState(int state) {
        mmkv.putInt(StringConstant.MMKV_API_LOGIN_INFO_KEY_VIP_STATE, state).apply();
    }

    public int getVipState() {
        return mmkv.getInt(StringConstant.MMKV_API_LOGIN_INFO_KEY_VIP_STATE, 0);
    }

    public void setIdentifyState(int state) {
        mmkv.putInt(StringConstant.MMKV_API_LOGIN_INFO_KEY_IDENTIFY_STATE, state).apply();
    }

    public int getIdentifyState() {
        return mmkv.getInt(StringConstant.MMKV_API_LOGIN_INFO_KEY_IDENTIFY_STATE, 0);
    }


    public void setNickname(String nickname) {
        mmkv.putString(StringConstant.MMKV_API_LOGIN_INFO_KEY_NICKNAME, nickname).apply();
    }

    public String getNickname() {
        return mmkv.getString(StringConstant.MMKV_API_LOGIN_INFO_KEY_NICKNAME, "");
    }

    public void setUID(String uid) {
        mmkv.putString(StringConstant.MMKV_API_LOGIN_INFO_KEY_UID, uid).apply();
    }

    public String getUID() {
        return mmkv.getString(StringConstant.MMKV_API_LOGIN_INFO_KEY_UID, "");
    }

    public void setStatus(int status) {
        mmkv.putInt(StringConstant.MMKV_API_LOGIN_INFO_KEY_STATUS, status).apply();
    }

    public int getStatus() {
        return mmkv.getInt(StringConstant.MMKV_API_LOGIN_INFO_KEY_STATUS, 0);
    }

    public String getLoginInfo() {
        return mmkv.getString(StringConstant.MMKV_API_LOGIN_INFO_KEY_LOGININFO, "");
    }

    public void setLoginInfo(String info) {
        mmkv.putString(StringConstant.MMKV_API_LOGIN_INFO_KEY_LOGININFO, info).apply();
    }

    //</editor-fold>


    public void clear() {
        mmkv.clear();
    }
}