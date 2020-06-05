本模块用于禅道与OpenId的SSO单点登录整合。
使用方法：
    
1. 将oidc整个目录拷贝到禅道的module模块中，作为禅道的一个模块使用。
2. 配置oidc中的config.php
3. 配置filter，由于禅道的框架会将url中的参数过滤不允许有空格存在。所以需要将以下代码放置到/config/my.php的最后。

        ```
        $filter->oidc=new stdclass();
        $filter->oidc->index=new stdclass();
        $filter->oidc->index->paramValue['scope']='reg::any';
        ```
4.  修改/config/my.php
    ```
     //$config->requestType     = 'PATH_INFO';

      $config->requestType     = 'GET';
    ```
5.	修改/module/commom/model.php，将oidc放置到匿名访问名单中，在model.php的isOpenMethod方法添加一行。
    ```
         public function isOpenMethod($module, $method)
         {
             if($module == 'oidc' and $method == 'index')  return true;
     ```

6.	如果你不希望出现禅道的用户登录界面，直接跳转到STS的登录界面在/module/common/model.php的 public function checkPriv() 最后一行改成

    ```
    //die(js::locate(helper::createLink('user', 'login', "referer=$referer")));

     die(js::locate(helper::createLink('oidc', 'index', "referer=$referer")));
    ```

7.  修改framework/base/router.class.php里面的setSuperVars方法，将下面的语句注释掉。
    ```
        public function setSuperVars()

      //  unset($_REQUEST);
    ```
    