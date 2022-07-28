package com.bigshark.android.http.model.mine;

/**
 * Truecaller的登录信息，继承电话号的登录信息
 */
public class PersonalInfoTruecallerResponseModel extends PersonalLgoinInfoResponseModel {

//    // 用户名: 手机号
//    private String username;
//    // session信息
//    private String sessionid;

    /**
     * 这次的truecaller操作，用户是登录、还是注册
     */
    private boolean isLogin = false;

    public boolean isLogin() {
        return isLogin;
    }

    public void setLogin(boolean login) {
        isLogin = login;
    }
}
