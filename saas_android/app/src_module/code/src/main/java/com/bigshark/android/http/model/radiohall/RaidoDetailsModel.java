package com.bigshark.android.http.model.radiohall;

import java.util.List;

/**
 * @创建者 wenqi
 * @创建时间 2019/5/27 20:58
 * @描述 广播详情
 */
public class RaidoDetailsModel {

    /**
     * nickname : xiao
     * sex : 2
     * avatar : http://3geng.oss-cn-hangzhou.aliyuncs.com/avatar/1/EDuDshDcKf.png
     * isVip : 0
     * isIdentify : 0
     * user_id : 1
     * id : 4
     * theme : 吃饭
     * hope : 看脸/土豪
     * city : 北京
     * date : 04/25
     * time_slot : 下午
     * supplement : /a\asd~sdweqwavghhkl<--\"'./
     * broadcast_img : 1
     * comment_status : 1
     * status : 1
     * uppermost : 0
     * created_at : 2019/04/25 15:39
     * enrolled_num : 0
     * click_good_num : 2
     * commented_num : 2
     * img : ["http://3geng.oss-cn-hangzhou.aliyuncs.com/broadcast/4/lH51CfWTJN.jpg","http://3geng.oss-cn-hangzhou.aliyuncs.com/broadcast/4/L0-K8NisTl.jpg","http://3geng.oss-cn-hangzhou.aliyuncs.com/broadcast/4/yKIzl0iO9U.jpg"]
     * is_oneself : 1
     * click_good : [{"user_id":"1","created_at":"2019/04/29 17:44","nickname":"xiao","avatar":"http://3geng.oss-cn-hangzhou.aliyuncs.com/avatar/1/EDuDshDcKf.png","sex":"2"},{"user_id":"100","created_at":"2019/04/29 17:44","nickname":"aaaa","avatar":"http://3geng.oss-cn-hangzhou.aliyuncs.com/avatar/1/EDuDshDcKf.png","sex":"2"}]
     * commented_list : [{"id":"1","content":"hahahahhahah","user_id":"100","created_at":"2019/04/29 11:25","nickname":"aaaa","sex":"2","avatar":"http://3geng.oss-cn-hangzhou.aliyuncs.com/avatar/1/EDuDshDcKf.png","reply":[{"comment_id":"1","content":"ccccc","nickname":"xiao"}]},{"id":"2","content":"嗯嗯嗯呢","user_id":"20","created_at":"2019/04/29 11:25","nickname":"bbbbb","sex":"2","avatar":"http://3geng.oss-cn-hangzhou.aliyuncs.com/avatar/1/EDuDshDcKf.png"}]
     */

    private String                  nickname;//广播用户昵称
    private int                     sex;//广播用户性别 1男  2女
    private String                  avatar;//广播用户头像
    private int                     isVip;//广播用户是否是VIP  1是
    private int                     isIdentify;//广播用户认证 1 是
    private String                  user_id;//广播用户id
    private String                  id;
    private String                  theme;//主题
    private String                  hope;//期望
    private String                  city;//城市
    private String                  date;//日期
    private String                  time_slot;//时间
    private String                  supplement;//补充说明
    private String                  created_at;//发布时间

    private int                     broadcast_img;//是否有图片 1 有
    private List<String>            img;//广播图片

    private int                     comment_status;//评论状态 1 可以评论 2 不可以
    private int                     is_enroll;//是否报名 1 是
    private int                     is_click_good;//是否点赞 1 是
    private int                     status;//广播状态 1发布 2结束
    private int                     uppermost;//是否置顶 1是
    private int                  enrolled_num;//报名数量
    private int                  click_good_num;//点赞数量
    private int                  commented_num;//评论数量
    private int                     is_oneself;//是否是自己发布的广播 1是 0 不是
    private List<ClickGoodBean>     click_good;//点赞列表
    private List<CommentedListBean> commented_list;//评论列表

    public int getIs_enroll() {
        return is_enroll;
    }

    public void setIs_enroll(int is_enroll) {
        this.is_enroll = is_enroll;
    }

    public int getIs_click_good() {
        return is_click_good;
    }

    public void setIs_click_good(int is_click_good) {
        this.is_click_good = is_click_good;
    }

    public String getNickname() {
        return nickname;
    }

    public void setNickname(String nickname) {
        this.nickname = nickname;
    }

    public int getSex() {
        return sex;
    }

    public void setSex(int sex) {
        this.sex = sex;
    }

    public String getAvatar() {
        return avatar;
    }

    public void setAvatar(String avatar) {
        this.avatar = avatar;
    }

    public int getIsVip() {
        return isVip;
    }

    public void setIsVip(int isVip) {
        this.isVip = isVip;
    }

    public int getIsIdentify() {
        return isIdentify;
    }

    public void setIsIdentify(int isIdentify) {
        this.isIdentify = isIdentify;
    }

    public String getUser_id() {
        return user_id;
    }

    public void setUser_id(String user_id) {
        this.user_id = user_id;
    }

    public String getId() {
        return id;
    }

    public void setId(String id) {
        this.id = id;
    }

    public String getTheme() {
        return theme;
    }

    public void setTheme(String theme) {
        this.theme = theme;
    }

    public String getHope() {
        return hope;
    }

    public void setHope(String hope) {
        this.hope = hope;
    }

    public String getCity() {
        return city;
    }

    public void setCity(String city) {
        this.city = city;
    }

    public String getDate() {
        return date;
    }

    public void setDate(String date) {
        this.date = date;
    }

    public String getTime_slot() {
        return time_slot;
    }

    public void setTime_slot(String time_slot) {
        this.time_slot = time_slot;
    }

    public String getSupplement() {
        return supplement;
    }

    public void setSupplement(String supplement) {
        this.supplement = supplement;
    }

    public int getBroadcast_img() {
        return broadcast_img;
    }

    public void setBroadcast_img(int broadcast_img) {
        this.broadcast_img = broadcast_img;
    }

    public int getComment_status() {
        return comment_status;
    }

    public void setComment_status(int comment_status) {
        this.comment_status = comment_status;
    }

    public int getStatus() {
        return status;
    }

    public void setStatus(int status) {
        this.status = status;
    }

    public int getUppermost() {
        return uppermost;
    }

    public void setUppermost(int uppermost) {
        this.uppermost = uppermost;
    }

    public String getCreated_at() {
        return created_at;
    }

    public void setCreated_at(String created_at) {
        this.created_at = created_at;
    }

    public int getEnrolled_num() {
        return enrolled_num;
    }

    public void setEnrolled_num(int enrolled_num) {
        this.enrolled_num = enrolled_num;
    }

    public int getClick_good_num() {
        return click_good_num;
    }

    public void setClick_good_num(int click_good_num) {
        this.click_good_num = click_good_num;
    }

    public int getCommented_num() {
        return commented_num;
    }

    public void setCommented_num(int commented_num) {
        this.commented_num = commented_num;
    }

    public int getIs_oneself() {
        return is_oneself;
    }

    public void setIs_oneself(int is_oneself) {
        this.is_oneself = is_oneself;
    }

    public List<String> getImg() {
        return img;
    }

    public void setImg(List<String> img) {
        this.img = img;
    }

    public List<ClickGoodBean> getClick_good() {
        return click_good;
    }

    public void setClick_good(List<ClickGoodBean> click_good) {
        this.click_good = click_good;
    }

    public List<CommentedListBean> getCommented_list() {
        return commented_list;
    }

    public void setCommented_list(List<CommentedListBean> commented_list) {
        this.commented_list = commented_list;
    }

    public static class ClickGoodBean {
        /**
         * user_id : 1
         * created_at : 2019/04/29 17:44
         * nickname : xiao
         * avatar : http://3geng.oss-cn-hangzhou.aliyuncs.com/avatar/1/EDuDshDcKf.png
         * sex : 2
         */

        private String user_id;//点赞用户id
        private String created_at;//点赞时间
        private String nickname;//昵称
        private String avatar;//头像
        private int sex;//性别 1男 2女

        public String getUser_id() {
            return user_id;
        }

        public void setUser_id(String user_id) {
            this.user_id = user_id;
        }

        public String getCreated_at() {
            return created_at;
        }

        public void setCreated_at(String created_at) {
            this.created_at = created_at;
        }

        public String getNickname() {
            return nickname;
        }

        public void setNickname(String nickname) {
            this.nickname = nickname;
        }

        public String getAvatar() {
            return avatar;
        }

        public void setAvatar(String avatar) {
            this.avatar = avatar;
        }

        public int getSex() {
            return sex;
        }

        public void setSex(int sex) {
            this.sex = sex;
        }
    }

    public static class CommentedListBean {
        /**
         * id : 1
         * content : hahahahhahah
         * user_id : 100
         * created_at : 2019/04/29 11:25
         * nickname : aaaa
         * sex : 2
         * avatar : http://3geng.oss-cn-hangzhou.aliyuncs.com/avatar/1/EDuDshDcKf.png
         * reply : [{"comment_id":"1","content":"ccccc","nickname":"xiao"}]
         */

        private String          id;//评论id
        private String          content;//评论内容
        private String          user_id;//评论用户id
        private String          created_at;//评论时间
        private String          nickname;//评论人昵称
        private int          sex;//评论人性别
        private String          avatar;//头像
        private List<ReplyBean> reply;//回复

        public String getId() {
            return id;
        }

        public void setId(String id) {
            this.id = id;
        }

        public String getContent() {
            return content;
        }

        public void setContent(String content) {
            this.content = content;
        }

        public String getUser_id() {
            return user_id;
        }

        public void setUser_id(String user_id) {
            this.user_id = user_id;
        }

        public String getCreated_at() {
            return created_at;
        }

        public void setCreated_at(String created_at) {
            this.created_at = created_at;
        }

        public String getNickname() {
            return nickname;
        }

        public void setNickname(String nickname) {
            this.nickname = nickname;
        }

        public int getSex() {
            return sex;
        }

        public void setSex(int sex) {
            this.sex = sex;
        }

        public String getAvatar() {
            return avatar;
        }

        public void setAvatar(String avatar) {
            this.avatar = avatar;
        }

        public List<ReplyBean> getReply() {
            return reply;
        }

        public void setReply(List<ReplyBean> reply) {
            this.reply = reply;
        }

        public static class ReplyBean {

            /**
             * comment_id : 1
             * content : ccccc
             * nickname : xiao
             */

            private String comment_id;
            private String content;//回复内容
            private String nickname;//昵称

            public String getComment_id() {
                return comment_id;
            }

            public void setComment_id(String comment_id) {
                this.comment_id = comment_id;
            }

            public String getContent() {
                return content;
            }

            public void setContent(String content) {
                this.content = content;
            }

            public String getNickname() {
                return nickname;
            }

            public void setNickname(String nickname) {
                this.nickname = nickname;
            }
        }
    }
}
