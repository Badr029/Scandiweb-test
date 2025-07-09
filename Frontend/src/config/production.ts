// Production Configuration for Scandiweb Test
export const PRODUCTION_CONFIG = {
  // Your InfinityFree Backend URL
  API_URL: 'https://scandiweb-test-mohamedbadr.web1337.net/graphql',
  
  // Vercel Frontend URL (actual deployed URL)
  FRONTEND_URL: 'https://scandiweb-test-badrs-projects-6643e546.vercel.app',
  
  // Environment
  NODE_ENV: 'production',
  
  // API Configuration
  API_TIMEOUT: 10000,
  ENABLE_LOGGING: false,
} as const

export default PRODUCTION_CONFIG 
