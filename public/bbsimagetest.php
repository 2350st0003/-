<?php
$dbh = new PDO('mysql:host=mysql;dbname=example_db', 'root', '');
if (isset($_POST['body'])) {
    $image_filename = null;
    if (isset($_FILES['image']) && !empty($_FILES['image']['tmp_name'])) {
        if (preg_match('/^image\//', mime_content_type($_FILES['image']['tmp_name'])) !== 1) {
            header("HTTP/1.1 302 Found");
            header("Location: ./bbsimagetest.php");
            return;
        }
        $pathinfo = pathinfo($_FILES['image']['name']);
        $extension = $pathinfo['extension'];
        $image_filename = strval(time()) . bin2hex(random_bytes(25)) . '.' . $extension;
        $filepath = '/var/www/upload/image/' . $image_filename;
        move_uploaded_file($_FILES['image']['tmp_name'], $filepath);
    }
    $insert_sth = $dbh->prepare("INSERT INTO bbs_entries (body, image_filename, reply_to) VALUES (:body, :image_filename, :reply_to)");
    $insert_sth->execute([
        ':body' => $_POST['body'],
        ':image_filename' => $image_filename,
        ':reply_to' => $_POST['reply_to'] ?? null
    ]);
    header("HTTP/1.1 302 Found");
    header("Location: ./bbsimagetest.php");
    return;
}
$select_sth = $dbh->prepare('SELECT * FROM bbs_entries ORDER BY created_at DESC');
$select_sth->execute();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>掲示板</title>
<style>
body { font-family: sans-serif; padding: 1em; }
textarea { width: 100%; box-sizing: border-box; min-height: 5em; margin-bottom:0.5em; }
button { padding: 0.5em 1em; margin-bottom: 1em; }
img { max-width: 100%; height: auto; display:block; margin-top:0.5em; }
dl { background: #f9f9f9; padding: 0.5em; border-radius: 5px; margin-bottom:1em; }
dt { font-weight: bold; }
dd { margin:0 0 0.5em 0; }
@media (max-width: 600px) { body { padding: 0.5em; } dl { font-size: 14px; } }
</style>
</head>
<body>

<form method="POST" action="./bbsimagetest.php" enctype="multipart/form-data" id="bbsForm">
  <input type="hidden" name="reply_to" id="replyTo">
  <textarea name="body" required placeholder="本文を入力"></textarea>
  <div>
    <input type="file" accept="image/*" name="image" id="imageInput">
  </div>
  <button type="submit">送信</button>
</form>

<hr>

<?php foreach($select_sth as $entry): ?>
  <dl id="post-<?= $entry['id'] ?>">
    <dt>No.</dt>
    <dd><?= $entry['id'] ?></dd>
    <dt>日時</dt>
    <dd><?= $entry['created_at'] ?></dd>
    <dt>内容</dt>
    <dd>
      <?= nl2br(htmlspecialchars($entry['body'])) ?>
      <?php if(!empty($entry['image_filename'])): ?>
        <img src="/image/<?= htmlspecialchars($entry['image_filename']) ?>">
      <?php endif; ?>
      <a href="#bbsForm" class="replyLink" data-id="<?= $entry['id'] ?>">#この投稿に返信</a>
    </dd>
  </dl>
<?php endforeach ?>


<script>
const imageInput = document.getElementById("imageInput");
imageInput.addEventListener("change", (event) => {
  const file = imageInput.files[0];
  if (!file) return;
  const maxFileSize = 5 * 1024 * 1024;
  if (file.size <= maxFileSize) return;
  const reader = new FileReader();
  reader.onload = function(e) {
    const img = new Image();
    img.onload = function() {
      const canvas = document.createElement("canvas");
      const scale = Math.sqrt(maxFileSize / file.size);
      canvas.width = img.width * scale;
      canvas.height = img.height * scale;
      const ctx = canvas.getContext("2d");
      ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
      canvas.toBlob((blob) => {
        const newFile = new File([blob], file.name, { type: file.type });
        const dt = new DataTransfer();
        dt.items.add(newFile);
        imageInput.files = dt.files;
      }, file.type, 0.9);
    };
    img.src = e.target.result;
  };
  reader.readAsDataURL(file);
});

document.querySelectorAll('.replyLink').forEach(link => {
  link.addEventListener('click', (e) => {
    e.preventDefault();
    const id = link.dataset.id;
    document.getElementById('replyTo').value = id;
    document.getElementById('bbsForm').scrollIntoView({ behavior: 'smooth' });
  });
});
</script>

</body>
</html>
