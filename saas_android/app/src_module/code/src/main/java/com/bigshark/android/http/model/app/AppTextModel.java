package com.bigshark.android.http.model.app;

import java.util.List;

/**
 * @创建者 wenqi
 * @创建时间 2019/5/22 20:30
 * @描述 没有去爱过怎知她美 没有你爱我问怎可脱俗
 */
public class AppTextModel {

    private List<String> affection;//情感状况 ["单身","热恋中","未婚","已婚","离异","丧偶"]
    private List<String> career;//职业 ["投资","IT","公务员","学生","模特","主播","秘书","老师","少妇","设计师","健身教练","运动员","银行","高管","女警","男警","其他"]
    private List<String> dateCondition;//类型 ["高颜值","高个子","收费约会","跟钱没关系","我要帅哥","我要好玩","我需要关心","看情况"]
    private List<String> dateProgram;//约会目的 ["吃饭","喝酒","看电影","吃吃喝喝","旅游","夜蒲聚会","应酬","内容不限"]
    private List<String> language;//语言  ["普通话","四川话","本地话","广东话"]
    private List<String> selfIntro;//["成功人士","有房有车","小清新","温柔","酒神","厨艺好","文艺","好身材","游泳","电影","土豪","公子哥","女神","学霸","女神经"
    // ,"绿茶","齐家少爷","肌肉","小鲜肉","暖男","土豪大气","高频持久","颜王","超频小哥","费油小姐","4.0上车"]
    private List<String> style;//风格" ["黑丝","长筒袜","吊带袜","蕾丝","超短裙","吊带裙","雪纺裙","连衣裙","清纯","可爱","角色扮演"]

    public List<String> getAffection() {
        return affection;
    }

    public void setAffection(List<String> affection) {
        this.affection = affection;
    }

    public List<String> getCareer() {
        return career;
    }

    public void setCareer(List<String> career) {
        this.career = career;
    }

    public List<String> getDateCondition() {
        return dateCondition;
    }

    public void setDateCondition(List<String> dateCondition) {
        this.dateCondition = dateCondition;
    }

    public List<String> getDateProgram() {
        return dateProgram;
    }

    public void setDateProgram(List<String> dateProgram) {
        this.dateProgram = dateProgram;
    }

    public List<String> getLanguage() {
        return language;
    }

    public void setLanguage(List<String> language) {
        this.language = language;
    }

    public List<String> getSelfIntro() {
        return selfIntro;
    }

    public void setSelfIntro(List<String> selfIntro) {
        this.selfIntro = selfIntro;
    }

    public List<String> getStyle() {
        return style;
    }

    public void setStyle(List<String> style) {
        this.style = style;
    }
}
