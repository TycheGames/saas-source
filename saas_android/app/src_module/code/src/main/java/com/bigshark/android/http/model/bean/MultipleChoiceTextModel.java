package com.bigshark.android.http.model.bean;

/**
 * @创建者 wenqi
 * @创建时间 2019/5/23 14:39
 * @描述 没有去爱过怎知她美 没有你爱我问怎可脱俗
 */
public class MultipleChoiceTextModel {

    private String text;
    private boolean is_selected;

    public String getText() {
        return text;
    }

    public void setText(String text) {
        this.text = text;
    }

    public boolean isIs_selected() {
        return is_selected;
    }

    public void setIs_selected(boolean is_selected) {
        this.is_selected = is_selected;
    }
}
