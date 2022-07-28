package com.bigshark.android.common.browser;

import android.Manifest;
import android.app.Activity;
import android.content.ContentResolver;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.database.Cursor;
import android.net.Uri;
import android.os.Build;
import android.os.Environment;
import android.provider.MediaStore;
import android.support.annotation.NonNull;
import android.support.v7.app.AlertDialog;
import android.text.TextUtils;
import android.webkit.ValueCallback;

import com.bigshark.android.core.common.RequestCodeType;
import com.bigshark.android.core.display.IDisplay;
import com.bigshark.android.core.permission.PermissionListener;
import com.bigshark.android.core.permission.PermissionTipInfo;
import com.bigshark.android.core.permission.PermissionsUtil;
import com.bigshark.android.core.utils.LoadingDialogUtils;
import com.bigshark.android.core.utils.image.ImageBitmapUtils;
import com.socks.library.KLog;

import java.io.File;

/**
 * Created by User on 2018/3/19.
 * 网易客服
 */

public class BrowserFileChooserHandler {
    private final IDisplay display;

    private ValueCallback<Uri> mUploadMessage;
    private ValueCallback<Uri[]> mUploadMessageArray;

    private static final String PATH = Environment.getExternalStorageDirectory() + "/DCIM";
    private String imageName;

    public BrowserFileChooserHandler(IDisplay display) {
        this.display = display;
    }

    // For Android < 3.0
    public void openFileChooser(ValueCallback<Uri> uploadMsg) {
        openFileChooser(uploadMsg, "image/*");
    }

    // For Android >=3.0
    public void openFileChooser(ValueCallback<Uri> uploadMsg, String acceptType) {
        if (acceptType.equals("image/*")) {
            if (mUploadMessage != null) {
                mUploadMessage.onReceiveValue(null);
                return;
            }
            mUploadMessage = uploadMsg;
            openWriteFile();
        } else {
            onReceiveValue();
        }
    }

    // For Android  >= 4.1.1
    public void openFileChooser(ValueCallback<Uri> uploadMsg, String acceptType, String capture) {
        openFileChooser(uploadMsg, acceptType);
    }

    // For Android  >= 5.0
    public void onShowFileChooser(ValueCallback<Uri[]> filePathCallback) {
        if (mUploadMessageArray != null) {
            mUploadMessageArray.onReceiveValue(null);
        }
        mUploadMessageArray = filePathCallback;
        openWriteFile();
    }

    private void openWriteFile() {
        PermissionTipInfo tip = PermissionTipInfo.getTip("Storage Permissions");
        PermissionsUtil.requestPermission(display.act(), new PermissionListener() {
            @Override
            public void permissionGranted(@NonNull String[] permission) {
                selectImage();
            }

            @Override
            public void permissionDenied(@NonNull String[] permission) {
                onReceiveValue();
                display.showToast("Please enable Storage permissions");
            }
        }, tip, Manifest.permission.WRITE_EXTERNAL_STORAGE, Manifest.permission.READ_EXTERNAL_STORAGE);
    }

    private void selectImage() {
        new AlertDialog.Builder(display.act())
                .setCancelable(false)
                .setItems(new String[]{"Camera", "Select from the album", "Cancel"}, new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        switch (which) {
                            case 0:
                                PermissionTipInfo tip = PermissionTipInfo.getTip("Camera Permissions");
                                PermissionsUtil.requestPermission(display.act(), new PermissionListener() {
                                    @Override
                                    public void permissionGranted(@NonNull String[] permission) {
                                        openCamera();
                                    }

                                    @Override
                                    public void permissionDenied(@NonNull String[] permission) {
                                        onReceiveValue();
                                        display.showToast("Please enable camera permissions");
                                    }
                                }, tip, Manifest.permission.CAMERA);
                                break;
                            case 1:
                                openAlbum();
                                break;
                            case 2:// cancel
                                onReceiveValue();
                                break;
                            default:
                                break;
                        }
                    }
                })
                .show();
    }

    private void openAlbum() {
        if (!hasSDcard()) {
            return;
        }

        Intent intent = new Intent();
        intent.setType("image/*");
        intent.setAction(Intent.ACTION_PICK);
        //使用以上这种模式，并添加以上两句
        intent.setData(MediaStore.Images.Media.EXTERNAL_CONTENT_URI);
        display.startActivityForResult(intent, RequestCodeType.WEBVIEW_OPEN_FILE_CHOOSER_CHOOSE);
    }

    private boolean hasSDcard() {
        boolean flag = Environment.getExternalStorageState().equals(Environment.MEDIA_MOUNTED);
        if (!flag) {
            display.showToast("Please insert the memory card before using this function");
            onReceiveValue();
        }
        return flag;
    }

    private void openCamera() {
        if (!hasSDcard()) {
            return;
        }

        Intent intent = new Intent(MediaStore.ACTION_IMAGE_CAPTURE);
        imageName = System.currentTimeMillis() + ".png";
        File file = new File(PATH);
        if (!file.exists()) {
            file.mkdirs();
        }
        intent.putExtra(MediaStore.EXTRA_OUTPUT, Uri.fromFile(new File(PATH, imageName)));
        display.startActivityForResult(intent, RequestCodeType.WEBVIEW_OPEN_FILE_CHOOSER_CAMERA);
    }

    private void handleFile(final File file) {
        if (file.isFile()) {
            LoadingDialogUtils.showLoadingDialog(display.act());
            new Thread(new Runnable() {
                @Override
                public void run() {
                    final File result = ImageBitmapUtils.getCompressImageFile(file);
                    display.act().runOnUiThread(new Runnable() {
                        @Override
                        public void run() {
                            uploadImgPath(result);
                            LoadingDialogUtils.hideLoadingDialog();
                        }
                    });
                }
            }).start();
        } else {
            onReceiveValue();
        }

    }

    public void uploadImgPath(File file) {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.LOLLIPOP) {
            if (null == mUploadMessageArray) {
                return;
            }
            Uri uri = Uri.fromFile(file);
            Uri[] uriArray = new Uri[]{uri};
            mUploadMessageArray.onReceiveValue(uriArray);
            mUploadMessageArray = null;
        } else {
            if (null == mUploadMessage) {
                return;
            }
            Uri uri = Uri.fromFile(file);
            mUploadMessage.onReceiveValue(uri);
            mUploadMessage = null;
        }
    }

    public void onReceiveValue() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.LOLLIPOP) {
            if (mUploadMessageArray != null) {
                mUploadMessageArray.onReceiveValue(null);
                mUploadMessageArray = null;
            }
        } else {
            if (mUploadMessage != null) {
                mUploadMessage.onReceiveValue(null);
                mUploadMessage = null;
            }
        }
    }

    public void onActivityResult(int requestCode, int resultCode, Intent intent) {
        if (resultCode != Activity.RESULT_OK) {
            onReceiveValue();
            return;
        }
        switch (requestCode) {
            case RequestCodeType.WEBVIEW_OPEN_FILE_CHOOSER_CAMERA:
                if (TextUtils.isEmpty(imageName)) {
                    KLog.d("imageName is null");
//                    CrashReport.postCatchedException(new Throwable("imageName is null, user" + UserDataStore.get().getUid()));
                    break;
                }
                File fileCamera = new File(PATH, imageName);
                handleFile(fileCamera);
                break;
            case RequestCodeType.WEBVIEW_OPEN_FILE_CHOOSER_CHOOSE:
                Uri uri = intent.getData();
                String absolutePath = getAbsolutePath(display.act(), uri);
                File fileAlbum = new File(absolutePath);
                handleFile(fileAlbum);
                break;
            default:
                break;
        }
    }

    public String getAbsolutePath(final Context context, final Uri uri) {
        if (null == uri) {
            return null;
        }
        final String scheme = uri.getScheme();
        String data = null;
        if (scheme == null) {
            data = uri.getPath();
        } else if (ContentResolver.SCHEME_FILE.equals(scheme)) {
            data = uri.getPath();
        } else if (ContentResolver.SCHEME_CONTENT.equals(scheme)) {
            Cursor cursor = context.getContentResolver().query(uri,
                    new String[]{MediaStore.Images.ImageColumns.DATA},
                    null, null, null);
            if (null != cursor) {
                if (cursor.moveToFirst()) {
                    int index = cursor.getColumnIndex(
                            MediaStore.Images.ImageColumns.DATA);
                    if (index > -1) {
                        data = cursor.getString(index);
                    }
                }
                cursor.close();
            }
        }
        return data;
    }

}
