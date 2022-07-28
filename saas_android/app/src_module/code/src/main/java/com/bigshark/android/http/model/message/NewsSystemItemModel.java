package com.bigshark.android.http.model.message;

/**
 * @创建者 wenqi
 * @创建时间 2019/6/12 21:37
 * @描述 没有去爱过怎知她美 没有你爱我问怎可脱俗
 */
public class NewsSystemItemModel {


    /**
     * id : 2
     * contents : 我再给你说哦
     * created_at : 2019/05/05 09:58
     * img_url :
     * http_url :
     */

    private String id;//消息id
    private String title;//
    private String contents;//消息内容
    private String created_at;//时间
    private String img_url;//图片地址
    private String http_url;//跳转地址

    public String getId() {
        return id;
    }

    public void setId(String id) {
        this.id = id;
    }

    public String getTitle() {
        return title;
    }

    public void setTitle(String title) {
        this.title = title;
    }

    public String getContents() {
        return contents;
    }

    public void setContents(String contents) {
        this.contents = contents;
    }

    public String getCreated_at() {
        return created_at;
    }

    public void setCreated_at(String created_at) {
        this.created_at = created_at;
    }

    public String getImg_url() {
        return img_url;
    }

    public void setImg_url(String img_url) {
        this.img_url = img_url;
    }

    public String getHttp_url() {
        return http_url;
    }

    public void setHttp_url(String http_url) {
        this.http_url = http_url;
    }
}
