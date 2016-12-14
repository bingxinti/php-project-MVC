<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Document</title>
</head>
<body>
	
	<table align="center">
		<tr>
			<td>ID</td>
			<td>name</td>
			<td>age</td>
			<td>edit</td>
		</tr>
		<?php foreach ($list as $key => $value): ?>
			<tr>
				<td><?php echo $value['id'] ?></td>
				<td><?php echo $value['name'] ?></td>
				<td><?php echo $value['age'] ?></td>
				<td>
					<a href="">update</a>
					<a href="">del</a>
				</td>
			</tr>
		<?php endforeach ?>
		<tr>
			<td colspan="123">
				<div>
					<?php echo $page->showPages(1); ?>
				</div>
			</td>
		</tr>
	</table>
	
</body>
</html>