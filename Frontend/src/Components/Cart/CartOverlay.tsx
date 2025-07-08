import React, { useState } from 'react'
import { useMutation } from '@apollo/client'
import { useCart } from '../../context/CartContext'
import { PLACE_ORDER } from '../../graphql/mutations'
import { OrderProductInput } from '../../types'
import './CartOverlay.css'

// Helper function to convert to kebab case
const toKebabCase = (str: string) => {
  return str.toLowerCase().replace(/[^a-z0-9]/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '')
}

const CartOverlay: React.FC = () => {
  const { 
    cartItems, 
    closeCart, 
    getTotalItems, 
    getTotalAmount, 
    updateQuantity, 
    clearCart 
  } = useCart()
  
  const [placeOrderMutation, { loading: orderLoading }] = useMutation(PLACE_ORDER)
  const [orderPlaced, setOrderPlaced] = useState(false)

  const totalItems = getTotalItems()
  const totalAmount = getTotalAmount()
  const primaryCurrency = cartItems[0]?.product.prices[0]?.currency || { symbol: '$' }

  const handleQuantityChange = (productId: string, selectedAttributes: { [attributeId: string]: string }, change: number) => {
    const currentItem = cartItems.find(item => 
      item.product.id === productId && 
      JSON.stringify(item.selectedAttributes) === JSON.stringify(selectedAttributes)
    )
    
    if (currentItem) {
      const newQuantity = currentItem.quantity + change
      updateQuantity(productId, selectedAttributes, newQuantity)
    }
  }

  const handlePlaceOrder = async () => {
    if (cartItems.length === 0 || orderLoading) return

    try {
      const orderProducts: OrderProductInput[] = cartItems.map(item => ({
        product_id: item.product.id,
        quantity: item.quantity,
        selected_attributes: item.selectedAttributes
      }))

      await placeOrderMutation({
        variables: {
          products: orderProducts
        }
      })

      setOrderPlaced(true)
      clearCart()
      
      // Auto close after showing success message
      setTimeout(() => {
        setOrderPlaced(false)
        closeCart()
      }, 2000)
    } catch (error) {
      console.error('Error placing order:', error)
    }
  }

  const renderAttributeValue = (attributeType: string, value: string, displayValue: string) => {
    if (attributeType === 'swatch') {
      return (
        <div 
          className="cart-attribute-swatch"
          style={{ backgroundColor: value }}
          title={displayValue}
        />
      )
    }
    return <span className="cart-attribute-text">{displayValue}</span>
  }

  return (
    <>
      <div className="cart-overlay-backdrop" onClick={closeCart} />
      <div className="cart-overlay">
        <div className="cart-overlay-header">
          <h3 className="cart-overlay-title">
            <strong>My Bag</strong>, {totalItems === 1 ? '1 Item' : `${totalItems} Items`}
          </h3>
        </div>
        
        <div className="cart-overlay-content">
          {orderPlaced ? (
            <div className="order-success-message">
              <p>Order placed successfully!</p>
            </div>
          ) : cartItems.length === 0 ? (
            <div className="empty-cart-message">
              <p>Your cart is empty</p>
            </div>
          ) : (
            <div className="cart-items-list">
              {cartItems.map((item, index) => (
                <div key={`${item.product.id}-${index}`} className="cart-item">
                  <div className="cart-item-info">
                    <h4 className="cart-item-brand">{item.product.brand}</h4>
                    <h5 className="cart-item-name">{item.product.name}</h5>
                    <div className="cart-item-price">
                      {item.product.prices[0].currency.symbol}{item.product.prices[0].amount.toFixed(2)}
                    </div>
                    
                    {item.product.attributes.length > 0 && (
                      <div className="cart-item-attributes">
                        {item.product.attributes.map(attribute => {
                          const selectedValue = item.selectedAttributes[attribute.id]
                          const selectedItem = attribute.items.find(attrItem => attrItem.value === selectedValue)
                          const attributeKebab = toKebabCase(attribute.name)
                          
                          return (
                            <div 
                              key={attribute.id} 
                              className="cart-attribute-group"
                              data-testid={`cart-item-attribute-${attributeKebab}`}
                            >
                              <span className="cart-attribute-name">{attribute.name}:</span>
                              <div className="cart-attribute-options">
                                {attribute.items.map(attrItem => {
                                  const isSelected = attrItem.value === selectedValue
                                  return (
                                    <div 
                                      key={attrItem.id} 
                                      className={`cart-attribute-option ${isSelected ? 'selected' : ''}`}
                                      data-testid={isSelected 
                                        ? `cart-item-attribute-${attributeKebab}-${attributeKebab}-selected`
                                        : `cart-item-attribute-${attributeKebab}-${attributeKebab}`
                                      }
                                    >
                                      {renderAttributeValue(attribute.type, attrItem.value, attrItem.display_value)}
                                    </div>
                                  )
                                })}
                              </div>
                            </div>
                          )
                        })}
                      </div>
                    )}
                  </div>

                  <div className="cart-item-controls">
                    <div className="quantity-controls">
                      <button 
                        className="quantity-button"
                        data-testid="cart-item-amount-increase"
                        onClick={() => handleQuantityChange(item.product.id, item.selectedAttributes, 1)}
                      >
                        +
                      </button>
                      <span className="quantity-display" data-testid="cart-item-amount">{item.quantity}</span>
                      <button 
                        className="quantity-button"
                        data-testid="cart-item-amount-decrease"
                        onClick={() => handleQuantityChange(item.product.id, item.selectedAttributes, -1)}
                      >
                        -
                      </button>
                    </div>
                    
                    <div className="cart-item-image">
                      <img 
                        src={item.product.gallery[0]} 
                        alt={item.product.name}
                      />
                    </div>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
        
        <div className="cart-overlay-footer">
          <div className="cart-total">
            <span data-testid="cart-total">Total: {primaryCurrency.symbol}{totalAmount.toFixed(2)}</span>
          </div>
          <div className="cart-buttons">
            <button 
              className={`check-out-button ${cartItems.length === 0 || orderLoading ? 'disabled' : ''}`}
              disabled={cartItems.length === 0 || orderLoading}
              onClick={handlePlaceOrder}
            >
              {orderLoading ? 'PLACING ORDER...' : 'PLACE ORDER'}
            </button>
          </div>
        </div>
      </div>
    </>
  )
}

export default CartOverlay 