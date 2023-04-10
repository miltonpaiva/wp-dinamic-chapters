<!-- ===== ===== ===== ===== ===== ===== ===== ===== -->
<!-- BLOCO INSTANCIADO NA CLASSE [<?= get_class(); ?>] -->
<!-- ===== ===== ===== ===== ===== ===== ===== ===== -->

<select name="chapter_template_slug" style="width: 100%;">
	<option value="" disabled>selecione um template para o capitulo</option>

	<?php foreach ($all_templates as $template): ?>
		<option value="<?= $template->post_name; ?>" <?= $template->is_selected? 'selected' : '' ; ?> >
			<?= $template->post_title; ?>
		</option>
	<?php endforeach; ?>

</select>