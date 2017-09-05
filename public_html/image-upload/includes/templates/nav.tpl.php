        <nav>
            <ul>
                <li>
                    <a href="index.php">Index</a>
                </li>
                <?php if( is_logged_in( $config ) ): ?>
                <li><a href="upload.php">Upload</a></li>
                <?php endif; ?>
                
                <?php if( is_logged_in( $config ) ): ?>
                <li><a href="logout.php">Log Out</a></li>
                <?php else: ?>
                <li><a href="login.php">Log In</a></li>
                <?php endif; ?>
            </ul>
        </nav>