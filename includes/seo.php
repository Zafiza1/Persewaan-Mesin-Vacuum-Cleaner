<?php
/**
 * SEO Helper Functions
 * Provides SEO optimization functions for meta tags, structured data, etc.
 */

/**
 * Generate SEO-optimized meta tags for different pages
 * @param string $page Current page identifier
 * @param array $data Additional data for meta tags
 * @return array SEO meta tags
 */
function generate_seo_meta($page, $data = []) {
    $base_title = "Sewa Mesin Vacuum Cleaner - Akasa Teknik";
    $base_desc = "Jasa sewa mesin vacuum cleaner profesional di Sidoarjo. Harga terjangkau, mesin terawat, pengantaran tepat waktu.";
    $base_url = BASE_URL;
    
    $seo_data = [
        'title' => $base_title,
        'description' => $base_desc,
        'keywords' => 'sewa vacuum cleaner, persewaan mesin vacuum, vacuum cleaner industri, akasa teknik, mesin vacuum cleaner, rental vacuum cleaner, layanan vacuum cleaner, sidoarjo, surabaya',
        'canonical' => $base_url,
        'og_title' => $base_title,
        'og_description' => $base_desc,
        'og_image' => IMAGES_URL . 'logo.png',
        'og_url' => $base_url,
        'og_type' => 'website',
        'twitter_card' => 'summary_large_image',
        'twitter_title' => $base_title,
        'twitter_description' => $base_desc,
        'twitter_image' => IMAGES_URL . 'logo.png'
    ];
    
    switch($page) {
        case 'home':
            $seo_data['title'] = "Sewa Vacuum Cleaner Profesional di Sidoarjo | Akasa Teknik";
            $seo_data['description'] = "Sewa mesin vacuum cleaner profesional di Sidoarjo dengan harga terjangkau. Mesin terawat, pengantaran tepat waktu.";
            $seo_data['canonical'] = $base_url;
            break;
            
        case 'produk':
            $seo_data['title'] = "Daftar Harga Sewa Vacuum Cleaner | Akasa Teknik";
            $seo_data['description'] = "Lihat daftar lengkap mesin vacuum cleaner yang tersedia untuk disewa. Vacuum 21L, 40L, 80L dengan harga terbaik di Sidoarjo.";
            $seo_data['canonical'] = $base_url . 'pages/produk.php';
            $seo_data['og_type'] = 'product';
            break;
            
        case 'sewa':
            $seo_data['title'] = "Form Sewa Vacuum Cleaner | Akasa Teknik";
            $seo_data['description'] = "Isi form sewa vacuum cleaner online. Proses mudah, cepat, dan aman. Dapatkan vacuum cleaner terbaik untuk proyek Anda.";
            $seo_data['canonical'] = $base_url . 'pages/sewa.php';
            break;
            
        case 'kontak':
            $seo_data['title'] = "Hubungi Kami - Sewa Vacuum Cleaner | Akasa Teknik";
            $seo_data['description'] = "Konsultasi gratis dan pemesanan sewa vacuum cleaner. Hubungi kami sekarang untuk solusi cleaning profesional Anda.";
            $seo_data['canonical'] = $base_url . 'pages/kontak.php';
            break;
            
        case 'admin':
            $seo_data['title'] = "Admin Dashboard | Akasa Teknik";
            $seo_data['description'] = "Dashboard admin untuk mengelola persewaan vacuum cleaner.";
            $seo_data['canonical'] = $base_url . 'admin/';
            $seo_data['robots'] = 'noindex, nofollow';
            break;
    }
    
    // Override with custom data if provided
    if (!empty($data)) {
        $seo_data = array_merge($seo_data, $data);
    }
    
    return $seo_data;
}

/**
 * Generate structured data (JSON-LD) for SEO
 * @param string $type Type of structured data
 * @param array $data Data for structured data
 * @return string JSON-LD script
 */
function generate_structured_data($type, $data = []) {
    $base_data = [
        '@context' => 'https://schema.org',
        '@type' => $type,
        'url' => BASE_URL,
        'name' => 'Akasa Teknik',
        'description' => 'Jasa sewa mesin vacuum cleaner profesional di Sidoarjo',
        'address' => [
            '@type' => 'PostalAddress',
            'addressLocality' => 'Sidoarjo',
            'addressCountry' => 'ID'
        ],
        'telephone' => '+62-812-3456-7890'
    ];
    
    switch($type) {
        case 'LocalBusiness':
            $base_data['@type'] = 'LocalBusiness';
            $base_data['openingHours'] = 'Mo-Su 00:00-23:59';
            $base_data['priceRange'] = '$$';
            break;
            
        case 'Product':
            $base_data['@type'] = 'Product';
            if (isset($data['product_name'])) {
                $base_data['name'] = $data['product_name'];
            }
            if (isset($data['product_price'])) {
                $base_data['offers'] = [
                    '@type' => 'Offer',
                    'price' => $data['product_price'],
                    'priceCurrency' => 'IDR',
                    'availability' => 'https://schema.org/InStock'
                ];
            }
            break;
    }
    
    $structured_data = array_merge($base_data, $data);
    
    return '<script type="application/ld+json">' . json_encode($structured_data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>';
}

/**
 * Generate breadcrumb structured data
 * @param array $breadcrumbs Breadcrumb items
 * @return string JSON-LD script
 */
function generate_breadcrumb_data($breadcrumbs) {
    $breadcrumb_list = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => []
    ];
    
    foreach ($breadcrumbs as $index => $breadcrumb) {
        $breadcrumb_list['itemListElement'][] = [
            '@type' => 'ListItem',
            'position' => $index + 1,
            'name' => $breadcrumb['name'],
            'item' => $breadcrumb['url']
        ];
    }
    
    return '<script type="application/ld+json">' . json_encode($breadcrumb_list, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>';
}

/**
 * Output meta tags HTML
 * @param array $seo_data SEO data array
 */
function output_meta_tags($seo_data) {
    echo '<title>' . htmlspecialchars($seo_data['title']) . '</title>' . "\n";
    echo '<meta name="description" content="' . htmlspecialchars($seo_data['description']) . '">' . "\n";
    echo '<meta name="keywords" content="' . htmlspecialchars($seo_data['keywords']) . '">' . "\n";
    
    if (isset($seo_data['robots'])) {
        echo '<meta name="robots" content="' . htmlspecialchars($seo_data['robots']) . '">' . "\n";
    }
    
    echo '<link rel="canonical" href="' . htmlspecialchars($seo_data['canonical']) . '">' . "\n";
    
    // Open Graph
    echo '<meta property="og:title" content="' . htmlspecialchars($seo_data['og_title']) . '">' . "\n";
    echo '<meta property="og:description" content="' . htmlspecialchars($seo_data['og_description']) . '">' . "\n";
    echo '<meta property="og:image" content="' . htmlspecialchars($seo_data['og_image']) . '">' . "\n";
    echo '<meta property="og:url" content="' . htmlspecialchars($seo_data['og_url']) . '">' . "\n";
    echo '<meta property="og:type" content="' . htmlspecialchars($seo_data['og_type']) . '">' . "\n";
    echo '<meta property="og:site_name" content="Akasa Teknik">' . "\n";
    
    // Twitter Card
    echo '<meta name="twitter:card" content="' . htmlspecialchars($seo_data['twitter_card']) . '">' . "\n";
    echo '<meta name="twitter:title" content="' . htmlspecialchars($seo_data['twitter_title']) . '">' . "\n";
    echo '<meta name="twitter:description" content="' . htmlspecialchars($seo_data['twitter_description']) . '">' . "\n";
    echo '<meta name="twitter:image" content="' . htmlspecialchars($seo_data['twitter_image']) . '">' . "\n";
    
    // Additional meta tags
    echo '<meta name="author" content="Akasa Teknik">' . "\n";
    echo '<meta name="language" content="id">' . "\n";
    echo '<meta name="geo.region" content="ID-JI">' . "\n";
    echo '<meta name="geo.placename" content="Sidoarjo">' . "\n";
}
?>
