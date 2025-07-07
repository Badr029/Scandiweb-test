# Scandiweb Fullstack Test - Backend (OOP Architecture)

## Overview

This backend implements a fully object-oriented PHP GraphQL API for an e-commerce application, demonstrating **meaningful use of inheritance, polymorphism, and clear delegation of responsibilities**. The architecture follows PSR standards and avoids procedural code outside of application bootstrap.

## OOP Architecture & Polymorphism

### Abstract Classes with Concrete Implementations

#### 1. Category Model Hierarchy
```php
abstract class Category
├── AllCategory extends Category        // Special "all products" category
└── ProductCategory extends Category    // Regular product categories
```

**Polymorphic behavior:**
- `getType()`: Returns category-specific type
- `validate()`: Type-specific validation rules
- `getDisplayName()`: Custom display formatting
- `canContainProducts()`: Type-specific product containment logic

#### 2. Product Model Hierarchy
```php
abstract class Product
├── SimpleProduct extends Product       // Products without configurable options
└── ConfigurableProduct extends Product // Products with selectable attributes
```

**Polymorphic behavior:**
- `getType()`: Returns 'simple' or 'configurable'
- `validate()`: Different validation for simple vs configurable products
- `processForDisplay()`: Type-specific display processing
- `hasConfigurableOptions()`: Returns false for simple, true for configurable
- `getAvailableOptions()`: Returns empty array for simple, options array for configurable

#### 3. Attribute Model Hierarchy
```php
abstract class Attribute
├── TextAttribute extends Attribute     // Text-based attributes (Size, Capacity)
└── SwatchAttribute extends Attribute   // Color/visual attributes with hex values
```

**Polymorphic behavior:**
- `processValue()`: Text trimming vs hex color validation
- `formatDisplayValue()`: Simple trim vs color name formatting
- `supportsValue()`: Text validation vs hex color validation
- `renderForUI()`: Different UI rendering (buttons vs color swatches)
- `getInputType()`: Returns 'select' vs 'color_swatch'

### Factory Pattern Implementation

All abstract classes use factory methods for object creation:

```php
// Categories
$category = Category::create('clothes'); // Returns ProductCategory instance

// Products  
$product = Product::create($productData); // Returns Simple or Configurable based on attributes

// Attributes
$attribute = Attribute::create('Color', 'swatch'); // Returns SwatchAttribute instance
```

### No Switch/If Statements for Type Differences

Type-specific behavior is handled entirely through polymorphism:

```php
// ❌ Old approach (switches/ifs):
if ($product->getType() === 'configurable') {
    // handle configurable logic
} else {
    // handle simple logic
}

// ✅ New approach (polymorphism):
$options = $product->getAvailableOptions(); // Method handles type differences internally
$canPurchase = $product->hasConfigurableOptions(); // Type-specific behavior
```

## PSR Compliance

- **PSR-1**: Basic coding standard ✓
- **PSR-12**: Extended coding style guide ✓  
- **PSR-4**: Autoloading standard with `App\` namespace ✓
- **Strict types**: All files use `declare(strict_types=1)` ✓

## Directory Structure

```
Backend/src/
├── config/
│   └── Database.php              # Singleton pattern database manager
├── models/
│   ├── Category.php              # Abstract Category + AllCategory + ProductCategory
│   ├── Product.php               # Abstract Product + SimpleProduct + ConfigurableProduct  
│   ├── Attribute.php             # Abstract Attribute + TextAttribute + SwatchAttribute
│   ├── AttributeItem.php         # Attribute value items
│   ├── Price.php                 # Product pricing with currency handling
│   └── Gallery.php               # Product image gallery management
└── scripts/
    └── import.php                # OOP data importer with polymorphism
```

## Database Schema

### Core Tables
- `categories` - Product categories
- `products` - Product catalog with brand, description, stock status
- `attributes` - Product attributes (Size, Color, Capacity, etc.)
- `attribute_items` - Individual attribute values
- `product_attributes` - Links products to their attributes
- `product_gallery` - Product images with sorting
- `prices` - Product pricing with currency support

## Key OOP Features Demonstrated

### 1. Inheritance
```php
// All models extend from appropriate abstract base classes
class ConfigurableProduct extends Product
class SwatchAttribute extends Attribute
class ProductCategory extends Category
```

### 2. Polymorphism
```php
// Same interface, different implementations
$product->processForDisplay();  // Different for Simple vs Configurable
$attribute->renderForUI();      // Different for Text vs Swatch
$category->getDisplayName();    // Different for All vs Product categories
```

### 3. Encapsulation
```php
// Protected properties, public methods with clear interfaces
protected string $id;
protected array $attributes;
public function getAvailableOptions(): array
```

### 4. Delegation of Responsibilities
- **Database**: Connection management, transactions, query execution
- **Models**: Data validation, business logic, persistence
- **Import**: Data transformation, bulk operations, error handling
- **Abstract Classes**: Define contracts, common functionality
- **Concrete Classes**: Type-specific implementations

### 5. Design Patterns
- **Singleton**: Database connection management
- **Factory**: Object creation based on type
- **Template Method**: Abstract classes define structure, subclasses implement details

## Data Import with Polymorphism

The import script demonstrates polymorphism in action:

```php
// Factory creates appropriate types automatically
$category = Category::create($categoryData['name']);
$product = Product::create($productData);
$attribute = Attribute::create($id, $name, $type);

// Each type handles its own validation and saving logic
if ($category->save()) {
    // Category-specific logic handled polymorphically
}
```

## Setup Instructions

1. **Database Setup:**
   ```bash
   mysql -u root -p < database_schema.sql
   ```

2. **Install Dependencies:**
   ```bash
   composer install
   ```

3. **Import Data:**
   ```bash
   php src/scripts/import.php
   ```

4. **Verify Import:**
   The importer will show type-specific information:
   ```
   ✓ Category 'all' (all)
   ✓ Category 'clothes' (product)
   ✓ Product 'Air Force 1 '07 QS' (configurable)
   ✓ Product 'AirPods Pro' (simple)
   ```

## Object-Oriented Benefits

1. **Type Safety**: Strict typing prevents runtime errors
2. **Extensibility**: Easy to add new product/attribute/category types
3. **Maintainability**: Changes to one type don't affect others
4. **Testability**: Each class has single responsibility
5. **Code Reuse**: Common functionality in abstract base classes
6. **Clear Contracts**: Abstract methods enforce implementation requirements

## Next Steps

With the OOP foundation complete, you can now:

1. **Implement GraphQL Resolvers**: Use the polymorphic models in GraphQL schema
2. **Add Business Logic**: Extend concrete classes with specific behaviors  
3. **Create Services**: Build service layer on top of models
4. **Add Validation**: Extend model validation methods
5. **Implement Caching**: Add caching layer respecting model interfaces

The architecture is designed to be easily extensible while maintaining clear separation of concerns and demonstrating advanced OOP principles throughout.

## Verification

To verify the setup was successful, check that all tables have data:

```sql
USE scandiweb_test;
SHOW TABLES;
SELECT COUNT(*) FROM products;
SELECT COUNT(*) FROM categories;
SELECT COUNT(*) FROM attributes;
```

Expected counts:
- Categories: 3
- Products: 8  
- Attributes: 5
- Attribute items: 19
- Product gallery: 33
- Product attributes: 15
- Prices: 8

## Error Handling

If you encounter database connection issues:

1. Check MySQL is running
2. Verify database credentials in `src/Config/Database.php`
3. Ensure the MySQL user has CREATE DATABASE permissions
4. Check that the specified port is correct

## Database Schema

The database schema is designed to match the data.json structure exactly:

### Tables Created:
- **categories** - Product categories (clothes, tech, all)
- **products** - Main product information
- **product_gallery** - Product images with sort order  
- **attributes** - Product attributes (Size, Color, Capacity, etc.)
- **attribute_items** - Individual attribute values
- **product_attributes** - Links products to their available attributes
- **prices** - Product pricing with currency information

## Model Structure

### Created Models:
- `Backend/src/config/Database.php` - Database configuration
- `Backend/src/models/Category.php` - Category model
- `Backend/src/models/Product.php` - Product model  
- `Backend/src/models/Attribute.php` - Attribute model
- `Backend/src/models/AttributeItem.php` - Attribute item model
- `Backend/src/models/Price.php` - Price model
- `Backend/src/models/Gallery.php` - Gallery model
- `Backend/src/scripts/import.php` - Import script

## Data.json Structure Analysis

The JSON contains:
- **3 categories**: all, clothes, tech
- **8 products**: Nike shoes, Canada Goose jacket, PlayStation 5, Xbox Series S, iMac 2021, iPhone 12 Pro, AirPods Pro, AirTag
- **Attributes**: Size (text), Color (swatch), Capacity (text), USB ports (text), Touch ID (text)
- **Gallery**: Multiple images per product
- **Prices**: USD currency with amounts

## Setup Instructions

1. **Create Database:**
   ```bash
   mysql -u root -p < database_schema.sql
   ```

2. **Install Dependencies:**
   ```bash
   composer install
   ```

3. **Run Import Script:**
   ```bash
   php src/scripts/import.php
   ```

## Next Steps

The models are ready for you to implement:
- Add methods to each model class
- Implement database operations (CRUD)
- Add validation logic
- Implement the import functionality
- Create GraphQL resolvers 