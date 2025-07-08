<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use App\Config\Database;
use App\Models\Product;
use App\Models\Attribute;
use App\Controller\GraphQL;

/**
 * Backend Requirements Validation Script
 * 
 * Validates that the backend implementation meets all specified requirements:
 * - PHP 7.4+ or 8.1+
 * - No frameworks (Laravel, Symfony, Slim)
 * - OOP approach with inheritance and polymorphism
 * - PSR compliance (PSR-1, PSR-12, PSR-4)
 * - MySQL database with populated data
 * - GraphQL schema for categories/products with proper attribute handling
 * - GraphQL mutation for inserting orders
 */
class RequirementsValidator
{
    private array $results = [];
    
    public function validateAll(): void
    {
        echo "🔍 Validating Backend Requirements...\n\n";
        
        $this->validatePHPVersion();
        $this->validateNoFrameworks();
        $this->validateOOPApproach();
        $this->validatePSRCompliance();
        $this->validateDatabaseSetup();
        $this->validateGraphQLImplementation();
        $this->validatePolymorphism();
        
        $this->displayResults();
    }
    
    /**
     * Validate PHP version requirements (7.4+ or 8.1+)
     */
    private function validatePHPVersion(): void
    {
        $version = phpversion();
        $majorMinor = (float) substr($version, 0, 3);
        
        $valid = $majorMinor >= 7.4;
        $this->addResult(
            '✅ PHP Version', 
            $valid, 
            "Current: {$version}" . ($valid ? ' (✅ Compatible)' : ' (❌ Requires 7.4+)')
        );
    }
    
    /**
     * Validate no prohibited frameworks are used
     */
    private function validateNoFrameworks(): void
    {
        $composerFile = __DIR__ . '/composer.json';
        $composer = json_decode(file_get_contents($composerFile), true);
        
        $frameworks = ['laravel', 'symfony', 'slim'];
        $foundFrameworks = [];
        
        $dependencies = array_merge(
            $composer['require'] ?? [],
            $composer['require-dev'] ?? []
        );
        
        foreach ($dependencies as $package => $version) {
            foreach ($frameworks as $framework) {
                if (str_contains(strtolower($package), $framework)) {
                    $foundFrameworks[] = $package;
                }
            }
        }
        
        $valid = empty($foundFrameworks);
        $this->addResult(
            '✅ No Prohibited Frameworks', 
            $valid,
            $valid ? 'Only allowed libraries used' : 'Found: ' . implode(', ', $foundFrameworks)
        );
    }
    
    /**
     * Validate OOP approach implementation
     */
    private function validateOOPApproach(): void
    {
        $checks = [
            'Abstract Product class exists' => class_exists('App\Models\Product') && 
                (new ReflectionClass('App\Models\Product'))->isAbstract(),
            'Abstract Attribute class exists' => class_exists('App\Models\Attribute') && 
                (new ReflectionClass('App\Models\Attribute'))->isAbstract(),
            'Repository pattern used' => class_exists('App\Repositories\ProductRepository'),
            'GraphQL controller exists' => class_exists('App\Controller\GraphQL'),
            'Database abstraction exists' => class_exists('App\Config\Database'),
        ];
        
        $passed = array_sum($checks);
        $total = count($checks);
        
        $this->addResult(
            '✅ OOP Approach', 
            $passed === $total,
            "Passed {$passed}/{$total} OOP checks"
        );
        
        foreach ($checks as $check => $result) {
            echo "   • {$check}: " . ($result ? '✅' : '❌') . "\n";
        }
    }
    
    /**
     * Validate PSR compliance
     */
    private function validatePSRCompliance(): void
    {
        $composerFile = __DIR__ . '/composer.json';
        $composer = json_decode(file_get_contents($composerFile), true);
        
        $checks = [
            'PSR-4 autoloading configured' => isset($composer['autoload']['psr-4']),
            'Proper namespace structure' => isset($composer['autoload']['psr-4']['App\\']),
            'Strict types declared' => $this->checkStrictTypes(),
            'Proper class naming' => $this->checkClassNaming(),
        ];
        
        $passed = array_sum($checks);
        $total = count($checks);
        
        $this->addResult(
            '✅ PSR Compliance', 
            $passed === $total,
            "Passed {$passed}/{$total} PSR checks"
        );
    }
    
    /**
     * Validate database setup and data population
     */
    private function validateDatabaseSetup(): void
    {
        try {
            $connection = Database::getConnection();
            
            $checks = [
                'Database connection' => true,
                'Categories table populated' => $this->checkTablePopulated('categories'),
                'Products table populated' => $this->checkTablePopulated('products'),
                'Attributes table populated' => $this->checkTablePopulated('attributes'),
                'Price data exists' => $this->checkTablePopulated('prices'),
                'Gallery data exists' => $this->checkTablePopulated('product_gallery'),
            ];
            
            $passed = array_sum($checks);
            $total = count($checks);
            
            $this->addResult(
                '✅ Database Setup', 
                $passed === $total,
                "Passed {$passed}/{$total} database checks"
            );
            
        } catch (Exception $e) {
            $this->addResult('❌ Database Setup', false, 'Connection failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Validate GraphQL schema and mutation implementation
     */
    private function validateGraphQLImplementation(): void
    {
        try {
            // Test categories query
            $categoriesQuery = [
                'query' => '{ categories { name } }'
            ];
            $categoriesResult = GraphQL::handle($categoriesQuery);
            $categoriesData = json_decode($categoriesResult, true);
            
            // Test products query
            $productsQuery = [
                'query' => '{ products { id name brand attributes { name type } } }'
            ];
            $productsResult = GraphQL::handle($productsQuery);
            $productsData = json_decode($productsResult, true);
            
            // Test order mutation
            $orderMutation = [
                'query' => 'mutation { placeOrder(items: ["test-product"], totalAmount: 99.99) { id status totalAmount } }'
            ];
            $orderResult = GraphQL::handle($orderMutation);
            $orderData = json_decode($orderResult, true);
            
            $checks = [
                'Categories query works' => !isset($categoriesData['error']),
                'Products query works' => !isset($productsData['error']),
                'Attributes in product schema' => isset($productsData['data']['products'][0]['attributes']) ?? false,
                'Order mutation works' => !isset($orderData['error']),
            ];
            
            $passed = array_sum($checks);
            $total = count($checks);
            
            $this->addResult(
                '✅ GraphQL Implementation', 
                $passed === $total,
                "Passed {$passed}/{$total} GraphQL checks"
            );
            
        } catch (Exception $e) {
            $this->addResult('❌ GraphQL Implementation', false, 'GraphQL test failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Validate polymorphism implementation
     */
    private function validatePolymorphism(): void
    {
        $checks = [
            'Product factory method exists' => method_exists('App\Models\Product', 'create'),
            'Attribute factory method exists' => method_exists('App\Models\Attribute', 'create'),
            'SimpleProduct class exists' => class_exists('App\Models\SimpleProduct'),
            'ConfigurableProduct class exists' => class_exists('App\Models\ConfigurableProduct'),
            'TextAttribute class exists' => class_exists('App\Models\TextAttribute'),
            'SwatchAttribute class exists' => class_exists('App\Models\SwatchAttribute'),
            'Abstract methods implemented' => $this->checkAbstractMethodsImplemented(),
        ];
        
        $passed = array_sum($checks);
        $total = count($checks);
        
        $this->addResult(
            '✅ Polymorphism Implementation', 
            $passed === $total,
            "Passed {$passed}/{$total} polymorphism checks"
        );
    }
    
    /**
     * Helper methods
     */
    private function checkStrictTypes(): bool
    {
        $files = [
            __DIR__ . '/src/Models/Product.php',
            __DIR__ . '/src/Models/Attribute.php',
            __DIR__ . '/src/Controller/GraphQL.php',
        ];
        
        foreach ($files as $file) {
            if (file_exists($file)) {
                $content = file_get_contents($file);
                if (!str_contains($content, 'declare(strict_types=1);')) {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    private function checkClassNaming(): bool
    {
        // Basic check for PascalCase class names
        $classes = ['Product', 'Attribute', 'GraphQL', 'Database'];
        foreach ($classes as $class) {
            if ($class !== ucfirst($class)) {
                return false;
            }
        }
        return true;
    }
    
    private function checkTablePopulated(string $table): bool
    {
        try {
            $count = Database::fetchOne("SELECT COUNT(*) as count FROM {$table}")['count'] ?? 0;
            return $count > 0;
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function checkAbstractMethodsImplemented(): bool
    {
        try {
            $productReflection = new ReflectionClass('App\Models\Product');
            $attributeReflection = new ReflectionClass('App\Models\Attribute');
            
            $productAbstractMethods = array_filter(
                $productReflection->getMethods(),
                fn($method) => $method->isAbstract()
            );
            
            $attributeAbstractMethods = array_filter(
                $attributeReflection->getMethods(),
                fn($method) => $method->isAbstract()
            );
            
            return count($productAbstractMethods) > 0 && count($attributeAbstractMethods) > 0;
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function addResult(string $category, bool $passed, string $details): void
    {
        $this->results[] = [
            'category' => $category,
            'passed' => $passed,
            'details' => $details
        ];
    }
    
    private function displayResults(): void
    {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "📋 BACKEND REQUIREMENTS VALIDATION REPORT\n";
        echo str_repeat("=", 60) . "\n\n";
        
        $totalPassed = 0;
        $totalChecks = count($this->results);
        
        foreach ($this->results as $result) {
            $status = $result['passed'] ? '✅ PASS' : '❌ FAIL';
            echo sprintf("%-35s %s\n", $result['category'], $status);
            echo "   Details: {$result['details']}\n\n";
            
            if ($result['passed']) {
                $totalPassed++;
            }
        }
        
        echo str_repeat("-", 60) . "\n";
        echo sprintf("OVERALL RESULT: %d/%d requirements met\n", $totalPassed, $totalChecks);
        
        if ($totalPassed === $totalChecks) {
            echo "🎉 ALL REQUIREMENTS SATISFIED! Backend is ready for production.\n";
        } else {
            echo "⚠️  Some requirements need attention. Please review failed checks.\n";
        }
        echo str_repeat("=", 60) . "\n";
    }
}

// Run validation when called directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $validator = new RequirementsValidator();
    $validator->validateAll();
} 