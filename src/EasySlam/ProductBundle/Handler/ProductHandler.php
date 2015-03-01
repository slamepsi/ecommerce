<?php

namespace EasySlam\ProductBundle\Handler;

use Doctrine\ORM\EntityManager;
use EasySlam\ProductBundle\Entity\Product;

class ProductHandler
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    private $productRepository;

    /**
     * Initialise the EntityManager and the repository Product
     *
     * @param EntityManager $em
     * @internal param $ |Doctrine\ORM\EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->productRepository = $this->em->getRepository('EasySlamProductBundle:Product');
    }

    /**
     * Add a product to database
     *
     * @param string $name
     * @param string $description
     * @param int $price
     * @param int $stock
     */
    public function addProduct($name, $description, $price, $stock)
    {
        $product = new Product();
        $product->setName($name);
        $product->setDescription($description);
        $product->setPrice($price);
        $product->setStock($stock);

        $this->em->persist($product);
        $this->em->flush();
    }

    /**
     * Return all the
     *
     * @param array[string]
     * @return array[\EasySlam\ProductBundle\Entity\Product]
     */
    public function getAllProducts()
    {
        $products = $this->productRepository->findAll();

        return $products;
    }

    public function getAllProductsSearch($criterias = array())
    {
        $where = 'VC.name = "' . $criterias[0] . '"';

        for ($i = 1; $i < count($criterias); $i++) {
            $where .= ' OR VC.name = "' . $criterias[$i] . '"';
        }

        $db = $this->em->getConnection();
        $products = $db->prepare('SELECT P.* FROM VarianteColor VC
                      INNER JOIN variantecolor_product V_P ON VC.id = V_P.variantecolor_id
                      INNER JOIN Product P ON V_P.product_id = P.id
                      WHERE ' . $where . '
                      GROUP BY P.id
                      HAVING count(P.id) = ' . count($criterias));
        $products->execute();

        $products = $products->fetchAll();

        return $products;
    }
}