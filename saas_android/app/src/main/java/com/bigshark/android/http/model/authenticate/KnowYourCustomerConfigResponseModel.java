package com.bigshark.android.http.model.authenticate;

public class KnowYourCustomerConfigResponseModel {

    private double fr = 0.98;// accuauth的活体验证的参数值，一般是大于0.98则为有问题的

    public double getFr() {
        return fr;
    }

    public void setFr(double fr) {
        this.fr = fr;
    }
}
