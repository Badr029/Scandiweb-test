import { ApolloClient, InMemoryCache, createHttpLink } from '@apollo/client'

// Use production API endpoint
const API_URL = import.meta.env.PROD
  ? 'https://scandiweb-test-mohamedbadr.web1337.net/api/public/index.php'
  : 'http://localhost:8000/graphql'

const httpLink = createHttpLink({
  uri: API_URL,
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