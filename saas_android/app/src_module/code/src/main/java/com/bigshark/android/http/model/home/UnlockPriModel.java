package com.bigshark.android.http.model.home;

/**
 * @创建者 wenqi
 * @创建时间 2019/6/1 11:31
 * @描述 没有去爱过怎知她美 没有你爱我问怎可脱俗
 */
public class UnlockPriModel {

    private float available_money;//账户可用余额

    private boolean is_unlock;//是否解锁

    public float getAvailable_money() {
        return available_money;
    }

    public void setAvailable_money(float available_money) {
        this.available_money = available_money;
    }

    public boolean isIs_unlock() {
        return is_unlock;
    }

    public void setIs_unlock(boolean is_unlock) {
        this.is_unlock = is_unlock;
    }
}
