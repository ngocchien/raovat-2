<?php

return array(
    'router' => array(
        'routes' => array(
            'backend' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/backend[/:controller][/:action][/id/:id][/code/:code][/from/:from][/ship/:ship][/to/:to][/page/:page][/gid/:gid][/pid/:pid][/type/:type][/tab/:tab][/limit/:limit][/][?s=:s][&ban_cate_id=:ban_cate_id][&ban_location=:ban_location]',
                    'constraints' => array(
                        'module' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'code' => '[a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                        'pid' => '[0-9]*',
                        'page' => '[0-9]*',
                        'limit' => '[0-9]*',
                        'ship' => '[0-9]*',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Backend\Controller',
                        'module' => 'backend',
                        'controller' => 'index',
                        'action' => 'index',
                        'id' => 0,
                        'pid' => 0,
                        'page' => 1,
                        'limit' => 15
                    ),
                ),
            ),
        ),
    ), 'console' => array(
        'router' => array(
            'routes' => array(
                'migrate' => array(
                    'options' => array(
                        'route' => 'migrate [--type=] [--createindex=] [--page=] [--limit=] [isdev]',
                        'defaults' => array(
                            '__NAMESPACE__' => 'Backend\Controller',
                            'controller' => 'console',
                            'action' => 'migrate'
                        ),
                    ),
                ), 'check-worker-running' => array(
                    'options' => array(
                        'route' => 'check-worker-running',
                        'defaults' => array(
                            '__NAMESPACE__' => 'Backend\Controller',
                            'controller' => 'console',
                            'action' => 'checkWorkerRunning'
                        ),
                    ),
                ), 'worker' => array(
                    'options' => array(
                        'route' => 'worker [--type=] [--stop=] [--background=]',
                        'defaults' => array(
                            '__NAMESPACE__' => 'Backend\Controller',
                            'controller' => 'console',
                            'action' => 'worker'
                        ),
                    ),
                ),
                'crontab' => array(
                    'options' => array(
                        'route' => 'crontab [--type=]',
                        'defaults' => array(
                            '__NAMESPACE__' => 'Backend\Controller',
                            'controller' => 'console',
                            'action' => 'crontab'
                        ),
                    ),
                ),
                'crawler' => array(
                    'options' => array(
                        'route' => 'crawler [--type=]',
                        'defaults' => array(
                            '__NAMESPACE__' => 'Backend\Controller',
                            'controller' => 'console',
                            'action' => 'crawler'
                        ),
                    ),
                ),
                'crawlerkeyword' => array(
                    'options' => array(
                        'route' => 'crawlerkeyword',
                        'defaults' => array(
                            '__NAMESPACE__' => 'Backend\Controller',
                            'controller' => 'console',
                            'action' => 'crawler-keyword'
                        ),
                    ),
                ),
                'sitemap' => array(
                    'options' => array(
                        'route' => 'sitemap',
                        'defaults' => array(
                            '__NAMESPACE__' => 'Backend\Controller',
                            'controller' => 'console',
                            'action' => 'sitemap'
                        ),
                    ),
                ),
                'init-es' => array(
                    'options' => array(
                        'route' => 'init-es',
                        'defaults' => array(
                            '__NAMESPACE__' => 'Backend\Controller',
                            'controller' => 'console',
                            'action' => 'init-es'
                        ),
                    ),
                ),
                'init-data-keyword' => array(
                    'options' => array(
                        'route' => 'init-data-keyword',
                        'defaults' => array(
                            '__NAMESPACE__' => 'Backend\Controller',
                            'controller' => 'console',
                            'action' => 'init-keyword-old'
                        ),
                    ),
                ),
                'test' => array(
                    'options' => array(
                        'route' => 'test',
                        'defaults' => array(
                            '__NAMESPACE__' => 'Backend\Controller',
                            'controller' => 'console',
                            'action' => 'test'
                        ),
                    ),
                ),
                'hot-key' => array(
                    'options' => array(
                        'route' => 'hot-key',
                        'defaults' => array(
                            '__NAMESPACE__' => 'Backend\Controller',
                            'controller' => 'console',
                            'action' => 'keyword-hot'
                        ),
                    ),
                ),
                'hot-trend' => array(
                    'options' => array(
                        'route' => 'hot-trend',
                        'defaults' => array(
                            '__NAMESPACE__' => 'Backend\Controller',
                            'controller' => 'console',
                            'action' => 'hot-trend'
                        ),
                    ),
                ),
                'videos' => array(
                    'options' => array(
                        'route' => 'videos-youtube',
                        'defaults' => array(
                            '__NAMESPACE__' => 'Backend\Controller',
                            'controller' => 'console',
                            'action' => 'videos-youtube'
                        ),
                    ),
                ),
                'update-keyword' => array(
                    'options' => array(
                        'route' => 'update-keyword',
                        'defaults' => array(
                            '__NAMESPACE__' => 'Backend\Controller',
                            'controller' => 'console',
                            'action' => 'update-keyword'
                        ),
                    ),
                ),
                'update-new-key' => array(
                    'options' => array(
                        'route' => 'update-new-key [--id=] [--pid=]',
                        'defaults' => array(
                            '__NAMESPACE__' => 'Backend\Controller',
                            'controller' => 'console',
                            'action' => 'update-new-key'
                        ),
                    ),
                ),
                'check-process' => array(
                    'options' => array(
                        'route' => 'check-process [--name=]',
                        'defaults' => array(
                            '__NAMESPACE__' => 'Backend\Controller',
                            'controller' => 'console',
                            'action' => 'check-process'
                        ),
                    ),
                ),
                'control-worker' => array(
                    'options' => array(
                        'route' => 'control-worker',
                        'defaults' => array(
                            '__NAMESPACE__' => 'Backend\Controller',
                            'controller' => 'console',
                            'action' => 'control-worker'
                        ),
                    ),
                )
            )
        )
    ),
    'module_layouts' => array(
        'Backend' => 'backend/layout',
    ),
    'view_helpers' => array(
        'invokables' => array(
            'paging' => 'My\View\Helper\Paging',
            'pagingajax' => 'My\View\Helper\Pagingajax'
        )
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'json_exceptions' => array(
            'display' => true,
            'ajax_only' => true,
            'show_trace' => true
        ),
        'doctype' => 'HTML5',
        'not_found_template' => 'backend/error/404',
        'exception_template' => 'backend/error/index',
        'template_map' => array(
            'backend/layout' => __DIR__ . '/../view/layout/layout.phtml',
            'backend/header' => __DIR__ . '/../view/layout/header.phtml',
            'backend/sidebar' => __DIR__ . '/../view/layout/sidebar.phtml',
            'backend/auth' => __DIR__ . '/../view/layout/auth.phtml',
            'backend/error/404' => __DIR__ . '/../view/error/404.phtml',
            'backend/error/index' => __DIR__ . '/../view/error/index.phtml',
            'backend/error/accessDeny' => __DIR__ . '/../view/error/access-deny.phtml',
            'backend/layout/send-mail-when-crawler' => __DIR__ . '/../view/layout/send-mail-when-crawler.phtml',
        ),
        'template_path_stack' => array(
            'backend' => __DIR__ . '/../view',
            'email' => __DIR__ . '/../view/email',
            'modal' => __DIR__ . '/../view/modal',
        ),
    ),
);
