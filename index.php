<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$is_windows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

function endsWith($str, $end) {
  return strtolower(substr($str, -strlen($end))) === strtolower($end);
}

$path = trim(file_get_contents("config.txt"));
$path = rtrim($path, "/\\");

$sorterDir = $path . DIRECTORY_SEPARATOR . '!sorter';
$binDir = $path . DIRECTORY_SEPARATOR . '!bin';

if (!file_exists($sorterDir)) {
  mkdir($sorterDir);
}

if (!file_exists($binDir)) {
  mkdir($binDir);
}

if (isset($_GET['filename'])) {
  $filename = urldecode($_GET['filename']);

  if (file_exists($filename)) {
    $mime = false;
    if(function_exists("mime_content_type")){
      $mime = mime_content_type($filename);
    }
    if ($mime === false) {
      $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
      if ($ext === 'jpg' || $ext === 'jpeg') {
        $mime = 'image/jpeg';
      } else if ($ext === 'png') {
        $mime = 'image/png';
      } else if ($ext === 'mp4') {
        $mime = 'video/mp4';
      } else if ($ext === 'mov') {
        $mime = 'video/quicktime';
      } else {
        $mime = 'application/octet-stream';
      }
    }

    header("Content-Type: " . $mime);
    header("Content-Disposition: inline;");
    header("Content-Length: " . filesize($filename));
    readfile($filename);
  }

  exit;
}

if (isset($_POST['folder_name']) && trim($_POST['folder_name']) !== '') {
  $newFolder = $path . DIRECTORY_SEPARATOR . trim($_POST['folder_name']);
  if (!file_exists($newFolder)) {
    mkdir($newFolder);
  }
}

if (isset($_GET['file']) && isset($_GET['folder'])) {
  $source = $path . DIRECTORY_SEPARATOR . urldecode($_GET['file']);
  $dest = $path . DIRECTORY_SEPARATOR . $_GET['folder'] . DIRECTORY_SEPARATOR . urldecode($_GET['file']);

  if (file_exists($source)) {
    rename($source, $dest);
  }
}

$dirs = glob($path . DIRECTORY_SEPARATOR . "*", GLOB_ONLYDIR);
$files = glob($path . DIRECTORY_SEPARATOR . "*.{heic,HEIC,jpg,JPG,jpeg,JPEG,mp4,MP4,mov,MOV}", GLOB_BRACE);

if ($files && count($files) > 0) {
  $file = $files[0];
}

$magick_path = null;

if (file_exists("magick.exe")) {
  $magick_path = realpath("magick.exe");
} else if (file_exists("magick")) {
  $magick_path = realpath("magick");
}

if (isset($file)) {
  if (endsWith($file, ".heic") && $magick_path != null) {
    $output = $sorterDir . DIRECTORY_SEPARATOR . "output.jpg";

    if (file_exists($output)) {
      unlink($output);
    }

    $cmd = '"' . $magick_path . '" "' . $file . '" -quality 100% "' . $output . '"';
    shell_exec($cmd);
  }
}

if (isset($_GET['file']) && isset($_GET['folder'])) {
  if (isset($file)) {
    $preview = "?filename=" . urlencode($file) . "&time=" . time();

    if (endsWith($file, ".heic")) {
      $preview = "?filename=" . urlencode($sorterDir . DIRECTORY_SEPARATOR . "output.jpg") . "&time=" . time();
    }

    $type = "image";
    if (endsWith($file, ".mov") || endsWith($file, ".mp4")) {
      $type = "video";
    }

    echo json_encode(array(
      "msg" => "Moved file " . $_GET['file'] . " to " . $_GET['folder'],
      "file" => basename($file),
      "preview" => $preview,
      "type" => $type,
      "total" => count($files)
    ));
    exit;
  } else {
    echo json_encode(array("msg" => "No more files"));
    exit;
  }
}

?>
<!doctype html>
<html>
  <head>
    <title>Photo sorter</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
      html.submitting{
        opacity:0.3;
      }
      html.submitting *{
        cursor:none;
      }
      *{
        box-sizing: border-box;
      }
      body{
        font-family:Arial;
        padding:0;
        margin:0;
        overflow:hidden;
      }
      label input{
        position: absolute;
        opacity: 0;
        left:0;
        top:0;
      }
      label{
        display:block;
        margin:3px 10px;
        background: #ddd;
        border-radius:5px;
        padding:8px 10px;
        cursor:pointer;
        text-align: center;
      }
      label:hover{
        background: #ccc;
      }
      #result{
        background:#66c571;
        border:1px #c3e6cb solid;
        border-radius:3px;
        padding:3px 7px;
        font-size:12px;
        margin:7px auto;
        width:50%;
        text-align: center;
        top:-60px;
        z-index: 1000;
        position: fixed;
        left: 0;
        right:0;
      }
      #name{
        position:absolute;
        font-size:20px;
        text-align: center;
        background: #ddd;
        top:0;
        left:0;
        right:0;
        padding: 10px;
      }
      #main{
        position: absolute;
        top:40px;
        bottom:0;
        right:300px;
        left:0;
        overflow: hidden;
        background: #000;
      }
      #main video{
        height:100%;
        width:100%;
      }
      #main img{
        max-height: 100%;
        max-width: 100%;
        margin: auto;
        display: block;
      }
      #folders{
        position:absolute;
        right:0;
        width:300px;
        bottom: 0;
        top:43px;
      }
      #folders form{
        position: absolute;
        left:0;
        right:0;
        bottom:50px;
        top:0;
        overflow:auto;
      }
      #no_more{
        position: absolute;
        left: 0;
        right: 0;
        top: 0;
        bottom: 0;
        color: #fff;
        margin: auto;
        height: 33px;
        text-align: center;
        font-size: 30px;
      }
      #new_folder_button{
        position: absolute;
        right:100px;
        bottom:10px;
        background: #f60;
        color:#fff;
        padding:8px 15px;
        border:0;
        border-radius: 100px;
      }
    </style>
  </head>
  <body>
    <?php echo '<!--Path : ' . htmlspecialchars($path, ENT_QUOTES) . '-->'; ?>
    <div id="result"></div>
    <?php
    if (!empty($files) && count($files) > 0) {
      echo '<div id="name">' . htmlspecialchars(basename($file), ENT_QUOTES) . ' [' . count($files) . ' left]</div>';
    } else {
      echo '<div id="name">No more files</div>';
    }
    ?>

    <div id="main">
      <?php
      if (!empty($files) && count($files) > 0) {
        if (endsWith($file, ".heic")) {
          echo '<img id="main_image" src="?filename=' . urlencode($sorterDir . DIRECTORY_SEPARATOR . 'output.jpg') . '&time=' . time() . '">';
          echo '<video autoplay controls id="main_video" style="display:none"><source type="video/mp4"></video>';
        } else if (endsWith($file, ".jpg") || endsWith($file, ".jpeg")) {
          echo '<img id="main_image" src="?filename=' . urlencode($file) . '">';
          echo '<video autoplay controls id="main_video" style="display:none"><source type="video/mp4"></video>';
        } else if (endsWith($file, ".mp4") || endsWith($file, ".mov")) {
          echo '<img id="main_image" style="display:none;">';
          echo '<video autoplay controls id="main_video"><source src="?filename=' . urlencode($file) . '" type="video/mp4"></video>';
        }
      } else {
        echo '<p id="no_more">No more files found!</p>';
      }
      ?>
    </div>

    <div id="folders">
      <form id="form">
        <input id="filename" type="hidden" value="<?php echo isset($file) ? htmlspecialchars(basename($file), ENT_QUOTES) : ''; ?>" name="file">
        <?php
        if (!empty($files) && count($files) > 0) {
          foreach ($dirs as $dir) {
            $dirName = basename($dir);
            echo '<label><input class="folder_btn" type="radio" name="folder" value="' . htmlspecialchars($dirName, ENT_QUOTES) . '">' . htmlspecialchars($dirName, ENT_QUOTES) . '</label>';
          }
        }
        ?>
      </form>
    </div>

    <button id="new_folder_button">New folder</button>
    <form id="folder_form" method="post">
      <input name="folder_name" id="folder_name">
    </form>
    <script src="https://www.jamiebalfour.scot/public/scripts/photo-sorter.js"></script>
  </body>
</html>
