<?php

class WooLockerApiPostType {
    
    public static function createPostType() {

        $labels = array(
            'name'                => _x( 'Casiers', 'Post Type General Name', 'woolockerapi' ),
            'singular_name'       => _x( 'Casier', 'Post Type Singular Name', 'woolockerapi' ),
            'menu_name'           => __( 'WooLockers', 'woolockerapi' ),
            'parent_item_colon'   => __( 'Casier parent', 'woolockerapi' ),
            'all_items'           => __( 'Tous les casiers', 'woolockerapi' ),
            'view_item'           => __( 'Voir casier', 'woolockerapi' ),
            'add_new_item'        => __( 'Ajouter un nouveau casier', 'woolockerapi' ),
            'add_new'             => __( 'Ajouter', 'woolockerapi' ),
            'edit_item'           => __( 'Modifier casier', 'woolockerapi' ),
            'update_item'         => __( 'Mettre à jour casier', 'woolockerapi' ),
            'search_items'        => __( 'Chercher des casiers', 'woolockerapi' ),
            'not_found'           => __( 'Aucun élément', 'woolockerapi' ),
            'not_found_in_trash'  => __( 'Aucun élément dans la corbeille', 'woolockerapi' )
        );
     
        $args = array(
            'label'               => __( 'casiers', 'woolockerapi' ),
            'description'         => __( 'Casiers de distribution', 'woolockerapi' ),
            'labels'              => $labels,
            'supports'            => array( 'title' ), 
            'taxonomies'          => array_keys( WOO_LOCKER_API_POST_TYPE_TAXONOMIES ),
            'hierarchical'        => false,
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_nav_menus'   => true,
            'show_in_admin_bar'   => true,
            'menu_position'       => 56,
            'can_export'          => true,
            'has_archive'         => true,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'capability_type'     => 'post',
            'show_in_rest'        => true,
            'menu_icon'           => 'dashicons-lock'
        );

        register_post_type( WOO_LOCKER_API_POST_TYPE, $args );

        self::addWooLockerSettingsSubMenu();

        self::registerMetaBox();

        self::registerLockerTaxonomy();
        
    }

    private static function addWooLockerSettingsSubMenu(){
        add_action('admin_menu', 'add_woo_locker_settings_sub_menu');

        function add_woo_locker_settings_sub_menu(){
            add_submenu_page(
                'edit.php?post_type='.WOO_LOCKER_API_POST_TYPE,
                __('Réglages de WooLockers', 'woolockerapi'),
                __('Réglages', 'woolockerapi'),
                'manage_options',
                'admin.php?page=wc-settings&tab=shipping&section='.SHIPPING_METHOD_ID
            );
            /*add_submenu_page(
                'edit.php?post_type='.WOO_LOCKER_API_POST_TYPE,
                __('Custom', 'woolockerapi'),
                __('Custom', 'woolockerapi'),
                'manage_options',
                'cusrom-page',
                'custom_render_page'
            );*/
        }

        /*function custom_render_page(){
            echo '<pre>';
            print_r(get_pages());
            echo '</pre>';
        }*/

    }

    private static function registerMetaBox(){
        function woo_locker_custom_metabox(){
            add_meta_box( 
                'woo-locker-metabox',
                __('Informations concernant le casier', 'woolockerapi'),
                'woo_locker_custom_metabox_callback',
                WOO_LOCKER_API_POST_TYPE,
                'normal',
                'core'
            );
        }
         
        add_action('add_meta_boxes', 'woo_locker_custom_metabox');
         
        function woo_locker_custom_metabox_callback($post){
            ?>
            <style type="text/css">
                .woo-locker-form-label {
                    font-weight: 500;
                }

                .woo-locker-form-input {
                    font-size: 1.7em;
                    height: 1.7em;
                    width: 100%;
                    outline: 0;
                    margin: 12px 0 0;
                }

                .woo-locker-form-textarea {
                    display: block;
                    margin: 12px 0 0;
                    height: 4em;
                    width: 100%;
                }

                .woo-locker-form-info {
                    margin: 6px 0px 24px;
                }
            </style>
            <div id="woo_locker_id_wrap">
                <label class="woo-locker-form-label" for="_woo_locker_id"><?php echo __('Identifiant du casier', 'woolockerapi'); ?></label>
                <input class="woo-locker-form-input" type="text" name="_woo_locker_id" size="30" placeholder="<?php echo __('Saisissez l\'ID du casier', 'woolockerapi'); ?>" required value="<?php echo get_post_meta($post->ID, 'woo_locker_id', true); ?>" id="_woo_locker_id" spellcheck="true" autocomplete="off">
                <p class="woo-locker-form-info"><?php echo __("Les identifiants des casiers sont des identifiants uniques fournis par le prestataire de l'API à utiliser", "woolockerapi"); ?></p>
            </div>
            <hr style="margin-bottom: 24px;" />
            <div id="woo_locker_localisation_wrap">
                <label class="woo-locker-form-label" for="_woo_locker_localisation"><?php echo __('Localisation du casier', 'woolockerapi'); ?></label>
                <textarea size="40" class="woo-locker-form-textarea" rows="1" name="_woo_locker_localisation" size="30" placeholder="<?php echo __('Localisation du casier', 'woolockerapi'); ?>" required id="_woo_locker_localisation" spellcheck="true" autocomplete="off"><?php echo get_post_meta($post->ID, 'woo_locker_localisation', true); ?></textarea>
                <p class="woo-locker-form-info"><?php echo __("La localisation est une sorte d'adresse physique pour que les clients puissent retrouver facilement l'emplacement du casier", "woolockerapi"); ?></p>
            </div>
            <?php
        }

        function woo_locker_save_custom_metabox($post_id, $post){
         
            if(isset($_POST["_woo_locker_id"])):

                if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
                    return $post_id;
                }

                if( $post->post_type != WOO_LOCKER_API_POST_TYPE ) {
                    return $post_id;
                }            
                 
                update_post_meta($post->ID, 'woo_locker_id', esc_attr($_POST["_woo_locker_id"]));
             
            endif;

            if(isset($_POST["_woo_locker_localisation"])):

                if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
                    return $post_id;
                }

                if( $post->post_type != WOO_LOCKER_API_POST_TYPE ) {
                    return $post_id;
                }            
                 
                update_post_meta($post->ID, 'woo_locker_localisation', esc_attr($_POST["_woo_locker_localisation"]));
             
            endif;

            return $post_id;
        }
         
        add_action('save_post', 'woo_locker_save_custom_metabox', 10, 2);
    }

    private static function registerLockerTaxonomy(){
        foreach(WOO_LOCKER_API_POST_TYPE_TAXONOMIES as $slug => $name){

            $labels = array(
                "name" => __( "Prestataires", "textdomain" ),
                "singular_name" => __( "Prestataire", "textdomain" ),
                "menu_name" => __( "Prestataires", "textdomain" ),
                "all_items" => __( "Tous les prestataires", "textdomain" ),
                "add_new_item" => __( "Ajouter", "textdomain" ),
                "new_item_name" => __( "Nouveau prestataire", "textdomain" ),
                "edit_item" => __( "Modifier prestataire", "textdomain" ),
                "view_item" => __( "Voir prestataire", "textdomain" ),
                "update_item" => __( "Mettre à jour prestataire", "textdomain" ),
                "search_items" => __( "Rechercher des prestataires", "textdomain"),
                "not_found" => __( "Aucun prestataire trouvé", "textdomain")
            );

            $args = array(
                "labels" => $labels,
                "public" => true,
                "publicly_queryable" => true,
                "hierarchical" => false,
                "show_in_menu" => true,
                "query_var" => true,
                'show_admin_column'     => true,
                '_builtin'              => true,
                'capabilities'          => array(
                    'manage_terms' => 'manage_categories',
                    'edit_terms'   => 'edit_categories',
                    'delete_terms' => 'delete_categories',
                    'assign_terms' => 'assign_categories',
                ),
                "rewrite" => array('slug' => WOO_LOCKER_API_POST_TYPE),
                'show_in_rest'          => true,
                'rest_base'             => $slug,
                'rest_controller_class' => 'WP_REST_Terms_Controller'
            );

            register_taxonomy( $slug, WOO_LOCKER_API_POST_TYPE, $args );

        }
    }

}