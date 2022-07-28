package com.bigshark.android.core.component.browser;

/**
 * @author Administrator
 * @date 2017/12/27
 */
public class BrowserConfig {

    public static final String JAVASCRIPT_NATIVE_METHOD = "nativeMethod";


    // ************** data **************
    private ProxyCreater proxyCreater;

    private String title;
    private String url;

    private boolean isPush;
    private String jumpToHome;

    private String authMethod;


    private BrowserConfig() {
    }

    public ProxyCreater getProxyCreater() {
        return proxyCreater;
    }

    public String getTitle() {
        return title;
    }

    public String getUrl() {
        return url;
    }

    public boolean isPush() {
        return isPush;
    }

    public String getJumpToHome() {
        return jumpToHome;
    }

    public String getAuthMethod() {
        return authMethod;
    }


    public static final class Builder {
        private final BrowserConfig config;

        public Builder() {
            config = new BrowserConfig();
        }

        public Builder setProxyCreater(ProxyCreater proxyCreater) {
            config.proxyCreater = proxyCreater;
            return this;
        }

        public Builder setTitle(String title) {
            config.title = title;
            return this;
        }

        public Builder setUrl(String url) {
            config.url = url;
            return this;
        }

        public Builder setIsPush(boolean isPush) {
            config.isPush = isPush;
            return this;
        }

        public Builder setJumpToHome(String jumpToHome) {
            config.jumpToHome = jumpToHome;
            return this;
        }

        public Builder setAuthMethod(String authMethod) {
            config.authMethod = authMethod;
            return this;
        }

        public BrowserConfig build() {
            return config;
        }
    }


    /**
     * 代理生成器，用于代码下移时，对应功能使用了上层代码，从而对功能进行接口化
     */
    public interface ProxyCreater {
        /**
         * 创建js的本地对象，一个页面(display, webview)只有一个，只能调用一次
         */
        INativeJavascriptInterfaceObj createNativeMethod();

        /**
         * 给window.open()，调用的
         */
        IBrowserWindowWebView createWindowWebView();
    }
}
