package com.bigshark.android.http.model.home;

/**
 * @创建者 wenqi
 * @创建时间 2019/6/24 21:34
 * @描述 没有去爱过怎知她美 没有你爱我问怎可脱俗
 */
public class UserReportOptionsModel {

    /**
     * id : 1
     * name : 发广告
     */

    private String id;
    private String name;
    private boolean isSelected;

    public String getId() {
        return id;
    }

    public void setId(String id) {
        this.id = id;
    }

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }

    public boolean isSelected() {
        return isSelected;
    }

    public void setSelected(boolean selected) {
        isSelected = selected;
    }
}
