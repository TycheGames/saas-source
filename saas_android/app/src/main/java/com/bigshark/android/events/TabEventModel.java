package com.bigshark.android.events;


public class TabEventModel extends BaseDisplayEventModel {
    private int tag;

    public TabEventModel(int tag) {
        this.tag = tag;
    }

    public int getTag() {
        return tag;
    }
}

