package com.bigshark.android.http.model.authenticate;

import java.io.Serializable;

public class SaveLivenessResponseModel implements Serializable {

    private String reportId;// 报告id

    public String getReportId() {
        return reportId;
    }

    public void setReportId(String reportId) {
        this.reportId = reportId;
    }

}
