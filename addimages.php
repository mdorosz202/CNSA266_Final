<?php
  session_start();

  include("config.php");
  include("functions.php");

  mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

	$db = mysqli_connect($dbhost, $dbuser, $dbpassword, $dbdatabase);

	//not needed with mysqli, replaced with 4th parameter in mysqli_connect
	//mysql_select_db($dbdatabase, $db);

  $validid = pf_validate_number($_GET['id'], "redirect", "index.php");

  if(isset($_SESSION['USERNAME']) == FALSE) {
    $url = $config_basedir . "login.php?ref=images&id=" . $validid);
    redirect($url);
  }

  $theitemsql = mysqli_real_escape_string($db, "SELECT user_id FROM items WHERE id=" .
    $validid . ";");
  $theitemresult = mysqli_query($db, $theitemsql);
  $theitemrow = mysqli_fetch_assoc($theitemresult);

  if($theitemrow['user_id'] != $_SESSION['USERID']) {
    $url = $config_basedir);
    redirect($url);
  }

  //the original of this function made me want to scream
  if($_POST['submit']) {
    if($_FILES['userfile']['name'] == '') {
      $url = $config_basedir. "addimages.php?error=nophoto");
      redirect($url);
    } elseif($_FILES['userfile']['size'] == 0) {
      $url = $config_basedir. "addimages.php?error=photoprob");
      redirect($url);
    } elseif($_FILES['userfile']['size'] > $MAX_FILE_SIZE) {
      $url = $config_basedir. "addimages.php?error=large");
      redirect($url);
    } elseif(!getimagesize($_FILES['userfile']['tmp_name'])) {
      $url = $config_basedir. "addimages.php?error=invalid");
      redirect($url);
    } else {
      $uploaddir = ""; //TODO: DIRECTORY FOR IMAGE UPLOAD ON WEB SERVER
      $uploadfile = $uploaddir . $_FILES['userfile']['name'];

      if(move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
        $inssql = mysqli_real_escape_string($db, "INSERT INTO images(item_id, name)" .
          "VALUES(" . $validid . ", '" . $_FILES['userfile']['name'] . "')");
        mysqli_query($db, $inssql);

        $url = $config_basedir. "addimages.php?id=" . $validid);
        redirect($url);
      } else {
        echo "There was a problem uploading your file.<br>";
      }
    }
  } else {
    require("header.php");

    $imagessql = mysqli_real_escape_string($db, "SELECT * FROM images WHERE item_id=" .
      $validid . ";");
    $imagesresult = mysqli_query($db, $imagessql);
    $imagesnumrows = mysqli_num_rows($imagesresult);

    echo "<h1>Current images</h1>";

    if($imagesnumrows == 0) {
      echo "No images.";
    } else {
      echo "<table>";
      while($imagesrow = mysqli_fetch_assoc($imagesresult)) {
        echo "<tr>";
        echo "<td><img src='" . $config_basedir . "/images/" . $imagesrow['name'] .
          "' width='100'></td>";
        echo "<td>[<a href='deleteimage.php?image_id=" . $imagesrow['id'] .
          "&item_id=" . $validid . "'>delete</a>]</td>";
        echo "</tr>";
      }
      echo "</table>";
    }

    switch($_GET['error']) {
      case "empty":
        echo "You did not select anything.";
        break;
      case "nophoto":
        echo "You did not select a photo to upload.";
        break;
      case "photoprob":
        echo "There appears to be a problem with the photo you are uploading.";
        break;
      case "large":
        echo "The photo you selected is too large.";
        break;
      case "invalid":
        echo "The photo you selected is not a valid image file";
        break;
      default:
        echo "Unknown error.";
        break;
    }
?>

<form enctype="multipart/form-data" action="addimages.php"
  method="POST">

  <input type="hidden" name="MAX_FILE_SIZE" value="3000000">

  <table>
    <tr>
      <td>Image to upload</td>
      <td><input name="userfile" type="file"></td>
    </tr>
    <td>
      <td colspan="2"><input type="submit" name="submit" value="Upload File"></td>
    </tr>
  </table>
</form>

When you have finished adding photos, go and <a href="<?php echo 'itemdetails.php?id=' . $validid; ?>">see your item!</a>

<?php
}
require("footer.php");
?>
