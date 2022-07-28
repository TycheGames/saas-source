package com.bigshark.android.http.model.home;

/**
 * @创建者 wenqi
 * @创建时间 2019/5/29 10:07
 * @描述 评价item
 */
public class EvaluationItemModel {

    /**
     * id : 7
     * name : 本人
     * count : 0
     */
    private String id;// 特征id
    private String name;//特征名称
    private int    count;//评价数

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

    public int getCount() {
        return count;
    }

    public void setCount(int count) {
        this.count = count;
    }
}
