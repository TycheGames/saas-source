package com.bigshark.android.adapters.authenticate;

import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;

import com.bigshark.android.R;
import com.bigshark.android.display.DisplayBaseAdapter;
import com.bigshark.android.http.model.contact.ContactRelationshipConfig;
import com.bigshark.android.display.DisplayBaseVh;
import com.bigshark.android.core.display.IDisplay;

public class EmergencyContactChooseListAdapter extends DisplayBaseAdapter<ContactRelationshipConfig.RelationItem> {
    private int checkPosition = 0;

    public EmergencyContactChooseListAdapter(IDisplay display) {
        super(display);
    }

    public int getCheckPosition() {
        return checkPosition;
    }

    public void setCheckPosition(int checkPosition) {
        this.checkPosition = checkPosition;
        notifyDataSetChanged();
    }

    @Override
    protected DisplayBaseVh.AdapterVh<ContactRelationshipConfig.RelationItem> createViewHolder(ViewGroup parent, int position) {
        return new ViewHolder(parent);
    }


    class ViewHolder extends Vh {

        private View mRadioView;
        private TextView mLabelText;

        public ViewHolder(ViewGroup parent) {
            super(parent, R.layout.layout_authenticate_emergency_contact_selects);
        }

        @Override
        protected void bindViews() {
            super.bindViews();
            mRadioView = findViewById(R.id.select_contact_relation_item_radio);
            mLabelText = findViewById(R.id.select_contact_relation_label);
        }

        @Override
        public void bindViewData(ContactRelationshipConfig.RelationItem relationBean) {
            super.bindViewData(relationBean);
            mRadioView.setBackgroundResource(mPosition == checkPosition ? R.drawable.shape_contact_radio_check : R.drawable.shape_contact_radio_default);
            mLabelText.setText(relationBean.getLabel());
        }

        @Override
        protected void bindListeners() {
            super.bindListeners();
            getRoot().setOnClickListener(new View.OnClickListener() {
                @Override
                public void onClick(View view) {
                    setCheckPosition(mPosition);
                }
            });
        }
    }
}
