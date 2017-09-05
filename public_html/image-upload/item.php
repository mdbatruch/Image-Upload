<?php
 
    session_start();

    require( 'includes/functions.inc.php' );

    $config = get_configuration( '../../data/config.json');

    $config->pageTitle = 'Gallery';

    $db = mysqli_connect ( $config->db->host, $config->db->user, $config->db->password, $config->db->name )
            or die( mysqli_connect_error() );

    $id = intval( sanitize( $db, $_GET[ 'id' ] ) );

    if( !is_numeric( $id ) ){
        redirect( 'index.php' );
    }
    
    $query = "SELECT * FROM image_app_content
                    WHERE id = $id";
    
    $result = mysqli_query( $db, $query )
                or die( mysqli_error( $db ) );

    if( mysqli_num_rows( $result ) < 1 ) {
        
        //the id doesn't exist in the database, so go back to the homepage
        redirect( 'index.php' );
    }
    
    $row = mysqli_fetch_assoc( $result );

    //set the page title. if there is no title, then set default to Untitled.
    $config->pageTitle = strlen( $row[ 'title' ] ) > 0 ? $row[ 'title' ] : 'Untitled';
    
    //add p tags to description
    $description = explode( "/n", $row[ 'description' ] );
    $description = '<p>' . implode( '</p></p>', $description ) . '</p>';
    $row[ 'description' ] = $description;

    $created_date = date( 'l F j, Y @ h:i a', strtotime( $row[ 'created_date' ] ) );

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
        
        <!--        JQUERY CDN         -->
        <script
                  src="https://code.jquery.com/jquery-1.12.4.js"
                  integrity="sha256-Qw82+bXyGq6MydymqBxNPYTaUXXq7c8v3CwiYwLLNXU="
                  crossorigin="anonymous"></script>
        
        <!--        MASONRY PLUG IN         -->
        <script src="js/masonry/dist/masonry.pkgd.min.js"></script>
        <!--        IMAGES LOADED         -->
        <script src="js/imagesloaded/imagesloaded.pkgd.min.js"></script>
        
        <!--        CUSTOM SCRIPT         -->
        <script src="js/gallery.js"></script>
        
        <!-- HTML5Shiv: adds HTML5 tag support for older IE browsers -->
        <!--[if lt IE 9]>
	    <script src="js/html5shiv.min.js"></script>
        <![endif]-->
    </head>
    <body>
        <?php include( 'includes/templates/header.tpl.php' ); ?>
        <?php include( 'includes/templates/nav.tpl.php' ); ?>
        <main>
            <article>
                <img src="<?php echo $config->largeFolder
                                    . $row['filename']?>" 
                     alt="<?php echo $row['title']?>" />
                <h3><?php echo $row['title']?></h3>
                <time datetime="<?php echo $row['created_date']?>">
                <?php echo $created_date; ?>
                </time>
                <?php echo $row['description']?>
            </article>
        </main>
    </body>
</html>