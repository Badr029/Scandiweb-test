// Production Configuration for Scandiweb Test
export const PRODUCTION_CONFIG = {
  // Your InfinityFree Backend URL
  API_URL: 'https://scandiweb-test-mohamedbadr.wuaze.com/api/',
  
  // Vercel Frontend URL (updated URL)
  FRONTEND_URL: 'https://scandiweb-test-gilt.vercel.app',
  
  // Environment
  NODE_ENV: 'production',
  
  // API Configuration
  API_TIMEOUT: 10000,
  ENABLE_LOGGING: false,
} as const

export default PRODUCTION_CONFIG 
