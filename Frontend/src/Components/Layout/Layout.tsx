import React from 'react'
import Header from '../Header/Header'
import { useCart } from '../../context/CartContext'
import './Layout.css'

interface LayoutProps {
  children: React.ReactNode
}

const Layout: React.FC<LayoutProps> = ({ children }) => {
  const { isCartOpen } = useCart()

  return (
    <div className="layout">
      <Header />
      <main className={`main-content ${isCartOpen ? 'cart-overlay-open' : ''}`}>
        {children}
      </main>
    </div>
  )
}

export default Layout 