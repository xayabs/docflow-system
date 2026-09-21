// ເມື່ອໄດ້ຮັບສັນຍານ Push Notification ຈາກ Server
self.addEventListener('push', function (event) {
    if (!(self.Notification && self.Notification.permission === 'granted')) {
        return;
    }

    let data = {};
    if (event.data) {
        data = event.data.json();
    }

    const title = data.title || 'ລະບົບຕິດຕາມເອກະສານການເງິນ';
    const options = {
        body: data.body || 'ທ່ານມີການແຈ້ງເຕືອນໃໝ່',
        icon: data.icon || '/images/icons/icon-192x192.png',
        badge: '/images/icons/icon-192x192.png',
        data: {
            url: data.url || '/'
        }
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

// ເມື່ອຜູ້ໃຊ້ກົດໃສ່ປ້າຍແຈ້ງເຕືອນເທິງໜ້າຈໍມືຖື
self.addEventListener('notificationclick', function (event) {
    event.notification.close();

    const targetUrl = event.notification.data.url;

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (clientList) {
            // ຖ້າເປີດແອັບຄ້າງໄວ້ຢູ່ແລ້ວ ໃຫ້ switch ໄປໜ້ານັ້ນ
            for (let i = 0; i < clientList.length; i++) {
                let client = clientList[i];
                if (client.url === targetUrl && 'focus' in client) {
                    return client.focus();
                }
            }
            // ຖ້າຍັງບໍ່ທັນເປີດ ໃຫ້ເປີດໜ້າຕ່າງໃໝ່ໄປຫາລິ້ງເອກະສານນັ້ນເລີຍ
            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});

