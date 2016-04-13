<?php

function can_process_request_board ($request) {
	if (
		(true === ($err = assert_board_name ($request['board_name']))) &&
		(true === ($err = assert_page ($request['page'])))
	) {
		return true;
	} else {
		debug (" +++ failing request +++ " . serialize ($request));
		return $err;
	}
}

function process_request_board ($request) {
	global $db, $mc, $post_fields, $cf;

	// this humble query is meant to get list of all threads of a board with posts and files count
	// limits are used to select only threads from specific page
	$pdo = $db->prepare ("
		select t1.id as id, t1.stickied as stickied, t1.locked as locked, t2.posts_count as posts_count, t3.files_count as files_count 
			from posts as t1 
			left join (
				select (
					case when p.parentid = 0 then p.id
					else
					p.parentid end
				) as x, count(p.parentid) as posts_count from posts p 
				join boards b on b.id = p.boardid
					where p.IS_DELETED = 0 and b.name = :board_name1 group by x
			) t2 on t1.id = t2.x 
			left join (
				select (
					case when p.parentid = 0 then p.id
					else
					p.parentid end
				) as x, count(p.file_md5) as files_count from posts p 
				join boards b on b.id = p.boardid
				where p.IS_DELETED = 0 and p.file_md5 != '' and b.name = :board_name2 group by x
			) t3 on t1.id = t3.x
			join boards b on b.id = t1.boardid 
		where b.name = :board_name3 and t1.parentid = 0 and t1.IS_DELETED = 0 order by bumped desc limit :num_threads_on_page offset :threads_offset");

	$pdo->execute(array (
		":board_name1" => $request['board_name'],
		":board_name2" => $request['board_name'],
		":board_name3" => $request['board_name'],
		":num_threads_on_page" => $cf['PN_THREADS_ON_PAGE'],
		":threads_offset" => $cf['PN_THREADS_ON_PAGE'] * $request['page']
	));
	$threads_on_page = $pdo->fetchAll (PDO::FETCH_ASSOC);

	foreach ($threads_on_page as $thread) {
		$data["id"] = $thread["id"];
		$data["stickied"] = $thread["stickied"];
		$data["locked"] = $thread["locked"];
		$data["posts_count"] = $thread["posts_count"];
		$data["files_count"] = $thread["files_count"];
		$thread = $thread["id"];
		$pdo = $db->prepare ("select distinct * from ((select ". $post_fields ." from posts p join boards b on p.boardid=b.id where b.name=:board_name1 and (p.parentid=:thread1 or p.id=:thread2) order by p.id desc limit 5) union (select ". $post_fields ." from posts p join boards b on p.boardid=b.id where b.name=:board_name2 and (p.parentid=:thread3 or p.id=:thread4) order by p.id asc limit 1)) x order by id asc");
		$pdo->execute(array (
			":board_name1" => $request['board_name'],
			":board_name2" => $request['board_name'],
			":thread1" => $thread,
			":thread2" => $thread,
			":thread3" => $thread,
			":thread4" => $thread
		));
		$data['posts'] = $pdo->fetchAll (PDO::FETCH_ASSOC);;
		$result["threads"][] = $data;
	}

	$pdo = $db->prepare ("select count(*) from posts p join boards b on b.id = p.boardid where p.parentid=0 and IS_DELETED = 0 and b.name = :board_name");
	$pdo->execute (array ( ":board_name" => $request['board_name']));
	$res = $pdo->fetchColumn();
	$result["pages"] = ceil ((int)$res / $cf['PN_THREADS_ON_PAGE']);
	return $result;
}

?>
