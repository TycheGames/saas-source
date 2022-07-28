package com.bigshark.android.http.model.authenticate;

import android.support.annotation.IntDef;

import com.bigshark.android.utils.StringConstant;

import java.io.Serializable;
import java.lang.annotation.Retention;
import java.lang.annotation.RetentionPolicy;
import java.util.Arrays;
import java.util.List;

public class AddressCardAuthConfigResponseModel implements Serializable {

    private boolean showSelectorDefault = true; // 是否显示列表中默认的第一个方式
    private List<Integer> selectorTypes = Arrays.asList(1, 2, 3, 4); // 可选方式的列表


    public boolean isShowSelectorDefault() {
        return showSelectorDefault;
    }

    public void setShowSelectorDefault(boolean showSelectorDefault) {
        this.showSelectorDefault = showSelectorDefault;
    }

    public List<Integer> getSelectorTypes() {
        return selectorTypes;
    }

    public void setSelectorTypes(List<Integer> selectorTypes) {
        this.selectorTypes = selectorTypes;
    }


    /**
     * 所有项都不需认证
     */
    public static boolean goneAll(AddressCardAuthConfigResponseModel data) {
        return data.selectorTypes == null || data.selectorTypes.isEmpty();
    }

    /**
     * 只有一个选项
     */
    public static boolean onlyShow(AddressCardAuthConfigResponseModel data) {
        return data.selectorTypes.size() == 1;
    }

    public static boolean onlyShowVoterId(AddressCardAuthConfigResponseModel data) {
        return data.selectorTypes.size() == 1 && data.hasThisType(StringConstant.ADDRESS_CARD_AUTH_RESPONSE_VOTERID);
    }

    public static boolean onlyShowPassport(AddressCardAuthConfigResponseModel data) {
        return data.selectorTypes.size() == 1 && data.hasThisType(StringConstant.ADDRESS_CARD_AUTH_RESPONSE_PASSPORT);
    }

    public static boolean onlyShowDriver(AddressCardAuthConfigResponseModel data) {
        return data.selectorTypes.size() == 1 && data.hasThisType(StringConstant.ADDRESS_CARD_AUTH_RESPONSE_DRIVER);
    }

    public static boolean onlyShowAadhaar(AddressCardAuthConfigResponseModel data) {
        return data.selectorTypes.size() == 1 && data.hasThisType(StringConstant.ADDRESS_CARD_AUTH_RESPONSE_AADHAAR);
    }

    public boolean hasThisType(int type) {
        return selectorTypes != null && selectorTypes.contains(type);
    }


    public static boolean defaultIsVoterId(AddressCardAuthConfigResponseModel data) {
        return data.showSelectorDefault && data.selectorTypes.get(0) == StringConstant.ADDRESS_CARD_AUTH_RESPONSE_VOTERID;
    }

    public static boolean defaultIsPassport(AddressCardAuthConfigResponseModel data) {
        return data.showSelectorDefault && data.selectorTypes.get(0) == StringConstant.ADDRESS_CARD_AUTH_RESPONSE_PASSPORT;
    }

    public static boolean defaultIsDriver(AddressCardAuthConfigResponseModel data) {
        return data.showSelectorDefault && data.selectorTypes.get(0) == StringConstant.ADDRESS_CARD_AUTH_RESPONSE_DRIVER;
    }

    public static boolean defaultIsAadhaar(AddressCardAuthConfigResponseModel data) {
        return data.showSelectorDefault && data.selectorTypes.get(0) == StringConstant.ADDRESS_CARD_AUTH_RESPONSE_AADHAAR;
    }

    @IntDef({StringConstant.ADDRESS_CARD_AUTH_RESPONSE_VOTERID, StringConstant.ADDRESS_CARD_AUTH_RESPONSE_PASSPORT,
            StringConstant.ADDRESS_CARD_AUTH_RESPONSE_DRIVER, StringConstant.ADDRESS_CARD_AUTH_RESPONSE_AADHAAR})
    @Retention(RetentionPolicy.SOURCE)
    public @interface IType {
    }
}
