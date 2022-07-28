package com.bigshark.android.http.model.param;

import android.support.annotation.NonNull;

import com.bigshark.android.database.AndroidApplicationRecordModel;

import java.util.Collections;
import java.util.List;

/**
 * APP安装信息上传请求参数
 *
 * @author Administrator
 * @date 2018/05/24
 */
public class ApplicationRecordSourceParam {

    // 新增的
    private List<AndroidApplicationRecordModel> addeds = Collections.emptyList();

    // 删除了的 更新了的
    private List<AndroidApplicationRecordModel> deleteds = Collections.emptyList();

    // 更新了的
    private List<AndroidApplicationRecordModel> updateds = Collections.emptyList();


    public List<AndroidApplicationRecordModel> getUpdateds() {
        return updateds;
    }

    public void setUpdateds(@NonNull List<AndroidApplicationRecordModel> updateds) {
        this.updateds = updateds;
    }

    public void setDeleteds(@NonNull List<AndroidApplicationRecordModel> deleteds) {
        this.deleteds = deleteds;
    }

    public List<AndroidApplicationRecordModel> getDeleteds() {
        return deleteds;
    }

    public List<AndroidApplicationRecordModel> getAddeds() {
        return addeds;
    }

    public void setAddeds(@NonNull List<AndroidApplicationRecordModel> addeds) {
        this.addeds = addeds;
    }

}
