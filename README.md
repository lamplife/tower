# tower for hyperf

```
该组件封装了 Tower 几乎所有的 api，组件适用于 Hyperf 框架，通过对 Tower 获取 AccessToken、刷新 AccessToken 两个接口的封装，结合 Hyperf 定时任务，可以完全无需理会业务接口调用过程中所需考虑的 AccessToken 问题，组件会将最新 AccessToken 自动更新进 options 里面。
组件使用了 Redis 缓存来存储 AccessToken，请安装完组件依赖的同时，继而事先配置好 Redis
```

## 安装组件
>composer require firstphp/tower


## 发布配置
>php bin/hyperf.php vendor:publish firstphp/tower


## 获取Tower授权码（详见Tower开发文档）
>https://tower.im/oauth/authorize?client_id={client_id}&redirect_uri=urn:ietf:wg:oauth:2.0:oob&response_type=code


## 编辑.env配置
CLIENT_ID=ef8gd5cb1071e61483303432be7183af7c285993d74392364090bbdcb8710bbe
CLIENT_SERCET=903390ab427caf30a4824c3322d004562714f6c69de34158749bc11e6fd72ddf
AUTH_CODE=e1acb4311d6df4d86g5f83022f0eca8a592e1cbff94e55dcpac838e96beb3a40
TOWER_URL=https://tower.im/


## Hyperf Demo

    use Hyperf\Di\Annotation\Inject;

    ......

    /**
     * @Inject()
     * @var \Firstphp\Tower\TowerInterface
     */
    private $tower;

    public function test() {
    	// 初次访问调用一次即可，刷新Token可以单独放在计划任务里面定时刷新
        $this->tower->getAccessToken();

        // 获取当前账号信息
        $this->tower->getUser();
    }