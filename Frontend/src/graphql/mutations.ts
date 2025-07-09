import { gql } from '@apollo/client'

export const PLACE_ORDER = gql`
  mutation PlaceOrder($items: [String!]!, $totalAmount: Float!, $customerEmail: String) {
    placeOrder(items: $items, totalAmount: $totalAmount, customerEmail: $customerEmail) {
      id
      status
      totalAmount
      currency
      customerEmail
      createdAt
    }
  }
` 