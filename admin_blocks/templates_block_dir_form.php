<!-- ===== ===== ===== ===== ===== ===== ===== ===== -->
<!-- BLOCO INSTANCIADO NA CLASSE [<?= get_class(); ?>] -->
<!-- ===== ===== ===== ===== ===== ===== ===== ===== -->

<label for="template_block_archive_name">
	<strong>
		<?= DEFAULT_TEMPLATES_BLOCKS_DIR; ?>
	</strong>
</label>
<input type="text" name="template_block_archive_name" id="template_block_archive_name" placeholder="Nome do Arquivo" value="<?= $template_block_archive_name ?? ''; ?>">