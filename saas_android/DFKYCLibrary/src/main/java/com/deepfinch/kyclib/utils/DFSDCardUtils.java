package com.deepfinch.kyclib.utils;

import android.content.Context;
import android.os.Environment;

import java.io.BufferedOutputStream;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;

/**
 * Copyright (c) 2018-2019 DEEPFINCH Corporation. All rights reserved.
 */

public class DFSDCardUtils {

    public static String PATH_KEY_FILE = null;

    public static String getDownloadDir(){
        String storageState = Environment.getExternalStorageDirectory().getAbsolutePath();
        String downloadDir = storageState + File.separator + "Download";
        return downloadDir;
    }

    public static String getUserInfoDir(Context context) {
        String path = null;
        if (context == null) {
            return null;
        }
        String aadhaarSaveDir = "result";
        File dataDir = context.getApplicationContext().getExternalFilesDir(null);
        boolean isExternal = true;
        if (dataDir != null) {
            path = dataDir.getAbsolutePath();
            try {
                File tempFile = dataDir.createTempFile("temp_", null, dataDir);
//                Log.e(TAG, "ret: " + tempFile);
                tempFile.delete();
            } catch (IOException e) {
                e.printStackTrace();
                isExternal = false;
            }
        }
        if (!isExternal) {
            dataDir = context.getApplicationContext().getFilesDir();
            path = dataDir.getAbsolutePath() + File.separator + aadhaarSaveDir;
        }

        return path;
    }

    public static String copyAssetsToSD(Context context, String assetFileName, String destDirPath) {
        String resultPath = "";
        if (destDirPath != null) {
            File modelFile = new File(destDirPath + File.separator + assetFileName);
            if (modelFile.exists())
                modelFile.delete();
            resultPath = modelFile.getAbsolutePath();
            try {
                InputStream in = context.getApplicationContext().getAssets().open(assetFileName);
                OutputStream out = new FileOutputStream(modelFile);
                byte[] buffer = new byte[4096];
                int n;
                while ((n = in.read(buffer)) > 0) {
                    out.write(buffer, 0, n);
                }
                in.close();
                out.close();
            } catch (IOException e) {
                modelFile.delete();
            }
        }
        return resultPath;
    }

    public static String saveFile(byte[] bfile, String filePath,String fileName) {
        String fileAbsolutePath = null;
        if (bfile == null) {
            return fileAbsolutePath;
        }
        BufferedOutputStream bos = null;
        FileOutputStream fos = null;
        try {
            File dir = new File(filePath);
            if(!dir.exists()){
                dir.mkdirs();
            }
            File file = new File(filePath + File.separator + fileName);
            fileAbsolutePath = file.getAbsolutePath();
            fos = new FileOutputStream(file);
            bos = new BufferedOutputStream(fos);
            bos.write(bfile);
        } catch (Exception e) {
            e.printStackTrace();
            fileAbsolutePath =  null;
        } finally {
            if (bos != null) {
                try {
                    bos.close();
                } catch (IOException e1) {
                    e1.printStackTrace();
                }
            }
            if (fos != null) {
                try {
                    fos.close();
                } catch (IOException e1) {
                    e1.printStackTrace();
                }
            }
        }
        return fileAbsolutePath;
    }

    public static String saveFile(String content, String filePath,String fileName) {
        String fileAbsolutePath = null;
        if (content == null) {
            return fileAbsolutePath;
        }
        BufferedOutputStream bos = null;
        FileOutputStream fos = null;
        try {
            File dir = new File(filePath);
            if(!dir.exists()){
                dir.mkdirs();
            }
            File file = new File(filePath + File.separator + fileName);
            fileAbsolutePath = file.getAbsolutePath();
            fos = new FileOutputStream(file);
            bos = new BufferedOutputStream(fos);
            bos.write(content.getBytes());
        } catch (Exception e) {
            e.printStackTrace();
            fileAbsolutePath =  null;
        } finally {
            if (bos != null) {
                try {
                    bos.close();
                } catch (IOException e1) {
                    e1.printStackTrace();
                }
            }
            if (fos != null) {
                try {
                    fos.close();
                } catch (IOException e1) {
                    e1.printStackTrace();
                }
            }
        }
        return fileAbsolutePath;
    }

    public static void copyFileUsingFilePath(String sourceFilePath, String destPath, String imageName) {
        File directory = new File(destPath);
        if (!directory.exists()) {
            directory.mkdirs();
        }
        String destFilePath = destPath.concat(File.separator).concat(imageName);

        FileInputStream inputStream = null;
        OutputStream output = null;
        try {
            inputStream = new FileInputStream(sourceFilePath);
            output = new FileOutputStream(new File(destFilePath));
            byte[] buf = new byte[1024];
            int bytesRead;
            while ((bytesRead = inputStream.read(buf)) > 0) {
                output.write(buf, 0, bytesRead);
            }
        } catch (FileNotFoundException e) {
            e.printStackTrace();
        } catch (IOException e) {
            e.printStackTrace();
        } finally {
            try {
                if (inputStream != null) {
                    inputStream.close();
                }
                if (output != null) {
                    output.close();
                }
            } catch (IOException e) {
                e.printStackTrace();
            }
        }
    }

    public static String copyKYCFileToPath(String destDirPath){
        File file = new File(PATH_KEY_FILE);
        if (file != null) {
            copyFileUsingFilePath(PATH_KEY_FILE, destDirPath, file.getName());
        }
        return destDirPath;
    }
}
