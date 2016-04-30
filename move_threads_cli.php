<?php

/**
 * 1. post_files изменяем postid файлов треда на СУЩЕСТВУЮЩИЙ на новом boardid, потому что нашего треда, 
 	который мы переносим на новый boardid еще там не существует, а ФК не даёт сделать иначе
 * 2. posts изменяем boardid треда на новый
 * 3. post_files возвращаем postid треда на родной, с новым boardid
 * 4. Тоже самое делаем с постами которые принадлежат треду
 */

require_once ('config.php');

$db = $tc_db;

$threadId = 1180;

$boardNameFrom = 'sss';
$boardNameTo = 'arch';
$existPostIdInNewBoard = 1; //пост в котором не должно быть картинок

$boardIdFrom = $db->GetOne('SELECT id FROM boards WHERE name = "' .$boardNameFrom. '"');
$boardIdTo = $db->GetOne('SELECT id FROM boards WHERE name = "' .$boardNameTo. '"');

$thread = $db->GetAll('SELECT * FROM posts WHERE id = "' .$threadId. '" AND boardid = "' .$boardIdFrom. '"');
$threadId = $thread[0]['id'];
$posts = $db->GetAll('SELECT * FROM posts WHERE parentid = "' .$threadId. '" AND boardid = "' .$boardIdFrom. '"');

$lastIdInNewboard = $db->GetOne('SELECT MAX(id) FROM posts WHERE boardid = ' .$boardIdTo);
$newThreadId = $lastIdInNewboard + 1;
$newPostId = $newThreadId;

if (empty($boardIdFrom) || empty($boardIdTo) || empty($threadId) || empty($posts) || empty($lastIdInNewboard)) {
	die("Что-то пошло не так, одна из переменных пустая. В треде есть посты? А сам тред то есть?\n");
}

// Переносим тред
// Пункт 1 -- переносим файлы 
$db->Execute('
	UPDATE posts_files 
	SET postid = ' .$existPostIdInNewBoard. ', 
	boardid = ' .$boardIdTo. ' 
	WHERE postid = ' .$threadId. ' 
	AND boardid = ' .$boardIdFrom);

// Пункт 2 -- переносим тред
$db->Execute('
	UPDATE posts
 	SET id = ' .$newThreadId. ',
 	boardid = ' .$boardIdTo. '
 	WHERE boardid = ' .$boardIdFrom. '
 	AND id = ' .$threadId);

// Пункт 3 -- переносим файлы обратно
$db->Execute('
	UPDATE posts_files
	SET postid = ' .$newThreadId. '
	WHERE boardid = ' .$boardIdTo. '
	AND postid = ' .$existPostIdInNewBoard);



// Теперь перенесём посты
$postsIds = [];
foreach ($posts as $post) {
	$postsIds[] = $post['id'];
}

foreach ($postsIds as $postId) {

	// Пункт 1 -- переносим файлы 
	$db->Execute('
		UPDATE posts_files 
		SET postid = ' .$existPostIdInNewBoard. ', 
		boardid = ' .$boardIdTo. ' 
		WHERE postid = ' .$postId. ' 
		AND boardid = ' .$boardIdFrom);

	// Пункт 2 -- переносим пост
	$db->Execute('
		UPDATE posts 
	 	SET id = ' .($newPostId = $newPostId + 1). ',
	 	boardid = ' .$boardIdTo. ',
	 	parentid = ' .$newThreadId. '
	 	WHERE boardid = ' .$boardIdFrom. '
	 	AND id = ' .$postId);

	// Пункт 3 -- переносим файлы обратно
	$db->Execute('
		UPDATE posts_files
		SET postid = ' .$newPostId. '
		WHERE boardid = ' .$boardIdTo. '
		AND postid = ' .$existPostIdInNewBoard);
}

echo "Скорее всего тред был перенесён успешно\n";