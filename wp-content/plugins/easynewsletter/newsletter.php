<?php
    class Newsletter
    {
        public function __construct()
        {
            // inscription du widget
        include_once plugin_dir_path(__FILE__) . '/newsletterwidget.php';
        add_action('widgets_init', function () {
            register_widget('NewsletterWidget');
        });
        // inscription de la fonction save_email au chargement de la page
        add_action('wp_loaded', array($this, 'save_email'));
        }
        public static function install()
        {
            //variable global d'accès à la base de données
            global $wpdb;
            //exécution de la requete de création de la table 
            $wpdb->query("CREATE TABLE IF NOT EXISTS {$wpdb->prefix}easy_newsletter ".
                        "(id INT AUTO_INCREMENT PRIMARY KEY, email VARCHAR(255) NOT NULL);");
        }

        public static function uninstall()
        {
            global $wpdb;
            $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}easy_newsletter;");
        }

        public function save_email()
    {
        // si la variable POST de soumission existe
        if (isset($_POST['easy_newsletter_email']) && !empty($_POST['easy_newsletter_email'])) {
            //recup de l'email saisi
            $email = $_POST['easy_newsletter_email'];
            //enregistrement de l'email si n'existe pas encore
            global $wpdb;
            $row = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}easy_newsletter_email WHERE email = '$email'");
            if (is_null($row)) {
                $wpdb->insert("{$wpdb->prefix}easy_newsletter_email", array('email' => $email));
            }
        }
    }

}
   