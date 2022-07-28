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
-keepattributes EnclosingMethod
-keepattributes SourceFile,LineNumberTable
-keepclassmembers class fqcn.of.javascript.interface.for.webview {
   public *;
}


-keepclassmembers class * extends android.webkit.WebChromeClient{
   public void openFileChooser(...);
   public boolean onShowFileChooser(...);
   public boolean onJsAlert(...);
}

-keepattributes *JavascriptInterface*
-keep class android.webkit.JavascriptInterface {*;}

-keepattributes *Annotation*
-keepattributes Signature
-keepattributes InnerClasses

-keep public class * extends android.app.TabActivity
-keep public class * extends android.app.Fragment
-keep public class * extends android.app.Activity
-keep public class * extends android.app.Application
-keep public class * extends android.app.Service
-keep public class * extends android.content.BroadcastReceiver
-keep public class * extends android.content.ContentProvider
-keep public class * extends android.app.backup.BackupAgentHelper
-keep public class * extends android.preference.Preference
-keep public class * extends android.support.v4.**
-keep public class com.android.vending.licensing.ILicensingService

# 保持 native 方法不被混淆
-keepclasseswithmembernames class * { native <methods>;}

# 保持自定义控件类不被混淆
-keepclasseswithmembers class * {
 public <init>(android.content.Context, android.util.AttributeSet);
}

# 保持自定义控件类不被混淆
-keepclasseswithmembers class * {
 public <init>(android.content.Context, android.util.AttributeSet, int);
}

# 保持自定义控件类不被混淆
-keepclassmembers class * extends android.app.Activity {
 public void *(android.view.View);
}

# 保持枚举 enum 类不被混淆
-keepclassmembers enum * {
 public static **[] values();
 public static ** valueOf(java.lang.String);
}

# 保持 Parcelable 不被混淆
-keep class * implements android.os.Parcelable {
 public static final android.os.Parcelable$Creator *;
}





# eventBus
-keep class de.greenrobot.event.** {*;}
-keepclassmembers class ** {
    public void onEvent*(**);
    void onEvent*(**);
    public void onEventMainThread*(**);
    void onEventMainThread*(**);
}
#sweet dialog
-keep class com.bigshark.android.core.component.ui.dailog.** {*;}

#bean 防混淆
-keep class com.bigshark.android.common.repository.data.** { *; }
-keep class com.bigshark.android.common.repository.http.param.** { *; }
-keep class com.bigshark.android.common.repository.http.entity.** { *; }
-keep class com.com.bigshark.android.common.webview.bean.** { *; }

# afinal db防混淆
-keep class com.rupeefly.android.framework.db.** { *; }
-keep class com.bigshark.android.common.repository.db.bean.** { *; }

# okHttp3
-keep class okhttp3.** { *; }


-keepclassmembers class * extends android.webkit.WebChromeClient {
     public void openFileChooser(...);
}

-keepclassmembers class * {
    public void openFileChooser(android.webkit.ValueCallback,java.lang.String);
    public void openFileChooser(android.webkit.ValueCallback);
    public void openFileChooser(android.webkit.ValueCallback, java.lang.String, java.lang.String);
}

# okhttp
-keepattributes Signature
-keepattributes *Annotation*
-keep class okhttp3.** { *; }
-keep interface okhttp3.** { *; }
-dontwarn okhttp3.**

# okio
-keep class sun.misc.Unsafe { *; }
-dontwarn java.nio.file.*
-dontwarn org.codehaus.mojo.animal_sniffer.IgnoreJRERequirement
-dontwarn okio.**
