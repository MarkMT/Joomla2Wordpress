<?php

/*
  Generate wordpress post_content field from mambo/joomla content data containing images - Mark M. Thomson, July 2010
  -------------------------------------------------------------------------------------------------------------------
    - Purpose: This function takes a row of the mambo/joomla database table 'mos_content' representing a single 
      article and returns a text string that can be inserted in the 'post_content' field of a row of the wordpress 
      'wp-posts' table (after first being escaped separately with 'mysql_escape_string'). It also copies images 
      associated with each mambo/joomla articles into the corresponding wordpress article.

    - Assumptions: The function assumes that mambo/joomla is installed in the root directory and that its images are in 
      /images/stories. It also assumes that wordpress is installed in a subdirectory of the directory that 
      mambo/joomla is installed in, e.g. <root>/wordpress and that this script is located in <root>/wordpress/export.

    - Input Parameter: $row is an associative array of fields from a single mambo/joomla content record, e.g. a row 
      of the mos_content table representing a single article. The fields used to generate the wordpress post_content 
      field are 'introtext', 'fulltext', and 'images' (the 'created' field is also used to generate image file 
      directories - see below).

    - Image Handling: The function copies images associated with mambo/joomla articles into a sub-directory of 
      wordpress/wp-content corresponding to the year/month in which the article was created and replaces "{mosimage}" 
      strings in the mambo/joomla article with html <a> and <img> elements required by wordpress. These are optionally 
      wrapped with "[caption]...[/caption]" tags if the mambo/joomla image has an attached caption or with <p>...</p> 
      if the image is center aligned without a caption (this is what wordpress does).

      Captions are positioned below the image with center alignment, regardless of how the caption is specified
      in mambo/joomla. The caption width parameter contained in the mambo/joomla data is ignored since wp places the
      caption in a frame that wraps the image.

      For captioned images, the width must be specified explicitly in both the <img> and [caption] tags to ensure 
      that the caption is rendered. Here, the width attribute is set to either the actual image width or the 
      value of the variable $wp_max_image_width defined below according to the theme's column width, which ever is 
      smaller.
      
      Note that in mambo (and presumably joomla) images are displayed at their true size. Some wordpress themes 
      (notably the standard Kubrick and Twenty Ten themes) have a css rule for '#content img' that sets the 'max-width', 
      so if required large images will be scaled down to fit the content column width.

*/

function article_with_images($row) {

  /* get mambo/joomla article data */
  if ( empty($row["introtext"]) || empty($row["fulltext"]) ) {
    $article = $row["introtext"] . $row["fulltext"];
  } else {
  /* remove any existing linebreaks on the end of the introtext so that the breaks added here between   */
  /* the introtext and fulltext will result in the correct paragraph spacing.                           */
    $row["introtext"] = preg_replace('|(<br />)+\s*$|', '', $row["introtext"]);
    $article = $row["introtext"]."<br /><!--more--><br />".$row["fulltext"];
  }

  /* create an array containing the data for each image in the article */
  $images = preg_split('/((\\\\r)|(\\\\n))+/', mysql_escape_string($row["images"]));
  $N_images = sizeof($images);

  if ($N_images > 0) {

    /* some configuration */
    $wp_article_width = 635;                       /* Set this to whatever your wordpress template requires */
    $wp_max_image_width = $wp_article_width - 18;  /* Allow for a 9px border on each side of wordpress captions */
    $omask = umask(0);                             /* Clear permissions for new file creation */

    /* parent directories for images */
    $mos_relative_image_dir = "../../images/stories/";    /* image storage in mambo/joomla */
    if (!file_exists("../wp-content/uploads"))
      mkdir("../wp-content/uploads", 0777);               /* image storage in wordpress    */

    /* parameters for date-specific wp image sub-directories */
    $date = mysql_escape_string($row["created"]);
    $year = date("Y", strtotime($date)); 
    $month = date("m", strtotime($date)); 

    /* Find each {mosimage} in the original article */
    $i = 0;
    while (strstr($article, "{mosimage}") && ($i < $N_images)) {

      /* Create new directories for the image files in wp if required */
      if (!file_exists("../wp-content/uploads/" . $year))
        mkdir("../wp-content/uploads/" . $year, 0777);
      if (!file_exists("../wp-content/uploads/" . $year . "/" . $month))
        mkdir("../wp-content/uploads/" . $year . "/" . $month, 0777);
    
      /* path to the wp image directory relative to this script */
      $wp_relative_image_dir = "../wp-content/uploads/" . $year . "/" . $month . "/";
    
      /* url of the wp image directory */
      $wp_image_url_prefix = get_option( 'siteurl' ) . "/wp-content/uploads/" . $year . "/" . $month . "/";
    
      /* Split the image data into its components                                  */
      /* The values provided by mambo/joomla are as follows -                      */
      /*   $image_data[0] - filename                                               */
      /*   $image_data[1] - image alignment ('none', 'center', 'left', 'right')    */
      /*   $image_data[2] - alt text                                               */
      /*   $image_data[3] - border width                                           */
      /*   $image_data[4] - caption text                                           */
      /*   $image_data[5] - caption position ('bottom', 'top')                     */
      /*   $image_data[6] - caption alignment ('none', 'center', 'left', 'right')  */
      /*   $image_data[7] - image width                                            */
      $image_data = explode("|", $images[$i]);

      /* Copy the image file */
      $filename = $image_data[0];
      $basename = basename($filename);  /* extracts just the filename from the file's full path */
      if (!file_exists($wp_relative_image_dir . $basename)) {
        if (file_exists($mos_relative_image_dir . $filename)) {
          echo "Copying image from " . $mos_relative_image_dir . $filename . "  to " . $wp_relative_image_dir . $basename . "<br>";
          if (!copy($mos_relative_image_dir . $filename, $wp_relative_image_dir . $basename))
            echo "<br />Copy failed";
        } else {
          echo "<br />File not found";
        }
      }

      /* Compose the image tag string used to replace {mosimage} in the mambo/joomla article */
      $wp_image_url = $wp_image_url_prefix . $basename;
      if (sizeof($image_data) == 1) {   /* the simple case - we only have a filename */

        $image_tag = "<img class=\"size-full\" src=\"" . $wp_image_url . "\">";

      } else {

        /* src */
        $image_tag = "src=\"" . $wp_image_url . "\"";

        /* alt text */
        $image_tag = $image_tag . " alt=\"" . $image_data[2] . "\"";
      
        /* if there is a caption, specify the image width */
        if(!empty($image_data[4])) {
          $size = getimagesize($wp_relative_image_dir . $basename);
          $image_width = $size[0];
          if ($image_width > $wp_max_image_width)
            $image_width = $wp_max_image_width;
          $image_tag = $image_tag . " width=\"" . $image_width . "\"";
        }

        /* border */
        if (!empty($image_data[3]))
          $image_tag = "style=\"border:" . $image_data[3] . "px solid black\" " . $image_tag;

        /* horizontal alignment (only if there is no caption - otherwise this goes on the caption tag below) */
        if(empty($image_data[4])) {
          if (empty($image_data[1]))
            $image_data[1] = 'none';
          $image_tag = "class=\"align" . $image_data[1] . "\" " . $image_tag;
        }

        /* img tag wrapper */
        $image_tag = "<a href=\"" . $wp_image_url . "\"><img " . $image_tag . " ></a>";


        if(!empty($image_data[4])) {

          /* caption wrapper */
          if (empty($image_data[1]))
            $image_data[1] = 'none';
          $image_tag = "[caption align=\"" . "align" . $image_data[1] . "\" width=\"" . $image_width . "\" caption=\"" . htmlspecialchars($image_data[4]) . "\"]" . $image_tag . "[/caption]";
        } elseif ($image_data[1] == "center") {

          /* Not certain why, but wp wraps the image as follows if there   */
          /* is no caption and the horizontal alignment is 'center'        */
          $image_tag = "<p style=\"text-align: center;\">" . $image_tag . "</p>";
        }

      }

      /* replace {mosimage} */
      $article = preg_replace("/\{mosimage\}/", $image_tag, $article, 1);

      $i++;

    }

    umask($omask);

  }

  /* Remove spurious newlines, so they don't get replaced with "<br />" 's by wordpress's wpautop               */
  /* filter (This filter is defined in ./wp-includes/formatting.php and is added to tag 'the_content' in        */
  /* ./wp-includes/default-filters.php. The filter is applied in function 'the_content()' defined in            */
  /* ./wp-includes/post_template.php, which is where the article gets rendered. 'the_content()' is invoked      */
  /* from wp-content/themes/<theme_name>/index.php).                                                            */
  $article = preg_replace('|(?<!<br />)\r\n|', " ", $article);

  return $article;

};

?>

