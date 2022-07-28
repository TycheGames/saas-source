package com.bigshark.android.http.model.bean;

/**
 * @创建者 wenqi
 * @创建时间 2019/6/14 17:20
 * @描述 没有去爱过怎知她美 没有你爱我问怎可脱俗
 */
public class MethodPaymentModel {

    private String type;
    private int checkIcon;
    private int uncheckIcon;
    private String methodName;
    private boolean isCheck;
    private boolean isClick;

    public String getType() {
        return type;
    }

    public void setType(String type) {
        this.type = type;
    }

    public int getCheckIcon() {
        return checkIcon;
    }

    public void setCheckIcon(int checkIcon) {
        this.checkIcon = checkIcon;
    }

    public int getUncheckIcon() {
        return uncheckIcon;
    }

    public void setUncheckIcon(int uncheckIcon) {
        this.uncheckIcon = uncheckIcon;
    }

    public String getMethodName() {
        return methodName;
    }

    public void setMethodName(String methodName) {
        this.methodName = methodName;
    }

    public boolean isCheck() {
        return isCheck;
    }

    public void setCheck(boolean check) {
        isCheck = check;
    }

    public boolean isClick() {
        return isClick;
    }

    public void setClick(boolean click) {
        isClick = click;
    }
}
