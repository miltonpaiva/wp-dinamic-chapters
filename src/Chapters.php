<?php


/**
 * classe responsavel por gerenciar as informações dos capitulos
 */
class Chapters
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
		// criando o post type de capitulos
        add_action('init', [&$this, 'createPostType' ]);

        // para criação e registro das infomrações do metabox na pagina de
        // inserção e edição dos capitulos
        add_action( 'add_meta_boxes', [&$this, 'createMetaBoxes' ]);
        add_action( 'save_post',      [&$this, 'saveMetaBoxData' ]);
	}

	/**
	 * registra o post type de capitulos
	 * @return void
	 */
    public function createPostType(): void
    {
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
                'menu_icon'                 => 'dashicons-text-page',
                'menu_position'             => 2
            )
        );
    }

    /**
     * cria as meta box da pagina de capitulos
     * @return void
     */
    public function createMetaBoxes(): void
    {
    	$this->registerMetaBox('chapters_relations', 'Seleção de Capitulo Pai', 'showRelationsForm');
    	$this->registerMetaBox('chapters_templates', 'Seleção de Template do Capitulo', 'showTemplatesForm');
    }

	/**
	 * cria a meta box dentro da pagina do post type de capitulos
	 * @return void
	 */
    public function registerMetaBox(string $id, string $title, string $callback_name): void
    {
        $callback  = [&$this, $callback_name];
        $post_type = 'chapters';
        $context   = 'normal';
        $priority  = 'default';

        // $callback_args = null
        add_meta_box($id ,$title, $callback, $post_type, $context, $priority);
    }

    /**
     * faz algumas mudanças nas informações do capitulo atual e
     * da lista e exibe o formulario da meta box de relação
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

        require TEMPLATE_DIRETORY . '/admin_blocs/chapters_relations_form.php';
    }

    /**
     * faz algumas mudanças nas informações do capitulo atual e
     * da lista e exibe o formulario da meta box de relação
     * @param  object $current_post
     * @return void
     */
    public function showTemplatesForm(object $current_post): void
    {
        $current_chapter_template_slug = get_post_meta( $current_post->ID, 'chapter_template_slug', true );

        // validando o template selecionado
        foreach ($this->templates as $key => $template) {
    		$this->templates[$key]['is_selected'] = ($template['slug'] == $current_chapter_template_slug);
        }

        require TEMPLATE_DIRETORY . '/admin_blocs/chapters_templates_form.php';
    }

    /**
     * salva os dados vindos das meta box
     * @param  int    $post_id
     * @return void
     */
    public function saveMetaBoxData(int $post_id): void
    {
        // pegando o json de informações da requisição
        $parent_chapter_info = $_REQUEST['parent_chapter_info'] ?? '[]';
        $parent_chapter_info = str_replace('\"', '"', $parent_chapter_info);

        // obtendo as informações
        $info = json_decode($parent_chapter_info, true);

        // pegando o post atual
        $current_post = get_post($post_id);

        $parent_chapter = $info['post_name'] ?? '';
        $chapter_tree   = $info['chapter_tree'] ?? "/{$current_post->post_name}";

        update_post_meta( $post_id, 'parent_chapter', $parent_chapter);
        update_post_meta( $post_id, 'chapter_tree', $chapter_tree);

        // pegando o slug do template pela url ou definindo um padrão
        $template_slug = $_REQUEST['chapter_template_slug'] ?? current($this->templates)['slug'];

        update_post_meta( $post_id, 'chapter_template_slug', $template_slug);
    }

    /**
     * retorna toda a lista dos capitulos com algumas informações a mais
     * @return array
     */
    public function getAllChapters(): array
    {
        $chapters = $this->getChaptersByTax('chapters')->posts ?? [];

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
     * retorna uma lista com os capitulos cadastrados em determinada categoria
     *
     * @param string  $post_type
     * @param integer $qtd
     * @return object
     */
    public function getChaptersByTax(string $post_type, int $qtd = -1, $taxonomy = false, $session_tax_term = false): object
    {
        $args = [
            'post_type'   => $post_type, 'posts_per_page' => $qtd,
            'post_status' => 'publish',
            'orderby' => 'id', 'order' => 'DESC',
        ];

        if ($taxonomy && $session_tax_term) {
            $args['tax_query'] = [
                [
                    'taxonomy' => $taxonomy,
                    'field'    => 'slug',
                    'terms'    => [$session_tax_term],
                ]
            ];
        }

        $loop = new WP_Query( $args );

        return $loop ?? new stdClass();
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