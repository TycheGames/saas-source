package com.bigshark.android.events;

public class RefreshDisplayEventModel extends BaseDisplayEventModel {

    private int type = EVENT_DEFAULT;

    public RefreshDisplayEventModel(int type) {
        this.type = type;
    }

    public int getType() {
        return type;
    }

}

