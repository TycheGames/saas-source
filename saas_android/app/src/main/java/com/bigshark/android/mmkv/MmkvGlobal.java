package com.bigshark.android.mmkv;

import com.bigshark.android.BuildConfig;
import com.bigshark.android.core.utils.ConvertUtils;
import com.bigshark.android.http.model.app.ConfigResponseModel;
import com.bigshark.android.utils.StringConstant;
import com.bigshark.android.utils.ConvertUtil;
import com.tencent.mmkv.MMKV;

import java.util.HashMap;
import java.util.List;
import java.util.Map;


/**
 * Created by Administrator on 2017/3/31.
 * 下发的配置
 */
public class MmkvGlobal {

    private final MMKV mmkv;

    private MmkvGlobal() {
        mmkv = MMKV.mmkvWithID(StringConstant.MMKV_GROUP_GLOBAL);
    }

    private static final class Helper {
        private static final MmkvGlobal INSTANCE = new MmkvGlobal();
    }

    public static MmkvGlobal instance() {
        return Helper.INSTANCE;
    }

    public void setConfigInfos(ConfigResponseModel configModel, String updateMsg, List<String> cookieDomains, Map<String, String> dataUrls) {
        mmkv.encode(StringConstant.MMKV_API_GLOBAL_KEY_UPDATE_CONTENT, updateMsg);
        mmkv.encode(StringConstant.MMKV_API_GLOBAL_KEY_COOKISE_DOMAINS, ConvertUtils.toString(cookieDomains));
        for (HashMap.Entry<String, String> urlEntry : dataUrls.entrySet()) {
            mmkv.encode(getCacheUrlRealKey(urlEntry.getKey()), urlEntry.getValue());
        }

        // code
        mmkv.putString(StringConstant.MMKV_API_GLOBAL_KEY_APP_NAME, configModel.getAppName());
        mmkv.putString(StringConstant.MMKV_API_GLOBAL_KEY_APP_TEXT, ConvertUtils.toString(configModel.getAppText()));
        mmkv.putString(StringConstant.MMKV_API_GLOBAL_KEY_URL_ALREADYVIPLINK, configModel.getAlreadyVipLink());
        mmkv.putString(StringConstant.MMKV_API_GLOBAL_KEY_URL_ARGUMENTSLINK, configModel.getArgumentsLink());
        mmkv.putString(StringConstant.MMKV_API_GLOBAL_KEY_URL_AUTHCENTERLINK, configModel.getAuthCenterLink());
        mmkv.putString(StringConstant.MMKV_API_GLOBAL_KEY_URL_COMPLETEINFOLINK, configModel.getCompleteInfoLink());
        mmkv.putString(StringConstant.MMKV_API_GLOBAL_KEY_URL_FEMALEINVITELINK, configModel.getFemaleInviteLink());
        mmkv.putString(StringConstant.MMKV_API_GLOBAL_KEY_URL_MYWALLETLINK, configModel.getMyWalletLink());
        mmkv.putString(StringConstant.MMKV_API_GLOBAL_KEY_URL_VIPLINK, configModel.getVipLink());

        if(configModel.getAppPrice() != null) {
            mmkv.putInt(StringConstant.MMKV_API_GLOBAL_KEY_PRICE_BROADCAST, configModel.getAppPrice().getBroadcastPrice());
            mmkv.putInt(StringConstant.MMKV_API_GLOBAL_KEY_PRICE_REDPACK_PHOTO, configModel.getAppPrice().getRedPackAmount());
            mmkv.putInt(StringConstant.MMKV_API_GLOBAL_KEY_PRICE_PRIVATECHAT, configModel.getAppPrice().getPrivateChat());
        }
        mmkv.putString(StringConstant.MMKV_API_GLOBAL_KEY_SUPPORT_PHONE, configModel.getSupport_phone());
    }


    public String getUpdateContent() {
        return mmkv.decodeString(StringConstant.MMKV_API_GLOBAL_KEY_UPDATE_CONTENT, "");
    }

    public void clearUpdateContent() {
        mmkv.removeValueForKey(StringConstant.MMKV_API_GLOBAL_KEY_UPDATE_CONTENT);
    }

    public List<String> getCookieDomains() {
        String cookieDomainText = mmkv.decodeString(StringConstant.MMKV_API_GLOBAL_KEY_COOKISE_DOMAINS, "");
        return ConvertUtils.toList(cookieDomainText, String.class);
    }

    /**
     * 缓存的URL，历史的URL也会存在在里面
     */
    public String getCacheUrl(String urlKey) {
        return mmkv.decodeString(getCacheUrlRealKey(urlKey), "");
    }

    /**
     * 缓存URL key的实际名称
     */
    private static String getCacheUrlRealKey(String urlKey) {
        return BuildConfig.PACKAGE_NAME + "url_" + urlKey;
    }


    //<editor-fold desc="code">

    public String getSupportPhone() {
        return mmkv.getString(StringConstant.MMKV_API_GLOBAL_KEY_SUPPORT_PHONE, "");
    }

    public int getPriceBroadcast() {
        return mmkv.getInt(StringConstant.MMKV_API_GLOBAL_KEY_PRICE_BROADCAST, 0);
    }

    public int getPriceRedpackPhoto() {
        return mmkv.getInt(StringConstant.MMKV_API_GLOBAL_KEY_PRICE_REDPACK_PHOTO, 0);
    }

    public int getPricePrivatechat() {
        return mmkv.getInt(StringConstant.MMKV_API_GLOBAL_KEY_PRICE_PRIVATECHAT, 0);
    }

    public void setCityJson(String str) {
        mmkv.putString(StringConstant.MMKV_API_GLOBAL_KEY_CITY, str).apply();
    }


    public String getArgumentsLink() {
        return mmkv.getString(StringConstant.MMKV_API_GLOBAL_KEY_URL_ARGUMENTSLINK, "");
    }

    public String getAuthCenterLink() {
        return mmkv.getString(StringConstant.MMKV_API_GLOBAL_KEY_URL_AUTHCENTERLINK, "");
    }

    public String getCompleteInfoLink() {
        return mmkv.getString(StringConstant.MMKV_API_GLOBAL_KEY_URL_COMPLETEINFOLINK, "");
    }

    public String getFemaleInviteLink() {
        return mmkv.getString(StringConstant.MMKV_API_GLOBAL_KEY_URL_FEMALEINVITELINK, "");
    }

    public String getMyWalletLink() {
        return mmkv.getString(StringConstant.MMKV_API_GLOBAL_KEY_URL_MYWALLETLINK, "");
    }

    public String getVipLink() {
        return mmkv.getString(StringConstant.MMKV_API_GLOBAL_KEY_URL_VIPLINK, "");
    }

    //</editor-fold>
}
