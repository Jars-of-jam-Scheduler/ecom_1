function postToServerThePushSubscription(pushSubscription) {
    const token = document.querySelector('meta[name=csrf-token]').getAttribute('content');

    fetch('/webpush_notification_pushsubscription', {
        method: 'POST',
        body: JSON.stringify(pushSubscription),
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-Token': token
        }
    })
        .then((res) => {
            return res.json();
        })
        .then((res) => {
            console.log(res)
        })
        .catch((err) => {
            console.log(err)
        });
}

function urlBase64ToUint8Array(base64String) {
    var padding = '='.repeat((4 - base64String.length % 4) % 4);
    var base64 = (base64String + padding)
        .replace(/\-/g, '+')
        .replace(/_/g, '/');

    var rawData = window.atob(base64);
    var outputArray = new Uint8Array(rawData.length);

    for (var i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

function subscribeBrowser() {
    navigator.serviceWorker.ready
        .then((registration) => {
            const subscribeOptions = {
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(
                    'BAb39gJlbo7lX89oIIab9BIJRsD1n67RzC8fkh-nAIh7xDJo6_Zaz1Evf2p4ST5JzRj8-QGb94Y1sBOA2g-P3iU'
                )
            };

            return registration.pushManager.subscribe(subscribeOptions);
        })
        .then((pushSubscription) => {
            postToServerThePushSubscription(pushSubscription);
        });
}

function askForBrowserPermissionGranting() {
    if (!navigator.serviceWorker.ready) {
        return;
    }

    new Promise(function (resolve, reject) {
        const permissionResult = Notification.requestPermission(function (result) {
            resolve(result);
        });

        if (permissionResult) {
            permissionResult.then(resolve, reject);
        }
    })
        .then((permissionResult) => {
            if (permissionResult !== 'granted') {
                throw new Error('Permission refused.');
            }
            subscribeBrowser();
        });
}

function initServiceWorker() {
    if (!"serviceWorker" in navigator) {
        return;
    }

    if (!"PushManager" in window) {
        return;
    }

    navigator.serviceWorker.register('../webpush_service_worker.js')
        .then(() => {
            askForBrowserPermissionGranting();
        })
        .catch((err) => {
            console.log(err)
        });
}

initServiceWorker();