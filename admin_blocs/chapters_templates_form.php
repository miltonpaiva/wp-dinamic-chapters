<select name="chapter_template_slug" style="width: 100%;">
	<option value="" disabled>selecione um template para o capitulo</option>

	<?php foreach ($this->templates as $template): ?>
		<option value="<?= $template['slug']; ?>" <?= $template['is_selected']? 'selected' : '' ; ?> >
			<?= $template['name']; ?>
		</option>
	<?php endforeach; ?>

</select>