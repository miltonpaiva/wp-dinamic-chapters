<style>
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
	</style>
</head>
<body>

<table>
  <tr>
    <td>
		Pesquisar:
    </td>
    <td>
		<input type="text" name="" placeholder="digite o Capitulo aqui">
    </td>
    <td>
		<strong>Arvore atual: </strong> <?= $current_post->tree; ?>
    </td>
  </tr>
  <tr>
    <th>*</th>
    <th>Capitulo</th>
    <th>Nova Arvore</th>
  </tr>

  <?php foreach ($chapters as $chapter): ?>
		  <tr>
		    <td>
				<input type="radio" name="parent_chapter_info" id="<?= $chapter->post_name; ?>" value='<?= $chapter->info; ?>' <?= $chapter->is_selected? 'checked' : '' ; ?> >
		    </td>
		    <td>
				<label for="<?= $chapter->post_name; ?>"><?= $chapter->post_title; ?></label>
		    </td>
		    <td>
				<label for="<?= $chapter->post_name; ?>"><?= $chapter->tree; ?></label>
		    </td>
		  </tr>
  <?php endforeach; ?>

  <?php if (count($chapters) == 0): ?>
		  <tr>
		    <td colspan="3">
				não há capitulos disponiveis ou o capitulo atual ja contem capitulos filhos e não pode ser vinculado.
		    </td>
		  </tr>
  <?php endif ?>

</table>