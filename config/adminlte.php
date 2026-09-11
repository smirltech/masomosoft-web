<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    |
    | Here you can change the default title of your scolarite panel.
    |
    | For detailed instructions you can look the title section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'title' => 'Collège ENK',
    'title_prefix' => '',
    'title_postfix' => '',

    /*
    |--------------------------------------------------------------------------
    | Favicon
    |--------------------------------------------------------------------------
    |
    | Here you can activate the favicon.
    |
    | For detailed instructions you can look the favicon section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_ico_only' => false,
    'use_full_favicon' => false,

    /*
    |--------------------------------------------------------------------------
    | Google Fonts
    |--------------------------------------------------------------------------
    |
    | Here you can allow or not the use of external google fonts. Disabling the
    | google fonts may be useful if your scolarite panel internet access is
    | restricted somehow.
    |
    | For detailed instructions you can look the google fonts section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'google_fonts' => [
        'allowed' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Logo
    |--------------------------------------------------------------------------
    |
    | Here you can change the logo of your scolarite panel.
    |
    | For detailed instructions you can look the logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'logo' => env('APP_NAME'),
    'logo_img' => 'images/logo.jpg',
    'logo_img_class' => 'brand-image img-circle elevation-3',
    'logo_img_xl' => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => 'CENK',

    /*
    |--------------------------------------------------------------------------
    | Preloader Animation
    |--------------------------------------------------------------------------
    |
    | Here you can change the preloader animation configuration.
    |
    | For detailed instructions you can look the preloader section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'preloader' => [
        'enabled' => false,
        'img' => [
            'path' => 'images/logo.jpg',
            'alt' => 'Marva Preloader Image',
            'effect' => 'animation_shake',
            'width' => 60,
            'height' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Menu
    |--------------------------------------------------------------------------
    |
    | Here you can activate and change the user menu.
    |
    | For detailed instructions you can look the user menu section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'usermenu_enabled' => true,
    'usermenu_header' => true,
    'usermenu_header_class' => 'bg-primary',
    'usermenu_image' => true,
    'usermenu_desc' => true,
    'usermenu_profile_url' => true,

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | Here we change the layout of your scolarite panel.
    |
    | For detailed instructions you can look the layout section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'layout_topnav' => null,
    'layout_boxed' => null,
    'layout_fixed_sidebar' => null,
    'layout_fixed_navbar' => true,
    'layout_fixed_footer' => null,
    'layout_dark_mode' => null,

    /*
    |--------------------------------------------------------------------------
    | Authentication Views Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the authentication views.
    |
    | For detailed instructions you can look the auth classes section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_auth_card' => 'card-outline card-primary',
    'classes_auth_header' => '',
    'classes_auth_body' => '',
    'classes_auth_footer' => '',
    'classes_auth_icon' => '',
    'classes_auth_btn' => 'btn-flat btn-primary',

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the scolarite panel.
    |
    | For detailed instructions you can look the scolarite panel classes here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_body' => '',
    'classes_brand' => '',
    'classes_brand_text' => '',
    'classes_content_wrapper' => '',
    'classes_content_header' => 'd-none d-sm-block',
    'classes_content' => '',
    'classes_sidebar' => 'sidebar-dark-primary elevation-4',
    'classes_sidebar_nav' => '',
    'classes_topnav' => 'navbar-white navbar-light',
    'classes_topnav_nav' => 'navbar-expand',
    'classes_topnav_container' => 'container',

    /*
    |--------------------------------------------------------------------------
    | Sidebar
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar of the scolarite panel.
    |
    | For detailed instructions you can look the sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'sidebar_mini' => 'lg',
    'sidebar_collapse' => true,
    'sidebar_collapse_auto_size' => false,
    'sidebar_collapse_remember' => false,
    'sidebar_collapse_remember_no_transition' => false,
    'sidebar_scrollbar_theme' => 'os-theme-light',
    'sidebar_scrollbar_auto_hide' => 'l',
    'sidebar_nav_accordion' => false,
    'sidebar_nav_animation_speed' => 300,

    /*
    |--------------------------------------------------------------------------
    | Control Sidebar (Right Sidebar)
    |--------------------------------------------------------------------------
    |
    | Here we can modify the right sidebar aka control sidebar of the scolarite panel.
    |
    | For detailed instructions you can look the right sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'right_sidebar' => false,
    'right_sidebar_icon' => 'fas fa-cogs',
    'right_sidebar_theme' => 'dark',
    'right_sidebar_slide' => true,
    'right_sidebar_push' => true,
    'right_sidebar_scrollbar_theme' => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide' => 'l',

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    |
    | Here we can modify the url settings of the scolarite panel.
    |
    | For detailed instructions you can look the urls section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_route_url' => false,
    'dashboard_url' => '/',
    'logout_url' => 'logout',
    'login_url' => 'login',
    'register_url' => 'register',
    'password_reset_url' => 'password/reset',
    'password_email_url' => 'password/email',
    'profile_url' => false,

    /*
    |--------------------------------------------------------------------------
    | Laravel Mix
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Laravel Mix option for the scolarite panel.
    |
    | For detailed instructions you can look the laravel mix section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'enabled_laravel_mix' => false,
    'laravel_mix_css_path' => 'css/app.css',
    'laravel_mix_js_path' => 'js/app.js',

    /*
    |--------------------------------------------------------------------------
    | Menu Items
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar/top navigation of the scolarite panel.
    |
    | For detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'menu' => [
        // Navbar items:
        [
            'type' => 'fullscreen-widget',
            'topnav_right' => true,
        ],

        [
            'type' => 'navbar-notification',
            'id' => 'my-dropdown-notification',
            'icon' => 'fas fa-bell',
            'route' => 'notifications.index',
            'topnav_right' => true,
            'dropdown_mode' => true, // Enable dropdown mode.
            'dropdown_flabel' => 'All notifications', // The label that will be used for the link to the configured url.
            'update_cfg' => [
                'route' => 'notifications.get',
                'period' => 30,
            ],
        ],
        [
            'text' => 'Tableau de bord',
            'icon' => 'fas fa-fw fa-tachometer-alt',
            'url' => '/',
        ],

        ['header' => 'SCOLARITÉ',
            'can' => [
                'eleves.view.*',
                'inscriptions.view.*',
                'eleves.create',
                'inscriptions.create',
                'responsables.view.*',
                'devoirs.view.*',
                'cours.view.*',
                'enseignants.view.*',
                'classes.view.*'
            ],
        ],
        [
            'text' => 'Recherche',
            'icon' => 'fas fa-fw fa-search',
            'can' => 'eleves.view.*',
            'url' => 'scolarite/eleves/recherche',
        ],
        [
            'text' => 'Élèves',
            'icon' => 'fas fa-fw fa-user-graduate',
            'can' => 'eleves.view.*',
            'url' => 'scolarite/eleves',
        ],
//        [
//            'text' => 'Reinscriptions',
//            'icon' => 'fa-solid fa-up-down',
//            'url' => '/scolarite/eleves/passer-classe-superieure',
//            'can' => 'scolarite.view.*',
//        ],



        [
            'text' => 'Reinscriptions',
            'icon' => 'fa-solid fa-up-down',
            'url' => '/scolarite/eleves/passer-classe-superieure',
//            'can' => 'scolarite.view.*',
        ],
        [
            'text' => 'Présences',
            'icon' => 'fas fa-fw fa-user-check',
          //  'can' => 'presences.view.*',
            'url' => 'scolarite/presences',
        ],
        [
            'text' => 'Responsables',
            'url' => 'scolarite/responsables',
            'icon' => 'fas fa-fw fa-person-pregnant',
            'can' => 'responsables.view.*',
        ],

        [
            'text' => 'Devoirs',
            'icon' => 'fas fa-fw fa-tasks',
            'url' => 'scolarite/devoirs',
            'can' => 'devoirs.view.*',
        ],

        [
            'text' => 'Cours',
            'url' => 'scolarite/cours',
            'icon' => 'fas fa-fw fa-book-open',
            'can' => 'cours.view.*',
        ],

        [
            'text' => 'Enseignants',
            'icon' => 'fas fa-fw fa-chalkboard-teacher',
            'url' => 'scolarite/enseignants',
            'can' => 'enseignants.view.*',
        ],


        [
            'header' => 'FINANCE',
            'can' => ['perceptions.view.*', 'perceptions.create', 'frais.view.*', 'depenses.view.*', 'depenses-types.view.*'],
        ],
        [
            'text' => 'Caisse',
            'icon' => 'fas fa-fw fa-cash-register',
            'url' => 'finance/caisse',
            'can' => 'perceptions.view.*',
        ],
        [
            'text' => 'Perceptions',
            'url' => 'finance/perceptions',
            'icon' => 'fas fa-fw fa-arrow-trend-up',
            'can' => 'perceptions.view.*',
        ],
        [
            'text' => 'Rapports',
            'icon' => 'fas fa-fw fa-chart-column',
            'can' => 'perceptions.view',
            'submenu' => [
                [
                    'text' => 'Financier',
                    'url' => 'finance/rapports',
                ],
                 [
                    'text' => 'Insovables',
                    'url' => 'finance/insolvables'
                ],
               /* [
                    'text' => 'Frais',
                    'route' => 'admin.rapports.index',
                ],*/
            ],
        ],
/*        [
            'text' => 'Rapports',
            'icon' => 'fas fa-fw fa-chart-column',
            'url' => 'finance/rapports',
            'can' => 'perceptions.view',
        ],*/
        [
            'text' => 'Frais',
            'url' => 'finance/frais',
            'can' => 'frais.view.*',
            'icon' => 'fas fa-fw fa-money-bill-wave',
        ],
        [
            'text' => 'Revenu Auxiliaire',
            'icon' => 'fas fa-fw fa-money-bill-trend-up',
            'url' => 'finance/revenus',
            'can' => 'revenus.view.*',
        ],
        [
            'text' => 'Dépenses',
            'icon' => 'fas fa-fw fa-arrow-trend-down',
            'can' => 'depenses.view.*',
            'url' => 'finance/depenses',
        ],
        [
            'text' => 'Types de Dépenses',
            'icon' => 'fas fa-fw fa-list-check',
            'can' => 'depense-types.view.*',
            'url' => 'finance/depense-types',
        ],


//        [
//            'header' => 'LOGISTIQUE',
//            'can' => ['consommables.view.*', 'operations.view.*', 'units.view.*', 'materiels.view.*', 'materiel-categories.view.*', 'mouvements.view.*', 'cessions.view.*'],
//        ],
//        [
//            'text' => 'Consommables',
//            'icon' => 'fas fa-fw fa-screwdriver-wrench',
//            'url' => 'logistique/consommables',
//            'can' => 'consommables.view.*',
//        ],
        /* [
             'text' => 'Les opérations',
             'icon' => 'fas fa-fw fa-layer-group',

             'url' => '#',
             'can' => 'operations.view.*',
         ],*/
//        [
//            'text' => 'Unités de mesure',
//            'icon' => 'fas fa-fw fa-ruler-combined',
//            'can' => 'units.view.*',
//            'url' => 'logistique/units',
//        ],
//        [
//            'text' => 'Materiels',
//            'icon' => 'fas fa-fw fa-wrench',
//            'url' => 'logistique/materiels',
//            'can' => 'materiels.view.*',
//        ],
//        [
//            'text' => 'Categories des materiels',
//            'icon' => 'fas fa-fw fa-layer-group',
//            'url' => 'logistique/categories',
//            'can' => 'materiel-categories.view.*',
//        ],
//        [
//            'text' => 'Mouvements des materiels',
//            'icon' => 'fas fa-fw fa-people-carry-box',
//            'url' => 'logistique/mouvements',
//            'can' => 'mouvements.view.*',
//        ],
        /*[
            'text' => 'Cessions des materiels',
            'icon' => 'fas fa-fw fa-hand-holding-hand',

            'url' => 'logistique/cessions',
            'can' => 'cessions.view.*',
        ],*/

//        [
//            'header' => 'BIBLIOTHÈQUE',
//            'can' => ['ouvrages.view.*', 'auteurs.view.*', 'tags.view.*', 'rayons.view.*'],
//        ],
//        [
//            'text' => 'Ouvrages',
//            'url' => 'bibliotheque/ouvrages',
//            'icon' => 'fas fa-fw fa-book',
//            'can' => 'ouvrages.view.*',
//        ],
//        [
//            'text' => 'Rayons',
//            'url' => 'bibliotheque/rayons',
//            'icon' => 'fas fa-fw fa-layer-group',
//            'can' => 'rayons.view.*',
//        ],
//        [
//            'text' => 'Auteurs',
//            'url' => 'bibliotheque/auteurs',
//            'icon' => 'fas fa-fw fa-book-open-reader',
//            'can' => 'auteurs.view.*',
//        ],
//        [
//            'text' => 'Étiquettes',
//            'url' => 'bibliotheque/etiquettes',
//            'icon' => 'fas fa-fw fa-tags',
//            'can' => 'tags.view.*',
//        ],

        [
            'header' => 'PROGRAMME',
            'can' => ['annees.view.*', 'filieres.view.*', 'options.view.*', 'sections.view.*'],
        ],
        [
            'text' => 'Classes',
            'icon' => 'fas fa-fw fa-person-chalkboard',
            'url' => 'scolarite/classes',
            'can' => 'classes.view.*',
        ],
        [
            'text' => 'Options',
            'url' => 'scolarite/options',
            'icon' => 'fas fa-fw fa-layer-group',
            'can' => 'options.view.*',
        ],
        [
            'text' => 'Sections',
            'url' => 'scolarite/sections',
            'icon' => 'fas fa-fw fa-layer-group',
            'can' => 'sections.view.*',
        ],
        [
            'text' => 'Années scolaires',
            'url' => 'scolarite/annees',
            'icon' => 'fas fa-fw fa-calendar-alt',
            'can' => 'annees.view.*',
        ],
        [
            'header' => 'PARAMÈTRES',
            'can' => ['users.view.*', 'roles.view'],
        ],
        [
            'text' => 'Utilisateurs',
            'url' => 'users',
            'icon' => 'fas fa-fw fa-users',
            'can' => 'users.view.*',
        ],
        [
            'text' => 'Rôles',
            'url' => 'roles',
            'icon' => 'fas fa-fw fa-user-tag',
            'can' => 'roles.view',
        ],

    ],
    /*
    |--------------------------------------------------------------------------
    | Menu Filters
    |--------------------------------------------------------------------------
    |
    | Here we can modify the menu filters of the scolarite panel.
    |
    | For detailed instructions you can look the menu filters section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'filters' => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugins Initialization
    |--------------------------------------------------------------------------
    |
    | Here we can modify the plugins used inside the scolarite panel.
    |
    | For detailed instructions you can look the plugins section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Plugins-Configuration
    |
    */

    'plugins' => [
        'Datatables' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/datatables-plugins/buttons/js/dataTables.buttons.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/datatables-plugins/buttons/js/buttons.bootstrap4.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/datatables-plugins/buttons/js/buttons.html5.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/datatables-plugins/buttons/js/buttons.print.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/datatables-plugins/jszip/jszip.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/datatables-plugins/pdfmake/pdfmake.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/datatables-plugins/pdfmake/vfs_fonts.js',
                ],
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'vendor/datatables-plugins/buttons/css/buttons.bootstrap4.min.css',
                ],
            ],
        ],
        'Select2' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.css',
                ],
            ],
        ],
        'Chartjs' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.bundle.min.js',
                ],
            ],
        ],
        'Sweetalert2' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/sweetalert2@8',
                ],
            ],
        ],
        'Pace' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/blue/pace-theme-center-radar.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | IFrame
    |--------------------------------------------------------------------------
    |
    | Here we change the IFrame mode configuration. Note these changes will
    | only apply to the view that extends and enable the IFrame mode.
    |
    | For detailed instructions you can look the iframe mode section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/IFrame-Mode-Configuration
    |
    */

    'iframe' => [
        'default_tab' => [
            'url' => null,
            'title' => null,
        ],
        'buttons' => [
            'close' => true,
            'close_all' => true,
            'close_all_other' => true,
            'scroll_left' => true,
            'scroll_right' => true,
            'fullscreen' => true,
        ],
        'options' => [
            'loading_screen' => 1000,
            'auto_show_new_tab' => true,
            'use_navbar_items' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Livewire support.
    |
    | For detailed instructions you can look the livewire here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'livewire' => true,
];
