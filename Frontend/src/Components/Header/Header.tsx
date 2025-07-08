import React from 'react'
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

  const categories: Category[] = categoriesData?.categories || []
  const totalItems = getTotalItems()

  const isActiveCategory = (categoryName: string) => {
    if (categoryName === 'all' && location.pathname === '/') {
      return true
    }
    return location.pathname === `/category/${categoryName}`
  }

  return (
    <>
      <header className="header">
        <nav className="navigation">
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

        <div className="logo">
          <Link to="/">
            <img src="/logo.svg" alt="Scandiweb" className="logo-image" />
          </Link>
        </div>

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
      </header>

      {isCartOpen && <CartOverlay />}
    </>
  )
}

export default Header 