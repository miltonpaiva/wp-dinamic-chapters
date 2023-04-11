<?php

define('DEFAULT_TEMPLATES_DIR',        TEMPLATE_DIRETORY . '/templates/');
define('DEFAULT_TEMPLATES_BLOCKS_DIR', TEMPLATE_DIRETORY . '/templates/blocks/');

/**
 * classe responsavel por gerenciar as informações dos templates
 * de capitulose as sessões dos conteudos dos capitulos
 */
class Templates extends BaseClass
{
	public $templates;

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
		// criando o post type de templates e blocos de template
        add_action('init', [&$this, 'createPostTypes' ]);

        // para criação e registro das infomrações do metabox na pagina de
        // inserção e edição dos templates e sessões
        add_action( 'add_meta_boxes', [&$this, 'createMetaBoxes' ]);
        add_action( 'save_post',      [&$this, 'saveMetaBoxData' ]);

         // inserindo uma nova coluna na listagem de blocos de template
        add_filter( 'manage_template_block_posts_columns', [&$this, 'registerCustomColumn' ]);
        add_filter( 'manage_template_block_posts_custom_column', [&$this, 'showInfoCustomColumns' ], 10, 2 );
	}

	/**
	 * registra os post type de templates
	 * @return void
	 */
    public function createPostTypes(): void
    {
        // post type de templates
        register_post_type( 'templates',
            array(
                'labels' => array(
                    'name'                  => __( 'Templates'),
                    'singular_name'         => __( 'Template'),
                    'add_new_item'          => __( 'Adicionar novo Template'),
                    'edit_item'             => __( 'Editar Template'),
                    'search_items'          => __( 'Pesquisar Template'),
                ),
                'supports'                  => array('title'),
                'public'                    => true,
                'has_archive'               => false,
                'show_in_rest'              => true,
                'publicly_queryable'        => false,
                'query_var'                 => false,
                'menu_icon'                 => 'dashicons-admin-page',
                'menu_position'             => 4
            )
        );

        // post type de blocos de template
        register_post_type( 'template_block',
            array(
                'labels' => array(
                    'name'                  => __( 'Blocos dos Templates'),
                    'singular_name'         => __( 'Bloco do Template'),
                    'add_new_item'          => __( 'Adicionar novo Bloco'),
                    'edit_item'             => __( 'Editar Bloco'),
                    'search_items'          => __( 'Pesquisar Bloco'),
                ),
                'supports'                  => array('title'),
                'public'                    => true,
                'has_archive'               => false,
                'show_in_rest'              => true,
                'publicly_queryable'        => false,
                'query_var'                 => false,
                'menu_icon'                 => 'dashicons-welcome-widgets-menus',
                'menu_position'             => 5
            )
        );
    }

    /**
     * cria as meta box da pagina de capitulos
     * @return void
     */
    public function createMetaBoxes(): void
    {
    	$this->registerMetaBox('template_dir', 'Caminho do Arquivo', 'showTemplateDirForm', ['templates']);
        $this->registerMetaBox('template_block_dir', 'Caminho do Arquivo', 'showTemplateBlockDirForm', ['template_block']);
        $this->registerMetaBox('template_block_relations', 'Seleção do Template', 'showTemplateBlockSelectionForm', ['template_block']);
    }

    /**
     * exibe o bloco de formulario para
     * capiturar o caminho do arquivo de template
     * @param  object $current_post
     * @return void
     */
    public function showTemplateDirForm(object $current_post): void
    {
        $template_archive_name = get_post_meta( $current_post->ID, 'template_archive_name', true );
        require ADMIN_BLOCKS_DIRETORY . '/templates_dir_form.php';
    }

    /**
     * exibe o bloco de formulario para
     * capiturar o caminho do arquivo do bloco de template
     * @param  object $current_post
     * @return void
     */
    public function showTemplateBlockDirForm(object $current_post): void
    {
        $template_block_archive_name = get_post_meta( $current_post->ID, 'template_block_archive_name', true );
        require ADMIN_BLOCKS_DIRETORY . '/templates_block_dir_form.php';
    }

    /**
     * exibe o bloco de formulario para
     * capiturar o caminho do arquivo do bloco de template
     * @param  object $current_post
     * @return void
     */
    public function showTemplateBlockSelectionForm(object $current_post): void
    {
        $current_template_block_template_slug = get_post_meta( $current_post->ID, 'template_block_template_slug', true );

        $all_templates = $this->getAllTemplates();

        // validando o template selecionado
        foreach ($all_templates as $key => $template) {
            $all_templates[$key]->is_selected = ($template->post_name == $current_template_block_template_slug);
        }

        require ADMIN_BLOCKS_DIRETORY . '/templates_block_selection_form.php';
    }

    /**
     * salva os dados vindos das meta box
     * @param  int    $post_id
     * @return void
     */
    public function saveMetaBoxData(int $post_id): void
    {
        // validando o post type
        if ( $_REQUEST['post_type'] == 'templates' ){
            update_post_meta( $post_id, 'template_archive_name', $_REQUEST['template_archive_name'] ?? '');
        }

        // validando o post type
        if ( $_REQUEST['post_type'] == 'template_block' ){
            update_post_meta( $post_id, 'template_block_archive_name', $_REQUEST['template_block_archive_name'] ?? '');
            update_post_meta( $post_id, 'template_block_template_slug', $_REQUEST['template_block_template_slug'] ?? '');
        }
    }

    /**
     * define uma nova coluna na listagem de blocos de template
     * @param  array  $columns
     * @return array
     */
    public function registerCustomColumn( array $columns ): array
    {
        $columns['template_block_template_slug'] = 'Template';

        return $columns;
    }

    /**
     * alimenta os valores das novas colunas inseridas anteriormente pela função [registerCustomColumn]
     * @param  string $column_name
     * @param  int    $post_id
     * @return void
     */
    public function showInfoCustomColumns(string $column_name, int $post_id ): void
    {
        if ( $column_name === 'template_block_template_slug' ) {
            echo esc_html( get_post_meta( $post_id, 'template_block_template_slug', true ) ) ?: '—';
        }
    }

    /**
     * retorna a lista dos templates
     * @return array
     */
    public function getAllTemplates(): array
    {
        $templates = $this->getPostsByTax('templates');

        foreach ($templates as $key => $template) {
            $archive_name = get_post_meta( $template->ID, 'template_archive_name', true );

            $templates[$key]->archive_dir = DEFAULT_TEMPLATES_DIR . $archive_name;
        }

        return $templates;
    }
}