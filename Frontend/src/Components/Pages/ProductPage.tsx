import React, { useState, useEffect } from 'react'
import { useParams } from 'react-router-dom'
import { useQuery } from '@apollo/client'
import parse from 'html-react-parser'
import { GET_PRODUCT_BY_ID } from '../../graphql/queries'
import { useCart } from '../../context/CartContext'
import './ProductPage.css'

// Helper function to convert to kebab case
const toKebabCase = (str: string) => {
  return str.toLowerCase().replace(/[^a-z0-9]/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '')
}

const ProductPage: React.FC = () => {
  // ALL HOOKS MUST BE AT THE TOP - NO CONDITIONAL HOOK CALLS
  const { productId } = useParams<{ productId: string }>()
  const { data, loading, error } = useQuery(GET_PRODUCT_BY_ID, {
    variables: { id: productId },
    skip: !productId
  })
  
  const { addToCart, openCart } = useCart()
  const [selectedImage, setSelectedImage] = useState(0)
  const [selectedAttributes, setSelectedAttributes] = useState<{ [attributeId: string]: string }>({})

  // Initialize selected attributes with first option of each attribute
  // This useEffect is now ALWAYS called, but only executes when product data is available
  useEffect(() => {
    if (data?.product?.attributes && data.product.attributes.length > 0) {
      const defaultAttributes: { [attributeId: string]: string } = {}
      data.product.attributes.forEach((attribute: any) => {
        if (attribute.items && attribute.items.length > 0) {
          defaultAttributes[attribute.id] = attribute.items[0].value
        }
      })
      setSelectedAttributes(defaultAttributes)
    }
  }, [data?.product?.attributes])

  // Reset selected image when product changes
  useEffect(() => {
    setSelectedImage(0)
  }, [productId])

  // NOW handle loading/error states AFTER all hooks
  if (loading) return <div className="loading">Loading product...</div>
  if (error) return <div className="error">Error loading product: {error.message}</div>
  if (!data?.product) return <div className="error">Product not found</div>

  const product = data.product

  const handleAttributeSelect = (attributeId: string, value: string) => {
    setSelectedAttributes(prev => ({
      ...prev,
      [attributeId]: value
    }))
  }

  const handleAddToCart = () => {
    if (!product.inStock) return
    
    // Check if all required attributes are selected
    const requiredAttributes = product.attributes || []
    const missingAttributes = requiredAttributes.filter((attr: any) => !selectedAttributes[attr.id])
    
    if (missingAttributes.length > 0) {
      alert('Please select all product options')
      return
    }

    addToCart(product, selectedAttributes)
    openCart()
  }

  const canAddToCart = product.inStock && 
    (product.attributes?.length === 0 || 
     product.attributes?.every((attr: any) => selectedAttributes[attr.id]))

  const handleImageNavigation = (direction: 'prev' | 'next') => {
    if (direction === 'prev') {
      setSelectedImage(prev => prev === 0 ? product.gallery.length - 1 : prev - 1)
    } else {
      setSelectedImage(prev => prev === product.gallery.length - 1 ? 0 : prev + 1)
    }
  }

  const renderAttributeValue = (attribute: any, item: any, isSelected: boolean) => {
    if (attribute.type === 'swatch') {
      return (
        <div 
          className={`color-swatch ${isSelected ? 'selected' : ''}`}
          style={{ backgroundColor: item.value }}
          title={item.display_value}
        />
      )
    }
    return (
      <span className={`text-attribute ${isSelected ? 'selected' : ''}`}>
        {item.display_value}
      </span>
    )
  }

  return (
    <div className="product-page">
      <div className="product-container">
        <div className="product-gallery" data-testid="product-gallery">
          <div className="gallery-thumbnails">
            {product.gallery.map((image: string, index: number) => (
              <img 
                key={index}
                src={image} 
                alt={`${product.name} ${index + 1}`}
                className={`gallery-thumbnail ${selectedImage === index ? 'active' : ''}`}
                onClick={() => setSelectedImage(index)}
              />
            ))}
          </div>
          <div className="gallery-main">
            <div className="gallery-main-container">
              <img 
                src={product.gallery[selectedImage]} 
                alt={product.name}
                className="main-image"
              />
              {product.gallery.length > 1 && (
                <>
                  <button 
                    className="gallery-arrow gallery-arrow-left"
                    onClick={() => handleImageNavigation('prev')}
                    aria-label="Previous image"
                  >
                    <svg width="8" height="14" viewBox="0 0 8 14" fill="none">
                      <path d="M7 1L1 7L7 13" stroke="white" strokeWidth="2"/>
                    </svg>
                  </button>
                  <button 
                    className="gallery-arrow gallery-arrow-right"
                    onClick={() => handleImageNavigation('next')}
                    aria-label="Next image"
                  >
                    <svg width="8" height="14" viewBox="0 0 8 14" fill="none">
                      <path d="M1 1L7 7L1 13" stroke="white" strokeWidth="2"/>
                    </svg>
                  </button>
                </>
              )}
            </div>
          </div>
        </div>

        <div className="product-details">
          <h1 className="product-brand">{product.brand}</h1>
          <h2 className="product-name">{product.name}</h2>
          
          <div className="attributes-section">
            {product.attributes?.map((attribute: any) => (
              <div 
                key={attribute.id} 
                className="attribute-group"
                data-testid={`product-attribute-${toKebabCase(attribute.name)}`}
              >
                <h3 className="attribute-name">{attribute.name}:</h3>
                <div className="attribute-items">
                  {attribute.items.map((item: any) => {
                    const isSelected = selectedAttributes[attribute.id] === item.value
                    return (
                      <div 
                        key={item.id} 
                        className={`attribute-item ${isSelected ? 'selected' : ''}`}
                        onClick={() => handleAttributeSelect(attribute.id, item.value)}
                      >
                        {renderAttributeValue(attribute, item, isSelected)}
                      </div>
                    )
                  })}
                </div>
              </div>
            ))}
          </div>

          <div className="price-section">
            <span className="price-label">PRICE:</span>
            <span className="price-value">
              {product.prices[0].currency.symbol}{product.prices[0].amount.toFixed(2)}
            </span>
          </div>

          <button 
            className={`add-to-cart-button ${!canAddToCart ? 'disabled' : ''}`}
            disabled={!canAddToCart}
            onClick={handleAddToCart}
            data-testid="add-to-cart"
          >
            {product.inStock ? 'ADD TO CART' : 'OUT OF STOCK'}
          </button>

          <div 
            className="product-description"
            data-testid="product-description"
          >
            {parse(product.description)}
          </div>
        </div>
      </div>
    </div>
  )
}

export default ProductPage 