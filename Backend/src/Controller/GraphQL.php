<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repositories\ProductRepository;
use App\Repositories\CategoryRepository;
use App\Models\Order;
use GraphQL\GraphQL as GraphQLBase;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Schema;
use GraphQL\Type\SchemaConfig;
use RuntimeException;
use Throwable;

/**
 * GraphQL Controller
 * 
 * Handles GraphQL requests and provides schema definition for the e-commerce API.
 * Implements proper separation of concerns by delegating data operations to repositories.
 * 
 * @package App\Controller
 */
class GraphQL 
{
    private static ProductRepository $productRepository;
    private static CategoryRepository $categoryRepository;

    /**
     * Handle incoming GraphQL requests and return JSON response
     * 
     * @return string JSON formatted response
     */
    public static function handle(): string
    {
        try {
            // Initialize repositories
            self::$productRepository = new ProductRepository();
            self::$categoryRepository = new CategoryRepository();

            // Define GraphQL types
            $categoryType = self::getCategoryType();
            $productType = self::getProductType();
            $attributeType = self::getAttributeType();
            $priceType = self::getPriceType();
            $orderType = self::getOrderType();

            // Define Query type with resolvers
            $queryType = new ObjectType([
                'name' => 'Query',
                'fields' => [
                    'categories' => [
                        'type' => Type::listOf($categoryType),
                        'description' => 'Get all categories',
                        'resolve' => function() {
                            return self::$categoryRepository->findAll();
                        }
                    ],
                    'category' => [
                        'type' => $categoryType,
                        'description' => 'Get category by name',
                        'args' => [
                            'name' => ['type' => Type::nonNull(Type::string())]
                        ],
                        'resolve' => function($root, $args) {
                            return self::$categoryRepository->findByName($args['name']);
                        }
                    ],
                    'products' => [
                        'type' => Type::listOf($productType),
                        'description' => 'Get products with optional filtering',
                        'args' => [
                            'category' => ['type' => Type::string()],
                            'inStock' => ['type' => Type::boolean()],
                            'search' => ['type' => Type::string()]
                        ],
                        'resolve' => function($root, $args) {
                            if (isset($args['search'])) {
                                return self::$productRepository->searchByText($args['search']);
                            }
                            
                            if (isset($args['category'])) {
                                return self::$productRepository->findByCategory($args['category']);
                            }
                            
                            if (isset($args['inStock']) && $args['inStock']) {
                                return self::$productRepository->findInStock();
                            }
                            
                            return self::$productRepository->findAll();
                        }
                    ],
                    'product' => [
                        'type' => $productType,
                        'description' => 'Get single product by ID',
                        'args' => [
                            'id' => ['type' => Type::nonNull(Type::string())]
                        ],
                        'resolve' => function($root, $args) {
                            return self::$productRepository->findById($args['id']);
                        }
                    ]
                ],
            ]);
        
            // Define Mutation type
            $mutationType = new ObjectType([
                'name' => 'Mutation',
                'fields' => [
                    'placeOrder' => [
                        'type' => $orderType,
                        'description' => 'Place a new order',
                        'args' => [
                            'items' => ['type' => Type::nonNull(Type::listOf(Type::string()))],
                            'totalAmount' => ['type' => Type::nonNull(Type::float())],
                            'customerEmail' => ['type' => Type::string()]
                        ],
                        'resolve' => function($root, $args) {
                            $order = Order::create(
                                'pending',
                                $args['totalAmount'],
                                'USD',
                                $args['customerEmail'] ?? null
                            );
                            
                            $order->setItems($args['items']);
                            
                            if ($order->save()) {
                                return $order;
                            }
                            
                            throw new RuntimeException('Failed to place order');
                        }
                    ]
                ],
            ]);
        
            // Create schema
            $schema = new Schema(
                (new SchemaConfig())
                ->setQuery($queryType)
                ->setMutation($mutationType)
            );
        
            // Handle GraphQL request
            $rawInput = file_get_contents('php://input');
            if ($rawInput === false) {
                throw new RuntimeException('Failed to get php://input');
            }
        
            $input = json_decode($rawInput, true);
            $query = $input['query'];
            $variableValues = $input['variables'] ?? null;
        
            // Execute GraphQL query
            $result = GraphQLBase::executeQuery($schema, $query, null, null, $variableValues);
            $output = $result->toArray();

        } catch (Throwable $e) {
            $output = [
                'error' => [
                    'message' => $e->getMessage(),
                ],
            ];
        }

        header('Content-Type: application/json; charset=UTF-8');
        return json_encode($output);
    }

    /**
     * Define Category GraphQL type with fields and resolvers
     * 
     * @return ObjectType Category GraphQL type definition
     */
    private static function getCategoryType(): ObjectType
    {
        return new ObjectType([
            'name' => 'Category',
            'description' => 'Product category',
            'fields' => [
                'id' => ['type' => Type::id()],
                'name' => ['type' => Type::nonNull(Type::string())],
                'type' => ['type' => Type::nonNull(Type::string())],
                'displayName' => ['type' => Type::nonNull(Type::string())],
                'canContainProducts' => ['type' => Type::nonNull(Type::boolean())],
                'products' => [
                    'type' => Type::listOf(self::getProductType()),
                    'description' => 'Products in this category',
                    'resolve' => function($category) {
                        return self::$productRepository->findByCategory($category->getName());
                    }
                ]
            ]
        ]);
    }

    /**
     * Define Product GraphQL type with polymorphic fields and resolvers
     * 
     * @return ObjectType Product GraphQL type definition
     */
    private static function getProductType(): ObjectType
    {
        return new ObjectType([
            'name' => 'Product',
            'description' => 'Product with polymorphic behavior',
            'fields' => [
                'id' => ['type' => Type::nonNull(Type::string())],
                'name' => ['type' => Type::nonNull(Type::string())],
                'brand' => ['type' => Type::nonNull(Type::string())],
                'description' => ['type' => Type::string()],
                'category' => ['type' => Type::nonNull(Type::string())],
                'inStock' => ['type' => Type::nonNull(Type::boolean())],
                'type' => ['type' => Type::nonNull(Type::string())],
                'hasConfigurableOptions' => ['type' => Type::nonNull(Type::boolean())],
                'gallery' => ['type' => Type::listOf(Type::string())],
                'prices' => ['type' => Type::listOf(self::getPriceType())],
                'attributes' => ['type' => Type::listOf(self::getAttributeType())],
                'availableOptions' => [
                    'type' => Type::listOf(Type::string()),
                    'description' => 'Available options for configurable products',
                    'resolve' => function($product) {
                        return $product->getAvailableOptions();
                    }
                ]
            ]
        ]);
    }

    /**
     * Define Attribute GraphQL type with fields
     * 
     * @return ObjectType Attribute GraphQL type definition
     */
    private static function getAttributeType(): ObjectType
    {
        return new ObjectType([
            'name' => 'Attribute',
            'description' => 'Product attribute',
            'fields' => [
                'id' => ['type' => Type::nonNull(Type::string())],
                'name' => ['type' => Type::nonNull(Type::string())],
                'type' => ['type' => Type::nonNull(Type::string())],
                'items' => ['type' => Type::listOf(Type::string())]
            ]
        ]);
    }

    /**
     * Define Price GraphQL type with currency information
     * 
     * @return ObjectType Price GraphQL type definition
     */
    private static function getPriceType(): ObjectType
    {
        return new ObjectType([
            'name' => 'Price',
            'description' => 'Product price with currency',
            'fields' => [
                'amount' => ['type' => Type::nonNull(Type::float())],
                'currency' => [
                    'type' => new ObjectType([
                        'name' => 'Currency',
                        'fields' => [
                            'label' => ['type' => Type::nonNull(Type::string())],
                            'symbol' => ['type' => Type::nonNull(Type::string())]
                        ]
                    ])
                ]
            ]
        ]);
    }

    /**
     * Define Order GraphQL type with polymorphic behavior fields
     * 
     * @return ObjectType Order GraphQL type definition
     */
    private static function getOrderType(): ObjectType
    {
        return new ObjectType([
            'name' => 'Order',
            'description' => 'Customer order with polymorphic behavior',
            'fields' => [
                'id' => ['type' => Type::id()],
                'status' => ['type' => Type::nonNull(Type::string())],
                'totalAmount' => ['type' => Type::nonNull(Type::float())],
                'currency' => ['type' => Type::nonNull(Type::string())],
                'customerEmail' => ['type' => Type::string()],
                'canBeModified' => ['type' => Type::nonNull(Type::boolean())],
                'canBeCancelled' => ['type' => Type::nonNull(Type::boolean())],
                'availableActions' => ['type' => Type::listOf(Type::string())]
            ]
        ]);
    }
}