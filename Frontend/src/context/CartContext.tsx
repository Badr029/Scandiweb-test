import React, { createContext, useContext, useState, useEffect, ReactNode } from 'react'
import { Product, CartItem } from '../types'

interface CartContextType {
  cartItems: CartItem[]
  isCartOpen: boolean
  addToCart: (product: Product, selectedAttributes: { [attributeId: string]: string }) => void
  removeFromCart: (productId: string, selectedAttributes: { [attributeId: string]: string }) => void
  updateQuantity: (productId: string, selectedAttributes: { [attributeId: string]: string }, quantity: number) => void
  clearCart: () => void
  openCart: () => void
  closeCart: () => void
  getTotalItems: () => number
  getTotalAmount: () => number
}

const CartContext = createContext<CartContextType | undefined>(undefined)

export const useCart = () => {
  const context = useContext(CartContext)
  if (!context) {
    throw new Error('useCart must be used within a CartProvider')
  }
  return context
}

interface CartProviderProps {
  children: ReactNode
}

const CART_STORAGE_KEY = 'scandiweb-cart'

export const CartProvider: React.FC<CartProviderProps> = ({ children }) => {
  const [cartItems, setCartItems] = useState<CartItem[]>([])
  const [isCartOpen, setIsCartOpen] = useState(false)
  const [isLoaded, setIsLoaded] = useState(false)

  // Load cart from localStorage on mount
  useEffect(() => {
    try {
      const savedCart = localStorage.getItem(CART_STORAGE_KEY)
      if (savedCart && savedCart !== 'undefined' && savedCart !== 'null') {
        const parsedCart = JSON.parse(savedCart)
        if (Array.isArray(parsedCart)) {
          console.log('Cart loaded from localStorage:', parsedCart.length, 'items')
          setCartItems(parsedCart)
        } else {
          console.warn('Invalid cart data in localStorage, starting with empty cart')
          setCartItems([])
        }
      } else {
        console.log('No cart data in localStorage, starting with empty cart')
        setCartItems([])
      }
    } catch (error) {
      console.error('Error loading cart from localStorage:', error)
      setCartItems([])
    } finally {
      setIsLoaded(true)
    }
  }, [])

  // Save cart to localStorage whenever it changes (but only after initial load)
  useEffect(() => {
    if (isLoaded) {
      try {
        console.log('Saving cart to localStorage:', cartItems.length, 'items')
        localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(cartItems))
      } catch (error) {
        console.error('Error saving cart to localStorage:', error)
      }
    }
  }, [cartItems, isLoaded])

  const addToCart = (product: Product, selectedAttributes: { [attributeId: string]: string }) => {
    setCartItems(prevItems => {
      // Check if same product with same attributes already exists
      const existingItemIndex = prevItems.findIndex(item => 
        item.product.id === product.id && 
        JSON.stringify(item.selectedAttributes) === JSON.stringify(selectedAttributes)
      )

      if (existingItemIndex > -1) {
        // Update quantity of existing item
        const updatedItems = [...prevItems]
        updatedItems[existingItemIndex] = {
          ...updatedItems[existingItemIndex],
          quantity: updatedItems[existingItemIndex].quantity + 1
        }
        console.log('Updated existing item in cart:', product.name)
        return updatedItems
      } else {
        // Add new item
        console.log('Added new item to cart:', product.name)
        return [...prevItems, {
          product,
          quantity: 1,
          selectedAttributes
        }]
      }
    })
  }

  const removeFromCart = (productId: string, selectedAttributes: { [attributeId: string]: string }) => {
    setCartItems(prevItems => {
      const filtered = prevItems.filter(item => 
        !(item.product.id === productId && 
          JSON.stringify(item.selectedAttributes) === JSON.stringify(selectedAttributes))
      )
      console.log('Removed item from cart:', productId)
      return filtered
    })
  }

  const updateQuantity = (productId: string, selectedAttributes: { [attributeId: string]: string }, quantity: number) => {
    if (quantity <= 0) {
      removeFromCart(productId, selectedAttributes)
      return
    }

    setCartItems(prevItems => {
      const updated = prevItems.map(item => 
        item.product.id === productId && 
        JSON.stringify(item.selectedAttributes) === JSON.stringify(selectedAttributes)
          ? { ...item, quantity }
          : item
      )
      console.log('Updated quantity for:', productId, 'to:', quantity)
      return updated
    })
  }

  const clearCart = () => {
    console.log('Clearing cart')
    setCartItems([])
  }

  const openCart = () => {
    setIsCartOpen(true)
  }

  const closeCart = () => {
    setIsCartOpen(false)
  }

  const getTotalItems = () => {
    return cartItems.reduce((total, item) => total + item.quantity, 0)
  }

  const getTotalAmount = () => {
    return cartItems.reduce((total, item) => {
      const price = item.product.prices[0]?.amount || 0
      return total + (price * item.quantity)
    }, 0)
  }

  const value: CartContextType = {
    cartItems,
    isCartOpen,
    addToCart,
    removeFromCart,
    updateQuantity,
    clearCart,
    openCart,
    closeCart,
    getTotalItems,
    getTotalAmount
  }

  return (
    <CartContext.Provider value={value}>
      {children}
    </CartContext.Provider>
  )
} 