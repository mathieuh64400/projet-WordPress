<?php
/*
Plugin Name: Easy Newsletter
Plugin URI: https://github.com/FabriqueNumeriquePau
Description: Easy, le développement facile de plugin sous WordPress
Version: 0.1
Author: Simplon Pau
License: GNU GPL2
*/

class EasyPlugin
{
    public function __construct()
    {
        //instancie la classe de base
        include_once plugin_dir_path(__FILE__) . '/newsletter.php';
        new Newsletter();

        //inscription de l'install et de l'uninstall 
        register_activation_hook(__FILE__, array('Newsletter', 'install'));
        register_uninstall_hook(__FILE__, array('Newsletter', 'uninstall'));

        //inscription du menu d'administration (colonne de gauche)
        add_action('admin_menu', array($this, 'add_admin_menu'));

        //inscription de la fonction de création des options du plugin
        add_action('admin_init', array($this, 'register_settings'));

        //pour configurer phpmailer pour l'envoi des mail
        add_action( 'phpmailer_init', array($this, 'mailer_config'));
        //et une gestion des logs d'erreurs
        add_action('wp_mail_failed', array($this, 'log_mailer_errors'));
    }

    //méthode d'inscription du menu d'administration
    public function add_admin_menu()
    {
        $id_menu = "easyNewsletterPlugin";
        $titrePage = "Easy Newsletter Plugin";
        $titreMenu = "Easy Newsletter";
        $droit = "manage_options";
        add_menu_page($titrePage, $titreMenu, $droit, $id_menu, array($this, 'menu_admin_html'));
    }

    // ============================================================
    // Formulaire d'administration

    //méthode d'inscriptions des options
    public function register_settings()
    {
        //inscriptions des options pour le groupe easy_newsletter_settings
        register_setting('easy_newsletter_settings', 'easy_newsletter_sender');
        register_setting('easy_newsletter_settings', 'easy_newsletter_object');
        register_setting('easy_newsletter_settings', 'easy_newsletter_content');

        //inscription d'une section de saisie des paramètres de la newsletter
        add_settings_section('easy_newsletter_section', 'Newsletter parameters', array($this, 'section_html'), 'easy_newsletter_settings');

        //inscriptions des paramètres pour la gestion du rendu automatique
        add_settings_field('easy_newsletter_sender', 'Expéditeur', array($this, 'sender_html'), 'easy_newsletter_settings', 'easy_newsletter_section');
        add_settings_field('easy_newsletter_object', 'Objet', array($this, 'object_html'), 'easy_newsletter_settings', 'easy_newsletter_section');
        add_settings_field('easy_newsletter_content', 'Contenu', array($this, 'content_html'), 'easy_newsletter_settings', 'easy_newsletter_section');
    }

    //rendu de l'entête de la section
    public function section_html()
    {
        echo 'Renseignez les paramètres d\'envoi de la newsletter.';
    }

    //rendu de chaque options
    public function sender_html()
    {
        $name = 'easy_newsletter_sender';
        $value = get_option($name);
        echo "  <input type='text' name='$name' value='$value'/>";
    }

    public function object_html()
    {
        $name = 'easy_newsletter_object';
        $value = get_option($name);
        echo "  <input type='text' name='$name' value='$value'/>";
    }

    public function content_html()
    {
        $name = 'easy_newsletter_content';
        $value = get_option($name);
        echo "  <textarea  type='text' name='$name'>$value</textarea>";
    }

    //méthode d'affichage de la page d'administration du plugin
    public function menu_admin_html()
    {
        echo '<h1>' . get_admin_page_title() . '</h1>';
        echo '<p>Bienvenue sur la page d\'accueil du plugin</p>';

        //formulaire d'enregistrement des options, utilisant le mécanisme propre de WP
        //le script options.php sauvegarde les options dans la table wp_options
        echo "<form method='post' action='options.php'>";

        //inscription du groupe d'options du plugin
        settings_fields('easy_newsletter_settings');

        //champs du formulaire pour chaque option
        do_settings_sections('easy_newsletter_settings');

        submit_button();
        echo "</form>";

        
        echo "<hr/>";
        echo "<div style='display:flex;'>";
        //Formulaire d'affichage des emails
        echo "  <form method='post' action=''>";
        echo "      <input type='hidden' name='voir_emails' value='1'/>";
        submit_button('Afficher les emails inscrits');
        echo "  </form>&nbsp;&nbsp;";

        //Formulaire d'envoi de la newsletter
        echo "  <form method='post' action=''>";
        echo "     <input type='hidden' name='send_newsletter' value='1'/>";
        submit_button('Envoyer la newsletter');
        echo "  </form>";
        echo "</div>";

        //traitement des actions
        if (isset($_POST['voir_emails'])) {
            echo "<h2>Liste des emails inscrits</h2>";
            $this->voir_emails();
        }

        if (isset($_POST['send_newsletter'])) {
            echo "<h2>Envoi des emails</h2>";
            $this->send_newsletter();
        }
    }

    // ============================================================
    // Affichage des emails et envoi de la Newsletter

    public function voir_emails()
    {
        global $wpdb;
        $recipients = $wpdb->get_results("SELECT email FROM {$wpdb->prefix}easy_newsletter");
        foreach ($recipients as $_recipient) {
            echo "<p>" . $_recipient->email . "</p>";
        }
    }

    public function send_newsletter()
    {
        $object = get_option('easy_newsletter_object', 'Newsletter');
        $content = get_option('easy_newsletter_content', 'Mon contenu');
        $sender = get_option('easy_newsletter_sender', 'no-reply@example.com');
        $header = array('From: ' . $sender);

        global $wpdb;
        $recipients = $wpdb->get_results("SELECT email FROM {$wpdb->prefix}easy_newsletter");
        foreach ($recipients as $_recipient) {
            $result = wp_mail($_recipient->email, $object, $content, $header);
            echo "<p>Envoi à " . $_recipient->email . " : " .  $result, "</p>";
        }
    }

    //fonction de configuration de PHPMailer
    function mailer_config(PHPMailer $mailer){
        $mailer->isSMTP(); // Paramétrer le Mailer pour utiliser SMTP 
        $mailer->Host = 'mail.gandi.net'; // Spécifier le serveur SMTP
        $mailer->SMTPAuth = true; // Activer authentication SMTP
        $mailer->Username = 'email'; // Votre adresse email d'envoi
        $mailer->Password = 'password'; // Le mot de passe de cette adresse email
        $mailer->SMTPSecure = 'ssl'; // Accepter SSL
        $mailer->Port = 465;
        $mailer->SMTPDebug = 0; // 0 : pa s de trace - 2: toutes les traces 
        $mailer->CharSet  = "utf-8";
    }

    //function de traces et d'erreur dans le fichier mail.log local
    function log_mailer_errors( $wp_error ){
        $fn = plugin_dir_path(__FILE__) . '/mail.log'; 
        $fp = fopen($fn, 'a');
        fputs($fp, "Mailer Error : " . $wp_error->get_error_message() ."\n");
        fclose($fp);
    }

}

new EasyPlugin(); //chargement du plugin