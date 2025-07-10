import React from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { Product } from '../../types'
import { useCart } from '../../context/CartContext'
import './ProductCard.css'

interface ProductCardProps {
  product: Product
}

// Helper function to convert to kebab case
const toKebabCase = (str: string) => {
  return str.toLowerCase().replace(/[^a-z0-9]/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '')
}

const ProductCard: React.FC<ProductCardProps> = ({ product }) => {
  const primaryPrice = product.prices[0] // Assuming first price is primary
  const primaryImage = product.gallery[0] // Assuming first image is primary
  const { addToCart, openCart } = useCart()
  const navigate = useNavigate()

  const handleQuickAddToCart = (e: React.MouseEvent) => {
    e.preventDefault()
    e.stopPropagation()

    // If product has attributes, redirect to product page to select them
    if (product.attributes && product.attributes.length > 0) {
      navigate(`/product/${product.id}`)
      return
    }

    // For products without attributes, add directly to cart
    const defaultAttributes: { [attributeId: string]: string } = {}
    addToCart(product, defaultAttributes)
    openCart()
  }

  return (
    <div 
      className={`product-card ${!product.inStock ? 'out-of-stock' : ''}`}
      data-testid={`product-${toKebabCase(product.name)}`}
    >
      <Link to={`/product/${product.id}`} className="product-link">
        <div className="product-image-container">
          <img 
            src={primaryImage} 
            alt={product.name}
            className="product-image"
          />
          {!product.inStock && (
            <div className="out-of-stock-overlay">
              <span className="out-of-stock-text">OUT OF STOCK</span>
            </div>
          )}

        </div>

        <div className="product-info">
          <h3 className="product-name">{product.brand} {product.name}</h3>
          <div className="product-price">
            {primaryPrice.currency.symbol}{primaryPrice.amount.toFixed(2)}
          </div>
        </div>
      </Link>
      {product.inStock && (
            <button 
              className="quick-add-button"
              onClick={handleQuickAddToCart}
              title="Add to cart"
            >
              <img src="/cart-white.svg" alt="Add to cart" />
            </button>
          )}
    </div>
  )
}

export default ProductCard 