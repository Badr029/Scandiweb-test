import { ApolloClient, InMemoryCache, createHttpLink } from '@apollo/client'

// Force the correct production API URL
const API_URL = 'https://scandiweb-test-mohamedbadr.web1337.net/api/public/index.php'

// Debug: Log the API URL being used
console.log('Apollo Client connecting to:', API_URL)

const httpLink = createHttpLink({
  uri: API_URL,
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