package com.bigshark.android.core.utils.encry;

import java.security.InvalidKeyException;
import java.security.KeyFactory;
import java.security.NoSuchAlgorithmException;
import java.security.PublicKey;
import java.security.spec.InvalidKeySpecException;
import java.security.spec.X509EncodedKeySpec;

import javax.crypto.BadPaddingException;
import javax.crypto.Cipher;
import javax.crypto.IllegalBlockSizeException;
import javax.crypto.NoSuchPaddingException;

/**
 * rsa加密
 * Created by ytxu on 2019/9/24.
 * 参考：https://www.jianshu.com/p/8747d01a0450
 */
public class EncryRsaUtils {

    private static final String KEY_CONTENT = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAnrTxcVbBeZu7OsNA7GQP\n" +
            "RaDIm9/3qfv8rS0q5/U7ZVfkVqaYAodmdJU+d4KNn/9FRPK8Kxv0UwwcBgvwHkPb\n" +
            "q3U3pLuFMgcbgEqQk1HQQBgxuuNNcPfbrrGjz0O1ZQdgsn9zWsJIueuzDj5yS830\n" +
            "YuaOLiJ3umrMT+P3w4kgnCTENXqOQcLRm4vt9IVIQ+O0o2RGQU6TQoMK1z03rU9T\n" +
            "oAh2MhhBsitK8lYvOLfeD5jltlbLmg+ULBfOYEgpiQLlai60iPWRD4elb15Ko+Ph\n" +
            "vI75ZGBUxahWOaJKnBKH845mJjA4Iaw8/HsYZdV0oGnBhx0FmZuqsK1deQEhodFf\n" +
            "cwIDAQAB";

    private static PublicKey publicKey;

    public static String encrypt(String data) {
        try {
            if (publicKey == null) {
                publicKey = getPublicKey();
            }
            return encryptData(data.getBytes(), publicKey);
        } catch (NoSuchAlgorithmException | InvalidKeySpecException e) {
            e.printStackTrace();
        }
        return data;
    }


    private final static String ALGORITHM = "RSA";

    public static PublicKey getPublicKey() throws NoSuchAlgorithmException, InvalidKeySpecException {
        byte[] keyBytes = android.util.Base64.decode(KEY_CONTENT.getBytes(), 32);
        return getPublicKey(keyBytes);
    }

    public static PublicKey getPublicKey(byte[] keyBytes) throws NoSuchAlgorithmException, InvalidKeySpecException {
        X509EncodedKeySpec keySpec = new X509EncodedKeySpec(keyBytes);
        KeyFactory keyFactory = KeyFactory.getInstance(ALGORITHM);
        PublicKey publicKey = keyFactory.generatePublic(keySpec);
        return publicKey;
    }

//    public static PrivateKey getPrivateKey(byte[] keyBytes) throws NoSuchAlgorithmException, InvalidKeySpecException {
//        PKCS8EncodedKeySpec keySpec = new PKCS8EncodedKeySpec(keyBytes);
//        KeyFactory keyFactory = KeyFactory.getInstance(ALGORITHM);
//        PrivateKey privateKey = keyFactory.generatePrivate(keySpec);
//        return privateKey;
//    }


    private final static String TRANSFORMATION = "RSA/ECB/PKCS1Padding";

    /**
     * 加密
     */
    public static String encryptData(byte[] data, PublicKey publicKey) {
        try {
            Cipher cipher = Cipher.getInstance(TRANSFORMATION);
            cipher.init(Cipher.ENCRYPT_MODE, publicKey);
            byte[] encryptedBytes = cipher.doFinal(data);
            return android.util.Base64.encodeToString(encryptedBytes, android.util.Base64.NO_WRAP);
        } catch (NoSuchAlgorithmException e) {// 无此解密算法
            e.printStackTrace();
        } catch (NoSuchPaddingException e) {
            e.printStackTrace();
        } catch (InvalidKeyException e) {// 解密私钥非法,请检查
            e.printStackTrace();
        } catch (IllegalBlockSizeException e) {// 密文长度非法
            e.printStackTrace();
        } catch (BadPaddingException e) {// 密文数据已损坏
            e.printStackTrace();
        }
        return null;
    }

//    public static byte[] decryptData(byte[] encryptedData, PrivateKey privateKey) {
//        try {
//            Cipher cipher = Cipher.getInstance(TRANSFORMATION);
//            cipher.init(Cipher.DECRYPT_MODE, privateKey);
//            return cipher.doFinal(encryptedData);
//        } catch (NoSuchAlgorithmException e) {// 无此解密算法
//            e.printStackTrace();
//        } catch (NoSuchPaddingException e) {
//            e.printStackTrace();
//        } catch (InvalidKeyException e) {// 解密私钥非法,请检查
//            e.printStackTrace();
//        } catch (IllegalBlockSizeException e) {// 密文长度非法
//            e.printStackTrace();
//        } catch (BadPaddingException e) {// 密文数据已损坏
//            e.printStackTrace();
//        }
//        return null;
//    }
}
