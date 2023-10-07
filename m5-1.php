<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>mission5-1</title>
</head>
    <body>
<?php
    //変数を定義付けする
    $name = filter_input(INPUT_POST, 'name');           //名前
    $comment = filter_input(INPUT_POST, 'comment');     //コメント
    $p_date = date("Y/m/d H:i:s");                      //日付
    $pass= filter_input(INPUT_POST, 'pass');            //投稿用パスワード
    $del_pass = filter_input(INPUT_POST, 'del_pass');   //削除用パスワード
    $edi_pass = filter_input(INPUT_POST, 'edi_pass');   //編集用パスワード
    
    $dsn = 'mysql:dbname=データベース名;host=localhost;charset=utf8';   // データベースに接続する
    $user = 'ユーザ名';
    $password = 'パスワード';
    $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
    
    $sql = "CREATE TABLE IF NOT EXISTS tbm5"    //データベースにテーブルを作成する
	    ." ("
	    ."id INT AUTO_INCREMENT PRIMARY KEY,"   //投稿番号を自動採番されるように設定
	    ."name char(32),"
        ."comment TEXT,"
        ."datedata DATETIME,"   //日付はDATETIME
        ."pass TEXT"            //パスワードはTEXT
	    .");";
    $stmt = $pdo->query($sql);  //今回は実行するsql文が固定されているので、query()で実行する（もしsql文に変数が含まれているなどの場合は、prepare()で実行する）。
?>

    

<?php
	//書き込み
	if(!empty($_POST['name']) && !empty($_POST['comment'])&& !empty($_POST['pass'])){
		//編集モード
		if(!empty($_POST['Edi_Num'])){      //hiddenのボックス内に編集番号が入っているかどうか
			$Edi_Num = $_POST['Edi_Num'];
			$sql = 'UPDATE tbm5 SET name=:name, comment=:comment, datedata=:datedata, pass=:pass WHERE id=:id';     //編集は既存データの更新であるから、UPDATE文を使うとともにWHERE句でid (投稿番号) を特定。idが一致した投稿のみ更新する
			$stmt_1 = $pdo->prepare($sql);  //sql文に変数が含まれている＝固定されていないからprepare()を使用
			$stmt_1->bindParam(':id', $Edi_Num, PDO::PARAM_INT);        //PARAM_INTのINTは「integer」の略。すなわち「"整数型"の変数」を意味する
			$stmt_1->bindParam(':name', $name, PDO::PARAM_STR);         //STRは「文字列型の変数」
			$stmt_1->bindParam(':comment', $comment, PDO::PARAM_STR);
            $stmt_1->bindParam(':datedata', $p_date, PDO::PARAM_STR);
            $stmt_1->bindParam(':pass', $pass, PDO::PARAM_STR);
			$stmt_1->execute();
		//新規投稿
		}else{
			$stmt_2 = $pdo->prepare('INSERT INTO tbm5(id, name, comment, datedata, pass) VALUES (:id,:name,:comment,:datedata,:pass)');     //INSERT文でデータレコードを登録するよう設定
			$stmt_2->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt_2->bindParam(':name', $name, PDO::PARAM_STR);
			$stmt_2->bindParam(':comment', $comment, PDO::PARAM_STR);
            $stmt_2->bindParam(':datedata', $p_date, PDO::PARAM_STR);
            $stmt_2->bindParam(':pass', $pass, PDO::PARAM_STR);
			$stmt_2->execute();
		}
	}
    
    //削除機能
    if(!empty($_POST['del_num'])&& !empty($_POST['del_pass']) && !empty($_POST['delete'])){     //ここの条件分岐はミッション3から流用
        $del_num = $_POST['del_num'];   //変数に代入
        $sql = 'SELECT * FROM tbm5 WHERE id=:id';      //SELECT文でデータを抽出、差し替える項目（今回はid）をWHERE句で特定
        $stmt_3 = $pdo->prepare($sql);       //プリペアドステートメントを使ってSQL文でいつでも変更できるように、変更する箇所のみ命令文を作る             
        $stmt_3->bindParam(':id', $del_num, PDO::PARAM_INT);    //パラメータIDである「:id」に、$del_numを渡す 
        $stmt_3->execute();       //バインドした変数を評価                      
        $lines = $stmt_3->fetchAll();   //fetchAllで結果データを全件まとめて配列で取得する
        foreach($lines as $line){       //取得した配列をループさせる
            if($line[4] == $del_pass){  //パスワードと削除パスが一致するかどうか
                $sql = 'delete from tbm5 where id=:id';     //再び差し替える項目をWHERE句で特定
                $stmt_4 = $pdo->prepare($sql);              //実行するsql文に変数が含まれているので、プリペアドステートメントで実行する
                $stmt_4->bindParam(':id', $del_num, PDO::PARAM_INT);    //パラメータIDである「:id」に$del_numを渡す
                $stmt_4->execute();
            }
        }
    }
  
  
    //編集機能
    if(!empty($_POST['edi_num']) && !empty($_POST['edi_pass']) && !empty($_POST['edit'])){      //ここの条件分岐はミッション3から流用
		$edi_num = $_POST['edi_num'];   //変数に代入
        $sql = 'SELECT * FROM tbm5 WHERE id=:id ';      //SELECT文でデータを抽出、差し替える項目（今回はid）をWHERE句で特定
        $stmt_5 = $pdo->prepare($sql);  //プリペアドステートメントを使ってSQL文でいつでも変更できるように、変更する箇所のみ命令文を作る 
        $stmt_5->bindParam(':id', $edi_num, PDO::PARAM_INT);    //パラメータIDである「:id」に$edi_numを渡す 
        $stmt_5->execute();       //バインドした変数を評価                      
        $lines = $stmt_5->fetchAll();   //fetchAllで結果データを全件まとめて配列で取得する
		foreach($lines as $line){       //取得した配列をループさせる
            if($line[4]==$_POST['edi_pass']){   //パスワードと編集パスが一致するかどうか
                    $edi_num = $line[0];        //投稿ボックス内に表示させたい３つの変数に、各要素を定義づける
                    $edi_name = $line[1];
		            $edi_comment = $line[2];
            }
        }
    }
?>

<h1>掲示板のテーマ：好きな名字</h1>
        <form action="" method="post">
        <input type="text" name="name" placeholder="名前"  value="<?php if(!empty($edi_name)) echo $edi_name;?>">
            <input type="text" name="comment" placeholder="コメント" value="<?php if(!empty($edi_comment))echo $edi_comment;?>">
            <input type="text" name="pass" placeholder="パスワード" >
            <input type="hidden" name="Edi_Num" value="<?php if(!empty($edi_num)) {echo $edi_num;}?>">
		    <input type="submit" name="submit">
        </form>

        <form action="" method="post">
            <input type="number" name="del_num" placeholder="削除対象番号">
            <input type="text" name="del_pass" placeholder="パスワード" >
            <input type="submit" name="delete" value="削除">
        </form>

        <form action="" method="post">
            <input type="number" name="edi_num" placeholder="編集対象番号">
            <input type="text" name="edi_pass" placeholder="パスワード" >
            <input type="submit" name="edit" value="編集">
        </form>
<?php
    //表示機能
	$sql = 'SELECT * FROM tbm5';    //テーブルから全て抽出する
	$stmt_6 = $pdo->query($sql);    //sql文が固定されているので、queryで実行する
	$lines = $stmt_6->fetchAll();   //fetchAllで結果データを全件まとめて配列で取得する
    foreach($lines as $line){       //取得した配列をループさせる
				echo $line[0].' ';  //既存データの各要素（パスワード以外）を順に表示させる
				echo $line[1].' ';
				echo $line[2].' ';
				echo $line[3].' ';
				echo '<hr>';        //投稿ごとに水平線で区切る
    }
?>

    </body>
</html>