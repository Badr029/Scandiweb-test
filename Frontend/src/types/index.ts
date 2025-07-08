export interface Currency {
  label: string
  symbol: string
}

export interface Price {
  amount: number
  currency: Currency
}

export interface AttributeItem {
  id: string
  display_value: string
  value: string
}

export interface Attribute {
  id: string
  name: string
  type: 'text' | 'swatch'
  items: AttributeItem[]
}

export interface Product {
  id: string
  name: string
  brand: string
  description: string
  inStock: boolean
  gallery: string[]
  category: string
  prices: Price[]
  attributes: Attribute[]
}

export interface Category {
  name: string
}

export interface CartItem {
  product: Product
  quantity: number
  selectedAttributes: { [attributeId: string]: string }
}

export interface Order {
  id: string
  status: string
  total: number
  created_at: string
}

export interface OrderProductInput {
  product_id: string
  quantity: number
  selected_attributes: { [key: string]: string }
} 