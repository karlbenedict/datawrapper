<?php

if (DatawrapperHooks::hookRegistered(DatawrapperHooks::GET_ADMIN_PAGES)) {
    // pull admin pages from plugins
    $__dw_admin_pages = DatawrapperHooks::execute(DatawrapperHooks::GET_ADMIN_PAGES);

    // order admin pages by index "order"
    usort($__dw_admin_pages, function($a, $b) {
        return (isset($a['order']) ? $a['order'] : 9999) - (isset($b['order']) ? $b['order'] : 9999);
    });

    // redirect to first admin page
    $app->get('/admin/', function() use ($app, $__dw_admin_pages) {
        $app->redirect('/admin'.$__dw_admin_pages[0]['url']);
    });

    foreach ($__dw_admin_pages as $admin_page) {

        $app->map('/admin' . $admin_page['url'], function() use ($app, $admin_page, $__dw_admin_pages) {
            $args = func_get_args();
            disable_cache($app);

            $user = DatawrapperSession::getUser();
            if ($user->isAdmin()) {
                $page_vars = array(
                    'title' => $admin_page['title'],
                    'adminmenu' => array(),
                    'adminactive' => $admin_page['url']
                );
                // add admin pages to menu
                foreach ($__dw_admin_pages as $adm_pg) {
                    if (empty($adm_pg['hide'])) {
                        $page_vars['adminmenu'][$adm_pg['url']] = $adm_pg['title'];
                    }
                }
                add_header_vars($page_vars, 'admin');
                call_user_func_array($admin_page['controller'], array($app, $page_vars, $args));
            } else {
                $app->notFound();
            }
        })->via('GET', 'POST');
    }
}