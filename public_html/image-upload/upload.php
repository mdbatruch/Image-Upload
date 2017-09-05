<?php 

    session_start();
    
    require( 'includes/functions.inc.php' );

    $config = get_configuration( '../../data/config.json');

    $config->pageTitle = 'Upload';

    check_login( $config );

    

    $db = mysqli_connect ( $config->db->host, $config->db->user, $config->db->password, $config->db->name )
            or die( mysqli_connect_error() );

    $errors = array();

    $preview = false;

    echo $_SERVER['UPLOAD_ERR_OK'];

    //IF THE FILE HAS BEEN SUBMITTED, THEN RUN THIS CODE
    if( isset( $_POST[ 'submitted' ] ) ){
        
         //CHECK IF THERE IS A FILENAME SUBMITTED
        if( strlen( $_FILES[ 'user-image' ][ 'name' ] ) > 0 ){
            
        $temp_location = $_FILES[ 'user-image' ][ 'tmp_name' ];
            
            if( ($_FILES[ 'user-image' ][ 'size' ] > $config->maxFileSize ) 
              or ($_FILES[ 'user-image' ][ 'error' ] == UPLOAD_ERR_INI_SIZE )
              ){
                
                $maxSize = round( $config->maxFileSize / 1024 );
                //file is too big
                $errors[ 'size' ] = "<p class=\"error\">
                                    File size is too large! Must be less than {$maxSize} KB.
                                    </p>";
            }
            
            //select uploaded file
            $info = getimagesize( $temp_location );
            //if file type is an image
            if( !$info 
                or ( strpos( $config->allowedFileTypes, $info[ 'mime' ] ) === false )  ){
                //file is either corrupted or not the correct file type, then run the following code
                $errors[ 'type' ] = "<p class=\"error\">
                                    Incorrect File type, please try with any of the following(JPEG, PNG, GIF)
                                    </p>";
            }
            
            
            
            if( count( $errors ) == 0 ){
            
        if ($config->randomizeFilenames ){
            
            //unique hash for the filename
            $hash = sha1( microtime() );
            
            // get the original extension
            $extension = explode ( '.', $_FILES[ 'user-image' ][ 'name' ] );
            $extension = array_pop( $extension );
            
            //combine it all together
            $final_location = "{$config->uploadsFolder}{$hash}.{$extension}";
        }
        else {
        $final_location = $config->uploadsFolder . $_FILES[ 'user-image' ][ 'name' ];
        }
            
            if( move_uploaded_file( $temp_location, $final_location ) ){
                
                //thumbnail
                resize_to_fit( $final_location,
                                            $config->smallFolder,
                                            $config->imageSize->small,
                                            $config->imageQuality );
                //medium
                resize_to_fit( $final_location,
                                            $config->mediumFolder,
                                            $config->imageSize->medium,
                                            $config->imageQuality );
                //large
                $preview = resize_to_fit( $final_location,
                                            $config->largeFolder,
                                            $config->imageSize->large,
                                            $config->imageQuality );
                
                //insert into database
                $filename = explode( '/', $final_location );
                $filename = array_pop( $filename );
                
                //gather other details
                $title = sanitize( $db, $_POST['title'] );
                $description = sanitize( $db, $_POST['description'] );
                $user_id = $_SESSION[ 'user_id' ];
                
                var_dump( $_SESSION );
                var_dump( $user_id );
                
                //query the database
                $query = "INSERT into 
                                image_app_content(user_id,
                                filename,
                                title,
                                description)
                        VALUES($user_id,
                                '$filename', 
                                '$title', 
                                '$description')" ;
                
                $result = mysqli_query( $db, $query )
                            or die( $query . '<br />' . mysqli_error( $db ) );
                
                
            } else {
                //COULD NOT MOVE FILE
                $preview = false;
                
            }
                }
            
        } else {
            
            $errors[ 'file' ] = "<p class=\"error\">Please insert a proper file type</p>";
        }
    }

?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width,initial-scale=1" />
        <title>
            <?php echo $config->pageTitle; ?>
            - <?php echo $config->appTitle; ?>
        </title>
        
        <!-- main stylesheet link -->
        <link rel="stylesheet" href="css/style.css" />
        
        <!-- HTML5Shiv: adds HTML5 tag support for older IE browsers -->
        <!--[if lt IE 9]>
	    <script src="js/html5shiv.min.js"></script>
        <![endif]-->
    </head>
    <body>
        <?php include( 'includes/templates/header.tpl.php' ); ?>
        <?php include( 'includes/templates/nav.tpl.php' ); ?>
        
        <main>
               <?php if ( $preview ): ?>
                   <img 
                        src="<?php echo $preview; ?>"
                        style="max-width: 100%;"
                        alt="preview" />
               <?php endif; ?>
               
            <form action="<?php $_SERVER[ 'REQUEST_URI' ]?>" 
                method="post"
                enctype="multipart/form-data">
                
                <!--       HIDDEN INPUT TO DETECT FORM SUBMISSION         -->
                <input type="hidden" name="submitted" />
                
                <ol>
                   <li>
                      <?php echo $errors[ 'file' ]; ?>
                      <?php echo $errors[ 'size' ]; ?>
                      <?php echo $errors[ 'type' ]; ?>
                      <div class ="file-input-container">
                           <input type="file" 
                              name="user-image" />
                           <span>Browse for Image</span>
                       </div>
                   </li>
                   <li>
                       <label>Title</label>
                       <input type="text" name="title" size="80" value="<?php echo $_POST['title']; ?>"/>
                   </li>
                   <li>
                       <label>Description</label>
                       <textarea name="description" cols="80" rows="6">
                           <?php echo $_POST['description']; ?>
                       </textarea>
                   </li>
                    <li>
                        <input type="submit" 
                               value="Save and Upload" />
                    </li>
                </ol>
                 
            </form>
        </main>
    </body>
</html>