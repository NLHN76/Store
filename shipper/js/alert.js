const ShipperState = {
    alertAudio: null,
    alertInterval: null,
    lastOrderId: 0,
    pendingOrders: new Set()
};

document.addEventListener("DOMContentLoaded", function () {
    ShipperState.alertAudio = document.getElementById("newOrderSound");

    const banner = document.getElementById("newOrderBanner");

    ShipperState.lastOrderId = parseInt(
        banner?.dataset.lastId || 0
    );
});


/* ================= ÂM THANH CẢNH BÁO ================= */

function startAlert() {
    if (ShipperState.alertInterval) return;

    ShipperState.alertInterval = setInterval(function () {
        if (ShipperState.alertAudio) {
            ShipperState.alertAudio
                .play()
                .catch(function () {});
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