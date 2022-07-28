package com.bigshark.android.vh.authenticate.addresscard.choose;

import android.support.annotation.NonNull;
import android.support.v7.widget.ListPopupWindow;
import android.view.Gravity;
import android.view.View;
import android.view.ViewGroup;
import android.widget.AdapterView;
import android.widget.ArrayAdapter;
import android.widget.TextView;

import com.bigshark.android.R;
import com.bigshark.android.activities.authenticate.AddressCardAuthActivity;
import com.bigshark.android.display.DisplayBaseVh;
import com.bigshark.android.http.model.authenticate.AddressCardAuthConfigResponseModel;
import com.bigshark.android.utils.StringConstant;

import java.util.ArrayList;
import java.util.List;

/**
 * 地址证明选择器
 */
public class AddressCardAuthTypeChooseVh extends DisplayBaseVh<View, AddressCardAuthConfigResponseModel> {

    private Callback mCallback;

    private TextView mSelectorText;

    private AddressCardAuthActivity mAddressCardAuthActivity;

    public AddressCardAuthTypeChooseVh(AddressCardAuthActivity activity, View root, @NonNull Callback mCallback) {
        super(activity);
        this.mAddressCardAuthActivity = activity;
        this.mCallback = mCallback;
        initViews(root);
    }

    @Override
    protected void bindViews() {
        super.bindViews();
        mSelectorText = findViewById(R.id.authenticate_address_address_select_txt);
    }

    @Override
    protected void bindListeners() {
        super.bindListeners();
        mSelectorText.setOnClickListener(new android.view.View.OnClickListener() {
            @Override
            public void onClick(View view) {
                final ListPopupWindow mPopup = new ListPopupWindow(mAddressCardAuthActivity);
                mPopup.setAdapter(new ArrayAdapter<>(mAddressCardAuthActivity, android.R.layout.simple_list_item_1, android.R.id.text1, listShows));
                mPopup.setWidth(ViewGroup.LayoutParams.WRAP_CONTENT);
                mPopup.setHeight(ViewGroup.LayoutParams.WRAP_CONTENT);
                mPopup.setModal(true);
                mPopup.setOnItemClickListener(new AdapterView.OnItemClickListener() {
                    @Override
                    public void onItemClick(AdapterView<?> parent, View view, int position, long id) {
                        mPopup.dismiss();
                        mSelectorText.setText(listShows.get(position));
                        mCallback.onClick(mData.getSelectorTypes().get(position));
                    }
                });
                mPopup.setDropDownGravity(Gravity.START);
                mPopup.setAnchorView(mSelectorText);
                mPopup.show();
            }
        });
    }

    private List<String> listShows = new ArrayList<>(4);

    @Override
    public void bindViewData(AddressCardAuthConfigResponseModel mData) {
        super.bindViewData(mData);

        for (Integer selectType : mData.getSelectorTypes()) {
            listShows.add(getShowText(selectType));
        }
    }

    private String getShowText(int selectType) {
        switch (selectType) {
            case StringConstant.ADDRESS_CARD_AUTH_RESPONSE_AADHAAR:
                return StringConstant.SHOW_TEXT_AADHAAR;
            case StringConstant.ADDRESS_CARD_AUTH_RESPONSE_VOTERID:
                return StringConstant.SHOW_TEXT_VOTER_ID;
            case StringConstant.ADDRESS_CARD_AUTH_RESPONSE_PASSPORT:
                return StringConstant.SHOW_TEXT_PASSPORT;
            case StringConstant.ADDRESS_CARD_AUTH_RESPONSE_DRIVER:
                return StringConstant.SHOW_TEXT_DRIVER_LICENSE;
            default:
                return null;
        }
    }


    public void refreshView(int selectedType) {
        mSelectorText.setText(getShowText(selectedType));
    }


    public interface Callback {
        void onClick(int selectedType);
    }
}
