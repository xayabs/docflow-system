import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

window.subscribeUserToPush = function() {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
        alert('Browser ຂອງທ່ານບໍ່ຮອງຮັບການແຈ້ງເຕືອນ!');
        return;
    }

    Notification.requestPermission().then(permission => {
        if (permission === 'granted') {
            navigator.serviceWorker.ready.then(registration => {
                
                // ✅ ແກ້ໄຂ: ດຶງຄ່າ VAPID Key ຈາກ Meta tag
                const vapidMeta = document.querySelector('meta[name="vapid-public-key"]');
                if (!vapidMeta) {
                    console.error('ບໍ່ພົບ VAPID Public Key Meta tag');
                    return;
                }
                const vapidPublicKey = vapidMeta.getAttribute('content');

                if(!vapidPublicKey) { console.error('VAPID Public Key ຫວ່າງເປົ່າ!'); return; }

                const convertedVapidKey = urlBase64ToUint8Array(vapidPublicKey);

                registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: convertedVapidKey
                })
                .then(subscription => {
                    
                    // ✅ ແກ້ໄຂ: ດຶງ URL ຈາກ Meta tag
                    const subscribeUrlMeta = document.querySelector('meta[name="push-subscribe-url"]');
                    if (!subscribeUrlMeta) {
                        console.error('ບໍ່ພົບ Push Subscribe URL Meta tag');
                        return;
                    }
                    const subscribeUrl = subscribeUrlMeta.getAttribute('content');

                    fetch(subscribeUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(subscription)
                    })
                    .then(res => res.json())
                    .then(data => {
                        alert('ເປີດການແຈ້ງເຕືອນສຳເລັດແລ້ວ!');
                    });
                })
                .catch(err => console.error('Failed to subscribe: ', err));
            });
        } else {
            alert('ທ່ານໄດ້ປະຕິເສດການອະນຸຍາດແຈ້ງເຕືອນ');
        }
    });
};

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}