package com.bigshark.android.database;

import com.bigshark.android.utils.StringConstant;

import org.xutils.db.annotation.Column;
import org.xutils.db.annotation.Table;

import java.io.Serializable;
import java.util.Objects;

/**
 * APP安装信息
 * <p>
 * 使用packageName作为数据唯一性判断
 *
 * @author Administrator
 * @date 2018/05/24
 */
@Table(name = StringConstant.APPLICATION_RECORD_MODEL_TABLE_NAME)
public final class AndroidApplicationRecordModel implements Serializable {


    @Column(name = StringConstant.APPLICATION_RECORD_MODEL_TABLE_COLUMN_KEY_ID, isId = true, autoGen = true)
    private int id;// 记录id

    @Column(name = StringConstant.APPLICATION_RECORD_MODEL_TABLE_COLUMN_KEY_USER_ID)
    private String userId;// 用户ID

    @Column(name = StringConstant.APPLICATION_RECORD_MODEL_TABLE_COLUMN_KEY_APP_NAME)
    private String appName;// app名称
    @Column(name = StringConstant.APPLICATION_RECORD_MODEL_TABLE_COLUMN_KEY_PACKAGE_NAME)
    private String packageName;// app的包名，能保证唯一性，appName，可以改变
    @Column(name = StringConstant.APPLICATION_RECORD_MODEL_TABLE_COLUMN_KEY_IS_SYSMTEM_APP)
    private boolean isSysmtemApp = false;// 是否为系统应用

    @Column(name = StringConstant.APPLICATION_RECORD_MODEL_TABLE_COLUMN_KEY_VERSION_NAME)
    private String versionName;// 版本名称，用户看到的
    @Column(name = StringConstant.APPLICATION_RECORD_MODEL_TABLE_COLUMN_KEY_VERSION_CODE)
    private int versionCode;// 版本号，开发者控制版本使用的

    @Column(name = StringConstant.APPLICATION_RECORD_MODEL_TABLE_COLUMN_KEY_FIRST_INSTALL_TIME)
    private long firstInstallTime;// 安装APP的时间戳，毫秒值
    @Column(name = StringConstant.APPLICATION_RECORD_MODEL_TABLE_COLUMN_KEY_LAST_UPDATE_TIME)
    private long lastUpdateTime;// 最后更新APP的时间戳，毫秒值


    public AndroidApplicationRecordModel() {
    }

    public int getId() {
        return id;
    }

    public void setId(int id) {
        this.id = id;
    }

    public String getUserId() {
        return userId;
    }

    public void setUserId(String userId) {
        this.userId = userId;
    }

    public String getAppName() {
        return appName;
    }

    public void setAppName(String appName) {
        this.appName = appName;
    }

    public String getPackageName() {
        return packageName;
    }

    public void setPackageName(String packageName) {
        this.packageName = packageName;
    }

    public boolean isSysmtemApp() {
        return isSysmtemApp;
    }

    public void setSysmtemApp(boolean sysmtemApp) {
        isSysmtemApp = sysmtemApp;
    }

    public String getVersionName() {
        return versionName;
    }

    public void setVersionName(String versionName) {
        this.versionName = versionName;
    }

    public int getVersionCode() {
        return versionCode;
    }

    public void setVersionCode(int versionCode) {
        this.versionCode = versionCode;
    }

    public long getFirstInstallTime() {
        return firstInstallTime;
    }

    public void setFirstInstallTime(long firstInstallTime) {
        this.firstInstallTime = firstInstallTime;
    }

    public long getLastUpdateTime() {
        return lastUpdateTime;
    }

    public void setLastUpdateTime(long lastUpdateTime) {
        this.lastUpdateTime = lastUpdateTime;
    }


//    @Override
//    public boolean equals(Object o) {
//        if (this == o) {
//            return true;
//        }
//        if (o == null || getClass() != o.getClass()) {
//            return false;
//        }
//        AndroidApplicationRecordModel that = (AndroidApplicationRecordModel) o;
//        return Objects.equals(packageName, that.packageName);
//    }
//
//    @Override
//    public int hashCode() {
//        return packageName != null ? packageName.hashCode() : 0;
//    }


    @Override
    public boolean equals(Object o) {
        if (this == o) {
            return true;
        }
        if (o == null || getClass() != o.getClass()) {
            return false;
        }
        AndroidApplicationRecordModel that = (AndroidApplicationRecordModel) o;
        return Objects.equals(packageName, that.packageName);
    }

    @Override
    public int hashCode() {
        return Objects.hash(packageName);
    }

    @Override
    public String toString() {
        return "AndroidApplicationRecordModel{" +
                "id=" + id +
                ", userId=" + userId +

                ", appName='" + appName + '\'' +
                ", packageName='" + packageName + '\'' +
                ", isSysmtemApp=" + isSysmtemApp +

                ", versionName='" + versionName + '\'' +
                ", versionCode=" + versionCode +

                ", firstInstallTime=" + firstInstallTime +
                ", lastUpdateTime=" + lastUpdateTime +
                '}';
    }
}