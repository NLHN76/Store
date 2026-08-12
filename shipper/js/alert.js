

var ShipperState = {
    alertAudio: null,
    alertInterval: null,
    lastOrderId: 0,
    pendingOrders: new Set()
};

$(function () {
    ShipperState.alertAudio = document.getElementById('newOrderSound');
    ShipperState.lastOrderId = parseInt($("#newOrderBanner").data("last-id") || 0);
});

/* ================= ÂM THANH CẢNH BÁO ================= */

function startAlert() {
    if (ShipperState.alertInterval) return;

    ShipperState.alertInterval = setInterval(function () {
        if (ShipperState.alertAudio) {
            ShipperState.alertAudio.play().catch(function () {});
        }
    }, 2000);
}

function stopAlert() {
    ShipperState.pendingOrders.clear();

    if (ShipperState.alertInterval) {
        clearInterval(ShipperState.alertInterval);
        ShipperState.alertInterval = null;
    }

    if (ShipperState.alertAudio) {
        ShipperState.alertAudio.pause();
        ShipperState.alertAudio.currentTime = 0;
    }
}
