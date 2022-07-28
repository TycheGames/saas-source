package com.bigshark.android.utils.image.compress;

import android.graphics.Bitmap;

import com.bigshark.android.core.display.IDisplay;
import com.bigshark.android.core.utils.file.FileSizeUtil;
import com.nanchen.compresshelper.CompressHelper;
import com.socks.library.KLog;

import java.io.File;

/**
 * 图片压缩
 * Created by ytxu on 2019/10/2.
 */
public class ImageFileCompressUtils {

    /**
     * 获取目标图片路径：主要是压缩图片，防止图片过大
     */
    public static String compressImageFileAndReturnCompressedImageFilePath(IDisplay display, String sourceImageFilePath) {
        KLog.d("source`s path：" + sourceImageFilePath + ", size：" + FileSizeUtil.getAutoFileOrFilesSize(sourceImageFilePath));
        double fileSize = FileSizeUtil.getFileOrFilesSize(sourceImageFilePath, FileSizeUtil.SIZETYPE_KB);
        // 超过500KB的文件才要压缩
        if (fileSize < 300) {
            return sourceImageFilePath;
        }

        File compressedImageFile = compressImageFile(display, sourceImageFilePath);

        String compressedImagePath = compressedImageFile.getAbsolutePath();
        KLog.d("compressed`s path：" + compressedImagePath + ", size：" + FileSizeUtil.getAutoFileOrFilesSize(compressedImagePath));
        return compressedImagePath;
    }

    private static File compressImageFile(IDisplay display, String sourcePath) {
        File sourceFile = new File(sourcePath);
        return new CompressHelper.Builder(display.act())
                .setMaxWidth(1280)  // 默认最大宽度为720
                .setMaxHeight(1280) // 默认最大高度为960
                .setQuality(80)    // 默认压缩质量为80
//                            .setFileName(yourFileName) // 设置你需要修改的文件名
                .setCompressFormat(Bitmap.CompressFormat.JPEG) // 设置默认压缩为jpg格式
                .setDestinationDirectoryPath(sourceFile.getParentFile().getAbsolutePath())
                .setFileNamePrefix("target_") // 添加前缀防止压缩的文件路径与选取的文件路径相同，造成崩溃
                .build()
                .compressToFile(sourceFile);
    }
}
