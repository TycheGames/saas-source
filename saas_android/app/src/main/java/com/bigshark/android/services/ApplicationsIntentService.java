package com.bigshark.android.services;

import android.app.IntentService;
import android.content.Intent;
import android.content.pm.ApplicationInfo;
import android.content.pm.PackageInfo;
import android.support.annotation.NonNull;

import com.bigshark.android.database.AndroidApplicationRecordModel;
import com.bigshark.android.core.utils.ConvertUtils;
import com.bigshark.android.contexts.PersonalContext;
import com.bigshark.android.database.XutilDatabaseWrapper;
import com.bigshark.android.http.HttpConfig;
import com.bigshark.android.http.model.param.ApplicationRecordSourceParam;
import com.bigshark.android.http.model.param.UpdateDataType;
import com.bigshark.android.core.display.IDisplay;
import com.bigshark.android.http.xutils.CommonRequestParams;
import com.bigshark.android.http.xutils.CommonResponseCallback;
import com.bigshark.android.http.xutils.HttpSender;
import com.bigshark.android.mmkv.MmkvGroup;
import com.bigshark.android.utils.StringConstant;
import com.socks.library.KLog;
import com.tencent.bugly.crashreport.CrashReport;

import org.jetbrains.annotations.NotNull;
import org.xutils.db.sqlite.WhereBuilder;

import java.util.ArrayList;
import java.util.List;

/**
 * 上传APP安装信息
 *
 * @author Administrator
 * @date 2018/05/24
 */
public class ApplicationsIntentService extends IntentService {

    public static final long UPLOAD_CONTACTS_SERVICES_TIME = 1000 * 60 * 10;


    public ApplicationsIntentService() {
        super("ApplicationsIntentService");
    }

    private long createTime;

    @Override
    public void onCreate() {
        super.onCreate();
        createTime = System.currentTimeMillis();
    }

    @Override
    public void onDestroy() {
        if (System.currentTimeMillis() - createTime > UPLOAD_CONTACTS_SERVICES_TIME) {
            KLog.e("live time over 10 minutes");
            String uid = MmkvGroup.loginInfo().getUserName();
            CrashReport.postCatchedException(new Throwable(uid + "live time over 10 minutes"));
        }
        super.onDestroy();
    }

    @Override
    protected void onHandleIntent(Intent intent) {
        KLog.i("start");

        final String userId = PersonalContext.instance().getUserId();

        // getInstalledAndroidApplicationList
        List<AndroidApplicationRecordModel> installedAndroidApplicationList = new ArrayList<>();
        List<PackageInfo> packages = this.getPackageManager().getInstalledPackages(0);

        for (int i = 0; i < packages.size(); i++) {
            PackageInfo packageInfo = packages.get(i);
            AndroidApplicationRecordModel appInfo = getAndroidApplicationRecordModel(userId, packageInfo);
            installedAndroidApplicationList.add(appInfo);
        }

        // find installed apps from db
        XutilDatabaseWrapper xutilDatabaseWrapper = new XutilDatabaseWrapper();
        List<AndroidApplicationRecordModel> databaseAndroidApplicationList = xutilDatabaseWrapper.findAllByWhere(
                AndroidApplicationRecordModel.class,
                WhereBuilder.b(StringConstant.APPLICATION_RECORD_MODEL_TABLE_COLUMN_KEY_USER_ID, "=", userId)
        );


        // 获取更新数据
        ApplicationRecordSourceParam recordParam = getApplicationRecordParam(installedAndroidApplicationList, databaseAndroidApplicationList);

        KLog.d("added:" + recordParam.getAddeds().size()
                + ", updated:" + recordParam.getUpdateds().size()
                + ", deleted:" + recordParam.getDeleteds().size()
                + ", curs:" + installedAndroidApplicationList.size()
                + ", dbs:" + databaseAndroidApplicationList.size()
        );


        if (recordParam.getAddeds().isEmpty()
                && recordParam.getDeleteds().isEmpty()
                && recordParam.getUpdateds().isEmpty()
        ) {
            KLog.d("upload unchanged");
            return;
        }

        HttpSender.post(new CommonResponseCallback<String>(null) {

            @Override
            public CommonRequestParams createRequestParams() {
                String uploadInfoUrl = HttpConfig.getRealUrl(StringConstant.HTTP_DATA_UPDATE_INFO);
                CommonRequestParams requestParams = new CommonRequestParams(uploadInfoUrl);
                String data = ConvertUtils.toString(recordParam);
                requestParams.addBodyParameter("data", data);
                requestParams.addBodyParameter("type", UpdateDataType.TYPE_APPLICATION);
                return requestParams;
            }

            @Override
            public void handleUi(boolean isStart) {
            }

            @Override
            public void handleSuccess(String resultData, int resultCode, String resultMessage) {
                for (AndroidApplicationRecordModel deleted : recordParam.getDeleteds()) {
                    xutilDatabaseWrapper.delete(deleted);
                }
                for (AndroidApplicationRecordModel added : recordParam.getAddeds()) {
                    xutilDatabaseWrapper.save(added);
                }
                for (AndroidApplicationRecordModel updated : recordParam.getUpdateds()) {
                    xutilDatabaseWrapper.update(updated, StringConstant.APPLICATION_RECORD_MODEL_TABLE_COLUMN_KEY_ID + "=" + updated.getId());
                }
            }

            @Override
            public void handleFailed(int resultCode, String resultMessage) {
                for (AndroidApplicationRecordModel deleted : recordParam.getDeleteds()) {
                    xutilDatabaseWrapper.delete(deleted);
                }
                for (AndroidApplicationRecordModel added : recordParam.getAddeds()) {
                    xutilDatabaseWrapper.save(added);
                }
                for (AndroidApplicationRecordModel updated : recordParam.getUpdateds()) {
                    xutilDatabaseWrapper.update(updated, StringConstant.APPLICATION_RECORD_MODEL_TABLE_COLUMN_KEY_ID + "=" + updated.getId());
                }
            }

            @Override
            public void onCancelled(CancelledException cex) {
                KLog.d(cex);
            }
        });
    }

    @NotNull
    private AndroidApplicationRecordModel getAndroidApplicationRecordModel(String userId, PackageInfo packageInfo) {
        AndroidApplicationRecordModel appInfo = new AndroidApplicationRecordModel();
        appInfo.setUserId(userId);
        appInfo.setAppName(packageInfo.applicationInfo.loadLabel(this.getPackageManager()).toString());
        appInfo.setPackageName(packageInfo.packageName);
        appInfo.setVersionName(packageInfo.versionName);
        appInfo.setVersionCode(packageInfo.versionCode);
        appInfo.setFirstInstallTime(packageInfo.firstInstallTime);
        appInfo.setLastUpdateTime(packageInfo.lastUpdateTime);
        appInfo.setSysmtemApp((packageInfo.applicationInfo.flags & ApplicationInfo.FLAG_SYSTEM) != 0);
        return appInfo;
    }


    @NonNull
    private static ApplicationRecordSourceParam getApplicationRecordParam(
            List<AndroidApplicationRecordModel> currApps,
            List<AndroidApplicationRecordModel> dbApps
    ) {
        KLog.d("currApps:" + currApps.size() + ", dbApps:" + dbApps.size());
        // 0 第一次，全部是新的
        if (dbApps.isEmpty()) {
            ApplicationRecordSourceParam uploadDataParam = new ApplicationRecordSourceParam();
            uploadDataParam.setAddeds(currApps);
            return uploadDataParam;
        }

        ApplicationRecordSourceParam uploadDataParam = new ApplicationRecordSourceParam();

        // 1 判断删除了的：数据库中有，但是当前没有
        List<AndroidApplicationRecordModel> deleteds = new ArrayList<>(dbApps);
        deleteds.removeAll(currApps);
        uploadDataParam.setDeleteds(deleteds);

        // 2 判断新增的：当前有，但是数据库中没有
        List<AndroidApplicationRecordModel> addeds = new ArrayList<>(currApps);
        addeds.removeAll(dbApps);
        uploadDataParam.setAddeds(addeds);

        // 3 判断更新了的：数据库与当前都有，但是更新时间不一样
        List<AndroidApplicationRecordModel> dbAppsStandin = new ArrayList<>(dbApps);
        List<AndroidApplicationRecordModel> currAppsStandin = new ArrayList<>(currApps);
        // 删除掉交集之外的数据
        dbAppsStandin.retainAll(currApps);
        currAppsStandin.retainAll(dbApps);

        List<AndroidApplicationRecordModel> updateds = new ArrayList<>();
        for (AndroidApplicationRecordModel dbApp : dbAppsStandin) {
            int currAppIndex = currAppsStandin.indexOf(dbApp);
            AndroidApplicationRecordModel currApp = currAppsStandin.get(currAppIndex);
            // 更新过
            if (currApp.getFirstInstallTime() != dbApp.getFirstInstallTime() || currApp.getLastUpdateTime() != dbApp.getLastUpdateTime()) {
                currApp.setId(dbApp.getId());
                updateds.add(currApp);
            }
        }
        uploadDataParam.setUpdateds(updateds);

        return uploadDataParam;
    }


    public static void report(IDisplay display) {
        Intent intent = new Intent(display.act(), ApplicationsIntentService.class);
        display.startService(intent);
    }
}
