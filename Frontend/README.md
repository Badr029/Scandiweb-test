# Scandiweb Test - Frontend

A modern React + TypeScript e-commerce application built with Vite for the Scandiweb developer test.

## Tech Stack

- **React 19** - Modern React with functional components
- **TypeScript** - Type safety and better developer experience
- **Vite** - Fast build tool and development server
- **Apollo Client** - GraphQL client for API communication
- **React Router** - Client-side routing for SPA
- **CSS3** - Custom styling without component libraries

## Features

- ✅ Single Page Application (SPA) with React Router
- ✅ TypeScript for type safety
- ✅ GraphQL integration with Apollo Client
- ✅ Responsive design
- ✅ Product catalog with categories
- ✅ Product detail pages
- ✅ Shopping cart functionality (placeholder)
- ✅ Modern CSS with custom styling
- ✅ Functional components with hooks

## Project Structure

```
src/
├── components/
│   ├── Layout/           # Layout components
│   ├── Header/           # Navigation header
│   ├── Product/          # Product-related components
│   ├── Cart/             # Shopping cart components
│   └── Pages/            # Page components
├── graphql/              # GraphQL queries and mutations
├── types/                # TypeScript type definitions
└── assets/               # Static assets
```

## Prerequisites

- Node.js 18+ 
- npm or yarn
- Backend server running on http://localhost:8000

## Installation

1. Install dependencies:
```bash
npm install
```

2. Start the development server:
```bash
npm run dev
```

3. Open http://localhost:3000 in your browser

## Available Scripts

- `npm run dev` - Start development server
- `npm run build` - Build for production
- `npm run preview` - Preview production build
- `npm run lint` - Run ESLint

## Backend Integration

This frontend communicates with the PHP GraphQL backend via Apollo Client. The backend should be running on `http://localhost:8000/graphql`.

### GraphQL Queries

- `GET_CATEGORIES` - Fetch all product categories
- `GET_PRODUCTS` - Fetch all products with attributes
- `GET_PRODUCT_BY_ID` - Fetch single product details
- `PLACE_ORDER` - Place a new order (mutation)

## Design Implementation

The application follows the design provided in the Figma file with:
- Clean, modern layout
- Product grid with hover effects
- Responsive navigation
- Shopping cart overlay
- Professional typography using Raleway, Roboto fonts

## Development Notes

- Uses functional components with React hooks
- No component libraries used (Material UI, Bootstrap, etc.)
- Custom CSS with modern layout techniques
- TypeScript interfaces for all data structures
- Apollo Client for efficient GraphQL data management
- Lazy loading and code splitting ready

## TODO

- [ ] Implement cart state management
- [ ] Add product attribute selection
- [ ] Implement order placement
- [ ] Add loading states and error handling
- [ ] Add unit tests
- [ ] Optimize images and assets

## Browser Support

- Chrome (latest)
- Firefox (latest)  
- Safari (latest)
- Edge (latest)
