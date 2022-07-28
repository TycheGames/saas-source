package com.bigshark.android.http.model.contact;

import java.util.List;

public class ContactRelationshipConfig {

    private List<RelationItem> relativeList; // 亲属关系

    public List<RelationItem> getRelativeList() {
        return relativeList;
    }

    public void setRelativeList(List<RelationItem> relativeList) {
        this.relativeList = relativeList;
    }


    public static final class RelationItem {
        private int id;// 关系ID
        private String label;// 关系的显示名称

        public int getId() {
            return id;
        }

        public void setId(int id) {
            this.id = id;
        }

        public String getLabel() {
            return label;
        }

        public void setLabel(String label) {
            this.label = label;
        }
    }

}