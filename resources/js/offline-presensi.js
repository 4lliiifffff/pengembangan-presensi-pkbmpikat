/**
 * PWA Offline Storage & Auto-Sync Module for Smart Presensi PKBM Pikat
 */
const DB_NAME = 'PikatPresensiOfflineDB';
const DB_VERSION = 1;
const STORE_NAME = 'offline_presensis';

function openOfflineDatabase() {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open(DB_NAME, DB_VERSION);

    request.onupgradeneeded = (event) => {
      const db = event.target.result;
      if (!db.objectStoreNames.contains(STORE_NAME)) {
        db.createObjectStore(STORE_NAME, { keyPath: 'id', autoIncrement: true });
      }
    };

    request.onsuccess = (event) => resolve(event.target.result);
    request.onerror = (event) => reject(event.target.error);
  });
}

export async function savePresensiOffline(payload) {
  try {
    const db = await openOfflineDatabase();
    const tx = db.transaction(STORE_NAME, 'readwrite');
    const store = tx.objectStore(STORE_NAME);

    const record = {
      ...payload,
      saved_at: new Date().toISOString()
    };

    await store.add(record);
    showOfflineBanner('⚠️ Presensi tersimpan secara offline. Akan otomatis disinkronkan saat terhubung ke internet.');
    return true;
  } catch (err) {
    console.error('Gagal menyimpan presensi offline:', err);
    return false;
  }
}

export async function getOfflinePresensis() {
  try {
    const db = await openOfflineDatabase();
    const tx = db.transaction(STORE_NAME, 'readonly');
    const store = tx.objectStore(STORE_NAME);
    return new Promise((resolve) => {
      const request = store.getAll();
      request.onsuccess = () => resolve(request.result);
    });
  } catch (err) {
    return [];
  }
}

export async function clearOfflinePresensis() {
  try {
    const db = await openOfflineDatabase();
    const tx = db.transaction(STORE_NAME, 'readwrite');
    const store = tx.objectStore(STORE_NAME);
    store.clear();
  } catch (err) {
    console.error('Gagal membersihkan database offline:', err);
  }
}

export async function syncOfflinePresensis() {
  if (!navigator.onLine) return;

  const records = await getOfflinePresensis();
  if (records.length === 0) return;

  showOfflineBanner('🔄 Mengirimkan data presensi offline ke server...');

  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  let successCount = 0;

  for (const record of records) {
    try {
      const formData = new FormData();
      Object.keys(record).forEach(key => {
        if (key !== 'id' && key !== 'saved_at') {
          formData.append(key, record[key]);
        }
      });

      const response = await fetch('/tutor/presensi', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrfToken || '',
          'Accept': 'application/json'
        },
        body: formData
      });

      if (response.ok || response.redirected) {
        successCount++;
      }
    } catch (err) {
      console.error('Gagal sinkronisasi data presensi:', err);
    }
  }

  if (successCount > 0) {
    await clearOfflinePresensis();
    showOfflineBanner(`✅ Berhasil menyinkronkan ${successCount} data presensi offline!`, 'success');
    setTimeout(() => {
      window.location.href = '/tutor/dashboard';
    }, 1500);
  }
}

function showOfflineBanner(message, type = 'warning') {
  let alertDiv = document.getElementById('pwaOfflineNotice');
  if (!alertDiv) {
    alertDiv = document.createElement('div');
    alertDiv.id = 'pwaOfflineNotice';
    alertDiv.className = `flashAlert ${type}`;
    alertDiv.style.position = 'fixed';
    alertDiv.style.top = '10px';
    alertDiv.style.left = '50%';
    alertDiv.style.transform = 'translateX(-50%)';
    alertDiv.style.zIndex = '9999';
    alertDiv.style.width = '90%';
    alertDiv.style.maxWidth = '480px';
    alertDiv.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
    document.body.prepend(alertDiv);
  }
  alertDiv.className = `flashAlert ${type}`;
  alertDiv.innerHTML = message;
}

// Auto Sync Event Listeners
if (typeof window !== 'undefined') {
  window.addEventListener('online', () => {
    syncOfflinePresensis();
  });

  window.addEventListener('offline', () => {
    showOfflineBanner('🌐 Anda sedang dalam mode Offline (Tanpa Internet)');
  });

  // Check on load
  document.addEventListener('DOMContentLoaded', () => {
    if (!navigator.onLine) {
      showOfflineBanner('🌐 Anda sedang dalam mode Offline (Tanpa Internet)');
    } else {
      syncOfflinePresensis();
    }
  });
}
