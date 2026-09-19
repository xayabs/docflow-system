const CACHE_NAME = 'fdtms-cache-v1';

// ເມື່ອຕິດຕັ້ງ Service Worker
self.addEventListener('install', event => {
    self.skipWaiting();
});

// ເມື່ອ Service Worker ເລີ່ມເຮັດວຽກ
self.addEventListener('activate', event => {
    event.waitUntil(clients.claim());
});

// ດັກຈັບການໂຫຼດຂໍ້ມູນ (ສຳລັບ PWA ພື້ນຖານ, ເຮົາປ່ອຍໃຫ້ໂຫຼດຜ່ານເນັດປົກກະຕິໄປເລີຍ)
self.addEventListener('fetch', event => {
    event.respondWith(fetch(event.request));
});