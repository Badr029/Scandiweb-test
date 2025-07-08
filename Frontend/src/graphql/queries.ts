import { gql } from '@apollo/client'

export const GET_CATEGORIES = gql`
  query GetCategories {
    categories {
      name
    }
  }
`

export const GET_PRODUCTS = gql`
  query GetProducts {
    products {
      id
      name
      brand
      description
      inStock
      gallery
      category
      prices {
        amount
        currency {
          label
          symbol
        }
      }
      attributes {
        id
        name
        type
        items {
          id
          display_value
          value
        }
      }
    }
  }
`

export const GET_PRODUCT_BY_ID = gql`
  query GetProductById($id: String!) {
    product(id: $id) {
      id
      name
      brand
      description
      inStock
      gallery
      category
      prices {
        amount
        currency {
          label
          symbol
        }
      }
      attributes {
        id
        name
        type
        items {
          id
          display_value
          value
        }
      }
    }
  }
` 