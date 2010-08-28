<?php
/*This script has been tested to work with Wordpress 2.3.2 and Joomla 1.0.12 and will not work with older versions of Wordpress due to major changes in the WP database schema. If this script breaks with a new release of Wordpress or Joomla it will need further work. If any developer puts in work on this script and redistributes it please do not remove the Credits from this file.*/
$_wp_installing = 1;
if (!file_exists('../wp-config.php')) 
    die("There doesn't seem to be a <code>wp-config.php</code> file. I need this before we can get started. Need more help? <a href='http://wordpress.org/docs/faq/#wp-config'>We got it</a>. You can <a href='setup-config.php'>create a <code>wp-config.php</code> file through a web interface</a>, but this doesn't work for all server setups. The safest way is to manually create the file.");

if (!file_exists('config.php')) 
    die("There doesn't seem to be a <code>config.php</code> file. Please open the <code>sample-config.php</code>, add your Mambo database name and resave it as <code>config.php</code>.");

require_once('../wp-config.php');
//require_once('./upgrade-functions.php');
require_once('config.php');

$guessurl = str_replace('/wp-admin/install.php?step=2', '', 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) );

$action=$_GET['action'];
$id=$_GET['select'];
$category=$_GET['category'];

if ($category == "") {
	$category=$_POST['category'];
}

$section=$_GET['section'];

if ($section == "") {
	$section=$_POST['section'];
}

$wpsection=$_GET['wpsection'];

if ($wpsection == "") {
	$wpsection=$_POST['wpsection'];
}

if (isset($_GET['step']))
	$step = $_GET['step'];
else
	$step = 0;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Joomla2WordPress &rsaquo; Import V3 by Azeem Khan</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<style media="screen" type="text/css">
	<!--
	html {
		background: #eee;
	}
	body {
		background: #fff;
		color: #000;
		font-family: Georgia, "Times New Roman", Times, serif;
		margin-left: 20%;
		margin-right: 20%;
		padding: .2em 2em;
	}
	
	h1 {
		color: #006;
		font-size: 18px;
		font-weight: lighter;
	}
	
	h2 {
		font-size: 16px;
	}
	
	p, li, dt {
		line-height: 140%;
		padding-bottom: 2px;
	}

	ul, ol {
		padding: 5px 5px 5px 20px;
	}
	#logo {
		margin-bottom: 2em;
	}
	.step a, .step input {
		font-size: 1em;
	}
	td input {
		font-size: 1.5em;
	}
	.step, th {
		text-align: right;
	}
	#footer {
		text-align: center; 
		border-top: 1px solid #ccc; 
		padding-top: 1em; 
		font-style: italic;
	}
	-->
	</style>
</head>
<body>
<h1 id="logo">Joomla2WordPress Import Wizard v3 by Azeem Khan</h1>
<?php
// Let's check to make sure WP isn't already installed.
/* $wpdb->hide_errors();
$installed = $wpdb->get_results("SELECT * FROM $wpdb->users");
if ($installed) die(__('<h1>Already Installed</h1><p>You appear to have already installed WordPress. To reinstall please clear your old database tables first.</p></body></html>'));
$wpdb->show_errors(); */

switch($step) {

	case 0:
?>
<p>Welcome to the Joomla2WordPress import wizard. With this wizard you can
  import articles from a Joomla installation to a WordPress installation as long
  as they're both on the same host.</p>
<p>Since we've come this far, you've already created a <code>config.php</code> file.&nbsp; Make
  sure the Joomla database name is okay, then click
  through to step one and we'll test to make sure we've got the connections to
  our databases.</p>
<h2 class="step"><a href="index.php?step=1">Step One &raquo;</a></h2>
<?php
	break;

	case 1:
    $link = mysql_connect("$dbhost", "$dbuser", "$dbpass")
        or die("Could not connect");
    //print "Connected successfully";
    mysql_select_db("$dbh") or die("<p>Could not select database</p>");

?>
<h1>Step One</h1>
<p>Okay, we're connected to the Joomla database.</p>
<p>Now, we need to select the method you want to use to import your articles or links.  (You should automatically advance to the next step, but if it doesn't you can click the button.)</p>
<?php

    /* Performing SQL query */
    $query = "SELECT * FROM ".$joomlatblprefix."sections";
    $result = mysql_query($query) or die("<h3>Query failed</h3>");
//echo $result;
	/*****************************************/	
	/* Write the form with the section names */
	/*****************************************/
	?>
	<p>Import articles from a whole section:</p>
	<form action="index.php" name="sections" id="sections">
        <table width="100%"  border="0">
          <tr>
            <td width="50%"><select name="section" class="import" onChange="submit('document.tables.sections');">
          <option selected>-- select a Joomla section --</option>
	      <?php
	    while ($row = mysql_fetch_assoc($result)) {
		?>
    
	    <option value="<?php echo $row["id"]; ?>"><?php echo $row["title"]; ?></option>
	      <?php } ?>
    
    </select>
            <input name="step" type="hidden" id="step" value="2" /></td>
            <td class="step">
            <input name="Submit" type="submit" value="Step Two" />            </td>
          </tr>
        </table>
	</form>

<?php
    mysql_free_result($result);

    $query = "SELECT ".$joomlatblprefix."categories.id, ".$joomlatblprefix."categories.title FROM `".$joomlatblprefix."categories`, `".$joomlatblprefix."sections` WHERE ".$joomlatblprefix."sections.id = ".$joomlatblprefix."categories.section";
    $result = mysql_query($query) or die("Query failed");
	
	/*****************************************/
	/* Write the form with the section names */
	/*****************************************/
	?>
	<p>Import articles from just one category:</p>

<form action="index.php" name="cats" id="cats">

        <table width="100%"  border="0">
          <tr>
            <td width="50%"><select name="category" onChange="submit('document.cats');">
          <option selected>-- select a Joomla category --</option>
       <?php while ($row = mysql_fetch_assoc($result)) {
		?>
    
	    <option value="<?php echo $row["id"]; ?>"><?php echo $row["title"]; ?></option>
	      <?php } ?>
    
    </select>
            <input name="step" type="hidden" id="step4" value="3" /></td>
            <td class="step">
            <input name="Submit2" type="submit" value="Step Two" />            </td>
          </tr>
  </table>
</form>
<?php
    mysql_free_result($result);

    $query = "SELECT id, title FROM `".$joomlatblprefix."categories` WHERE section = 'com_weblinks'";
    $result = mysql_query($query) or die("Query failed");
	
	/***********************************************/
	/* Write the form with the link category names */
	/***********************************************/
	?>
	<p>Import links from just a category:</p>

<form action="index.php" name="links" id="links">
	    
        <table width="100%"  border="0">
          <tr>
            <td width="50%"><select name="category" onChange="submit('document.links');">
          <option selected>-- select a Joomla link category --</option>
       <?php while ($row = mysql_fetch_assoc($result)) {
		?>
    
	    <option value="<?php echo $row["id"]; ?>"><?php echo $row["title"]; ?></option>
	      <?php } ?>
    
    </select>
            <input name="step" type="hidden" id="step5" value="6" /></td>
            <td class="step">
            <input name="Submit3" type="submit" value="Step Two" />            </td>
          </tr>
  </table>
</form>
<?php
	break;
	case 2:
	// Select a WP category to import from the section.

	echo "<h1>Step Two</h1>";

	$link = mysql_connect("$dbihost", "$dbiuser", "$dbipass")
        or die("<p>Could not connect</p>");
    //print "Connected successfully";
    mysql_select_db("$dbi") or die("<p>Could not select database</p>");

	//$query = "SELECT cat_ID, cat_name FROM `wp_categories`";
	//$query = "SELECT term_id, name FROM `wp_terms`";
	$query="SELECT ".$wptblprefix."terms.term_id, name FROM `".$wptblprefix."terms` LEFT OUTER JOIN ".$wptblprefix."term_taxonomy ON ".$wptblprefix."terms.term_id = ".$wptblprefix."term_taxonomy.term_id WHERE ".$wptblprefix."term_taxonomy.taxonomy = 'category'"; 
	//echo $query;
	$result = mysql_query($query) or die("Query failed");
	?>
	<p>Select a WordPress category to import from your section.</p>
	<form action="index.php" name="wpcat" id="wpcat">
	    <select name="wpsection" class="import" onChange="submit('document.wpcat');">
          <option selected>-- select a WordPress section --</option>
	      <?php
	    while ($row = mysql_fetch_assoc($result)) {
		
    
	    //<option value="<?php echo $row["cat_ID"]; ?>"><?php echo $row["cat_name"]; ?></option>
	?>
	<option value="<?php echo $row["term_id"]; ?>"><?php echo $row["name"]; ?></option>	      
<?php } ?>
    
    </select>
	    <input name="step" type="hidden" id="step" value="4">
	    <input name="section" type="hidden" id="step" value="<?php echo $section; ?>">
	</form>
<a href="index.php?step=1"><h2>Back to step 1</h2></a>

<?php
	break;
	case 3:
	// Select a WP category to import from the category.

	echo "<h1>Step Two</h1>";

	$link = mysql_connect("$dbihost", "$dbiuser", "$dbipass")
        or die("<p>Could not connect</p>");
    //print "Connected successfully";
    mysql_select_db("$dbi") or die("<p>Could not select database</p>");

	//$query = "SELECT cat_ID, cat_name FROM `wp_categories`";
	//$query = "SELECT term_id, name FROM `wp_terms`";
	$query="SELECT ".$wptblprefix."terms.term_id, name FROM `".$wptblprefix."terms` LEFT OUTER JOIN ".$wptblprefix."term_taxonomy ON ".$wptblprefix."terms.term_id = ".$wptblprefix."term_taxonomy.term_id WHERE ".$wptblprefix."term_taxonomy.taxonomy = 'category'"; 
	//echo $query;
	$result = mysql_query($query) or die("Query failed");
	?>
	<p>Select a WordPress section to import from your category.</p>
	<form action="index.php" name="wpcat" id="wpcat">
	    <select name="wpsection" class="import" onChange="submit('document.wpcat');">
          <option selected>-- select a WordPress category --</option>
	      <?php
	    while ($row = mysql_fetch_assoc($result)) {
		
    
	    //<option value="<?php echo $row["cat_ID"]; ?>"><?php echo $row["cat_name"]; ?></option>
	?>
	<option value="<?php echo $row["term_id"]; ?>"><?php echo $row["name"]; ?></option>

	      <?php } ?>
    
    </select>
	    <input name="step" type="hidden" id="step" value="5">
	    <input name="category" type="hidden" id="step" value="<?php echo $category; ?>">
	</form>
<a href="index.php?step=1"><h2>Back to step 1</h2></a>

<?php
	break;
	case 4:
	//require_once ("../wp-includes/functions-formatting.php");
	require_once ("../wp-includes/functions.php");

	echo "<h1>Step Three</h1>";
	//echo "<p>".$section."</p>";
	//echo "<p>".$wpsection."</p>";

	/* Make connection to Mambo (exporting) database */
    $link = mysql_connect("$dbhost", "$dbuser", "$dbpass")
        or die("Could not connect");
    //print "Connected successfully";
    mysql_select_db("$dbh") or die("Could not select database");

    /* Performing SQL query */
    $query = "SELECT id, title, introtext, `fulltext`, created, modified FROM ".$joomlatblprefix."content WHERE `sectionid` = '$section'";
	//echo $query."<br />\n";
    $result = mysql_query($query) or die("Query failed getting content from section");

    /* Load database values into an array, making sure to escape them for quotes, etc. */
	$i = 0;
    while ($row = mysql_fetch_assoc($result)) {
			$import[0][$i] = mysql_escape_string($row["id"]);
			$import[1][$i] = mysql_escape_string($row["title"]);
			$import[2][$i] = mysql_escape_string($row["introtext"]."<br /><!--more--><br />".$row["fulltext"]);
			$import[3][$i] = mysql_escape_string($row["created"]);
			$import[4][$i] = mysql_escape_string($row["modified"]);
			$i++;

    }
	    /* Free result set */
    mysql_free_result($result);
	
   /* Closing connection */
    mysql_close($link);
/* For debugging purposes */
/*	$j = 0;
	echo "i = ".$i."<br />";
	while ($j < $i) {
			echo "id: ".$import[0][$j]."<br />\n";
        	echo "title: ".$import[1][$j]."<br />\n";
        	echo "introtext: ".$import[2][$j]."<br />\n";        
        	echo "created: ".$import[3][$j]."<br />\n";
        	echo "modified: ".$import[4][$j]."<br /><br />\n";
			$j++;
	}
*/

	echo "<tr><td>Importing ".$i." item";
	if ($i != 1) {
		echo "s";
	}

	/* Make connection to Word Pro (importing) database */
	$link = mysql_connect("$dbihost", "$dbiuser", "$dbipass")
        or die("<p>Could not connect</p>");
    //print "Connected successfully";
    mysql_select_db("$dbi") or die("<p>Could not select database</p>");

	$j = 0;
	while ($j < $i) {
	
		

		/* Create an acceptable WP post_name */
		$post_name = sanitize_title_with_dashes($import[1][$j]);

		$query5 = "SELECT ID from ".$wptblprefix."users WHERE ".$wptblprefix."users.user_login = '$authorusername'";
		echo "<br />".$query5."<br />";
		$result5 = mysql_query($query5);
		while ($row=mysql_fetch_array($result5)) 
		{ 
		$ID5 = $row["ID"];
		}
		
		/* Do the actual query */
		$query = "INSERT INTO ".$wptblprefix."posts (id, post_author, post_title, post_content, post_date, post_modified, post_name, post_category) VALUES ('', '$ID5', '{$import[1][$j]}', '{$import[2][$j]}', '{$import[3][$j]}', '{$import[4][$j]}', '$post_name', '$wpsection')";
		/* For debugging purposes */
		echo "<br />".$query."<br />";
		$result = mysql_query($query) or die("<p>Query failed inserting Section</p>");


		/* For debugging purposes */
		echo "<br />".$query."<br />";
		

		$query2 = "SELECT term_taxonomy_id FROM ".$wptblprefix."term_taxonomy WHERE ".$wptblprefix."term_taxonomy.term_id = $wpsection AND ".$wptblprefix."term_taxonomy.taxonomy = 'category'";

		/* For debugging purposes */
		echo "<br />".$query2."<br />";
		$result2 = mysql_query($query2);
		while ($row=mysql_fetch_array($result2)) 
		{ 
		$term_taxonomy_id = $row["term_taxonomy_id"];
		}
		
		$query3 = "SELECT id from ".$wptblprefix."posts WHERE ".$wptblprefix."posts.post_title = '{$import[1][$j]}'";
		
		/* For debugging purposes */
		echo "<br />".$query3."<br />";

		$result3 = mysql_query($query3);

		while ($row=mysql_fetch_array($result3)) 
		{ 
		$id2 = $row["id"];	
		}
		
		$query4 = "INSERT INTO ".$wptblprefix."term_relationships (object_id, term_taxonomy_id) VALUES ('$id2', '$term_taxonomy_id')";
		
		/* For debugging purposes */
		echo "<br />".$query4."<br />";		

		$result4 = mysql_query($query4) or die("Query failed");

		$j++;
	}

   /* Closing connection */
    mysql_close($link);
	
?>

	<p><em>Finished!</em></p>

<?php
	echo "<p><a href=\"index.php\">Go back to the beginning</a>.</p>\n";

	break;
	case 5:
	//require_once ("../wp-includes/functions-formatting.php");
	require_once ("../wp-includes/functions.php");

	echo "<h1>Second Step</h1>";
	//echo "<p>".$section."</p>";
	//echo "<p>".$wpsection."</p>";

	/* Make connection to Mambo (exporting) database */
    $link = mysql_connect("$dbhost", "$dbuser", "$dbpass")
        or die("Could not connect");
    //print "Connected successfully";
    mysql_select_db("$dbh") or die("Could not select database");

    /* Performing SQL query */
    $query = "SELECT id, title, introtext, `fulltext`, created, modified FROM ".$joomlatblprefix."content WHERE `catid` = '$category'";
	
	//kill this later	
    //echo $query."<br />\n";
    $result = mysql_query($query) or die("Query failed");

    /* Load database values into an array, making sure to escape them for quotes, etc. */
	$i = 0;
    while ($row = mysql_fetch_assoc($result)) {
			$import[0][$i] = mysql_escape_string($row["id"]);
			$import[1][$i] = mysql_escape_string($row["title"]);
			$import[2][$i] = mysql_escape_string($row["introtext"]."<br /><!--more--><br />".$row["fulltext"]);
			$import[3][$i] = mysql_escape_string($row["created"]);
			$import[4][$i] = mysql_escape_string($row["modified"]);
			$i++;
    }
	    /* Free result set */
    mysql_free_result($result);
	
   /* Closing connection */
    mysql_close($link);
/* For debugging purposes */
/*	$j = 0;
	echo "i = ".$i."<br />";
	while ($j < $i) {
			echo "id: ".$import[0][$j]."<br />\n";
        	echo "title: ".$import[1][$j]."<br />\n";
        	echo "introtext: ".$import[2][$j]."<br />\n";        
        	echo "created: ".$import[3][$j]."<br />\n";
        	echo "modified: ".$import[4][$j]."<br /><br />\n";
			$j++;
	}
*/

	echo "<tr><td>Importing ".$i." item";
	if ($i != 1) {
		echo "s";
	}

	/* Make connection to Word Pro (importing) database */
	$link = mysql_connect("$dbihost", "$dbiuser", "$dbipass")
        or die("<p>Could not connect</p>");
    //print "Connected successfully";
    mysql_select_db("$dbi") or die("<p>Could not select database</p>");

	$j = 0;
	while ($j < $i) {
	
		/* Create an acceptable WP post_name */
		$post_name = sanitize_title_with_dashes($import[1][$j]);

		$query5 = "SELECT ID from ".$wptblprefix."users WHERE ".$wptblprefix."users.user_login = '$authorusername'";
		echo "<br />".$query5."<br />";
		$result5 = mysql_query($query5);
		while ($row=mysql_fetch_array($result5)) 
		{ 
		$ID5 = $row["ID"];
		}
		
		/* Do the actual query */
		$query = "INSERT INTO ".$wptblprefix."posts (id, post_author, post_title, post_content, post_date, post_modified, post_name, post_category) VALUES ('', '$ID5', '{$import[1][$j]}', '{$import[2][$j]}', '{$import[3][$j]}', '{$import[4][$j]}', '$post_name', '$wpsection')";
//Check this query to see if it works
//		$query = "INSERT INTO wp_posts (id, post_title, post_content, post_date, post_modified, post_name, post_category) VALUES ('', '{$import[1][$j]}', '{$import[2][$j]}', '{$import[3][$j]}', '{$import[4][$j]}', '$post_name', '$wpsection')";		
		
		/* For debugging purposes */
		echo "<br />".$query."<br />";
		$result = mysql_query($query) or die("<p>Query failed inserting into categories</p>");

		$query2 = "SELECT term_taxonomy_id FROM ".$wptblprefix."term_taxonomy WHERE ".$wptblprefix."term_taxonomy.term_id = $wpsection AND ".$wptblprefix."term_taxonomy.taxonomy = 'category'";

		/* For debugging purposes */
		echo "<br />".$query2."<br />";

		$result2 = mysql_query($query2);

		while ($row=mysql_fetch_array($result2)) 
		{ 
		$term_taxonomy_id = $row["term_taxonomy_id"];
		}
		
		$query3 = "SELECT id from ".$wptblprefix."posts WHERE ".$wptblprefix."posts.post_title = '{$import[1][$j]}'";
		
		/* For debugging purposes */
		echo "<br />".$query3."<br />";

		$result3 = mysql_query($query3);

		while ($row=mysql_fetch_array($result3)) 
		{ 
		$id2 = $row["id"];	
		}
			
		$query4 = "INSERT INTO ".$wptblprefix."term_relationships (object_id, term_taxonomy_id) VALUES ('$id2', '$term_taxonomy_id')";
		
		/* For debugging purposes */
		echo "<br />".$query4."<br />";

		$result4 = mysql_query($query4) or die("Query failed");

		$j++;
	}

   /* Closing connection */
    mysql_close($link);
	
?>

	<p><em>Finished!</em></p>
<?php
	echo "<p><a href=\"index.php\">Go back to the beginning</a>.</p>\n";
	break;
	case 6:
	// Select a WP link category to import from the category.
	echo "<h1>Step Two</h1>";
	$link = mysql_connect("$dbihost", "$dbiuser", "$dbipass")
        or die("<p>Could not connect</p>");
    //print "Connected successfully";
    mysql_select_db("$dbi") or die("<p>Could not select database</p>");

	//$query = "SELECT cat_id, cat_name FROM `wp_linkcategory`";
	$query ="SELECT ".$wptblprefix."terms.term_id, name FROM `".$wptblprefix."terms` LEFT OUTER JOIN ".$wptblprefix."term_taxonomy ON ".$wptblprefix."terms.term_id = ".$wptblprefix."term_taxonomy.term_id WHERE ".$wptblprefix."term_taxonomy.taxonomy = 'link_category'";
	$result = mysql_query($query) or die("Query failed");
	?>
	<p>Select a WordPress category to import from your section.</p>
	<form action="index.php" name="wpcat" id="wpcat">
	    <select name="wpsection" class="import" onChange="submit('document.wpcat');">
          <option selected>-- select a WordPress section --</option>
	      <?php
	    while ($row = mysql_fetch_assoc($result)) {
		?>
    
	    <option value="<?php echo $row["term_id"]; ?>"><?php echo $row["name"]; ?></option>
	      <?php } ?>
    
    </select>
	    <input name="step" type="hidden" id="step" value="7">
	    <input name="category" type="hidden" id="step" value="<?php echo $category; ?>">
	</form>
<a href="index.php?step=1"><h2>Back to step 1</h2></a>

<?php
	break;
	case 7:
	//require_once ("../wp-includes/functions-formatting.php");
	//require_once ("../wp-includes/functions.php");

	echo "<h1>Step Three</h1>";
	echo "<p>Category= ".$category."</p>";
	echo "<p>WPsection= ".$wpsection."</p>";

	/* Make connection to Mambo (exporting) database */
    $link = mysql_connect("$dbhost", "$dbuser", "$dbpass")
        or die("Could not connect");
    //print "Connected successfully";
    mysql_select_db("$dbh") or die("Could not select database");

    /* Performing SQL query */
    $query = "SELECT title, url, `description` FROM ".$joomlatblprefix."weblinks WHERE `catid` = '$category'";
	//echo $query."<br />\n";
    $result = mysql_query($query) or die("Query failed");

    /* Load database values into an array, making sure to escape them for quotes, etc. */
	$i = 0;

    while ($row = mysql_fetch_assoc($result)) {
			$import[0][$i] = mysql_escape_string($row["title"]);
			$import[1][$i] = mysql_escape_string($row["url"]);
			$import[2][$i] = mysql_escape_string($row["description"]);
			$i++;
    }
	    /* Free result set */
    mysql_free_result($result);
	
   /* Closing connection */
    mysql_close($link);
/* For debugging purposes */
/*	$j = 0;
	echo "i = ".$i."<br />";
	while ($j < $i) {
			echo "id: ".$import[0][$j]."<br />\n";
        	echo "title: ".$import[1][$j]."<br />\n";
        	echo "introtext: ".$import[2][$j]."<br />\n";        
        	echo "created: ".$import[3][$j]."<br />\n";
        	echo "modified: ".$import[4][$j]."<br /><br />\n";
			$j++;
	}
*/

	echo "<tr><td>Importing ".$i." item";
	if ($i != 1) {
		echo "s";
	}

	/* Make connection to Word Pro (importing) database */
	$link = mysql_connect("$dbihost", "$dbiuser", "$dbipass")
        or die("<p>Could not connect</p>");
    //print "Connected successfully";
    mysql_select_db("$dbi") or die("<p>Could not select database</p>");

	$j = 0;
	while ($j < $i) {
	
		/* Create an acceptable WP post_name */
		//$post_name = sanitize_title_with_dashes($import[1][$j]);
		
		/* Do the actual query */
		$query = "INSERT INTO ".$wptblprefix."links (link_id, link_url, link_name, link_description, link_category) VALUES ('', '{$import[1][$j]}', '{$import[0][$j]}', '{$import[2][$j]}', '$wpsection')";
		/* For debugging purposes */
		echo "<br />".$query."<br />";
		$result = mysql_query($query) or die("<p>Query failed</p>");
		
		
		$query2 = "SELECT term_taxonomy_id FROM ".$wptblprefix."term_taxonomy WHERE ".$wptblprefix."term_taxonomy.term_id = $wpsection AND ".$wptblprefix."term_taxonomy.taxonomy = 'link_category'";

		/* For debugging purposes */
		echo "<br />".$query2."<br />";
		$result2 = mysql_query($query2);
		while ($row=mysql_fetch_array($result2)) 
		{ 
		$term_taxonomy_id = $row["term_taxonomy_id"];
		}
		
		$query3 = "SELECT link_id from ".$wptblprefix."links WHERE ".$wptblprefix."links.link_url = '{$import[1][$j]}'";
		
		/* For debugging purposes */
		echo "<br />".$query3."<br />";

		$result3 = mysql_query($query3);

		while ($row=mysql_fetch_array($result3)) 
		{ 
		$id2 = $row["link_id"];	
		}
		
		$query4 = "INSERT INTO ".$wptblprefix."term_relationships (object_id, term_taxonomy_id) VALUES ('$id2', '$term_taxonomy_id')";
		
		/* For debugging purposes */
		echo "<br />".$query4."<br />";		

		$result4 = mysql_query($query4) or die("Query failed");
		
		
		$j++;
	}

   /* Closing connection */
    mysql_close($link);
	
?>

	<p><em>Finished!</em></p>

<?php
	echo "<p><a href=\"index.php\">Go back to the beginning</a>.</p>\n";

	break;

}
?>
<p Credits>
<p id="footer"><p><b>Credits</b><br>
v1: <a href="http://www.blevins.nl/missiontech/">Mambo2Wordpress Import Wizard</a> 
by <a href="http://www.blevins.nl/missiontech">Mission:Tech</a>, technology blog.<br>
v2:<a href="http://www.mediafire.com/?6dsuxjjm0ig"><u>Joomla To Wordpress</u></a> 
by Chris Chee of
<a href="http://rangit.com/software/6-steps-how-to-migrate-from-joomla-to-wordpress/">
Rangit.com</a><br>
v3: <a href="http://azeemkhan.info/2008/joomla2wordpress-import-wizard-v3/">Joomla2WordPress Import</a> Wizard by 
Azeem Khan of <a href="http://www.azeemkhan.info">Azeemkhan.info</a></p></p>
</body>
</html>
