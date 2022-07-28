package com.bigshark.android.http.model.app;

/**
 * @创建者 wenqi
 * @创建时间 2019/6/26 15:38
 * @描述 没有去爱过怎知她美 没有你爱我问怎可脱俗
 */
public class AppPriceModel {


    /**
     * broadcastPrice : 50
     * redPackAmount : 3
     * privateChat : 10
     */
    private int broadcastPrice;
    private int redPackAmount;
    private int privateChat;

    public int getBroadcastPrice() {
        return broadcastPrice;
    }

    public void setBroadcastPrice(int broadcastPrice) {
        this.broadcastPrice = broadcastPrice;
    }

    public int getRedPackAmount() {
        return redPackAmount;
    }

    public void setRedPackAmount(int redPackAmount) {
        this.redPackAmount = redPackAmount;
    }

    public int getPrivateChat() {
        return privateChat;
    }

    public void setPrivateChat(int privateChat) {
        this.privateChat = privateChat;
    }
}
