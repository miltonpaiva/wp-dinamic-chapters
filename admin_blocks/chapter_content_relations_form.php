<style>

	/* TABLE CSS */
	table {
	  font-family: arial, sans-serif;
	  border-collapse: collapse;
	  width: 100%;
	}

	td, th {
	  border: 1px solid #dddddd;
	  text-align: left;
	  padding: 8px;
	}

	tr:nth-child(even) {
	  background-color: #dddddd;
	}

	tr:hover {
	  background-color: #ccc;
	}

</style>

<!-- ===== ===== ===== ===== ===== ===== ===== ===== -->
<!-- BLOCO INSTANCIADO NA CLASSE [<?= get_class(); ?>] -->
<!-- ===== ===== ===== ===== ===== ===== ===== ===== -->

<table>
  <tr>
    <td> Pesquisar: </td>
    <td>
			<input type="text" name="" placeholder="digite o Capitulo aqui">
    </td>
  </tr>
  <tr>
    <th>Capitulo</th>
    <th>Bloco de template</th>
  </tr>

  <?php foreach ($chapters as $chapter): ?>
		  <tr>
		    <td>
					<label><?= $chapter->post_title; ?></label>
		    </td>
		    <td>
					<select name="chapters_content_info" style="width: 100%;" class="select_block_content" onfocusout="verifySelects(this)" <?= ($chapter->is_selected || $content_block == '')? '' : 'disabled' ; ?> >
						<option value="">selecione o bloco para inserir esse conteudo</option>

						<?php foreach ($chapter->blocks as $block): ?>
							<option value='{"linked_chapter": "<?= $chapter->post_name; ?>", "content_block": "<?= $block->post_name; ?>"}' <?= $block->is_selected? 'selected' : '' ; ?> >
								<?= $block->post_title; ?>
							</option>
						<?php endforeach; ?>

					</select>
		    </td>
		  </tr>

  <?php endforeach; ?>

  <?php if (count($chapters) == 0): ?>
		  <tr>
		    <td colspan="2">
					não há capitulos disponiveis.
		    </td>
		  </tr>
  <?php endif ?>

</table>

<!-- script para desabilitar os selects não selecionados -->
<script type="text/javascript">
	function verifySelects(current_select) {
		let selects = document.getElementsByClassName('select_block_content');
		for(key in selects){
			if (typeof selects[key] == 'object'){
				selects[key].disabled = (current_select.value != '')
			}
		}

		current_select.disabled = false
	}
</script>