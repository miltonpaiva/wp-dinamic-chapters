<!-- ===== ===== ===== ===== ===== ===== ===== ===== -->
<!-- BLOCO INSTANCIADO NA CLASSE [<?= get_class(); ?>] -->
<!-- ===== ===== ===== ===== ===== ===== ===== ===== -->

<label for="template_archive_name">
	<strong>
		<?= DEFAULT_TEMPLATES_DIR; ?>
	</strong>
</label>
<input type="text" name="template_archive_name" id="template_archive_name" placeholder="Nome do Arquivo" value="<?= $template_archive_name ?? ''; ?>">