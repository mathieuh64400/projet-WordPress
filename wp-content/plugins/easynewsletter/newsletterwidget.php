<?php
class NewsletterWidget extends WP_Widget
{

	// Surcharge obligatoire du constructer du widget : 
	// identificant : le nom de la classe 'Easy_newsletter'
	// name : le titre pour l’administration 'Easy Newsletter'
	// widget_options : une description pour la classe 
	public function __construct()
	{
		$widget_ops = array(
			'classname' => 'NewsletterWidget',
			'description' => 'Un formulaire d\'inscription à la newsletter.',
		);
		parent::__construct('NewsletterWidget', 'Easy Newsletter', $widget_ops);
	}

	// Méthode de rendu du widget
	// On récupère les paramètres grâce à $instance
	public function widget($args, $instance)
	{
		echo $args['before_widget'];
		echo $args['before_title'];
		echo apply_filters('widget_title', $instance['title']);
		echo $args['after_title'];

		echo "<form action='' method='post'>",
				"<p>",
					"<label for='easy_newsletter_email'>Votre email :</label>",
					"<input id='easy_newsletter_email' name='easy_newsletter_email' type='email' />",
				"</p>",
				"<input type='submit' value='Envoyer' />",
			 "</form>";

		echo $args['after_widget'];
	}

	// Méthode de gestion des paramètre dans l'admin
	// On gère les paramètres grâce à $instance
	public function form($instance)
	{
		$title = !empty($instance['title']) ? $instance['title'] : '';
		$id = esc_attr($this->get_field_id('title'));
		$name = esc_attr($this->get_field_name('title'));
		$label = esc_attr_e('Title:', 'text_domain');
		$value = esc_attr($title);
		echo "<p>",
			"<label for='$id'>$label</label>",
			"<input class='widefat' id='$id' name='$name' type='text' value='$value'>",
			"</p>";
	}

	//Méthode d'enregistrement des options
	public function update($new_instance, $old_instance)
	{
		$instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        return $instance;
    }
}