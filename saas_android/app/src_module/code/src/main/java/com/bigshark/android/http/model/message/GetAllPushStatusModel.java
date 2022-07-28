package com.bigshark.android.http.model.message;

/**
 * @创建者 wenqi
 * @创建时间 2019/6/17 20:43
 * @描述 没有去爱过怎知她美 没有你爱我问怎可脱俗
 */
public class GetAllPushStatusModel {


    /**
     * privateChat : {"status":"2"}
     * broadcast : {"status":1}
     * system : {"status":1}
     * comment : {"status":1}
     * reply : {"status":1}
     * profit : {"status":1}
     */

    private PushStateBean privateChat;//私聊
    private PushStateBean broadcast;//广播
    private PushStateBean system;//系统
    private PushStateBean comment;//评论
    private PushStateBean reply;//回复点赞
    private PushStateBean profit;//收益

    public PushStateBean getPrivateChat() {
        return privateChat;
    }

    public void setPrivateChat(PushStateBean privateChat) {
        this.privateChat = privateChat;
    }

    public PushStateBean getBroadcast() {
        return broadcast;
    }

    public void setBroadcast(PushStateBean broadcast) {
        this.broadcast = broadcast;
    }

    public PushStateBean getSystem() {
        return system;
    }

    public void setSystem(PushStateBean system) {
        this.system = system;
    }

    public PushStateBean getComment() {
        return comment;
    }

    public void setComment(PushStateBean comment) {
        this.comment = comment;
    }

    public PushStateBean getReply() {
        return reply;
    }

    public void setReply(PushStateBean reply) {
        this.reply = reply;
    }

    public PushStateBean getProfit() {
        return profit;
    }

    public void setProfit(PushStateBean profit) {
        this.profit = profit;
    }

    public static class PushStateBean {

        private int status;//1 允许  2 不允许

        public int getStatus() {
            return status;
        }

        public void setStatus(int status) {
            this.status = status;
        }
    }


}
