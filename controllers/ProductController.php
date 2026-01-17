<?php
require_once __DIR__ . '/BaseController.php';

/**
 * Product Controller
 */
class ProductController extends BaseController {
    private $productModel;
    
    public function __construct($database) {
        parent::__construct($database);
        $this->productModel = $this->model('ProductModel');
    }
    
    /**
     * Display all products
     */
    public function index() {
        $products = $this->productModel->getAvailableProducts();
        $this->view('products/index', [
            'products' => $products,
            'title' => 'Daftar Produk'
        ]);
    }
    
    /**
     * Display single product
     */
    public function show($id) {
        $product = $this->productModel->getProductWithStatus($id);
        if (!$product) {
            $this->redirect(BASE_URL . 'pages/produk.php');
        }
        
        $this->view('products/show', [
            'product' => $product,
            'title' => $product['nama_mesin']
        ]);
    }
    
    /**
     * Search products
     */
    public function search() {
        $keyword = $_GET['q'] ?? '';
        if (empty($keyword)) {
            $this->json(['error' => 'Keyword pencarian harus diisi'], 400);
        }
        
        $products = $this->productModel->searchProducts($keyword);
        $this->json(['products' => $products]);
    }
}
