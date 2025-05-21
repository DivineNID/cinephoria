// public/assets/js/notifications.js
class NotificationHandler {
    constructor(userId) {
        this.pusher = new Pusher(PUSHER_APP_KEY, {
            cluster: PUSHER_CLUSTER
        });
        
        this.userId = userId;
        this.initializeChannels();
    }
    
    initializeChannels() {
        const userChannel = this.pusher.subscribe(`user-${this.userId}`);
        userChannel.bind('booking-confirmed', this.handleBookingConfirmation);
        
        if (this.isEmployee()) {
            const roomChannels = ROOM_IDS.map(roomId => 
                this.pusher.subscribe(`room-${roomId}`));
            roomChannels.forEach(channel => 
                channel.bind('incident-reported', this.handleIncident));
        }
    }
    
    handleBookingConfirmation(data) {
        this.showNotification('Réservation confirmée', {
            body: `Votre réservation pour ${data.filmTitle} a été confirmée`,
            icon: '/assets/images/logo.png'
        });
    }
    
    handleIncident(data) {
        this.showNotification('Nouvel incident', {
            body: `Un incident a été signalé dans la salle ${data.roomName}`,
            icon: '/assets/images/warning.png'
        });
    }
    
    showNotification(title, options) {
        if (Notification.permission === 'granted') {
            new Notification(title, options);
        } else if (Notification.permission !== 'denied') {
            Notification.requestPermission().then(permission => {
                if (permission === 'granted') {
                    new Notification(title, options);
                }
            });
        }
    }
}