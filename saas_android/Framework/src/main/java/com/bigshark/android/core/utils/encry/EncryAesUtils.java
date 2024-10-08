package com.bigshark.android.core.utils.encry;

import java.io.UnsupportedEncodingException;

import javax.crypto.Cipher;
import javax.crypto.spec.IvParameterSpec;
import javax.crypto.spec.SecretKeySpec;

/**
 * aes-256-cbc
 * Created by ytxu on 2019/9/23.
 */
public class EncryAesUtils {


    //<editor-fold desc="encrypt">

    /**
     * 算法/模式/填充
     **/
    private static final String CIPHER_MODE = "AES/CBC/PKCS5Padding";


    /**
     * 创建密钥
     **/
    private static SecretKeySpec createKey(String key) {
        byte[] data = null;
        if (key == null) {
            key = "";
        }
        StringBuilder sb = new StringBuilder(16);
        sb.append(key);
        while (sb.length() < 16) {
            sb.append("0");
        }
        if (sb.length() > 16) {
            sb.setLength(16);
        }


        try {
            data = sb.toString().getBytes("UTF-8");
        } catch (UnsupportedEncodingException e) {
            e.printStackTrace();
        }
        return new SecretKeySpec(data, "AES");
    }


    private static IvParameterSpec createIv(String password) {
        byte[] data = null;
        if (password == null) {
            password = "";
        }
        StringBuilder sb = new StringBuilder(16);
        sb.append(password);
        while (sb.length() < 16) {
            sb.append("0");
        }
        if (sb.length() > 16) {
            sb.setLength(16);
        }


        try {
            data = sb.toString().getBytes("UTF-8");
        } catch (UnsupportedEncodingException e) {
            e.printStackTrace();
        }
        return new IvParameterSpec(data);
    }


    /**
     * 加密字节数据
     **/
    public static byte[] encrypt(byte[] content, String password, String iv) {
        try {
            SecretKeySpec key = createKey(password);
            Cipher cipher = Cipher.getInstance(CIPHER_MODE);
            cipher.init(Cipher.ENCRYPT_MODE, key, createIv(iv));
            byte[] result = cipher.doFinal(content);
            return result;
        } catch (Exception e) {
            e.printStackTrace();
        }
        return null;
    }

    /**
     * 加密(结果为16进制字符串)
     **/
    public static String encrypt(String content, String password, String iv) {
        byte[] data = null;
        try {
            data = content.getBytes("UTF-8");
        } catch (Exception e) {
            e.printStackTrace();
        }
        data = encrypt(data, password, iv);
        String result = byte2hex(data);
        return result;
    }


    /**
     * 解密字节数组
     **/
    public static byte[] decrypt(byte[] content, String password, String iv) {
        try {
            SecretKeySpec key = createKey(password);
            Cipher cipher = Cipher.getInstance(CIPHER_MODE);
            cipher.init(Cipher.DECRYPT_MODE, key, createIv(iv));
            byte[] result = cipher.doFinal(content);
            return result;
        } catch (Exception e) {
            e.printStackTrace();
        }
        return null;
    }

    /**
     * 解密(输出结果为字符串)
     **/
    public static String decrypt(String content, String password, String iv) {
        byte[] data = null;
        try {
            data = hex2byte(content);
        } catch (Exception e) {
            e.printStackTrace();
        }
        data = decrypt(data, password, iv);
        if (data == null) {
            return null;
        }
        String result = null;
        try {
            result = new String(data, "UTF-8");
        } catch (UnsupportedEncodingException e) {
            e.printStackTrace();
        }
        return result;
    }


    /**
     * 字节数组转成16进制字符串
     **/
    public static String byte2hex(byte[] b) { // 一个字节的数，
        StringBuffer sb = new StringBuffer(b.length * 2);
        String tmp = "";
        for (int n = 0; n < b.length; n++) {
            // 整数转成十六进制表示
            tmp = (java.lang.Integer.toHexString(b[n] & 0XFF));
            if (tmp.length() == 1) {
                sb.append("0");
            }
            sb.append(tmp);
        }
        return sb.toString().toUpperCase(); // 转成大写
    }

    /**
     * 将hex字符串转换成字节数组
     **/
    private static byte[] hex2byte(String inputString) {
        if (inputString == null || inputString.length() < 2) {
            return new byte[0];
        }
        inputString = inputString.toLowerCase();
        int l = inputString.length() / 2;
        byte[] result = new byte[l];
        for (int i = 0; i < l; ++i) {
            String tmp = inputString.substring(2 * i, 2 * i + 2);
            result[i] = (byte) (Integer.parseInt(tmp, 16) & 0xFF);
        }
        return result;
    }

    //</editor-fold>

    //<editor-fold desc="encryptNew">

    //AES/CBC/PKCS5Padding默认对应PHP则为：AES-128-CBC
    private static final String CBC_PKCS5_PADDING = "AES/CBC/PKCS5Padding";

    private static final String AES = "AES";//AES 加密

    /**
     * @param key     这个key长度应该为16位，另外不要用KeyGenerator进行强化，否则无法跨平台
     * @param srcData
     * @return
     */
    public static String encryptNew(String srcData, String key, String iv) {
        try {
            Cipher cipher = Cipher.getInstance(CBC_PKCS5_PADDING);
            SecretKeySpec keyspec = new SecretKeySpec(key.getBytes(), AES);
            IvParameterSpec ivspec = new IvParameterSpec(iv.getBytes());
            cipher.init(Cipher.ENCRYPT_MODE, keyspec, ivspec);
            byte[] encrypted = cipher.doFinal(srcData.getBytes());
            //base64编码一下
            return android.util.Base64.encodeToString(encrypted, 32);
        } catch (Exception e) {
            e.printStackTrace();
            return null;
        }
    }

    public static String decryptNew(String encryptedData, String key, String iv) {
        try {

            byte[] encrypted1 = android.util.Base64.decode(encryptedData, 32);
            Cipher cipher = Cipher.getInstance(CBC_PKCS5_PADDING);
            SecretKeySpec keyspec = new SecretKeySpec(key.getBytes(), AES);
            IvParameterSpec ivspec = new IvParameterSpec(iv.getBytes());
            cipher.init(Cipher.DECRYPT_MODE, keyspec, ivspec);
            byte[] original = cipher.doFinal(encrypted1);
            //转换为字符串
            return new String(original);
        } catch (Exception e) {
            e.printStackTrace();
            return null;
        }
    }

    //</editor-fold>

}
