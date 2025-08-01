const express = require("express");
const http = require("http");
const { Server } = require("socket.io");

const app = express();
const server = http.createServer(app);

// Configuration de Socket.io avec support CORS
const io = new Server(server, {
    cors: {
        origin: "http://127.0.0.1:8000", // Adresse du frontend Symfony
        methods: ["GET", "POST"],
        credentials: true,
    },
});

// Classe pour logger les connexions et déconnexions
class ConnexionLogger {
    constructor() {
        this.connections = new Map();
    }

    logConnection(socketId, userId) {
        console.log(`Utilisateur ${userId} connecté (${socketId})`);
        this.connections.set(socketId, userId);
    }

    logDisconnection(socketId) {
        const userId = this.connections.get(socketId);
        console.log(`Utilisateur ${userId} déconnecté (${socketId})`);
        this.connections.delete(socketId);
    }
}

const logger = new ConnexionLogger();

// Gérer la connexion Socket.io
io.on("connection", (socket) => {
    const userId = socket.handshake.query.userId || "anon";
    logger.logConnection(socket.id, userId);

    // L'utilisateur rejoint un salon (room)
    socket.on("join_room", (room) => {
        socket.join(room);
        console.log(`Utilisateur ${userId} rejoint le salon ${room}`);
    });

    // Réception et diffusion du message
    socket.on("send_message", (data) => {
        console.log("Message reçu:", data);
        const room = data.room || "global";

        // Diffuse le message à tous les clients dans la salle
        io.to(room).emit("receive_message", {
            text: data.text,
            username: data.username || "Anonyme", // 🔥 On inclut le nom d'utilisateur
            authorId: data.authorId,
        });
    });

    // Déconnexion
    socket.on("disconnect", () => {
        logger.logDisconnection(socket.id);
    });
});

// Lancer le serveur
server.listen(3001, () => {
    console.log("Socket.io serveur démarré sur port 3001");
});
