<?php

      function redirect( $url ){
            
            header( 'Location: ' . $url );
            die( "Redirect to <a href=\"$url\">$url</a> failed.");
        }

      function get_configuration( $path ){
        $config = file_get_contents( $path );
        $config = json_decode( $config );
        
        return $config;
    }


        /*
                SANITIZES DATA FOR USE IN A MYSLI QUERY.
                @param resource $db the database connection resource.
                @param string | int | float $data VALUE TO SANITIZE.
                @return string SANITIZED VERSION OF THE DATA.
        */

        function sanitize( $db, $data ){
            
            $data = trim( $data );
            $data = strip_tags( $data );
            $data = mysqli_real_escape_string( $db, $data );
            
            return $data;
        }

    function resize_to_fit( $image_filepath, $destination_folder, $dimensions, $quality ){
        
        $info = getimagesize( $image_filepath );
        
        //get the type of image it is
        $type = $info[ 'mime' ];
        
        //get original image width
        $original_width = $info[ 0 ];
        
        //get original image height
        $original_height = $info[ 1 ];
        
        //read the image into the web server's memory
        switch( $type ){
                
            case 'image/png':
                $original_image = imagecreatefrompng( $image_filepath );
                break;
                
            case 'image/gif':
                $original_image = imagecreatefromgif( $image_filepath );
                break;
                
            case 'image/jpeg':
            case 'image/jpg':
                $original_image = imagecreatefromjpeg( $image_filepath );
                break;
                
            default:
                return false;
                break;
        }
        
        //disable the blending of the alpha channel which would only create opaque pixels
        imagealphablending( $original_image, false );
        //enable the complete alpha channel so you can get translucent pixels
        imagesavealpha( $original_image, true );
        
        //calculate aspect ratio
        $aspect_ratio = $original_height / $original_width;
        
        //calculate resized width & height
        if ( $aspect_ratio > 1 ){
            //portrait image
            $resized_height = $dimensions;
            $resized_width = $resized_height / $aspect_ratio;
        } else {
            //landscape or square image
            $resized_width = $dimensions;
            $resized_height = floor($resized_width * $aspect_ratio);
        }
        
        //create an empty image in memory to match resized dimensions
        $resized_image = imagecreatetruecolor( $resized_width,
                                                $resized_height );
        
        $transparent =imagecolorallocatealpha( $resized_image, 
                                               0,
                                               0,
                                               0,
                                               127 );
        
        //fill the image with transparency
        imagefill( $resized_image, 0, 0, $transparent );
        
        //disable the blending of the alpha channel which would only create opaque pixels
        imagealphablending( $resized_image, false );
        //enable the complete alpha channel so you can get translucent pixels
        imagesavealpha( $resized_image, true );
        
        //copy and resample pixels from large image to small
        imagecopyresampled ( $resized_image,
                             $original_image,
                             0, 0, 0, 0,
                             $resized_width,
                             $resized_height,
                             $original_width,
                             $original_height
                            );
        
                   //explode function breaks up a string
        $filename = explode( '/', $image_filepath );
        
        //array_pop takes last part of the array variable (actual file name and extension) and put it in the $filename variable
        $filename = array_pop( $filename );
        
        //append filename to desired destination folder
        $resized_filepath = $destination_folder . $filename;
        
        //write the resized image to the destination folder
        switch( $type ){
                
            case 'image/png':
                //convert 0 to 10 -> 9 to 0
                $png_quality = 9 - ( ( $quality / 10 ) * 9);
                imagepng( $resized_image, $resized_filepath, $png_quality );
                break;
                
            case 'image/gif':
                imagegif( $resized_image, $resized_filepath );
                break;
                
            case 'image/jpeg':
            case 'image/jpg':
                imagejpeg( $resized_image, $resized_filepath, $quality * 10 );
                break;
                
            default:
                return false;
                break;
        }
        
        //free up memory after the task is completed
        imagedestroy( $original_image );
        imagedestroy( $resized_image );
        
        return $resized_filepath;
        
        
    }

 function log_in( $database, $config, $email, $password ){
            
            $errors = array();
                
            //VALIDATE THAT EMAIL IS IN PROPER FORMAT
                if( !filter_var( $email, FILTER_VALIDATE_EMAIL ) ){
                    $errors[ 'email' ] = '<p class="error">
                                            Please enter a proper email.</p>';
                    
                }
            
            //VALIDATE THAT PASSWORD IS CORRECT
                if( strlen( $password ) < 1 ){
                    
                    $errors[ 'password' ] = '<p class="error">
                                            Please enter a proper password.</p>';
                }
            
            if( count( $errors ) == 0 ){
                
                $email = sanitize( $database, $email );
                
                $addition = "SELECT
                                    id,
                                    password
                                        FROM image_app_users
                                        WHERE email ='$email'
                                        LIMIT 1";
                
                $result = mysqli_query( $database, $addition )
        or die( mysqli_error( $database ) );
                
                if( mysqli_num_rows( $result ) > 0){
                    
                    $row = mysqli_fetch_assoc( $result );
                    
                    if( password_verify( $password, $row[ 'password'] ) ){
                        
                        $_SESSION[ 'login_token' ] = $config->loginToken;
                        $_SESSION[ 'user_id' ] = $row[ 'id' ];
                        $_SESSION[ 'email' ] = $email;
                        
                        redirect( 'index.php' );
                    } else {
                        
                        $errors[ 'password' ] = '<p class="error">
                                                    Wrong Password.
                                                </p>';
                    } 
                }else {
                        
                        $errors[ 'email' ] = '<p class="error">
                                                    No Email like this.
                                                </p>';
                    }
            }
            
            return $errors;
        }

  function check_login( $config ){

            //if user is not logged in
            if( !is_logged_in($config) ) {
                redirect( 'login.php' );
            }
        }

function is_logged_in( $config ){

            if( strcmp( $_SESSION[ 'login_token' ], $config->loginToken ) != 0 ){
                
               return false;
                
            } else {
                
                return true;
            } 
        }


      function logout(){
            
            $_SESSION[ 'login_token' ] = null;
            $_SESSION[ 'user_id' ] = null;
            $_SESSION[ 'email' ] = null;
            
            unset( $_SESSION[ 'login_token' ] );
            unset( $_SESSION[ 'user_id' ] );
            unset( $_SESSION[ 'email' ] );
       
          redirect( 'index.php' );
      }

