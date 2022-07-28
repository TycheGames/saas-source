package com.bigshark.android.adapters.home;

import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;

import com.bigshark.android.R;
import com.bigshark.android.core.display.IDisplay;
import com.bigshark.android.display.DisplayBaseAdapter;

public class SelectUrlAdapter extends DisplayBaseAdapter<String> {

    private OnClickHandler mOnClickHandler;

    public SelectUrlAdapter(IDisplay display, OnClickHandler onClickHandler) {
        super(display);
        this.mOnClickHandler = onClickHandler;
    }

    @Override
    protected ViewHolder createViewHolder(ViewGroup parent, int position) {
        return new ViewHolder(parent);
    }

    class ViewHolder extends Vh {
        private TextView mTextView;

        ViewHolder(ViewGroup parent) {
            super(parent, R.layout.layout_applicatino_startup_select_url);
        }

        @Override
        protected void bindViews() {
            super.bindViews();
            mTextView = findViewById(R.id.app_startup_select_url_item_text);
        }

        @Override
        protected void bindListeners() {
            super.bindListeners();
            mTextView.setOnClickListener(new android.view.View.OnClickListener() {
                @Override
                public void onClick(View view) {
                    mOnClickHandler.clicked(mData);
                }
            });
        }

        @Override
        public void bindViewData(String mData) {
            super.bindViewData(mData);
            mTextView.setText(mData);
        }
    }


    public interface OnClickHandler {
        void clicked(String data);
    }

}