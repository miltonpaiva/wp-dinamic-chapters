<?php

/**
 * Classe base com metodos em comum para as outras classes
 */
class BaseClass
{

	/**
	 * cria a meta box dentro da pagina do post type indicado
	 * @return void
	 */
    public function registerMetaBox(string $id, string $title, string $callback_name, array $post_type): void
    {
        $callback  = [&$this, $callback_name];
        $context   = 'normal';
        $priority  = 'default';

        // chamando a função nativa para criação do meta box
        add_meta_box($id ,$title, $callback, $post_type, $context, $priority);
    }

    /**
     * retorna uma lista com os posts cadastrados em determinada categoria
     *
     * @param string  $post_type
     * @param integer $qtd
     * @return array
     */
    public function getPostsByTax(string $post_type, int $qtd = -1, $taxonomy = false, $session_tax_term = false): array
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

        return $loop->posts ?? [];
    }

    /**
     * retorna uma lista de posts com base nos valores meta passados
     * @param  string      $post_type
     * @param  string      $meta_key
     * @param  string      $meta_value
     * @param  int|integer $qtd
     * @return array
     */
    public function getPostsByMeta(string $post_type, string $meta_key, string $meta_value, int $qtd = -1): array
    {
        $args =
        [
            'post_type'      => $post_type,
            'posts_per_page' => $qtd,
            'post_status'    => 'publish',
            'orderby'        => 'id',
            'order'          => 'DESC',
            'meta_key'       => $meta_key,
            'meta_value'     => $meta_value,
        ];

        $loop = new WP_Query( $args );

        return $loop->posts ?? [];
    }
}