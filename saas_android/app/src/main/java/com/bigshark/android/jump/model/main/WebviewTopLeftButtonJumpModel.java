package com.bigshark.android.jump.model.main;


import com.bigshark.android.jump.base.JumpModel;

/**
 * @author Administrator
 * @date 2017/12/20
 */
public class WebviewTopLeftButtonJumpModel extends JumpModel {

    /**
     * webview中右上角点击功能的实体类
     */
    private ClickBean data;


    public ClickBean getData() {
        return data;
    }

    public void setData(ClickBean data) {
        this.data = data;
    }

    public static final class ClickBean {
        /**
         * 控制恢复为默认的控制，即为直接调用webview的goback与Activity的finish
         */
        private boolean controlRestoreDefault = true;

        public boolean isControlRestoreDefault() {
            return controlRestoreDefault;
        }

        public void setControlRestoreDefault(boolean controlRestoreDefault) {
            this.controlRestoreDefault = controlRestoreDefault;
        }

        private String callback;
        /**
         * 恢复默认功能
         */
        private boolean restoreDefaultFunction = false;
        private String click;

        public String getCallback() {
            return callback;
        }

        public void setCallback(String callback) {
            this.callback = callback;
        }

        public boolean isRestoreDefaultFunction() {
            return restoreDefaultFunction;
        }

        public void setRestoreDefaultFunction(boolean restoreDefaultFunction) {
            this.restoreDefaultFunction = restoreDefaultFunction;
        }

        public String getClick() {
            return click;
        }

        public void setClick(String click) {
            this.click = click;
        }

    }
}
