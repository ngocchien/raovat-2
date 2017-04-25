<?php

if (APPLICATION_ENV === 'dev') {
    $display_not_found_reason = true;
    $display_exceptions = true;
    $errorHandler = array(
        'display' => true,
        'ajax_only' => true,
        'show_trace' => true
    );
} else {
    $display_not_found_reason = false;
    $display_exceptions = false;
    $errorHandler = array(
        'display' => false,
        'ajax_only' => false,
        'show_trace' => false
    );
}

return array(
    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'index',
                        'action' => 'index',
                    ),
                ),
            ),
            'frontend' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '[/:controller[/:action][/page:page][/id:id]][/]',
                    'constraints' => array(
                        'controller' => 'index',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'sort' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'page' => '[0-9]+',
                        'id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'index',
                        'action' => 'index',
                    ),
                ),
            ),
            'index' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '[[/page[/:page]].html]',
                    'constraints' => array(
                        'page' => '[0-9]+',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'index',
                        'action' => 'index',
                        'page' => 1
                    ),
                ),
            ),
            'search' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/tim-kiem/[[?keyword=:keyword][&page=:page]]',
                    'constraints' => array(
                        'controller' => 'search',
                        'action' => 'index',
                        'page' => '[0-9]+',
                        'keyword' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'search',
                        'action' => 'index',
                        'page' => 1,
                        'keyword' => ''
                    ),
                ),
            ),
            'keyword' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/tu-khoa[[/:keySlug]-[:keyId].html[?page=:page]]',
                    'constraints' => array(
                        'controller' => 'search',
                        'action' => 'keyword',
                        'keyId' => '[0-9]+',
                        'keySlug' => '[a-zA-Z0-9_-]*',
                        'page' => '[0-9]+'
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'search',
                        'action' => 'keyword',
                        'keyId' => 0,
                        'keySlug' => '',
                        'page' => 1
                    ),
                ),
            ),
            'list-keyword' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/danh-sach-tu-khoa/[[?page=:page]]',
                    'constraints' => array(
                        'controller' => 'search',
                        'action' => 'list-keyword',
                        'page' => '[0-9]+'
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'search',
                        'action' => 'list-keyword',
                        'page' => 1
                    ),
                ),
            ),
            'general' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/general[[/:geneSlug]-[:geneId].html]',
                    'constraints' => array(
                        'module' => 'frontend',
                        'controller' => 'general',
                        'action' => 'index',
                        'geneSlug' => '[a-zA-Z0-9_-]*',
                        'geneId' => '[0-9]+',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'general',
                        'action' => 'index',
                        'geneSlug' => '',
                        'geneId' => 0,
                    ),
                ),
            ),
            'view-content' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/bai-viet[[/:contentSlug]-[:contentId].html]',
                    'constraints' => array(
                        'module' => 'frontend',
                        'controller' => 'content',
                        'action' => 'detail',
                        'contentSlug' => '[a-zA-Z0-9_-]*',
                        'contentId' => '[0-9]+',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'content',
                        'action' => 'detail',
                        'contentSlug' => '',
                        'contentId' => 0
                    ),
                ),
            ),
            '404' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/404.html',
                    'constraints' => array(
                        'controller' => 'error',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'error',
                        'action' => 'e404',
                    ),
                ),
            ),
            'contact' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/lien-he.html',
                    'constraints' => array(
                        'controller' => 'general',
                        'action' => 'contact',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'general',
                        'action' => 'contact',
                    ),
                ),
            ),
            'privacy-policy' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/chinh-sach-bao-mat.html',
                    'constraints' => array(
                        'controller' => 'general',
                        'action' => 'privacy-policy',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'general',
                        'action' => 'privacy-policy',
                    ),
                ),
            ),
            'notfound' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/s[?[s=:s][&price=:price][&sort=:sort][&page=:page]]',
                    'constraints' => array(
                        'controller' => 'error',
                        'index' => 'redirect',
                        's' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'brand' => '[a-zA-Z0-9_-]*',
                        'price' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'sort' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'page' => '[0-9]+',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'error',
                        'action' => 'redirect',
                    ),
                ),
            ),
            'category' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/danh-muc/[[:cateSlug]-[:cateId]][/page[/:page]][.html]',
                    'constraints' => array(
                        'controller' => 'category',
                        'action' => 'index',
                        'cateSlug' => '[a-zA-Z0-9_-]*',
                        'cateId' => '[0-9]+',
                        'page' => '[0-9]+',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'category',
                        'action' => 'index',
                        'cateSlug' => '',
                        'cateId' => 0,
                        'page' => 1
                    ),
                ),
            ),
            'sitemap' => array(
                'type' => 'Segment',
                'options' => array(
//                    'route' => '/sitemap/[[:action]/[:page][.html]]',
                    'route' => '/sitemap[[/:action][/:page].html]',
                    'constraints' => array(
                        'controller' => 'sitemap',
                        'action' => '[a-zA-Z0-9_-]*',
                        'page' => '[0-9]+',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'sitemap',
                        'action' => 'index',
                        'page' => 1
                    ),
                ),
            ),
        ),
    ),
    'module_layouts' => array(
        'Frontend' => FRONTEND_TEMPLATE . '/layout/layout'
    ),
    'view_helpers' => array(
        'invokables' => array(
            'paging' => 'My\View\Helper\Paging',
        )
    ),
    'translator' => array('locale' => 'en_US'),
    'view_manager' => array(
        'display_not_found_reason' => $display_not_found_reason,
        'display_exceptions' => $display_exceptions,
        'doctype' => 'HTML5',
        'template_map' => array(
            FRONTEND_TEMPLATE . '/layout/layout' => __DIR__ . '/../view/' . FRONTEND_TEMPLATE . '/layout/layout.phtml',
            'frontend/header' => __DIR__ . '/../view/' . FRONTEND_TEMPLATE . '/layout/header.phtml',
            'frontend/layder-slider' => __DIR__ . '/../view/' . FRONTEND_TEMPLATE . '/layout/layderSlider.phtml',
            'frontend/search' => __DIR__ . '/../view/' . FRONTEND_TEMPLATE . '/layout/search.phtml',
            'frontend/premium' => __DIR__ . '/../view/' . FRONTEND_TEMPLATE . '/layout/premium.phtml',
            'frontend/category' => __DIR__ . '/../view/' . FRONTEND_TEMPLATE . '/layout/category.phtml',
            'frontend/classify' => __DIR__ . '/../view/' . FRONTEND_TEMPLATE . '/layout/classify.phtml',
            'frontend/footer' => __DIR__ . '/../view/' . FRONTEND_TEMPLATE . '/layout/footer.phtml',
            'frontend/template_email' => __DIR__ . '/../view/' . FRONTEND_TEMPLATE . '/layout/template_email.phtml',
            'frontend/content/upload' => __DIR__ . '/../view/' . FRONTEND_TEMPLATE . '/frontend/content/upload.phtml',
            'frontend/auth/reset-password' => __DIR__ . '/../view/' . FRONTEND_TEMPLATE . '/frontend/auth/reset-password.phtml',
            'frontend/nav-user-left' => __DIR__ . '/../view/' . FRONTEND_TEMPLATE . '/layout/nav-user-left.phtml',
            'frontend/nav-right' => __DIR__ . '/../view/' . FRONTEND_TEMPLATE . '/layout/nav-right.phtml',
            'frontend/nav-left' => __DIR__ . '/../view/' . FRONTEND_TEMPLATE . '/layout/nav-left.phtml',
            'frontend/content/add-comment' => __DIR__ . '/../view/' . FRONTEND_TEMPLATE . '/frontend/content/add-comment.phtml',
            'frontend/email-messages' => __DIR__ . '/../view/' . FRONTEND_TEMPLATE . '/layout/email-messages.phtml',
            'frontend/footer-email' => __DIR__ . '/../view/' . FRONTEND_TEMPLATE . '/layout/footer-email.phtml',
            'frontend/user/get-messages' => __DIR__ . '/../view/' . FRONTEND_TEMPLATE . '/frontend/user/get-messages.phtml',
            'frontend/email-replay-messages' => __DIR__ . '/../view/' . FRONTEND_TEMPLATE . '/layout/email-replay-messages.phtml'
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view/' . FRONTEND_TEMPLATE,
        ),
        'json_exceptions' => $errorHandler,
    ),
);
