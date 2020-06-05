<?php
require __DIR__ . '/vendor/autoload.php';

use Jumbojett\OpenIDConnectClient;






class oidc extends control{

    public function index()
    {
        $last = $this->server->request_time;
        $referer = empty($_GET['referer']) ? '' : $this->get->referer;
        $locate  = empty($referer) ? getWebRoot() : base64_decode($referer);

        $oidc = new OpenIDConnectClient(
            $this->config->oidc->issuer,
            $this->config->oidc->clientId,
            $this->config->oidc->clientSecret
        );
        if( $_SERVER["HTTPS"])
                $path="https://".$_SERVER["HTTP_HOST"];
        else
            $path="http://".$_SERVER["HTTP_HOST"];
        $oidc->setRedirectURL($path."/index.html?m=oidc&f=index");
       
        try{
           $oidc->addScope("profile");
        $oidc->addScope("email");
       // $token = $oidc->requestClientCredentialsToken()->access_token;
        $oidc->authenticate();

        $user = $oidc->requestUserInfo();
        }catch(Exception $e)
         {
            die("oidc error:".$e->getMessage());
        }
       $this->view->name =$user->name;
       $this->view->email=$user->email;
       $this->view->sub=$user->sub;
      
     
        $dbuser = $this->oidc->getBindUser($user->name);
        if(!$dbuser)
        {
            $this->view->error="user not exist!";
            $this->oidc->createUser($user);
        }

        $this->loadModel('user');
        
        //设置登录信息
        $this->user->cleanLocked($dbuser->account);
        /* Authorize him and save to session. */
        $dbuser->admin    = strpos($this->app->company->admins, ",{$dbuser->account},") !== false;
        $dbuser->rights   = $this->user->authorize($dbuser->account);
        $dbuser->groups   = $this->user->getGroups($dbuser->account);
        $dbuser->view     = $this->user->grantUserView($dbuser->account, $dbuser->rights['acls']);
        $dbuser->last     = date(DT_DATETIME1, $last);
        $dbuser->lastTime = $dbuser->last;
        $dbuser->modifyPassword = ($dbuser->visits == 0 and !empty($this->config->safe->modifyPasswordFirstLogin));
        if($dbuser->modifyPassword) $dbuser->modifyPasswordReason = 'modifyPasswordFirstLogin';
        if(!$dbuser->modifyPassword and !empty($this->config->safe->changeWeak))
        {
            $dbuser->modifyPassword = $this->loadModel('admin')->checkWeak($user);
            if($dbuser->modifyPassword) $user->modifyPasswordReason = 'weak';
        }

        $userIP = $this->server->remote_addr;
        $this->dao->update(TABLE_USER)->set('visits = visits + 1')->set('ip')->eq($userIP)->set('last')->eq($last)->where('account')->eq($dbuser->account)->exec();

        $this->session->set('user', $dbuser);
        $this->app->user = $this->session->user;
        $this->loadModel('action')->create('user', $dbuser->id, 'login');
        die($this->locate($locate));

        $this->display("my","index");
    }

    
}
?>