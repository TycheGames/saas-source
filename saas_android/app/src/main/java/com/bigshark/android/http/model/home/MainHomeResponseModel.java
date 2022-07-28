package com.bigshark.android.http.model.home;

public class MainHomeResponseModel {

    private String title;// 顶部的"loan"文案
    private String moneyTip;// 金额上部的文案(Your maximum credit to borrow)
    private int moneyAmount;// 金额：根据登录态、用户认证等数据显示用户当前的借款金额
    private String actionTip;// 按钮上部的文案(Boost your credit by repaying your loan on time)
    private String actionText;// 按钮的文案，根据用户进行显示
    private String jump;// 按钮的跳转指令
    private boolean isRefreshTabList;
    private FloatImageEntity floatInfo;// 悬浮图片


    public String getTitle() {
        return title;
    }

    public void setTitle(String title) {
        this.title = title;
    }

    public String getMoneyTip() {
        return moneyTip;
    }

    public void setMoneyTip(String moneyTip) {
        this.moneyTip = moneyTip;
    }

    public int getMoneyAmount() {
        return moneyAmount;
    }

    public void setMoneyAmount(int moneyAmount) {
        this.moneyAmount = moneyAmount;
    }

    public String getActionTip() {
        return actionTip;
    }

    public void setActionTip(String actionTip) {
        this.actionTip = actionTip;
    }

    public String getActionText() {
        return actionText;
    }

    public void setActionText(String actionText) {
        this.actionText = actionText;
    }

    public String getJump() {
        return jump;
    }

    public void setJump(String jump) {
        this.jump = jump;
    }

    public boolean isRefreshTabList() {
        return isRefreshTabList;
    }

    public void setRefreshTabList(boolean refreshTabList) {
        isRefreshTabList = refreshTabList;
    }

    public FloatImageEntity getFloatInfo() {
        return floatInfo;
    }

    public void setFloatInfo(FloatImageEntity floatInfo) {
        this.floatInfo = floatInfo;
    }


    public static final class FloatImageEntity {
        private String imageUrl;// 图片地址
        private String jump;// 跳转

        public String getImageUrl() {
            return imageUrl;
        }

        public void setImageUrl(String imageUrl) {
            this.imageUrl = imageUrl;
        }

        public String getJump() {
            return jump;
        }

        public void setJump(String jump) {
            this.jump = jump;
        }
    }

}
