import React, { useState } from 'react'
import { Link, useLocation } from 'react-router-dom'
import { useQuery } from '@apollo/client'
import { GET_CATEGORIES } from '../../graphql/queries'
import { Category } from '../../types'
import { useCart } from '../../context/CartContext'
import CartOverlay from '../Cart/CartOverlay'
import './Header.css'

const Header: React.FC = () => {
  const location = useLocation()
  const { data: categoriesData } = useQuery(GET_CATEGORIES)
  const { isCartOpen, openCart, closeCart, getTotalItems } = useCart()
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false)

  const categories: Category[] = categoriesData?.categories || []
  const totalItems = getTotalItems()

  const isActiveCategory = (categoryName: string) => {
    if (categoryName === 'all' && location.pathname === '/') {
      return true
    }
    return location.pathname === `/category/${categoryName}`
  }

  const toggleMobileMenu = () => {
    setIsMobileMenuOpen(!isMobileMenuOpen)
    // Close cart if open when opening mobile menu
    if (!isMobileMenuOpen && isCartOpen) {
      closeCart()
    }
  }

  const closeMobileMenu = () => {
    setIsMobileMenuOpen(false)
  }

  return (
    <>
      <header className="header">
        {/* Left Section: Hamburger Menu (Mobile) + Desktop Navigation */}
        <div className="header-left">
          {/* Hamburger Menu Button - Mobile Only */}
          <button 
            className="mobile-menu-button"
            onClick={toggleMobileMenu}
            aria-label="Toggle mobile menu"
          >
            <div className={`hamburger ${isMobileMenuOpen ? 'open' : ''}`}>
              <span></span>
              <span></span>
              <span></span>
            </div>
          </button>

          {/* Desktop Navigation */}
          <nav className="navigation desktop-nav">
            <ul className="nav-list">
              <li className={`nav-item ${isActiveCategory('all') ? 'active' : ''}`}>
                <Link 
                  to="/" 
                  className="nav-link"
                  data-testid={isActiveCategory('all') ? 'active-category-link' : 'category-link'}
                >
                  ALL
                </Link>
              </li>
              {categories.filter(category => category && category.name).map((category) => {
                if (category.name === 'all') return null
                const isActive = isActiveCategory(category.name)
                return (
                  <li 
                    key={category.name} 
                    className={`nav-item ${isActive ? 'active' : ''}`}
                  >
                    <Link 
                      to={`/category/${category.name}`} 
                      className="nav-link"
                      data-testid={isActive ? 'active-category-link' : 'category-link'}
                    >
                      {category.name.toUpperCase()}
                    </Link>
                  </li>
                )
              })}
            </ul>
          </nav>
        </div>

        {/* Center Section: Logo */}
        <div className="header-center">
          <div className="logo">
            <Link to="/">
              <img src="/logo.svg" alt="Scandiweb" className="logo-image" />
            </Link>
          </div>
        </div>

        {/* Right Section: Cart */}
        <div className="header-right">
          <div className="cart-section">
            <button 
              className="cart-button"
              data-testid="cart-btn"
              onClick={() => isCartOpen ? closeCart() : openCart()}
            >
              <img src="/cart-icon.svg" alt="Cart" className="cart-icon" />
              {totalItems > 0 && (
                <span className="cart-count">{totalItems}</span>
              )}
            </button>
          </div>
        </div>
      </header>

      {/* Mobile Menu Overlay */}
      {isMobileMenuOpen && (
        <div className="mobile-menu-overlay" onClick={closeMobileMenu}>
          <nav className="mobile-navigation" onClick={(e) => e.stopPropagation()}>
            <ul className="mobile-nav-list">
              <li className={`mobile-nav-item ${isActiveCategory('all') ? 'active' : ''}`}>
                <Link 
                  to="/" 
                  className="mobile-nav-link"
                  onClick={closeMobileMenu}
                  data-testid={isActiveCategory('all') ? 'active-category-link' : 'category-link'}
                >
                  ALL
                </Link>
              </li>
              {categories.filter(category => category && category.name).map((category) => {
                if (category.name === 'all') return null
                const isActive = isActiveCategory(category.name)
                return (
                  <li 
                    key={category.name} 
                    className={`mobile-nav-item ${isActive ? 'active' : ''}`}
                  >
                    <Link 
                      to={`/category/${category.name}`} 
                      className="mobile-nav-link"
                      onClick={closeMobileMenu}
                      data-testid={isActive ? 'active-category-link' : 'category-link'}
                    >
                      {category.name.toUpperCase()}
                    </Link>
                  </li>
                )
              })}
            </ul>
          </nav>
        </div>
      )}

      {isCartOpen && <CartOverlay />}
    </>
  )
}

export default Header 