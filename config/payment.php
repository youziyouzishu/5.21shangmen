<?php

use Yansongda\Pay\Pay;

return [
    'alipay' => [
        'default' => [
            // 必填-支付宝分配的 app_id
            'app_id' => '2021005138609956',
            // 必填-应用私钥 字符串或路径
            // 在 https://open.alipay.com/develop/manage 《应用详情->开发设置->接口加签方式》中设置
            'app_secret_cert' => 'MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQDFVq2QLp/JP/3QfTGA6BpFTnFv9TcUBLOk7mg8ATbJ2g6tTjbNspMhNbG6HhLFC7BoDBuScHPAHhDaY1+UnBV9efI4qAilFaJmk0EI2y3n+82O/Wv9zRsiuyJ58EZInyQuSauuSnRS6/d5N29jpro+ZhaYLDSVQT0Hh20ln+64g9dDwy31U2d9uSeH4YvIck/pXwSP1g0xkcLgbB3F8FO8RfC+kVkz6eMAOZrYkuyAsXRDYNCCRz132HuCo6q+nYRMdjCbjQ+MbMslofl1jnkTL0Q0s+VHE7DD4jJLAL1xuUF+qdPVDSGNzvAmaL/mUdhKwIjRy1ASMGQqEeBR9GgJAgMBAAECggEAXi8rcWg/TP8ktdA6w0qD11ZEH+pKWlyQHQTIbPMrYhWX8bElyPEU9cG5zJmz3aXhkRdORYs0Q5pV8tWA9d21CAkeyVmxnpD/zLM1U/PAKjts9XUQiLbO37mvD8HU2a5Sx2DT9sCEWltS5uDFYiteMo/o8sIOaroTVbeWKWH2vsse4upuk7FcFRbgBWXaHM9p4hGQ+K9dd6sAo8xQhBAo+Hxeu4vyRL7uQlcCXo953o4Yc+bz+bC2YO+BrfTmaBCqT0AmbVIniRAjrOE6ryOZTsWEMEGP/HhfG5JN4LjLxrSlvYJPebPzbPU4QJlye4VvX4/mhh1ESDtqWFKCx9OOLQKBgQDxooJOqSDf5Q3WiWjENMxUqqKd068MqCRnPQ7vdtz7cvLPaEquf3305L5GERCbQQtfU24lZV00AaCpOBRVxAO+grd7TAUerqvThnrlofRVyJVNIgxmro2+2T//g9F1cxcBq0QIpsqw3h1NPmFbVks+TH1Z1UB0sToTyCVnCBE85wKBgQDREgP566V/cv8qKjwe7GmATZyMGlgKs5sS0rgl4lDPOZ/O4TvGXt5On7LZLpWvtGABwMk7VVDEXR4PYlCBZ3X6qPZwQUb3UFJdiKNcd+S+RK1nRlUoTJXb4nQ5DieCmV1bh1kEmBz2dg3U+uNhejLbAICyYxH2PGN29S/Y0ooljwKBgQDoMyRJdtlyjmiH2y/kLGCWlAsE46D55rJkyxYJJA4jTEjs6umpWjauGQqHxmpQ52aISmI0aX3xNgcv1gYkjOKUNoUfZOQeoffYowiClul3bfObVbbZDbCEz7xiTEgCN4DKR8xvC+kHocpCqklgPkK8MUDcDMON36lRzSm0Itp9zQKBgQCrM1dyNhcFsINmYcNthyY1XFiPGZty1/Rqi4cj7plBt+w3GpU6McFVHMz1r4H/qXLcvKOMJKbIxX+yFU3bSX+AzQ9QiQO1zV5FHXi4Za04aUCdyyg8ocotjtihSAful+us4b5xVqaQ48cb2n6L+ebfEyBP83Rn8Ld6V/jgUjex3QKBgE2i/K4p2qtLztCyphKE1NIZtynj5+uoeOMk2buUQddzEUPnpJcfGqqTI9HliOxRqJziecevSC3WTjFqc0jezThN9yrhgugQdF39pPdc4h4IlEIN45JAsfCObqHbXaJ/eDUdWrzg3ZSytUf+6+zO7mVzVAxxG+EeG/kUlgnYNORQ',
            // 必填-应用公钥证书 路径
            // 设置应用私钥后，即可下载得到以下3个证书
            'app_public_cert_path' => './config/payment/appCertPublicKey_2021005138609956.crt',
            // 必填-支付宝公钥证书 路径
            'alipay_public_cert_path' => './config/payment/alipayCertPublicKey_RSA2.crt',
            // 必填-支付宝根证书 路径
            'alipay_root_cert_path' => './config/payment/alipayRootCert.crt',
            'return_url' => '',
            'notify_url' => 'https://zhying.top/api/notify/alipay',
            // 选填-第三方应用授权token
            'app_auth_token' => '',
            // 选填-服务商模式下的服务商 id，当 mode 为 Pay::MODE_SERVICE 时使用该参数
            'service_provider_id' => '',
            // 选填-默认为正常模式。可选为： MODE_NORMAL, MODE_SANDBOX, MODE_SERVICE
            'mode' => Pay::MODE_NORMAL,
        ]
    ],
    'wechat' => [
        'default' => [
            // 必填-商户号，服务商模式下为服务商商户号
            // 可在 https://pay.weixin.qq.com/ 账户中心->商户信息 查看
            'mch_id' => '1715415117',
            // 选填-v2商户私钥
            'mch_secret_key_v2' => '',
            // 必填-v3 商户秘钥
            // 即 API v3 密钥(32字节，形如md5值)，可在 账户中心->API安全 中设置
            'mch_secret_key' => 'd710su1efN2L5IVXq0iLuuBMrwnD8ZqX',
            // 必填-商户私钥 字符串或路径
            // 即 API证书 PRIVATE KEY，可在 账户中心->API安全->申请API证书 里获得
            // 文件名形如：apiclient_key.pem
            'mch_secret_cert' => './config/payment/apiclient_key.pem',
            // 必填-商户公钥证书路径
            // 即 API证书 CERTIFICATE，可在 账户中心->API安全->申请API证书 里获得
            // 文件名形如：apiclient_cert.pem
            'mch_public_cert_path' => './config/payment/apiclient_cert.pem',
            // 必填-微信回调url
            // 不能有参数，如?号，空格等，否则会无法正确回调
            'notify_url' => 'https://zhying.top/api/notify/wechat',
            // 选填-公众号 的 app_id
            // 可在 mp.weixin.qq.com 设置与开发->基本配置->开发者ID(AppID) 查看
            'mp_app_id' => '',
            // 选填-小程序 的 app_id
            'mini_app_id' => '',
            // 选填-app 的 app_id
            'app_id' => 'wxde1034983b564c86',
            // 选填-服务商模式下，子公众号 的 app_id
            'sub_mp_app_id' => '',
            // 选填-服务商模式下，子 app 的 app_id
            'sub_app_id' => '',
            // 选填-服务商模式下，子小程序 的 app_id
            'sub_mini_app_id' => '',
            // 选填-服务商模式下，子商户id
            'sub_mch_id' => '',
            // 「选填」（适用于 2024-11 及之前开通微信支付的老商户）微信支付平台证书序列号及证书路径，强烈建议 php-fpm 模式下配置此参数
            // 「必填」微信支付公钥ID及证书路径，key 填写形如 PUB_KEY_ID_0000000000000024101100397200000006 的公钥id，见 https://pay.weixin.qq.com/doc/v3/merchant/4013053249
            'wechat_public_cert_path' => [
                '45F59D4DABF31918AFCEC556D5D2C6E376675D57' => __DIR__.'/Cert/wechatPublicKey.crt',
                'PUB_KEY_ID_0117154151172025042700441733000600'=> './config/payment/pub_key.pem'
            ],
            // 选填-默认为正常模式。可选为： MODE_NORMAL, MODE_SERVICE
            'mode' => Pay::MODE_NORMAL,
        ]
    ],
    'unipay' => [
        'default' => [
            // 必填-商户号
            'mch_id' => '777290058167151',
            // 选填-商户密钥：为银联条码支付综合前置平台配置：https://up.95516.com/open/openapi?code=unionpay
            'mch_secret_key' => '979da4cfccbae7923641daa5dd7047c2',
            // 必填-商户公私钥
            'mch_cert_path' => __DIR__.'/Cert/unipayAppCert.pfx',
            // 必填-商户公私钥密码
            'mch_cert_password' => '000000',
            // 必填-银联公钥证书路径
            'unipay_public_cert_path' => __DIR__.'/Cert/unipayCertPublicKey.cer',
            // 必填
            'return_url' => 'https://yansongda.cn/unipay/return',
            // 必填
            'notify_url' => 'https://yansongda.cn/unipay/notify',
            'mode' => Pay::MODE_NORMAL,
        ],
    ],
    'douyin' => [
        'default' => [
            // 选填-商户号
            // 抖音开放平台 --> 应用详情 --> 支付信息 --> 产品管理 --> 商户号
            'mch_id' => '73744242495132490630',
            // 必填-支付 Token，用于支付回调签名
            // 抖音开放平台 --> 应用详情 --> 支付信息 --> 支付设置 --> Token(令牌)
            'mch_secret_token' => 'douyin_mini_token',
            // 必填-支付 SALT，用于支付签名
            // 抖音开放平台 --> 应用详情 --> 支付信息 --> 支付设置 --> SALT
            'mch_secret_salt' => 'oDxWDBr4U7FAAQ8hnGDm29i4A6pbTMDKme4WLLvA',
            // 必填-小程序 app_id
            // 抖音开放平台 --> 应用详情 --> 支付信息 --> 支付设置 --> 小程序appid
            'mini_app_id' => 'tt226e54d3bd581bf801',
            // 选填-抖音开放平台服务商id
            'thirdparty_id' => '',
            // 选填-抖音支付回调地址
            'notify_url' => 'https://yansongda.cn/douyin/notify',
        ],
    ],
    'jsb' => [
        'default' => [
            // 服务代码
            'svr_code' => '',
            // 必填-合作商ID
            'partner_id' => '',
            // 必填-公私钥对编号
            'public_key_code' => '00',
            // 必填-商户私钥(加密签名)
            'mch_secret_cert_path' => '',
            // 必填-商户公钥证书路径(提供江苏银行进行验证签名用)
            'mch_public_cert_path' => '',
            // 必填-江苏银行的公钥(用于解密江苏银行返回的数据)
            'jsb_public_cert_path' => '',
            //支付通知地址
            'notify_url' => '',
            // 选填-默认为正常模式。可选为： MODE_NORMAL:正式环境, MODE_SANDBOX:测试环境
            'mode' => Pay::MODE_NORMAL,
        ],
    ],
    'logger' => [
        'enable' => true,
        'file' => './runtime/logs/pay.log',
        'level' => 'info', // 建议生产环境等级调整为 info，开发环境为 debug
        'type' => 'single', // optional, 可选 daily.
        'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
    ],
    'http' => [ // optional
        'timeout' => 5.0,
        'connect_timeout' => 5.0,
        // 更多配置项请参考 [Guzzle](https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html)
    ],
];
