const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const Redis = require('ioredis');

const app = express();
const server = http.createServer(app);
const io = new Server(server, {
    cors: {
        origin: '*',
        methods: ['GET', 'POST'],
    },
});

const redis = new Redis({
    host: 'redis',
    port: 6379,
});

io.on('connection', (socket) => {
    console.log(`User connected: ${socket.id}`);

    socket.on('private-message', (data) => {
        const { recipientId, message, senderId } = data;

        io.to(recipientId).emit('new-message', { senderId, message });

        redis.publish('private-chat', JSON.stringify(data));
    });

    socket.on('disconnect', () => {
        console.log(`User disconnected: ${socket.id}`);
    });
});

redis.subscribe('private-chat', () => {
    console.log('Subscribed to Redis channel: private-chat');
});

redis.on('message', (channel, message) => {
    const parsedMessage = JSON.parse(message);
    io.to(parsedMessage.recipientId).emit('new-message', parsedMessage);
});

server.listen(6001, () => {
    console.log('WebSocket server is running on port 6001');
});
