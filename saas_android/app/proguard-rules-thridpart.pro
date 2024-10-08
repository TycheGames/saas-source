# Add project specific ProGuard rules here.
# You can control the set of applied configuration files using the
# proguardFiles setting in build.gradle.
#
# For more details, see
#   http://developer.android.com/guide/developing/tools/proguard.html

# If your project uses WebView with JS, uncomment the following
# and specify the fully qualified class name to the JavaScript interface
# class:
#-keepclassmembers class fqcn.of.javascript.interface.for.webview {
#   public *;
#}

# Uncomment this to preserve the line number information for
# debugging stack traces.
#-keepattributes SourceFile,LineNumberTable

# If you keep the line number information, uncomment this to
# hide the original source file name.
#-renamesourcefileattribute SourceFile

# 第三方混淆规则


# ############### appsflyer
-keep class com.appsflyer.** { *; }
-dontwarn com.android.installreferrer


# ############### ocrfr
-keep class com.yuanding.idcardcamera.mode.**{*;}


# ############### df
-dontwarn com.deepfinch.**
-keep class com.deepfinch.** { *; }


# ############### bugly
-dontwarn com.tencent.bugly.**
-keep public class com.tencent.bugly.**{*;}


# 友盟
-keep class com.umeng.** {*;}
-keepclassmembers class * {
   public <init> (org.json.JSONObject);
}
-keepclassmembers enum * {
    public static **[] values();
    public static ** valueOf(java.lang.String);
}








