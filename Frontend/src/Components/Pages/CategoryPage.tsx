import React from 'react'
import { useParams } from 'react-router-dom'
import { useQuery } from '@apollo/client'
import { GET_PRODUCTS } from '../../graphql/queries'
import { Product } from '../../types'
import ProductCard from '../Product/ProductCard'
import './CategoryPage.css'

const CategoryPage: React.FC = () => {
  const { categoryName } = useParams<{ categoryName?: string }>()
  const { data, loading, error } = useQuery(GET_PRODUCTS)

  if (error) return <div className="error">Error loading products: {error.message}</div>

  const products: Product[] = data?.products || []
  const filteredProducts = categoryName && categoryName !== 'all' 
    ? products.filter(product => product.category === categoryName)
    : products

  const categoryTitle = categoryName ? categoryName.charAt(0).toUpperCase() + categoryName.slice(1) : 'All'

  return (
    <div className="category-page">
      <h1 className="category-title">{categoryTitle}</h1>
      
      <div className="products-grid">
        {filteredProducts.map((product) => (
          <ProductCard 
            key={product.id} 
            product={product} 
          />
        ))}
        {loading && (
          <div className="loading-placeholder">Loading products...</div>
        )}
      </div>

      {!loading && filteredProducts.length === 0 && (
        <div className="no-products">
          No products found in this category.
        </div>
      )}
    </div>
  )
}

export default CategoryPage 