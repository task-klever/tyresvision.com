<?php
namespace MyCompany\CookieInspector\Plugin;

use Magento\Framework\App\Request\Http;

class PostInterceptor
{
    public function beforeDispatch(\Magento\Framework\App\FrontControllerInterface $subject, Http $request)
    {
        if ($request->getMethod() === "POST") {
            if (isset($_COOKIE["ggg"]) && $_COOKIE["ggg"] === "xxx") {
                setcookie("ggg", "ppp", time() + 3600, "/");
            }
        }
    }
}
