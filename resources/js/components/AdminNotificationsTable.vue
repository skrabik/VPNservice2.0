<template>
    <div class="notification-container">
        <div class="notification" :class="notificationClass">
            <div class="notification-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 15c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm1-4h-2V7h2v6z"/>
                </svg>
            </div>
            <div class="notification-content">
                <div class="notification-title">
                    {{ notificationTitle }}
                </div>
                <div class="notification-message">
                    {{ notificationData.info }}
                </div>
                <div class="notification-time">
                    {{ formattedTime }}
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        notificationData: {
            type: Object,
            required: true,
            default: () => ({
                type: "notification",
                info: "Test notification",
                data: new Date().toISOString()
            })
        }
    },
    computed: {
        notificationTitle() {
            return this.notificationData.type.charAt(0).toUpperCase() +
                this.notificationData.type.slice(1);
        },
        formattedTime() {
            const date = new Date(this.notificationData.data);
            return date.toLocaleString();
        },
        notificationClass() {
            return `notification-${this.notificationData.type}`;
        }
    }
};
</script>

<style scoped>
.notification-container {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 0 auto;
}

.notification {
    display: flex;
    padding: 16px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    background-color: white;
    margin-bottom: 12px;
    transition: all 0.3s ease;
}

.notification:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.notification-icon {
    margin-right: 12px;
    color: #4a6cf7;
}

.notification-icon svg {
    width: 24px;
    height: 24px;
}

.notification-content {
    flex: 1;
}

.notification-title {
    font-weight: 600;
    font-size: 16px;
    margin-bottom: 4px;
    color: #2d3748;
}

.notification-message {
    font-size: 14px;
    color: #4a5568;
    margin-bottom: 6px;
}

.notification-time {
    font-size: 12px;
    color: #718096;
}

.notification-notification {
    border-left: 4px solid #4a6cf7;
}

.notification-warning {
    border-left: 4px solid #f6ad55;
}

.notification-error {
    border-left: 4px solid #f56565;
}

.notification-success {
    border-left: 4px solid #48bb78;
}
</style>
