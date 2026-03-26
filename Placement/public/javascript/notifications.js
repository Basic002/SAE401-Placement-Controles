function showNotification(message, type = 'error') {
    // If DOM is not ready, defer the notification
    if (!document.body) {
        document.addEventListener('DOMContentLoaded', () => showNotification(message, type));
        return;
    }

    // Basic toast notification implementation
    var toast = document.createElement("div");
    toast.className = "notification-toast " + type;
    toast.textContent = message;

    document.body.appendChild(toast);

    // Trigger reflow to ensure the transition runs
    setTimeout(function() {
        toast.classList.add("show");
    }, 10);

    // Remove the toast after 3 seconds
    setTimeout(function() {
        toast.classList.remove("show");
        setTimeout(function() {
            document.body.removeChild(toast);
        }, 300); // Wait for transition to finish
    }, 3000);
}

// Ensure window.alert defaults to error if called inadvertently
// window.alert = function(msg) { showNotification(msg, 'error'); };
