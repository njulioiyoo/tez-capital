FROM node:18-alpine

# Install dependencies
RUN apk update && apk add --no-cache git

# Set working directory
WORKDIR /var/www/html

# Copy package files first for better caching
COPY www/package*.json ./

# Install npm dependencies
RUN npm install

# Copy the rest of the application
COPY www/ .

# Expose Vite dev server port
EXPOSE 5173

# Start Vite development server
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0", "--port", "5173"]