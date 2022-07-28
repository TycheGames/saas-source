package com.bigshark.android.database;

import com.bigshark.android.utils.StringConstant;

import org.xutils.db.annotation.Column;
import org.xutils.db.annotation.Table;

@Table(name = StringConstant.IMAGE_DIALOG_RECORD_MODEL_TABLE_NAME)
public class ImageDialogRecordModel {

    @Column(name = StringConstant.IMAGE_DIALOG_RECORD_MODEL_TABLE_COLUMN_KEY_ID, isId = true, autoGen = true)
    private int id;//记录id
    @Column(name = StringConstant.IMAGE_DIALOG_RECORD_MODEL_TABLE_COLUMN_KEY_UNIQUE_ID)
    private String uniId;//  弹框的唯一id
    @Column(name = StringConstant.IMAGE_DIALOG_RECORD_MODEL_TABLE_COLUMN_KEY_TOTAL_SIZE)
    private int totalSize;// 弹框可以展示的总次数(若为0或负数展示次数则无限制，否则展示次数有限制)
    @Column(name = StringConstant.IMAGE_DIALOG_RECORD_MODEL_TABLE_COLUMN_KEY_CURRENT_SHOW_SIZE)
    private int currentShowSize;// 当前显示的次数


    public int getId() {
        return id;
    }

    public void setId(int id) {
        this.id = id;
    }

    public String getUniId() {
        return uniId;
    }

    public void setUniId(String uniId) {
        this.uniId = uniId;
    }

    public int getTotalSize() {
        return totalSize;
    }

    public void setTotalSize(int totalSize) {
        this.totalSize = totalSize;
    }

    public int getCurrentShowSize() {
        return currentShowSize;
    }

    public void setCurrentShowSize(int currentShowSize) {
        this.currentShowSize = currentShowSize;
    }
}