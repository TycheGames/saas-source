package com.bigshark.android.contexts;

import android.app.Activity;
import android.content.Context;
import android.content.Intent;

import com.bigshark.android.activities.home.MainActivity;
import com.bigshark.android.activities.mine.UserEnterActivity;
import com.bigshark.android.common.browser.BrowserCookieManagerUtils;
import com.bigshark.android.core.common.event.UserGotoLoginPageEvent;
import com.bigshark.android.core.common.event.UserLoginedEvent;
import com.bigshark.android.core.display.IDisplay;
import com.bigshark.android.core.utils.ConvertUtils;
import com.bigshark.android.events.BaseDisplayEventModel;
import com.bigshark.android.events.RefreshDisplayEventModel;
import com.bigshark.android.http.model.mine.PersonalLgoinInfoResponseModel;
import com.bigshark.android.mmkv.MmkvGroup;

import java.util.List;

import de.greenrobot.event.EventBus;


/**
 * Created by Administrator on 2017/8/16.
 * 用户中心
 */
public class PersonalContext {

    public static PersonalContext instance() {
        return Helper.INSTANCE;
    }

    private static final class Helper {
        static final PersonalContext INSTANCE = new PersonalContext();
    }

    private PersonalContext() {
        EventBus.getDefault().register(this);
    }


    // 数据

    private PersonalLgoinInfoResponseModel lgoinInfoResponseModel;

    public void iniLoginInfo() {
        syncUserLgoinStatus(geLoginInfo());
    }

    public PersonalLgoinInfoResponseModel geLoginInfo() {
        if (lgoinInfoResponseModel == null) {
            // 缓存user，这样不需要每次都将json字符串转换为object
            lgoinInfoResponseModel = ConvertUtils.toObject(MmkvGroup.loginInfo().getUserData(), PersonalLgoinInfoResponseModel.class);
        }
        return lgoinInfoResponseModel;
    }

    public String getUserId() {
        try {
            return geLoginInfo().getUsername();
        } catch (Exception ignore) {
            return "";
        }
    }


    // 跳转到登录页(输入手机号)

    public void onEventMainThread(UserGotoLoginPageEvent event) {
        toLogin(event.getDisplay().act());
    }

    public void toLogin(Activity display) {
        UserEnterActivity.createIntent(display);
    }


    // 数据--更新

    /**
     * 登陆成功后保存用户信息
     */
    public void saveUserInfo(PersonalLgoinInfoResponseModel userInfo, IDisplay display) {
        if (userInfo == null) {
            return;
        }

        MmkvGroup.loginInfo().setUserInfo(userInfo.getUsername(), userInfo.getSessionid());
        syncUserLgoinStatus(userInfo);

        List<String> cookieDomains = MmkvGroup.global().getCookieDomains();
        BrowserCookieManagerUtils.setCookie(display.act(), userInfo.getSessionid(), userInfo.getUsername(), cookieDomains);
//        UploadHelper.reportServiceDatas(display, false);
        EventBus.getDefault().post(new RefreshDisplayEventModel(BaseDisplayEventModel.EVENT_REFRESH_MAIN_TAB_LIST));
        EventBus.getDefault().post(new UserLoginedEvent(display));
    }

    public void syncUserLgoinStatus(PersonalLgoinInfoResponseModel loginInfos) {
        this.lgoinInfoResponseModel = loginInfos;
        MmkvGroup.loginInfo().setUserInfo(ConvertUtils.toString(loginInfos));
    }


    // 登出

    /**
     * 退出
     */
    public void doLogout(Context context) {
        clearLoginStatus(context);
        MmkvGroup.loginInfo().clear();
        EventBus.getDefault().post(new RefreshDisplayEventModel(BaseDisplayEventModel.EVENT_REFRESH_MAIN_TAB_LIST));

        Intent intent = new Intent(context, MainActivity.class);
        intent.addFlags(Intent.FLAG_ACTIVITY_CLEAR_TASK | Intent.FLAG_ACTIVITY_NEW_TASK);
        context.startActivity(intent);
    }

    /************
     * 清除登录状态
     */
    private void clearLoginStatus(Context context) {
        MmkvGroup.loginInfo().clearUserInfo();
        syncUserLgoinStatus(null);
        // 清除cookie
        BrowserCookieManagerUtils.clearCookie(context);
    }
}
