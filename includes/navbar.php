<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
    <div class="container">
        <a class="navbar-brand" href="<?php echo BASE_URL; ?>">
            <img src="<?php echo IMAGES_URL; ?>logo.png" alt="Akasa Teknik" height="40" onerror="this.onerror=null; this.src='<?php echo IMAGES_URL; ?>default_logo.png';">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-expanded="false" aria-controls="navbarNav">
            <span class="navbar-toggler-icon">
                <span></span>
            </span>
        </button>
        
        <!-- Profile/Login button - always visible on right -->
        <?php if(isset($_SESSION['admin_id'])): ?>
            <a href="<?php echo ADMIN_URL; ?>logout.php" class="btn btn-danger ms-auto d-lg-none">
                <i class="fas fa-sign-out-alt me-1"></i> Logout
            </a>
        <?php elseif(isset($_SESSION['user_id'])): ?>
            <div class="dropdown ms-auto d-lg-none">
                <button class="btn btn-success dropdown-toggle" type="button" id="mobileDropdownMenuButton" 
                        data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user me-1"></i> 
                    <?php echo htmlspecialchars($_SESSION['nama_lengkap'] ?? 'User', ENT_QUOTES, 'UTF-8'); ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="mobileDropdownMenuButton">
                    <li><a class="dropdown-item" href="<?php echo USER_URL; ?>profile.php">Profil Saya</a></li>
                    <li><a class="dropdown-item" href="<?php echo USER_URL; ?>orders.php">Riwayat Sewa</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="<?php echo USER_URL; ?>logout.php">Logout</a></li>
                </ul>
            </div>
        <?php else: ?>
            <a href="<?php echo USER_URL; ?>login.php" class="btn btn-success ms-auto d-lg-none">
                <i class="fas fa-sign-in-alt me-1"></i> Login
            </a>
        <?php endif; ?>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <?php if(isset($_SESSION['admin_id'])): ?>
                    <!-- Admin gets no navbar items -->
                <?php else: ?>
                    <!-- Regular users get full navbar -->
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo PAGES_URL; ?>produk.php">Produk</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo PAGES_URL; ?>kontak.php">Kontak</a>
                    </li>
                <?php endif; ?>
            
            </ul>
            
            <!-- Desktop profile/login button -->
            <?php if(isset($_SESSION['admin_id'])): ?>
                <a href="<?php echo ADMIN_URL; ?>logout.php" class="btn btn-danger ms-auto d-none d-lg-inline">
                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                </a>
            <?php elseif(isset($_SESSION['user_id'])): ?>
                <div class="dropdown ms-auto d-none d-lg-inline">
                    <button class="btn btn-success dropdown-toggle" type="button" id="desktopDropdownMenuButton" 
                            data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user me-1"></i> 
                        <?php echo htmlspecialchars($_SESSION['nama_lengkap'] ?? 'User', ENT_QUOTES, 'UTF-8'); ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="desktopDropdownMenuButton">
                        <li><a class="dropdown-item" href="<?php echo USER_URL; ?>profile.php">Profil Saya</a></li>
                        <li><a class="dropdown-item" href="<?php echo USER_URL; ?>orders.php">Riwayat Sewa</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?php echo USER_URL; ?>logout.php">Logout</a></li>
                    </ul>
                </div>
            <?php else: ?>
                <a href="<?php echo USER_URL; ?>login.php" class="btn btn-success ms-auto d-none d-lg-inline">
                    <i class="fas fa-sign-in-alt me-1"></i> Login
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>
