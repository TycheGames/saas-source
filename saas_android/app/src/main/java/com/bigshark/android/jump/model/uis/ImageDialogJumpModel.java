package com.bigshark.android.jump.model.uis;


import com.bigshark.android.jump.base.JumpModel;

public class ImageDialogJumpModel extends JumpModel {

    private LogicParam logic;
    private ContentParam content;

    public ContentParam getContent() {
        return content;
    }

    public void setContent(ContentParam content) {
        this.content = content;
    }

    public LogicParam getLogic() {
        return logic;
    }

    public void setLogic(LogicParam logic) {
        this.logic = logic;
    }

    /**
     * 弹框是否展示的逻辑部分
     */
    public static final class LogicParam {
        private String uniqueId;//  弹框的唯一id
        private int totalCount;// 弹框可以展示的总次数(若为0或负数展示次数则无限制，否则展示次数有限制)

        public String getUniqueId() {
            return uniqueId;
        }

        public void setUniqueId(String uniqueId) {
            this.uniqueId = uniqueId;
        }

        public int getTotalCount() {
            return totalCount;
        }

        public void setTotalCount(int totalCount) {
            this.totalCount = totalCount;
        }
    }

    /**
     * 展示的内容部分
     */
    public static final class ContentParam {
        private String imageUrl;// dialog的图片URL
        private String jump;// 跳转指令
        private boolean isShowCloseView = false;// 是否显示关闭按钮
        private boolean isCloseDialogAfterClicked = true;// 点击后是否关闭弹框

        public String getImageUrl() {
            return imageUrl;
        }

        public void setImageUrl(String imageUrl) {
            this.imageUrl = imageUrl;
        }

        public boolean isShowCloseView() {
            return isShowCloseView;
        }

        public void setShowCloseView(boolean showCloseView) {
            isShowCloseView = showCloseView;
        }

        public boolean isCloseDialogAfterClicked() {
            return isCloseDialogAfterClicked;
        }

        public void setCloseDialogAfterClicked(boolean closeDialogAfterClicked) {
            isCloseDialogAfterClicked = closeDialogAfterClicked;
        }

        public String getJump() {
            return jump;
        }

        public void setJump(String jump) {
            this.jump = jump;
        }
    }
}
