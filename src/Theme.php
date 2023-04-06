<?php


/**
 * classe responsavel por gerenciar as informações do tema
 */
class Theme
{

	function __construct()
	{
		$this->defineHooks();
	}

	/**
	* define os hooks dessa entidade
	* @return void
	*/
	public function defineHooks(): void
	{
		/*------------------------------------*\
			Theme Support + removes
		\*------------------------------------*/

		if (function_exists('add_theme_support')) {
			// Add Menu Support
			add_theme_support('menus');

         // Add Thumbnail Theme Support
         add_theme_support('post-thumbnails');
		}

      // Remove wp emoji
		remove_action('wp_head', 'print_emoji_detection_script', 7);
		remove_action('wp_print_styles', 'print_emoji_styles');
		remove_action('admin_print_scripts', 'print_emoji_detection_script');
		remove_action('admin_print_styles', 'print_emoji_styles');


		/*------------------------------------*\
			hooks
		\*------------------------------------*/

		add_action('wp_enqueue_scripts', array(&$this, 'defineAssets'));
		add_action('admin_menu', array(&$this, 'removeMenu'));
	}

	/**
	 * insere os assets na pagina quando as
	 * funções wp_head() e wp_footer() é chamada
	 * @return void
	 */
	public function defineAssets(): void
	{
        // CSS
        wp_enqueue_style('style', TEMPLATE_DIRETORY_URI . '/style.css');
	}

	/**
	 * remove alguns itens do menu
	 * @return void
	 */
   public function removeMenu(): void
   {
       remove_menu_page('edit.php');                // Posts
       remove_menu_page('edit.php?post_type=page'); // Pages
       remove_menu_page('edit-comments.php');       // Comments
       remove_menu_page('index.php');               // Dashboard
   }

}