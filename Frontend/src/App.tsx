import React from 'react'
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom'
import { ApolloProvider } from '@apollo/client'
import { apolloClient } from './graphql/apolloClient'
import { CartProvider } from './context/CartContext'
import Layout from './Components/Layout/Layout'
import CategoryPage from './Components/Pages/CategoryPage'
import ProductPage from './Components/Pages/ProductPage'
import CartPage from './Components/Pages/CartPage'
import './App.css'

const App: React.FC = () => {
  return (
    <ApolloProvider client={apolloClient}>
      <CartProvider>
        <Router>
          <Layout>
            <Routes>
              <Route path="/" element={<CategoryPage />} />
              <Route path="/category/:categoryName" element={<CategoryPage />} />
              <Route path="/product/:productId" element={<ProductPage />} />
              <Route path="/cart" element={<CartPage />} />
            </Routes>
          </Layout>
        </Router>
      </CartProvider>
    </ApolloProvider>
  )
}

export default App 