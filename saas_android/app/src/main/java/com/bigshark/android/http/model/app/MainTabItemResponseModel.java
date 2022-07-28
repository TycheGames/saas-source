package com.bigshark.android.http.model.app;

/**
 *
 */
public class MainTabItemResponseModel {
    private int tag; // tab的fragment类型
    private String url;

    /**
     * 是否为默认展示的tab
     * <p>
     * v1.5.1版本添加
     */
    private boolean isDefaultShow = false;

    public boolean isDefaultShow() {
        return isDefaultShow;
    }

    public void setDefaultShow(boolean defaultShow) {
        isDefaultShow = defaultShow;
    }

    // tab title的颜色
    private String span_color = "#999999"; // 默认文字颜色
    private String sel_span_color = "#28BB32"; // 选中文字颜色

    private String title; // 标题

    // 默认图标
    private transient int selectIcon;
    private transient int unSelectIcon;
    // 图片URL
    private String normalImage;
    private String selectImage;

    private String redPointShowTime;
    private transient boolean isClickRedPoint;

    public String getTitle() {
        return title;
    }

    public void setTitle(String title) {
        this.title = title;
    }

    public int getTag() {
        return tag;
    }

    public void setTag(int tag) {
        this.tag = tag;
    }

    public String getNormalImage() {
        return normalImage;
    }

    public void setNormalImage(String normalImage) {
        this.normalImage = normalImage;
    }

    public String getSelectImage() {
        return selectImage;
    }

    public void setSelectImage(String selectImage) {
        this.selectImage = selectImage;
    }


    public String getSpan_color() {
        return span_color;
    }

    public void setSpan_color(String span_color) {
        this.span_color = span_color;
    }

    public String getSel_span_color() {
        return sel_span_color;
    }

    public void setSel_span_color(String sel_span_color) {
        this.sel_span_color = sel_span_color;
    }


    public String getUrl() {
        return url;
    }

    public void setUrl(String url) {
        this.url = url;
    }


    public int getSelectIcon() {
        return selectIcon;
    }

    public void setSelectIcon(int selectIcon) {
        this.selectIcon = selectIcon;
    }

    public int getUnSelectIcon() {
        return unSelectIcon;
    }

    public void setUnSelectIcon(int unSelectIcon) {
        this.unSelectIcon = unSelectIcon;
    }

    public String getRedPointShowTime() {
        return redPointShowTime;
    }

    public void setRedPointShowTime(String redPointShowTime) {
        this.redPointShowTime = redPointShowTime;
    }

    public boolean isClickRedPoint() {
        return isClickRedPoint;
    }

    public void setClickRedPoint(boolean clickRedPoint) {
        isClickRedPoint = clickRedPoint;
    }
}
