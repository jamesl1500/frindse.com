<?php
/*
 * Frindse Router Class
 * ----
 * Author: James Latten
 * Copyright: Frindse @ 2017
 * Created By: Sitelyftstudios.com
 */

class Router
{
    
    /*
     * Routing Pages
     * ----
     * @desc This will route pages with single url strings
     */
    static public function Route($page)
    {
        if(!empty($page))
        {
            $file = SITE_ROOT . '/app/controllers/' . $page . '.controller.php';
            
            if(file_exists($file))
            {
                require $file;
                
                $c = $page . 'Controller';
                
                $controller = new $c();
                $controller->index();
                
                return false;
            }else{
                Redirect::to('errors', '404');
            }
        }
    }

    static public function RoutePageWithSub($page, $sub, $params = array())
    {
        if(!empty($page))
        {
            // First lets make sure this page exist
            $file = SITE_ROOT . '/app/controllers/' . $page . '.controller.php';

            if (file_exists($file)) {
                require $file;

                $c = $page . 'Controller';

                $controller = new $c();

                if(count($params) == 0)
                {
                    $controller->$sub();
                }else{
                    $controller->$sub($params);
                }
                return false;
            }else{
                Redirect::to('errors', '404');
            }
        }
    }

    static public function RouteApiCall()
    {
        
    }
    
}