import { ApolloClient, InMemoryCache, createHttpLink } from '@apollo/client'

// Use environment variable for API URL, fallback to production InfinityFree URL
const apiUrl = import.meta.env.VITE_API_URL || 'https://scandiweb-test-MohamedBadr.wuaze.com/api/'

const httpLink = createHttpLink({
  uri: apiUrl,
  credentials: 'same-origin',
})

export const apolloClient = new ApolloClient({
  link: httpLink,
  cache: new InMemoryCache(),
  defaultOptions: {
    watchQuery: {
      errorPolicy: 'all',
    },
    query: {
      errorPolicy: 'all',
    },
  },
}) 