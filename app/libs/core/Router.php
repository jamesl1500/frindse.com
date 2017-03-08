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
                
                $controller = new $page();
                $controller->index();
                
                return false;
            }else{
                echo 'no';
            }
        }
    }
    
}