<?php

require TEMPLATE_DIRETORY . '/src/BaseClass.php';
require TEMPLATE_DIRETORY . '/src/Templates.php';

/**
 * classe responsavel por gerenciar as informações dos capitulos
 */
class Chapters extends BaseClass
{
	public $templates;

	function __construct()
	{
		$this->defineHooks();

        $this->templates = new Templates();
	}

    /**
     * define os hooks dessa entidade
     * @return void
     */
	public function defineHooks(): void
	{
		// criando os post type de capitulos
        add_action('init', [&$this, 'createPostTypes' ]);

        // para criação e registro das infomrações do metabox na pagina de
        // inserção e edição dos capitulos
        add_action( 'add_meta_boxes', [&$this, 'createMetaBoxes' ]);
        add_action( 'save_post',      [&$this, 'saveMetaBoxData' ]);

         // inserindo uma nova coluna na listagem de capitulos
        add_filter( 'manage_chapters_posts_columns', [&$this, 'registerCustomColumn' ]);
        add_filter( 'manage_chapters_posts_custom_column', [&$this, 'showInfoCustomColumns' ], 10, 2 );
	}

	/**
	 * registra os post type de capitulos
	 * @return void
	 */
    public function createPostTypes(): void
    {
        // post type de capitulos
        register_post_type( 'chapters',
            array(
                'labels' => array(
                    'name'                  => __( 'Capitulos'),
                    'singular_name'         => __( 'Capitulo'),
                    'add_new_item'          => __( 'Adicionar novo Capitulo'),
                    'edit_item'             => __( 'Editar Capitulo'),
                    'search_items'          => __( 'Pesquisar Capitulo'),
                ),
                'supports'                  => array('title', 'thumbnail', 'excerpt'),
                'public'                    => true,
                'has_archive'               => false,
                'show_in_rest'              => true,
                'publicly_queryable'        => false,
                'query_var'                 => false,
                'menu_icon'                 => 'dashicons-welcome-add-page',
                'menu_position'             => 2
            )
        );

        // post type de conteudo de capitulos
        register_post_type( 'chapter_content',
            array(
                'labels' => array(
                    'name'                  => __( 'Conteúdo dos Capitulos'),
                    'singular_name'         => __( 'Conteúdo do Capitulo'),
                    'add_new_item'          => __( 'Adicionar novo Conteúdo'),
                    'edit_item'             => __( 'Editar Conteúdo'),
                    'search_items'          => __( 'Pesquisar Conteúdo'),
                ),
                'supports'                  => array('title', 'editor'),
                'public'                    => true,
                'has_archive'               => false,
                'show_in_rest'              => true,
                'publicly_queryable'        => false,
                'query_var'                 => false,
                'menu_icon'                 => 'dashicons-media-text',
                'menu_position'             => 3
            )
        );
    }

    /**
     * cria as meta box da pagina de capitulos
     * @return void
     */
    public function createMetaBoxes(): void
    {
    	$this->registerMetaBox('chapters_relations', 'Seleção de Capitulo Pai', 'showRelationsForm', ['chapters']);
    	$this->registerMetaBox('chapters_templates', 'Seleção de Template do Capitulo', 'showTemplatesForm', ['chapters']);
    }

    /**
     * exibe a meta box que define a arvore do capitulo
     * @param  object $current_post
     * @return void
     */
    public function showRelationsForm(object $current_post): void
    {
        // pegando algumas informações do capitulo atual
        $current_parent_chapter = get_post_meta( $current_post->ID, 'parent_chapter', true );
        $current_chapter_tree   = get_post_meta( $current_post->ID, 'chapter_tree', true );

        // pegando todos os capitulos
        $chapters = $this->getAllChapters();

        // tratamento da arvore atual do capitulo atual
        $current_post->tree = $current_chapter_tree ?: "/{$current_post->post_name}";

        // percorrendo todos os capitulos
        foreach ($chapters as $key => $chapter) {

            // removendo o capitulo atual da lista de capitulos disponiveis
            if ($chapter->ID == $current_post->ID) { unset($chapters[$key]); continue; }

            // definindo se o site é o selecionado
            $is_selected = ($current_parent_chapter == $chapter->post_name);
            $chapters[$key]->is_selected = $is_selected;

            // validando que não seja possivel cadastrar um capitulo pai dentro de um capitulo filho
            $is_invalid_tree = (strpos($chapter->tree, $current_post->tree) !== false);
            if (!$is_selected && $is_invalid_tree && $current_post->tree != '/') { unset($chapters[$key]); continue; }

            // tratamendo de como vai ficar a arvore do capitulo atual
            // se ele selecionar esse capitulo como pai
            $chapters[$key]->tree .= "/{$current_post->post_name}";

            $chapter_info =
            [
                'post_name'    => $chapter->post_name,
                'chapter_tree' => $chapters[$key]->tree,
            ];

            $chapters[$key]->info = json_encode($chapter_info, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        require ADMIN_BLOCKS_DIRETORY . '/chapters_relations_form.php';
    }

    /**
     * exibe a meta box que define o template do capitulo
     * @param  object $current_post
     * @return void
     */
    public function showTemplatesForm(object $current_post): void
    {
        $current_chapter_template_slug = get_post_meta( $current_post->ID, 'chapter_template_slug', true );

        $all_templates = $this->templates->getAllTemplates();

        // validando o template selecionado
        foreach ($all_templates as $key => $template) {
    		$all_templates[$key]->is_selected = ($template->post_name == $current_chapter_template_slug);
        }

        require ADMIN_BLOCKS_DIRETORY . '/chapters_templates_form.php';
    }

    /**
     * salva os dados vindos das meta box
     * @param  int    $post_id
     * @return void
     */
    public function saveMetaBoxData(int $post_id): void
    {
        // validando o post type
        if ( $_REQUEST['post_type'] != 'chapters' ) return;

        // pegando o json de informações da requisição
        $parent_chapter_info = $_REQUEST['parent_chapter_info'] ?? '[]';
        $parent_chapter_info = str_replace('\"', '"', $parent_chapter_info);

        // obtendo as informações
        $info = json_decode($parent_chapter_info, true);

        // pegando o post atual
        $current_post = get_post($post_id);

        $parent_chapter = $info['post_name']    ?? '';
        $chapter_tree   = $info['chapter_tree'] ?? "/{$current_post->post_name}";

        update_post_meta( $post_id, 'parent_chapter', $parent_chapter);
        update_post_meta( $post_id, 'chapter_tree', $chapter_tree);

        // pegando o slug do template pela url ou definindo um padrão
        $template_slug = $_REQUEST['chapter_template_slug'] ?? current($this->templates)['slug'];

        update_post_meta( $post_id, 'chapter_template_slug', $template_slug);
    }

    /**
     * define novas colunas na listagem de capitulos
     * @param  array  $columns
     * @return array
     */
    public function registerCustomColumn( array $columns ): array
    {

        $columns['parent_chapter']        = 'Capitulo Pai';
        $columns['chapter_tree']          = 'Arvore';
        $columns['chapter_template_slug'] = 'Template';

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
        if ( $column_name === 'chapter_tree' ) {
            echo esc_html( get_post_meta( $post_id, 'chapter_tree', true ) ) ?: '—';
        }

        if ( $column_name === 'parent_chapter' ) {
            echo esc_html( get_post_meta( $post_id, 'parent_chapter', true ) ) ?: '—';
        }

        if ( $column_name === 'chapter_template_slug' ) {
            echo esc_html( get_post_meta( $post_id, 'chapter_template_slug', true ) ) ?: '—';
        }
    }

    /**
     * retorna toda a lista dos capitulos com algumas informações a mais
     * @return array
     */
    public function getAllChapters(): array
    {
        $chapters = $this->getPostsByTax('chapters');

        // percorrendo todos os capitulos
        foreach ($chapters as $key => $chapter) {

			$chapter = $this->getChapterMoreDara($chapter);

            $chapters[$key] = $chapter;
        }

        return $chapters;
    }

    /**
     * retorna alfuns dados e metadados do capitulo infomrado
     * @param  object $chapter
     * @return object
     */
    public function getChapterMoreDara(object $chapter): object
    {
        // pegando algumas informações do capitulo
        $parent_chapter        = get_post_meta( $chapter->ID, 'parent_chapter', true );
        $chapter_tree          = get_post_meta( $chapter->ID, 'chapter_tree', true );
        $chapter_template_slug = get_post_meta( $chapter->ID, 'chapter_template_slug', true );

        $chapter->parent_post_name = $parent_chapter;
        $chapter->template_slug    = $chapter_template_slug;

        // pegando/definindo a arvore do capitulo
        $chapter->tree = $chapter_tree ?: "/{$chapter->post_name}";

        // pegando o nivel com base na arvore
        $chapter->level = substr_count($chapter->tree, '/', 1);

        return $chapter;
    }

    /**
     * retorna um capitulo com base no post_name passado
     * @param  string $post_name
     * @return object
     */
	public function getChapterByPostName(string $post_name) {

        $args = [
            'post_type' => 'chapters',
	        "name"      => $post_name,
        ];

	    $query = new WP_Query($args);

	    $chapter = $query->have_posts() ? reset($query->posts) : new stdClass();

	    $chapter = $this->getChapterMoreDara($chapter);

	    return $chapter;
	}
}