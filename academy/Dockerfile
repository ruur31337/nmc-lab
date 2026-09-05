FROM node:20-slim

WORKDIR /app

# Install deps
COPY package*.json ./
RUN npm ci --omit=dev

# Copy app
COPY . .

# Create data directory and seed inbox attachments
RUN mkdir -p /data/inbox
COPY seed-inbox/ /data/inbox/

EXPOSE 4000

ENV PORT=4000 \
    DB_PATH=/data/academy.db \
    DATA_DIR=/data \
    JWT_SECRET=nmc-academy-jwt-k3y-2026! \
    INTERNAL_SECRET=nmc-admission-s3cr3t-2026!

CMD ["node", "server.js"]
