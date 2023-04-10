<?php
/**
 * TestePath
 *
 * @author 1000TU - Milton Paiva <miltonpaiva268@gmail.com | miltonpaiva@opovodigital.com>
 *
 */

define('TEMPLATE_DIRETORY_URI', get_template_directory_uri());
define('TEMPLATE_DIRETORY',     get_template_directory());
define('ADMIN_BLOCKS_DIRETORY', get_template_directory() . '/admin_blocks');

require TEMPLATE_DIRETORY . '/src/Theme.php';
require TEMPLATE_DIRETORY . '/src/Chapters.php';

class TestePath {

    public $theme_name = 'TestePath';

    public $theme;
    public $chapters;

    /**
     * Autoload method
     * @return void
     */
    public function __construct() {

        /*------------------------------------*\
            Theme custom pages + endpoints
        \*------------------------------------*/

        $this->theme     = new Theme();
        $this->chapters  = new Chapters();

        // ações para gerir as urls dos sites com base na arvore e nos templates
        add_action( 'init', array(&$this, 'addCustomEndpoints'));
        add_filter( 'query_vars', array(&$this, 'addEndpointsQueryvars' ));
        add_action( 'template_include', array(&$this, 'verifyAndReplaceTemplates' ), 1000, 1);
    }

    /**
     * adiciona uma regra para cada capitulo criado conforme seu nivel
     * @return void
     */
    public function addCustomEndpoints(): void
    {
        // listando os capitulos
        $chapters = $this->chapters->getAllChapters();

        foreach ($chapters as $chapter) {
            $rewrite_arr = [];

            // montando um array pora o regex
            // com base no nivel da arvore/url do capitulo
            for ($index=0; $index <= $chapter->level ; $index++) {
                $rewrite_arr[] = '([^/]+)';
            }

            // montando o regex e definindo em que nivel na url está
            // o parametro que recebe o post_name do capitulo
            $rewrite_str  = implode('/', $rewrite_arr) . '/?$';
            $paramn_match = 'index.php?chapter_post_name=$matches[' . ($chapter->level +1) . ']';

            // adicionando regra de url
            add_rewrite_rule( $rewrite_str, $paramn_match, 'top' );
        }
    }

    /**
     * insere nas variaveis da url o
     * parametro(post_name) esperado nos endpoints
     * informado atravez do rewrite na função addCustomEndpoints()
     * @param  array $query_vars
     * @return array
     */
    public function addEndpointsQueryvars( array $query_vars ): array
    {
        $query_vars[] = 'chapter_post_name';

        return $query_vars;
    }

    /**
     * verifica se na url há um parametro e se o
     * parametro em questão é de um capitulo
     * se positivo retorna o template do arquivo em questão
     * @param  string $default_template
     * @return string
     */
    public function verifyAndReplaceTemplates( string $default_template ): string
    {
        $is_custom_endpoint = false;

        $post_name = get_query_var( 'chapter_post_name' ) ?? null;

        $chapter = $this->chapters->getChapterByPostName($post_name);

        // verifica se é algum endpoint customizado, se for substitui o template atual
        foreach ($this->chapters->templates->getAllTemplates() as $template) {

            if ($template->post_name == $chapter->template_slug) {
                $is_custom_endpoint = true;
                $custom_template    = $template->archive_dir;
                break;
            }

        }

        // se não for um endpoint customizado ele retorna o padrão
        if (!$is_custom_endpoint || !$post_name) return $default_template;

        // removendo o status de erro
        http_response_code(200);
        return $custom_template ?? $default_template;
    }
}

// instancia da classe principal
// ao chamar o arquivo de functions
$TestePath = new TestePath();

?>
