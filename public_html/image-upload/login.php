<?php

    session_start();

    require( 'includes/functions.inc.php' );

    $config = get_configuration( '../../data/config.json' );

    $config->pageTitle = 'Login';

    $db = mysqli_connect ( $config->db->host, $config->db->user, $config->db->password, $config->db->name )
            or die( mysqli_connect_error() );

    if ( isset( $_POST[ 'email' ] ) ){
        $errors = log_in( $db, 
                          $config,
                          $_POST[ 'email' ], 
                          $_POST[ 'password'] );
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
            <form action="<?php echo $_SERVER[ 'REQUEST_URI' ]; ?>" method="post">
                <ul>
                    <li>
                      <?php echo $errors[ 'email' ]; ?>
                       <label>Email</label>
                        <input type="text" 
                               size="80"
                               name="email"
                               value="<?php echo $_POST[ 'email' ]; ?>"/>
                    </li>
                    
                    <li>
                       <?php echo $errors[ 'password' ]; ?>
                        <label>Password</label>
                        <input type="password" 
                               size="80"
                               name="password" />
                    </li>
                    
                    <li>
                        <input type="submit" name="Log In" />
                    </li>
                </ul>
            </form>
        </main>
    </body>
</html>