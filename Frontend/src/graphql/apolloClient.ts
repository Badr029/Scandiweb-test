import { ApolloClient, InMemoryCache, createHttpLink, from } from '@apollo/client'
import { onError } from '@apollo/client/link/error'
import { PRODUCTION_CONFIG } from '../config/production'

// Get API URL from environment variable or fallback to production config
const getApiUrl = (): string => {
  // Try environment variable first (for Vercel deployment)
  if (import.meta.env.VITE_API_URL) {
    return import.meta.env.VITE_API_URL
  }
  
  // Fallback to production config
  return PRODUCTION_CONFIG.API_URL
}

// Error handling link
const errorLink = onError(({ graphQLErrors, networkError, operation, forward }) => {
  if (graphQLErrors) {
    graphQLErrors.forEach(({ message, locations, path }) => {
      console.error(
        `GraphQL error: Message: ${message}, Location: ${locations}, Path: ${path}`
      )
    })
  }

  if (networkError) {
    console.error(`Network error: ${networkError}`)
    
    // Handle CORS errors specifically
    if (networkError.message.includes('CORS') || networkError.message.includes('fetch')) {
      console.error('CORS Error: Check backend CORS configuration')
      console.error('Backend URL:', getApiUrl())
    }
  }
})

// HTTP link with timeout
const httpLink = createHttpLink({
  uri: getApiUrl(),
  credentials: 'omit', // Changed from 'same-origin' for cross-origin requests
  headers: {
    'Content-Type': 'application/json',
  },
})

// Apollo Client with error handling
export const apolloClient = new ApolloClient({
  link: from([errorLink, httpLink]),
  cache: new InMemoryCache({
    typePolicies: {
      Product: {
        keyFields: ['id'],
      },
      Category: {
        keyFields: ['name'],
      },
    },
  }),
  defaultOptions: {
    watchQuery: {
      errorPolicy: 'all',
      fetchPolicy: 'cache-and-network',
    },
    query: {
      errorPolicy: 'all',
      fetchPolicy: 'cache-first',
    },
  },
})

// Log configuration in development
if (import.meta.env.DEV) {
  console.log('Apollo Client Configuration:')
  console.log('API URL:', getApiUrl())
  console.log('Environment:', import.meta.env.MODE)
} 