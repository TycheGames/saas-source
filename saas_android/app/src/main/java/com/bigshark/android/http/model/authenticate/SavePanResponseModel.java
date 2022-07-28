package com.bigshark.android.http.model.authenticate;

import java.io.Serializable;

public class SavePanResponseModel implements Serializable {

    private String reportId;// 报告id
    private String panCode;// pan卡的number

    public String getReportId() {
        return reportId;
    }

    public void setReportId(String reportId) {
        this.reportId = reportId;
    }

    public String getPanCode() {
        return panCode;
    }

    public void setPanCode(String panCode) {
        this.panCode = panCode;
    }
}
