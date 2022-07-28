package com.bigshark.android.http.model.app;

import java.util.List;
import java.util.Map;

public class ConfigResponseModel {

    private String name;
    private String configVersion;
    private String androidVersion; // 安卓版本号

    //<editor-fold desc="code">
    private String appName;

    private AppTextModel appText;

    private String alreadyVipLink;//已经是会员 我的会员地址
    private String argumentsLink;//用户协议地址
    private String authCenterLink;//认证中心地址
    private String completeInfoLink;//完善資料地址

    private String femaleInviteLink;//女性分享红包邀请
    private String myWalletLink;//我的钱包地址
    private String vipLink;//非会员 我的会员地址
    private String support_phone; // 02150682305
    private AppPriceModel appPrice;

    //</editor-fold>

    private String user_agreement_url; // 用户协议
    private String privacyPolicyUrl; // 隐私协议
    private String termsOfUseUrl; // 使用协议

    private List<String> shareCookieDomain; // 分享

    private Map<String, String> dataUrl; // 接口地址
    private String homeImageDialogCommand;// 首页图片弹框指令，可以当做是首页指令

    private String updateMsg; // APP更新

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }

    public String getConfigVersion() {
        return configVersion;
    }

    public void setConfigVersion(String configVersion) {
        this.configVersion = configVersion;
    }

    public String getAndroidVersion() {
        return androidVersion;
    }

    public void setAndroidVersion(String androidVersion) {
        this.androidVersion = androidVersion;
    }

    public String getUser_agreement_url() {
        return user_agreement_url;
    }

    public void setUser_agreement_url(String user_agreement_url) {
        this.user_agreement_url = user_agreement_url;
    }

    public List<String> getShareCookieDomain() {
        return shareCookieDomain;
    }

    public void setShareCookieDomain(List<String> shareCookieDomain) {
        this.shareCookieDomain = shareCookieDomain;
    }

    public Map<String, String> getDataUrl() {
        return dataUrl;
    }

    public void setDataUrl(Map<String, String> dataUrl) {
        this.dataUrl = dataUrl;
    }

    public String getPrivacyPolicyUrl() {
        return privacyPolicyUrl;
    }

    public void setPrivacyPolicyUrl(String privacyPolicyUrl) {
        this.privacyPolicyUrl = privacyPolicyUrl;
    }

    public String getTermsOfUseUrl() {
        return termsOfUseUrl;
    }

    public void setTermsOfUseUrl(String termsOfUseUrl) {
        this.termsOfUseUrl = termsOfUseUrl;
    }

    public String getHomeImageDialogCommand() {
        return homeImageDialogCommand;
    }

    public void setHomeImageDialogCommand(String homeImageDialogCommand) {
        this.homeImageDialogCommand = homeImageDialogCommand;
    }

    public String getUpdateMsg() {
        return updateMsg;
    }

    public void setUpdateMsg(String updateMsg) {
        this.updateMsg = updateMsg;
    }

    private boolean openTruecaller;// 输入手机号页面，是否有truecaller登录的功能

    public boolean isOpenTruecaller() {
        return openTruecaller;
    }

    public void setOpenTruecaller(boolean openTruecaller) {
        this.openTruecaller = openTruecaller;
    }

    //<editor-fold desc="code">

    public String getSupport_phone() {
        return support_phone;
    }

    public void setSupport_phone(String support_phone) {
        this.support_phone = support_phone;
    }

    public AppPriceModel getAppPrice() {
        return appPrice;
    }

    public void setAppPrice(AppPriceModel appPrice) {
        this.appPrice = appPrice;
    }

    public String getAppName() {
        return appName;
    }

    public void setAppName(String appName) {
        this.appName = appName;
    }

    public AppTextModel getAppText() {
        return appText;
    }

    public void setAppText(AppTextModel appText) {
        this.appText = appText;
    }

    public String getAlreadyVipLink() {
        return alreadyVipLink;
    }

    public void setAlreadyVipLink(String alreadyVipLink) {
        this.alreadyVipLink = alreadyVipLink;
    }

    public String getArgumentsLink() {
        return argumentsLink;
    }

    public void setArgumentsLink(String argumentsLink) {
        this.argumentsLink = argumentsLink;
    }

    public String getAuthCenterLink() {
        return authCenterLink;
    }

    public void setAuthCenterLink(String authCenterLink) {
        this.authCenterLink = authCenterLink;
    }

    public String getCompleteInfoLink() {
        return completeInfoLink;
    }

    public void setCompleteInfoLink(String completeInfoLink) {
        this.completeInfoLink = completeInfoLink;
    }

    public String getFemaleInviteLink() {
        return femaleInviteLink;
    }

    public void setFemaleInviteLink(String femaleInviteLink) {
        this.femaleInviteLink = femaleInviteLink;
    }

    public String getMyWalletLink() {
        return myWalletLink;
    }

    public void setMyWalletLink(String myWalletLink) {
        this.myWalletLink = myWalletLink;
    }

    public String getVipLink() {
        return vipLink;
    }

    public void setVipLink(String vipLink) {
        this.vipLink = vipLink;
    }

    //</editor-fold>
}
