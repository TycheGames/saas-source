package com.bigshark.android.common.source.sharedpreferences;

import com.bigshark.android.core.component.BaseApplication;


/**
 * Created by Administrator on 2017/3/31.
 * 用户相关的
 */
public class SharedPreferencesApiUser extends SharedPreferencesApiBase {

    private static final String NAME = "user";

    private SharedPreferencesApiUser() {
        super(BaseApplication.app, NAME);
    }

    public static SharedPreferencesApiUser instance() {
        return INSTANCE;
    }

    private static final SharedPreferencesApiUser INSTANCE = new SharedPreferencesApiUser();


    /**
     * 用户名:phone
     */
    private static final String KEY_USER_NAME = /*BuildConfig.PACKAGE_NAME +*/ "user_name";
    /**
     * sessionId
     */
    private static final String KEY_SESSION_ID = /*BuildConfig.PACKAGE_NAME +*/ "sessionid";
    /**
     * 用户信息
     */
    private static final String KEY_USER_INFO = /*BuildConfig.PACKAGE_NAME +*/ "user_info";


    public void clearUserInfo() {
        edit().putString(KEY_USER_NAME, "").putString(KEY_SESSION_ID, "").apply();
    }

    public void setUserInfo(String username, String sessionId) {
        edit().putString(KEY_USER_NAME, username).putString(KEY_SESSION_ID, sessionId).apply();
    }

    public String getPhone() {
        return sp.getString(KEY_USER_NAME, "");
    }

    public String getUserName() {
        return sp.getString(KEY_USER_NAME, "");
    }

    public String getSessionId() {
        return sp.getString(KEY_SESSION_ID, "");
    }

    public String getUserInfo() {
        return sp.getString(KEY_USER_INFO, "");
    }

    public void setUserInfo(String userInfo) {
        edit().putString(KEY_USER_INFO, userInfo).apply();
    }
}